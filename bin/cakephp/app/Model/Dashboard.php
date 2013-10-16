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

        //get details about export exec locations
        $export = Configure::read('Preslog.export.exec');

        $jsonFile = TMP . $tmpFilename . '.json';
        $outFile = TMP . $tmpFilename;

        $optionsFile = fopen($jsonFile, 'w');
        fwrite($optionsFile, $chartOptions);
        fclose($optionsFile);

        $phantomjs = $export['phantomjs'];
        $convertScript = $export['highchartsExport.js'];


        $command = $phantomjs . ' ' . $convertScript . '  -infile ' . $jsonFile . ' -outfile ' . $outFile . ' -scale 1 -width 600 -constr Chart';
        $result = exec($command);
    }

    public function generateReport($dashboard, $clientDetails, $reportName)
    {
        //get layout configurtaion
        $layout = Configure::read('Preslog.export.layout');

        $phpWord = new PHPWord();
        $section= $phpWord->createSection();

        $salt = md5(date('Y-m-d h:s'));

        //setup any document wide formatting required
        $styleFont = array('name'=>'Tahoma', 'size'=>12);
        $tocDepth = 1;
        $phpWord->addTitleStyle( $tocDepth, $styleFont);

//
//        //toc
//        $section->addText('Table of Contents', array('bold' => true, 'size' => 24));
//        $styleTOC = array('tabLeader' => PHPWord_Style_TOC::TABLEADER_DOT);
//        $section->addTOC($styleFont, $styleTOC);
//        $section->addPageBreak();


        foreach($dashboard['widgets'] as $widget) {
            $section->addTitle($widget->getName());

            //only aggregate widgets can make charts
            if ($widget->isAggregate()) {

                $unique = substr(md5($widget->getName()), 0, 6);
                $imageFilename = $salt .$unique . '.png';
                $this->getChartImageLocal($widget->getDisplayData(), $imageFilename);
                $section->addImage(TMP . $imageFilename);
            } else { //data is not aggregated so just show it as a list of logs
                $logs = $widget->getDisplayData();

                if (empty($logs)) {
                    $section->addText('No Errors');
                }

                foreach($logs as $log) {

                    $table = $section->addTable();

                    $titleStyle = array(
                        'color' => $layout['titleColor'],
                    );

                    $paragraphStyle = array(
                        'spacing' => 1,
                        'spaceAfter' => 1,
                    );

                    $cellStyle = array(
                        'borderSize' => $layout['cellBorder'],
                        'borderColor' => $layout['cellBorderColor'],
                    );

                    //fault id
                    $table->addRow();
                    $cell = $table->addCell($layout['titleColWidth'], $cellStyle);
                    $cell->addText('Fault', $titleStyle, $paragraphStyle);
                    $cell = $table->addCell($layout['detailColWidth'], $cellStyle);
//                    $cell->addText($log['hrid']);
                    $cell->addText($log['hrid'], array(), $paragraphStyle);

                    //date
                    $table->addRow();
                    $cell = $table->addCell($layout['titleColWidth'], $cellStyle);
                    $cell->addText('Date', $titleStyle, $paragraphStyle);
                    $cell = $table->addCell($layout['detailColWidth'], $cellStyle);
                    $cell->addText(date('Y-m-d', strtotime($log['created'])), array(), $paragraphStyle);

                    //time
                    $table->addRow();
                    $cell = $table->addCell($layout['titleColWidth'], $cellStyle);
                    $cell->addText('Time', $titleStyle, $paragraphStyle);
                    $cell = $table->addCell($layout['detailColWidth'], $cellStyle);
                    $cell->addText(date('h:i:s A', strtotime($log['created'])), array(), $paragraphStyle);

                    $client = null;
                    foreach($clientDetails as $detail) {
                        if ($detail['Client']['_id'] == $log['client_id']) {
                            $client = $detail['Client'];
                            break;
                        }
                    }

                    //TODO clean this up so we dont have duplicated code. i copied this from Log->getFieldHelperByClientId
                    // Load poor-mans cache for this pageload
                    $clientFieldHelperCache = Configure::read('Preslog.cache.clientFieldsHelper');
                    if (!is_array($clientFieldHelperCache))
                    {
                        throw new Exception('can not acces any clients'); //TODO replace with cake error
                    }

                    if (!isset($clientFieldHelperCache[$client['_id']])) {
                        throw new Exception('no permission to view this users logs'); //TODO replace with cake error
                    }

                    $fieldHelper = $clientFieldHelperCache[$client['_id']];

                    //dynamic fields
                    $document = $fieldHelper->convertToDocument($log);
                    foreach($log['fields'] as $field) {
                    //    $format = $fieldHelper['fields'][$field['field_id']];

                     //   $table->addRow();
//                        $table->addCell($layout['titleColWidth'])
//                            ->addText($format->getProperties('name'));
//                        $table->addCell($layout['detailColWidth'])
//                            ->addText($format->chartDisplay($field['data']));
                    }

                    //add a space before next table.
                    $section->addTextBreak();

                }
            }

            $section->addPageBreak();
        }

        $objWriter = PHPWord_IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save(TMP . $reportName);

        return TMP . $reportName;
    }

}