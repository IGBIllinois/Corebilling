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
$output_command = "disable_users.php Removes access for users who are disabled\n";
$output_command .= "Usage: php disable_users.php \n";
$output_command .= "	--dry-run	Do dry run, output only\n";
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
//Sets up ldap connection
$ldap = new \IGBIllinois\ldap(settings::get_ldap_host(),
	settings::get_ldap_base_dn(),
	settings::get_ldap_port(),
	settings::get_ldap_ssl(),
	settings::get_ldap_tls());

$log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());	
$users = User::getUsers($db,User::DISABLED);

//Disables device access
$devices = Device::getAllDevices($db);
foreach ($users as $user) {
	$user_obj = new User($db);
	$user_obj->load($user['id']);
	$user_obj->setGroupIds(array());
	$device_access = array();
	foreach ($devices as $device) {
		if ($user_obj->hasAccessTo($device['id'])) {
			$user_obj->removeAccessTo($device['id']);
		}
	}
}

//Removes User from all groups
$groups = Group::getAllGroups($db);
foreach ($groups as $group) {
	$group_obj = new Group($db);
	$group_obj->load($group['id']);
	$members = $group_obj->getMembers();
	if (!count($members)) {
		$group_obj->delete();
	}
}
?>

