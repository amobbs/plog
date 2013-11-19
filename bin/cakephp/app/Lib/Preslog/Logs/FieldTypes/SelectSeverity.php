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
            'dataLocation'  => 'selected',
            'isTopLevel'    => false,
            'groupBy'       => array(),
            'aggregate'     => false,
        ),
    );

    /**
     * Client Schema
     * - Added: Severity sub-field
     * @var array
     */
    protected $mongoClientSchema = array(
        'options'           => array('type'=>'subCollection', 'schema'=>array(
            '_id'               => array('type'=>'string', 'length'=>24, 'mongoType'=>'mongoId'),
            'name'              => array('type'=>'string', 'length'=>255),
            'deleted'           => array('type'=>'bool'),
            'order'             => array('type'=>'integer'),
            'severity'          => array('type'=>'string', 'length'=>'32'),
        )),
        'placeholder'       => array('type'=>'string', 'length'=>1024),
    );


    /**
     * @var array   Severities type list
     */
    protected $severities = array(
        'level-1'      =>'Level 1',
        'level-2'      =>'Level 2',
        'level-3'      =>'Level 3',
        'level-4'      =>'Level 4',
        'reported'     =>'Reported Only'
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


    /**
     * Get the severity level reference for the selected item
     * @return  string      Severity Level
     */
    public function getSelectedSeverityLevel()
    {
        // Must have a selection
        if (!isset($this->data['data']['selected']) || empty( $this->data['data']['selected']))
        {
            return false;
        }

        // Opt must exist
        if (!isset($this->options[ $this->data['data']['selected'] ]))
        {
            return fasle;
        }

        // Return the severity
        return $this->options[ $this->data['data']['selected'] ]['severity'];
    }


    /**
     * Cross-field validation of Severity against Duration
     * - if Severity 1, Duration must be > 10s
     * - if Severity 2, Duration must be < 20s
     * - if Reported, no duration
     * @return array|void
     */
    public function validates()
    {
        // parent validation
        $errors = parent::validates();

        if (!isset($this->data['data']['selected']) || empty($this->data['data']['selected']))
        {
            return array("You must select an option.");
        }


        // Fetch duration field
        $durationField = $this->log->getFieldByName('duration');
        if ( is_object($durationField) )
        {
            $option = null;

            // Find the selected option
            foreach ( $this->fieldSettings['data']['options'] as $opt )
            {
                if ($this->data['data']['selected'] == $opt['_id'])
                {
                    $option = $opt;
                    break;
                }
            }

            // If option found, and severity check exists on the field
            if (is_array($option) && isset($option['severity']))
            {

                // Validate: Duration must be > 10s
                if ($option['severity'] == 'level-1')
                {
                    if ($durationField->data['data']['seconds'] < 10)
                    {
                        $errors[] = 'Severity 1 can only be selected for faults with duration 10 seconds or greater.';
                    }
                }

                // Validate: Duration must be < 10s
                if ($option['severity'] == 'level-2')
                {
                    if ($durationField->data['data']['seconds'] >= 10)
                    {
                        $errors[] = 'Severity 2 can only be selected for faults with duration less than 10 seconds.';
                    }
                }


                // Validate: Duration must not be set, or 0
                if ($option['severity'] == 'reported')
                {
                    if ($durationField->data['data']['seconds'] > 0)
                    {
                        $errors[] = 'Reported events must have a duration of zero seconds.';
                    }
                }
            }
        }

        return $errors;
    }
}
