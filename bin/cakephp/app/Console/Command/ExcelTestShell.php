<?php


use \PHPExcel;

class ExcelTestShell extends AppShell {

    public function main() {

        $this->loadModel('Log');
        $this->loadModel('Client');
        $this->loadModel('User');

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
            }else{
                $sheet->getColumnDimension($column_id)->setAutoSize(ture);
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