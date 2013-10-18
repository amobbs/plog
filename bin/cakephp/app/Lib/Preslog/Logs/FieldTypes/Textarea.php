<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\FieldTypeAbstract;

/**
 * Preslog Field Type: Textarea
 * Handles text
 */
class Textarea extends FieldTypeAbstract
{

    protected $alias = 'textarea';
    protected $name = 'Multi-Line Text Field';
    protected $description = 'A large text area for multiple lines of text.';

    protected $mongoSchema = array(
        'text'  => array('type' => 'string', 'length'=>65536),      // Arbitrary limit.
    );

    protected $mongoClientSchema = array(
        'placeholder'   => array('type' => 'string', 'length'=>1024),
    );


    /**
     * Validate field data
     * @param   array       $fieldName
     * @param               $data
     * @return  array|void
     */
    public function validate( $data, $fieldName )
    {
        $errors = array();

        $errors[] = 'Herp a derp';

        return $errors;
    }


    /**
     * Check if this fields configuration validates
     * For use by the Admin section when editing client fields.
     */
    public function adminValidates( $data )
    {

    }


    /**
     * Convert for display
     * @param array $data
     */
    public function convertForDisplay( &$data )
    {
        // No action required; text
    }


    protected function defaultConvertToFields( $field )
    {
        return array($this->fieldDetails['label'] => $field['data']['text']);
    }
}