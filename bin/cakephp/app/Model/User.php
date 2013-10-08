<?php

/**
 * User Model
 */

App::uses('AppModel', 'Model');
App::uses('Client', 'Model');

/**
 * Class User
 * @property    Client      $Client
 */

class User extends AppModel
{
    public $name = "User";

    public $actsAs = array('Mongodb.Schema');


    /**
     * Constructor
     * @param bool $id
     * @param null $table
     * @param null $ds
     */
    public function __construct($id = false, $table = null, $ds = null)
    {
        // Load associated classes
        $this->Client = ClassRegistry::init('Client');

        // Load parent construct
        return parent::__construct($id = false, $table = null, $ds = null);
    }


    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'           => array('type' => 'string', 'length'=>24, 'primary' => true, 'mongoType'=>'MongoId'),
        'firstName'     => array('type' => 'string', 'length'=>255),
        'lastName'      => array('type' => 'string', 'length'=>255),
        'email'         => array('type' => 'string', 'length'=>255),
        'password'      => array('type' => 'string', 'length'=>60),
        'company'       => array('type' => 'string', 'length'=>255),
        'phoneNumber'   => array('type' => 'string', 'length'=>40),
        'role'          => array('type' => 'string', 'length'=>32),
        'client_id'     => array('type' => 'string', 'length'=>24, 'mongoType'=>'MongoId'),
        'deleted'       => array('type' => 'boolean'),
        'notifications'         => array('type' => null),
        'favouriteDashboards'   => array('type' => null),
        'created'       => array('type' => 'datetime', 'mongoType'=>'MongoDate'),
        'modified'      => array('type' => 'datetime', 'mongoType'=>'MongoDate'),
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
                'rule'=>array('minLength', 6),
                'message'=>'Password must be at least 6 characters long.',
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

        return $this->find('first', array_merge( $defaultOptions, $options ));
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


    /**
     * Validate fields "My Profile" form
     * @returns     bool        True if valid
     */
    public function validatesMyProfile()
    {
        $rules = $this->validateUser;

        // Optinally validate the password field(s) if supplied
        if ( !empty($data['password']) )
        {
            $rules = array_merge($rules, $this->validatePassword);
        }

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


    /**
     * Validate fields from "My Notifications" form
     * @returns     bool        True if valid
     */
    public function validatesMyNotifications()
    {
        $rules = $this->validateNotifications;

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


    /**
     * Check the notifications options selected exist in the available options for this user.
     * @param       $check
     * @return      bool        True if the notification opts exist
     */
    public function validateNotifications( $check )
    {
        // Fetch all notifications that are available to this user
        // TODO: Make this load the USER TO BE SAVED and pass it to the function
        $this->Client->getNotificationsList( array() );

        // TODO: Validation code

        return true;
    }


    /**
     * Check if the chosen role actually exists
     * @param   $check
     * @return  bool        True if the role exists
     */
    public function validateRole( $check )
    {
        // Fetch roles
        $roles = $this->getAvailableRoles();

        // Check for the role existing
        foreach ($roles as $role)
        {
            if ($role['id'] == $check['role'])
                return true;
        }

        return false;
    }


    /**
     * Check if the the selected Client is a valid client
     * @param       $check
     * @return      bool        True if the client exists in the client list
     */
    public function validateClient( $check )
    {
        // Attempt to fetch the client and check
        $client = $this->Client->findById( $check['client_id'] );
        return (sizeof($client) > 0);
    }


    /**
     * Fetch a list of users by the notifications they're interested in.
     * Looks for users with any of the $keys and any of the $attributes specified.
     * @param   array   $keys
     * @param   array   $attributes
     * @return  array
     */
    public function findUsersByNotifications( $keys, $attributes )
    {
        $users = $this->find('all', array(
            'conditions'=>array(
                // TODO: Add $keys and $attributes conditions
            )
        ));

        return $users;
    }

    /**
     * Given an array of user id's Return a list of users from the database
     * @param $userIds
     *
     * @return array
     */
    public function listUsersByIds($userIds) {
        return $this->find('all', array(
            'conditions' => array(
                '_id' => array(
                    '$in' => $userIds,
                ),
            ),
        ));
    }

}