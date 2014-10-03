<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kieran.yates
 * Date: 8/30/13
 * Time: 10:48 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Preslog\JqlParser\JqlFunction;

class JqlFunction {

    public static function listFunctions() {
        return array(
            'now' => new NowFunction(),
            'startOfDay' => new StartOfDayFunction(),
            'startOfWeek' => new StartOfWeekFunction(),
            'startOfMonth' => new StartOfMonthFunction(),
            'endOfDay' => new EndOfDayFunction(),
            'endOfWeek' => new EndOfWeekFunction(),
            'endOfMonth' => new EndOfMonthFunction(),
            'empty' => new EmptyFunction(),
        );
    }

    /**
     * name of the function used by user to call it
     * @var String
     */
    private $_name;

    /**
     * constructor
     * @param $name
     */
    public function _construct($name) {
        $this->_name = $name;
    }

    /**
     * returns uppercased name
     * @return string
     */
    public function getName() {
        return strtoupper($this->_name);
    }
    /**
     * execute the function
     * @param $args
     *
     * @throws Not Implemented Exception
     */
    public function execute($args = null) {
        throw Exception('Not Implemented');
    }

    public function executeForMongo($args = null)
    {
        return Exception('Not Implemented');
    }

    /**
     * given a string in the format "<timestamp>" [-|+]offset return the actual unix timestamp
     *
     * assumption: there will only be one date literal in any value
     * @param $value
     *
     * @return int
     */
    protected function _convertValueToTimestamp($value) {
        $date = 0;
        //replace ' with " so they can use both types
        $value = str_replace("'", '"', $value);
        if (substr_count($value, '"') > 0)
        {
            $value = str_replace('"', '', $value);
        }

        //if we can convert the string to a time then do it.
        $time = strtotime($value);

        if ($time)
        {
            $date = $time;
        }

        if ($date == 0) $date = new \DateTime('now', new \DateTimeZone('UTC'));

        // $date = $this->_evaluateInterval($value, $date);
        return $date;
    }

    protected function _evaluateInterval($string, $date) {
        $sign = '';
        if (substr($string, 0, 1) == '-') {
            $sign = '-';
            $string = substr($string, 1);
        }

        $string = strtolower($string);

        $string = preg_replace('%(\d+)s%', $sign . '${1}seconds', $string);
        $string = preg_replace('%(\d+)min%', $sign . '${1}minutes', $string);
        $string = preg_replace('%(\d+)h%', $sign . '${1}hours', $string);
        $string = preg_replace('%(\d+)d%', $sign . '${1}days', $string);
        $string = preg_replace('%(\d+)w%', $sign . '${1}weeks', $string);
        $string = preg_replace('%(\d+)mon%', $sign . '${1}months', $string);
        $string = preg_replace('%(\d+)y%', $sign . '${1}years', $string);

        return strtotime($string, $date);
    }
}