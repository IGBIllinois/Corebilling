<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('includes/initializer.php');
include('includes/browsercheck.php');

//Check if the proper get inputs are set
echo md5(uniqid(mt_rand(), true));
if(isset($_GET['deviceid']) && isset($_GET['username']) && isset($_GET['key']))
{
        $deviceId = mysql_real_escape_string($_GET['deviceid']);
        $username = mysql_real_escape_string($_GET['username']);
        $deviceKey = mysql_real_escape_string($_GET['key']);

	echo "User name ".$username;	
	echo "all gets found";
	$deviceInfo = new Device($sqlDataBase);
	$deviceInfo->LoadDevice($deviceId);
	//check if device token matches
	if($deviceKey == $deviceInfo->GetDeviceToken())
	{
		echo "Device Key matched";
		$sessionInfo = new Session($sqlDataBase);
		$userInfo = new User($sqlDataBase);
		$userId = $userInfo->Exists($username);
		//check if username exists
		if($userId)
		{
			//Start tracking session
			echo "updating session";
			$sessionInfo->TrackSession($deviceId,$userId);
			
		}
		else
		{
			echo "updating tick";
			$deviceInfo->UpdateLastTick();
		}
		
	}
	

}

include('includes/mysql_close.php');
?>	
