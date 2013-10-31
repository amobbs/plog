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

    public function __construct() {
        parent::_construct(
            '~',
            'LIKE',
            '',
            'IS LIKE',
            array(
                'TEXT',
            ),
            true);
    }

    public function formatValueForSql($value) {
        return '' . $value . '';
    }

    public function matches($value1, $value2)
    {
        return strpos(strtolower($value1), strtolower($value2)) !== false;
    }
}