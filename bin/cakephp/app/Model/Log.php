<?php

/**
 * Log Model
 */

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
    public function findByMongoCriteria($criteria, $start, $limit, $orderBy) {

        return $this->find('all', array(
            'conditions' => $criteria,
            'limit' => $limit,
            'offset' => $start,
        ));
    }

    public function countByMongoCriteria($criteria) {
        return $this->find('count', array(
            'conditions' => $criteria,
        ));
    }
}