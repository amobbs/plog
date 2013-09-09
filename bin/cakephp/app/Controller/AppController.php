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

        echo $this->render();
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
            'Form'=>array(
                'passwordHasher'=>'blowfish',
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
     * We can also call $this->isAuthorised against a specific permission
     */
    function isAuthorized( $permission=null )
    {
        // Get the users role
        $userRole = $this->PreslogAuth->user('role');

        // Load the ACL configuration
        $config = Configure::read('auth-acl');

        // If no role, set as anonymous
        $userRole = ($userRole ? $userRole : $config['anonymousRole']);

        // If we're not checking a SPECIFIC permission on the user, check the controller/action path
        if ( $permission ) {
            return ( isset($config['permissions'][ $userRole ]) && in_array($permission, $config['permissions'][ $userRole ]) );
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
                if ( sizeof( array_intersect($rule['permissions'], $config['permissions'][$userRole]) ) )
                    return true;
            }
        }

        // Permisson check failed
        return false;
    }


}
