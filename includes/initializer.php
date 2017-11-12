<?php
// setting up the web root and server root for
include('config.php');
include('classes/AutoLoadClasses.php');
include('includes/mysql_connect.php');

$userTypeAdmin = 1;
$userTypeSuper = 2;
$userTypeUser = 3;

$thisFile = str_replace('\\', '/', __FILE__);
$docRoot = $_SERVER['DOCUMENT_ROOT'];
$webRoot  = str_replace(array($docRoot, 'library/config.php'), '', $thisFile);
$srvRoot  = str_replace('library/config.php', '', $thisFile);
define('WEB_ROOT', $webRoot);
define('SRV_ROOT', $srvRoot);

// Run authentication on user
include('includes/authenticate.php');
?>
