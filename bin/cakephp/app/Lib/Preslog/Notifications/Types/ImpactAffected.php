<?php

namespace Preslog\Notifications\Types;

use Preslog\Notifications\Types\TypeAbstract;

/**
 * Preslog Notification: On Air Impact affected transmission
 * Sends alerts for:
 * - New Logs
 * - "on-air impact" affected transmission
 */
class ImpactAffected extends TypeAbstract
{
    protected $key  = 'impact-affected';
    protected $name = 'Impact Affected Transmission';

    public $settings = array(
        'email'=>array(
            'subject'=>'Impact Aff',
            'template'=>'impact-affected',
        ),
    );


    /**
     * Check this log is:
     * - On Air Impact affected transmission
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