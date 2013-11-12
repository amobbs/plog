<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 8/30/13
 * Time: 10:52 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Preslog\JqlParser\JqlFunction;


use MongoDate;

class NowFunction extends JqlFunction{

    /**
     * construtor
     */
    public function __construct() {
        parent::_construct('now');
    }

    /**
     * returns current server time as unix time stamp
     *
     * @param $args - no arguments
     *
     * @return \MongoDate|void
     */
    public function execute($args = null) {
        return time();
    }

    public function executeForMongo($args = null)
    {
        return new MongoDate($this->execute($args));
    }

}