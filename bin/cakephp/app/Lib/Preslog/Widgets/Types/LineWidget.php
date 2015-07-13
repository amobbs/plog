<?php

namespace Preslog\Widgets\Types;

use Configure;
use Highchart;
use Preslog\Logs\FieldTypes\FieldTypeAbstract;
use Preslog\Widgets\Widget;

class LineWidget extends Widget {

    public function __construct($data, $variables = array()) {
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

        parent::__construct($data, $variables);

        $this->printOptions = array(
            'width' => 700,
            'height' => 400,
        );

    }

    /**
     * get the widget in a Highcarts compatible format.
     *
     * @return array|string
     */
    public function getDisplayData() {
        $chart = new Highchart();

        //setup chart look and layout
        $chart->chart = $this->getTypeSize();
        $chart->exporting = $this->getExportSettings(); //settings for phantomjs when exporting to image for word report
        $chart->title = $this->getTitle();
        $chart->legend = $this->getLegend();

        //get chart data
        if (empty($this->series))
        {  //no data soo show no data
            $chart->series = array(
                array(
                    'name' => 'no data',
                    'data' => array(),
                ),
            );
        }
        else
        { //there is data format it as needed
            //xAxis
            $chart->xAxis = array(
                'title' => array(
                    'text' => $this->getAxisLabel('xAxis'),
                ),
            );

            //find the field type so we can format the display later
            $xParts = explode(':', $this->details['xAxis']);
            $xAggregateBy = $xParts[1];

            $yParts = explode(':', $this->details['yAxis']);
            $yFieldType = $yParts[0];
            $yAggregateBy = $yParts[1];

            $xFieldType = $this->getAxisFieldType('xAxis');
            $yFieldType = $this->getAxisFieldType('yAxis', $yFieldType);

            //find all the values that are within the given range,
            //so we can fill the ones that are not returned by mongo with 0's
            $allXValues = array();
            if (isset($this->variables['lowestDate']) && isset($this->variables['highestDate']))
            {
                $allXValues = $this->listAllXValues($xFieldType, $xAggregateBy);
            }

            //go through each point in the series and format it as needed
            $seriesData = $this->getFormattedSeriesForHighCharts($xFieldType, $xAggregateBy, $yFieldType, $yAggregateBy);

            //populate any missing data with 0's (eg: missing months)
            $seriesComplete = $this->fillInMissing($allXValues, $seriesData);

            //get the values for each series
            $series = array_values($seriesComplete);
            $categories = array_values($allXValues);

            $chart->xAxis->categories = $categories;

            $chart->yAxis = array(
                'title' => array(
                    'text' => $this->getAxisLabel('yAxis'),
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
                $series = $this->getTrendLines($series);
            }

            if ( $this->details['sla'] )
            {
                $series[] = $this->getSLALine($categories);
            }

            $chart->series = $series;
        }

        return $chart->renderOptions();
    }


    /**
     * return HighCharts formatted chart type and size information
     * @return array
     */
    private function getTypeSize()
    {
        return array(
            'type' => $this->chartType,
            'marginRight' => 120,
            'marginBottom' => 100,
        );
    }

    /**
     * Highcharts formatted export information, including size of resulting image.
     * @return array
     */
    private function getExportSettings() {
        return array(
            'sourceWidth' => 1200,
            'sourceHeight' => 600,
        );
    }

    /**
     * Highcharts formatted title of graph
     * @return array
     */
    private function getTitle()
    {
        return array(
            'text' => isset($this->details['title']) ? $this->details['title'] : '',
            'x' => - 20,
        );
    }

    /**
     * Highcharts formatted legend layout information
     * @return array
     */
    private function getLegend()
    {
        //default legend location = right
        $legend = array(
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
            $legend['align'] = 'center';
            $legend['verticalAlign'] = 'bottom';
            $legend['layout'] = 'horizontal';
            $chart['marginRight'] = 70;
            unset($legend['width']);
        }

        return $legend;
    }

    /**
     * Get the label that will be shown on the graph for the specified axis.
     * @param $axisName (eg: "xAxis", "yAxis")
     *
     * @return string
     */
    private function getAxisLabel($axisName)
    {
        $axisLabel = '';
        foreach($this->displayOptions[$axisName] as $option) {
            if ($option['id'] == $this->details[$axisName]) {
                $axisLabel = $option['name'];
            }
        }

        return $axisLabel;
    }

    /**
     * get the field type that was passed in from the interface.
     * @param $axisName
     * @param null $defaultValue
     *
     * @return null|FieldTypeAbstract
     */
    private function getAxisFieldType($axisName, $defaultValue = null)
    {
        $axisFieldType = $defaultValue;
        $axisParts = explode(':', $this->details[$axisName]);
        //get the field type so we can get the point's label format
        foreach($this->options[$axisName] as $option) {
            $type = $option['fieldType'];
            if ($type instanceof FieldTypeAbstract
                && strtolower($type->getProperties('alias')) == strtolower($axisParts[0])) {
                $axisFieldType = $type;
            }
        }
        return $axisFieldType;
    }

    /**
     * build a list of all values that should exist for X axis.
     * Mongo will not return 0 or null values for all aggregated data. so this is used later to fill in the blanks.
     *
     * @param $xFieldType
     * @param $aggregateBy
     *
     * @return array
     */
    private function listAllXValues($xFieldType, $aggregateBy)
    {
        $start = $this->variables['lowestDate'];
        $end = $this->variables['highestDate'];

        $values = array();

        $workingDate = $start;

        //set up date value with correct timezone to the first day of the month
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $dateTime->setDate(date('Y', $workingDate), date('n', $workingDate), date('n', 1));
        $dateTime->setTime(0, 0, 0);

        while ($workingDate < $end)
        {
            //find each point in the series that should have a value.
            $display = array();
            switch ($aggregateBy)
            {
                case 'hour':
                    $display = array(
                        'hour' => date("H", $workingDate),
                    );
                    $dateTime->modify('+1 hour');
                    $workingDate = $dateTime->getTimestamp();
                    break;
                case 'day' :
                    $display = array(
                        'day' => date("j", $workingDate),
                        'month' => date("n", $workingDate),
                    );
                    $dateTime->modify('+1 day');
                    $workingDate = $dateTime->getTimestamp();
                    break;
                case 'month':
                    $display = array(
                        'month' => date("n", $workingDate),
                        'year' => date("Y", $workingDate),
                    );
                    $dateTime->modify('+1 month');
                    $workingDate = $dateTime->getTimestamp();
                    break;
            }

            $label = '';

            //add it to the list in the display format we require.
            if ($xFieldType instanceof FieldTypeAbstract)
            {
                $label = $xFieldType->chartDisplay($display, $aggregateBy);
            }
            else if ($xFieldType == 'created' || $xFieldType == 'modified')
            {
                switch ($aggregateBy) {
                    case 'hour':
                        $label = $display['hour'];
                        break;
                    case 'day':
                        $label = $display['day'] . '/' . $display['month'];
                        break;
                    case 'month':
                        $label = date('M', $display['month']) . '-' . substr($display['year'], 2);
                        break;
                    case 'all':
                        $label = $display['day'] . '/' . $display['month']. '/' . substr($display['year'], 2);
                }
            }

            $values[$label] = $label;
        }

        return $values;
    }

    /**
     * format the series data that was passed to the array from mongo, into the Highcharts required format.
     *
     * @param $xFieldType
     * @param $xAggregateBy
     * @param $yFieldType
     * @param $yAggregateBy
     *
     * @internal param $allXValues
     *
     * @return array
     */
    private function getFormattedSeriesForHighCharts($xFieldType, $xAggregateBy, $yFieldType, $yAggregateBy)
    {
        $seriesData = array();

        foreach($this->series as $point) {
            $seriesId = $point['series'];

            if ( empty($seriesId) )
            {
                $seriesId = '(Empty)' ;
            }

            //find if this line(series) has been seen/formatted before if not add it.
            if (!isset($seriesData[$seriesId])) {
                $seriesData[$seriesId] = array(
                    'name' => $seriesId,
                    'data' => array(),
                );
            }

            //get the name of this point
            $pointLabel = $point['xAxis'];

            //there is no x axis and so no label.
            //TODO why would there be no field type of xAxis???
//            if ($xFieldType == null)
//            {
//                $pointLabel = '';
//            }
//            else if ($xFieldType instanceof FieldTypeAbstract)
            if ($xFieldType instanceof FieldTypeAbstract)
            {
                $pointLabel = $xFieldType->chartDisplay($point['xAxis'], $xAggregateBy);
            }
            else if ($xFieldType == 'created' || $xFieldType == 'modified')
            {
                switch ($xAggregateBy) {
                    case 'hour':
                        $pointLabel = $point['xAxis']['hour'];
                        break;
                    case 'day':
                        $pointLabel = $point['xAxis']['day'] . '/' . $point['xAxis']['month'];
                        break;
                    case 'month':
                        $dateTime = new \DateTime();
                        $dateTime->setTimezone(new \DateTimeZone('UTC'));
                        $dateTime->setDate(1, $point['xAxis']['month'] ,1);
                        $dateTime->setTime(0, 0, 0);
                        $month = $dateTime->getTimestamp();
                        $pointLabel = date('M', $month) . '-' . substr($point['xAxis']['year'], 2);
                        break;
                    case 'all':
                        $pointLabel = $point['xAxis']['day'] . '/' . $point['xAxis']['month']. '/' . substr($point['xAxis']['year'], 2);
                }
            }

//            if ( empty($pointLabel) )
//            {
//                $allXValues['(Empty)'] = '(Empty)' ;
//            }
//            else
//            {
//                if (isset($allXValues[$pointLabel]))
//                {
//                    $allXValues[$pointLabel] = $pointLabel;
//                }
//            }


            //format the data depending on the field type
            $pointValue = 0;
            if ($yFieldType instanceof FieldTypeAbstract) {
                $pointValue = $yFieldType->chartDisplay($point['yAxis'], $yAggregateBy);
            } else if ($yFieldType == 'count'){
                $pointValue = $point['yAxis'];
            }

            $data = array();
            $data['y'] = $pointValue;

            if ( isset($this->details['showLabels']) && $this->details['showLabels'])
            {
                if($yAggregateBy == 'minutes') {
                    $min = (int) $pointValue;
                    $sec = round(($pointValue - (int) $pointValue) *60);
                    if ($sec < 10) {
                        $sec = 0 . $sec;
                    }
                    $data['dataLabels'] = array(
                        'enabled' => true,
                        'format' => "$min:$sec",
                    );
                } else {
                    $data['dataLabels'] = array(
                        'enabled' => true,
                    );
                }
            }
            $data['x'] = $pointLabel;

            $seriesData[$seriesId]['data'][] = $data;
        }

        return $seriesData;
    }

    /**
     * loop through all the x values that should exist and make sure we have data for all of them.
     *
     * @param $allXValues
     * @param $seriesData
     *
     * @return array
     */
    private function fillInMissing($allXValues, $seriesData)
    {
        $seriesComplete = array();
        foreach($seriesData as $seriesPoint)
        {
            $pointName = $seriesPoint['name'];

            //add series to array to be populated
            $seriesComplete[$pointName] = array(
                'name' => $pointName,
                'data' => array(),
            );

            //are all labels that should be there in this series?
            foreach($allXValues as $label => $val)
            {
                $found = false;
                $dataLabels = array();

                //find any points in the series that did not get aggregated correctly (aggregating on a select field for example has issues with matching _id's
                $actualData = array();
                foreach($seriesPoint['data'] as $spData)
                {
                    $seriesPointXValue = $spData['x'];
                    if (isset($actualData[$seriesPointXValue]))
                    {
                        //we have already found data that matches an x axis value, but here it is again, so add them together.
                        //mongo must not have done its job correctly.
                        if (is_numeric($spData['y']))
                        {
                            $actualData[$seriesPointXValue]['y'] += $spData['y'];
                        }
                    }
                    else //an unseen x axis value
                    {
                        $actualData[$seriesPointXValue] = $spData;
                    }
                }

                //if the value is actually provided then parse it
                foreach($actualData as $data)
                {
                    $dataLabels = isset($data['dataLabels']) ? $data['dataLabels'] : null;
                    if ($data['x'] == $label)
                    {
                        $found = true;
                        unset($data['x']);
                        $seriesComplete[$pointName]['data'][] = $data;
                        break;
                    }
                }

                //if a label we are expecting is not here then add it in.
                if (!$found)
                {
                    $newPointInSeries = array(
                        'y' => 0,
                    );
                    if ($dataLabels != null)
                    {
                        $newPointInSeries['dataLabels'] = $dataLabels;
                        if(isset($dataLabels['format'])) {
                            unset($newPointInSeries['dataLabels']['format']);
                        }
                    }
                    $seriesComplete[$pointName]['data'][] = $newPointInSeries;
                }
            }
        }

        return $seriesComplete;
    }

    /**
     * create any trend lines that have been requested and return them in the HighCharts format
     *
     * @param $series
     *
     * @return array
     */
    private function getTrendLines($series)
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

        return $seriesWithTrends;
    }

    /**
     * return a HighCharts formatted SLA line
     *
     * @param $categories
     *
     * @return array
     */
    private function getSLALine($categories)
    {
        return array(
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
}