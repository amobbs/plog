<?php

namespace Preslog\Notifications\Types;

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