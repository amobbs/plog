<?php

namespace Preslog\Fields\Types;

use Preslog\Fields\Types\TypeAbstract;

/**
 * Preslog Field Type: Textarea
 * Handles text
 */
class Textarea extends TypeAbstract
{

    protected $alias = 'textarea';
    protected $name = 'Multi-Line Text Field';
    protected $description = 'A large text area for multiple lines of text.';


    /**
     * Check if this field validates for user submission
     */
    public function validates( $data )
    {

    }


    /**
     * Check if this fields configuration validates
     * For use by the Admin section when editing client fields.
     */
    public function adminValidates( $data )
    {

    }

}