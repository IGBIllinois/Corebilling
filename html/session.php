<?php

//Check if the proper get inputs are set
if (isset($_POST['username']) && $_POST['username']!="" && isset($_POST['key'])) {
    require_once('includes/main.inc.php');
    $deviceInfo = new Device($db);
    $deviceInfo->load(0, $_POST['key']);
    //check if device token matches
	$ipaddress = $_SERVER['REMOTE_ADDR'];

    if ($deviceInfo->getId() > 0) {
        $userId = User::exists($db,$_POST['username']);

        //check if user_name exists
        if ($userId) {
            //Start tracking session
            Session::trackSession($db,$deviceInfo->getId(), $userId,$ipaddress);
        } else {
            //User was not found in website database so check for user exceptions
		if (in_array(strtolower($_POST['username']), array_map('strtolower', $USER_EXCEPTIONS_ARRAY))){
			$deviceInfo->updateLastTick();
		}
		else {
			$deviceInfo->updateLastTick($_POST['username']);
	        }
	            
        }

    }
}

?>	
