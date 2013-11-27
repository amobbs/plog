<?php

namespace Preslog\Widgets;


use Preslog\Widgets\Types\preset\ByCause;
use Preslog\Widgets\Types\preset\Highlighted;
use Preslog\Widgets\Types\preset\Level1ByDuration;
use Preslog\Widgets\Types\preset\Level1ByNumber;
use Preslog\Widgets\Types\BarWidget;
use Preslog\Widgets\Types\BenchmarkWidget;
use Preslog\Widgets\Types\DateWidget;
use Preslog\Widgets\Types\LineWidget;
use Preslog\Widgets\Types\ListWidget;
use Preslog\Widgets\Types\PieWidget;
use Preslog\Widgets\Types\preset\Level2ByDuration;
use Preslog\Widgets\Types\preset\Level2ByNumber;


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


            //preset widgets
            case 'highlighted':
                $widget = new Highlighted($data, $variables);
                break;
            case 'level1number':
                $widget = new Level1ByNumber($data, $variables);
                break;
            case 'level1duration':
                $widget = new Level1ByDuration($data, $variables);
                break;
            case 'level2number':
                $widget = new Level2ByNumber($data, $variables);
                break;
            case 'level2duration':
                $widget = new Level2ByDuration($data, $variables);
                break;
            case 'bycause':
                $widget = new ByCause($data, $variables);
                break;
        }

        return $widget;
    }

}