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
$output_command = basename(__FILE__) . " Gets List of Active Users\n";
$output_command .= "Usage: php " . basename(__FILE__) . "\n";
$output_command .= "	--username	Output Username (default)\n";
$output_command .= "	--email		Output email\n";
$output_command .= "    -h, --help	Display help menu\n";

$username = true;
$email = false;
//Parameters
$shortopts = "h";

$longopts = array(
	"username",
	"email",
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

if (isset($options['username']) && isset($options['email'])) {
	echo $output_command;
	exit(1);
}
if (isset($options['username'])) {
	$username = true;
	$email = false;
}
elseif (isset($options['email'])) {
	$username = false;
	$email = true;
}
try {
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
	die("Error initializing PDO: " . $e->getMessage());
}

$users = User::getUsers($db,User::ACTIVE);
$i = 0;
foreach ($users as $user) {
	$i++;
	if ($username) {
		echo $user['user_name'];
	}
	elseif ($email) {
		echo $user['email'];
	}
	if (count($users) != $i) {
		echo "\n";
	}
}
?>

