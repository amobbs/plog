<?php

namespace User\Form;

use Zend\InputFilter\InputFilter;

class LostPasswordFilter extends InputFilter
{

    public function __construct()
    {
        $this->add(array(
            'name'       => 'newIdentityVerify',
            'required'   => true,
            'validators' => array(
                array(
                    'name' => 'identical',
                    'options' => array(
                        'token' => 'newIdentity'
                    )
                ),
            ),
        ));
    }
}
