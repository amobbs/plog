<?php

namespace Preslog\Widgets\Types;

use ClassRegistry;
use Configure;
use Highchart;
use Preslog\Widgets\Widget;

class BenchmarkWidget extends Widget {

    public function __construct($data) {
        $this->type = 'benchmark';
        $this->chartType = 'line';
        $this->aggregate = true;

        $this->maxWidth = isset($data['maxWidth']) ? $data['maxWidth'] : 1;

        //info for this instance of the widget
        if (!is_array($this->details)) {
            $this->details = array();
        }

        if (isset($data['details']))
        {

            $this->details['trendLine'] = isset($data['details']['trendLine']) ? $data['details']['trendLine'] : false;
            $this->details['bhpm'] = isset($data['details']['bhpm']) ? $data['details']['bhpm'] : false;
            $this->details['sla'] = isset($data['details']['sla']) ? $data['details']['sla'] : false;
            $this->details['legendLocation'] = isset($data['details']['legendLocation']) ? $data['details']['legendLocation'] : 1;
            $this->details['showLabels'] = isset($data['details']['showLabels']) ? (bool)$data['details']['showLabels'] : false;


            $this->details['clients'] = isset($data['details']['clients']) ? $data['details']['clients'] : array();

            //clients can not change the below values for this widget type
            $this->details['xAxis'] = 'created:month';
            $this->details['yAxis'] = 'duration:minutes';

            $clientList = '';
            foreach($this->details['clients'] as $client)
            {
                $clientList = $client .', ';
            }
            if (!empty( $clientList ))
            {
                $clientList  = substr($clientList, -2);
                $data['details']['query'] .= " and client  in ( $clientList )";
            }
        }

        $preslogSettings = Configure::read('Preslog');
        $fields = $preslogSettings['Fields'];
        $this->options = array(
            'xAxis' => array(
                array('fieldType' => 'created'),
            ),
            'yAxis' => array(
                array('fieldType' => $fields['duration']),
            ),
        );

        parent::__construct($data);
    }

    //note: i have not been able to find away to get the line to start right on the yaxis line because when using catagory
    //Data to label the x axis it places the point in the middle of each tick, you would need to use a label formatter on the xaxis in order to put each point on the tick.

    public function getDisplayData() {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => $this->chartType,
            'marginRight' => 220,
            'marginBottom' => 100,
        );

        $chart->title = array(
            'text' => isset($this->details['title']) ? $this->details['title'] : '',
            'x' => - 20,
        );

        $chart->legend = array(
            'align' => 'right',
            'verticalAlign' => 'middle',
            'borderWidth' => 0,
            'layout' => 'vertical',
            'width' => 100,
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

            $chart->xAxis = array(
                'title' => array(
                    'text' => '',
                ),
                'endOnTick' => true,
                'useHTML' => true,
            );

            $oatSeries = array(
                'name' => 'OAT',
                'data' => array(),
                'color' => '#3A4AC7',
                'yAxis' => 0,
                'tooltip' => array(
                    'valueSuffix' => '%',
                ),
            );
            $categorieData = array();
            $dates = array();
            foreach ($this->series as $point)
            {
                $dates[] = mktime(0, 0, 0, $point['xAxis']['month'], 1, $point['xAxis']['year']);
                $date = $point['xAxis']['month'] . '/' . substr($point['xAxis']['year'], 2);

                $data = array();
                $data['y'] = $this->asPercentageOfBHPM($point['yAxis']);
                $data['dataLabels'] = array(
                    'enabled' => true,
                    'format' => '{y}%',
                );
                $categorieData[] = $date . '<br/>' . $this->_formatDuration($point['yAxis'])  ;

                $oatSeries['data'][] = $data;
            }

            $series = array($oatSeries);
            $categories = array_values($categorieData);

            $chart->xAxis->categories = $categories;

            $oatYAxis = array(
                'title' => array(
                    'text' => 'OAT',
                ),
                'labels' => array(
                    'format' => '{value}%',
                ),
                'min' => 0,
            );

             //yAxis two, BHPM
            $bhpmYAxis = array(
                'title' => array(
                    'text' => 'BHPM (Hours)',
                ),
                'min' => null,
                'max' => null,
                'opposite' => true,
            );
            $yAxis = array(
                $oatYAxis,
            );

            //we can't combine sla lines, so this only works if one client is selected
            if ( $this->details['sla'] !== false )
            {
                $clientName =  $this->details['sla'];
                $clientModel = ClassRegistry::init('Client');
                $client = $clientModel->find('first', array(
                    'conditions' => array(
                        'name' => $clientName,
                    ),
                ));

                if ( isset($client['Client']) )
                {
                    $series[] = array(
                        'name' => 'SLA',
                        'type' => 'line',
                        'color' => '#FF0000',
                        'data' =>  $this->calculateSLALine($dates, $client['Client']['benchmark']),
                        'marker' => array(
                            'enabled' => false,
                        ),
                        'dashStyle' => 'dash',
                        'enableMouseTracking' => false,
                    );
                }

            }


            $chart->tooltip->shared = true;

            if (isset($this->details['trendLine']) && $this->details['trendLine'])
            {
                $seriesWithTrends = array();
                foreach($series as $s)
                {
                    $seriesWithTrends[] = $s;

                    $seriesData = $this->flattenData($s['data']);

                    //only add trend lines if we have enough data
                    if (sizeof($seriesData) < 3)
                    {
                        continue;
                    }

                    $seriesWithTrends[] = array(
                        'name' => 'Linear ' . $s['name'],
                        'type' => 'line',
                        'color' => '#000000',
                        'data' =>  $this->calculateTrendLine($seriesData),
                        'yAxis' => $s['yAxis'],
                        'marker' => array(
                            'enabled' => false,
                        ),
                        'dashStyle' => 'dash',
                        'enableMouseTracking' => false,
                    );
                }
                $series = $seriesWithTrends;
            }

            if ( isset($this->details['bhpm']) && $this->details['bhpm'])
            {
                $min = 0;
                $bhpmArray = $this->calculateBHPM($dates, $min);
                $series[] = array(
                    'name' => 'BHMP',
                    'type' => 'line',
                    'color' => '#FF0000',
                    'data' => $bhpmArray,
                    'yAxis' => 1,
                    'tooltip' => array(
                        'valueSuffix' => ' Hours',
                    ),
                );

                //put BHPM line roughly 2/3 of the way up
                $minThirds = $min / 3;
                $bhpmYAxis['min'] = $min - $minThirds * 2;
                $bhpmYAxis['max'] = $min + $minThirds;
                $yAxis[] = $bhpmYAxis;
            }

            $chart->yAxis = $yAxis;

            $chart->series = $series;
        }

