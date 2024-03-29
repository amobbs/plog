<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 9/2/13
 * Time: 3:07 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Preslog\JqlParser\JqlOperator;


class LessThanOperator extends JqlOperator {

    /**
     * constructor
     */
    public function __construct() {
        parent::_construct(
            '<',
            '<',
            '$lte',
            'LESS THAN',
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

        return strtolower($value1) < strtolower($value2);
    }

}