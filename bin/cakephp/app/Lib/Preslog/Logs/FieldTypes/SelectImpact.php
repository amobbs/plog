<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\Select;

/**
 * Preslog Field Type: SelectImpact
 * Handles drop-down select boxes for the impact field
 */
class SelectImpact extends Select
{

    protected $alias = 'select-impact';
    protected $name = 'Drop-down Select Box for On-Air Impact';
    protected $description = 'A drop-down selection box, including validation between Impact and Duration.';
    protected $aggregationDetails = array(
        'select' => array(
            'dataLocation'  => 'selected',
            'isTopLevel'    => false,
            'groupBy'       => array(),
            'aggregate'     => false,
        ),
    );


    /**
     * Client Schema
     * - Added: Impact sub-field
     * @var array
     */
    protected $mongoClientSchema = array(
        'options'           => array('type'=>'subCollection', 'schema'=>array(
            '_id'               => array('type'=>'string', 'length'=>24, 'mongoType'=>'mongoId'),
            'name'              => array('type'=>'string', 'length'=>255),
            'deleted'           => array('type'=>'bool'),
            'order'             => array('type'=>'integer'),
            'impact'            => array('type'=>'string', 'length'=>'32'),
        )),
        'placeholder'       => array('type'=>'string', 'length'=>1024),
    );


    /**
     * @var array       List of Impact Types
     */
    protected $impacts = array(
        'affected'      =>'Transmission Affected',
        'not-affected'  =>'No Affect',
    );


    /**
     * Fetch properties for this field type
     * @return  array
     */
    public function getPropertyList()
    {
        // Create list
        $return = parent::getPropertyList();
        $return['impacts'] = $this->impacts;

        // Return data
        return $return;
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

}
