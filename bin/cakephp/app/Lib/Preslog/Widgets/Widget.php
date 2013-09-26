<?php

namespace Preslog\Widgets;

use Highchart;
use MongoId;

class Widget {
    protected $id, $order, $name, $type, $data, $maxWidth = 1, $refreshInterval;

    public function setId($id) { $this->id = $id; }
    public function setData($data) { $this->data = $data; }
    public function setOrder($order) { $this->order = $order; }

    public function __construct($data) {
        $this->id = isset($data['id']) ? new MongoId($data['id']): new MongoId();
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->order = isset($data['order']) ? $data['order'] : null;
        $this->refreshInterval = isset($data['refresh']) ? $data['refresh'] : 0;
        if (isset($data['data'])) {
            if (!is_array($this->data)) {
                $this->data = array();
            }
            $this->data['title'] = isset($data['data']['title']) ? $data['data']['title'] : '';
            $this->data['query'] = isset($data['data']['query']) ? $data['data']['query'] : '';
        }
    }

    public function toArray() {
        return array(
            'id' => (string)$this->id,
            'order' => $this->order,
            'name' => $this->name,
            'type' => $this->type,
            'data' => $this->data,
            'refresh' => $this->refreshInterval,
            'highcharts' => $this->toHighCharts(),
            'maxWidth' => $this->maxWidth,
        );
    }

    public function toHighCharts() {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => $this->type,
            'marginRight' => 130,
            'marginBottom' => 25
        );

        $chart->title = array(
            'text' => $this->data['title'],
            'x' => - 20
        );

        $chart->legend = array(
            'layout' => 'vertical',
            'align' => 'right',
            'verticalAlign' => 'top',
            'x' => - 10,
            'y' => 100,
            'borderWidth' => 0
        );

        $chart->xAxis->categories = array(
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        );

        $chart->yAxis = array(
            'title' => array(
                'text' => 'Temperature (°C)'
            ),
            'plotLines' => array(
                array(
                    'value' => 0,
                    'width' => 1,
                    'color' => '#808080'
                )
            )
        );


        $chart->series[] = array(
            'name' => 'Tokyo',
            'data' => array(
                7.0,
                6.9,
                9.5,
                14.5,
                18.2,
                21.5,
                25.2,
                26.5,
                23.3,
                18.3,
                13.9,
                9.6
            )
        );
        $chart->series[] = array(
            'name' => 'New York',
            'data' => array(
                - 0.2,
                0.8,
                5.7,
                11.3,
                17.0,
                22.0,
                24.8,
                24.1,
                20.1,
                14.1,
                8.6,
                2.5
            )
        );
        $chart->series[] = array(
            'name' => 'Berlin',
            'data' => array(
                - 0.9,
                0.6,
                3.5,
                8.4,
                13.5,
                17.0,
                18.6,
                17.9,
                14.3,
                9.0,
                3.9,
                1.0
            )
        );
        $chart->series[] = array(
            'name' => 'London',
            'data' => array(
                3.9,
                4.2,
                5.7,
                8.5,
                11.9,
                15.2,
                17.0,
                16.6,
                14.2,
                10.3,
                6.6,
                4.8
            )
        );

//        $chart->tooltip->formatter = new HighchartJsExpr(
//            "function() { return ''+ this.series.name +'
//        '+ this.x +': '+ this.y +'°C';}");

        return $chart->renderOptions();

    }
}