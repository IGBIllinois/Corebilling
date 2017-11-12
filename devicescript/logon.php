<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>

<?php

@define ('DB_USER','devicelogon');
@define ('DB_PASSWORD','update$');
@define ('DB_HOST','localhost');
@define ('DB_NAME','igb_instru');

@$dbc=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Could not connect to MySQL:'.mysql_error());
mysql_select_db(DB_NAME,$dbc) OR die ('could not select the database: '.mysql_error());

if(isset($_GET['computername']) AND isset($_GET['username'])) {
   if(isset($_GET['description'])) {
	$description=$_GET['description'];
   }
   else {
	$description="";
   }
   $computername=$_GET['computername'];
   $username=$_GET['username'];
   echo $username.$computername; 
   $computername=mysql_real_escape_string($computername);
   $username=mysql_real_escape_string($username);
   echo $username.$computername;
   $sql="SELECT id FROM users WHERE username='$username'";
   echo $sql;
   $uid_result=mysql_query($sql,$dbc);
   echo mysql_errno($dbc). ": " .mysql_error($dbc). "\n";
   if($uid_result) {
	echo "User ID found";
   }
   $uid=mysql_result($uid_result,0);
   echo "userid: $uid";
   $sql="SELECT ID FROM device WHERE devicename='$computername'";
   $did_result=mysql_query($sql);
   $did=mysql_result($did_result,0,'ID');
   echo "<br>DeviceID: $did";
   $sql="SELECT ID FROM session WHERE deviceid='$did' AND userid='$uid' AND status=1";
   $sessioncheck=mysql_query($sql);
   if(mysql_num_rows($sessioncheck)>0) {
	$sessionid=mysql_result($sessioncheck,0,"ID");
	$sql="UPDATE session SET description='$description' WHERE ID=$sessionid";
	mysql_query($sql);
   }
   else {
   	$sql="INSERT into session (userid, start, status, deviceid, description) values ('$uid',Now(),1,'$did','$description')";
   	mysql_query($sql);
   }
}
mysql_close();
?>

</body>
</html>

