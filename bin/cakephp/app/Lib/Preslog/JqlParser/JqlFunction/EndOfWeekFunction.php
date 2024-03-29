<?php

namespace Preslog\JqlParser\JqlFunction;


use MongoDate;

class EndOfWeekFunction extends JqlFunction {
    /**
     * construtor
     */
    public function __construct() {
        parent::_construct('endofweek');
    }

    /**
     * given a string (Y-m-d H:s) find the start of that day (00:00)
     * if no arg is provided then use todays date
     *
     * @param null $args
     *
     * @return int|void
     */
    public function execute($args = null) {
        $date = mktime(23, 59, 59, date('n'), date('j'), date('y'));
        if ($args != null || empty($args)) {
            $date = $this->_convertValueToTimestamp($args);
        }

        $dayOfWeek = date('w', $date);

        return mktime(23, 59, 59, date('n', $date), date('j', $date) + (6 - $dayOfWeek), date('y', $date));
    }


    public function executeForMongo($args = null)
    {
        return new MongoDate($this->execute($args));
    }
}