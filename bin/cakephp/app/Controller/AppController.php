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
    public $viewClass = 'PreslogJson';
    public $components = array('PreslogAuth', 'RequestHandler', 'Session');


    /**
     * Error 400 - Bad Request
     * - Resource request validation failed
     */
    public function errorBadRequest( $options=array() )
    {
        return $this->triggerError( 400, $options);
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
     * Error 403 - Forbidden
     * @param $options
     * @return mixed
     */
    public function errorForbidden( $options=array() )
    {
        return $this->triggerError( 403, $options);
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
     * Error 500 - Generic server error
     * @param $options
     */
    public function errorGeneric( $options=array() )
    {
        return $this->triggerError( 500, $options );
    }


    /**
     * Error 503 - Gateway Time-out
     * @param $options
     */
    public function errorGateway( $options=array() )
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

        // BUGFIX: CakePHP Cookie expiry fix
        // Until CakePHP 2.3, the cookie expiry will not update when cookie content is updated. This is a workaround.
        // http://cakephp.lighthouseapp.com/projects/42648/tickets/3047-session-cookie-timeout-is-not-refreshed
        if(isset($_COOKIE[Configure::read("Session.cookie")]))
        {
            $session_delay = Configure::read("Session.timeout");
            setcookie(Configure::read("Session.cookie"), $_COOKIE[Configure::read("Session.cookie")], time() + $session_delay, "/");
        }

        // Run parent filters
        parent::beforeFilter();
    }


    /**
     * Check if the user is authorised on this resource
     * We can also call $this->isAuthorised against a specific permission or specific userRole
     *
     * @param   string  $permission     Specific permission to check for
     * @param   string  $userRole       The role to check the permission for, as opposed to the active user
     * @return  bool                    True if permission is available
     */
    function isAuthorized( $permission=null, $userRole=null )
    {
        // get user model
        $userModel = ClassRegistry::init('User');

        // Get the users role from PreslogAuth if not supplied
        $userRole = ($userRole ? $userRole : $this->PreslogAuth->user('role'));

        // Check
        return $userModel->isAuthorized(
            $permission,
            $userRole,
            array('controller'=>$this->name, 'action'=>$this->action)
        );
    }


}
