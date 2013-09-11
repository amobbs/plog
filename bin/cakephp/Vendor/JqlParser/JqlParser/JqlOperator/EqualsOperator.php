<?php
namespace JqlParser\JqlOperator;


class EqualsOperator extends JqlOperator {

    public function __construct() {
        parent::_construct('=', '=', '', true);
    }



}