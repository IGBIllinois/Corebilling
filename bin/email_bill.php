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
$output_command = "email.php Emails users their monthly bill\n";
$output_command .= "Usage: php email.php \n";
$output_command .= "	--year			Year (YYYY) (Default: Current Year)\n";
$output_command .= "	--month			Month (MM) (Default: Previous Month)\n";
$output_command .= "	-h, --help              Display help menu\n";

//Parameters
$shortopts = "h";

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

$year = date('Y',strtotime(date('Y-m')." -1 month"));
$month = date('m',strtotime(date('Y-m')." -1 month"));
$options = getopt($shortopts,$longopts);

if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}
if (isset($options['year']) && isset($options['month'])) {
	$year = $options['year'];
	$month = $options['month'];
}
elseif ((!isset($options['year']) && isset($options['month'])) ||
	(isset($options['year']) && !isset($options['month']))) {
	echo "Must specify year and month together\n";
	echo $output_command;
	exit;
}

$ldap = new \IGBIllinois\ldap(settings::get_ldap_host(),
                        settings::get_ldap_base_dn(),
                        settings::get_ldap_port(),
                        settings::get_ldap_ssl(),
                        settings::get_ldap_tls());

try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
        die("Error initializing PDO: " . $e->getMessage());
}

$log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());

$user_list = User::getSupervisors($db,User::ACTIVE);

foreach ($user_list as $user) {
	$user_object = new user($db,$ldap);
	$user_object->,$user['user_id']);
	//$user_object->email_bill(__ADMIN_EMAIL__,$year,$month);
}











?>


