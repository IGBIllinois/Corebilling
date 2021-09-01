<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	require_once('includes/main.inc.php');
	
	require_once 'includes/authenticate.inc.php';
	header('content-type: application/json');
	
	if(isset($_REQUEST['uid'])){
		echo json_encode($ldapman->getUser($_REQUEST['uid']));
	}
