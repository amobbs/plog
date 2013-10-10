<?php

namespace Preslog\Widgets\Types;

use Configure;
use Highchart;
use MongoDate;
use MongoId;
use Preslog\Widgets\Widget;

class LineWidget extends Widget {

    public function __construct($data) {
        //widget type specific info
        $this->type = 'line';
        $this->aggregate = true;

        //info for this instance of the widget
        if (!is_array($this->details)) {
            $this->details = array();
        }

        $this->details['xAxis'] = array();
        $this->details['yAxis'] = array();

        if (isset($data['details'])) {

            $this->details['xAxis'] = isset($data['details']['xAxis']) ? $data['details']['xAxis'] : '';
            $this->details['yAxis'] = isset($data['details']['yAxis']) ? $data['details']['yAxis'] : '';
            $this->details['series'] = isset($data['details']['series']) ? $data['details']['series'] : '';
        }

        $fields = Configure::Read('Preslog')['Fields'];
        $this->options = array(
            'xAxis' => array(
                array('fieldType' => $fields['datetime']),
                array('fieldType' => 'created'),
                array('fieldType' => 'modified'),
            ),
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
            'type' => $this->type,
            'marginRight' => 25,
            'marginBottom' => 50,
        );

        $chart->title = array(
            'text' => isset($this->details['title']) ? $this->details['title'] : '',
            'x' => - 20,
        );


        $chart->legend = array(
            'align' => 'center',
            'verticalAlign' => 'bottom',
            'borderWidth' => 0,
        );

        $xLabel = '';
        foreach($this->options['xAxis'] as $option) {
            if ($option['id'] == $this->details['xAxis']) {
                $xLabel = $option['name'];
            }
        }

        $chart->xAxis = array(
            'title' => array(
                'text' => $xLabel,
            ),
        );

        $yLabel = '';
        foreach($this->options['yAxis'] as $option) {
            if ($option['id'] == $this->details['yAxis']) {
                $yLabel = $option['name'];
            }
        }

        $categorieData = array();
        $seriesData = array();

        foreach($this->series as $point) {
            $seriesId = (string)$point['series'];
            if (!isset($seriesData[$seriesId])) {
                $seriesData[$seriesId] = array(
                    'name' => $seriesId,
                    'data' => array(),
                );
            }
            $categorieData[$point['xAxis']['hour']] = $point['xAxis']['hour']; //TODO fix this put the format somewhere
            $seriesData[$seriesId]['data'][] = $point['yAxis'];
        }

        $series = array_values($seriesData);
        $categories = array_values($categorieData);

        $chart->xAxis->categories = $categories;

        $chart->yAxis = array(
            'title' => array(
                'text' => $yLabel,
            ),
            'plotLines' => array(
                array(
                    'value' => 0,
                    'width' => 1,
                    'color' => '#808080'
                )
            )
        );

        if (empty($this->series)) {
            $chart->series = array(
                array(
                    'name' => 'no data',
                    'data' => array(),
                ),
            );
        } else {
            $chart->series = $series;
        }

//            $chart->series[] = array(
//                'name' => 'Tokyo',
//                'data' => array(
//                    7.0,
//                    6.9,
//                    9.5,
//                    14.5,
//                    18.2,
//                    21.5,
//                    25.2,
//                    26.5,
//                    23.3,
//                    18.3,
//                    13.9,
//                    9.6
//                )
//            );

//        $chart->series[] = array(
//            'name' => 'New York',
//            'data' => array(
//                - 0.2,
//                0.8,
//                5.7,
//                11.3,
//                17.0,
//                22.0,
//                24.8,
//                24.1,
//                20.1,
//                14.1,
//                8.6,
//                2.5
//            )
//        );
//        $chart->series[] = array(
//            'name' => 'Berlin',
//            'data' => array(
//                - 0.9,
//                0.6,
//                3.5,
//                8.4,
//                13.5,
//                17.0,
//                18.6,
//                17.9,
//                14.3,
//                9.0,
//                3.9,
//                1.0
//            )
//        );
//        $chart->series[] = array(
//            'name' => 'London',
//            'data' => array(
//                3.9,
//                4.2,
//                5.7,
//                8.5,
//                11.9,
//                15.2,
//                17.0,
//                16.6,
//                14.2,
//                10.3,
//                6.6,
//                4.8
//            )
//        );

        return $chart->renderOptions();
    }

}