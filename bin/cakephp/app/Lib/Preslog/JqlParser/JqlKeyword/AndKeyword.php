<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 9/2/13
 * Time: 4:11 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Preslog\JqlParser\JqlKeyword;


class AndKeyword extends JqlKeyword{
    public function __construct() {
        parent::__construct(' AND ', ' AND ', '$and');
    }
}