<?php

namespace Preslog\Logs\Entities;

use Preslog\Logs\FieldTypes\FieldTypeAbstract;
use DataSource;
use User;
use ClassRegistry;

/**
 * Class FieldHelper
 * - Provides general functionality for extracting Schema to Fields
 * @package Preslog\Logs\Entities
 */
class ClientEntity
{

    /**
     * @var     array                   Client data
     */
    public $data = array();

    /**
     * @var     FieldTypeAbstract[]     Fields objects, per field, for this client
     */
    public $fields = array();

    /**
     * @var     FieldTypeAbstract[]     Array of field types that can exist on a client.
     */
    protected $fieldTypes = array();

    /**
     * @var     DataSource              Data source, connection to DB.
     */
    protected $dataSource;

    /**
     * @var     array                   Attribute lookup by _id:name key/value pair
     */
    public $attributeLookup = array();

    /**
     * @var     int                     Attribute field permissions
     */
    public $attributePermissions = 0;

    /**
     * @var     bool                    Permissions to delete logs?
     */
    public $deletePermissions = false;

    /**
     * @var     array                   User details, used for authentication
     */
    protected $user;

    /**
     * @var     User                    User model, used for authentication
     */
    protected $userModel;


    /**
     * Constructor
     * - Initialise an instance of the User model.
     */
    public function __construct()
    {
        // Instigate user model
        $this->userModel = ClassRegistry::init('User');
    }


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
                    trigger_error("Unable to locate specified field type '{$field['type']}' for client '{$clientData['_id']}'.", E_USER_ERROR);
                }

                // Clone the type for individual use on this client
                $type = clone $this->fieldTypes[ $field['type'] ];

                // Initialise field obj
                $type->setDataSource( $this->dataSource );

                // Convert the client data so it's usable
                $type->clientFromArray( $field );

                // Apply settings to the field
                $type->setFieldSettings( $field );

                // Apply permissions rules to the field
                $this->applyPermissions( $type );

                // Store type for use in lookup
                $this->fields[ $field['_id'] ] = $type;
            }
        }

        // Remove fields from generic data store
        unset($clientData['fields']);

        // Store resultant client data
        $this->data = $clientData;

        // Walk the Attributes groups and create a flat attribute lookup.
        // This map is used later on for converting fields.
        if (isset($this->data['attributes']))
        {
            $this->attributeLookup = $this->getFlatAttributesList( $this->data['attributes'] );
        }

        // Apply attribute field permissions
        $this->applyLogPermissions();
    }


    /**
     * Output data as a document
     * @return  array
     */
    public function toDocument()
    {
        // Copy the data to a doc
        $doc = $this->data;
        $doc['fields'] = array();

        // Use mongo datasource to convert Array to Document in fields
        foreach ($this->fields as &$field)
        {
            // Convert Entity
            $f = $field->clientToDocument();
            $doc['fields'][] = $f;
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

                // Apply permissions rules to the field
                $this->applyPermissions( $type );

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

        // Apply attribute field permissions
        $this->applyLogPermissions();
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
     * Return Client Schema as options, for use in conjunction with Log
     * - Remove field options that are not visible
     */
    public function getOptions()
    {
        // Take a copy of the client
        $data = $this->data;

        // Remove fields
        foreach ($data['fields'] as $k=>$field)
        {
            // If hidden
            if ( $this->fields[ $field['_id'] ]->isHiddenFromOptions() )
            {
                unset( $data['fields'][ $k ] );
            }
        }

        // Remove attrs if hidden
        if ( $this->attributePermissions & FieldTypeAbstract::FLAG_HIDDEN )
        {
            $data['attributes'] = array();
        }

        return $data;
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


    /**
     * Set the user accessing this client. Applies a permissions element to the client as a result.
     * @param       array       $user       User to apply to client
     */
    public function setUser( $user )
    {
        $this->user = $user;
    }


    /**
     * Fetch from $this->fields where Field's "_id" is $id
     * @param   string      $id             Mongo ID of field
     * @return  FieldTypeAbstract|bool      Field data
     */
    public function getFieldById( $id )
    {
        // Return the field by $id if it's available
        if (isset($this->fields[ $id ]))
        {
            return $this->fields[ $id ];
        }

        return false;
    }

    /**
     * Get the field type by given $fieldName
     * @param   string      $fieldName      Name of searchable field
     * @return  FieldTypeAbstract|bool                 Name of the field, or false is no match is found
     */
    public function getFieldTypeByName( $fieldName )
    {
        // Loop through fields and find the matching fieldname
        foreach( $this->fields as $field )
        {
            // Check field names
            if ($field->isName($fieldName))
            {
                // Return the field name
                return $field;
            }
        }

        return false;
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
     * Apply permissions rules to the given $field
     * Ideally this would be set dynamically in the field data, and then parsed on Client load directly inside the field.
     * Unfortunately this isn't in the budget, so we code it here and identify the fields by name instead.
     * Maybe this can be implemented with client control in future.
     * @param   FieldTypeAbstract   $field      Field object to process
     */
    protected function applyPermissions( &$field )
    {
        // If user is comment-only..
        // Everything except CommentOnly is ReadOnly.
        if ($this->userModel->isAuthorized( 'comment-only', $this->user['role'] ))
        {
            if (!$field->isName('comments'))
            {
                $field->setFlag( FieldTypeAbstract::FLAG_READONLY );
            }
        }

        // If user is not log-accountability...
        // Accountability and Status fields will not be displayed
        if (!$this->userModel->isAuthorized( 'log-accountability', $this->user['role'] ))
        {
            if ($field->isName('status') || $field->isName('accountability'))
            {
                $field->setFlag( FieldTypeAbstract::FLAG_HIDDEN );
            }
        }
    }

    /**
     * Apply permissions for log based on the user
     * This raises another point; ideally the Attribute hierarchy would be field data in of itself.
     * But this would vastly complicate the setup.
     */
    protected function applyLogPermissions()
    {
        // If user is "log-delete"
        // Enable log deletion
        if ($this->userModel->isAuthorized( 'log-delete', $this->user['role'] ))
        {
            $this->deletePermissions = true;
        }

        // If user is "comment-only"
        // Attributes are set to readonly
        if ($this->userModel->isAuthorized( 'comment-only', $this->user['role'] ))
        {
            $this->attributePermissions = $this->attributePermissions|FieldTypeAbstract::FLAG_READONLY;
        }
    }


    /**
     * Validate the Client
     */
    public function validates()
    {
        // TODO: Validation of client Edit
    }


    /**
     * Before Save
     * Operations to perform before a save
     */
    public function beforeSave()
    {
        // Sub-field beforeSave
        foreach ($this->fields as &$field)
        {
            $field->clientBeforeSave();
        }

        // Apply attribute IDs
        $this->attributeBeforeSave( $this->data['attributes'] );
    }


    /**
     * Recursively parse attribute tree
     * @param   array       $attributes     Attribute layer to parse
     */
    protected function attributeBeforeSave( &$attributes )
    {
        // Must have attributes!
        if (!sizeof($attributes))
        {
            return;
        }

        // Check each item
        foreach( $attributes as &$attr )
        {
            // If has children, parse children
            if (isset($attr['children']) && sizeof($attr['children']))
            {
                $this->attributeBeforeSave( $attr['children'] );
            }

            // New item?
            // isn't set, is empty, not 24 char, or has newGroup or newChild.
            if (!isset($attr['_id']) || empty($attr['_id']) || strlen($attr['_id']) != 24 || isset($attr['newGroup']) || isset($attr['newChild']))
            {
                // Remove notification
                unset($attr['newChild']);
                unset($attr['newGroup']);

                // New ID (as string)
                $attr['_id'] = (string) new \MongoId();
            }
        }
    }



    /**
     * After Find
     * Operations to perform after a find
     */
    public function afterFind()
    {
        // Sub-field afterFind
        foreach ($this->fields as &$field)
        {
            $field->clientAfterFind();
        }
    }


}