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
        parent::_construct('>', '>', '$gt', false);
    }
}