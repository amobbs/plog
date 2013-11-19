<?php

namespace Preslog\Notifications\Types;
use Preslog\Logs\FieldTypes\SelectSeverity;


/**
 * Preslog Notification: Severity 3
 * Sends alerts for:
 * - New logs only
 * - Severity 2 logs
 * - Email only
 */
class SeverityThree extends SeverityOne
{
    protected $key  = 'severity-three';
    protected $name = 'Severity 3';

    public $settings = array(
        'email'=>array(
            'template'=>'severity',
        )
    );


    /**
     * Check this log is:
     * - Severity Three
     * - New
     * @return  bool
     */
    public function checkCriteria()
    {
        // Validate: Must be a new log
        $field = $this->log->getFieldByName('version');
        if ( !$field instanceof LogInfo)
        {
            return false;
        }

        $fields = $field->convertToFields();
        $version = ($field ? $fields['Version']: 'ERROR');
        if ($version != 1)
        {
            return false;
        }

        // Validate: Must be a Severity three log
        $field = $this->log->getFieldByName('severity');
        if ( !$field instanceof SelectSeverity)
        {
            return false;
        }

        $level = ($field ? $field->getSelectedSeverityLevel() : 'ERROR');
        if ( 'level-3' != $level  )
        {
            return false;
        }

        // Pass
        return true;
    }

}