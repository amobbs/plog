<?php

App::uses('SearchController', 'Controller');
App::uses('PreslogAuthComponent', 'Controller/Component');
App::uses('CakeEmail', 'Network/Email');

use \PHPExcel;
use Preslog\Logs\Entities\LogEntity;

class PrimeLogShell extends AppShell {

    public $uses = array('Log', 'Client', 'User');
    CONST CLIENT_NAME = 'PRIME';
    CONST TO_EMAIL = 'PrimeDailyReport@mediahubaustralia.com.au';
    CONST LOG_PERIOD = '-1 Day';

    /**
     * Find logs for PRIME withing query time.
     * @param $query  - period of logs.
     * @return array
     */
    protected function findLogs($query)
    {
        //Get only PRIME
        $clientModel = ClassRegistry::init('Client');
        $clientObjs = $clientModel->find('all',
            array(
                'conditions' => array(
                    'name' => self::CLIENT_NAME
                )
            ));
        $clients = array();
        foreach($clientObjs as $c)
        {
            $clients[] = $c['Client'];
        }

        $results = $this->Log->findByQuery($query, $clients, true);
        // Error on query failure
        if ( isset($results['ok']) && !$results['ok'] )
        {
            return array(
                'query' => $query,
                'errors' => $results['errors'],
            );
        }
        // Get size of query results
        $clients = array();
        //loop results for client, created by users and any other info we will need to grab for display
        foreach ($results as $k=>$result)
        {
            // Collate the list of clients for fetching the field format
            $clients[] = $result['Log']['client_id'];
        }

        //list all fields that we can use to sort these logs
        $allFieldNames = array();

        //loop through the logs again and reformat them for display
        $logs = array();
        foreach ($results as $k=>$rawLog) {

            // Fetch client entity
            $clientEntity = $clientModel->getClientEntityById( $rawLog['Log']['client_id'] );

            // Skip clients that don't load
            if ( !$clientEntity )
            {
                continue;
            }

            // Load the log schema by the client
            $log = new LogEntity();
            $log->setDataSource( $this->Log->getDataSource() );
            $log->setClientEntity($clientEntity);

            // Interpret the log data to be saved
            $log->fromArray( $rawLog['Log'] );

            // Generate fields list
            $fields = $log->toDisplay();

            // New field list per log
            $fieldList = array();

            // Track all field names
            foreach ($fields as $key=>$value)
            {
                if ( ! $clientEntity->isAttributeLabel($key))
                {
                    // Track field names
                    $allFieldNames[$key] = true;
                }

                // Convert to arrangement the client is expecting
                $logData = array(
                    'title'=>$key,
                    'value'=>$value,
                );

                // Put to field list
                $fieldList[] = $logData;
            }

            // Put to log list
            $logs[] = array(
                'id' => $rawLog['Log']['hrid'],
                'attributes'=>$fieldList
            );

        }

        // Return the Results and the corresponding Client opts
        return $logs;
    }

