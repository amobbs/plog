<?php

/**
 * Log Model
 */

use Preslog\JqlParser\JqlParser;

App::uses('AppModel', 'Model');

class Log extends AppModel
{
    public $name = "Log";

    public $actsAs = array('Mongodb.Schema');

    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'           => array('type' => 'string', 'length'=>40, 'primary' => true, 'mongoType'=>'MongoId'),
        'hrid'          => array('type' => 'int'),
        'deleted'       => array('type' => 'boolean'),
        'fields'        => array(
            'field_id'      => array('type' => 'string', 'length'=>40, 'mongoType'=>'MongoId'),
            'data'          => array(null),
        ),
        'attributes'    => array('type' => null),
        'created'       => array('type' => 'datetime', 'mongoType'=>'MongoDate'),
        'modified'      => array('type' => 'datetime', 'mongoType'=>'MongoDate'),
    );



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
     */
    public function findByQuery($query, $start, $limit, $orderBy) {

        // Translate query to Mongo
        $jqlParser = new JqlParser();
        $jqlParser->setSqlFromJql($query);
        $criteria = $jqlParser->getMongoCriteria();

        return $this->find('all', array(
            'conditions' => $criteria,
            'limit' => $limit,
            'offset' => $start,
        ));
    }

    public function countByQuery($query) {
        // Translate query to Mongo
        $jqlParser = new JqlParser();
        $jqlParser->setSqlFromJql($query);
        $criteria = $jqlParser->getMongoCriteria();

        return $this->find('count', array(
            'conditions' => $criteria,
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

        //create the group by condtions for each axis we want to show
        foreach($mongoPipeLine as $axisName => $axisFields) {
            $axisFieldIds = array();

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

            if (empty($axisFieldIds)) {
                //this is a bit stupid but since there are no id's for this just grab the first record.
                //there should only be one field in the axis so grab the first one
                $keys = array_keys($axisFields);
                $field = $axisFields[$keys[0]];
                $x = array(
                    '$first' => '$' . $field['dataLocation'],
                );
            } else {
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

                if ($detail['aggregate']) {
                    $group['$group'][$axisName] = array(
                        $detail['groupBy'] => '$' . $axisName . '.' . $detail['dataLocation'],
                    );
                } else {
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
}