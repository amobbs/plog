<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\JqlParser\JqlOperator\EqualsOperator;
use Preslog\JqlParser\JqlOperator\LikeOperator;
use Preslog\JqlParser\JqlOperator\NotEqualsOperator;


/**
 * Preslog Field Type: Select
 * Handles drop-down select boxes
 */
class Select extends FieldTypeAbstract
{

    protected $alias = 'select';
    protected $name = 'Drop-down Select Box';
    protected $description = 'A drop-down selection box with various preset options.';
    protected $queryFieldType = 'SELECT';


    protected $aggregationDetails = array(
        'select' => array(
            'dataLocation' => 'selected',
            'isTopLevel' => false,
            'groupBy' => array(),
            'aggregate' => false,
        ),
    );

    protected $mongoSchema = array(
        'selected'  => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
    );

    protected $mongoClientSchema = array(
        'options'   => array('type' => 'subCollection', 'schema'=>array(
            '_id'       => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
            'name'      => array('type' => 'string', 'length'=>255),
            'deleted'   => array('type' => 'bool'),
            'order'     => array('type' => 'integer'),
        )),
        'placeholder'   => array('type' => 'string', 'length'=>1024),
    );

    /**
     * @var     array       $options        Field options that may be selected
     */
    protected $options = array();

    public function __construct()
    {
        $this->allowedJqlOperators = array(
            new EqualsOperator(),
            new NotEqualsOperator(),
            new LikeOperator(),
        );
    }


    /***
     * used to create a human readable list for the aggregation details that can be used in the interface
     *
     * @param $fieldName
     *
     * @internal param $fieldId
     * @return array
     */
    public function listDetails($fieldName) {
        $list = array();
        foreach($this->aggregationDetails as $name => $detail) {
            $list[] = array(
                'name' => $fieldName,
                'id' =>  $fieldName . ':' . $name,
            );
        }

        return $list;
    }

    public function chartDisplay($data, $aggregationType = null) {
        switch ($aggregationType) {
            case 'select':
                return $data;
                break;
        }
    }


    /**
     * Initialise the select option with the available fields.
     * @param array $field
     */
    public function setFieldSettings( $field )
    {
        parent::setFieldSettings($field);

        // Convert array to have _id as the key, for lookups
        foreach ($field['data']['options'] as $option)
        {
            $this->options[ $option['_id'] ] = $option;
        }
    }


    /**
     * Validate select field data
     * @return  array|void
     */
    public function validates()
    {
        $errors = array();

        if ($this->isHiddenFromOptions()) {
        	return $errors;
        }

        // Required? Must not be empty
        if ($this->fieldSettings['required'] == true && (!isset($this->data['data']['selected']) || empty($this->data['data']['selected'])))
        {
            return array("This field is required. An option must be selected.");
        }

        // Field may not exist under certain circumstances
        if (isset($this->data['data']['selected']))
        {
            $optionId = $this->data['data']['selected'];

            // Is the selected option in the array?
            if ( !empty($optionId) && !isset($this->options[ $optionId ]) )
            {
                $errors[] = 'The selected option could not be found in the database schema.';
            }
        }

        return $errors;
    }


    /**
     * Convert the Selected ID to a name
     * @param $label
     * @param $field
     * @return array
     */
    protected function defaultConvertToFields( $label, $field )
    {
        $selected = (isset($this->options[ $field['data']['selected'] ]) ? $this->options[ $field['data']['selected'] ]['name'] : '');
        return array($label => $selected);
    }


    /**
     * Before Save
     * - Populate SELECT option _ids if not set (eg. new)
     */
    public function clientBeforeSave()
    {
        // Parent actions
        parent::clientBeforeSave();

        // Set options as array if not already applies.
        if (!is_array($this->fieldSettings['data']['options']))
        {
            $this->fieldSettings['data']['options'] = array();
        }

        // Skim all options
        foreach ($this->fieldSettings['data']['options'] as &$option )
        {
            // Create mongo ID if not set
            if (!isset($option['_id']) || empty($option['_id']) || strlen($option['_id']) != 24)
            {
                $option['_id'] = new \MongoId();
            }
        }
    }


    /**
     * Select fields that have a selection are not deleted
     * @return bool
     */
    public function isDeleted()
    {
        $deleted = parent::isDeleted();

        // always show fields with content
        if ( isset($this->data['data']['selected']) && !empty($this->data['data']['selected']))
        {
            return false;
        }

        return $deleted;
    }


    /**
     * Get Options for this field type
     * - Remove all fields that should be removed, bar any data supplied in $fieldData
     * @param   array       $log        Log we're trying to load
     * @return  array                   Modified Options
     */
    public function getOptions( $log=null )
    {
        $data = parent::getOptions($log);

        // Abort if the field isn't available
        if (!$data)
        {
            return false;
        }

        if (isset($log['fields']))
        {
            // Find this field in the log
            foreach ($log['fields'] as $field)
            {
                // Use $field
                if ($field['field_id'] == $this->fieldSettings['_id'])
                {
                    break;
                }

                // Clear $field if not found.
                unset($field);
            }
        }

        // Modify the field options in $data to REMOVE any fields which are:
        // DELETED and NOT in the $log data.
        foreach ($data['data']['options'] as $key=>$option)
        {
            // Existing log?
            if ( isset($field) && isset($field['data']) )
            {
                // If we find a match, esure we don't remove it from the options available
                if ( $field['data']['selected'] == $option['_id'])
                {
                    continue;
                }
            }

            // Not an existing log. If field deleted..
            if( $option['deleted'] == true )
            {
                unset( $data['data']['options'][$key] );
            }
        }

        // Reformat the array so values are sequential.
        $data['data']['options'] = array_values($data['data']['options']);

        // Done
        return $data;
    }

}