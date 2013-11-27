<?php

namespace Preslog\Widgets\Types\preset;

use Preslog\Widgets\Types\PieWidget;

class ByCause extends PieWidget
{
    public function __construct($data, $variables)
    {
        parent::__construct($data, $variables);

        $this->name = 'Number of Errors by Cause';

        $this->details['title'] = '';
        $this->details['query'] = "datetime > {start} and datetime < {end}";
        $this->details['yAxis'] = 'count:count';
        $this->details['series'] = 'why:select';
    }
}