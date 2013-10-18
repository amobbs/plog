<?php

namespace Preslog\Logs;


/**
 * Class FieldHelper
 * - Provides general functionality for extracting Schema to Fields
 * @package Preslog\Fields
 */
class LogHelper
{

    /**
     * @var     string      $clientId               Store the clientId. Might be useful later.
     */
    protected $clientId;

    /**
     * @var     array       $fields                 Fields, sorted by array ID key
     */
    protected $fields = array();


    /**
     * @var     array       $fieldTypes             Array of field type objects
     */
    protected $fieldTypes = array();

    /**
     * @var     array       $attributes             Flat list of attributes as key=>name pair
     */
    protected $attributes = array();

    /**
     * @var     array       $attributesHierarchy    Hierarchial series of attribute groups, as per client schema.
     */
    protected $attributesHierarchy = array();

    /**
     * @var     DboSource   $dataSource             Datasource object for DB.
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
     * Instigate the client's schema based off the array given here
     * @param   array   $clientData    Array of Client info from client model
     */
    public function loadSchema( $clientData )
    {
        // Store the client ID.
        $this->clientId = $clientData['_id'];

        // Re-structure fields by the field_id so we don't have to search for them.
        foreach ($clientData['fields'] as $field)
        {
            // Load the field type
            if (!isset($this->fieldTypes[ $field['type'] ]))
            {
                trigger_error("Unable to locate specified field type '{$field['type']}'", E_USER_ERROR);
            }

            // Fetch type
            $type = clone $this->fieldTypes[ $field['type'] ];

            // Initialise field with data
            $type->initialise( $field );

            // Store type to lookup
            $this->fields[ $field['_id'] ] = $type;
        }

        // Store attribs
        $this->attributesHierarchy = $clientData['attributes'];

        // Walk the Attributes groups and create a flat attribute lookup.
        // This map is referenced later on for converting fields.
        $this->attributes = $this->getFlatAttributesList( $this->attributesHierarchy );
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
     * Validate the given $modelData, passing validation errors to the $validator class.
     * @param   array               $modelData      Array of Log data to validate
     * @return  bool                    True if validation passes
     */
    public function validates( $modelData )
    {
        $errors = array();

        // Refactor validation fields to associative, by name.
        $fields = array();
        foreach ($modelData['fields'] as $field)
        {
            // Does the field exist? Skip anything that's bad, and raise an error.
            if ( !isset($this->fields[ $field['field_id'] ]) )
            {
                $errors[ $field['fields'] ] = 'One or more fields could not be found in the log schema.';
                continue;
            }

            // Fetch the field name
            $name = $this->fields[ $field['field_id'] ];

            // Refactor the field
            $fields[ $name ] = $field;
        }

        // Validate fields
        foreach ( $fields as $name=>$field)
        {
            // Validate field content
            $error = $this->fields[ $field['field_id'] ]->validate( $fields, $name );
            if (!empty($error))
            {
                $errors[ 'fields.'.$field['field_id'] ] = $error;
            }
        }

        // Validate attributes; all given items must exist in the schema keys
        foreach( $modelData['attributes'] as $attr)
        {
            // If the attribute can't be found
            if (!isset($this->attrbutes[ $attr ]))
            {
                $errors['attributes'] = 'One or more attributes could not be found in the log schema.';
            }
        }
    }


    /**
     * Convert an Array to a Document
     * - Process the Log Schema against the given $data and convert to a Document
     * @param   array       $data       Array to convert to Document format
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
     * @param   array       $data       Document to convert to Array format
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


    /**
     * Convert client document to array
     * @param   array       $data       client Document to convert to Array
     */
    public function convertClientToArray( &$data )
    {
        // Check the datasource is available
        if (!$this->dataSource)
        {
            trigger_error('Datasource must be initialised before convertToArray is called.', E_USER_ERROR);
        }

        if (isset($data['fields']))
        {
            // Cycle through fields and run schema conversion
            foreach ($data['fields'] as &$field)
            {
                // Load the field type
                if (!isset($this->fieldTypes[ $field['type'] ]))
                {
                    trigger_error("Unable to locate specified field type '{$field['type']}'", E_USER_ERROR);
                }

                // Fetch type
                $type = clone $this->fieldTypes[ $field['type'] ];

                // Fetch schema for this object
                $schema = $type->getClientSchema();

                // Convert data using datasource
                $this->dataSource->convertToArray($field['data'], $schema, array());
            }
        }
    }


    /**
     * Convert array to document
     * @param   array       $data       Convert given client Array to Document
     */
    public function convertClientToDocument( &$data )
    {
        // Check the datasource is available
        if (!$this->dataSource)
        {
            trigger_error('Datasource must be initialised before convertToArray is called.', E_USER_ERROR);
        }

        if (isset($data['fields']))
        {
            // Cycle through data and convert to new schema
            foreach ($data['fields'] as &$field)
            {
                // Load the field type
                if (!isset($this->fieldTypes[ $field['type'] ]))
                {
                    trigger_error("Unable to locate specified field type '{$field['type']}'", E_USER_ERROR);
                }

                // Fetch type
                $type = clone $this->fieldTypes[ $field['type'] ];

                // Fetch schema for this object
                $schema = $type->getClientSchema();

                // Convert data using datasource
                $this->dataSource->convertToDocument($field['data'], $schema, array());
            }
        }
    }


    /**
     * Convert the given $data item for display purposes
     * This is a one-way function - once data has been converted for display, there's no coming back.
     * - Replace all associative array IDs with their target data names
     * @param   array       $data       Array structure to convert for display
     */
    public function convertForDisplay( &$data )
    {
        // Sort by field order
        uasort($data['fields'], function($a, $b)
        {
            return ($a['order'] > $b['order']) ? -1 : 1;
        });

        // Process fields to their data equivalents
        foreach ($data['fields'] as &$field)
        {
            // Get client info fields
            $data = $this->fields[ $field['field_id'] ]->getFieldDetails();
            $data['data'] = $field['data'];

            // Convert for display purposes
            $this->fields[ $field['field_id'] ]->convertForDisplay( $data['data'] );

            // Switch
            $field = $data;
        }

        // Process Attribute to their data equivalents, using the attributeHierarchy as the tree
        $this->convertAttributesForDisplay( $data['attributes'], $this->attributesHierarchy );
    }


    /**
     * Convert the given $data by $typeCallbacks
     * @param   array   $data               Data to be converted
     * @param   array   $typeCallbacks      Callbacks to use for field types, indexed by Type, returning an array
     * @return  array                       Series of fields, extrapolated for the data types
     */
    public function convertToFields( $data, $typeCallbacks = array() )
    {
        $outFields = array();

        // Run fields through conversion/callbacks
        foreach ($data['fields'] as $field)
        {
            // Get closure
            $closure = (isset($typeCallbacks[ $field['type'] ]) ? $typeCallbacks[ $field['type'] ] : null);

            // Convert to fields, using specified closure, and put to array
            $outFields = array_merge($outFields, $this->fields[ $field['field_id'] ]->convertToFields( $field, $closure ));
        }

        // Run through attributes, to fetch collapsed list
        foreach ($data['attributes'] as $attribute)
        {
            // TODO make this work properly, k?
            $outFields[] = $attribute;
        }

        return $outFields;
    }


    /**
     * Convert attributes for display purposes
     * - Takes an array of $attributeList, and a hierarchy of $attributeHierarchy and converts to
     *   a structured hierarchy with true field names, omitting unselected members.
     * @param   array   $attributeList      List of attributes to convert
     * @param   array   $attributeHierarchy Hierarchy to use for structure of attributes
     * @return  array                       Hierarchy of selected fields
     */
    protected function convertAttributesForDisplay( $attributeList, $attributeHierarchy )
    {
        $hierarchy = array();

        // Don't try to do anything with an empty array
        if (!sizeof($attributeList))
        {
            return;
        }

        // Process all items
        foreach ($attributeHierarchy as $attribute)
        {
            // Storage for the log info
            $item = array();

            // Convert children FIRST
            if ( isset($attribute['children']) && is_array($attribute['children']) && sizeof($attribute['children']))
            {
                // Convert
                $children = $this->convertAttributesForDisplay( $attributeList, $attribute['children'] );

                // If the child contains members:
                // - we MUST keep the parent
                // - we keep the child data
                if (sizeof($children))
                {
                    // Keep the child data
                    $item['children'] = $children;

                    // Force this parent to be included, but it might not be selected.
                    $item['selected'] = false;
                }
            }

            // If we're present in $attributeList, we're selected.
            if (in_array($attribute['_id'], $attributeList))
            {
                $item['selected'] = true;
            }

            // If selected appears AT ALL, we lookup and keep this item.
            if (isset($item['selected']))
            {
                $item['name'] = $this->attributes[ $attribute['_id'] ];
            }

            // If data was found for $item, keep it.
            if (sizeof($item))
            {
                $hierarchy[] = $item;
            }
        }

        return $hierarchy;
    }
}