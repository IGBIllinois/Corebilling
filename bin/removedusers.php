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


//Sets up ldap connection
if(LDAPMAN_API_ENABLED){
	$ldapman = new LdapManager(LDAPMAN_API_URL, LDAPMAN_API_USERNAME, LDAPMAN_API_PASSWORD);
} else {
	$ldapman = new LdapManager(LDAPMAN_API_URL);
}

$allUsers = User::getAllActiveUsers($db);
$selectedUser = new User($db);
foreach($allUsers as $user){
	if($ldapman->getUser($user['user_name']) == null){
		echo $user['user_name']."\n";
        $selectedUser->load($user['id']);
        $selectedUser->setStatusId(User::DISABLED);
        $selectedUser->update();
	}
}
