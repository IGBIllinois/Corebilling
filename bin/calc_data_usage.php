#!/usr/bin/env php
<?php 
chdir(dirname(__FILE__));

$include_paths = array(__DIR__ . '/../libs');
set_include_path(get_include_path() . ":" . implode(':',$include_paths));

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

require_once __DIR__ . '/../conf/app.inc.php';
require_once __DIR__ . '/../conf/config.inc.php';
require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set(settings::get_timezone());

//Command parameters
$output_command = "data.php Averages out the data usage for a month and creates a data bill\n";
$output_command .= "Defaults to previous month\n";
$output_command .= "Usage: php data.php \n";
$output_command .= "	-y, --year		Year (YYYY)\n";
$output_command .= "	-m, --month		Month (MM)\n";
$output_command .= "	-h, --help              Display help menu\n";

//Parameters
$shortopts = "";
$shortopts .= "h";
$shortopts .= "y:";
$shortopts .= "m:";

$longopts = array(
        "help",
	"year::",
	"month::"
);

//Following code is to test if the script is being run from the command line or the apache server.
if (php_sapi_name() != 'cli') {
	echo "Error: This script can only be run from the command line.";
	exit();
}

$year = date("Y",strtotime("-1 month"));
$month = date("m",strtotime("-1 month"));

$options = getopt($shortopts,$longopts);
if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}
if ((isset($options['y']) || isset($options['year'])) 
	&& (isset($options['m']) || isset($options['month']))) {
	if (isset($options['y'])) {
		$year = $options['y'];
	}
	elseif (isset($options['year'])) {
		$year = $options['year'];
	}
	if (isset($options['m'])) {
		$month = $options['m'];
	}
	elseif (isset($options['month'])) {
		$month = $options['month'];
	}
}

try {
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
	die("Error initializing PDO: " . $e->getMessage());
}

$log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
$directories = data_functions::get_all_directories($db);
foreach ($directories as $directory) {
	$data_dir = new data_dir($db,$directory['data_dir_id']);
	$data_usage = $data_dir->get_usage($month,$year);
	$count = count($data_usage);
	$sum = 0;
	foreach ($data_usage as $usage) {
		$sum += $usage['data_usage_bytes'];
	}
	$average = round($sum / $count);
	try {			
		$result = $data_dir->add_data_bill($month,$year,$average);
	}
	catch (Exception $e) {
		$log_file->send_log($e->getMessage(),\IGBIllinois\log::ERROR);
	}
	if ($result) {
		$log_file->send_log("Data Bill: Directory: " . $data_dir->get_directory() . " successfully added to data bill");
	}
}
	
	

?>

