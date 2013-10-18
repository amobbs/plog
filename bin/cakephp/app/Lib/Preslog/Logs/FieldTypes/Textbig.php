<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\Textarea;

/**
 * Preslog Field Type: Textbig
 * Handles text
 */
class Textbig extends Textarea
{

    protected $alias = 'textbig';
    protected $name = 'Large Text Field';
    protected $description = 'A full-width text area for a single line of text.';

    protected $mongoSchema = array(
        'text'  => array('type' => 'string', 'length'=>65536),      // Arbitrary limit.
    );

    protected $mongoClientSchema = array(
        'placeholder'   => array('type' => 'string', 'length'=>1024),
    );


    /**
     * Convert for display
     * @param array $data
     */
    public function convertForDisplay( &$data )
    {
        // No action required; text
    }
}