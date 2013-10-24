<?php

namespace Preslog\Widgets\Types;

class BarWidget extends LineWidget {

    public function __construct($data) {
        parent::__construct($data);
        $this->type = 'bar';
        $this->chartType = 'bar';
    }
}