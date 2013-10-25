<?php
namespace Preslog\JqlParser\JqlOperator;


class NotEqualsOperator extends JqlOperator {

    public function __construct() {
        parent::_construct('!=', '!=', '$not', false);
    }

    public function matches($value1, $value2)
    {
        if (is_numeric($value1) && is_numeric($value2))
        {
            return $value1 != $value2;
        }

        return strtolower($value1) != strtolower($value2);
    }

}