<?php

namespace Preslog\Widgets\Types;

use Highchart;
use Preslog\Widgets\Widget;

class ListWidget extends Widget {

    public function __construct($data) {
        $this->type = 'list';
        $this->maxWidth = 3;

        parent::__construct($data);
    }

    public function toHighCharts() {

    }

}