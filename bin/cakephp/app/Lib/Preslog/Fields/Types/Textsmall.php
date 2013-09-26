<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\Textarea;

/**
 * Preslog Field Type: Textsmall
 * Handles text
 */
class Textsmall extends Textarea
{
    protected $alias = 'textsmall';
    protected $name = 'Small Text Field';
    protected $description = 'A small text area for a single short piece of text.';

}