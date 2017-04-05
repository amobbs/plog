<?php

App::uses('SearchController', 'Controller');
App::uses('PreslogAuthComponent', 'Controller/Component');

use \PHPExcel;
use Preslog\Logs\Entities\LogEntity;

class PrimeLogShell extends AppShell {

    public $uses = array('Log', 'Client', 'User');

    protected function executeSearch($query)
    {
        $results = $this->Log->findByQuery($query,true);
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

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');
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

            if(in_array($column_id, range('A', 'K'))){

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
        $sheet->getStyle('A1:K3')->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );

        $sheet->getStyle('A1:K3')->applyFromArray(array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '0070C0')
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

        $sheet->mergeCells('J1:K2');

        $DateTime = new DateTime('now');
        $yesterday = $DateTime->modify('-100 Day')->format('Y-m-d');

        $logs = $this->executeSearch('created > ' . $yesterday . '', true);

        $logCount = 3;
        foreach($logs as $log){
            $logCount ++;
            $sheet->setCellValue('A'.$logCount, $log['attributes'][0]['value']);
            $sheet->setCellValue('B'.$logCount, $log['attributes'][7]['value']);
            $sheet->setCellValue('C'.$logCount, $log['attributes'][18]['value']);
            $sheet->setCellValue('D'.$logCount, $log['attributes'][8]['value']);
            $sheet->setCellValue('E'.$logCount, $log['attributes'][13]['value']);
            $sheet->setCellValue('F'.$logCount, $log['attributes'][9]['value']);
            $sheet->setCellValue('G'.$logCount, $log['attributes'][20]['value']);
            $sheet->setCellValue('H'.$logCount, $log['attributes'][12]['value']);
            $sheet->setCellValue('I'.$logCount, $log['attributes'][15]['value']);
            $sheet->setCellValue('J'.$logCount, $log['attributes'][16]['value']);
            $sheet->setCellValue('K'.$logCount, $log['attributes'][17]['value']);
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
        $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
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
        $objWriter->save(str_replace('.php', '.xls', __FILE__));
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
    }


}