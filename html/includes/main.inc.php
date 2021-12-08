<?php
// setting up the web root and server root for
require_once(__DIR__ . '/../../conf/app.inc.php');
require_once(__DIR__ . '/../../conf/config.inc.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

set_include_path(get_include_path().":" . __DIR__ . "/../../libs");

function my_autoloader($class_name) {
        if(file_exists(__DIR__ . '/../../libs/' . $class_name . '.class.inc.php')) {
                require_once($class_name . '.class.inc.php');
        }
}
spl_autoload_register('my_autoloader');

try {
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
} 
catch (PDOException $e) {
	die("Error initializing Database: " . $e->getMessage());
}


date_default_timezone_set(settings::get_timezone());

//Sets up ldap connection
$ldap = new \IGBIllinois\ldap(settings::get_ldap_host(),
			settings::get_ldap_base_dn(),
			settings::get_ldap_port(),
			settings::get_ldap_ssl(),
			settings::get_ldap_tls());

$ldapman = null;
if(LDAPMAN_API_ENABLED){
	$ldapman = new LdapManager(LDAPMAN_API_URL, LDAPMAN_API_USERNAME, LDAPMAN_API_PASSWORD);
} 

//Authenticates to website database
$authenticate = new Authenticate($db,$ldap);

