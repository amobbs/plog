<?php

namespace Preslog\Logs\Entities;


/**
 * Class Log Entity
 * - Provides functionality surrounding an individual log, and a single access point.
 * @package Preslog\Logs\Entities
 */
class LogEntity
{

    /**
     * @var     array           Log Data, as array
     */
    protected $data = array();

    /**
     * @var     ClientEntity    Client Object
     */
    protected $client = null;

    /**
     * @var     null            Data SOurce
     */
    protected $dataSource = null;

    /**
     * @var     array           Field list for this log
     */
    protected $fields = array();


    /**
     * Output to an array format
     * @return array
     */
    public function toArray()
    {
        // Take a copy of the log data
        $data = $this->data;
        $data['fields'] = array();

        // toArray dynamic fields
        foreach ($this->fields as $field)
        {
            $data['fields'][] = $field->toArray();
        }

        return $data;
    }


    /**
     * Parse data in from an array
     * @param   array   $data       Array to parse
     */
    public function fromArray( $data )
    {
        // Use mongo datasource to convert Array to Document in fields
        foreach ($data['fields'] as &$field)
        {
            // Create each field instance, and load the data
            $this->fields[ $field['field_id'] ] = clone $this->client->fields[ $field['field_id'] ];
            $this->fields[ $field['field_id'] ]->setLog( $this );
            $this->fields[ $field['field_id'] ]->fromArray($field);
        }

        // Do not store the dynamic fields in the data block.
        unset($data['fields']);

        // Save data block
        $this->data = $data;

    }


    /**
     * Output to a document format
     */
    public function toDocument()
    {
        // Copy the data to a doc
        $doc = $this->data;

        // Use mongo datasource to convert Array to Document in fields
        foreach ($doc['fields'] as &$field)
        {
            $this->client->fields[ $field['field_id'] ]->toDocument($field);
        }

        return $doc;
    }


    /**
     * Parse data in from a document format
     * @param   array   $doc        Document to parse
     */
    public function fromDocument( $doc )
    {
        // Use mongo datasource to convert Array to Document in fields
        foreach ($doc['fields'] as &$field)
        {
            // Create each field instance, and load the data
            $this->fields[ $field['field_id'] ] = clone $this->client->fields[ $field['field_id'] ];
            $this->fields[ $field['field_id'] ]->setLog( $this );
            $this->fields[ $field['field_id'] ]->fromDocument($field);
        }

        // Do not store the dynamic fields in the data block.
        unset($doc['fields']);

        // Save data block
        $this->data = $doc;
    }


    /**
     * Parse the Log structure to a Field-based array of key:value pairs.
     * @param   array|null      $fieldTypeCallbacks     Callback functions per field type, for alternate processing methods
     * @return  array                                   Key/Value pair fields and values
     */
    public function toDisplay( $fieldTypeCallbacks = null )
    {
        // todo
        $outFields = array();

        // Run fields through conversion/callbacks
        foreach ($this->fields as &$field)
        {
            $settings = $field->getFieldSettings();

            // Get closure
            $closure = (isset($fieldTypeCallbacks[ $settings['type'] ]) ? $fieldTypeCallbacks[ $settings['type'] ] : null);

            // Convert to fields, using specified closure, and put to array
            $outFields = array_merge($outFields, $field->convertToFields( $closure ));
        }

        // Run through attributes, to fetch collapsed list
        foreach ($this->data['attributes'] as $attribute)
        {
            // TODO make this work properly, k?
            $outFields[] = $attribute;
        }

        return $outFields;
    }




    /**
     * Set Client
     * - Set the client entity, for working with the log schema
     * @param ClientEntity $client
     */
    public function setClientEntity( ClientEntity $client )
    {
        $this->client = $client;
    }


    /**
     * Set Data Source
     * - Save the data source to this object, so parsing can be performed
     * @param   $dataSource
     */
    public function setDataSource( &$dataSource )
    {
        $this->dataSource = $dataSource;
    }

    /**
     * Validate the Log
     */
    public function validates()
    {
        // TODO - Seriously, fix this.
        $errors = array();

        // Refactor validation fields to associative, by name.
        $fields = array();
        foreach ($this->data['fields'] as $field)
        {
            // Does the field exist? Skip anything that's bad, and raise an error.
            if ( !isset($this->client->fields[ $field['field_id'] ]) )
            {
                $errors[ $field['fields'] ] = 'One or more fields could not be found in the log schema.';
                continue;
            }

            // Fetch the field name
            $name = $this->client->fields[ $field['field_id'] ];

            // Refactor the field
            $fields[ $name ] = $field;
        }

        // Validate fields
        foreach ( $fields as $name=>$field)
        {
            // Validate field content
            $error = $this->client->fields[ $field['field_id'] ]->validate( $fields, $name );
            if (!empty($error))
            {
                $errors[ 'fields.'.$field['field_id'] ] = $error;
            }
        }

        // Validate attributes; all given items must exist in the schema keys
        foreach( $this->data['attributes'] as $attr)
        {
            // If the attribute can't be found
            if (!isset($this->client->attrbuteLookup[ $attr ]))
            {
                $errors['attributes'] = 'One or more attributes could not be found in the log schema.';
            }
        }
    }


    /**
     * Before Save
     * - Perform actions required before a Log save operation
     */
    public function beforeSave()
    {
        // Cycle through data and convert to new schema
        foreach ($this->fields as &$field)
        {
            $field->beforeSave();
        }
    }


    /**
     * After Find
     * - Perform actions required after a log find operation
     */
    public function afterFind()
    {
        // Cycle through fields and run schema conversion
        foreach ($this->fields as &$field)
        {
            $field->afterFind();
        }
    }

}