<?php

namespace Preslog\JqlParser\JqlFunction;


use MongoDate;
use Zend\Stdlib\DateTime;

class EndOfMonthFunction extends JqlFunction {
    /**
     * construtor
     */
    public function __construct() {
        parent::_construct('endofmonth');
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

        // Use datetime object with utc timezone
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $dateTime->setDate(date('Y', $date), date('n', $date), date('t', $date));

        // Time in database is off by 11 hours because of timezones so to make sure we display the correct data we need to offset the time by -11 hours
        $dateTime->setTime(12, 59, 59);

        return $dateTime->getTimestamp();
    }


    public function executeForMongo($args = null)
    {
        return new MongoDate($this->execute($args));
    }
}