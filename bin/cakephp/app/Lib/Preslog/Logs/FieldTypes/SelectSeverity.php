<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\Select;

/**
 * Preslog Field Type: SelectSeverity
 * Handles drop-down select boxes for the seveirty field
 */
class SelectSeverity extends Select
{

    protected $alias = 'select-severity';
    protected $name = 'Drop-down Select Box for Severity';
    protected $description = 'A drop-down selection box, including validation between Severity and Duration.';

    protected $aggregationDetails = array(
        'select' => array(
            'dataLocation' => 'selected',
            'groupBy' => array(),
            'aggregate' => false,
        ),
    );


    /**
     * @var array   Severities type list
     */
    protected $severities = array(
        'level-1'      =>'Level 1',
        'level-2'      =>'Level 2',
        'level-3'    =>'Level 3',
        'level-4'     =>'Level 4',
        'reported'          =>'Reported Only'
    );


    /**
     * Fetch properties for this field type
     * @return  array
     */
    public function getPropertyList()
    {
        // Fetch standard list
        $return = parent::getPropertyList();
        $return['severities'] = $this->severities;

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
