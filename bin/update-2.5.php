#!/usr/bin/env php
<?php 
chdir(dirname(__FILE__));

$include_paths = array('../libs');
set_include_path(get_include_path() . ":" . implode(':',$include_paths));

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

require_once '../conf/app.inc.php';
require_once '../conf/config.inc.php';
require_once '../vendor/autoload.php';

date_default_timezone_set(settings::get_timezone());

//Command parameters
$output_command = "update-2.5.php Updates to version 2.5\n";
$output_command .= "Usage: php data.php \n";
$output_command .= "	--dry-run	Do dry run, do not add to database\n";
$output_command .= "    -h, --help              Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
	"dry-run",
        "help"
);

//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.";
	exit();
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
	//Sets up ldap connection
	$ldap = new \IGBIllinois\ldap(settings::get_ldap_host(),
                        settings::get_ldap_base_dn(),
                        settings::get_ldap_port(),
                        settings::get_ldap_ssl(),
                        settings::get_ldap_tls());
	$log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());	

	$data_dirs = data_functions::get_all_directories($db);
	foreach ($data_dirs as $dir) {
		$user_id = $dir['data_dir_user_id'];
		$user_object = new User($db);
		$user_object->load($user_id);
		$username = $user_object->getUsername();
		$sql = "UPDATE users SET status=1,email=:email WHERE id=:id LIMIT 1";
		$params = array(":id"=>$user_id,
				':email'=>$username . "@illinois.edu"
		);
		$query = $db->prepare($sql);
		$result = $query->execute($params);
		if ($result) {
			echo "Updated user " . $user_id . " to active\n";
		}	
	}

?>

