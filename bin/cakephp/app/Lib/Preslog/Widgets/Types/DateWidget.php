<?php

namespace Preslog\Widgets\Types;

use Preslog\JqlParser\Clause;
use Preslog\Widgets\Widget;

class DateWidget extends Widget {

    public function __construct($data) {
        $this->type = 'date';

        $this->maxWidth = 3;

        //info for this instance of the widget
        if (!is_array($this->details)) {
            $this->details = array();
        }

        if (isset($data['details']))
        {
            $this->details['period'] = isset($data['details']['period']) ? $data['details']['period'] : 'Week';

            if (isset($data['details']['defaultStart']))
            {
                $this->details['defaultStart'] = $data['details']['defaultStart'];
                $clause = new Clause("created = " . $data['details']['defaultStart'], true);
                $this->details['start'] = date('c', $clause->getFunctionEvaluated());
            }

            if (isset($data['details']['defaultEnd']))
            {
                $this->details['defaultEnd'] = $data['details']['defaultEnd'];
                $clause = new Clause("created = " . $data['details']['defaultEnd'], true);
                $this->details['end'] = date('c',$clause->getFunctionEvaluated());
            }
        }

        parent::__construct($data);
    }

    public function getDisplayData() {

    }
}