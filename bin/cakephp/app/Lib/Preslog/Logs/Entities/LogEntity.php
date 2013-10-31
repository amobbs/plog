<?php

namespace Preslog\Logs\Entities;

use Preslog\Logs\FieldTypes\FieldTypeAbstract;


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
    public $data = array();

    /**
     * @var     ClientEntity    Client Object
     */
    protected $client = null;

    /**
     * @var     null            Data Source
     */
    protected $dataSource = null;

    /**
     * @var     FieldTypeAbstract[]           Field list for this log
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
            $fieldData = $field->toArray();

            // If there's data to put to Array
            if (is_array($fieldData))
            {
                $data['fields'][] = $fieldData;
            }
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
            if (!$fieldObject = $this->client->getFieldById( $field['field_id'] ))
            {
                trigger_error("Could not read log from array; Client field id '{$field['field_id']}' could not be matched.", E_USER_ERROR);
            }

            // Create each field instance, and load the data
            $this->fields[ $field['field_id'] ] = clone $fieldObject;
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
        foreach ($this->fields as &$field)
        {
            $field->toDocument($field);
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
            if (!$fieldObject = $this->client->getFieldById( $field['field_id'] ))
            {
                trigger_error("Could not read log from document; Client field id '{$field['field_id']}' could not be matched.", E_USER_ERROR);
            }

            // Create each field instance, and load the data
            $this->fields[ $field['field_id'] ] = clone $fieldObject;
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
            $outFields[] = $attribute; // TODO - needs to actually list the selected attributes as separated fields
        }

        return $outFields;
    }


    /**
     * Merge a series of changes with this log
     * - If an element is set as READONLY, do NOT overwrite the contents of this log (the original) with $newLog (the changes)
     * - Conditionally make certain other changes.
     * @param       LogEntity       $newLog      Long Entity to inherit from
     */
    public function overwiteWithChanges( $newLog )
    {
        // Note: Standard fields (_id, client_id, deleted, etc) aren't modified on the origin (this) log.

        // Skim $newLog fields - if a field is not READONLY or HIDDEN then update the content of this log.
        foreach ($newLog->fields as $fieldKey=>$field)
        {
            // If permissions permit, overwriteWithChanges will overwrite the current field data with new daat
            $this->fields[ $fieldKey ]->overwriteWithChanges( $field->data );
        }

        // Attributes; if NOT readonly then use the change version
        if (!($this->client->attributePermissions & FieldTypeAbstract::FLAG_READONLY))
        {
            $this->data['attributes'] = $newLog->data['attributes'];
        }
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
        $errors = array();

        // Refactor validation fields to associative, by name.
        foreach ($this->fields as &$field)
        {
            $field_id = $field->data['field_id'];

            // Validate field content
            $error = $field->validates();
            if (!empty($error))
            {
                $errors[ 'fields_'.$field_id ] = $error;
            }
        }

        // Validate attributes; all given items must exist in the schema keys
        foreach( $this->data['attributes'] as $attr)
        {
            // If the attribute can't be found
            if (!isset($this->client->attributeLookup[ $attr ]))
            {
                $errors['attributes'][] = 'One or more attributes could not be found in the log schema.';
            }
        }

        return $errors;
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

        // Convert HRID to just numbers
        if (!empty($this->data['hrid']) && !is_numeric($this->data['hrid']))
        {
            preg_match('/[a-zA-Z]+_\#([0-9]+)/', $this->data['hrid'], $matches);
            $this->data['hrid'] = $matches[1];
        }

        // If no _id for this log, lookup the next suitable increment in the database
        if (!isset($this->data['_id']))
        {
            // TODO: Fetch the new log ID from the Mongo database incrementor and apply it to this _id
            $this->data['hrid'] = '99999';
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

        // Convert HRID to prefixed string and slug (for links)
        $this->data['slug'] = $this->client->data['logPrefix'].'_'.$this->data['hrid'];
        $this->data['hrid'] = $this->client->data['logPrefix'].'_#'.$this->data['hrid'];
    }


    /**
     * Fetch the local field object by it's SEARCHABLE name (not the field->name itself)
     * @param       string                  $fieldName      Name of the field
     * @returns     FieldTypeAbstract|bool                  Field object, or false if fields doesn't exist.
     */
    public function getFieldByName( $fieldName )
    {
        // Find the field by name
        foreach ( $this->fields as $field)
        {
            // Check for field name match
            if ($field->isName($fieldName))
            {
                return $field;
            }
        }

        return false;
    }

}