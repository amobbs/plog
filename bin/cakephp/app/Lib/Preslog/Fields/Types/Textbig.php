<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\Textarea;

/**
 * Preslog Field Type: Textbig
 * Handles text
 */
class Textbig extends Textarea
{

    protected $alias = 'textbig';
    protected $name = 'Large Text Field';
    protected $description = 'A full-width text area for a single line of text.';

}