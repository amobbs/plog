<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Loginfo
 * Handles log information for general logs. Used just to control this items position in the log.
 */
class Loginfo extends TypeAbstract
{

    protected $alias = 'loginfo';
    protected $name = 'Log Information';
    protected $description = 'A block that contains general log information.';
}
