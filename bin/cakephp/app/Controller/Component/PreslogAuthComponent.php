<?php

App::import('Component', 'Auth');

/**
 * Preslog customised authentication component
 * - Handles unauthorised attempts with 401 errors
 */

class PreslogAuthComponent extends AuthComponent
{
    protected $controller;


    /**
     * Maintain an connection to the initialising controller
     * @param Controller $controller
     */
    public function initialize(Controller $controller) {
        $this->controller = &$controller;

        parent::initialize($controller);
    }


    /**
     * Action when used is not authenticated
     * @param Controller $controller
     * @return bool
     */
    protected function _unauthenticated(Controller $controller)
    {
        if (empty($this->_authenticateObjects)) {
            $this->constructAuthenticate();
        }
        $auth = $this->_authenticateObjects[count($this->_authenticateObjects) - 1];
        if ($auth->unauthenticated($this->request, $this->response)) {
            return false;
        }

        if ($this->_isLoginAction($controller)) {
            return true;
        }

        // Execute unauthorised action
        $this->controller->errorUnauthorised( array('message'=>'You do not have permission to access this resource.') );

        // Display and exit
        echo $this->controller->render();
        $this->controller->shutdownProcess();
        $this->_stop();
    }


    /**
     * Action when the user is not authorised to access this resource
     *
     * @param Controller $controller
     * @return bool
     * @throws ForbiddenException
     */
    protected function _unauthorized(Controller $controller)
    {
        // Dsiplay 301
        $this->controller->errorUnauthorised( array('message'=>'You do not have permission to access this resource.') );

        return false;
    }


    /**
     * Checks whether current action is accessible without authentication.
     *
     * @param Controller $controller A reference to the instantiating controller object
     * @return boolean True if action is accessible without authentication else false
     */
    protected function _isAllowed(Controller $controller) {

        $action = strtolower($controller->request->params['action']);

        if (in_array($action, array_map('strtolower', $this->allowedActions))) {
            return true;
        }
        return false;
    }


    /**
     * Main execution method. Handles redirecting of invalid users, and processing
     * of login form data.
     *
     * @param Controller $controller A reference to the instantiating controller object
     * @return boolean
     */
    public function startup(Controller $controller) {
        $methods = array_flip(array_map('strtolower', $controller->methods));
        $action = strtolower($controller->request->params['action']);

        $isMissingAction = (
            $controller->scaffold === false &&
            !isset($methods[$action])
        );

        if ($isMissingAction) {
            return true;
        }

        if (!$this->_setDefaults()) {
            return false;
        }

        if ($controller->isAuthorized()) {
            return true;
        }

        if (!$this->_getUser()) {
            return $this->_unauthenticated($controller);
        }

        if (empty($this->authorize) || $this->isAuthorized($this->user())) {
            return true;
        }

        return $this->_unauthorized($controller);
    }


    /**
     * Fetch a users full list of permissions via their role
     * @params      array       User Data
     * @returns     array       Permissions
     */
    public function getUserPermissions( $user=null )
    {
        $config = Configure::read('auth-acl');

        // Use the current user if none supplied
        if ( empty($user) ) {
            $role = $this->user('role');
        }
        else {
            $role = $user['role'];
        }

        // No role? Must be a guest. Grab the anonymous permissions
        if (empty($role)) {
            $role = $config['anonymousRole'];
        }

        // If the role desginated so far doesn't exist, make it anonymous..
        if (!isset($config['roles'][ $role ]))
        {
            $role = $config['anonymousRole'];
        }

        // Return available permissions
        return $config['roles'][ $role ]['permissions'];
    }

}