    public function main() {
        define('EOL',(isCli()) ? PHP_EOL : '<br />');
        $objPHPExcel = new PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Preslog")
            ->setLastModifiedBy("Preslog")
            ->setTitle("Logs")
            ->setSubject(self::CLIENT_NAME." Logs")
            ->setDescription("Logs document generated for ".self::CLIENT_NAME)
            ->setKeywords("office PHPExcel php")
            ->setCategory("logs");

        $sheet = $objPHPExcel->getActiveSheet();

        foreach(range('A', 'Z') as $column_id){
            //set width for cell
            if(in_array($column_id, array('G', 'H', 'I', 'J', 'K'))){
                $sheet->getColumnDimension($column_id)->setWidth('40');
            } else if (in_array($column_id, array('A'))){
                $sheet->getColumnDimension($column_id)->setWidth('20');
            } else {
                $sheet->getColumnDimension($column_id)->setWidth('30');
            }

            if(in_array($column_id, range('A', 'T'))){
                //set font attributes for first row
                $sheet->getStyle($column_id+'1')->applyFromArray(array(
                    'font' => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FFFFFF'),
                        'size'  => 14,
                        'name'  => 'Arial'
                    )

                ));
                //set font attributes for second row
                $sheet->getStyle($column_id+'2')->applyFromArray(array(
                    'font' => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FFFFFF'),
                        'size'  => 11,
                        'name'  => 'Arial'
                    ),
                ));
                //set font attributes for third row
                $sheet->getStyle($column_id+'3')->applyFromArray(array(
                    'font' => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FFFFFF'),
                        'size'  => 9,
                        'name'  => 'Arial'
                    ),
                ));
            }
        }

        //align static column's text
        $sheet->getStyle('A1:T3')->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );

        //setting up colors for different cell
        $sheet->getStyle('A1:K3')->applyFromArray(array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '0070C0')
            )
        ));
        $sheet->getStyle('L1:T3')->applyFromArray(array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF5D61')
            )
        ));
        $sheet->getStyle('G2')->applyFromArray(array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF8080')
            )
        ));
        $sheet->getStyle('H2')->applyFromArray(array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '7030A0')
            )
        ));
        $sheet->getStyle('I2')->applyFromArray(array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '00B050')
            )
        ));

        //set wrap text for all rows
        $sheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        //set fixed size for 1st three rows
        foreach(range(1,3) as $row_id){
            $sheet->getRowDimension($row_id)->setRowHeight('25');
        }

        //static cells for headers.
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1',date('l'));
        $sheet->getStyle('A1')->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
        );
        $sheet->setCellValue('C1', date('d/m/Y'));
        $sheet->mergeCells('G1:I1');
        $sheet->setCellValue('G1',self::CLIENT_NAME.' TELEVISION ON-AIR REPORT');
        $sheet->mergeCells('L1:T2');
        $sheet->setCellValue('L1','ASSIGNMENTS AND NOTIFICATIONS');
        $sheet->setCellValue('G2','OFF-AIR');
        $sheet->setCellValue('H2','CAPTIONS');
        $sheet->setCellValue('I2','MAKE GOOD');
        $sheet->setCellValue('A3', 'ID');
        $sheet->setCellValue('B3', 'Date:');
        $sheet->setCellValue('C3', 'Severity:');
        $sheet->setCellValue('D3', 'Duration:');
        $sheet->setCellValue('E3', 'Why It Happened:');
        $sheet->setCellValue('F3', 'Programme or Event');
        $sheet->setCellValue('G3', 'Networks');
        $sheet->setCellValue('H3', 'Brief Description');
        $sheet->setCellValue('I3', 'Details of What Happened:');
        $sheet->setCellValue('J3', 'What Action Taken:');
        $sheet->setCellValue('K3', 'Follow Up or Resolution:');
        $sheet->setCellValue('L3', 'Bdcst Ops');
        $sheet->setCellValue('M3', 'Comm Media');
        $sheet->setCellValue('N3', 'Prog / Prom');
        $sheet->setCellValue('O3', 'News');
        $sheet->setCellValue('P3', 'Br Eng');
        $sheet->setCellValue('Q3', 'Tx Eng');
        $sheet->setCellValue('R3', 'On Air');
        $sheet->setCellValue('S3', 'Prod');
        $sheet->setCellValue('T3', 'Department Comments');
        $sheet->mergeCells('J1:K2');

        $DateTime = new DateTime('now');
        $DateTime_yesterday = $DateTime->modify(self::LOG_PERIOD);
        $yesterday = $DateTime_yesterday->format('Y-m-d H:i');
        $logs = $this->findLogs('created > ' . $yesterday . '', true);
        //Log count set to three since 1st 3 rows are booked for Static cell
        $logCount = 3;

        //Filling up log values in columns
        foreach($logs as $log){
            $logCount ++;
            foreach($log['attributes'] as $attribute){
                switch ($attribute['title']){
                    case 'ID':
                        $sheet->setCellValue('A'.$logCount, $attribute['value']);
                        break;
                    case 'Date:':
                        $sheet->setCellValue('B'.$logCount, $attribute['value']);
                        break;
                    case 'Severity:':

                        if($attribute['value'] == 'Level 1 - OUTAGE Over 10 seconds'){
                            $sheet->getStyle('A'.$logCount.':K'.$logCount)->applyFromArray(array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF8080')
                                )
                            ));
                        }

                        if($attribute['value'] == 'Level 2 - OUTAGE Under 10 seconds'){
                            $sheet->getStyle('A'.$logCount.':K'.$logCount)->applyFromArray(array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'FF8080')
                                )
                            ));
                        }

                        if($attribute['value'] == 'Level 1 - CAPTION OUTAGE'){
                            $sheet->getStyle('A'.$logCount.':K'.$logCount)->applyFromArray(array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => '7030A0')
                                )
                            ));
                        }
                        $sheet->setCellValue('C'.$logCount, $attribute['value']);
                        break;
                    case 'Duration:':
                        $sheet->setCellValue('D'.$logCount, $attribute['value']);
                        break;
                    case 'Why It Happened:':
                        $sheet->setCellValue('E'.$logCount, $attribute['value']);
                        break;
                    case 'Programme or Event':
                        $sheet->setCellValue('F'.$logCount, $attribute['value']);
                        break;
                    case 'Networks':
                        $sheet->setCellValue('G'.$logCount, $attribute['value']);
                        break;
                    case 'Brief Description':
                        $sheet->setCellValue('H'.$logCount, $attribute['value']);
                        break;
                    case 'Details of What Happened:':
                        $sheet->setCellValue('I'.$logCount, $attribute['value']);
                        break;
                    case 'What Action Taken:':
                        $sheet->setCellValue('J'.$logCount, $attribute['value']);
                        break;
                    case 'Follow Up or Resolution:':
                        $sheet->setCellValue('K'.$logCount, $attribute['value']);
                        break;
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('Logs');
        $objPHPExcel->setActiveSheetIndex(0);

        // Save Excel5 file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $yesterday_date = $DateTime_yesterday->format('dmY');
        $path = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'excelfile'.DIRECTORY_SEPARATOR.self::CLIENT_NAME.'_MediaHub_Preslog_Report_'.$yesterday_date.'.xls';
        $objWriter->save($path);

        $Email = new CakeEmail();
        $Email->config('default')
            ->subject(self::CLIENT_NAME.' MediaHub Preslog Report '.$yesterday_date)
            ->template('prime-log-email')
            ->emailFormat('html')
            ->viewVars(compact('yesterday_date'))
            ->to(self::TO_EMAIL)
            ->cc('letigre@4mation.com.au')
            ->attachments(array(
                self::CLIENT_NAME.'_MediaHub_Preslog_Report_'.$yesterday_date.'.xls' => array(
                    'file' =>  dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'excelfile'.DIRECTORY_SEPARATOR.self::CLIENT_NAME.'_MediaHub_Preslog_Report_'.$yesterday_date.'.xls',
                    'mimetype' => 'application/vnd.ms-excel'
                )))
            ->send();
    }
}