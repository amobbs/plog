<?php

/**
 * Log Model
 */

use Preslog\Logs\Entities\LogEntity;
use Preslog\PreslogParser\PreslogParser;

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');
App::uses('Set', 'Utility');

class Log extends AppModel
{
    public $name = "Log";

    public $actsAs = array('Mongodb.Schema');

    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'               => array('type' => 'string', 'length'=>24, 'primary' => true, 'mongoType'=>'mongoId'),
        'client_id'         => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
        'hrid'              => array('type' => 'integer'),
        'deleted'       => array('type' => 'boolean'),
        'hidden'        => array('type', 'boolean'),
        'fields'        => array('type' => 'subCollection',
            'schema'=> array(
                'field_id'      => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
                'data'          => array('type' => 'array'),
            ),
        ),
        'attributes'    => array('type' => 'subArray',
            'arraySchema'=> array( 'type' => 'string', 'length'=>24, 'mongoType'=>'mongoId' )
        ),
    );


    /**
     * Validate the Log content
     * - Note that log.fields is dynamically allocated, so the log validation needs to be loaded based on the Client and tested per-field.
     * - Attributes are expected to be an array of IDs, so simple validation occurs here.
     * @param   array|null  $options    Options for validation
     * @return  bool                    True on success
     */
    public function validates($options = array())
    {
        // Validate the core model
        if (!$coreResult = parent::validates( $options ))
        {
            return false;
        }

        // Fetch the Client Schema
        $clientModel = ClassRegistry::init('Client');
        $clientEntity = $clientModel->getClientEntityById( $this->data[ $this->name ]['client_id'] );

        // Check the client loaded
        if ( !$clientEntity )
        {
            return false;
        }

        // Load the schema
        $log = new LogEntity;
        $log->setDataSource( $this->getDataSource() );
        $log->setClientEntity($clientEntity);

        // Interpret the log data to be saved
        $log->fromArray( $this->data[ $this->name ] );


        // Validate the data, using $this->validator() for errors
        $errors = $log->validates();
        foreach ($errors as $field=>$error)
        {
            // Invalidate individual errors
            foreach ($error as $line)
            {
                $this->validator()->invalidate($field, $line);
            }
        }

        // Return the validation result
        return (sizeof($this->validator()->errors()) < 1);
    }


    /**
     * Before Save (inverse of After Find)
     * - Process the Schema objects here before passing the data to the DB abstract.
     * - Remove any fields the user does not have permission to modify
     * @param   array|null  $options        Options array
     * @return  bool
     */
    public function beforeSave($options = array())
    {
        // Process any AppModel beforeSave tasks
        if (!$ok = parent::beforeSave($options))
        {
            return $ok;
        }

        // Client ID must be present or we'll have some serious problems
        if (!isset( $this->data[ $this->name ]['client_id'] ))
        {
            trigger_error('client_id must be present to save a log. Log could not be saved.', E_USER_WARNING);
            return false;
        }

        // Load client model
        $clientModel = ClassRegistry::init('Client');

        // Fetch the client
        if (!$client = $clientModel->getClientEntityById( $this->data[ $this->name ]['client_id'] ))
        {
            trigger_error('Given client_id could not be loaded. Log could not be saved.', E_USER_WARNING);
            return false;
        }

        // Establish the entity
        $log = new LogEntity;
        $log->setDataSource( $this->getDataSource() );
        $log->setClientEntity($client);

        // Interpret the log data to be saved
        $log->fromArray( $this->data[ $this->name ] );

        // Fetch the original log data from the DB, where available
        if ( isset($this->data[ $this->name ]['_id']) && !empty($this->data[ $this->name ]['_id']) )
        {
            // Fetch original log
            $sourceLogData = $this->find('first', array('conditions'=>array(
                '_id'=>$this->data[ $this->name ]['_id'],
            )));

            // Load source log as entity
            $sourceLog = new LogEntity;
            $sourceLog->setDataSource( $this->getDataSource() );
            $sourceLog->setClientEntity($client);
            $sourceLog->fromArray($sourceLogData[ $this->name ]);

            // Perform overwrite of readonly fields
            $sourceLog->overwiteWithChanges( $log );

            // Swap entities. We're done with the original.
            $log = $sourceLog;
        }

        // Updated required fields
        $log->beforeSave();

        // Save log changes
        $this->data['Log'] = $log->toDocument();

        return true;
    }


    /**
     * After Find (inverse of Before Save)
     * - Process the Schema objects specific to this log.client_id in log.fields
     * - Remove fields this user does not have permission to see
     * @param mixed $results
     * @param bool $primary
     * @return mixed
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

        // Load client Model
        $clientModel = ClassRegistry::init('Client');

        // Do not try to do the next step is the client_id doesn't exist
        foreach ($results as &$result)
        {
            // Don't try it if the client_id isn't in the resultset
            // This might be omitted due to the 'fields' list of the find options
            if ( !isset( $result[ $this->name ]['client_id'] ))
            {
                continue;
            }

            // Get the client object instance for this log
            if (!$client = $clientModel->getClientEntityById( $result[ $this->name ]['client_id'] ))
            {
                continue;
            }

            // Establish the entity
            $log = new LogEntity;
            $log->setDataSource( $this->getDataSource() );
            $log->setClientEntity($client);

            // Populate
            $log->fromDocument($result[ $this->name ]);

            // After Find tasks
            $log->afterFind();

            // Put results to array of data
            $result[ $this->name ] = $log->toArray();
        }

        return $results;
    }

    /**
     * After Save Callback
     *
     * @param bool $created
     * @param array $options
     */
    public function afterSave($created, $options = array())
    {
        /**
         * @TODO Need to uncomment this once you get the saving of logs done
         */
        //$this->sendOutNotifications($created);
    }


    /**
     * Fetch a list of field types from the config
     */
    public function getFieldTypes()
    {
        // Fetch list of field types
        $fieldsList = Configure::read('Preslog.Fields');
        $fieldMap = array();

        // Get special options for these field types
        foreach ($fieldsList as $key=>$field)
        {
            $fieldObj = $field->getProperties();
            $fieldMap[ $fieldObj['alias'] ] = $fieldObj;
        }

        return $fieldMap;
    }


    /**
     * Fetch the log by the HRID
     * @param   int     $hrid       Human readable ID
     * @return  array               Log
     */
    public function findByHrid( $hrid )
    {
        // If the HRID matches the format ABC_123, add the # after the underscore.
        if (preg_match('/([a-zA-Z]+)_([0-9]+)/', (string) $hrid, $matches))
        {
            $hrid = $matches[1] .'_#'.$matches[2];
        }

        // Find log
        $logs = $this->findByQuery('id="'.$hrid.'"', true);

        // False if no log
        if (!sizeof($logs))
        {
            return false;
        }

        return $logs[0];
    }

    /**
     * fetch a list of logs based on mongo find
     *
     * @param $query
     * @param array $clients        - list of client details to be used to match field names to their id's if TRUE is passed in all clients are used.
     * @param string $orderBy
     * @param int $start            - log id to return from
     * @param int $limit            - how many logs top return
     * @param array $fieldDetails   - array('clientIds', 'dataFieldName') list of field ids from each client and the name of the value we want to sort on from the found field.
     * @param bool $orderAsc        - should the logs be returned in an ascending order
     *
     * @throws Exception
     * @return mixed
     */
    public function findByQuery($query, $clients = array(), $orderBy = '', $start = 0, $limit = 10, $fieldDetails = array(), $orderAsc = true) {
        if (empty($query)) {
            return array(
                'ok' => 1,
                'data' => array(),
            );
        }

        //double check that the called of this function actually wants to check against all clients. (cron jobs are not logged in but want to check all clients
        if ($clients === true)
        {
            $clientModel = ClassRegistry::init('Client');
            $clientObjs = $clientModel->find('all');
            $clients = array();
            foreach($clientObjs as $c)
            {
                $clients[] = $c['Client'];
            }

        }

        //convert string from jql to mongo array
        $parser = new PreslogParser();
        $parser->setSqlFromJql($query);
        $errors = $parser->getErrors();
        if ( sizeof($errors) == 0)
        {
            $errors = $parser->validate($clients);
        }

        if ( sizeof($errors) > 0)
        {
            return array(
                'ok' => 0,
                'errors' => $errors,
            );
        }

        $match = $parser->parse($clients);

        //initial match to find records we want
        $criteria[] = array(
            '$match' => $match,
        );

        //are we sorting the results?
        if (!empty($orderBy)) {
            //used later to rewind the log back together and add the extra 'sort' field so we can sort
            $group = array(
                //list all the log fields since there is no 'select *'
                '_id' => '$_id',
                'hrid' => array('$first' => '$hrid'),
                'client_id' => array('$first' => '$client_id'),
                'fields' => array('$push' => '$fields'),
                'attributes' => array('$first' => '$attributes'),
                'deleted' => array('$first' => '$deleted'),
                'created' => array('$first' => '$created'),
                'modified' => array('$first' => '$modified'),
            );



            //split all the fields up so we can order based on sub field
            $criteria[] = array(
                '$unwind' => '$fields',
            );

            $clientModel = ClassRegistry::init('Client');

            $fieldIds = array();
            $orderByDataFieldName = '';
            foreach($clients as $clientDetails) {
                $clientEntity = $clientModel->getClientEntityById((string)$clientDetails['_id']);

                $clientField = $clientEntity->getFieldTypeByName( strtolower($orderBy) );
                $clientFieldSettings = $clientField->getFieldSettings();
                $fieldIds[] = array(
                    '$eq' => array(
                        '$fields.field_id',
                        new MongoId($clientFieldSettings['_id']),
                    ),
                );

                if (strtolower($orderBy) == 'created' || strtolower($orderBy) == 'modified' || strtolower($orderBy) == 'version')
                {
                    $orderByDataFieldName = strtolower($orderBy);
                }
                else
                {
                    $schemaKeys = array_keys( $clientField->getMongoSchema() );
                    $orderByDataFieldName = $schemaKeys[0];
                }
            }

            //extra field so we can perform the sort
            $group['sort'] = array(
                '$max' => array(
                    //the sort field should just be the field we are ordering by for each client this user has access to
                    '$cond' => array(
                        array('$or' => $fieldIds),
                        '$fields.data',
                        null,
                    ),
                ),
            );

            $criteria[] = array(
                '$group' => $group
            );

            $orderDirection = -1;
            if ($orderAsc) {
                $orderDirection = 1;
            }

            //do the sort
            $criteria[] = array(
                '$sort' => array(
                    'sort.' . $orderByDataFieldName => $orderDirection,
                )
            );
        }

        //offset for pagination
        if ($start >= 0)
        {
            $criteria[] = array(
                '$skip' => (int)$start,
            );
        }

        //limit for pagination
        if ($limit > 0)
        {
            $criteria[] = array(
                '$limit' => (int)$limit,
            );
        }

        //actually do the query and return result
        $mongo = $this->getMongoDb();
        $data = $mongo->selectCollection('logs')->aggregate($criteria);

        if ($data['ok'] == 0) {
            return array(
                'ok' => 0,
                'errors' => array($data['errmsg']),
            );
        }

        //pass into cake format (for afterFind)
        $logs = array();
        foreach($data['result'] as $log) {
            $this->getDataSource()->convertToArray($log, $this->mongoSchema);
            $logs[] = array(
                'Log' => $log,
            );
        }

        return $this->_filterResults( $logs );
    }

    public function countByQuery($query, $clients) {
        //convert string from jql to mongo array
        $parser = new PreslogParser();
        $parser->setSqlFromJql($query);
        $errors = $parser->getErrors();
        if ( sizeof($errors) == 0 )
        {
            $errors = $parser->validate($clients);
        }

        if ( sizeof($errors) > 0)
        {
            return array(
                'ok' => 0,
                'errors' => $errors,
            );
        }

        $match = $parser->parse($clients);

        return $this->find('count', array(
            'conditions' => $match,
        ));
    }

    public function findAggregate($query, $clients, $mongoPipeLine = array(), $fields = array()) {
        if (empty($query)) {
            return array(
                'result' => array(),
                'ok' => 1,
            );
        }

        //convert string from jql to mongo array
        $parser = new PreslogParser();
        $parser->setSqlFromJql($query);
        $errors = $parser->getErrors();
        if ( sizeof($errors) == 0 )
        {
            $errors = $parser->validate($clients);
        }

        if ( sizeof($errors) > 0)
        {
            return array(
                'ok' => 0,
                'errors' => $errors,
            );
        }

        $match = $parser->parse($clients);

        //initial match to get the set we are working on
        $criteria = array(
            array('$match' => $match),
        );

        //get all the field ids in one array
        $fieldIds = array();
        $fieldNames = $parser->getFieldList();
        foreach($fields as $name => $ids) {
            $fieldIds = array_merge($fieldIds, $ids);
        }

        //match only the fields we want
        if (sizeOf($fieldIds) > 0) {
            //separate all the fields out so we can get the data we need
            $criteria[] = array(
                '$unwind' => '$fields',
            );


            $criteria[] = array(
                '$match' => array(
                    'fields.field_id' => array(
                        '$in' => $fieldIds,
                    ),
                ),
            );
        }

        //condense to axis we need
        $group = array(
            '$group' => array(
                '_id' => array('_id' => '$_id'),
            ),
        );

        //create the group by conditions for each axis we want to show
        foreach($mongoPipeLine as $axisName => $axisFields) {
            if (empty($axisFields)) {
                continue;
            }

            $axisFieldIds = array();

            //only return the fields we are searching against
            foreach($axisFields as $fieldName => $value) {
                if (isset($fields[$fieldName]))
                {
                    foreach($fields[$fieldName] as $id) {
                        $axisFieldIds[] = array(
                            '$eq' => array(
                                '$fields.field_id',
                                $id,
                            ),
                        );
                    }
                }

                if (isset($fields['loginfo']) && ($fieldName == 'created' || $fieldName == 'modified'))
                {
                    foreach($fields['loginfo'] as $id) {
                        $axisFieldIds[] = array(
                            '$eq' => array(
                                '$fields.field_id',
                                $id,
                            ),
                        );
                    }
                }
            }

            //format depending on what we are aggregating against
            if (empty($axisFieldIds)) {
                //this is a bit stupid but since there are no id's for this just grab the first record.
                //there should only be one field in the axis so grab the first one
                $keys = array_keys($axisFields);
                $field = $axisFields[$keys[0]];
                $x = array(
                    '$first' => '$' . $field['dataLocation'],
                );
            } else {

                //continue to make sure we only get the fields we want to check against.
                $x = array(
                    '$max' => array(
                        '$cond' => array(
                            array(
                                '$or' => $axisFieldIds,
                            ),
                            '$fields.data',
                            null,
                        ),
                    ),
                );
            }

            $group['$group'][$axisName] = $x;
        }

        $criteria[] = $group;

        //perform the group functions to calculate data needed (eg: sum, count)
        $group = array(
            '$group' => array(
                '_id' => array(),
            ),
        );
        //get the sort order at same time as grouping
        $sort = array(
            '$sort' => array(),
        );

        //project to make it easier to work with
        $project = array(
            '$project' => array(),
        );

        foreach($mongoPipeLine as $axisName => $axis) {
            foreach($axis as $detailName => $detail) {
                //project the data so each axis is at the top level
                $project['$project'][$axisName] = '$' . $axisName;

                //format group depending if we are performing an aggregate function on this field
                if ($detail['aggregate']) {
                    $aggregateOn = '';
                    if ($detailName == 'count') {
                        $aggregateOn = $detail['dataLocation'];
                    } else {
                        $aggregateOn = '$' . $axisName . '.' . $detail['dataLocation'];
                    }

                    $group['$group'][$axisName] = array(
                        $detail['groupBy'] => $aggregateOn,
                    );
                } else {
                    //anything we are not aggregating on just group on it (in _id).
                    $group['$group']['_id'][$detailName] = array();
                    if (empty($detail['groupBy'])) {
                        $group['$group']['_id'][$detailName] = '$' . $axisName;
                        $sort['$sort']['_id.'  . $detailName] = 1;
                    } else {
                        foreach($detail['groupBy'] as $groupFunctionName => $groupFunction) {
                            $group['$group']['_id'][$detailName][$groupFunctionName] = array($groupFunction => '$' . $axisName . '.' . $detail['dataLocation']);

                            //we can only sort on non aggregates
                            $sort['$sort']['_id.' . $detailName . '.' . $groupFunctionName] = 1;
                        }
                    }

                    $project['$project'][$axisName] = '$_id.' . $detailName;
                }

            }
        }

        $criteria[] = $group;
        if ( !empty($sort['$sort']) )
        {
            $criteria[] = $sort;
        }
        $criteria[] = $project;

        $mongo = $this->getMongoDb();
        $data = $mongo->selectCollection('logs')->aggregate($criteria);

        //put err message into same format all other errors come out
        if ( isset($data['errmsg']) )
        {
            $data['errors'] = array('Database Error: ' . $data['errmsg']);
        }

        return $data;
    }


    /**
     * Fetch options fields for this client.
     * - fields
     * - Attributes hierarchy
     * @param   $client_id
     * @return  array
     */
    public function getOptionsByClientId( $client_id )
    {
        // Load client
        $clientModel = ClassRegistry::init('Client');
        $clientEntity = $clientModel->getClientEntityById( $client_id );

        // Get the settings limited field list
        $opt = $clientEntity->getOptions();

        // Save only the items relevant to this action
        $options = array(
            'fields' => $opt['fields'],
            'attributes' => $opt['attributes'],
        );

        return $options;
    }

    public function buildMatch($match, $clients) {
        //convert any named fields to the client mongo id's
        $newMatch = array();
        foreach($match as $key => $value) {
            //we only need to replace conditions that relate to subDocuments (not on the top level of the schema)
            if ($key == '$or' || $key == '$and') {
                $newMatch[]['$or'] = $this->buildMatch($value, $clients);
            } else if (in_array($key, array_keys($this->mongoSchema))) {
                $newMatch[$key] = $value;
            } else {
                //find all instances of this field for each client
                $fieldIds = array();
                $dataField = 'seconds';
                foreach($clients as $client) {
                    foreach($client['fields'] as $field) {
                        if ($key == 'loginfo' &&
                            ($field['name'] == 'created' || $field['name'] == 'modified')) {
                            $dataField = $field['name'];
                            $fieldIds[] = $field['_id'];
                        }
                        if ($field['name'] == $key) {
                            $fieldIds[] = $field['_id'];
                        }
                    }
                }

                if (!isset($newMatch['$or'])) {
                    $newMatch['$or'] = array();
                }

                $newMatch['$or'][] = array(
                    'fields.data.' . $dataField => $value,
                    'fields.field_id' => array(
                        '$in' => $fieldIds,
                    ),
                );

            }
        }

        return $newMatch;


    }

    /**
     * Send out email/sms notifications based on what the users are subscribed to
     *
     * @param $created
     */
    public function sendOutNotifications($created)
    {
        // Only if it's a new record
        if (! $created) {
            return;
        }

        // @TODO This should be using the modal data, but is using a test log for right now, should be removed in the future
        //$data = $this->data[$this->name];
        $log = $this->findByHrid(6997);
        $log = $log['Log'];


        // Need attributes to do anything else
        if (empty($log['attributes'])) {
            return;
        }

        // grab out attributes
        $attributes = array();
        foreach ($log['attributes'] as &$attr) {
            $attributes[] = (string) $attr;
        }

        /**
         * @var $User User
         */
        $User = ClassRegistry::init('User');

        /**
         * @var $Client Client
         */
        $Client = ClassRegistry::init('Client');

        $logEntity = new LogEntity;
        $logEntity->setDataSource($this->getDataSource());
        $logEntity->setClientEntity($Client->getClientEntityById($log['client_id']));

        $logEntity->fromDocument($log);
        $log = $logEntity->toDisplay();


        $users = $User->find('all', array('conditions' => array(
            'notifications.clients.attributes' => array('$in' => $attributes)
        )));

        foreach ($users as $user) {
            $methods = $user['User']['notifications']['methods'];
            foreach ($user['User']['notifications']['clients'] as $client) {
                // Check to see if attributes match
                if (! array_intersect($client['attributes'], $attributes)) {
                    continue;
                }

                // Check severity level of log to severity level the user is interested in
                /**
                 * @TODO I have the severity level for the person, but not the proper severity level to check for
                 * the log.  This needs to be formatted properly in the log so that it can be checked against what
                 * the user wants to be notified about
                 *
                 * $log['Severity'] should be either 'severity-one', 'severity-two', or 'other'
                 */

                // This is for testing purposes so the code below works!!!
                $log['Severity'] = 'severity-two';
                if (property_exists($client['types'], $log['Severity']) && $client['types']->{$log['Severity']}) {
                    /**
                     * If the person wants email and it's either severity-one or severity-two, send them the email
                     */
                    if ($methods['email'] && in_array($log['Severity'], array('severity-one', 'severity-two'))) {
                        $this->sendNotificationEmail($log, $user);
                    }

                    /**
                     * If the person wants SMS's and it's a severity-one, send them the sms
                     */
                    if ($methods['sms'] && 'severity-one' === $log['Severity']) {
                        $this->sendNotificationSMS($log, $user);
                    }
                }
            }
        }
    }

    /**
     * @TODO This needs to send out an email to the user to be notified of a log
     *
     * @param $log
     * @param $user
     */
    protected function sendNotificationEmail($log, $user) {
        debug('send email');
    }

    /**
     * @TODO This needs to send out an SMS to the user to be notified of a log
     * @param $log
     * @param $user
     */
    protected function sendNotificationSMS($log, $user) {
        debug('send sms');
    }

}