<?php

namespace Preslog\Widgets;

use Highchart;
use MongoId;

class Widget {
    protected $id, $order, $name, $type, $data, $maxWidth = 1, $series = array(), $query = '';

    public function setId($id) { $this->id = $id; }
    public function setData($data) { $this->data = $data; }
    public function setOrder($order) { $this->order = $order; }
    public function setSeries($series) { $this->series = $series; }
    public function setQuery($query) { $this->query = $query; }

    public function getQuery() { return $this->query; }

    public function __construct($data) {
        $this->id = isset($data['id']) ? new MongoId($data['id']): new MongoId();
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->order = isset($data['order']) ? $data['order'] : null;
        //$this->query = isset($data['query']) ? $data['query'] : null;
        if (!is_array($this->data)) {
            $this->data = array();
        }


        if (isset($data['data'])) {
            $this->data['title'] = isset($data['data']['title']) ? $data['data']['title'] : '';
            $this->data['query'] = isset($data['data']['query']) ? $data['data']['query'] : '';
            $this->data['refresh'] = isset($data['data']['refresh']) ? $data['data']['refresh'] : 0;
        } else {
            $this->data['title'] = '';
        }

//        $this->series = array(
//            array(
//                'name' => 'a',
//                'data' => array(1, 4, 5, 6, 7,1, 3, 2),
//            ),
//            array(
//                'name' => 'b',
//                'data' => array(2, 8, 21, 56 ,9, 21),
//            ),
//            array(
//                'name' => 'c',
//                'data' => array(1, 4, 8,9 ,0 , 10),
//            ),
//        );
    }

    public function toArray() {
        return array(
            '_id' => (string)$this->id,
            'order' => $this->order,
            'name' => $this->name,
            'type' => $this->type,
            'data' => $this->data,
            'highcharts' => $this->toHighCharts(),
            'maxWidth' => $this->maxWidth,
        );
    }

    public function toHighCharts() {
       return array();
    }
}