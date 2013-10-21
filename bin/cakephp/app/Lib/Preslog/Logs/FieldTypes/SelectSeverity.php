<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\Select;

/**
 * Preslog Field Type: SelectSeverity
 * Handles drop-down select boxes for the seveirty field
 */
class SelectSeverity extends Select
{

    protected $alias = 'select-seveirty';
    protected $name = 'Drop-down Select Box for Severity';
    protected $description = 'A drop-down selection box, including validation between Severity and Duration.';

    protected $aggregationDetails = array(
        'select' => array(
            'dataLocation' => 'selected',
            'groupBy' => array(),
            'aggregate' => false,
        ),
    );

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