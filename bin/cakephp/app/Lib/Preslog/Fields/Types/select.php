<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Select
 * Handles drop-down select boxes
 */
class Select extends TypeAbstract
{

    protected $alias = 'select';
    protected $name = 'Drop-down Select Box';
    protected $description = 'A drop-down selection box with various preset options.';
}
