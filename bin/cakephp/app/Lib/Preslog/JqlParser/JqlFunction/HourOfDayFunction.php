<?php

namespace Preslog\JqlParser\JqlFunction;

class HourOfDayFunction extends JqlFunction
{
    /**
     * construtor
     */
    public function __construct() {
        parent::_construct('timeofday');
    }


    public function execute($args = null) {
        if ( $args != null && !empty($args) )
        {
            $time = $args;
        }

        return array(

        );

    }
}