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

        $this->query = array(
            array(
                '$match' => array(
                    'created' => array('$gt' => new MongoDate(strtotime("2012-07-01T00:00:00.0Z")), '$lt' => new MongoDate(strtotime("2012-07-31T23:59:59.0Z"))),
                    'client_id' => new MongoId("524a42bddf81d178120031a0"),
                ),
            ),
            array(
                '$project'
            ),
            array(
                '$sort' => array(
                    'created' => 1
                ),
            ),
        );

        parent::__construct($data);
    }

    public function toHighCharts() {

    }

}