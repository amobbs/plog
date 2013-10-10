<?php

/**
 * Widget Model
 */

App::uses('AppModel', 'Model');

class Widget extends AppModel
{
    public $name = "Log";

    public $userTable = false;

    public function updateWidget($widget, $data) {
        if(isset($data['name'])) $widget['name'] = $data['name'];
        if(isset($data['details']['title'])) $widget['details']['title'] = $data['details']['title'];
        if(isset($data['details']['query'])) $widget['details']['query'] = $data['details']['query'];
        if(isset($data['details']['refresh'])) $widget['details']['refresh'] = $data['details']['refresh'];
        if(isset($data['details']['xAxis'])) $widget['details']['xAxis'] = $data['details']['xAxis'];
        if(isset($data['details']['yAxis'])) $widget['details']['yAxis'] = $data['details']['yAxis'];


        return $widget;
    }

}