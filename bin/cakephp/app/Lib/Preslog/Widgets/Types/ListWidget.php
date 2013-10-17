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

        //info for this instance of the widget
        if (!is_array($this->details)) {
            $this->details = array();
        }

        $this->details['perPage'] = isset($data['details']['perPage']) ? $data['details']['perPage'] : 3;
        $this->details['orderBy'] = isset($data['details']['orderBy']) ? $data['details']['orderBy'] : '';
        $this->details['orderDirection'] = isset($data['details']['orderDirection']) ? $data['details']['orderDirection'] : 'Asc';

        parent::__construct($data);
    }

    public function getDisplayData() {
       return $this->series;
    }
}