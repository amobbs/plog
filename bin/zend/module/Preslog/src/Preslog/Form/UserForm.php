<?php

/**
 * User Form, for User Details
 */

namespace Preslog\Form;

use Preslog\Form\AbstractRestForm;

class UserForm extends AbstractRestForm
{
    // List of fields
    protected $fieldList = array(
        'id',
        'firstName',
        'lastName',
        'role',
        'company',
        'phoneNumber',
        'email',
    );

}