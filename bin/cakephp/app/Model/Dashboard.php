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
        'widgets'       => array('type' => null),
        'shares'        => array('type' => null),

        'email'         => array('type' => 'string', 'length'=>255),
        'password'      => array('type' => 'string'),
        'company'       => array('type' => 'text'),
        'phoneNumber'   => array('type' => 'integer'),
        'role'          => array('type' => 'string'),
        'client'        => array('type' => 'string'),
        'deleted'       => array('type' => 'boolean'),

        'favouriteDashboards'   => array('type' => null),
        'created'       => array('type' => 'datetime'),
        'modified'      => array('type' => 'datetime'),
    );

    public function serializeWidgetForHighcharts($widget)
    {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => $widget['type'],
            'marginRight' => 130,
            'marginBottom' => 25
        );

        $chart->title = array(
            'text' => $widget['title'],
            'x' => - 20
        );

        $chart->xAxis->categories = array(
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        );

        $chart->yAxis = array(
            'title' => array(
                'text' => 'Temperature (°C)'
            ),
            'plotLines' => array(
                array(
                    'value' => 0,
                    'width' => 1,
                    'color' => '#808080'
                )
            )
        );
        $chart->legend = array(
            'layout' => 'vertical',
            'align' => 'right',
            'verticalAlign' => 'top',
            'x' => - 10,
            'y' => 100,
            'borderWidth' => 0
        );

        $chart->series[] = array(
            'name' => 'Tokyo',
            'data' => array(
                7.0,
                6.9,
                9.5,
                14.5,
                18.2,
                21.5,
                25.2,
                26.5,
                23.3,
                18.3,
                13.9,
                9.6
            )
        );
        $chart->series[] = array(
            'name' => 'New York',
            'data' => array(
                - 0.2,
                0.8,
                5.7,
                11.3,
                17.0,
                22.0,
                24.8,
                24.1,
                20.1,
                14.1,
                8.6,
                2.5
            )
        );
        $chart->series[] = array(
            'name' => 'Berlin',
            'data' => array(
                - 0.9,
                0.6,
                3.5,
                8.4,
                13.5,
                17.0,
                18.6,
                17.9,
                14.3,
                9.0,
                3.9,
                1.0
            )
        );
        $chart->series[] = array(
            'name' => 'London',
            'data' => array(
                3.9,
                4.2,
                5.7,
                8.5,
                11.9,
                15.2,
                17.0,
                16.6,
                14.2,
                10.3,
                6.6,
                4.8
            )
        );

//        $chart->tooltip->formatter = new HighchartJsExpr(
//            "function() { return ''+ this.series.name +'
//        '+ this.x +': '+ this.y +'°C';}");

        return $chart->renderOptions();

    }



    public function serializeDashboardForHighcharts()
    {
        $chart = new Highchart();

        $chart->chart = array(
            'type' => 'column',
            'marginRight' => 130,
            'marginBottom' => 25
        );

        $chart->title = array(
            'text' => 'Monthly Average Temperature',
            'x' => - 20
        );
        $chart->subtitle = array(
            'text' => 'Source: WorldClimate.com',
            'x' => - 20
        );

        $chart->xAxis->categories = array(
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        );

        $chart->yAxis = array(
            'title' => array(
                'text' => 'Temperature (°C)'
            ),
            'plotLines' => array(
                array(
                    'value' => 0,
                    'width' => 1,
                    'color' => '#808080'
                )
            )
        );
        $chart->legend = array(
            'layout' => 'vertical',
            'align' => 'right',
            'verticalAlign' => 'top',
            'x' => - 10,
            'y' => 100,
            'borderWidth' => 0
        );

        $chart->series[] = array(
            'name' => 'Tokyo',
            'data' => array(
                7.0,
                6.9,
                9.5,
                14.5,
                18.2,
                21.5,
                25.2,
                26.5,
                23.3,
                18.3,
                13.9,
                9.6
            )
        );
        $chart->series[] = array(
            'name' => 'New York',
            'data' => array(
                - 0.2,
                0.8,
                5.7,
                11.3,
                17.0,
                22.0,
                24.8,
                24.1,
                20.1,
                14.1,
                8.6,
                2.5
            )
        );
        $chart->series[] = array(
            'name' => 'Berlin',
            'data' => array(
                - 0.9,
                0.6,
                3.5,
                8.4,
                13.5,
                17.0,
                18.6,
                17.9,
                14.3,
                9.0,
                3.9,
                1.0
            )
        );
        $chart->series[] = array(
            'name' => 'London',
            'data' => array(
                3.9,
                4.2,
                5.7,
                8.5,
                11.9,
                15.2,
                17.0,
                16.6,
                14.2,
                10.3,
                6.6,
                4.8
            )
        );

//        $chart->tooltip->formatter = new HighchartJsExpr(
//            "function() { return ''+ this.series.name +'
//        '+ this.x +': '+ this.y +'°C';}");

        return $chart->renderOptions();

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

    public function generateReport($dashboard, $reportName)
    {
        $phpWord = new PHPWord();
        $section= $phpWord->createSection();
        $imageFilename = 'chart1.png';
        $this->getChartImage($this->serializeDashboardForHighcharts(), $imageFilename);
        $section->addImage(TMP . $imageFilename);

        $objWriter = PHPWord_IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save(TMP . $reportName);

        return TMP . $reportName;
    }
}