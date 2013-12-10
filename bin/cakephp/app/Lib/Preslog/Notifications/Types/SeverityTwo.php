<?php

namespace Preslog\Notifications\Types;
use Preslog\Logs\FieldTypes\SelectSeverity;


/**
 * Preslog Notification: Severity 2
 * Sends alerts for:
 * - New logs only
 * - Severity 2 logs
 * - Email only
 */
class SeverityTwo extends SeverityOne
{
    protected $key  = 'severity-two';
    protected $name = 'Severity 2';
    protected $priority = 2;

    public $settings = array(
        'email'=>array(
            'template'=>'severity',
        )
    );


    /**
     * Check this log is:
     * - Severity Two
     * - New
     * @return  bool
     */
    public function checkCriteria()
    {
        // Validate: Must be a new log
        $field = $this->log->getFieldByName('version');
        if ( !$field instanceof Loginfo)
        {
            return false;
        }

        $fields = $field->convertToFields();
        $version = ($field ? $fields['Version']: 'ERROR');
        if ($version != 1)
        {
            return false;
        }

        // Validate: Must be a Severity two log
        $field = $this->log->getFieldByName('severity');
        if ( !$field instanceof SelectSeverity)
        {
            return false;
        }

        $level = ($field ? $field->getSelectedSeverityLevel() : 'ERROR');
        if ( 'level-2' != $level  )
        {
            return false;
        }

        // Pass
        return true;
    }

}