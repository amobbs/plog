<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\Textarea;

/**
 * Preslog Field Type: Textsmall
 * Handles text
 */
class Textsmall extends Textarea
{
    protected $alias = 'textsmall';
    protected $name = 'Small Text Field';
    protected $description = 'A small text area for a single short piece of text.';

    protected $mongoSchema = array(
        'text'  => array('type' => 'string', 'length'=>65536),      // Arbitrary limit.
    );

    protected $mongoClientSchema = array(
        'placeholder'   => array('type' => 'string', 'length'=>1024),
    );
}