<?php

namespace Preslog\Widgets\Types;

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

            $start = mktime(0, 0, 0, date('m'), 1, date('o'));
            $this->details['start'] = isset($data['details']['start']) ? $data['details']['start'] : date('D, d, M, o H:i:s T', $start);
            if ($this->details['start'] == '{dynamic}')
            {
                $start = mktime(0, 0, 0, date('m'), date('j'), date('o'));
                switch($this->details['period'])
                {
                    case 'Week':
                        $day = date('N');
                        $start = mktime(0, 0, 0, date('m'), date('j') - $day, date('o'));
                        break;
                    case 'Month':
                        $start = mktime(0, 0, 0, date('m'), 1, date('o'));
                        break;
                    case 'Day':
                        $start = mktime(0, 0, 0, date('m'), date('j'), date('o'));
                        break;
                }
                $this->details['start'] = date('D, d, M, o H:i:s T', $start);
            }

            $end = mktime(0, 0, 0, date('m'), date('t'), date('o'));
            $this->details['end'] = isset($data['details']['end']) ? $data['details']['end'] : date('D, d, M, o H:i:s T', $end);

            if ($this->details['end'] == '{dynamic}')
            {
                $end = mktime(0, 0, 0, date('m'), date('j'), date('o'));
                switch($this->details['period'])
                {
                    case 'Week':
                        $day = date('N');
                        $end = mktime(23, 59, 59, date('m'), date('j') + (7 - $day), date('o'));
                        break;
                    case 'Month':
                        $end = mktime(23, 59, 59,  date('m'), date('t'), date('o'));
                        break;
                    case 'Day':
                        $end = mktime(23, 59, 59,  date('m'), date('j'), date('o'));
                        break;
                }
                $this->details['end'] = date('D, d, M, o H:i:s T', $end);
            }

        }

        parent::__construct($data);
    }

    //note: i have not been able to find away to get the line to start right on the yaxis line because when using catagory
    //Data to label the x axis it places the point in the middle of each tick, you would need to use a label formatter on the xaxis in order to put each point on the tick.

    public function getDisplayData() {

    }
}