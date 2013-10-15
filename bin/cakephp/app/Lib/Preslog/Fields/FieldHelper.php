<?php

namespace Preslog\Fields;


/**
 * Class FieldHelper
 * - Provides general functionality for extracting Schema to Fields
 * @package Preslog\Fields
 */
class FieldHelper
{

    /**
     * @var     array   $fields         Fields, sorted by array ID key
     */
    protected $fields = array();


    /**
     * @var     array   $fieldTypes     Array of field type objects
     */
    protected $fieldTypes = array();


    /**
     * Set the fields types the FieldHelper can use
     * @param $types
     */
    public function setFieldTypes( $types )
    {
        $this->fieldTypes = $types;
    }


    /**
     * Instigate the Field schema based off the fields array given here
     * @param   array   $fieldsArray    Array of fields from Client model
     */
    public function loadFieldSchema( $fieldsArray )
    {
        // Re-structure fields by the field_id so we don't have to search for them.
        foreach ($fieldsArray as $field)
        {
            // Load the field type
            if (!isset($this->fieldTypes[ $field['type'] ]))
            {
                trigger_error("Unable to locate specified field type '{$field['type']}'", E_USER_ERROR);
            }

            // Fetch type
            $type = clone $this->fieldTypes[ $field['type'] ];

            // Initialise type with type data
            $type->initialise( $field['data'] );

            // Store type to lookup
            $this->fields[ $field['_id'] ] = $type;
        }
    }


    /**
     * Validate the given $modelData, passing validation errors to the $validator class.
     * @param   array               $modelData      Array of Log data to validate
     * @param   ModelValidator      $validator      Validator object to pass errors to
     * @return  bool                    True if validation passes
     */
    public function validates( $modelData, ModelValidator $validator )
    {
        // Do some things
    }


    /**
     * Convert an Array to a Document
     * - Process the Log Schema against the given $data and convert to a Document
     */
    public function convertToDocument( &$data )
    {
        foreach ($data as &$field)
        {
            $this->fields[ $field['field_id'] ];
        }
    }


    /**
     * Convert a Document to an Array
     * - Process the Log Schema and convert the given $data to an Array
     */
    public function convertToArray( &$data )
    {
        // Do stuff to the $data
    }





}