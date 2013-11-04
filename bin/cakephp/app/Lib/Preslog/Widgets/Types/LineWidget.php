<?php

namespace Preslog\Widgets\Types;

use Configure;
use Highchart;
use Preslog\Logs\FieldTypes\FieldTypeAbstract;
use Preslog\Widgets\Widget;

class LineWidget extends Widget {

    public function __construct($data) {
        //widget type specific info
        $this->type = 'line';
        $this->chartType = 'line';
        $this->aggregate = true;

        $this->maxWidth = isset($data['maxWidth']) ? $data['maxWidth'] : 1;

        //info for this instance of the widget
        if (!is_array($this->details)) {
            $this->details = array();
        }

        $this->details['xAxis'] = array();
        $this->details['yAxis'] = array();

        if (isset($data['details']))
        {
            $this->details['xAxis'] = isset($data['details']['xAxis']) ? $data['details']['xAxis'] : '';
            $this->details['yAxis'] = isset($data['details']['yAxis']) ? $data['details']['yAxis'] : '';
            $this->details['series'] = isset($data['details']['series']) ? $data['details']['series'] : '';

            $this->details['trendLine'] = isset($data['details']['trendLine']) ? $data['details']['trendLine'] : false;
            $this->details['restrictTrendLineTo'] = isset($data['details']['restrictTrendLineTo']) ? $data['details']['restrictTrendLineTo'] : '';
            $this->details['sla'] = isset($data['details']['sla']) ? $data['details']['sla'] : false;
            $this->details['legendLocation'] = isset($data['details']['legendLocation']) ? $data['details']['legendLocation'] : 1;
            $this->details['showLabels'] = isset($data['details']['showLabels']) ? $data['details']['showLabels'] : false;

        }

        $prelogSettings = Configure::Read('Preslog');
        $fields = $prelogSettings['Fields'];
        $this->options = array(
            'xAxis' => array(
                array('fieldType' => $fields['datetime']),
                array('fieldType' => 'created'),
                array('fieldType' => 'modified'),
            ),
            'yAxis' => array(
                array('fieldType' => 'count'),
                array('fieldType' => $fields['duration']),
            ),
            'series' => array(
                array('fieldType' => 'client'),
                array('fieldType' => $fields['select']),
                array('fieldType' => $fields['select-impact']),
                array('fieldType' => $fields['select-severity']),
            ),
        );

        parent::__construct($data);
    }

    public function getDisplayData() {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => $this->chartType,
            'marginRight' => 120,
            'marginBottom' => 100,
        );

        $chart->title = array(
            'text' => isset($this->details['title']) ? $this->details['title'] : '',
            'x' => - 20,
        );

        $align = 'right';
        $verticalAlign = 'middle';
        $legendWidth = 100;
        if ( isset($this->details['legendLocation']) && $this->details['legendLocation'] === 2) //bottom
        {
            $align = 'center';
            $verticalAlign = 'bottom';
        }

        $chart->legend = array(
            'align' => $align,
            'verticalAlign' => $verticalAlign,
            'borderWidth' => 0,
            'layout' => 'vertical',
            'width' => $legendWidth,
            'navigation' => array(
                'activeColor' => '#3E576F',
                'animation' => true,
                'arrowSize' => 12,
                'inactiveColor' => '#CCC',
                'style' => array(
                    'fontWeight'=> array('bold',
                        'color'=> '#333',
                        'fontSize' => '12px')
                ),
            ),
        );

        if ($this->details['legendLocation'] === "2") //bottom
        {
            $chart->legend['align'] = 'center';
            $chart->legend['verticalAlign'] = 'bottom';
            $chart->legend['layout'] = 'horizontal';
            $chart->chart['marginRight'] = 70;
            unset($chart->legend['width']);
        }

