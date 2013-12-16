<?php

/**
 * Client Model
 */

use Preslog\Logs\Entities\ClientEntity;

App::uses('AppModel', 'Model');
App::uses('Client', 'Model');


class Client extends AppModel
{
    public $name = "Client";


    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id' => array(
            'type' => 'string',
            'length' => 24,
            'primary' => true,
            'mongoType' => 'mongoId'
        ),
        'name' => array(
            'type' => 'string',
            'length' => 255
        ),
        'logoUrl' => array(
            'type' => 'string',
            'length' => 255
        ),
        'shortName' => array(
            'type' => 'string',
            'length' => 4
        ),
        'contact' => array(
            'type' => 'string',
            'length' => 255
        ),
        'logPrefix' => array(
            'type' => 'string',
            'length' => 6
        ),
        'activationDate' => array(
            'type' => 'datetime',
            'mongoType' => 'mongoDate'
        ),
        'benchmark' => array(
            'type' => 'float',
        ),
        'fields' => array(
            'type' => 'subCollection',
            'schema' => array(
                '_id' => array(
                    'type' => 'string',
                    'length' => 24,
                    'mongoType' => 'mongoId'
                ),
                'order' => array('type' => 'int'),
                'deleted' => array('type' => 'boolean'),
                'required' => array('type' => 'boolean'),
                'type' => array('type' => 'string', 'length'=>32),
                'name' => array('type' => 'string', 'length' => 64),
                'label' => array('type' => 'string', 'length'=>64),
                'data' => array('type' => 'array')
            )
        ),
        'attributes' => array(
            'type' => 'subCollection',
            'schema' => array(
                '_id' => array(
                    'type' => 'string',
                    'length' => 24,
                    'mongoType' => 'mongoId'
                ),
                'name' => array('type' => 'string', 'length'=>64),
                'label' => array('type' => 'string', 'length'=>64),
                'deleted' => array('type' => 'boolean'),
                'network' => array('type' => 'boolean'),
                'children' => array(
                    'type' => 'subCollection',
                    'schema' => array(
                        '_id' => array(
                            'type' => 'string',
                            'length' => 24,
                            'mongoType' => 'mongoId'
                        ),
                        'name' => array('type' => 'string', 'length'=>64),
                        'live_date' => array(
                            'type' => 'datetime',
                            'mongoType' => 'mongoDate'
                        ),
                        'deleted' => array('type' => 'boolean'),
                        'children' => array(
                            'type' => 'subCollection',
                            'schema' => array(
                                '_id' => array(
                                    'type' => 'string',
                                    'length' => 24,
                                    'mongoType' => 'mongoId'
                                ),
                                'name' => array('type' => 'string', 'length'=>64),
                                'deleted' => array('type' => 'boolean'),
                                'children' => array('type' => 'array')
                            )
                        )
                    )
                )
            )
        ),
        'deleted'=>array( 'type'=>'boolean'),
        'created' => array(
            'type' => 'datetime',
            'mongoType' => 'mongoDate'
        ),
        'modified' => array(
            'type' => 'datetime',
            'mongoType' => 'mongoDate'
        ),
        'logIncrement'=> array(
            'type'=>'integer'
        )
    );


    public $validateClient = array(
        'name'=>array(
            'min-length'=>array(
                'rule'=>array('notEmpty'),
                'message'=>'Must not be empty',
                'required'=>true
            ),
            'max-length'=>array(
                'rule'=>array('maxLength', 255),
                'message'=>'Maximum length is 255 characters',
                'required'=>true,
            ),
            'noCollision'=>array(
                'rule'=>array('checkCollision', 'name'),
                'message'=>'This name is already in use',
                'required'=>true,
            )
        ),
        'shortName'=>array(
            'min-length'=>array(
                'rule'=>array('notEmpty'),
                'message'=>'Must not be empty',
                'required'=>true
            ),
            'max-length'=>array(
                'rule'=>array('maxLength', 6),
                'message'=>'Maximum length is 6 characters',
                'required'=>true,
            ),
            'custom'=>array(
                'rule'=>array('custom', '/^[a-zA-Z]+$/'),
                'message'=>'Must only contain letters',
                'required'=>true
            ),
            'noCollision'=>array(
                'rule'=>array('checkCollision', 'shortName'),
                'message'=>'This short name is already in use',
                'required'=>true,
            )
        ),
        'logPrefix'=>array(
            'min-length'=>array(
                'rule'=>array('notEmpty'),
                'message'=>'Must not be empty',
                'required'=>true
            ),
            'max-length'=>array(
                'rule'=>array('maxLength', 6),
                'message'=>'Maximum length is 6 characters',
                'required'=>true,
            ),
            'custom'=>array(
                'rule'=>array('custom', '/^[a-zA-Z]+$/'),
                'message'=>'Must only contain letters',
                'required'=>true
            ),
            'noCollision'=>array(
                'rule'=>array('checkCollision', 'logPrefix'),
                'message'=>'This log prefix is already in use',
                'required'=>true,
            )
        ),
        'contact'=>array(
            'max-length'=>array(
                'rule'=>array('maxLength', 255),
                'message'=>'Maximum length is 255 characters',
                'required'=>true,
                'allowEmpty'=>true,
            ),
        ),
        'benchmark'=>array(
            'range'=>array(
                'rule'=>array('range', 0, 100),
                'message'=>'Must be a percentage between 0 and 100',
                'required'=>true,
            ),
        ),
        'activationDate'=>array(
            'date'=>array(
                'rule'=>array('validate_datetime_rfc2822', 'activationDate'),
                'message'=>'Must be a valid RFC-2822 date.',
                'required'=>true,
            ),
        )
    );


    /**
     * Custom validation to verify the given $field value isn't in use elsewhere
     * @param   $check
     * @return  bool        true if valid
     */
    public function checkCollision($check)
    {
        $ds = $this->getDataSource();

        // Find a collision
        $count = $this->find('count', array(
            'conditions'=>array(
                $check
            )
        ));

        // Return true if no collision
        return ($count == 0);
    }


    /**
     * convert any id's into mongo id's and add any missing id's
     * @param array $options
     *
     * @return bool|void
     */
    public function beforeSave($options = array()) {

        // Process any AppModel beforeSave tasks
        $ok = parent::beforeSave($options);

        // Return if the initial process fails
        if (!$ok)
        {
            return $ok;
        }

        // Convert to an Array
        // Initialize a client entity
        $client = new ClientEntity();
        $client->setDataSource( $this->getDataSource() );
        $client->setUser( PreslogAuthComponent::getInstance()->user() );
        $client->setFieldTypes( Configure::read('Preslog.Fields') );

        // Load to the entity
        $client->fromArray( $this->data['Client'] );

        // Perform beforeSave tasks
        $client->beforeSave();

        // Prep for save by outputting as a document
        $this->data['Client'] = $client->toDocument();

        return true;
    }


    /**
     * After Find (inverse of before save)
     * - Crawl the Fields and Attribute schema and convert values as needed
     * @param mixed $results
     * @param bool $primary
     * @return mixed|void
     */
    public function afterFind($results, $primary = false)
    {
        // Run traditional afterFind
        $results = parent::afterFind($results, $primary);

        // Check there's data to process
        if ( !sizeof($results) )
        {
            return $results;
        }

        // Do not try to do the next step is the client_id doesn't exist
        foreach ($results as &$result)
        {
            // Don't try it if the client_id isn't in the resultset
            // This might be omitted due to the 'fields' list of the find options
            if ( !isset( $result[ $this->name ]['_id'] ) )
            {
                continue;
            }

            // Initialize a client entity
            $client = new ClientEntity();
            $client->setFieldTypes( Configure::read('Preslog.Fields') );
            $client->setDataSource( $this->getDataSource() );
            $client->setUser( PreslogAuthComponent::getInstance()->user() );

            // Load as a doc
            $client->fromDocument($result[ $this->name ]);

            // Perform after find
            $client->afterFind();

            // Put to an array rather than a doc
            $result[ $this->name ] = $client->toArray();
        }

        return $results;
    }



    /**
     * Fetch the notifications for clients
     * If UserID is specified
     * @param       string      UserId
     * @param       Controller  Authable object
     * @return      array       Notification structure
     */
    public function getNotificationsList( $userId, $authObject )
    {
        $this->User = ClassRegistry::init('User');

        // Basic conditions for search
        $conditions = array('deleted'=>false);

        // If the UserID is present, establish the find conditions
        if ( !empty($userId) )
        {
            // Fetch the user
            $user = $this->User->find('first', array(
                'conditions'=>array(
                    '_id'=>$userId
                ),
                'fields'=>array(
                    'role',
                    'client_id'
                )
            ));

            // Error on nothing to respond with
            if (!sizeof($user))
            {
                return false;
            }

            // Check the permission for single-client. If we get that, we limit on the client_id.
            if ($authObject->isAuthorized('single-client'))
            {
                $conditions['_id'] = $user['User']['client_id'];
            }
        }

        // Fetch the client(s) available
        $clients = $this->find('all', array(
            'conditions'=>$conditions,
            'fields'=>array(
                '_id',
                'name',
                'attributes'
            )
        ));

        // Get the notification types
        $notifyTypes = Configure::read('Preslog.Notifications');
        $types = array();

        foreach ($notifyTypes as $type)
        {
            $types[] = array(
                'name'=>$type->getName(),
                'id'=>$type->getKey(),
            );
        }

        // make the list!
        $ret = array('clients'=>array());
        foreach ($clients as $client)
        {
            // Make the client object
            $client = $client['Client'];
            $client['types'] = $types;

            // collate to array
            $ret['clients'][] = $client;
        }

        return $ret;
    }


    /**
     * Fetch list of clients as options
     * @returns     array
     */
    public function getClientsAsOptions()
    {
        $clientList = array();

        // Get all clients and the fields we care for
        $clients = $this->find('all', array(
            'fields'=>array(
                '_id',
                'name',
                'deleted'
            )
        ));

        // Flatten model
        foreach ($clients as $client)
        {
            // Flatten to client list
            $clientList[] = $client['Client'];
        }

        return $clientList;
    }


    /**
     * Fetch the requested client by their ID
     * @param       string      ClientID
     * @return      array       Client
     */
    public function findById( $id )
    {
        // Fetch all client info
        return $this->find('first', array(
            'conditions'=>array(
                'id'=>$id
            )
        ));
    }


    /**
     * Validate Admin Edit of a Client
     * @return bool
     */
    public function validatesAdminEdit() {

        $rules = $this->validateClient;

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
     * Fetch client by the given $id, returning just the Options used for Edit.
     * @param   ClientID        $id
     * @return  array
     */
    public function getLogOptionsById( $id )
    {
        // Get the client
        $client = $this->findById( $id );

        // Get options fields from client
        $options = array(
            'fields'        => $client['Client']['fields'],
            'attributes'    => $client['Client']['attributes'],
        );

        return $options;
    }


    /**
     * Fetch the logo path for this client
     * @param   array       $client         Client data
     * @return  string
     */
    public function getLogoPath( $client )
    {
        // Use logo if not missing
        if (isset($client['logoUrl']) && !empty($client['logoUrl']))
        {
            $path = $client['logoUrl'];
        }
        else
        {
            // Placeholder URL
            $path = Configure::read('Preslog.Client.logoPlaceholder');

        }

        return $path;
    }


    /**
     * Fetch a Client Entity by the given Client ID
     * @param   string      $client_id      Mongo ID of the client
     * @return  ClientEntity                Client Entity, after initialisation
     */
    public function getClientEntityById( $client_id )
    {
        // Poor-mans cache method
        $clientCache = Configure::read('Preslog.cache.clientEntity');
        if (!is_array($clientCache))
        {
            $clientCache = array();
        }

        // Attempt to load the ClientSchema from cache before calling up a new one.
        if ( isset($clientCache[ $client_id ]))
        {
            return $clientCache[ $client_id ];
        }

        // Prep client object
        $client = new ClientEntity;
        $client->setDataSource( $this->getDataSource() );
        $client->setUser( PreslogAuthComponent::getInstance()->user() );
        $client->setFieldTypes( Configure::read('Preslog.Fields') );

        // Find the client in the DB
        $clientData = $this->find('first', array(
            'conditions'=>array(
                '_id'=> $client_id,
            ),
        ));

        // Catch any errors
        if (empty($clientData))
        {
            trigger_error("Client could not be loaded by ID '{$client_id}''", E_USER_ERROR);
        }

        // Load from doc
        $client->fromDocument( $clientData['Client'] );

        // Write to cache
        $clientCache[ $client_id ] = $client;
        Configure::write('Preslog.cache.clientEntity', $clientCache);

        // Pass back
        return $client;
    }

}