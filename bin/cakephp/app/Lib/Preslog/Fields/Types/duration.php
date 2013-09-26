<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Duration
 * Handles duration fields
 */
class Duration extends TypeAbstract
{

    protected $alias = 'duration';
    protected $name = 'Duration Field (H:M:S)';
    protected $description = 'A field for specifying durations, in H:M:S format.';
}
