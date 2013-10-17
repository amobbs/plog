<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\FieldTypeAbstract;

/**
 * Preslog Field Type: Duration
 * Handles duration fields
 */
class Duration extends FieldTypeAbstract
{

    protected $alias = 'duration';
    protected $name = 'Duration Field (H:M:S)';
    protected $description = 'A field for specifying durations, in H:M:S format.';

    protected $aggregationDetails = array(
        'seconds' => array(
            'dataLocation' => 'seconds',
            'groupBy' => '$sum',
            'aggregate' => true,
            ),
            'minutes' => array(
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


    protected $mongoSchema = array(
        'seconds'  => array('type' => 'int'),
    );

    protected $mongoClientSchema = array(
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
                'name' => 'Total  ' . $fieldName . ' by ' . $name ,
                'id' =>  $fieldName . ':' . $name,
            );
        }

        return $list;
    }

    public function chartDisplay($aggregationType, $data) {
        switch ($aggregationType) {
            case 'seconds':
                return $data;
                break;
            case 'minutes':
                return $data / 60;
                break;
        }

        return $data;
    }
}
