<?php

namespace Preslog\Widgets;


use Preslog\Widgets\Types\BarWidget;
use Preslog\Widgets\Types\BenchmarkWidget;
use Preslog\Widgets\Types\DateWidget;
use Preslog\Widgets\Types\LineWidget;
use Preslog\Widgets\Types\ListWidget;
use Preslog\Widgets\Types\PieWidget;


class WidgetFactory {

    public static function createWidget($data, $variables = array()) {
        $widget = null;

        if(!isset($data['type']))
                return null;

        switch (strtolower($data['type'])) {
            case 'line':
                $widget = new LineWidget($data, $variables);
                break;
            case 'bar':
                $widget = new BarWidget($data, $variables);
                break;
            case 'pie':
                $widget = new PieWidget($data, $variables);
                break;
            case 'list':
                $widget = new ListWidget($data, $variables);
                break;
            case 'benchmark':
                $widget = new BenchmarkWidget($data, $variables);
                break;
            case 'date':
                $widget = new DateWidget($data, $variables);
                break;
        }

        return $widget;
    }

}