<?php

/**
 * Client Model
 */

App::uses('AppModel', 'Model');

class Client extends AppModel
{
    public $name = "Client";


    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'           => array('type' => 'string', 'length'=>40, 'primary' => true),
        'name'          => array('type' => 'string', 'length'=>255),
        'shortName'     => array('type' => 'string', 'length'=>4),
        'contact'       => array('type' => 'text'),
        'logPrefix'     => array('type' => 'string', 'length'=>4),
        'activationDate'=> array('type' => 'datetime'),
        'format'        => array('type' => null),
        'attributes'    => array('type' => null),
        'created'       => array('type' => 'datetime'),
        'modified'      => array('type' => 'datetime'),
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
     * Limits the Client list to those specified in the Ids array, if set.
     * @param       string      Id
     * @return      array       Notification structure
     */
    public function getNotificationsList( $ids=null )
    {
        // TODO: Make this run off client data

        $notify = array(
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

        return $notify;
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
                'id',
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
     * Get notifications available to this specific user
     * @param   array       User Object
     * @return  array       Notification options
     */
    public function getNotificationOptionsAvailableToUser( $user )
    {
        // TODO
        // if user has "single-client" as a permission, use user[client_id] as a lookup for the client
        // else: fetch all clients that aren't deleted.

        // Return the hierachy
        return array();
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