        if (empty($this->series)) {
            $chart->series = array(
                array(
                    'name' => 'no data',
                    'data' => array(),
                ),
            );
        } else {
            //get the label for the xAxis
            $xLabel = '';
            foreach($this->displayOptions['xAxis'] as $option) {
                if ($option['id'] == $this->details['xAxis']) {
                    $xLabel = $option['name'];
                }
            }

            //find the field type so we can format the display later
            $xFieldType = null;
            $xParts = explode(':', $this->details['xAxis']);
            //get the field type so we can get the point label format
            foreach($this->options['xAxis'] as $option) {
                $type = $option['fieldType'];
                if ($type instanceof FieldTypeAbstract
                    && strtolower($type->getProperties('alias')) == strtolower($xParts[0])) {
                    $xFieldType = $type;
                }
                else
                {

                }
                //add something here to handle created/modified!!!!
            }

            $chart->xAxis = array(
                'title' => array(
                    'text' => $xLabel,
                ),
            );

            //get the y label and field type
            $yLabel = '';
            foreach($this->displayOptions['yAxis'] as $option) {
                if ($option['id'] == $this->details['yAxis']) {
                    $yLabel = $option['name'];
                }
            }

            $yParts = explode(':', $this->details['yAxis']);
            $yFieldType = $yParts[0];
            //get the field type so we can get the point label format
            foreach($this->options['yAxis'] as $option) {
                $type = $option['fieldType'];
                if ($type instanceof FieldTypeAbstract
                    && strtolower($type->getProperties('alias')) == strtolower($yParts[0])) {
                    $yFieldType = $type;
                }
            }

            $categorieData = array();
            $seriesData = array();

            //go through each point in the series and
            foreach($this->series as $point) {
                $seriesId = $point['series'];
                if ( empty($seriesId) )
                {
                    $seriesId = '(Empty)' ;
                }

                if (!isset($seriesData[$seriesId])) {
                    $seriesData[$seriesId] = array(
                        'name' => $seriesId,
                        'data' => array(),
                    );
                }

                $pointLabel = $point['xAxis'];
                if ($xFieldType == null)
                {
                    $pointLabel = '';
                }
                else if ($xFieldType instanceof FieldTypeAbstract)
                {
                    $pointLabel = $xFieldType->chartDisplay($point['xAxis'], $xParts[1]);
                }
                else if ($xFieldType == 'created' || $xFieldType == 'modified')
                {
                    switch ($xParts[1]) {
                        case 'hour':
                            $pointLabel = $point['xAxis']['hour'];
                            break;
                        case 'day':
                            $pointLabel = $point['xAxis']['day'] . '/' . $point['xAxis']['month'];
                            break;
                        case 'month':
                            $pointLabel = $point['xAxis']['month'] . '/' . substr($point['xAxis']['year'], 2);
                            break;
                        case 'all':
                            $pointLabel = $point['xAxis']['day'] . '/' . $point['xAxis']['month']. '/' . substr($point['xAxis']['year'], 2);
                    }
                }

                if ( empty($pointLabel) )
                {
                    $categorieData['(Empty)'] = '(Empty)' ;
                }
                else
                {
                    $categorieData[$pointLabel] = $pointLabel;
                }


                //format the data depending n the field type
                $pointValue = 0;
                if ($yFieldType instanceof FieldTypeAbstract) {
                    $pointValue = $yFieldType->chartDisplay($point['yAxis'], $yParts[1]);
                } else if ($yFieldType == 'count'){
                    $pointValue = $point['yAxis'];
                }

                $data = array();
                $data['y'] = $pointValue;
                $data['dataLabels'] = array(
                    'enabled' => true,
                );

                $seriesData[$seriesId]['data'][] = $data;
            }

            $series = array_values($seriesData);
            $categories = array_values($categorieData);

            $chart->xAxis->categories = $categories;

            $chart->yAxis = array(
                'title' => array(
                    'text' => $yLabel,
                ),
                'plotLines' => array(
                    array(
                        'value' => 0,
                        'width' => 1,
                        'color' => '#808080'
                    )
                ),
                'min' => 0,
            );

            if (isset($this->details['trendLine']) && $this->details['trendLine'])
            {
                $seriesWithTrends = array();
                foreach($series as $s)
                {
                    $seriesWithTrends[] = $s;

                    //restrict trendline to one series
                    if ( isset($this->details['restrictTrendLineTo'])
                        && ( !empty($this->details['restrictTrendLineTo']) && strtolower($this->details['restrictTrendLineTo']) !== strtolower($s['name']))
                    )
                    {
                        continue;
                    }

                    //only add trend lines if we have enough data
                    if (sizeof($s['data']) < 3)
                    {
                        continue;
                    }

                    $sData = $this->flattenData($s['data']);
                    $seriesWithTrends[] = array(
                        'name' => 'Linear ' . $s['name'],
                        'type' => 'line',
                        'data' =>  $this->calculateTrendLine($sData),
                        'marker' => array(
                            'enabled' => false,
                        ),
                        'dashStyle' => 'dash',
                        'enableMouseTracking' => false,
                    );
                }
                $series = $seriesWithTrends;
            }

            if ( $this->details['sla'] )
            {
                $series[] = array(
                    'name' => 'SLA',
                    'type' => 'line',
                    'data' =>  $this->calculateSLALine($categories),
                    'marker' => array(
                        'enabled' => false,
                    ),
                    'dashStyle' => 'dash',
                    'enableMouseTracking' => false,
                );
            }

            $chart->series = $series;
        }

        return $chart->renderOptions();
    }

}