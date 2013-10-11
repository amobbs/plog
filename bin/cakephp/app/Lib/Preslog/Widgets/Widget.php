<?php

namespace Preslog\Widgets;

use Highchart;
use MongoId;

class Widget {
    protected $id; //mongo id for instance of widget
    protected $order; //order displayed on screen
    protected $name; //shown in title bar of widget
    protected $type; //determines type of graph or method used to display widget
    protected $details; //information used to render the graph (title, refresh rate, options available to generate the graph
    protected $displayOptions = array(); //all possible options that are shown when picking details to generate the graphs
    protected $maxWidth = 1; //how much space the widget should take up on screen
    protected $series = array(); //data used to populate graph
    protected $aggregate; //is the result of the data an aggregate or just a list of logs?


    public function setId($id) { $this->id = $id; }
    public function setSeries($series) { $this->series = $series; }
    public function setDetail($key, $value) { $this->details[$key] = $value; }
    public function setDisplayOptions($key, $value) { $this->displayOptions[$key] = $value; }

    /*
     * an array for all the details in this object
     */
//    public function getProperties() {
//        return array(
//            'id' => $this->id,
//            'order' => $this->order,
//            'name' => $this->name,
//            'type' => $this->type,
//            'aggregate' => $this->aggregate,
//            'details' => $this->details,
//            'options' => $this->options,
//            'series' => $this->series,
//        );
//    }

    public function getDetail($key) { return isset($this->details[$key]) ? $this->details[$key] : ''; }
    public function getOptions() { return $this->options; }
    public function isAggregate() { return $this->aggregate; }

    public function __construct($data) {
        //set all the widget details
        $this->id = isset($data['_id']) ? new MongoId($data['_id']): new MongoId();
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->order = isset($data['order']) ? $data['order'] : null;
        if (!is_array($this->details)) {
            $this->details = array();
        }

        //set details about the chart that will be displayed
        if (isset($data['details'])) {
            $this->details['title'] = isset($data['details']['title']) ? $data['details']['title'] : '';
            $this->details['query'] = isset($data['details']['query']) ? $data['details']['query'] : '';
            $this->details['refresh'] = isset($data['details']['refresh']) ? $data['details']['refresh'] : 0;
        } else {
            $this->data['title'] = '';
            $this->details['query'] = '';
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

    public function toArray($forMongo = true) {
        $widget = array(
            '_id' => (string)$this->id,
            'order' => $this->order,
            'name' => $this->name,
            'type' => $this->type,
            'details' => $this->details,
            'maxWidth' => $this->maxWidth,
        );

        if (!$forMongo) {
            $widget['options'] = $this->displayOptions;
            $widget['display'] = $this->getDisplayData();
        }

        return $widget;
    }

    //return data that is needed to display this widget in the interface
    public function getDisplayData() {
       return array();
    }

//    private function _parseOptionsForDisplay() {
//        $result = array();
//
//        foreach($this->options as $key => $value) {
//
//        }
//    }
}