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
		if(isset($_POST['track_session']))
		{	
			$queryUserid = "SELECT ID FROM users WHERE username=\"".$_POST['username']."\"";
			$useridResult = $sqlDataBase->query($queryUserid);

			if($useridResult)
			{
				$userid = $useridResult[0]['ID'];	
			}
			else
			{
				$userid = 0;
				echo "No registered user found for ".$_POST['username']."\n";
			}

			$queryDeviceid = "SELECT ID from device WHERE devicename=\"".$_POST['devicename']."\"";
			$deviceidResult = $sqlDataBase->query($queryDeviceid);
		
			if($deviceidResult)
			{
				$deviceid = $deviceidResult[0]['ID'];
			}
			else
			{
				$deviceid = 0;
				echo "Computer name ".$_POST['devicename']." is not registered."\n";
			}
		}
	}
	if(isset($_POST['get_news']))
	{
        	$queryArticles = "SELECT title,text,user,time FROM articles";
        	$articles = $sqlDataBase->query($queryArticles);
        	foreach($articles as $id=>$article)
        	{
        	        echo "\n".$article['title']."\n".$article['text'];
        	}
	}
	if(isset($_POST['set_description']))
	{
		
	}
}
else
{
	echo "Incorrect password used";
}

?>
