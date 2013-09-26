<?php

namespace Preslog\Widgets\Types;

use Highchart;
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

        foreach($this->series as $dataPoint) {
            $chart->series[0]['data'][] = array(
                $dataPoint['name'],
                $dataPoint['data'][0],
            );
        }

        if (empty($this->data)) {
            $chart->series[0]['name'] = 'no data';
        }

        return $chart->renderOptions();
    }

}