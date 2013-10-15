<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Datetime
 * Handles DateTime fields
 */
class Datetime extends TypeAbstract
{

    protected $alias = 'datetime';
    protected $name = 'Date and Time field';
    protected $description = 'A field for specifying dates and times.';

    protected $aggregationDetails = array(
        'hour' => array(
            'dataLocation' => 'datetime',
            'groupBy' => array(
                'hour' => '$hour',
            ),
            'aggregate' => false,
        ),
        'day' => array(
            'dataLocation' => 'datetime',
            'groupBy' => array(
                'month' => '$month',
                'day' => '$dayOfMonth',
            ),
            'aggregate' => false,
        ),
        'month' => array(
            'dataLocation' => 'datetime',
            'groupBy' => array(
                'year' => '$year',
                'month' => '$month',
            ),
            'aggregate' => false,
        ),
    );

    protected $mongoSchema = array(
        'datetime'          => array('type' => 'datetime', 'mongoType'=>'mongoDate'),
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
                'name' => $fieldName . ' by ' . $name,
                'id' =>  $fieldName . ':' . $name,
            );
        }

        return $list;
    }

    public function chartDisplay($aggregationType, $data) {
        switch ($aggregationType) {
            case 'hour':
                return $data['hour'];
                break;
            case 'day':
                return $data['day'] . '/' . $data['month'];
                break;
            case 'month':
                return $data['month'] . '/' . substr($data['year'], 2);
                break;
        }
    }
}
