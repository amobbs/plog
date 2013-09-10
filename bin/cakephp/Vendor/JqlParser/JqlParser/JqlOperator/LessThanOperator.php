<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 9/2/13
 * Time: 3:07 PM
 * To change this template use File | Settings | File Templates.
 */

namespace JqlParser\JqlOperator;


class LessThanOperator extends JqlOperator {

    /**
     * constructor
     */
    public function __construct() {
        parent::_construct('<', '<', '$lt', false);
    }

}