<?php

/**
 * User Model
 */

App::uses('AppModel', 'Model');

class User extends AppModel
{
    public $name = "User";


    /**
     * Fetch all available roles and return a simple array
     * @return array
     */
    public function getAvailableRoles()
    {
        $rawRoles = Configure::read('auth-acl.roles');

        $roles = array();

        foreach ($rawRoles as $roleKey=>$role)
        {
            $item['hidden'] = (isset($role['hidden']) ? $role['hidden'] : false);
            $item['name'] = $role['name'];
            $item['id'] = $roleKey;

            $roles[] = $item;
        }

        return $roles;
    }


    /**
     * Find a specific user by the supplied ID
     * @param   $id
     * @param   $options
     * @return  array
     */
    public function findById( $id, $options = array() )
    {
        $defaultOptions = array(
            'conditions'=>array(
                'id'=>$id
            ),
            'fields'=>array(
                'id',
                'firstName',
                'lastName',
                'email',
                'company',
                'phoneNumber',
                'role',
                'client_id',
                'notifications',
                'favouriteDashboards',
                'deleted',
            ),
        );

        return $this->find('first', array_merge( $options, $defaultOptions ));
    }


}