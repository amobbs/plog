<?php

namespace Preslog\Logs\FieldTypes;


/**
 * Preslog Field Types: Type Abstract
 * Extend this to create different field types for use with Preslog.
 */
abstract class FieldTypeAbstract
{

    /**
     * @var string      Unique for this notification type
     */
    protected $alias = '';


    /**
     * @var string      Human-readable Name of the field
     */
    protected $name = '';


    /**
     * @var string      Friendly name for user reference
     */
    protected $description = '';

    /**
     * @var array       list of details needed to aggregate this field type
     */
    protected $aggregationDetails = array();


    /**
     * @var array       Mongo schema definition for this field within Log
     */
    protected $mongoSchema = array();

    /**
     * @var array       Mongo schema definition for this field within Admin Client
     */
    protected $mongoClientSchema =array();

    /**
     * @var array       Storage for Field Data, driven directly from the Client schema.
     */
    protected $fieldSettings = array();

    /**
     * @var null        Data source object, back to main DB. Required for some lookups
     */
    protected $dataSource = null;

    /**
     * @var array       Field data, as per the parent log
     */
    public $data = array();

    /**
     * @var null|LogEntity      Reference to parent Log Entity
     */
    protected $log = null;

    /**
     * Fetch a list of properties for this field, or just the one specified.
     * @param   string|null  $property
     * @return  array
     */
    public function getProperties( $property=null )
    {
        $out = $this->getPropertyList();

        if (!empty($property))
        {
            if (!isset( $out[ $property ] ))
            {
                trigger_error("Requested property '{$property}' does not exist on field '{$this->alias}'", E_USER_WARNING);
            }

            return $out[ $property ];
        }

        return $out;
    }


    /**
     * Fetch all properties
     * @return  array
     */
    protected function getPropertyList()
    {
        return array(
            'alias'             =>$this->alias,
            'name'              =>$this->name,
            'description'       =>$this->description,
            'aggregationDetails'=>$this->aggregationDetails,
        );
    }


    /**
     * used to create a human readable list for the aggregation details that can be used in the interface
     *
     * @param   $fieldName
     * @return  array
     */
    public function listDetails($fieldName) {
        return array();
    }


    /**
     * @param $data
     * @param null $aggregationType
     * @return string
     */
    public function chartDisplay($data, $aggregationType = null) {
        return '';
    }


    /**
     * Initialise this field type with the given data.
     * - Will save the fieldData from the client
     * - Should be called by an extended class, which has it's own handler for $field['data']
     * @param   array       $field          Data to configure this type
     */
    public function setFieldSettings( $field )
    {
        $this->fieldSettings = $field;

    }


    /**
     * Initialise a link to the DBO source.
     * @param   DboSource   $dboSource      Data source
     */
    public function setDataSource( &$dboSource )
    {
        $this->dataSource = &$dboSource;
    }


    /**
     * Backreference to the parent log.
     * @param   $log
     */
    public function setLog( &$log )
    {
        $this->log = &$log;
    }


    /**
     * Validate the given $data, passing errors to the $validator
     * @param   array   $data           Data to validate
     * @param   string  $fieldName      Field name being validated
     * @return  array                   Data
     */
    public function validate( $data, $fieldName )
    {
        return array();
    }


    /**
     * Validate the admin schema of $data, passing errors to the $validator
     * @param   array   $data           Data to validate
     * @param   string  $fieldName      Field name being validated
     * @return  array                   Errors
     */
    public function validateClient( $data, $fieldName )
    {
        return array();
    }


    /**
     * Convert the given $data set into displayable data
     * @param   array   $data           Data to convert
     */
    public function convertForDisplay( &$data )
    {

    }


    /**
     * Fetch the field details from $this->fieldData
     * @return      array       Field detail information
     */
    public function getFieldSettings()
    {
        return $this->fieldSettings;
    }


    /**
     * Convert the given field to an Array, based on the field schema
     * @param   array   $field      Field data
     */
    public function clientFromDocument( &$field )
    {
        // Standard conversion
        $this->dataSource->convertToArray($field['data'], $this->mongoClientSchema, array());
    }


    /**
     * Convert the given field to an Array, based on the field schema
     * @param   array   $field      Field data
     */
    public function clientToDocument( &$field )
    {
        // Standard conversion
        $this->dataSource->convertToDocument($field['data'], $this->mongoClientSchema, array());
    }


    /**
     * Convert the given field to an Array, based on the field schema
     * @param   array   $field      Field data
     */
    public function fromDocument( $field )
    {
        $this->data = $field;

        // Standard conversion
        $this->dataSource->convertToArray($this->data['data'], $this->mongoSchema, array());
    }


    /**
     * Convert the given field to an Array, based on the field schema
     * @return  array               Data
     */
    public function toDocument()
    {
        // Copy data
        $out = $this->data;

        // Standard conversion
        $this->dataSource->convertToDocument($out['data'], $this->mongoSchema, array());

        return $out;
    }


    /**
     * Input field data from an array
     * @param   array   $data       Array data
     */
    public function fromArray( $data )
    {
        $this->data = $data;
    }


    /**
     * Output field data as an array
     * @return  array
     */
    public function toArray()
    {
        return $this->data;
    }


    /**
     * Convert data to individual fields
     * Many field types contain more than one item of data. This splits them to individual blocks with names.
     * @param   closure|null    $callback   Callback function to process data, if set
     * @return  array                       Fields
     */
    public function convertToFields( $callback=null )
    {
        // Callback not available? Use default
        if ( !is_callable($callback) )
        {
            return $this->defaultConvertToFields( $this->fieldSettings['label'], $this->data );
        }

        // Use custom callback
        return $callback( $this->fieldDetails['label'], $this->data );
    }


    /**
     * afterFind callback
     */
    public function afterFind()
    {
    }

    /**
     * beforeSave Callback
     */
    public function beforeSave()
    {
    }

}


