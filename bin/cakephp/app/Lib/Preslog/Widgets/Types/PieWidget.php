<?php

namespace Preslog\Widgets\Types;

use Highchart;
use MongoDate;
use MongoId;
use Preslog\Widgets\Widget;

class PieWidget extends Widget {

    public function __construct($data) {
        $this->type = 'pie';
        if (isset($data['data'])) {
            if (!is_array($this->data)) {
                $this->data = array();
            }
            $this->data['x'] = isset($data['x']) ? $data['x'] : '';
        }

        $this->query = array(
            array(
                '$match' => array(
                    "created" => array('$gt' => new MongoDate(strtotime("2012-01-01T00:00:00.0Z")), '$lt' => new MongoDate(strtotime("2012-12-01T00:00:00.0Z"))),
                    '_id' => new MongoId('524a42bddf81d178120031a0')
                 )
            ),
	        array(
                '$group' => array(
                    '_id' => '$fields.data.text',
                    'count' => array('$sum' =>  1)
                )
            )
        );

        parent::__construct($data);
    }

    public function toHighCharts() {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => 'pie',
            'marginRight' => 25,
            'marginBottom' => 25
        );

        $chart->plotOptions = array(
            "pie" => array(
                'allowPointerSelect' => true,
                'cursor' => 'pointer',
                'dataLabels' => array(
                    'enabled' => true,
                    'color' => '#000000',
                    'connectorColor' => '#000000',
                    'format' => '<b>{point.name}</b>: {point.percentage:.1f} %',
                ),
            ),
        );

        $chart->title = array(
            'text' => $this->data['title'],
        );


//        $chart->legend = array(
//            'layout' => 'vertical',
//            'align' => 'right',
//            'verticalAlign' => 'top',
//            'x' => - 10,
//            'y' => 100,
//            'borderWidth' => 0
//        );


        $chart->series = array(
            array(
                'type' => 'pie',
                'name' => '',
                'data' => array(),
            ),
        );

        if (isset($this->series['result'])) {
            foreach($this->series['result'] as $dataPoint) {
                $chart->series[0]['data'][] = array(
                    $dataPoint['_id'],
                    $dataPoint['count'],
                );
            }
        }

//        if (empty($this->data)) {
//            $chart->series[0]['name'] = 'no data';
//        }

        return $chart->renderOptions();
    }

}