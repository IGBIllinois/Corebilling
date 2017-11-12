CTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
   $computername=$_GET['computername'];
   $username=$_GET['username'];

   $computername=mysql_real_escape_string($computername);
   $username=mysql_real_escape_string($username);
   
   $sql="SELECT u.id FROM igb_instru.users u WHERE u.username='$username'";
   $uid_result=mysql_query($sql);
   $uid=mysql_result($uid_result,0,"id");
   echo "userid: $uid";
   $sql="SELECT d.ID FROM device d WHERE d.devicename='$computername'";
   $did_result=mysql_query($sql);
   $did=mysql_result($did_result,0,"ID");
   echo "<br>DeviceID: $did";

   $sql="SELECT start FROM session WHERE userid='$uid' AND deviceid='$did' AND status=1";
   $start_results=mysql_query($sql);
   $start=mysql_result($start_results,0,"start");
   
   
   $sql="UPDATE session SET stop=NOW(), status=0, elapsed=round(TIME_TO_SEC(TIMEDIFF(NOW(),start))/60) WHERE deviceid='$did' AND status=1 AND userid='$uid'";
   mysql_query($sql,$dbc);
   echo mysql_errno($dbc). ": " .mysql_error($dbc). "\n";   
}
?>
</body>
</html>

