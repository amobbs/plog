<?php
/**
 * Class UsersController
 */

App::uses('AppController', 'Controller');
use Swagger\Annotations as SWG;

/**
 * Class UsersController
 * @property User $User
 */

class UsersController extends AppController
{
    public $uses = array('User');


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
            if( $this->PreslogAuth->login() )
            {
                // Do magical i-just-logged-on things
                //$this->Session->write('Auth.User.group', $this->PreslogAuth->user());     NO LONGER REQUIRED
            }
            else
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

            // Override error response with success response!
            $response = array(
                'success' => true,
                'user' => $user,
                'permissions' => $permissions
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
     * My-Profile Get/Edit
     *
     * @SWG\Operation(
     *      partial="users.my-profile.read",
     *      summary="Fetch My Profile data",
     *      notes="Any logged in user may load this data."
     * )
     *
     * @SWG\Operation(
     *      partial="users.my-profile.update",
     *      summary="Update My Profile data",
     *      notes="Updates are applied to the currently logged in user account."
     * )
     */
    public function myProfile()
    {
        // TODO
        $this->set('todo', 'My Profile');
        $this->set('_serialize', array('todo'));
    }


    /**
     * My-Notifications Get/Edit
     *
     * @SWG\Operation(
     *      partial="users.my-notifications.read",
     *      summary="Read My Notifications data",
     *      notes="Any logged in user may load this data."
     * )
     *
     * @SWG\Operation(
     *      partial="users.my-notifications.update",
     *      summary="Update My Notifications data",
     *      notes="Updates are applied to the currently logged in user account."
     * )
     */
    public function myNotifications()
    {
        // TODO
        $this->set('todo', 'My Notifications');
        $this->set('_serialize', array('todo'));
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
    public function adminEditOptions()
    {
        $options = array();

        // Get all roles
        $options['roles'] = $this->User->getAvailableRoles();


        // TODO
        $options['clients'] = array(
            array(
                'id'=>'1',
                'name'=>'ABC',
            ),
            array(
                'id'=>'2',
                'name'=>'WIN',
            )
        );

        // TODO
        $options['notifications'] = array(
            'clients'=>array(
                'name'=>'one',
                'id'=>1,
                'severities'=>array(
                    array(
                        'name'=>'Sev 1',
                        'id'=>'1',
                    ),
                    array(
                        'name'=>'Sev 2',
                        'id'=>'2',
                    )
                ),
                'attributes'=>array(
                    array(
                        'id'=>'1234',
                        'name'=>'Networks',
                        'deleted'=>false,
                        'children'=>array(
                            array(
                                'id'=>'1',
                                'name'=>'test',
                                'deleted'=>false,
                                'children'=>array()
                            )
                        )
                    ),
                )
            )
        );


        // Output
        $this->set($options);
        $this->set('_serialize', array_keys($options));
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


}