<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: SelectSeverity
 * Handles drop-down select boxes for the seveirty field
 */
class SelectSeverity extends TypeAbstract
{

    protected $alias = 'select-seveirty';
    protected $name = 'Drop-down Select Box for Severity';
    protected $description = 'A drop-down selection box, including validation between Severity and Duration.';
}
