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
        if(isset($data['data']['title'])) $widget['data']['title'] = $data['data']['title'];
        if(isset($data['data']['query'])) $widget['data']['query'] = $data['data']['query'];
        if(isset($data['data']['refresh'])) $widget['data']['refresh'] = $data['data']['refresh'];

        return $widget;
    }

}