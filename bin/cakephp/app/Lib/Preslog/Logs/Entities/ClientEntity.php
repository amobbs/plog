<?php

namespace Preslog\Logs\Entities;


/**
 * Class FieldHelper
 * - Provides general functionality for extracting Schema to Fields
 * @package Preslog\Logs\Entities
 */
class ClientEntity
{

    /**
     * @var     array       Client data
     */
    protected $data = array();

    /**
     * @var     array       Fields objects, per field, for this client
     */
    public $fields = array();

    /**
     * @var     array       Attribute lookup by _id:name key/value pair
     */
    public $attributeLookup = array();


    /**
     * Output object as an array
     * @return  array       Client Data
     */
    public function toArray()
    {
        return $this->data;
    }


    /**
     * Parse data from an array
     * @param   array       $clientData
     */
    public function fromArray( $clientData )
    {
        if ( isset($clientData['fields']) )
        {
            // Re-structure fields by the field_id so we don't have to search for them.
            foreach ($clientData['fields'] as &$field)
            {
                // Load the field type
                if (!isset($this->fieldTypes[ $field['type'] ]))
                {
                    trigger_error("Unable to locate specified field type '{$field['type']}' for client {$clientData['_id']}", E_USER_ERROR);
                }

                // Clone the type for individual use on this client
                $type = clone $this->fieldTypes[ $field['type'] ];

                // Initialise field obj
                $type->setDataSource( $this->dataSource );

                // Convert the client data so it's usable
                $type->clientFromArray( $field );

                // Apply settings to the field
                $type->setFieldSettings( $field );

                // Store type for use in lookup
                $this->fields[ $field['_id'] ] = $type;
            }
        }

        // Store resultant client data
        $this->data = $clientData;

        // Walk the Attributes groups and create a flat attribute lookup.
        // This map is used later on for converting fields.
        if (isset($this->data['attributes']))
        {
            $this->attributeLookup = $this->getFlatAttributesList( $this->data['attributes'] );
        }
    }


    /**
     * Output data as a document
     * @return  array
     */
    public function toDocument()
    {
        // Copy the data to a doc
        $doc = $this->data;

        // Use mongo datasource to convert Array to Document in fields
        foreach ($doc['fields'] as &$field)
        {
            $this->fields[ $field['_id'] ]->clientToDocument($field);
        }

        return $doc;
    }


    /**
     * Parse data from a document
     * - Mongo Data source will have parsed the model schema, but the dynamic schema still needs to be parsed.
     * @param   array       $clientData
     */
    public function fromDocument( $clientData )
    {
        if ( isset($clientData['fields']) )
        {
            // Re-structure fields by the field_id so we don't have to search for them.
            foreach ($clientData['fields'] as &$field)
            {
                // Load the field type
                if (!isset($this->fieldTypes[ $field['type'] ]))
                {
                    trigger_error("Unable to locate specified field type '{$field['type']}' for client {$clientData['_id']}", E_USER_ERROR);
                }

                // Clone the type for individual use on this client
                $type = clone $this->fieldTypes[ $field['type'] ];

                // Initialise field obj
                $type->setDataSource( $this->dataSource );

                // Convert the client data so it's usable
                $type->clientFromDocument( $field );

                // Apply settings to the field
                $type->setFieldSettings( $field );

                // Store type for use in lookup
                $this->fields[ $field['_id'] ] = $type;
            }
        }

        // Store resultant client data
        $this->data = $clientData;

        // Walk the Attributes groups and create a flat attribute lookup.
        // This map is used later on for converting fields.
        if (isset($this->data['attributes']))
        {
            $this->attributeLookup = $this->getFlatAttributesList( $this->data['attributes'] );
        }
    }


    /**
     * Convert the client Attribute Hierarchy into a list of Attributes by ID.
     * @param   array       $attributeList      Attribute hierarchy to convert
     * @return  array                          Flat attribute list
     */
    protected function getFlatAttributesList( $attributeList )
    {
        $list = array();

        // Only process if there's something to process!
        if (!sizeof($attributeList))
        {
            return array();
        }

        // Process all attributes
        foreach ($attributeList as $item)
        {
            // Save the linkage
            $list[ $item['_id'] ] = $item['name'];

            // Only process children if it's set, an array, and has entries.
            if (isset($item['children']) && is_array($item['children']) && sizeof($item['children']))
            {
                // Merge result with list. Shouldn't get key collisions as each attribute has a unique ID.
                $list = array_merge($list, $this->getFlatAttributesList( $item['children'] ) );
            }
        }

        return $list;
    }


    /**
     * Set Field Types
     * - Initialise the client with the available field types
     * @param   array       $fieldTypes
     */
    public function setFieldTypes( $fieldTypes )
    {
        // Field types are an associated lookup table of initialised classes
        $this->fieldTypes = $fieldTypes;
    }

    public function getFieldTypeByName( $fieldName )
    {
        if ($fieldName == 'created' || $fieldName == 'modified' || $fieldName == 'version')
        {
            $fieldName = 'loginfo';
        }


        foreach( $this->fields as $field )
        {
            $fieldSettings = $field->getFieldSettings();
            if ($fieldSettings['name'] == $fieldName)
            {
                return $field;
            }
        }
    }

    /**
     * Set Data Source
     * - Attach the data source to this object so we can run parse processes
     * @param   $dataSource
     */
    public function setDataSource( &$dataSource )
    {
        $this->dataSource = $dataSource;
    }


    /**
     * Validate the Client
     */
    public function validates()
    {

    }


    /**
     * Before Save
     * Operations to perform before a save
     */
    public function beforeSave()
    {
    }


    /**
     * After Find
     * Operations to perform after a find
     */
    public function afterFind()
    {
    }


}