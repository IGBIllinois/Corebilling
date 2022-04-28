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
$output_command = "data.php Inserts data usage into database\n";
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
}
else {

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

	$handle = fopen("group_folders.csv","r");
	$all_users = User::getUsers($db);
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			$user_name = trim(rtrim($line));
			$path = "/core-server/groups/" . $user_name;
			echo $path . "\n";
			$user_obj = new user($db);
			$sql = "SELECT id FROM users where user_name=:user_name LIMIT 1";
			$query = $db->prepare($sql);
			$query->execute(array(':user_name'=>$user_name));
			$result = $query->fetch(PDO::FETCH_ASSOC);
			$user_id = $result['id'];
			echo "User ID: " . $user_id . "\n";
			$group_sql = "SELECT id FROM groups WHERE netid=:user_name LIMIT 1";
			$group_query = $db->prepare($group_sql);
			$group_query->execute(array(':user_name'=>$user_name));
			$group_result = $group_query->fetch(PDO::FETCH_ASSOC);
			$group_id = $group_result['id'];
			echo "Group ID: " . $group_id . "\n";
			if (!data_dir::get_id_by_directory($db,$path)) {
				$insert_sql = "INSERT INTO data_dir(data_dir_group_id,data_dir_user_id,data_dir_path) VALUES(:group_id,:user_id,:path)";

				$insert_query = $db->prepare($insert_sql);
				$insert_query->execute(array(':group_id'=>$group_id,':user_id'=>$user_id,':path'=>$path));
			}
			

		}

	}
}
?>

