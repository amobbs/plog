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
    protected $options = array(); //all possible options that are shown when picking details to generate the graphs
    protected $displayOptions = array(); //parsed options that are are sent to interface
    protected $maxWidth = 1; //how much space the widget should take up on screen
    protected $series = array(); //data used to populate graph
    protected $aggregate; //is the result of the data an aggregate or just a list of logs?


    public function setId($id) { $this->id = $id; }
    public function setSeries($series) { $this->series = $series; }
    public function setDetail($key, $value) { $this->details[$key] = $value; }
    public function setDisplayOptions($key, $value) { $this->displayOptions[$key] = $value; }


    public function getDetail($key) { return isset($this->details[$key]) ? $this->details[$key] : ''; }
    public function getName() { return $this->name; }
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
}