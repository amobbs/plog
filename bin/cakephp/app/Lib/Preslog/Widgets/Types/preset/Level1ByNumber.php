<?php

namespace Preslog\Widgets\Types\preset;

use Preslog\Widgets\Types\LineWidget;

class Level1ByNumber extends LineWidget
{
    public function __construct($data, $variables = array())
    {
        parent::__construct($data, $variables);

        $this->name = 'Level 1 Errors by Number';

        $this->details['xAxis'] = 'datetime:month';
        $this->details['yAxis'] = 'count:count';
        $this->details['series'] = 'accountability:select';

        $this->details['trendLine'] = true;
        $this->details['restrictTrendLineTo'] = 'Mediahub Error';
        $this->details['sla'] = false;
        $this->details['legendLocation'] =1;
        $this->details['showLabels'] = true;

        $this->details['title'] = 'Number of Faults';
        $this->details['query'] = "datetime > startofmonth({start} \"-12months\") and datetime < endofmonth({end})\n" .
            " and severity ~ level 1 and accountability != empty()";
    }
}