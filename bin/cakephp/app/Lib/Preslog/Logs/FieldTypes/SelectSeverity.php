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
                    if ($durationField->data['data']['duration'] <= 10)
                    {
                        $errors[] = 'Severity 1 can only be selected for faults with duration greater than 10 seconds.';
                    }
                }

                // Validate: Duration must be < 10s
                if ($option['severity'] == 'level-2')
                {
                    if ($durationField->data['data']['duration'] <= 10)
                    {
                        $errors[] = 'Severity 2 can only be selected for faults with duration 10 seconds or less.';
                    }
                }


                // Validate: Duration must not be set, or 0
                if ($option['severity'] == 'reported')
                {
                    if ($durationField->data['data']['duration'] > 0)
                    {
                        $errors[] = 'Reported events must have a duration of zero seconds.';
                    }
                }
            }
        }

        return $errors;
    }
}
