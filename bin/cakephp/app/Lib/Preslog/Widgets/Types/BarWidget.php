<?php

namespace Preslog\Widgets\Types;

class BarWidget extends LineWidget {

    public function __construct($data, $variables = array()) {
        parent::__construct($data, $variables);
        $this->type = 'bar';
        $this->chartType = 'bar';
    }
}