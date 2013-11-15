<?php

namespace Preslog\Notifications\Types;


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