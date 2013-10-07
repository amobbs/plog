<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 8/30/13
 * Time: 11:26 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Preslog\JqlParser\JqlOperator;


class LikeOperator extends JqlOperator{

    public function _construct() {
        parent::_construct('~', 'LIKE', '', true);
    }

    public function formatValueForSql($value) {
        return '%' . $value . '%';
    }
}