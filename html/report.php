<?php
ob_start();
require_once 'includes/main.inc.php';
require_once 'includes/authenticate.inc.php';
ob_clean();

if (isset($_POST['create_cal_report'])) {

        // TODO set start and end based on month and year
        $month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];

        $start_date = $year . $month . "01";
        try {
            $end_date = (new DateTime($start_date))->format('Y-m-t 23:59:59');
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $data = Reservation::getEventsInRangeForSpreadsheet(
            $db,
            $start_date,
            $end_date,
            $_POST['user_id'],
            $_POST['device_id'],
            $_POST['training']
        );
        $filename = "calendar-" . $month . "-" . $year . "." . $type;
}

elseif (isset($_POST['create_data_report'])) {
	$month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];
        $data = data_functions::get_data_bill($db,$month,$year);
	$filename = "data-" . $month . "-" . $year . "." . $type;
}

elseif (isset($_POST['create_data_dir_report'])) {
	$type = $_POST['report_type'];
	$data = data_functions::get_dir_report($db);
	$filename = "data_dir." . $type;


}
switch ($type) {
	case 'csv':
		\IGBIllinois\report::create_csv_report($data, $filename);
		break;
	case 'xlsx':
		\IGBIllinois\report::create_excel_2007_report($data, $filename);
		break;
	}
?>
