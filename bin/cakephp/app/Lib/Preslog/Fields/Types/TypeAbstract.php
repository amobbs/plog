<?php

namespace Preslog\Fields\Types;


/**
 * Preslog Field Types: Type Abstract
 * Extend this to create different field types for use with Preslog.
 */
abstract class TypeAbstract
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
     * @var array       Mongo schema definition for this field
     */
    protected $mongoSchema = array();



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


    /***
     * used to create a human readable list for the aggregation details that can be used in the interface
     *
     * @param $fieldName
     * @param $fieldId
     * @return array
     */
    public function listDetails($fieldName) {
        return array();
    }

    public function chartDisplay($data, $aggregationType = null) {
        return '';
    }


    /**
     * Initialise this field type with the given data.
     * Usually used in conjunction with Select fields, etc.
     * @param   array   $data       Data to configure this type
     */
    public function initialise( $data )
    {
    }


    /**
     * Fetch the schema for this type of object
     * @returns     array       Schema definition for this field
     */
    public function getSchema()
    {
        return $this->mongoSchema;
    }

}