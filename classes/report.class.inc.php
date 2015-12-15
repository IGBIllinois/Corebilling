<?php
require_once 'PHPExcel.php';
class report {
	// Outputs the given data as an Excel 2003 document with the given filename
	public static function create_xls_report($data,$filename){
		$excel_file = self::create_generic_excel($data);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename='.$filename);
		header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
		header('Pragma: public');
		$writer = PHPExcel_IOFactory::createWriter($excel_file, 'Excel5');
		$writer->save('php://output');
	}
	
	// Outputs the given data as an Excel 2007+ document with the given filename
	public static function create_xlsx_report($data,$filename){
		$excel_file = self::create_generic_excel($data);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=" . $filename);
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		$writer = PHPExcel_IOFactory::createWriter($excel_file,'Excel2007');
		$writer->save('php://output');
	}
	
	// Performs setup for either type of excel file
	public static function create_generic_excel($data) {
		$excel_file = new PHPExcel();
		$excel_file->setActiveSheetIndex(0);
		if (count($data) !== 0 ) {
			//Creates headers
			$headings = array_keys($data[0]);
			for ($i=0;$i<count($headings);$i++) {
				$excel_file->getActiveSheet()->setCellValueByColumnAndRow($i,1,$headings[$i]);
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getFont()->setBold(true);
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getFont()->setUnderline(PHPExcel_STYLE_Font::UNDERLINE_SINGLE);
				$excel_file->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
			}
			//Adds data
			$rows = count($data);
			$start_row = 2;
			foreach ($data as $row_data) {
				$column=0;
				foreach ($row_data as $key => $value) {
					$excel_file->getActiveSheet()->setCellValueByColumnAndRow($column,$start_row,$value);
					if (($key == 'Cost') || ($key == 'Billed Amount')) {
						$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$start_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
					}
					else {
						$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$start_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
						$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$start_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					}
					$column++;
				}
				$start_row++;
			}
		}
		$excel_file->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$excel_file->getActiveSheet()->getPageSetup()->setFitToPage(true);
		return $excel_file;
	}
	
	// Outputs the given data as a CSV file with the given filename
	public static function create_csv_report($data,$filename){
		$delimiter = ",";
		$file_handle = fopen('php://output','w');
		$headings = array_keys($data[0]);
		ob_start();
		fputcsv($file_handle,$headings,$delimiter);
		foreach ($data as $row) {
			fputcsv($file_handle,$row,$delimiter);
		}
		fclose($file_handle);
		$result = ob_get_clean();
		//Sets headers then downloads the csv report file.
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/csv');
		header("Content-Disposition:attachment; filename=" . $filename);
		echo $result;	
	}
}