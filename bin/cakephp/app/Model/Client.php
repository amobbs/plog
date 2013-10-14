<?php

/**
 * Client Model
 */

App::uses('AppModel', 'Model');
App::uses('Client', 'Model');

class Client extends AppModel
{
    public $name = "Client";

    public $actsAs = array('Mongodb.Schema');

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
            'mongoType' => 'MongoDate'
        ),
        'format' => array(
            'type' => 'subCollection',
            'schema' => array(
                '_id' => array(
                    'type' => 'string',
                    'length' => 24,
                    'mongoType' => 'mongoId'
                ),
                'order' => array('type' => 'int'),
                'type' => array('type' => 'string'),
                'name' => array(
                    'type' => 'string',
                    'length' => 255
                ),
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
                'name' => array('type' => 'string'),
                'deleted' => array('type' => 'boolean'),
                'children' => array(
                    'type' => 'subCollection',
                    'schema' => array(
                        '_id' => array(
                            'type' => 'string',
                            'length' => 24,
                            'mongoType' => 'mongoId'
                        ),
                        'name' => array('type' => 'string'),
                        'deleted' => array('type' => 'boolean'),
                        'children' => array(
                            'type' => 'subCollection',
                            'schema' => array(
                                '_id' => array(
                                    'type' => 'string',
                                    'length' => 24,
                                    'mongoType' => 'mongoId'
                                ),
                                'name' => array('type' => 'string'),
                                'deleted' => array('type' => 'boolean'),
                                'children' => array('type' => 'array')
                            )
                        )
                    )
                )
            )
        ),
        'created' => array(
            'type' => 'datetime',
            'mongoType' => 'mongoDate'
        ),
        'modified' => array(
            'type' => 'datetime',
            'mongoType' => 'mongoDate'
        ),
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
                'rule'=>array('checkCollision'),
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
                'rule'=>array('checkCollision'),
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
                'rule'=>array('checkCollision'),
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
        'activationDate'=>array(
            'date'=>array(
                'rule'=>array('date', 'ymd'),
                'message'=>'Must be a valid date format  of YYYY-MM-DD',
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
        // Find a collision
        $found = $this->find('first', array(
            'conditions'=>array(
                $check
            )
        ));

        // Return true if no collision
        return empty($found);
    }


    /**
     * convert any id's into mongo id's and add any missing id's
     * @param array $options
     *
     * @return bool|void
     */
    public function beforeSave($options = array()) {
        //TODO clean up this horrible code
        $client = $this->data['Client'];
        if (!($client['_id'] instanceof mongoId)) {
            if ($client['_id'] == null || (isset($client['newGroup']) && $client['newGroup'])) {
                $client['_id'] = new mongoId();
            } else {
                $client['_id'] = new mongoId($client['_id']);
            }
        }


        $groups = [];
        //check all the attributes
        foreach ($client['attributes'] as $group) {
            if ($group['_id'] == null || (isset($group['newGroup']) && $group['newChild']) || strlen($group['_id']) != 24) {
                $group['_id'] = new mongoId();
            } else {
                $group['_id'] = new mongoId($group['_id']);
            }
            $children = [];
            foreach($group['children'] as $child) {
                if (!($child['_id'] instanceof mongoId)) {
                    if ($child['_id'] == null || (isset($child['newGroup']) && $child['newChild']) || strlen($child['_id']) != 24) {
                        $child['_id'] = new mongoId();
                    } else {
                        $child['_id'] = new mongoId($child['_id']);
                    }
                }
                $subChildren = [];
                foreach($child['children'] as $subChild) {
                    if (!($subChild['_id'] instanceof mongoId)) {
                        if ($subChild['_id'] == null || (isset($subChild['newGroup']) && $subChild['newChild']) || strlen($subChild['_id']) != 24) {
                            $subChild['_id'] = new mongoId();
                        } else {
                            $subChild['_id'] = new mongoId($subChild['_id']);
                        }
                    }
                    $subChildren[] = $subChild;
                }
                $child['children'] = $subChildren;
                $children[] = $child;
            }
            $group['children'] = $children;
            $groups[] = $group;
        }
        $client['attributes'] = $groups;
        $this->data['Client'] = $client;
    }

    /**
     * Fetch the notifications for clients
     * If UserID is specified
     * @param       string      UserId
     * @return      array       Notification structure
     */
    public function getNotificationsList( $userId=null )
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
            if (false)
            {
                $conditions['_id'] = $user['client_id'];
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
     * Fetch options fields for this client.
     * - Format
     * - Attributes hierarchy
     * @param   $client_id
     * @return  array
     */
    public function getOptionsByClientId( $client_id )
    {
        $options = $this->find('all', array(
            'conditions'=>array(
                '_id'=>$client_id
            ),
            'fields'=>array(
                'format',
                'attributes'
            ),
        ));

        return $options;
    }

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
            'fields'        => $client['Client']['format'],
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
        return '/assets/clients/'.(string) $client['_id'].'/logo.png';
    }

}