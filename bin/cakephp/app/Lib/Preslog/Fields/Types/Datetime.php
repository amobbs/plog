<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Datetime
 * Handles DateTime fields
 */
class Datetime extends TypeAbstract
{

    protected $alias = 'datetime';
    protected $name = 'Date and Time field';
    protected $description = 'A field for specifying dates and times.';
}
