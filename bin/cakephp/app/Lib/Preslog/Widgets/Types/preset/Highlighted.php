<?php

namespace Preslog\Widgets\Types\preset;

use Preslog\Widgets\Types\ListWidget;

class Highlighted extends ListWidget
{
    public function __construct($data, $variables)
    {
        parent::__construct($data, $variables);

        $this->name = 'Highlighted Incidents';

        $this->details['perPage'] = 3;
        $this->details['orderBy'] ='Created';
        $this->details['orderDirection'] = 'Desc';
        $this->details['summary'] = true;

        $this->details['title'] = 'Number of Faults';
        $this->details['query'] = "datetime > {start} and datetime < {end}\n" .
            "and for_reports = true";
    }
}