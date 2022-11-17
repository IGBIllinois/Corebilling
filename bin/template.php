#!/usr/bin/env php
<?php 
chdir(dirname(__FILE__));

$include_paths = array('../libs');
set_include_path(get_include_path() . ":" . implode(':',$include_paths));

function my_autoloader($class_name) {
        if(file_exists(__DIR__ . "/../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

require_once __DIR__ . '/../conf/app.inc.php';
require_once __DIR__ . '/../conf/config.inc.php';
require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set(settings::get_timezone());

//Command parameters
$output_command = basename(__FILE__) . " Template\n";
$output_command .= "Usage: php " . basename(__FILE__) . "\n";
$output_command .= "	--dry-run	Do dry run\n";
$output_command .= "    -h, --help	Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
	"dry-run",
        "help"
);

//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
	exit("Error: This script can only be run from the command line.");
}

$options = getopt($shortopts,$longopts);

if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}

try {
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
	die("Error initializing PDO: " . $e->getMessage());
}

$log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());	


?>

