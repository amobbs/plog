<?php

namespace Preslog\Notifications\Types;

use Preslog\Notifications\Types\TypeAbstract;

/**
 * Preslog Notification: Severity 1
 * Sends alerts for:
 * - New Logs
 * - Severity 1 only
 * - Via email and SMS
 */
class SeverityOne extends TypeAbstract
{
    protected $key  = 'severity-one';
    protected $name = 'Severity 1';

    public $settings = array(
        'email'=>array(
            'subject'=>'Sev One',
            'template'=>'severity-one',
        ),
        'sms'=>array(
            'template'=>'severity-one',
        ),
    );


    /**
     * Check this log is:
     * - Severity One
     * - New
     * @param   array   $log
     * @return  bool
     */
    public function checkCriteria( $log )
    {
        // TODO

        // Validate: new log?
        if (false)
        {
            return false;
        }

        // Validate: Severity one log?
        if (false)
        {
            return false;
        }

        // Pass
        return true;
    }
}