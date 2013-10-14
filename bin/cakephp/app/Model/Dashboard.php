<?php

/**
 * Dashboard Model
 */

//use Phighchart\Chart;
//use Phighchart\Options\Container;
//use Phighchart\Options\ExtendedContainer;
//use Phighchart\Data;
//use Phighchart\Renderer\Pie;
//use Phighchart\Renderer\Line;

//use Misd\Highcharts\Chart;
//use Misd\Highcharts\DataPoint\DataPoint;
//use Misd\Highcharts\Renderer\HSRenderer;
//use Misd\Highcharts\Series\LineSeries;
//use Misd\Highcharts\Series\ScatterSeries;
//use Zend\Json\Json;

App::uses('AppModel', 'Model', 'HttpSocket', 'Network/Http');

class Dashboard extends AppModel
{
    public $name = "Dashboard";

    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'           => array('type' => 'string', 'length'=>40, 'primary' => true),
        'name'          => array('type' => 'string', 'length'=>255),
        'type'          => array('type' => 'string', 'length'=>64),
        'widgets'       => array(null),
        'shares'        => array('type' => 'array'),

        'created'       => array('type' => 'datetime'),
        'modified'      => array('type' => 'datetime'),

        'preset'        =>array('type'  => 'boolean'),
    );

    /**
     * Fetch the requested client by their ID
     * @param       string      ClientID
     * @return      array       Client
     */
    public function findById( $id )
    {
        // Fetch all client info
        return $this->find('first', array(
            'conditions'=>array(
                '_id'=>$id
            )
        ));
    }

    public function findWidgetArrayId($dashboard, $widgetId) {
        foreach($dashboard['widgets'] as $key => $widget) { //find the widget in the dashboard
            $w = $dashboard['widgets'][$key];
            if ($widgetId == (String)$w['_id']) {
                return $key;
            }
        }

        return false;
    }

    public function toArray($dashboard, $forMongo = true) {
        $parsed = array();
        $parsed['id'] = (String)$dashboard['_id'];
        $parsed['name'] = $dashboard['name'];
        $parsed['type'] = $dashboard['type'];
        $parsed['widgets'] = array();
        foreach($dashboard['widgets'] as $widget) {
            $parsed['widgets'][] = $widget->toArray($forMongo);
        }

        return $parsed;
    }

    public function getChartImage($chartOptions, $tmpFilename) {
        $data = array(
            'options' => $chartOptions,
            'type' => 'image/png',
            'filename' => $tmpFilename,
            'constr' => 'Chart',
        );

        $httpSocket = new HttpSocket();
        $f = fopen(TMP . $tmpFilename, 'w');
        $httpSocket->setContentResource($f);
        $result = $httpSocket->post(
            Configure::read('highcharts_export_server'),
            $data
        );
        fclose($f);
    }

    public function getChartImageLocal($chartOptions, $tmpFilename) {
        $jsonFile = TMP . $tmpFilename . '.json';
        $outFile = TMP . $tmpFilename;

        $optionsFile = fopen($jsonFile, 'w');
        fwrite($optionsFile, $chartOptions);
        fclose($optionsFile);

        $phantomjs = '/root/highcharts/phantomjs-1.9.2-linux-x86_64/bin/phantomjs';
        $convertScript = '/tmp/phantomjs/highcharts-convert.js';


        $command = $phantomjs . ' ' . $convertScript . '  -infile ' . $jsonFile . ' -outfile ' . $outFile . ' -scale 1 -width 500 -constr Chart';
        $result = exec($command);
    }

    public function generateReport($dashboard, $reportName)
    {
        $phpWord = new PHPWord();
        $section= $phpWord->createSection();

        $dashboardObject = $this->toArray($dashboard);
        $salt = md5(date('Y-m-d h:s'));
        foreach($dashboardObject['widgets'] as $key => $widget) {
            $unique = substr(md5($widget['name']), 0, 6);
            $imageFilename = $salt .$unique . '.png';
            $this->getChartImageLocal($widget['highcharts'], $imageFilename);
            $section->addImage(TMP . $imageFilename);
        }

        $objWriter = PHPWord_IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save(TMP . $reportName);

        return TMP . $reportName;
    }

}