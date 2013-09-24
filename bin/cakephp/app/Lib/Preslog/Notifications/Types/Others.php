<?php

namespace Preslog\Notifications\Types;

use Preslog\Notifications\Types\TypeAbstract;

/**
 * Preslog Notification: Other Types
 * Sends notifications for:
 * - New Logs
 * - Not Severity 1 or 2
 * - Email only
 */
class Others extends TypeAbstract
{
    protected $key  = 'other';
    protected $name = 'Everything else';

    public $settings = array(
        'email'=>array(
            'subject'=>'Others',
            'template'=>'other',
        ),
    );

    /**
     * Check this log is:
     * - Not Severity One OR Severity Two
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

        // Validate: Sev1 or Sev2?
        if (true)
        {
            return false;
        }

        // Pass
        return true;
    }
}