        return $chart->renderOptions();
    }

    /**
     * given the number of hours in a broadcast month
     *
     * @param $value
     *
     * @return float
     */
    private function asPercentageOfBHPM($value)
    {
        $preslogSettings = Configure::read('Preslog');
        $quantities = $preslogSettings['Quantities'];
        $bhpmSeconds = $quantities['BHPM'] * 60 * 60;
        $decimalPlaces = pow(10, $quantities['decimalPlacesForPercentages']);

        $percent = ($value / $bhpmSeconds) * 100;
        return floor($percent * $decimalPlaces) / $decimalPlaces; //round to number of places required
    }

    private function calculateBHPM($dates, &$min = 0)
    {
        //find all the times whena  network comes live for the affected clients
        $bhpmDates = array();
        foreach( $this->clients as $client )
        {
            foreach( $client['Client']['attributes'] as $attr)
            {
                if ( isset($attr['network']) && $attr['network'] )
                {
                    foreach ( $attr['children'] as $child )
                    {
                        if ( isset($child['live_date']) )
                        {
                            $bhpmDates[] = $child['live_date'];
                        }
                        else
                        {
                            $bhpmDates[] = '1970-01-01';
                        }
                    }
                }
            }
        }

        $preslogSettings = Configure::read('Preslog');
        $bhpm = $preslogSettings['Quantities']['BHPM'];
        $bhpmTotal = 0;

        //find BHPM total before start of graph
        foreach( $bhpmDates as $bDate )
        {
            if ( $bDate < $dates[0])
            {
                $bhpmTotal += $bhpm;
            }
        }
        $min = $bhpmTotal;

        //calculate running total of bhpm during graph period
        $result = array();
        foreach( $dates as $date )
        {
            $startOfMonth = mktime(0, 0, 0, date('n', $date), 1, date('y', $date));
            $endOfMonth = mktime(23, 59, 59, date('n', $date), date('t', $date), date('y', $date));
            foreach( $bhpmDates as $bDate )
            {
                $formattedBDate = strtotime($bDate);
                if ( $formattedBDate > $startOfMonth and $formattedBDate < $endOfMonth)
                {
                    $bhpmTotal += $bhpm;
                }
            }
            $result[] = $bhpmTotal;
        }

        return $result;
    }

    private function _formatDuration($duration) {
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration - ($hours *3600) - ($minutes * 60);

        $string = $hours > 0 ? $hours . 'h ': '';
        $string .= $minutes > 0 ? $minutes . 'm ': '';
        $string .= $seconds > 0 ? $seconds . 's ': '';

        //not likely but if the string is empty show somehting
        if (empty($string)) {
            $string = '0s';
        }

        return $string;
    }
}