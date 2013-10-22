<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\FieldTypeAbstract;

/**
 * Preslog Field Type: Loginfo
 * Handles log information for general logs. Used just to control this items position in the log.
 */
class Loginfo extends FieldTypeAbstract
{

    protected $alias = 'loginfo';
    protected $name = 'Log Information';
    protected $description = 'A block that contains general log information.';

    protected $aggregationDetails = array(
        'hour' => array(
            'fieldName' => 'created',
            '$project' => array(
                'hour' => array('operation' => '$hour', 'data' => '$fields.data.created'),
            ),
            '$group' => array(),
        ),
        'day' => array(
            'fieldName' => 'created',
            '$project' => array(
                'day' => array('operation' => '$dayOfMonth', 'data' => '$fields.data.created'),
                'month' => array('operation' => '$month', 'data' => '$fields.data.created'),
            ),
            '$group' => array()
        ),
        'month' => array(
            'fieldName' => 'created',
            '$project' => array(
                'month' => array('operation' => '$month', 'data' => '$fields.data.created'),
                'year' => array('operation' => '$year', 'data' => '$fields.data.created'),
            ),
            '$group' => array()
        ),
    );

    protected $mongoSchema = array(
        'created'           => array('type' => 'datetime', 'mongoType'=>'mongoDate'),
        'modified'          => array('type' => 'datetime', 'mongoType'=>'mongoDate'),
        'created_user_id'   => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
        'modified_user_id'  => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
        'version'           => array('type' => 'int'),
    );

    protected $mongoClientSchema = array();


    /**
     * Convert LogInfo to Array
     * - Lookup the users for Created and Modified and populate additional field data
     */
    public function afterFind()
    {
        parent::afterFind();

        // Collate IDs for IN() search
        $userList = array();
        $userList[] = (!empty($this->data['data']['created_user_id']) ? $this->data['data']['created_user_id'] : '');
        $userList[] = (!empty($this->data['data']['created_user_id']) ? $this->data['data']['modified_user_id'] : '');

        // User Model and search
        $userModel = \ClassRegistry::init('User');
        $users = $userModel->find('all', array(
            'conditions'=>array(
                '_id'=>array('$in'=>$userList),
            ),
            'fields'=>array(
                '_id',
                'firstName',
                'lastName'
            ),
        ));

        // Convert to lookup
        $userLookup = array();
        foreach ($users as $user)
        {
            $userLookup[ $user['User']['_id'] ] = $user['User'];
        }

        // Apply users
        $this->data['data']['created_user'] = (isset($userLookup[ $this->data['data']['created_user_id'] ])
            ? $userLookup[ $this->data['data']['created_user_id'] ]
            : array()
        );
        $this->data['data']['modified_user'] = (isset($userLookup[ $this->data['data']['modified_user_id'] ])
            ? $userLookup[ $this->data['data']['modified_user_id'] ]
            : array()
        );

    }


    /**
     * Before save
     * - Update the Modified user with the current user
     * - Update the Created user with the current user if it hasn't already been set before.
     */
    public function beforeSave()
    {

    }


    protected function defaultConvertToFields( $label, $data )
    {
        $cUser = (!isset($this->data['data']['created_user']['_id']) ? '' :
            $this->data['data']['created_user']['firstName'] .' '. $this->data['data']['created_user']['lastName']
        );
        $mUser = (!isset($this->data['data']['modified_user']['_id']) ? '' :
            $this->data['data']['modified_user']['firstName'] .' '. $this->data['data']['modified_user']['lastName']
        );

        return array(
            'Created' => date('Y-m-d H:i:s', strtotime($this->data['data']['created'])),
            'Created By' => $cUser,
            'Modified' => date('Y-m-d H:i:s', strtotime($this->data['data']['modified'])),
            'Modified By' => $mUser,
        );
    }
}
