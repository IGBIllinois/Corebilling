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
$output_command = "update-2.4.php Script to update to version 2.4\n";
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

	$users = User::getUsers($db);
	$sql = "SELECT id, group_name, netid FROM groups ORDER BY group_name";
	$query = $db->query($sql);	
	$groups = $query->fetchAll(PDO::FETCH_ASSOC);

	//Create account for users who own groups
	foreach ($groups as $group) {
		
		if ($group['netid'] && !User::exists($db,$group['netid'])) {
			$user_object = new User($db);
			$user_ldap_info = User::get_ldap_info($ldap,$group['netid']);
			$department_id = 0;
			$rateId = 0;
			$certified = 0;
			$supervisorId = 0;
	
			$status = 0;
			$email = $group['netid'] . "@igb.illinois.edu";
			$first = "";
			$last = "";
			if (isset($user_ldap_info['givenName'])) {
				$first = $user_ldap_info['givenName'];
				$last = $user_ldap_info['sn'];
				$email = $user_ldap_info['mail'];
				$status = 1;
			}
			$user_object->create($group['netid'],
				$first,
				$last,
				$email,
				$department_id,
				$rateId,
				$status,
				User::ROLE_SUPERVISOR,
				$certified,
				$supervisorId
			);
			$user_object->setGroupIds(array($group['id']));
				
		}

	}

	//change user to supervisor if they have an account already and own a group
	foreach ($users as $user) {
		foreach ($groups as $group) {
			if ($group['netid'] && ($user['user_role_id'] != User::ROLE_SUPERVISOR) && ($group['netid'] == $user['user_name'])) {
				$user_object = new User($db);
				$user_object->load($user['id']);
				$user_object->setRoleId(User::ROLE_SUPERVISOR);
				$user_object->update();
			}

		}
	}

	//Set supervusor for each user based on the group they belong to
	foreach ($users as $user) {
		$user_obj = new user($db);
		$user_obj->load($user['id']);
		$user_groups = $user_obj->getGroupIds();
		if (count($user_groups) > 1) {
				echo "User: " . $user['user_name'];
		}
		elseif (count($user_groups) == 1) {
			$group_obj = new Group($db);
			$group_obj->load($user_groups[0]);
			if ($group_obj->getNetid() != "") {
				$sql = "SELECT id FROM users WHERE user_name=:user_name LIMIT 1";
				$query = $db->prepare($sql);
				$query->execute(array(":user_name"=>$group_obj->getNetId()));
				$result = $query->fetch(PDO::FETCH_ASSOC);
				$supervisor_id = $result['id'];
				$user_obj->setSupervisorId($supervisor_id);
				$user_obj->update();
			}
		}

	}
}
?>

