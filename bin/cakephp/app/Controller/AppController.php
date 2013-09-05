<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    // use Json responses
    public $viewClass = 'Json';
    public $components = array('PreslogAuth', 'RequestHandler');



    /**
     * Error 404 -  Not Found handler
     */
    public function errorNotFound( $options )
    {
        return $this->triggerError( 401, $options );
    }


    /**
     * Error 401 - Unauthorised access
     * @param $options
     * @return mixed
     */
    public function errorUnauthorised( $options )
    {
        return $this->triggerError( 401, $options );
    }


    /**
     * Error 500 - Generic server error
     * @param $options
     */
    public function errorGeneric( $options )
    {
        return $this->triggerError( 500, $options );
    }


    /**
     * Error 403 - Gateway Time-out
     * @param $options
     */
    public function errorGateway( $options )
    {
        return $this->triggerError( 503, $options );
    }


    /**
     * Trigger an error on the client response
     * @param int       $code       Error code
     * @param array     $options    Options for the error
     */
    public function triggerError( $code = 500, $options=array() )
    {
        // Apply message
        $options['message'] = ( isset($options['message']) && !empty($options['message']) ? $options['message'] : 'Unspecified error' );

        // Apply code
        $options['code'] = $code;

        // Set header
        $this->response->statusCode( $code );

        // Set data for output
        $this->set( 'options', $options );
        $this->set('_serialize', array('options'));
    }


    /**
     * Configure authentication
     */
    function beforeFilter()
    {
        $this->PreslogAuth->fields  = array(
            'username'=>'username', //The field the user logs in with (eg. username)
            'password' =>'password' //The password field
        );
        $this->PreslogAuth->authorize = 'controller';
        $this->PreslogAuth->autoRedirect = false;
        $this->PreslogAuth->loginAction = array('controller' => 'users', 'action' => 'login');
        $this->PreslogAuth->logoutRedirect = array('controller' => 'users', 'action' => 'login');
        $this->PreslogAuth->loginRedirect = array('controller' => 'users', 'action' => 'welcome');
    }


    /**
     * Check if the user is authorised on this resource
     * We can also call $this->isAuthorised against a specific permission
     */
    function isAuthorised( $permission=null )
    {
        // Get the users role
        $userRole = $this->PreslogAuth->user('role');

        // Load the ACL configuration
        $config = Configure::read('auth-acl');

        // If no role, set as anonymous
        $userRole = ($userRole ? $userRole : $config['anonymousRole']);

        // super-user can do everything
        if($userRole === $config['superUser']) return true;

        // If we're not checking a SPECIFIC permission on the user, check the controller/action path
        if ( $permission ) {
            return ( isset($config['permissions'][ $userRole ]) && in_array($permission, $config['permissions'][ $userRole ]) );
        }

        // Check the route
        else {

            // Scan all rules
            foreach ($config as $rule)
            {
                // Match by controller or wildcard
                if ($rule['controller'] != $this->name && $rule['controller'] != '*')
                    continue;

                // Match by action or wildcard
                if ($rule['action'] != $this->action && $rule['action'] != '*')
                    continue;

                if ( in_array($userRole, $rule['permissions']) )
                    return true;
            }
        }

        // Permisson check failed
        return false;
    }


}
