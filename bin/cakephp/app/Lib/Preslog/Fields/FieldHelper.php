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
     * @var     dataSource  $dataSource Datasource object for DB.
     */
    protected $dataSource = null;

    /**
     * Set the fields types the FieldHelper can use
     * @param $types
     */
    public function setFieldTypes( $types )
    {
        $this->fieldTypes = $types;
    }


    /**
     * Set the data source for use in document conversion
     * @param   DboSource   $datasource     Datasource to use in doc conversion
     */
    public function setDataSource( &$datasource )
    {
        $this->dataSource = &$datasource;
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
        // Check the datasource is available
        if (!$this->dataSource)
        {
            trigger_error('Datasource must be initialised before convertToArray is called.', E_USER_ERROR);
        }

        // Cycle through data and convert to new schema
        foreach ($data['fields'] as &$field)
        {
            // Fetch schema for this object
            $schema = $this->fields[ $field['field_id'] ]->getSchema();

            // Convert data using datasource
            $this->dataSource->convertToDocument($field['data'], $schema, array());

        }
    }


    /**
     * Convert a Document to an Array
     * - Process the Log Schema and convert the given $data to an Array
     */
    public function convertToArray( &$data )
    {
        // Check the datasource is available
        if (!$this->dataSource)
        {
            trigger_error('Datasource must be initialised before convertToArray is called.', E_USER_ERROR);
        }

        // Cycle through fields and run schema conversion
        foreach ($data['fields'] as &$field)
        {
            // Fetch schema for this object
            $schema = $this->fields[ $field['field_id'] ]->getSchema();

            // Convert data using datasource
            $this->dataSource->convertToArray($field['data'], $schema, array());
        }
    }



}