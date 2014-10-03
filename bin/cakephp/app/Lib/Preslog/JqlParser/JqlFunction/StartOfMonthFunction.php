<?php

namespace Preslog\JqlParser\JqlFunction;


use MongoDate;

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
        $tmpDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $date = $tmpDate->getTimestamp();

        if ($args != null && !empty($args)) {
            $date = $this->_convertValueToTimestamp($args);
        }

        //use datetime object with utc timezone
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $dateTime->setDate(date('Y', $date), date('n', $date), 1);
        $dateTime->setTime(0, 0, 0);

        return $dateTime->getTimestamp();
    }


    public function executeForMongo($args = null)
    {
        return new MongoDate($this->execute($args));
    }
}