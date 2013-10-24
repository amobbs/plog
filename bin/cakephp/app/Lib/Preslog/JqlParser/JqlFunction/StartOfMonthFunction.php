<?php

namespace Preslog\JqlParser\JqlFunction;


class StartOfMonthFunction extends JqlFunction {
    /**
     * construtor
     */
    public function __construct() {
        parent::_construct('startofmonth');
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
        $date = mktime(0, 0, 0, date('n'), date('j'), date('y'));
        if ($args != null && !empty($args)) {
            $date = $this->_convertValueToTimestamp($args);
        }

        return mktime(0, 0, 0, date('n', $date), 1, date('y', $date));
    }
}