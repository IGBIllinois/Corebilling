<?php
ob_start();

$json_string = json_encode(array('version'=>'1.0','os'=>'Unknown'));
$key = "";
$username = "";
//If CoreBillingService >= 2.0
if (isset($_POST['json']) && !isset($_POST['username']) && !isset($_POST['key'])) {
	$json_string = $_POST['json']; 
	$json = json_decode($json_string);
	$key = $json->{'key'};
	$username = $json->{'username'};
	error_log($json_string);	
}

//If CoreBillingService >=1.0 < 2.0
elseif (isset($_POST['username']) && $_POST['username']!="" && isset($_POST['key'])) {
	$key = $_POST['key'];
	$username = $_POST['username'];
}
else {
	exit();
}

require_once('includes/main.inc.php');
$ipaddress = $_SERVER['REMOTE_ADDR'];
$deviceInfo = new Device($db);
$deviceInfo->load(0, $key);
if ($deviceInfo->getId() > 0) {
	$userId = User::exists($db,$username);
	//check if user_name exists
	$result = false;
	if ($userId) {
		//Start tracking session
		$result = Session::trackSession($db,$deviceInfo->getId(), $userId,$ipaddress,$json_string);
	} 
	else {
		//User was not found in website database so check for user exceptions
		if (in_array(strtolower($username), settings::get_users_exceptions())) {
			$deviceInfo->updateLastTick('',$ipaddress,$json_string);
		}
		else {
			$deviceInfo->updateLastTick($username,$ipaddress,$json_string);
		}
	}
}


ob_clean();

?>	
