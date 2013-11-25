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
        '_id' => array(
            'type' => 'string',
            'mongoType' => 'mongoId',
            'length' => 24,
            'primary' => true
        ),
        'firstName' => array(
            'type' => 'string',
            'length' => 255
        ),
        'lastName' => array(
            'type' => 'string',
            'length' => 255
        ),
        'email' => array(
            'type' => 'string',
            'length' => 255
        ),
        'password' => array(
            'type' => 'string',
            'length' => 60
        ),
        'company' => array(
            'type' => 'string',
            'length' => 255
        ),
        'phoneNumber' => array(
            'type' => 'string',
            'length' => 40
        ),
        'password-token' => array(
            'type' => 'string',
            'length' => 32
        ),
        'login-token' => array(
            'type' => 'string',
            'length' => 32
        ),
        'role' => array(
            'type' => 'string',
            'length' => 32
        ),
        'client_id' => array(
            'type' => 'string',
            'mongoType' => 'mongoId',
            'length' => 24
        ),
        'deleted' => array('type' => 'boolean'),
        'notifications' => array(
            'type' => 'subDocument',
            'schema' => array(
                'methods' => array(
                    'type' => 'subDocument',
                    'schema' => array(
                        'sms' => array(
                            'type' => 'boolean',
                            'default' => false,
                        ),
                        'email' => array(
                            'type' => 'boolean',
                            'default' => false
                        )
                    )
                ),
                'clients' => array(
                    'type' => 'subCollection',
                    'schema' => array(
                        'client_id' => array(
                            'type' => 'string',
                            'mongoType' => 'mongoId',
                            'length' => 24
                        ),
                        'attributes' => array(
                            'type' => 'array'
                        ),
                        'types' => array(
                            'type' => 'object'
                        )
                    )
                )
            )
        ),
        'favouriteDashboards' => array('type' => 'array'),
        'created' => array(
            'type' => 'datetime',
            'mongoType' => 'mongoDate'
        ),
        'modified' => array(
            'type' => 'datetime',
            'mongoType' => 'mongoDate'
        ),
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
            ),
            'collision'=>array(
                'rule'=>array('checkCollision'),
                'message'=>'Email address is already in use by another user.'
            ),
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
     * Before Save
     * - Modify password to hash format
     * @param   array   $options
     * @return  bool
     */
    public function beforeSave( $options = array() )
    {
        // Convert password, if set, to bCrypt password
        if (isset($this->data['User']['password']))
        {
            $this->data['User']['password'] = Security::hash($this->data['User']['password'], 'blowfish');;
        }

        return parent::beforeSave( $options );
    }


    /**
     * @param   null|string     $permission     Permission we're checking for
     * @param   null|string     $userRole       Role we're checking for the permission
     * @param   null|array      $route          Information about the current route. Supplied from Controller::isAuthorized
     * @return  bool            False if not permitted
     */
    public function isAuthorized( $permission=null, $userRole=null, $route=null )
    {
        // Load the ACL configuration
        $config = Configure::read('auth-acl');

        // If no role, set as anonymous
        $userRole = ($userRole ? $userRole : $config['anonymousRole']);

        // If we're not checking a SPECIFIC permission on the user, check the controller/action path
        if ( $permission ) {
            return ( isset($config['roles'][ $userRole ]['permissions']) && in_array($permission, $config['roles'][ $userRole ]['permissions']) );
        }

        // Check the route
        elseif (is_array($route)) {

            // Scan all rules
            foreach ($config['routes'] as $rule)
            {
                // Match by controller or wildcard
                if ($rule['controller'] != $route['controller'] && $rule['controller'] != '*')
                    continue;

                // Match by action or wildcard
                if ($rule['action'] != $route['action'] && $rule['action'] != '*')
                    continue;

                // Match permission required to the role's available permissions
                if ( sizeof( array_intersect($rule['permissions'], $config['roles'][$userRole]['permissions']) ) )
                    return true;
            }
        }

        // Failed to find
        return false;
    }


    /**
     * Fetch all available roles and return a simple array
     * @param   string      $requiredRole       Role that must be included.
     * @return  array
     */
    public function getAvailableRoles( $requiredRole=null )
    {
        $rawRoles = Configure::read('auth-acl.roles');

        $roles = array();

        foreach ($rawRoles as $roleKey=>$role)
        {
            $item['hidden'] = (isset($role['hidden']) ? $role['hidden'] : false);
            $item['name'] = $role['name'];
            $item['id'] = $roleKey;

            if ($item['hidden'] && $requiredRole != $item['id'])
            {
                continue;
            }

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
                '_id'=>$id
            ),
            'fields'=>array(
                '_id',
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
        $this->Client->getNotificationsList( array(), $this );

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
     * Validate for collision on the email address
     * @param   $check      Field to check
     * @return  bool        True if valid
     */
    public function checkCollision( $check )
    {
        // Find by email
        $conditions = array(
            'email'=>$check['email'],
        );

        // Not this user
        if (isset($this->data['User']['_id']) && !empty($this->data['User']['_id']))
        {
            $conditions['_id'] = array('$ne' => $this->data['User']['_id']);
        }

        $count = $this->find('count', array('conditions'=>$conditions));

        return ($count < 1);
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


    /**
     * Fetch a list of clients available to this user.
     * Requires the client_id and role fields for the user.
     * @param   array   $user       The user
     * @return  array               Client list
     */
    public function listAvailableClientsForUser( $user ) {

        // Get list of clients, where not deleted
        $conditions = array('deleted'=>false);

        // Is this ocked to single client? If so, use the client_id.
        if ( $this->isAuthorized('single-client', $user['role']) )
        {
            $conditions['_id'] = $user['client_id'];
        }

        // Fetch!
        $clients = $this->Client->find('all', array(
            'conditions'=>$conditions,
            'fields'=>array(
                '_id',
                'logoUrl',
                'name',
            )
        ));

        // Some crunching
        if (sizeof($clients))
        {
            // Flatten/populate client list
            foreach ($clients as $k=>$client)
            {
                $client['Client']['logo'] = $this->Client->getLogoPath($client['Client']);
                $clients[$k] = $client['Client'];
            }
        }
        else
        {
            // Default is an empty array
            $clients = array();
        }

        return $clients;
    }

}