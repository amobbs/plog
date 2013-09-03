<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 9/2/13
 * Time: 2:22 PM
 * To change this template use File | Settings | File Templates.
 */

namespace JqlParser\JqlOperator;


class NotInOperator extends JqlOperator {

    /**
     * constructor
     */
    public function __construct() {
        parent::_construct('NOT IN', 'NOT IN', '', true);
    }
}