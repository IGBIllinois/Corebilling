<?php
ob_start();
@session_start();
// setting up the web root and server root for
require_once(__DIR__ . '/../../conf/app.inc.php');
require_once(__DIR__ . '/../../conf/config.inc.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

set_include_path(get_include_path().":../libs");
function my_autoloader($class_name) {
        if(file_exists('../libs/' . $class_name . '.class.inc.php'))
        {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

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
if(settings::get_coreserver_enabled()){
	$coreserverman = new CoreServerManager();
}

//Authenticates to website database
$authenticate = new Authenticate($db, $authen);

