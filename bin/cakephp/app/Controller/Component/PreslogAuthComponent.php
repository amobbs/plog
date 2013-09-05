<?php

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

        $this->controller->errorUnauthorised( array('message'=>'You do not have permission to access this resource.') );

        //$this->controller = new Controller();
        $this->controller->render();
        $this->controller->shutdownProcess();

        $this->_stop();

        return false;
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
        // Throw 401 error
        $this->controller->errorUnauthorised( array('message'=>'You do not have permission to access this resource.') );

        return false;
    }

}
