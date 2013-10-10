<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Duration
 * Handles duration fields
 */
class Duration extends TypeAbstract
{

    protected $alias = 'duration';
    protected $name = 'Duration Field (H:M:S)';
    protected $description = 'A field for specifying durations, in H:M:S format.';

    protected $aggregationDetails = array(
        'sum' => array(
            'dataLocation' => 'seconds',
            'groupBy' => '$sum',
            'aggregate' => true,
            ),
        );
//        'sum' => array(
//            'fieldName' => 'duration',
//            '$project' => array(),
//            '$group' => array(
//                'sum' => array('operation' => '$sum', 'data' => '$fields.data.seconds'),
//            )
//        ),


    /***
     * used to create a human readable list for the aggregation details that can be used in the interface
     *
     * @param $fieldName
     * @param $fieldId
     * @return array
     */
    public function listDetails($fieldName) {
        $list = array();
        foreach($this->aggregationDetails as $name => $detail) {
            $list[] = array(
                'name' => $name . '  ' . $fieldName,
                'id' =>  $fieldName . ':' . $name,
            );
        }

        return $list;
    }
}
