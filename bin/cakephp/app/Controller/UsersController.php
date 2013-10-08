<?php
/**
 * Class UsersController
 */

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
use Swagger\Annotations as SWG;

/**
 * Class UsersController
 * @property User $User
 * @property Client $Client
 */

class UsersController extends AppController
{
    public $uses = array('User', 'Client');


    /**
     * TODO: DELETE ME
     * Debug task, for random actions.
     */
    public function debugTask()
    {
        echo Security::hash('test11', 'blowfish', false);
    }



    /**
     * Login the given user
     * - A call without credentials is an auto-login ping (session login). Return a simple error on fail.
     * - A call with credentials is considered a new login attempt. Always try to validate.
     *
     * @SWG\Operation(
     *      partial="users.login",
     *      summary="Attempt to login the user.",
     *      notes="Attempts to login with existing Session, then form data.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="User[email]",
     *              paramType="form",
     *              dataType="string",
     *              required="false",
     *              description="Users username"
     *          ),
     *          @SWG\Parameter(
     *              name="User[password]",
     *              paramType="form",
     *              dataType="string",
     *              required="false",
     *              description="Users password"
     *          )
     *      )
     * )
     */
    public function login()
    {
        // Establish default failure response
        $response = array(
            'error' => true,
            'message' => 'An unknown error occurred.',
        );

        // User supplied credentials?
        if (array_key_exists('User', $this->request->data))
        {
            // Try to login with form validation
            if( !$this->PreslogAuth->login() )
            {
                $response['message'] = 'Invalid username or password.';
            }
        }
        else
        {
            // If no credentials, and not logged in, a simple error will suffice.
            // This gets overridden below if the user IS logged in.
            $response['message'] = 'You are not logged in.';
        }

        // Did login succeed?
        if ($this->PreslogAuth->loggedIn())
        {
            // Fetch user data
            $user = $this->PreslogAuth->User();
            $permissions = $this->PreslogAuth->getUserPermissions();

            // Get list of accessible clients, where available
            $conditions = array('deleted'=>false);
            if ( $this->isAuthorized('single-client') )
            {
                $conditions['_id'] = $user['client_id'];
            }

            $clients = $this->Client->find('all', array(
                'conditions'=>$conditions,
                'fields'=>array(
                    '_id',
                    'name',
                )
            ));

            // Flatten
            foreach ($clients as $k=>$client)
            {
                $client['Client']['logo'] = $this->Client->getLogoPath($client['Client']);
                $clients[$k] = $client['Client'];
            }

            // Override error response with success response!
            $response = array(
                'success' => true,                  // Went Ok
                'user' => $user,                    // User Info
                'permissions' => $permissions,      // User Permissions
                'clients' => $clients,              // Accessible client list
            );
        }

        // Send response
        $this->set('login', $response);
        $this->set('_serialize', array('login'));
    }


    /**
     * Logout the current user
     *
     * @SWG\Operation(
     *      partial="users.logout",
     *      summary="Logout the current user.",
     *      notes=""
     * )
     */
    public function logout()
    {
        // Logout
        $this->PreslogAuth->logout();

        // Response
        $response = array(
            'success'=>true,
            'message'=>'You have been logged out.',
        );

        // Send
        $this->set('login', $response);
        $this->set('_serialize', array('login'));
    }


    /**
     * My-Profile Options
     *
     * @SWG\Operation(
     *      partial="users.my-profile.options",
     *      summary="Fetch My Profile options",
     *      notes="Any logged in user may load this data."
     * )
     */
    public function myProfileOptions()
    {
        // Nothing to return.
        $this->set('_serialize', array());
    }


    /**
     * My-Profile Read
     *
     * @SWG\Operation(
     *      partial="users.my-profile.read",
     *      summary="Fetch My Profile data",
     *      notes="Any logged in user may load this data."
     * )
     */
    public function myProfileRead()
    {
        $userId = $this->PreslogAuth->user('_id');

        // Safeguard peoples info!
        if (!$userId)
        {
            $this->errorGeneric(array('message'=>'Error loading current users details.'));
        }

        // Fetch user with all fields
        $user = $this->User->findById(
            $userId,
            array('fields'=>array(
                'firstName',
                'lastName',
                'email',
                'company',
                'phoneNumber',
            ))
        );

        // User must exist
        if (!$user)
        {
            $this->errorNotFound(array('message'=>'User could not be found'));
        }

        // Kill the ID - otherwise POST backs go wrong
        unset($user['User']['_id']);

        // Output
        $this->set($user);
        $this->set('_serialize', array_keys($user));
    }


