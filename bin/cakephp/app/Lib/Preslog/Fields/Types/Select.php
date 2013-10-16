<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Select
 * Handles drop-down select boxes
 */
class Select extends TypeAbstract
{

    protected $alias = 'select';
    protected $name = 'Drop-down Select Box';
    protected $description = 'A drop-down selection box with various preset options.';

    protected $aggregationDetails = array(
        'select' => array(
            'dataLocation' => 'selected',
            'groupBy' => array(
                'select' => ''
            ),
            'aggregate' => false,
        ),
    );

    protected $mongoSchema = array(
        'selected'   => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
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
