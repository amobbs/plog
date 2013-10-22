<?php

namespace Preslog\JqlParser\JqlFunction;


class EmptyFunction extends JqlFunction {
    /**
     * construtor
     */
    public function __construct() {
        parent::_construct('empty');
    }


    public function execute($args = null) {
        return '';
    }
}