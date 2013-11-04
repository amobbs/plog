<?php

namespace Preslog\Logs\FieldTypes;

/**
 * Preslog Field Type: Loginfo
 * Handles log information for general logs. Used just to control this items position in the log.
 */
class Loginfo extends FieldTypeAbstract
{

    protected $alias = 'loginfo';
    protected $name = 'Log Information';
    protected $description = 'A block that contains general log information.';
    protected $queryFieldType = '';

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
        'version'           => array('type' => 'integer'),
    );

    protected $mongoClientSchema = array();


    /**
     * Check the field name matches static preset names
     * @param   string  $name   Field name to check
     * @return  bool            True is name is a match
     */
    public function isName( $name )
    {
        // LogInfo has 3 PRESET names you can search on...
        $names = array('created', 'modified', 'version');
        return (in_array( $name, $names ));
    }


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
        $authObject = \PreslogAuthComponent::getInstance();

        // If no Created date, set it.
        if (!isset($this->data['data']['created']))
        {
            $this->data['data']['created'] = date('r', time());
        }

        // If no Created user, set it.
        if (!isset($this->data['data']['created_user_id']))
        {
            $this->data['data']['created_user_id'] = $authObject->user('_id');
        }

        // Update Modified Date
        $this->data['data']['modified'] = date('r', time());

        // Update Modified Time
        $this->data['data']['modified_user_id'] = $authObject->user('_id');

        // Establish version if it doesn't exist
        if (!isset($this->data['data']['version']))
        {
            $this->data['data']['version'] = 0;
        }

        // Version increment
        $this->data['data']['version'] += 1;

        // Execute parent - convert schema
        parent::beforeSave();
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


    /**
     * No validation required
     * @return array|bool
     */
    public function validates()
    {
        return array();
    }
}
