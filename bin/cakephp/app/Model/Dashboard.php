<?php

/**
 * Dashboard Model
 */

use Preslog\Logs\Entities\LogEntity;

App::uses('AppModel', 'Model', 'HttpSocket', 'Network/Http');

class Dashboard extends AppModel
{
    public $name = "Dashboard";

    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id' => array(
            'type' => 'string',
            'length'=>40,
            'primary' => true,
            'mongoType'=>'mongoId',
        ),
        'name' => array(
            'type' => 'string',
            'length' => 255
        ),
        'type' => array(
            'type' => 'string',
            'length' => 64
        ),
        'widgets' => array(
            'type' => 'subCollection',
            'schema'=> array(
                '_id' =>array(
                    'type' => 'string',
                    'length'=>24,
                    'mongoType'=>'mongoId'
                ),
                'order' => array(
                    'type' => 'integer'
                ),
                'name' => array(
                    'type' => 'string'
                ),
                'type' => array(
                    'type' => 'string'
                ),
                'details' => array(
                    'type' => 'array',
                ),
                'maxWidth' => array(
                    'type' => 'integer'
                ),
            ),
        ),
        'shares' => array(
            'type' => 'array'
        ),

        'created' => array(
            'type' => 'datetime',
            'mongoType'=>'mongoDate',
        ),
        'modified' => array(
            'type' => 'datetime',
            'mongoType'=>'mongoDate',
        ),

        'preset' => array(
            'type' => 'boolean'
        ),
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
        $parsed['preset'] = $dashboard['preset'];
        $parsed['shares'] = $dashboard['shares'];
        $parsed['widgets'] = array();
        foreach($dashboard['widgets'] as $widget) {
            $parsed['widgets'][] = $widget->toArray($forMongo);
        }
        return $parsed;
    }

    /**
     * use an export server for rasterization. NOT BEING USED.
     *
     * @param $chartOptions
     * @param $tmpFilename
     */
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

    /**
     * run a program on this machine that will create the chart image
     *
     * @param $chartOptions
     * @param $tmpFilename
     */
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


        $command = $phantomjs . ' ' . $convertScript . '  -infile ' . $jsonFile . ' -outfile ' . $outFile . ' -scale 1 -width 1200 -constr Chart';
        $result = exec($command);
    }

    /**
     * Given a dashboard create a doc file with one graph per page. In the case of log-list widgets also add a summary page before listing the logs.
     *
     * note: if there are any issues generating these reports my guess is it will be in the summary page section.
     * @param $dashboard
     * @param $clientDetails
     * @param $reportName
     *
     * @return string
     */
    public function generateReport($dashboard, $clientDetails, $reportName)
    {
        //get layout configurtaion
        $layout = Configure::read('Preslog.export.layout');

        //new section is the whole doc
        $phpWord = new PHPWord();
        $phpWord->setDefaultFontSize(10);
        $section= $phpWord->createSection();

        $sectionStyle = $section->getSettings();
        $sectionStyle->setMarginLeft(500);
        $sectionStyle->setMarginRight(500);
        $sectionStyle->setMarginTop(500);
        $sectionStyle->setMarginBottom(500);

        //setup any document wide formatting required
        $styleFont = array('name'=>'Tahoma', 'size'=>12);
        $tocDepth = 1;
        $phpWord->addTitleStyle( $tocDepth, $styleFont);


        //toc
        $section->addText('Table of Contents', array('bold' => true, 'size' => 24));
        $styleTOC = array('tabLeader' => PHPWord_Style_TOC::TABLEADER_DOT);
        $section->addTOC($styleFont, $styleTOC);
        $section->addPageBreak();


        //loop through the widgets, generate charts and add one per page
        for($i = 0; $i < sizeof($dashboard['widgets']); $i++)
        {
            $widget = $dashboard['widgets'][$i];
            $widgetDetails = $widget->toArray();
            if ($widgetDetails['type'] == 'date')
            {
                continue;
            }

            $section->addTitle($widget->getName());


            //only aggregate widgets can make charts
            if ($widget->isAggregate()) {

                $imageFilename = $this->getUniqueImageName($widget->getName());
                $this->getChartImageLocal($widget->getDisplayData(), $imageFilename);

                $printOptions = $widget->getPrintOptions();
                $style = array('width' => $printOptions['width'], 'height' => $printOptions['height']);
                $section->addImage(TMP . $imageFilename, $style);

            }
            else
            {
                //data is not aggregated so just show it as a list of logs
                $logs = $widget->getDisplayData();

                $config = Configure::read('Preslog');
                $primeTimeStart = $config['Primetime']['start'];
                $primeTimeEnd = $config['Primetime']['end'];

                $logs = $widget->getDisplayData();
                $primeTime = $this->seperateLogsByTimeOfDay($logs, $primeTimeStart, $primeTimeEnd);
                $nonPrimeTime = $this->seperateLogsByTimeOfDay($logs, $primeTimeEnd, $primeTimeStart);

                //create summary

                $createSummary = $widget->getDetail('summary');
                if ($createSummary !== false)
                {
                    $this->createSummaryPage($primeTime, $nonPrimeTime, sizeof($logs), $section);
                }

                if (empty($logs)) {
                    $section->addText('No Errors Shown');
                    $section->addPageBreak();
                    continue;
                }

                $section->addText('Primetime', array('color' => $layout['red']));
                $section->addTextBreak();

                foreach($primeTime as $log)
                {
                    $this->addLog($log, $section);
                }

                $section->addPageBreak();

                $section->addText('Non-Primetime', array('color' => $layout['brown']));
                $section->addTextBreak();
                foreach($nonPrimeTime as $log)
                {
                    $this->addLog($log, $section);
                }
            }

            if ($i !== (sizeof($dashboard['widgets']) -1))
            {
                $section->addPageBreak();
            }
        }

        $objWriter = PHPWord_IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save(TMP . $reportName);

        return TMP . $reportName;
    }

    private function getUniqueImageName($name)
    {
        $ext = "png";

        // Convert spaces to underscores
        $origBase = md5($name . time());
        $base = strtolower($origBase);

        $i=1;
        while(is_file(TMP . $base . "." . $ext)) {
            $base = $origBase . "-" . $i++;
        }
        return $base . "." . $ext;
    }

    private function addLog($log, &$section)
    {
        $layout = Configure::read('Preslog.export.layout');

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
        $cell->addText($log['hrid'], array(), $paragraphStyle);

        $clientModel = ClassRegistry::init('Client');

        $clientEntity = $clientModel->getClientEntityById((string)$log['client_id']);
        $logEntity = new LogEntity();
        $logEntity->setDataSource($this->getDataSource());
        $logEntity->setClientEntity($clientEntity);
        $logEntity->fromArray($log);
        $logFields = $logEntity->toDisplay();

        //dynamic fields
        foreach($logFields as $key => $value) {
            if (is_array($value))
            {
                continue;
            }

            //the attributes are come out as an array 0+ of the mongo id
            if ( is_numeric($key) )
            {
                continue;
            }

            $table->addRow();
            $cell = $table->addCell($layout['titleColWidth'], $cellStyle);
            $cell->addText($key, $titleStyle, $paragraphStyle);
            $cell = $table->addCell($layout['detailColWidth'], $cellStyle);
            $cell->addText($value, array(), $paragraphStyle);

        }

        //each log on one page
        $section->addPageBreak();
    }

    private function createSummaryPage($primeTime, $nonPrimeTime, $totalSize, &$section)
    {
        //todo: title
        $config = Configure::read('Preslog');
        $primeTimeStart = $config['Primetime']['start'];
        $primeTimeEnd = $config['Primetime']['end'];
        $layout = Configure::read('Preslog.export.layout');

        $redHex = $layout['red'];
        $brownHex = $layout['brown'];

        //total incidents
        //note: 'Level 1' should probably not be hard coded
        $textIncidentCount = $section->createTextRun();
        $textIncidentCount->addText('Level 1', array('color' => $redHex));
        $textIncidentCount->addText(' incidents: ');
        $textIncidentCount->addText($totalSize, array('underline' => true));

        //count in prime time
        $textPrimetimeCount = $section->createTextRun(array('align' => 'right'));
        $textPrimetimeCount->addText('In ');
        $textPrimetimeCount->addText('Primetime', array('color' => $redHex));
        $textPrimetimeCount->addText(' hours: ');
        $textPrimetimeCount->addText(sizeof($primeTime), array('underline' => true));

        $prettyEndTime = $primeTimeEnd === 0 ? 'Midnight' : $primeTimeEnd . ':00';
        $section->addText("(Primetime counted here as being between $primeTimeStart:00 and $prettyEndTime)", array('size' => 6), array('align' => 'right'));


        //add space between details
        $section->addTextBreak();


        //primetime broken up by accountability (with examples)
        $logsByAccountability = $this->splitLogsByAccountAbility($primeTime);
        foreach($logsByAccountability as $key => $exampleIds)
        {
            $examples = sizeof($exampleIds) > 0 ? ' Examples: ' : '';
            foreach($exampleIds as $id)
            {
                $examples .= $id . ' & ';
            }
            $examples = substr($examples, 0, -3);
            $section->addText("\t" . $key . ': ' . sizeof($exampleIds) . $examples);
        }

        //count in non prime time
        $textPrimetimeCount = $section->createTextRun(array('align' => 'right'));
        $textPrimetimeCount->addText('In ');
        $textPrimetimeCount->addText('Non-Primetime', array('color' => $brownHex));
        $textPrimetimeCount->addText(' hours: ');
        $textPrimetimeCount->addText(sizeof($nonPrimeTime), array(), array('underline' => true));

        $section->addText("(Non-Primetime counted here as being between $prettyEndTime and $primeTimeStart:00)", array('size' => 6), array('align' => 'right'));


        //non primetime broken up by accountability (with examples)
        $logsByAccountability = $this->splitLogsByAccountAbility($nonPrimeTime);
        foreach($logsByAccountability as $key => $exampleIds)
        {
            $examples = sizeof($exampleIds) > 0 ? ' Examples: ' : '';
            foreach($exampleIds as $id)
            {
                $examples .= $id . ' & ';
            }
            $examples = substr($examples, 0, -3);
            $section->addText("\t" . $key . ': ' . sizeof($exampleIds) . $examples);
        }


        $section->addPageBreak();
    }

    /**
     * given a range of longs return only logs that happened between the hours passed in
     *
     * this is done in code because we can not query by hour of day in mongo, only when using an aggregation which made other stuff to complicated at this stage in the project
     * @param $logs
     * @param $start
     * @param $end
     *
     * @return array
     */
    private function seperateLogsByTimeOfDay($logs, $start, $end)
    {
        $result = array();

        $clientModel = ClassRegistry::init('Client');


        foreach($logs as $log)
        {
            $clientEntity = $clientModel->getClientEntityById((string)$log['client_id']);
            $logEntity = new LogEntity();
            $logEntity->setDataSource($this->getDataSource());
            $logEntity->setClientEntity($clientEntity);
            $logEntity->fromArray($log);
            $logFields = $logEntity->toDisplay();

            //warning: i am using the datetime field to determine this, it is stupid to hardcode that,
            //we don't know that field will always be called datetime or that it will even exist. but the time at which
            //the event happened is not specifically stated in the log, we only have the fields provided.
            //START ONE OF THE STUPID PARTS!
            $strTime = 0;
            foreach($logFields as $key => $value)
            {
                if (strtolower(substr($key, 0, 4)) == 'date')
                {
                    $strTime = $value ;
                    break;
                }
            }
            //END ONE OF THE STUPID PARTS!

            //account for 0 being start and end of day
            if ($end === 0)
            {
                $end = 24;
            }

            $time = strtotime($strTime);
            $hour = date('G', $time);
            if (!$time) //string to time dosn't always seem to work. the date/time comes out as specified in $logEntity->toDisplay so we know the format.
            {
                $parts = explode(" ", $strTime);
                $timeParts = explode(":", $parts[1]);
                $hour = $timeParts[0];
            }

            if ($hour >= $start and $hour < $end)
            {
                $result[] = $log;
            }
        }

        return $result;
    }

    private function splitLogsByAccountAbility($logs)
    {
        $result = array();

        $clientModel = ClassRegistry::init('Client');

        foreach($logs as $log)
        {
            $clientEntity = $clientModel->getClientEntityById((string)$log['client_id']);
            $logEntity = new LogEntity();
            $logEntity->setDataSource($this->getDataSource());
            $logEntity->setClientEntity($clientEntity);
            $logEntity->fromArray($log);
            $logFields = $logEntity->toDisplay();



            //warning: stupid code
            //ignoring because the example report we were given did not show this
            //and the Accountability field is nothing special so we have no way to indicate which options are special in regards to the report.
            if ( ! isset($logFields['Accountability']) )
            {
                continue;
            }

            if ( empty($result) )
            {
                $clientArray = $clientEntity->toArray();
                foreach($clientArray['fields'] as $field)
                {
                    if ($field['name'] == 'accountability')
                    {
                        foreach($field['data']['options'] as $option)
                        {
                            if ( $option['name'] == 'For Information Only' )
                            {
                                continue;
                            }

                            $result[$option['name']] = array();
                        }
                    }
                }
            }

            $accountabilityName = '';
            if (is_array($logFields['Accountability']))
            {
                $accountabilityName = $logFields['Accountability']['name'];
            }

            if ( ! isset($result[$accountabilityName]))
            {
                $result[$accountabilityName] = array();
            }

            $result[$accountabilityName][] = $log['hrid'];
        }

        return $result;
    }

}