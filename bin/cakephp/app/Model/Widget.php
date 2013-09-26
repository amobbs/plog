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
        if(isset($data['title'])) $widget['data']['title'] = $data['title'];
        if(isset($data['query'])) $widget['data']['query'] = $data['query'];

        return $widget;
    }

}