<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\JqlParser\JqlOperator\EqualsOperator;
use Preslog\JqlParser\JqlOperator\LessThanOperator;
use Preslog\JqlParser\JqlOperator\LikeOperator;
use Preslog\JqlParser\JqlOperator\NotEqualsOperator;
use Preslog\Logs\FieldTypes\FieldTypeAbstract;

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
        else
        {
            //$errors[] = "Must not be empty.";
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
}
