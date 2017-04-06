<?php

App::uses('SearchController', 'Controller');
App::uses('PreslogAuthComponent', 'Controller/Component');
App::uses('CakeEmail', 'Network/Email');

use \PHPExcel;
use Preslog\Logs\Entities\LogEntity;

class PrimeLogShell extends AppShell {

    public $uses = array('Log', 'Client', 'User');

    /**
     * Find logs for PRIME withing query time.
     * @param $query  - period of logs.
     * @return array
     */
    protected function findPrimeLogs($query)
    {
        //Get only PRIME
        $clientModel = ClassRegistry::init('Client');
        $clientObjs = $clientModel->find('all',
            array(
                'conditions' => array(
                    'name' => "PRIME"
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
            $clientModel = ClassRegistry::init('Client');
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

        date_default_timezone_set('Australia/Sydney');
        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
        // Create new PHPExcel object
        echo date('H:i:s') , " Create new PHPExcel object" , EOL;
        $objPHPExcel = new PHPExcel();
        // Set document properties
        echo date('H:i:s') , " Set document properties" , EOL;
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("PHPExcel Test Document")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");
        // Add some data
        echo date('H:i:s') , " Add some data" , EOL;

        $sheet = $objPHPExcel->getActiveSheet();

        foreach(range('A', 'Z') as $column_id){
            if(in_array($column_id, array('G', 'H', 'I', 'J', 'K'))){
                $sheet->getColumnDimension($column_id)->setWidth('40');
            } else if (in_array($column_id, array('A'))){
                $sheet->getColumnDimension($column_id)->setWidth('20');
            } else {
                $sheet->getColumnDimension($column_id)->setWidth('30');
            }

            if(in_array($column_id, range('A', 'T'))){

                $sheet->getStyle($column_id+'1')->applyFromArray(array(
                    'font' => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FFFFFF'),
                        'size'  => 14,
                        'name'  => 'Arial'
                    )

                ));

                $sheet->getStyle($column_id+'2')->applyFromArray(array(
                    'font' => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FFFFFF'),
                        'size'  => 11,
                        'name'  => 'Arial'
                    ),
                ));
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

        $sheet->getStyle('A1:T3')->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );

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
                'color' => array('rgb' => 'FF0000')
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

        foreach(range(1,3) as $row_id){
            $sheet->getRowDimension($row_id)->setRowHeight('25');
        }

        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1','Wednesday');
        $sheet->getStyle('A1')->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
        );
        $sheet->setCellValue('C1', '03/22/2017');
        $sheet->mergeCells('G1:I1');
        $sheet->setCellValue('G1','PRIME TELEVISION ON-AIR REPORT');
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
        $yesterday = $DateTime->modify('-100 Day')->format('Y-m-d');

        $logs = $this->findPrimeLogs('created > ' . $yesterday . '', true);

        $logCount = 3;
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

// Rename worksheet
        echo date('H:i:s') , " Rename worksheet" , EOL;
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
// Save Excel 2007 file
        echo date('H:i:s') , " Write to Excel2007 format" , EOL;
        $callStartTime = microtime(true);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $path = getcwd()."\\Command\\excelfile\\prime.xlsx";
        $objWriter->save($path);
        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;

        echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
        echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
        echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
// Save Excel5 file
        echo date('H:i:s') , " Write to Excel5 format" , EOL;
        $callStartTime = microtime(true);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $path = getcwd()."\\Command\\excelfile\\prime.xls";
        $objWriter->save($path);
        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        echo date('H:i:s') , " File written to " , str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
        echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
        echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
// Echo memory peak usage
        echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;
// Echo done
        echo date('H:i:s') , " Done writing files" , EOL;
        echo 'Files have been created in ' , getcwd() , EOL;

        $logs = array();
        $niceDate = "14th";
        $dashboardId = "some";
        $Email = new CakeEmail();
        $Email->config('default')
            ->subject('Prime Logs')
            ->template('prime-log-email')
            ->viewVars(compact('logs', 'niceDate', 'dashboardId'))
            ->emailFormat('html')
            ->from('mohammed.fahad@4mation.com.au')
            ->to('mohammed.fahad@4mation.com.au')
            ->attachments(array(
                'prime.xls' => array(
                    'file' =>  getcwd()."\\Command\\excelfile\\prime.xls",
                    'mimetype' => 'application/vnd.ms-excel'
                )))
            ->send();
    }

}