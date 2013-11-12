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


//
//            $start = mktime(0, 0, 0, date('m'), 1, date('o'));
//            if (!isset($data['details']['start']) || $data['details']['start'] == '{dynamic}')
//            {
//                $start = mktime(0, 0, 0, date('m'), date('j'), date('o'));
//                switch($this->details['period'])
//                {
//                    case 'Week':
//                        $day = date('N');
//                        $start = mktime(0, 0, 0, date('m'), date('j') - $day, date('o'));
//                        break;
//                    case 'Month':
//                        $start = mktime(0, 0, 0, date('m'), 1, date('o'));
//                        break;
//                    case 'Day':
//                        $start = mktime(0, 0, 0, date('m'), date('j'), date('o'));
//                        break;
//                }
//                $this->details['start'] = date('D, d, M, o H:i:s T', $start);
//            }
//            else
//            {
//                $this->details['start'] = isset($data['details']['start']) ? $data['details']['start'] : date('D, d, M, o H:i:s T', $start);
//            }
//
//            $end = mktime(0, 0, 0, date('m'), date('t'), date('o'));
//
//            if (!isset($data['details']['end']) || $data['details']['end'] == '{dynamic}')
//            {
//                $end = mktime(0, 0, 0, date('m'), date('j'), date('o'));
//                switch($this->details['period'])
//                {
//                    case 'Week':
//                        $day = date('N');
//                        $end = mktime(23, 59, 59, date('m'), date('j') + (7 - $day), date('o'));
//                        break;
//                    case 'Month':
//                        $end = mktime(23, 59, 59,  date('m'), date('t'), date('o'));
//                        break;
//                    case 'Day':
//                        $end = mktime(23, 59, 59,  date('m'), date('j'), date('o'));
//                        break;
//                }
//                $this->details['end'] = date('D, d, M, o H:i:s T', $end);
//            }
//            else
//            {
//                $this->details['end'] = isset($data['details']['end']) ? $data['details']['end'] : date('D, d, M, o H:i:s T', $end);
//            }

        }

        parent::__construct($data);
    }

    //note: i have not been able to find away to get the line to start right on the yaxis line because when using catagory
    //Data to label the x axis it places the point in the middle of each tick, you would need to use a label formatter on the xaxis in order to put each point on the tick.

    public function getDisplayData() {

    }
}