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
        '_id'           => array('type' => 'string', 'length'=>24, 'primary' => true, 'mongoType'=>'MongoId'),
        'name'          => array('type' => 'string', 'length'=>255),
        'shortName'     => array('type' => 'string', 'length'=>4),
        'contact'       => array('type' => 'string', 'length'=>255),
        'logPrefix'     => array('type' => 'string', 'length'=>6),
        'activationDate'=> array('type' => 'datetime', 'mongoType'=>'MongoDate'),
        'format'        => array(
            '_id'           => array('type' => 'string', 'length'=>24, 'mongoType'=>'MongoId'),
            'order'         => array('type'=>'int'),
            'name'          => array('type'=>'string', 'length'=>255),
            'data'          => array(null),
        ),
        'attributes'    => array(null),
        'created'       => array('type' => 'datetime', 'mongoType'=>'MongoDate'),
        'modified'      => array('type' => 'datetime', 'mongoType'=>'MongoDate'),
    );


    /**
     * convert any id's into mongo id's and add any missing id's
     * @param array $options
     *
     * @return bool|void
     */
    public function beforeSave($options = array()) {
        //TODO clean up this horrible code
        $client = $this->data['Client'];
        if (!($client['_id'] instanceof MongoId)) {
            if ($client['_id'] == null || (isset($client['newGroup']) && $client['newGroup'])) {
                $client['_id'] = new MongoId();
            } else {
                $client['_id'] = new MongoId($client['_id']);
            }
        }


        $groups = [];
        //check all the attributes
        foreach ($client['attributes'] as $group) {
            if ($group['_id'] == null || (isset($group['newGroup']) && $group['newChild']) || strlen($group['_id']) != 24) {
                $group['_id'] = new MongoId();
            } else {
                $group['_id'] = new MongoId($group['_id']);
            }
            $children = [];
            foreach($group['children'] as $child) {
                if (!($child['_id'] instanceof MongoId)) {
                    if ($child['_id'] == null || (isset($child['newGroup']) && $child['newChild']) || strlen($child['_id']) != 24) {
                        $child['_id'] = new MongoId();
                    } else {
                        $child['_id'] = new MongoId($child['_id']);
                    }
                }
                $subChildren = [];
                foreach($child['children'] as $subChild) {
                    if (!($subChild['_id'] instanceof MongoId)) {
                        if ($subChild['_id'] == null || (isset($subChild['newGroup']) && $subChild['newChild']) || strlen($subChild['_id']) != 24) {
                            $subChild['_id'] = new MongoId();
                        } else {
                            $subChild['_id'] = new MongoId($subChild['_id']);
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
        return true;
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