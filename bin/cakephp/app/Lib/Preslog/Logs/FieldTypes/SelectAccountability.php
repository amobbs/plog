<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\Select;

/**
 * Preslog Field Type: SelectAccountability
 * Handles drop-down select boxes for the accountability field
 */
class SelectAccountability extends Select
{

    protected $alias = 'select-accountability';
    protected $name = 'Drop-down Select Box for Accountability';
    protected $description = 'A drop-down selection box, including specific Accountability search parameters.';

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
     * - Added: Accountability sub-field
     * @var array
     */
    protected $mongoClientSchema = array(
        'options'           => array('type' => 'subCollection', 'schema'=>array(
            '_id'               => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
            'name'              => array('type' => 'string', 'length'=>255),
            'deleted'           => array('type' => 'bool'),
            'order'             => array('type' => 'integer'),
            'accountability'    => array('type'=>'string', 'length'=>'32'),
        )),
        'placeholder'       => array('type' => 'string', 'length'=>1024),
    );


    /**
     * @var array   Severities type list
     */
    protected $accountabilities = array(
        'client'        =>'Client',
        'mediahub'      =>'MediaHub',
        'shared'        =>'Shared',
        'third-party'   =>'Third Party',
        'other'         =>'Other',
    );


    /**
     * Fetch properties for this field type
     * @return  array
     */
    public function getPropertyList()
    {
        // Fetch standard list
        $return = parent::getPropertyList();
        $return['accountabilities'] = $this->accountabilities;

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


    /**
     * Validation
     * @return array|void
     */
    public function validates()
    {
        // parent validation
        $errors = parent::validates();

        return $errors;
    }
}
