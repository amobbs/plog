<?php

namespace Preslog\Widgets\Types;

use Configure;
use Highchart;
use MongoDate;
use MongoId;
use Preslog\Widgets\Widget;

class PieWidget extends Widget {

    public function __construct($data) {
        $this->type = 'pie';
        $this->chartType = 'pie';
        $this->aggregate = true;
        if (isset($data['details'])) {
            if (!is_array($this->details)) {
                $this->details = array();
            }

            $this->details['yAxis'] = isset($data['details']['yAxis']) ? $data['details']['yAxis'] : '';
            $this->details['series'] = isset($data['details']['series']) ? $data['details']['series'] : '';

        }

        $preslogSettings = Configure::read('Preslog');
        $fields = $preslogSettings['Fields'];
        $this->options = array(
            'yAxis' => array(
                array('fieldType' => 'count'),
                array('fieldType' => $fields['duration']),
            ),
            'series' => array(
                array('fieldType' => 'client'),
                array('fieldType' => $fields['select']),
                array('fieldType' => $fields['select-impact']),
                array('fieldType' => $fields['select-severity']),
            ),
        );

        parent::__construct($data);
    }

    public function getDisplayData() {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => 'pie',
            'marginRight' => 0,
            'marginBottom' => 0
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
                'showInLegend' => false,
            ),
        );

        if (isset($this->details['title'])) {
            $chart->title = array(
                'text' => $this->details['title'],
            );
        }

        $seriesData = array();

        foreach($this->series as $point) {
            $seriesId = (string)$point['series'];
            $seriesData[] = array(
                $seriesId,
                $point['yAxis'],
            );
        }

        $yLabel = '';
        if (isset($this->details['yAxis'])) {
            foreach($this->displayOptions['yAxis'] as $option) {
                if ($option['id'] == $this->details['yAxis']) {
                    $yLabel = $option['name'];
                }
            }
        }

        $series = array(
            'type' => 'pie',
            'name' => 'No Data',
            'data' => array(),
        );
        if (!empty($this->series)) {
            $series['name'] = $yLabel;
            $series['data'] = $seriesData;
        }


        $chart->series = array(
            $series,
        );

//        if (isset($this->series['result'])) {
//            foreach($this->series['result'] as $dataPoint) {
//                $chart->series[0]['data'][] = array(
//                    $dataPoint['_id'],
//                    $dataPoint['count'],
//                );
//            }
//        }

//        if (empty($this->data)) {
//            $chart->series[0]['name'] = 'no data';
//        }

        return $chart->renderOptions();
    }

}