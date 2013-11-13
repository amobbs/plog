<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\JqlParser\JqlOperator\EqualsOperator;
use Preslog\JqlParser\JqlOperator\LessThanOperator;
use Preslog\JqlParser\JqlOperator\LikeOperator;
use Preslog\JqlParser\JqlOperator\NotEqualsOperator;
use Preslog\Logs\FieldTypes\FieldTypeAbstract;

/**
 * Preslog Field Type: Checkbox
 * Handles checkbox types
 */
class Checkbox extends FieldTypeAbstract
{

    protected $alias = 'checkbox';
    protected $name = 'Check Box';
    protected $description = 'A single checkbox toggle field.';
    protected $queryFieldType = 'CHECKBOX';

    protected $mongoSchema = array(
        'checked'  => array('type' => 'boolean'),
    );

    protected $mongoClientSchema = array(
        'default'   => array('type' => 'boolean'),
    );

    /**
     * @var     array       $options        Field options that may be selected
     */
    protected $options = array();

    public function __construct()
    {
        $this->allowedJqlOperators = array(
            new EqualsOperator(),
            new NotEqualsOperator(),
            new LikeOperator(),
        );
    }


    /**
     * Validate select field data
     * @return  array|void
     */
    public function validates()
    {
        $errors = array();

        return $errors;
    }


    /**
     * Convert the Selected ID to a name
     * @param $label
     * @param $field
     * @return array
     */
    protected function defaultConvertToFields( $label, $field )
    {
        // Set default if not present
        $value = (isset($field['data']['checked']) ? $field['data']['checked'] : $this->fieldSettings['data']['default']);

        // Return value
        return array($label => $value);
    }


    /**
     * Before Save
     * - Populate SELECT option _ids if not set (eg. new)
     */
    public function clientBeforeSave()
    {
        // Parent actions
        parent::clientBeforeSave();
    }


    /**
     * Checkboxes that are checked are not deleted
     * @return bool
     */
    public function isDeleted()
    {
        $deleted = parent::isDeleted();

        // always show fields with content
        if ( isset($this->data['data']['checked']) && !empty($this->data['data']['checked']))
        {
            return false;
        }

        return $deleted;
    }
}
