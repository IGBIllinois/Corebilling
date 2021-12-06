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

//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.";
}
else {


	try {
		$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
	} catch (PDOException $e) {
		die("Error initializing PDO: " . $e->getMessage());
	}
	//Sets up ldap connection
	$authen = new LdapAuth ( LDAP_HOST, LDAP_PEOPLE_DN, LDAP_GROUP_DN,LDAP_PORT);
	if(LDAPMAN_API_ENABLED){
        	$ldapman = new LdapManager(LDAPMAN_API_URL, LDAPMAN_API_USERNAME, LDAPMAN_API_PASSWORD);
	} else {
        	$ldapman = new LdapManager(LDAPMAN_API_URL);
	}

	$ldap = new Ldap(LDAP_HOST . " " . LDAP_PORT);

        $ldap->setOption(LDAP_OPT_PROTOCOL_VERSION,3);
        $ldap->setOption(LDAP_OPT_REFERRALS,0);
	$ldap->connect();

        //attempt to connect to ldap server
	$sql = "SELECT * FROM users WHERE status_id=0";
	$query = $db->prepare($sql);
	$result = $query->execute();
	$all_users = $query->fetchAll(PDO::FETCH_ASSOC);
	$deviceList = Device::getAllDevices($db);
	foreach ($all_users as $user) {
		if ($user['status_id'] == User::DISABLED)  {
			$user_obj = new User($db);
			$user_obj->load($user['id']);
			$search_filter = "(uid=".$user_obj->getUsername() .")";
			
                        //Search filter for the member attribute of the group
                        $searchMembersFilter = array("uid");
			$groupSearchResults = $ldap->searchSubtree("ou=People,dc=igb,dc=uiuc,dc=edu",$search_filter,$searchMembersFilter);

                        $entries = $groupSearchResults->getEntries();
			if ($entries['count'] == 0) {
				echo "User: " . $user_obj->getUsername() . "\n";
				$empty_array = [];	
				$user_obj->setGroupIds($empty_array);
    				foreach ($deviceList as $deviceInfo) {
            				if ($user_obj->hasAccessTo($deviceInfo['id'])) {
						if (LDAPMAN_API_ENABLED) {
							$ldapman->removeGroupMember(LDAPMAN_DEVICE_PREFIX . $deviceInfo['device_name'],$user_obj->getUsername());
	                			}
						$user_obj->removeAccessTo($deviceInfo['id']);
            				}
        			}
			}
    		}
			

	}


	$group_sql = "SELECT * FROM groups WHERE netid IS NOT NULL";
	$query = $db->prepare($group_sql);
	$result = $query->execute();
	$all_groups = $query->fetchAll(PDO::FETCH_ASSOC);
	$group_obj = new Group($db);
	foreach ($all_groups as $group) {
			$group_obj->load($group['id']);
			$members = $group_obj->getMembers();
			if (count($members) == 0) {
				echo "Group: " . $group['group_name'] . " LDAP: " . LDAPMAN_PI_PREFIX . $group['netid'] . "\n";

			}

	}
}

?>

