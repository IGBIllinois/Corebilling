#!/usr/bin/env/php
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

//Sets up ldap connection
if(LDAPMAN_API_ENABLED){
	$ldapman = new LdapManager(LDAPMAN_API_URL, LDAPMAN_API_USERNAME, LDAPMAN_API_PASSWORD);
} else {
	$ldapman = new LdapManager(LDAPMAN_API_URL);
}

$selectedUser = new User($db);
$selectedGroup = new Group($db);
$allUsers = User::getAllActiveUsers($db);
$allDevices = Device::getAllDevices($db);
$allGroups = Group::getAllGroups($db);


// First, add ldap groups
foreach($allGroups as $group){
    if($group['netid'] !== null){
        $gid = LDAPMAN_PI_PREFIX.$group['netid'];
        if($ldapman->getGroup($gid) === null){
            echo "Adding ldap group '$gid'\n";
            $ldapman->addGroup($gid, "Core ".$group['netid']." PI group");
        }
    }
}

// Next, add users to groups
foreach($allUsers as $user){
    echo "Processing ".$user['user_name']."\n";
    $selectedUser->load($user['id']);
    foreach($allDevices as $device){
        $ldapGroup = LDAPMAN_DEVICE_PREFIX. $device['device_name'];
        if($selectedUser->hasAccessTo($device['id'])){
            if(!$ldapman->isMemberOf($user['user_name'], $ldapGroup)) {
                echo "\tAdding " . $user['user_name'] . " to " . $ldapGroup . "... ";
                if ( $ldapman->addGroupMember($ldapGroup, $user['user_name']) ) {
                    echo "\n";
                } else {
                    echo "failed.\n";
                }
            }
        } else {
            if($ldapman->isMemberOf($user['user_name'], $ldapGroup)){
                echo "\tRemoving ".$user['user_name']." from ".$ldapGroup."... ";
                if($ldapman->removeGroupMember($ldapGroup, $user['user_name'])){
                    echo "\n";
                } else {
                    echo "failed.\n";
                }
            }
        }
    }
    foreach($selectedUser->getGroupIds() as $groupId){
        $selectedGroup->load($groupId);
        if($selectedGroup->getNetid() != null) {
            $ldapGroup = LDAPMAN_PI_PREFIX . $selectedGroup->getNetid();
            if(!$ldapman->isMemberOf($user['user_name'], $ldapGroup)) {
                echo "\tAdding " . $user['user_name'] . " to " . $ldapGroup . "... ";
                if ( $ldapman->addGroupMember($ldapGroup, $user['user_name']) ) {
                    echo "\n";
                } else {
                    echo "failed.\n";
                }
            }
        }
        // TODO we need a way to remove users if they are no longer in the PI group. Not urgent
    }
}
