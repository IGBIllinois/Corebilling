<?php
include('config.php');
include('classes/AutoLoadClasses.php');
include('includes/mysql_connect.php');

if(isset($_POST['tracker_password']))
{	
	if($_POST['tracker_password'] == TRACKER_PASSWORD)
	{
		$deviceid = 0;
		$userid = 0;

		$queryUserid = "SELECT ID FROM users WHERE username=\"".$_POST['username']."\"";
		echo "User Detected:".$_POST['username'];
		$useridResult = $sqlDataBase->query($queryUserid);

		if($useridResult)
		{
			$userid = $useridResult[0]['ID'];	
		}
		else
		{
			$userid = 0;
			echo "No registered user found for ".$_POST['username'];
		}

		$queryDeviceid = "SELECT ID from device WHERE devicename=\"".$_POST['devicename']."\"";
		$deviceidResult = $sqlDataBase->query($queryDeviceid);
		echo "Device detected:".$_POST['devicename'];
		
		if($deviceidResult)
		{
			$deviceid = $deviceidResult[0]['ID'];
		}
		else
		{
			$deviceid = 0;
			echo "Computer name ".$_POST['devicename']." is not registered.";
		}
			
		$sessionToTrack = new Session($sqlDataBase);
		$sessionID = $sessionToTrack->TrackSession($deviceid,$userid);	
		
		if(isset($_POST['description']) && $sessionID > 0)
		{
			$sessionToTrack->LoadSession($sessionID);
			$sessionToTrack->SetDescription($_POST['description']);
			$sessionToTrack->UpdateSession();
		}
	}
}
else
{
	echo "Incorrect password used";
}

?>