    /**
     * My-Profile Edit
     *
     * @SWG\Operation(
     *      partial="users.my-profile.update",
     *      summary="Update My Profile data",
     *      notes="Updates are applied to the currently logged in user account."
     * )
     */
    public function myProfileUpdate()
    {
        // Fetch user data
        $user = $this->request->data['User'];

        // Force the user ID to be the current user
        $user['_id'] = $this->PreslogAuth->user('_id');

        // Safeguard peoples info!
        if (!$user['_id'])
        {
            $this->errorGeneric(array('message'=>'Error loading current users details.'));
        }

        // Apply data and validate before insert
        $this->User->set($user);
        if ( !$this->User->validatesMyProfile() )
        {
            $this->errorBadRequest( array('data'=>$this->User->validationErrors, 'message'=>'Validation failed') );
        }

        // Save limited fields
        $ret = $this->User->save( $user, false, array(
            'firstName',
            'lastName',
            'email',
            'password',
            'company',
            'phoneNumber',
        ));

        // Return success
        $return = array('Success'=>$ret);
        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * My-Notifications Options
     *
     * @SWG\Operation(
     *      partial="users.my-notifications.options",
     *      summary="Fetch My Notifications Options",
     *      notes="Any logged in user may load this data."
     * )
     */
    public function myNotificationsOptions()
    {
        // This users ID
        $userId = $this->PreslogAuth->user('_id');

        // Safeguard peoples info!
        if (!$userId)
        {
            $this->errorGeneric(array('message'=>'Error loading current users details.'));
        }

        // Get notifications this user can see
        $options['notifications'] = $this->Client->getNotificationsList( $userId );

        // Output
        $this->set($options);
        $this->set('_serialize', array_keys($options));
    }


    /**
     * My-Notifications Read
     *
     * @SWG\Operation(
     *      partial="users.my-notifications.read",
     *      summary="Read My Notifications data",
     *      notes="Any logged in user may load this data."
     * )
     */
    public function myNotificationsRead()
    {
        // Force the user ID to be the current user
        $userId = $this->PreslogAuth->user('_id');

        // Safeguard peoples info!
        if (!$userId)
        {
            $this->errorGeneric(array('message'=>'Error loading current users details.'));
        }

        // Fetch user with all fields
        $user = $this->User->findById(
            $userId,
            array('fields'=>array(
                'notifications',
            ))
        );

        // User must exist
        if (!$user)
        {
            $this->errorNotFound(array('message'=>'User could not be found'));
        }

        // Output
        $this->set($user);
        $this->set('_serialize', array_keys($user));
    }


    /**
     * My Notifications Edit
     *
     * @SWG\Operation(
     *      partial="users.my-notifications.update",
     *      summary="Update My Notifications data",
     *      notes="Updates are applied to the currently logged in user account."
     * )
     */
    public function myNotificationsEdit()
    {
        // Fetch user data
        $user = $this->request->data['User'];

        // Set the ID to the current user
        $user['_id'] = $this->PreslogAuth->user('_id');

        // Safeguard peoples info!
        if (!$user['_id'])
        {
            $this->errorGeneric(array('message'=>'Error loading current users details.'));
        }

        // Apply data and validate before insert
        $this->User->set($user);
        if ( !$this->User->validatesMyNotifications() )
        {
            $this->errorBadRequest( array('data'=>$this->User->validationErrors, 'message'=>'Validation failed') );
        }

        // Save
        $ret = $this->User->save( $user, false, array(
            'notifications'
        ));

        // Return success
        $return = array('Success'=>$ret);
        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * List all users with partial details
     *
     * @SWG\Operation(
     *      partial="admin.users.read",
     *      summary="List users",
     *      notes="User must be an Administrator"
     * )
     */
    public function adminList()
    {
        // Fetch all users, with limited fields
        $users = $this->User->find('all', array(
            'fields'=>array(
                'id',
                'firstName',
                'lastName',
                'role',
                'email',
                'client',
                'company',
                'deleted',
            )
        ));

        // Flatten the array for simplicity
        foreach ($users as &$user) {
            $user = $user['User'];
        }

        // Output
        $this->set('users', $users);
        $this->set('_serialize', array('users'));
    }


    /**
     * Fetch field options for use with Admin Edit
     * - User Roles
     * - Clients available
     * - Notifications available
     *
     * @SWG\Operation(
     *      partial="admin.users.options",
     *      summary="Retrieves form options for users",
     *      notes="User must be an Administrator"
     * )
     */
    public function adminEditOptions( $userId=null )
    {
        $options = array();

        // Get all roles
        $options['roles'] = $this->User->getAvailableRoles();

        // Get all clients
        $options['clients'] = $this->Client->getClientsAsOptions();

        // Get all clients and attributes
        $options['notifications'] = $this->Client->getNotificationsList( $userId );

        // Output
        $this->set($options);
        $this->set('_serialize', array_keys($options));
    }


    /**
     * GET: Read the specified user
     *
     * @SWG\Operation(
     *      partial="admin.users.specific.read",
     *      summary="Fetch data for a specific user",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="user_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="User ID"
     *          )
     *      )
     * )
     */
    public function adminRead( $id )
    {
        // Fetch user with all fields
        $user = $this->User->findById( $id );

        // User must exist
        if (!$user)
        {
            $this->errorNotFound(array('message'=>'User could not be found'));
        }

        // Output
        $this->set($user);
        $this->set('_serialize', array_keys($user));
    }


    /**
     * User Editing
     * - GET: Fetch a users details
     * - POST without ID: create a new user
     * - POST with ID: update an existing user
     * @param   int     $id
     *
     * @SWG\Operation(
     *      partial="admin.users.create",
     *      summary="Create a new user",
     *      notes="User must be an Administrator"
     * )
     *
     * @SWG\Operation(
     *      partial="admin.users.specific.update",
     *      summary="Update a specific user",
     *       notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="user_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="User ID"
     *          )
     *      )
     * )
     */
    public function adminEdit( $id=null )
    {
        // Fetch user data
        $user = $this->request->data['User'];

        // If user has ID, make sure this is the one we save
        if ($id)
        {
            $user['_id'] = $id;
        }

        // Apply data and validate before insert
        $this->User->set($user);
        if ( !$this->User->validatesAdminEdit() )
        {
            $this->errorBadRequest( array('data'=>$this->User->validationErrors, 'message'=>'Validation failed') );
        }

        // Save
        $ret = $this->User->save( $user );


        // Return success
        $return = array('Success'=>$ret);
        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * Delete a given user
     *
     * @SWG\Operation(
     *      partial="admin.users.specific.delete",
     *      summary="Delete a specific user",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="user_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="User ID"
     *          )
     *      )
     * )
     */
    public function adminDelete( $id )
    {
        // user must exist
        if (!$this->User->findById($id)) {
            $this->errorNotFound('User could not be found');
        }

        // Simple delete save
        $user = array(
            'id'=>$id,
            'deleted'=>true,
        );

        // Delete
        $this->User->save( array('User'=>$user) );

        // OK Response
        $this->set('success', true);
        $this->set('_serialize', array('success'));
    }



    /**
     * Send a password reset email
     *
     * @SWG\Operation(
     *      partial="users.reset-password.email",
     *      summary="Send a password reset email to the requested user",
     *      notes="Any user can make this request. Email address must exist in the system. Email will be sent to the requested address.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="email",
     *              paramType="form",
     *              dataType="string",
     *              required="true",
     *              description="Email address"
     *          )
     *      )
     * )
     */
    public function resetPasswordEmail()
    {
        // Fetch requested email address
        $emailAddress = $this->request->data('email');

        // Validate: Email is valid?
        if (!$emailAddress)
        {
            $this->errorBadRequest(array('message'=>'You must supply a valid email address.'));
        }

        // Find the user
        $user = $this->User->find('first', array('conditions'=>array(
            'email'=>$emailAddress
        )));

        // Error if user doesn't exist
        if (empty($user))
        {
            $this->errorBadRequest(array('message'=>'There are no users with the email address you specified.'));
        }

        // Create a token for the reset
        $token = 123;

        // Save the token
        $user['password-token'] = $token;
        $this->User->save($user);

        // Author email to the user for their reset
        $email = new CakeEmail('default');
        $email->to($user['email'], $user['firstName'].' '.$user['lastName']);
        $email->subject('Password Reset');
        $email->viewVars(array('user' => $user));
        $email->helpers(array('Html'));
        $email->template('password-reset');

        // Send the email
        $email->send();

        // Send OK to user
        $this->set('success', true);
        $this->set('_serialize', array('success'));
    }


    /**
     * Send a password reset email
     *
     * @SWG\Operation(
     *      partial="users.reset-password",
     *      summary="Change the users password, using the supplied token",
     *      notes="Any user can make this request. Token must exist in the system under a user.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="token",
     *              paramType="form",
     *              dataType="string",
     *              required="true",
     *              description="Password reset token"
     *          )
     *      )
     * )
     */
    public function resetPassword()
    {
        // Find the user by their token
        $user = array();

        // User or token doesn't exist? Error.
        if (empty($user))
        {
            $this->errorBadRequest(array('message'=>'Invalid password reset token. Please try again.'));
        }

        // encrypt the new password
        $password = 123;

        // Save the new password over this user and remove the existing token
        $user['password'] = $password;
        $user['password-token'] = null;
        $this->User->save($user);

        // Report success
        $this->set('success', true);
        $this->set('_serialize', array('success'));
    }
}