<?php

namespace Preslog\Widgets;


use Preslog\Widgets\Types\BarWidget;
use Preslog\Widgets\Types\LineWidget;
use Preslog\Widgets\Types\ListWidget;
use Preslog\Widgets\Types\PieWidget;


class WidgetFactory {

    public static function createWidget($data) {
        $widget = null;

        if(!isset($data['type']))
                return null;

        switch (strtolower($data['type'])) {
            case 'line':
                $widget = new LineWidget($data);
                break;
            case 'bar':
                $widget = new BarWidget($data);
                break;
            case 'pie':
                $widget = new PieWidget($data);
                break;
            case 'list':
                $widget = new ListWidget($data);
                break;
        }

        return $widget;
    }

}