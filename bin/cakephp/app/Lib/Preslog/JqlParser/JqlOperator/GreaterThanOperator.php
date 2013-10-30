<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 8/30/13
 * Time: 10:38 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Preslog\JqlParser\JqlOperator;


class GreaterThanOperator extends JqlOperator {

    /**
     * constructor
     */
    public function __construct() {
        parent::_construct(
            '>',
            '>',
            '$gt',
            'GREATER THAN',
            array(
                'DATE',
                'DURATION',
            ),
            false);
    }

    public function matches($value1, $value2)
    {
        if (is_numeric($value1) && is_numeric($value2))
        {
            return $value1 > $value2;
        }

        return strtolower($value1) > strtolower($value2);
    }

}