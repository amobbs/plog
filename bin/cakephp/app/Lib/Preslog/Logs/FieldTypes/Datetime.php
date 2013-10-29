<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\FieldTypeAbstract;

/**
 * Preslog Field Type: Datetime
 * Handles DateTime fields
 */
class Datetime extends FieldTypeAbstract
{

    protected $alias = 'datetime';
    protected $name = 'Date and Time field';
    protected $description = 'A field for specifying dates and times.';
    protected $queryFieldType = 'DATE';

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
                'name' => $fieldName . ' by ' . $name,
                'id' =>  $fieldName . ':' . $name,
            );
        }

        return $list;
    }

    public function chartDisplay($data, $aggregationType = 'all') {
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
            case 'all':
                return $data['day'] . '/' . $data['month']. '/' . substr($data['year'], 2);
        }
    }


    protected function defaultConvertToFields( $label, $field )
    {
        return array($label => date('Y-m-d H:i:s', strtotime($field['data']['datetime'])));
    }

}
