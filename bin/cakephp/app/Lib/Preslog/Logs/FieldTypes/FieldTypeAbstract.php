<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\Entities\LogEntity;

/**
 * Preslog Field Types: Type Abstract
 * Extend this to create different field types for use with Preslog.
 */
abstract class FieldTypeAbstract
{
    const FLAG_READONLY = 1;        // Field will be read-only
    const FLAG_HIDDEN = 2;          // Field will be hidden

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
     * @var string      type of field that will be used for editing in red query builder (search page)
     */
    protected $queryFieldType = '';

    /**
     * jql operators that can be used to query against these fields
     */
    protected $allowedJqlOperators = array();

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
     * @var int                 Field flags. Controls certain permissions aspects of the field
     */
    protected $flags = 0;


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
            'queryFieldType'    =>$this->queryFieldType,
            'allowedJqlOperators' => $this->allowedJqlOperators,
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
     * Set flag on this field
     * @param   int     $flag       Flag to apply
     */
    public function setFlag( $flag )
    {
        // Add to flags
        $this->flags = $this->flags|$flag;

        // Apply Read-Only flag which needs to be passed in getOptions.
        $this->fieldSettings['readonly'] = ($this->flags & self::FLAG_READONLY);
    }


    /**
     * Validate the given $data, passing errors to the $validator
     * @return  bool        True if field data is valid
     */
    public function validates()
    {
        // This is not a valid field type
        return array("Validation for field type '".__CLASS__."' must be overridden.");
    }


    /**
     * Validate the admin schema of $data, passing errors to the $validator
     * @return  bool        True if the admin schema is valid
     */
    public function validatesSchema()
    {
        // This is not a valid field type
        return array("Validation for field type '".__CLASS__."' must be overridden.");
    }


    /**
     * Convert the given $data set into displayable data
     * @param   array   $data           Data to convert
     */
    public function convertForDisplay( &$data )
    {

    }

    /**
     * convert the given $data
     * @param $data
     */
    public function convertForMongo( &$data )
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
     * fetch the mongo schema
     * @return  array   details of the schema used in mongo
     */
    public function getMongoSchema()
    {
        return $this->mongoSchema;
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
     * @return      array       Document version of this field
     */
    public function clientToDocument()
    {
        $doc = $this->fieldSettings;

        // Standard conversion of data
        $this->dataSource->convertToDocument($doc['data'], $this->mongoClientSchema, array());

        return $doc;
    }


    /**
     * Convert given field data from Array.
     * @param $field
     */
    public function clientFromArray( &$field )
    {
        // No action required
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

        // prune fields
        $this->dataSource->prune($out['data'], $this->mongoSchema);

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
        // Do not return data field if set to hidden
        if ($this->flags & self::FLAG_HIDDEN)
        {
            return false;
        }

        // Return the field data
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
     * Is this field hidden from being displayed in Options?
     * @return  bool        True if field is hidden from Client Options displays
     */
    public function isHiddenFromOptions()
    {
        // Check if hidden?
        if ( $this->flags & self::FLAG_HIDDEN )
        {
            return true;
        }

        // Not hidden
        return false;
    }

    /**
     * Check the field name matches the one given
     * @param   string  $name   Field name to check
     * @return  bool            True is name is a match
     */
    public function isName( $name )
    {
        return ($this->fieldSettings['name'] == $name ? true : false);
    }

    /**
     * Check the field label matches the one given
     * @param $label
     * @return  bool            True is name is a match
     */
    public function isLabel( $label )
    {
        return (strtolower($this->fieldSettings['label']) == strtolower($label) ? true : false);
    }


    /**
     * Check if this field should be visible for the chosen $type of display
     * @param   string  $type       Type of view we're checking against
     * @return  bool                True if visible
     */
    public function isVisibleFor( $type='' )
    {
        // Always visible on the standard list
        if ($type == 'list')
        {
            return true;
        }

        // Visible?
        if ( isset($this->fieldSettings['visibility'][$type]) && $this->fieldSettings['visibility'][$type] == true)
        {
            return true;
        }

        // Legacy; if it isn't set, then allow it.
        if (!isset($this->fieldSettings['visibility']) || !isset($this->fieldSettings['visibility'][$type]))
        {
            return true;
        }

        return false;
    }

    /**
     * Subroutine for LogEntity::overwriteWithChanges
     * Should return null if this field cannot overwrite the original log's data
     * Otherwise return the array of overwriting data, for the entire field
     * @param   array       $data       Data from the new log
     * @return  array|null              Field data if writable, or null.
     */
    public function overwriteWithChanges( $data )
    {
        // Hidden fields don't overwrite
        if ($this->flags & self::FLAG_HIDDEN)
        {
            return;
        }

        // Readonly fields don't overwrite
        if ($this->flags & self::FLAG_READONLY)
        {
            return;
        }

        // Overwrite the data
        $this->data = $data;
    }


    /**
     * afterFind callback
     */
    public function afterFind()
    {
        // This space intentionally left blank
    }


    /**
     * beforeSave Callback
     */
    public function beforeSave()
    {
        // This space intentionally left blank
    }


    /**
     * beforeSave callback for Client
     */
    public function clientBeforeSave()
    {
        // Set the MongoID of the field if it doesn't exist, or isn't the right format (eg. new field)
        if (!isset($this->fieldSettings['_id']) || empty($this->fieldSettings['_id']) || strlen($this->fieldSettings['_id']) != 24)
        {
            $this->fieldSettings['_id'] = (string) new \MongoId();
        }
    }


    /**
     * afterFind callback for Client
     */
    public function clientAfterFind()
    {
        // This space intentionally left blank
    }


    /**
     * is this field deleted?
     * @return bool
     */
    public function isDeleted()
    {
        return (isset($this->fieldSettings['deleted']) && $this->fieldSettings['deleted'] == true );
    }


    /**
     * Remove all fields that should be removed, saving the data supplied in $fieldData
     * @param   array       $settings   Field configuration
     * @param   array       $data       Field data to leave alone
     * @return  array                   Corrected settings
     */
    public function removeDeleted( $settings, $data )
    {
        return $settings;
    }


    /**
     * Fetch the Options data for this field
     * @param   array   $log        Log data
     * @return  array|bool          Field options or false if it shouldn't be included.
     */
    public function getOptions( $log=null )
    {

        // If should be deleted and doesn't contain data in this log:
        if ($this->isDeleted() && !isset( $log['fields'][ $this->fieldSettings['_id'] ] ))
        {
            return false;
        }

        // Create a copy of the field options data
        $data = $this->fieldSettings;
        $data['is_hidden'] = $this->isHiddenFromOptions();
        return $data;
    }
}


