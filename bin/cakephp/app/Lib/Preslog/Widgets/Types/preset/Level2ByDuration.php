<?php

namespace Preslog\Widgets\Types\preset;

use Preslog\Widgets\Types\LineWidget;

class Level2ByDuration extends LineWidget
{
    public function __construct($data, $variables = array())
    {
        parent::__construct($data, $variables);

        $this->name = 'Level 2 Errors by Duration';

        $this->details['xAxis'] = 'datetime:month';
        $this->details['yAxis'] = 'duration:seconds';
        $this->details['series'] = 'accountability:select';

        $this->details['trendLine'] = true;
        $this->details['restrictTrendLineTo'] = 'Mediahub Error';
        $this->details['sla'] = false;
        $this->details['legendLocation'] =1;
        $this->details['showLabels'] = true;

        $this->details['title'] = 'Number of Faults';
        $this->details['query'] = "datetime > startofmonth({start} \"-12months\") and datetime < endofmonth({end})\n" .
            " and severity ~ level 2 and accountability != empty()";
    }
}