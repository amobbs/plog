<?php

/**
 * Log Model
 */

use Preslog\JqlParser\JqlParser;
use Preslog\Logs\LogHelper;

App::uses('AppModel', 'Model');

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
        'fields'        => array('type' => 'subCollection',
            'schema'=> array(
                'field_id'      => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
                'data'          => array('type' => 'array'),
            ),
        ),
        'attributes'    => array('type' => 'array'),
        'version'       => array('type' => 'integer'),
        'created'       => array('type' => 'datetime', 'mongoType'=>'mongoDate'),
        'modified'      => array('type' => 'datetime', 'mongoType'=>'mongoDate'),
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
        $coreResult = parent::validates( $options );

        // Fetch the Client Schema
        $clientModel = ClassRegistry::init('Client');
        $client = $clientModel->find('first', array(
            'conditions'=>array(
                '_id' => $this->data['client_id'],
            )
        ));

        // Check the client loaded
        if ( !sizeof($client) )
        {
            return false;
        }

        // Load the schema
        $logHelper = new LogHelper;
        $logHelper->loadSchema($client);

        // Validate the data, using $this->validator() for errors
        $errors = $logHelper->validates( $this->data );
        foreach ($errors as $field=>$error)
        {
            $this->validator()->invalidate($field, $error);
        }

        // Return the validation result
        return ($coreResult == false ? false : $result);
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
        $ok = parent::beforeSave($options);

        // Return if the initial process fails
        if (!$ok)
        {
            return $ok;
        }

        // Client ID must be present or we'll have some serious problems
        if (!isset( $this->data[ $this->name ]['client_id'] ))
        {
            trigger_error('Log saves must have an appropriate client_id field attached. Log cannot be saved.', E_USER_WARNING);
            return false;
        }

        // Try to load LogHelper from cache first
        if (!$logHelper = $this->getLogHelperByClientId( $this->data[ $this->name ]['client_id'] ))
        {
            trigger_error('The given client_id does not appear to exist. Log cannot be saved.', E_USER_WARNING);
        }

        // Process schema of the fields
        $logHelper->convertToDocument( $this->data[ $this->name ] );

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

        // Do not try to do the next step is the client_id doesn't exist
        foreach ($results as &$result)
        {
            // Don't try it if the client_id isn't in the resultset
            // This might be omitted due to the 'fields' list of the find options
            if ( !isset( $result[ $this->name ]['client_id'] ))
            {
                continue;
            }

            // Get the field helper instance for this client_id
            if (!$logHelper = $this->getLogHelperByClientId( $result[ $this->name ]['client_id'] ))
            {
                continue;
            }

            // Convert the data
            $logHelper->convertToArray( $result[ $this->name ] );
        }


        return $results;
    }


    /**
     * Fetch a LogHelper object by the given $client_id
     * - Attempts to cache these requests per client, otherwise the lookup could take a long, long time.
     * @param       string          $client_id      Client ID to load data for
     * @return      LogHelper|bool                Field Helper Object, or false if client unavailable
     */
    public function getLogHelperByClientId( $client_id )
    {
        // Load poor-mans cache for this pageload
        $clientLogHelperCache = Configure::read('Preslog.cache.clientLogHelper');
        if (!is_array($clientLogHelperCache))
        {
            $clientLogHelperCache = array();
        }

        // Attempt to load the ClientSchema from cache before calling up a new one.
        if ( isset($clientLogHelperCache[ $client_id ]))
        {
            return $clientLogHelperCache[ $client_id ];
        }
        else
        {
            // Fetch the Client Schema
            $clientModel = ClassRegistry::init('Client');
            $client = $clientModel->find('first', array(
                'conditions'=>array(
                    '_id' => $client_id,
                )
            ));

            // Abort if the client couldn't be loaded from the DB
            if ( sizeof($client) )
            {
                // Initialize field helper
                // Pass the field types available from config
                // Pass the schema from Client
                // Pass the datasource to the helper
                $logHelper = new LogHelper();
                $logHelper->setFieldTypes( Configure::read('Preslog.Fields') );
                $logHelper->loadSchema( $client['Client'] );
                $logHelper->setDataSource( $this->getDataSource() );

                // Save to cache
                $clientLogHelperCache[ $client_id ] = $logHelper;
                Configure::write('Preslog.cache.clientLogHelper', $clientLogHelperCache);

                return $logHelper;
            }
        }

        // Fell through - return our failure to find the client/logHelper
        return false;
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
        // Fetch all client info
        return $this->find('first', array(
            'conditions'=>array(
                'hrid'=>$hrid
            )
        ));
    }

    /**
     * fetch a list of logs based on mongo find
     *
     * @param $query
     * @param int $start            - log id to return from
     * @param int $limit            - how many logs top return
     * @param array $fieldDetails   - array('clientIds', 'dataFieldName') list of field ids from each client and the name of the value we want to sort on from the found field.
     * @param bool $orderAsc        - should the logs be returned in an ascending order
     *
     * @throws Exception
     * @return mixed
     */
    public function findByQuery($query, $start = 0, $limit = 10, $fieldDetails = array(), $orderAsc = true) {

        if (empty($query)) {
            return array();
        }

        //initial match to find records we want
        $criteria[] = array(
            '$match' => $query,
        );

        //rewind the log back together and add the extra 'sort' field so we can sort
        $group = array(
            //list all the log fields since there is no 'select *'
            '_id' => '$_id',
            'hrid' => array('$first' => '$hrid'),
            'client_id' => array('$first' => '$client_id'),
            'fields' => array('$push' => '$fields'),
            'attributes' => array('$first' => '$attributes'),
            'deleted' => array('$first' => '$deleted'),
         );

        //are we sorting the results?
        if (isset($fieldDetails['clientIds']) && !empty($fieldDetails['clientIds'])) {

            //split all the fields up so we can order based on sub field
            $criteria[] = array(
                '$unwind' => '$fields',
            );

            //put the list of field id's we are sorting on into a friendly array
            $fieldIds = array();
            foreach($fieldDetails['clientIds'] as $id) {
                //only return the fields we are searching against
                $fieldIds[] = array(
                    '$eq' => array(
                        '$fields.field_id',
                        new MongoId($id),
                    ),
                );
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
                    'sort.' . $fieldDetails['dataFieldName'] => $orderDirection,
                )
            );
        }

        //offset for pagination
        $criteria[] = array(
            '$skip' => (int)$start,
        );

        //limit for pagination
        $criteria[] = array(
            '$limit' => (int)$limit,
        );

        //actually do the query and return result
        $mongo = $this->getMongoDb();
        $data = $mongo->selectCollection('logs')->aggregate($criteria);

        if ($data['ok'] == 0) {
            throw new Exception($data['errmsg']);
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

    public function countByQuery($query) {
        return $this->find('count', array(
            'conditions' => $query,
        ));
    }

    public function findAggregate($match, $mongoPipeLine = array(), $fields = array()) {
        if (empty($match)) {
            return array(
                'result' => array(),
                'ok' => 1,
            );
        }


        //initial match to get the set we are working on
        $criteria = array(
            array('$match' => $match),
        );


        //get all the field ids in one array
        $fieldIds = array();
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
                if (isset($fields[$fieldName])) {
                    foreach($fields[$fieldName] as $id) {
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
        $criteria[] = $sort;
        $criteria[] = $project;

        $mongo = $this->getMongoDb();
        $data = $mongo->selectCollection('logs')->aggregate($criteria);

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

        // Client fetch opts
        $client = $clientModel->find('first', array(
            'conditions'=>array(
                '_id'=>$client_id
            ),
            'fields'=>array(
                'fields',
                'attributes'
            ),
        ));

        // Save only the items relevant to this action
        $options = array(
            'fields' => $client['Client']['fields'],
            'attributes' => $client['Client']['attributes'],
        );

        return $options;
    }

}