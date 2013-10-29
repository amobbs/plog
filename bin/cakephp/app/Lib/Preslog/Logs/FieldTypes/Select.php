<?php

namespace Preslog\Logs\FieldTypes;

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
            'order'     => array('type' => 'int'),
        )),
        'placeholder'   => array('type' => 'string', 'length'=>1024),
    );

    /**
     * @var     array       $options        Field options that may be selected
     */
    protected $options = array();


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
     * @param   array       $fieldName
     * @param               $data
     * @return  array|void
     */
    public function validate( $data, $fieldName )
    {
        $errors = array();

        $optionId = $data[ $fieldName ]['data']['selected'];

        // Is the selected option in the array?
        if ( !isset($this->options[ $optionId ]) )
        {
            $errors[] = 'The selected option could not be found in the database schema.';
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
        $selected = (isset($this->options[ $field['data']['selected'] ]) ? $this->options[ $field['data']['selected'] ] : '');

        return array($label => $selected);
    }

}
