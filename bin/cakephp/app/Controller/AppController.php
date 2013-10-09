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
 * CakePHP Component & Model Code Completion
 * @author junichi11
 *
 * ==============================================
 * CakePHP Core Components
 * ==============================================
 * @property PreslogAuthComponent $PreslogAuth
 * @property AclComponent $Acl
 * @property CookieComponent $Cookie
 * @property EmailComponent $Email
 * @property RequestHandlerComponent $RequestHandler
 * @property SecurityComponent $Security
 * @property SessionComponent $Session
 */

class AppController extends Controller {

    // use Json responses
    public $viewClass = 'Json';
    public $components = array('PreslogAuth', 'RequestHandler', 'Session');


    /**
     * Error 400 - Bad Request
     * - Resource request validation failed
     */
    public function errorBadRequest( $options=array() )
    {
        $this->triggerError( 400, $options);
    }


    /**
     * Error 404 -  Not Found handler
     * - Resource does not exist
     */
    public function errorNotFound( $options=array() )
    {
        return $this->triggerError( 404, $options );
    }


    /**
     * Error 401 - Unauthorised access
     * @param $options
     * @return mixed
     */
    public function errorUnauthorised( $options=array() )
    {
        return $this->triggerError( 401, $options );
    }


    /**
     * Error 403 - Gateway Time-out
     * @param $options
     */
    public function errorGateway( $options=array() )
    {
        return $this->triggerError( 503, $options );
    }


    /**
     * Error 500 - Generic server error
     * @param $options
     */
    public function errorGeneric( $options=array() )
    {
        return $this->triggerError( 500, $options );
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

        $options['error'] = true;

        // Set header
        $this->response->statusCode( $code );

        // Set data for output
        $this->set($options);
        $this->set('_serialize', array_keys($options));

        $this->render();
        $this->response->send();
        $this->shutdownProcess();
        exit();
    }


    /**
     * Configure authentication
     */
    public function beforeFilter()
    {
        // Configure auth
        $this->PreslogAuth->authorize = 'controller';
        $this->PreslogAuth->autoRedirect = false;
        $this->PreslogAuth->loginAction = array('controller' => 'users', 'action' => 'login');
        $this->PreslogAuth->logoutRedirect = array('controller' => 'users', 'action' => 'login');
        $this->PreslogAuth->loginRedirect = array('controller' => 'users', 'action' => 'welcome');

        // Use Blowfish (bcrypt)
        // Set Form fields are email and password
        $this->PreslogAuth->authenticate = array(
            'Preslog'=>array(
                'passwordHasher'=>'Blowfish',
                'fields'=>array(
                    'username'=>'email',
                    'password'=>'password',
                ),
            ),
        );

        parent::beforeFilter();
    }


    /**
     * Check if the user is authorised on this resource
     * We can also call $this->isAuthorised against a specific permission or specific userRole
     */
    function isAuthorized( $permission=null, $userRole=null )
    {
        // Get the users role from PreslogAuth if not supplied
        $userRole = ($userRole ? $userRole : $this->PreslogAuth->user('role'));

        // Load the ACL configuration
        $config = Configure::read('auth-acl');

        // If no role, set as anonymous
        $userRole = ($userRole ? $userRole : $config['anonymousRole']);

        // If we're not checking a SPECIFIC permission on the user, check the controller/action path
        if ( $permission ) {
            return ( isset($config['roles'][ $userRole ]['permissions']) && in_array($permission, $config['roles'][ $userRole ]['permissions']) );
        }

        // Check the route
        else {

            // Scan all rules
            foreach ($config['routes'] as $rule)
            {
                // Match by controller or wildcard
                if ($rule['controller'] != $this->name && $rule['controller'] != '*')
                    continue;

                // Match by action or wildcard
                if ($rule['action'] != $this->action && $rule['action'] != '*')
                    continue;

                // Match permission required to the role's available permissions
                if ( sizeof( array_intersect($rule['permissions'], $config['roles'][$userRole]['permissions']) ) )
                    return true;
            }
        }

        // Permisson check failed
        return false;
    }


}
