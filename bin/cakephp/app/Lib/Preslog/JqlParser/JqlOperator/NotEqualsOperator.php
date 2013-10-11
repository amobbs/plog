<?php
namespace Preslog\JqlParser\JqlOperator;


class NotEqualsOperator extends JqlOperator {

    public function __construct() {
        parent::_construct('!=', '!=', '$not', false);
    }



}