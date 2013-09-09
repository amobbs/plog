<?php

/**
 * Abstract Rest form
 */

namespace Preslog\Form;

use Zend\Form\Form;

class AbstractRestForm extends Form
{
    /**
     * @var array   List of fields in the entity/form
     */
    protected $fieldList = array();


    /**
     * Build for the form
     * @param null $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        // Populate fields
        foreach ($this->fieldList as $field) {
            $this->add(array('name' => $field));
        }
    }
}