<?php

namespace Preslog\Widgets\Types;

use Highchart;
use MongoDate;
use MongoId;
use Preslog\Widgets\Widget;

class ListWidget extends Widget {

    public function __construct($data) {
        $this->type = 'list';
        $this->maxWidth = 3;
        $this->aggregate = false;


        parent::__construct($data);
    }

    public function getDisplayData() {

    }
}