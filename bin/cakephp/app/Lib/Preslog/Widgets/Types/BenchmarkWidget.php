<?php

namespace Preslog\Widgets\Types;

use ClassRegistry;
use Configure;
use Highchart;
use Preslog\Logs\FieldTypes\Datetime;
use Preslog\Widgets\Widget;

class BenchmarkWidget extends Widget {

    public function __construct($data, $variables = array()) {
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
            $this->details['restrictTrendLineTo'] = isset($data['details']['restrictTrendLineTo']) ? $data['details']['restrictTrendLineTo'] : '';
            $this->details['bhpm'] = isset($data['details']['bhpm']) ? $data['details']['bhpm'] : false;
            $this->details['sla'] = isset($data['details']['sla']) ? $data['details']['sla'] : false;
            $this->details['legendLocation'] = isset($data['details']['legendLocation']) ? $data['details']['legendLocation'] : 1;
            $this->details['showLabels'] = isset($data['details']['showLabels']) ? (bool)$data['details']['showLabels'] : false;


            $this->details['clients'] = isset($data['details']['clients']) ? $data['details']['clients'] : array();

            //clients can not change the below values for this widget type
            $this->details['xAxis'] = 'datetime:month';
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
                array('fieldType' => new Datetime()),
            ),
            'yAxis' => array(
                array('fieldType' => $fields['duration']),
            ),
        );

        parent::__construct($data, $variables);

        $this->printOptions = array(
            'width' => 700,
            'height' => 400,
        );
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

        $chart->exporting = array(
            'sourceWidth' => 1200,
            'sourceHeight' => 600,
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

        if (isset($this->details['legendLocation']) && $this->details['legendLocation'] === "2") //bottom
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
            $parsedSeries = $this->series;
            //try and figure out what data will have to show based on the dtaes passed into the query. not the best method.
            if (isset($this->variables['lowestDate']) && isset($this->variables['highestDate']))
            {
                $start = $this->variables['lowestDate'];
                $end = $this->variables['highestDate'];

                $workingDate = $start;

                //set up date value with correct timezone to the first day of the month
                $dateTime = new \DateTime();
                $dateTime->setTimezone(new \DateTimeZone('UTC'));
                $dateTime->setDate(date('Y', $workingDate), date('n', $workingDate), date('n', 1));
                $dateTime->setTime(0, 0, 0);

                while ($workingDate <= $end)
                {
                    //find each point in the series that should have a value.
                    $date = date('M', $workingDate) . '-' . substr(date('Y',$workingDate), 2);
                    $key = $dateTime->getTimestamp();
                    $categorieData[$key] = $date . '<br/>0s';

                    $found = false;
                    for($i = 0; $i < sizeOf($parsedSeries); $i++)
                    {
                        $xAxis = $parsedSeries[$i]['xAxis'];
                        if (isset($xAxis['year']) && isset($xAxis['month']))
                        {
                            if ($xAxis['year'] == date('Y', $workingDate)
                                && $xAxis['month'] == date('n', $workingDate))
                            {
                                $found = true;
                                break;
                            }
                        }
                    }

                    if (!$found)
                    {
                        $yearMonth = array('year' => date('Y', $workingDate), 'month' => date('n', $workingDate));
                        $insert = array(
                            array(
                                '_id' => array('datetime' => $yearMonth),
                                'yAxis' => 0,
                                'xAxis' => $yearMonth,
                            )
                        );

                        array_splice($parsedSeries, sizeOf($categorieData) -1, 0, $insert);
                    }

                    $dateTime->modify('+1 month');
                    $workingDate = $dateTime->getTimestamp();
                }

            }

            //benchmark still does not add the 0's at the end because there is not data that matches they key in the series. !!!!
            $dates = array();
            $dateTime = new \DateTime();
            $dateTime->setTimezone(new \DateTimeZone('UTC'));
            foreach ($parsedSeries as $point)
            {
                //calculate last day on the month for the given date
                $dateTime->setDate($point['xAxis']['year'], $point['xAxis']['month'], 1);
                $dateTime->setTime(0, 0, 0);
                $wholeDate = $dateTime->getTimestamp();

                $dates[] = $wholeDate;
                $date = date('M', $wholeDate) . '-' . substr($point['xAxis']['year'], 2);

                $data = array();
                $data['y'] = $this->asPercentageOfBHPM($point['yAxis'], $wholeDate);
                $data['dataLabels'] = array(
                    'enabled' => true,
                    'format' => '{y}%',
                );
                $key = $wholeDate;
                $categorieData[$key] = $date . '<br/>' . $this->_formatDuration($point['yAxis'])  ;

                $oatSeries['data'][] = $data;
            }

            ksort($categorieData, SORT_STRING);

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

            //when bhpm is shown this makes the 2 lines (% vs hours) scale correctly or at least look correct.
            if (isset($this->details['bhpm']) && $this->details['bhpm'])
            {
                $oatYAxis['max'] = 100; //make % max be 100 and
                $oatYAxis['endOnTick'] = false;
                $oatYAxis['maxPadding'] = 0 ;
                $oatYAxis['labels']['enabled'] = false;
            }

            //yAxis two, BHPM
            $bhpmYAxis = array(
                'title' => array(
                    'text' => 'BHPM (Hours)',
                ),
                'opposite' => true,
                'gridLineWidth' => 0, //hide bhpm grid lines since they dont match the % ones. looks cluttered
                'minorGridLineWidth' => 0,
            );
            $yAxis = array(
                $oatYAxis,
            );

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

            //we can't combine sla lines, so this only works if one client is selected
            //also no trend lines for SLA since it is a straight line
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

            if ( isset($this->details['bhpm']) && $this->details['bhpm'])
            {
                $min = 0;
                $bhpmArray = $this->calculateBHPM($dates, $min);
                $series[] = array(
                    'name' => 'BHPM',
                    'type' => 'line',
                    'color' => '#FF0000',
                    'data' => $bhpmArray,
                    'yAxis' => 1,
                    'tooltip' => array(
                        'valueSuffix' => ' Hours',
                    ),
                );

                //find the highest bhpm value and make it the max
                $max = 0;
                foreach($bhpmArray as $hours) {
                    if ($max < $hours)
                    {
                        $max = $hours;
                    }
                }

                $bhpmYAxis['max'] = $max;
                $bhpmYAxis['min'] = $max / 3; //why 3? well because it math'd best and made it look good
                $bhpmYAxis['endOnTick'] = false;
                $bhpmYAxis['maxPadding'] = 0 ;

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
     * @param $date
     *
     * @return float
     */
    private function asPercentageOfBHPM($value, $date)
    {
        $preslogSettings = Configure::read('Preslog');
        $quantities = $preslogSettings['Quantities'];

        $bhpmDates = $this->getBHPMDates();
        $bhpmTotal = 0;
        $bhpmHours = $quantities['BHPM'];

        //find BHPM total before start of graph
        foreach( $bhpmDates as $bDate )
        {
            if ( strtotime($bDate) < $date)
            {
                $bhpmTotal += $bhpmHours;
            }
        }
        $bhpmTotal = $bhpmTotal  * 60 * 60;

        $decimalPlaces = pow(10, $quantities['decimalPlacesForPercentages']);

        $percent = ($value / $bhpmTotal) * 100;
        return floor($percent * $decimalPlaces) / $decimalPlaces; //round to number of places required
    }


    /**
     * We need to use a recursive function to iterate all network services
     * @param $attr
     * @param array $dates
     * @return array
     */
    public function iterateChildren($attr, &$dates = array()) {
        foreach ( $attr['children'] as $child )  {
            //------------------------------------
            //TODO: add a deleted date on to attributes and then use the deleted date to decide if it should be included
            //------------------------------------
            if (isset($child['deleted']) && $child['deleted']) {
                continue;
            }
            //if it children, we skip it and keep going (it's a folder)
            if ( !empty($child['children'])) {
                $this->iterateChildren($child, $dates);
                continue;
            }
            //else we add the date
            if ( isset($child['live_date']) )
                $dates[] = $child['live_date'];
            else
                $dates[] = '1970-01-01';
        }
        return $dates;
    }

    private function getBHPMDates()
    {
        //find all the times when a network comes live for the affected clients
        $bhpmDates = array();
        foreach( $this->clients as $client )
        {

            foreach( $client['Client']['attributes'] as $attr)
            {
                if ( !empty($attr['network']) )
                {
                    $dates = $this->iterateChildren($attr);
                    foreach($dates as $d) {
                        if (is_array($d)) {
                            foreach($d as $d2) {
                                $bhpmDates[] = $d2;
                            }
                        } else
                            $bhpmDates[] = $d;
                    }
                }
            }
        }
        return $bhpmDates;
    }

    private function calculateBHPM($dates, &$min = 0)
    {
        //find all the times whena  network comes live for the affected clients
        $bhpmDates = $this->getBHPMDates();

        $preslogSettings = Configure::read('Preslog');
        $bhpm = $preslogSettings['Quantities']['BHPM'];//730
        $bhpmTotal = 0;

        //find BHPM total before start of graph
        foreach( $bhpmDates as $bDate )
        {
            if ( strtotime($bDate) < $dates[0])
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