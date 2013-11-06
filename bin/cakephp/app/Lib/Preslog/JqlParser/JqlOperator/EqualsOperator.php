<?php
namespace Preslog\JqlParser\JqlOperator;


class EqualsOperator extends JqlOperator {

    public function __construct() {
        parent::_construct(
            '=',
            '=',
            '',
            'EQUALS',
            array(
                'DATE',
                'TEXT',
                'DURATION',
                'SELECT',
                'ID',
            ),
            true);
    }

    public function matches($value1, $value2)
    {
        if (is_numeric($value1) && is_numeric($value2))
        {
            return $value1 == $value2;
        }

        return strtolower($value1) == strtolower($value2);
    }

}