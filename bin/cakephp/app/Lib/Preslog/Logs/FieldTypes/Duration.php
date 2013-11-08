<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\JqlParser\JqlOperator\EqualsOperator;
use Preslog\JqlParser\JqlOperator\GreaterThanOperator;
use Preslog\JqlParser\JqlOperator\LessThanOperator;
use Preslog\JqlParser\JqlOperator\NotEqualsOperator;
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
    protected $queryFieldType = 'DURATION';

    //describes fields and data to use during aggregation in mongo
    protected $aggregationDetails = array(
        'seconds' => array(
            'dataLocation' => 'seconds',
            'isTopLevel' => false,
            'groupBy' => '$sum',
            'aggregate' => true,
        ),
        'minutes' => array(
            'dataLocation' => 'seconds',
            'isTopLevel' => false,
            'groupBy' => '$sum',
            'aggregate' => true,
        ),
    );

    protected $mongoSchema = array(
        'seconds'  => array('type' => 'integer'),
    );

    protected $mongoClientSchema = array(
    );

    public function __construct()
    {
        $this->allowedJqlOperators = array(
            new EqualsOperator(),
            new NotEqualsOperator(),
            new GreaterThanOperator(),
            new LessThanOperator(),
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
                'name' => 'Total  ' . $fieldName . ' by ' . $name ,
                'id' =>  $fieldName . ':' . $name,
            );
        }

        return $list;
    }

    public function chartDisplay($data, $aggregationType = 'minutes') {
        $decimalPlaces = pow(10, 2);
        switch ($aggregationType) {
            case 'seconds':
                return $data;
                break;
            case 'minutes':
                return  floor($data / 60 * $decimalPlaces) / $decimalPlaces;
                break;
        }

        return $data;
    }


    /**
     * Convert for display
     * @param array $data
     */
    public function convertForDisplay( &$data )
    {
        // Nothing to do
        // ['data']['seconds'] will show as duration in seconds
    }


    protected function defaultConvertToFields( $label, $field )
    {
        $seconds = $field['data']['seconds'];

        $units = array(
            604800 => 'w',
            86400 => 'd',
            3600 => 'h',
            60 => 'm',
            1 => 's'
        );

        $result = array();

        foreach ($units as $divisor => $unitName) {
            $units = intval($seconds / $divisor);

            if ($units) {
                $seconds %= $divisor;
                $result[] = "$units$unitName";
            }
        }

        $time = implode(' ', $result);

        return array($label => $time);
    }


    /**
     * Validate duration
     * @return array|bool
     */
    public function validates()
    {
        $errors = array();

        // Must not be empty
        if (!isset($this->data['data']['seconds']) || empty($this->data['data']['seconds']))
        {
            return array("Duration must not be empty.");
        }

        // Seconds must be numeric
        if (!is_numeric($this->data['data']['seconds']))
        {
            $errors[] = "Durations must be supplied as numeric values in seconds. Received '{$this->data['data']['seconds']}'.";
        }

        return $errors;
    }
}
