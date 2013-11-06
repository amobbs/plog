<?php
namespace Preslog\JqlParser\JqlOperator;


class NotEqualsOperator extends JqlOperator {

    public function __construct() {
        parent::_construct(
            '!=',
            '!=',
            '$not',
            'NOT EQUALS',
            array(
                'DATE',
                'TEXT',
                'DURATION',
                'SELECT',
            ),
            false);
    }

    public function matches($value1, $value2)
    {
        if (is_numeric($value1) && is_numeric($value2))
        {
            return $value1 != $value2;
        }

        if ( is_array($value2) )
        {
            $value2 = array_values($value2)[0];
        }

        return strtolower($value1) != strtolower($value2);
    }

}