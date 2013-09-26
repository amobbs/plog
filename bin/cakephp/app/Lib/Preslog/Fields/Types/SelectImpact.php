<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: SelectImpact
 * Handles drop-down select boxes for the impact field
 */
class SelectImpact extends TypeAbstract
{

    protected $alias = 'select-impact';
    protected $name = 'Drop-down Select Box for On-Air Impact';
    protected $description = 'A drop-down selection box, including validation between Impact and Duration.';
}
