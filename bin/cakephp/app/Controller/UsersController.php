<?php
/**
 * Class UsersController
 */

class UsersController extends AppController
{

    public $uses = array('Users');


    /**
     * Login the given user
     */
    public function login()
    {
        $users = $this->Users->find('all');
        print_r($users);
        die();

        // Establish default response
        $response = array(
            'error' => true,
            'message' => '',
        );

        // Try to login the user
        if($this->PreslogAuth->user()) {
            $this->Session->write('Auth.User.group', $this->User->Group->field('name',array('id' => $this->Auth->user('group_id'))));
        }
        else {
            $response['message'] = "Invalid username or password";

        }

        // Did login succeed?
        if ($this->PreslogAuth->loggedIn())
        {
            // Fetch user data
            $user = $this->PreslogAuth->User();
            $permissions = array();

            // Override error response with success response!
            $response = array(
                'user' => $user,
                'permissions' => $permissions
            );
        }

        $this->set('login', $response);
        $this->set('_serialize', array('login'));
    }


    /**
     * Logout the current user
     */
    public function logout()
    {
        $this->PreslogAuth->logout();
        $this->set('test', 'Logged out');
        $this->set('_serialize', array('test'));

    }

}