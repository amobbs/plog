<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 9/2/13
 * Time: 2:22 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Preslog\JqlParser\JqlOperator;


class InOperator extends JqlOperator {

    /**
     * constructor
     */
    public function __construct() {
        parent::_construct('IN', 'IN', '$in', true);
    }

    public function formatValueForJql($value) {
        $value = str_replace('(', '[', $value);
        $value = str_replace(')', ']', $value);

        return $value;
    }

    public function formatValueForSql($value) {
        $value = str_replace('[', '(', $value);
        $value = str_replace(']', ')', $value);

        return $value;
    }

}