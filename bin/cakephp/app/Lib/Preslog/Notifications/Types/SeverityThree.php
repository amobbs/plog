<?php

namespace Preslog\Notifications\Types;

use Preslog\Notifications\Types\TypeAbstract;

/**
 * Preslog Notification: Severity 3
 * Sends alerts for:
 * - New logs only
 * - Severity 2 logs
 * - Email only
 */
class SeverityThree extends TypeAbstract
{
    protected $key  = 'severity-three';
    protected $name = 'Severity 3';

    public $settings = array(
        'email'=>array(
            'subject'=>'Sev 3',
            'template'=>'severity-three',
        )
    );


    /**
     * Check this log is:
     * - Severity Three
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