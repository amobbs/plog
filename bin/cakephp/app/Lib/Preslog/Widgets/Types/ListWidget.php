<?php

namespace Preslog\Widgets\Types;

use Highchart;
use MongoDate;
use MongoId;
use Preslog\Widgets\Widget;

class ListWidget extends Widget {

    public function __construct($data) {
        $this->type = 'list';
        $this->chartType = 'list';
        $this->maxWidth = 3;
        $this->aggregate = false;

        //info for this instance of the widget
        if (!is_array($this->details)) {
            $this->details = array();
        }

        $this->details['perPage'] = isset($data['details']['perPage']) ? $data['details']['perPage'] : 3;
        $this->details['orderBy'] = isset($data['details']['orderBy']) ? $data['details']['orderBy'] : '';
        $this->details['orderDirection'] = isset($data['details']['orderDirection']) ? $data['details']['orderDirection'] : 'Asc';
        $this->details['summary'] = isset($data['details']['summary']) ? $data['details']['summary'] : false;

        parent::__construct($data);
    }

    public function getDisplayData() {
        $result = array();
        foreach($this->series as $log) {
            if (isset($log['Log'])) {
                $result[] = $log['Log'];
            } else {
                $result[] = $log;
            }
        }

       return $result;
    }
}