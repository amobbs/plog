<?php
/**
 * Class UsersController
 */

class UsersController extends AppController
{

    public $uses = array('User');


    /**
     * Login the given user
     */
    public function login()
    {
        $success = 'nope';

        if($this->PreslogAuth->user()) {
            $this->Session->write('Auth.User.group', $this->User->Group->field('name',array('id' => $this->Auth->user('group_id'))));

            $success = 'yup';
        }

        $this->set('test', $success);
        $this->set('_serialize', array('test'));
    }


    /**
     * test user permissions
     * delete me
     */
    public function testPermissions()
    {
        echo "derp";
        var_dump($this->isAuthorised('guest'));
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