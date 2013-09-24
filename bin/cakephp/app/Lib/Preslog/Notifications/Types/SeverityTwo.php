<?php

namespace Preslog\Notifications\Types;

use Preslog\Notifications\Types\TypeAbstract;

/**
 * Preslog Notification: Severity 2
 * Sends alerts for:
 * - New logs only
 * - Severity 2 logs
 * - Email only
 */
class SeverityTwo extends TypeAbstract
{
    protected $key  = 'severity-two';
    protected $name = 'Severity 2';

    public $settings = array(
        'email'=>array(
            'subject'=>'Sev 2',
            'template'=>'severity-two',
        )
    );


    /**
     * Check this log is:
     * - Severity Two
     * - New
     * @param   array   $log
     * @return  bool
     */
    public function checkCriteria( $log )
    {
        // TODO

        // Validate: New log?
        if (false)
        {
            return false;
        }

        // Validate: Severity Two?
        if (false)
        {
            return false;
        }

        // Pass
        return true;
    }
}