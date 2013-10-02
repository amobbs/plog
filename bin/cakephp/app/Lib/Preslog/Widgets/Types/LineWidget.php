<?php

namespace Preslog\Widgets\Types;

use Highchart;
use MongoDate;
use MongoId;
use Preslog\Widgets\Widget;

class LineWidget extends Widget {

    public function __construct($data) {
        $this->type = 'line';
        if (!is_array($this->data)) {
            $this->data = array();
        }

        $this->data['x'] = array();
        $this->data['y'] = array();

        if (isset($data['data'])) {

            $this->data['x']['label'] = isset($data['x']['label']) ? $data['x']['label'] : '';
            $this->data['x']['fieldId'] = isset($data['x']['field_id']) ? $data['x']['field_id'] : '';
            $this->data['x']['showTrend'] = isset($data['x']['showTrend']) ? $data['x']['showTred'] : false;

            $this->data['y']['label'] = isset($data['y']['label']) ? $data['y']['label'] : '';
            $this->data['y']['fieldId'] = isset($data['y']['field_id']) ? $data['y']['field_id'] : '';
        }

        $created = '2013-01-01';

        $this->query = array(
            array(
                '$match' => array(
                    "created" => array('$gt' => new MongoDate(strtotime("2012-01-01T00:00:00.0Z")), '$lt' => new MongoDate(strtotime("2012-12-01T00:00:00.0Z"))),
                )
            ),
            array(
                '$project' => array(
                    'created' => array(
                        'month' => array('$month' => '$created'),
                        'year' => array('$year' =>  '$created'),
                    ),
                )
            ),
            array(
                '$group' => array(
                    '_id' => '$created',
                    'count' => array('$sum' => 1),
                ),
            ),
            array(
                '$sort' => array(
                    "_id.year" => 1,
                    "_id.month" =>  1,
                ),
            ),
        );

        parent::__construct($data);
    }

    public function toHighCharts() {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => $this->type,
            'marginRight' => 25,
            'marginBottom' => 50,
        );

        $chart->title = array(
            'text' => $this->data['title'],
            'x' => - 20,
        );


        $chart->legend = array(
            'align' => 'center',
            'verticalAlign' => 'bottom',
            'borderWidth' => 0,
        );

        $xLabel = isset($this->data['x']['label']) ? $this->data['x']['label'] : 'no data';
        $chart->xAxis = array(
            'title' => array(
                'text' => $xLabel,
            ),
        );

        //TODO remove this and make more generic
        $categories = array();
        $series = array(
            'name' => 'faults',
            'data' => array(),
        );
        if (isset($this->series['result'])) {
            foreach($this->series['result'] as $value) {
                $categories[] = $value['_id']['month'] . '/' . substr($value['_id']['year'], 2,2);
                $series['data'][] = $value['count'];
            }
        }
        //TODO remove ^^^

        $chart->xAxis->categories = $categories;

        $yLabel = isset($this->data['y']['label']) ? $this->data['y']['label'] : 'no data';
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
            $chart->series = array($series);
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