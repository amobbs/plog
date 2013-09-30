<?php

/**
 * Log Model
 */

App::uses('AppModel', 'Model');

class Log extends AppModel
{
    public $name = "Log";


    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'           => array('type' => 'string', 'length'=>40, 'primary' => true),
        'hrid'                  => array('type' => 'int'),
        'created_user_id'       => array('type' => 'string', 'length'=>255),
        'modified_user_id'      => array('type' => 'string', 'length'=>4),
        'deleted'               => array('type' => 'boolean'),
        'fields'                => array('type' => null),
        'attributes'            => array('type' => null),
        'version'               => array('type' => 'int'),
        'client_id'             => array('type' => 'string', 'length'=>40),
        'created'       => array('type' => 'datetime'),
        'modified'      => array('type' => 'datetime'),
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


}