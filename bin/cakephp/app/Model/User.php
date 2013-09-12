<?php

/**
 * User Model
 */

App::uses('AppModel', 'Model');

class User extends AppModel
{
    public $name = "User";


    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'           => array('type' => 'string', 'length'=>40, 'primary' => true),
        'firstName'     => array('type' => 'string', 'length'=>255),
        'lastName'      => array('type' => 'string', 'length'=>255),
        'email'         => array('type' => 'string', 'length'=>255),
        'password'      => array('type' => 'string'),
        'company'       => array('type' => 'text'),
        'phoneNumber'   => array('type' => 'string', 'length'=>40),
        'role'          => array('type' => 'string'),
        'client'        => array('type' => 'string'),
        'deleted'       => array('type' => 'boolean'),
        'notifications' => array('type' => null),
        'favouriteDashboards'   => array('type' => null),
        'created'       => array('type' => 'datetime'),
        'modified'      => array('type' => 'datetime'),
    );


    /**
     * @var array   Validation rules for standard user profile
     */
    public $validateUser = array(
        'firstName'=>array(
            'max-length'=>array(
                'rule'=>array('maxLength', 255),
                'message'=>'Maximum length is 255 characters',
                'required'=>true
            ),
            'min-length'=>array(
                'rule'=>array('notEmpty'),
                'message'=>'Must not be empty',
            ),
        ),
        'lastName'=>array(
            'max-length'=>array(
                'rule'=>array('maxLength', 255),
                'message'=>'Maximum length is 255 characters',
                'required'=>true
            ),
            'min-length'=>array(
                'rule'=>array('notEmpty'),
                'message'=>'Must not be empty',
            ),
        ),
        'email'=>array(
            'max-length'=>array(
                'rule'=>array('maxLength', 255),
                'message'=>'Maximum length is 255 characters',
                'required'=>true
            ),
            'min-length'=>array(
                'rule'=>array('notEmpty'),
                'message'=>'Must not be empty',
            ),
            'email'=>array(
                'rule'=>array('email'),
                'message'=>'Must be a valid email address',
            )
        ),
        'company'=>array(
            'max-length'=>array(
                'rule'=>array('maxLength', 255),
                'message'=>'Maximum length is 255 characters',
                'required'=>true,
                'allowEmpty'=>true,
            ),
            'min-length'=>array(
                'rule'=>array('notEmpty'),
                'message'=>'Must not be empty',
                'required'=>true
            ),
        ),
        'phoneNumber'=>array(
            'max-length'=>array(
                'rule'=>array('maxLength', 40),
                'message'=>'Maximum length is 40 characters',
                'required'=>true,
                'allowEmpty'=>true,
            ),
            'min-length'=>array(
                'rule'=>array('minLength', 3),
                'message'=>'Minimum length is 3 characters',
            ),
            'numericOnly'=>array(
                'rule'=>array('numeric'),
                'message'=>'Must contain only numbers.'
            ),
        ),

    );


    /**
     * @var array   Validation rules for fields available to admins only
     */
    public $validateUserAdmin = array(
        'role'=>array(
            'role'=>array(
                'required'=>true,
                'rule'=>array('validateRole'),
                'message'=>'Must be a valid role',
            )
        ),
        'client_id'=>array(
            'client'=>array(
                'required'=>true,
                'rule'=>array('validateClient'),
                'message'=>'Must be a valid client ID',
            )
        ),
    );


    /**
     * @var array   Validation rules for Notification fields
     */
    public $validateNotifications = array(
        'notifications'=>array(
            'notify'=>array(
                'required'=>true,
                'rule'=>array('validateNotifications'),
                'message'=>'Must be a valid notification selection',
            ),
        ),
    );


    /**
     * @var array   Validation rules for (optional) Password fields
     */
    public $validatePassword = array(
        'password'=>array(
            'password'=>array(
                'required'=>true,
                'rule'=>array('validatePassword'),
                'message'=>'Must be a valid password',
            )
        )
    );


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


    /**
     * Validate the given user details for AdminEdit operations
     * @returns     bool
     */
    public function validatesAdminEdit()
    {
        $rules = array();

        // Optinally validate the password field(s) if supplied
        if ( !empty($data['password']) )
        {
            $rules = array_merge($rules, $this->validatePassword);
        }

        // Validate on Notifications
        $rules = array_merge($rules, $this->validateNotifications);

        // Validate on user deets
        $rules = array_merge($rules, $this->validateUser);

        // Validate on user admin-only vars
        $rules = array_merge($rules, $this->validateUserAdmin);

        // Apply rules to validator
        $validator = $this->validator();
        foreach ($rules as $field=>$rule)
        {
            $validator->add($field, $rule);
        }

        // Validate
        $success = $validator->validates();

        return $success;
    }


    public function validateNotifications( $check )
    {
        return true;
    }

    public function validateRole( $check )
    {
        return true;
    }

    public function validatePassword( $check )
    {
        return true;
    }

    public function validateClient( $check )
    {
        return true;
    }

}