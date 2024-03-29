<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\JqlParser\JqlOperator\EqualsOperator;
use Preslog\JqlParser\JqlOperator\LikeOperator;
use Preslog\JqlParser\JqlOperator\NotEqualsOperator;
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
    protected $queryFieldType = 'TEXT';

    protected $mongoSchema = array(
        'text'  => array('type' => 'string', 'length'=>65536),      // Arbitrary limit.
    );

    protected $mongoClientSchema = array(
        'placeholder'   => array('type' => 'string', 'length'=>1024),
    );


    public function __construct()
    {
        $this->allowedJqlOperators = array(
            new EqualsOperator(),
            new NotEqualsOperator(),
            new LikeOperator(),
        );
    }

    /**
     * Validate field data
     * @return  array|void
     */
    public function validates()
    {
        $errors = array();

        // Required? Must not be empty
        if ($this->fieldSettings['required'] == true && (!isset($this->data['data']['text']) || empty($this->data['data']['text'])))
        {
            $errors[] = ("This field is required. You must enter some text content.");
        }

        return $errors;
    }


    /**
     * Check if this fields configuration validates
     * For use by the Admin section when editing client fields.
     */
    public function adminValidates( $data )
    {

    }


    protected function defaultConvertToFields( $label, $field )
    {
        return array($label => $field['data']['text']);
    }

    /**
     * Text fields that contain data are not deleted
     * @return bool
     */
    public function isDeleted()
    {
        $deleted = parent::isDeleted();

        // always show fields with content
        if ( isset($this->data['data']['text']) && !empty($this->data['data']['text']))
        {
            return false;
        }

        return $deleted;
    }

}