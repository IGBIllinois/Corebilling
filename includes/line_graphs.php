<?php 
if(isset($_SESSION['group'])){
if($_SESSION['group']==1){
include"./includes/mysql_connect.php";
echo "<h4><center>Device Statistics</center></h4>";

if(isset($_GET['graph'])) {
	$graph=$_GET['graph'];
}
else {
	$graph="";
}
if(isset($_POST['selectDevice'])) {
	$deviceID=$_POST['deviceID'];
	$_SESSION['deviceid']=$deviceID;
}
elseif(isset($_GET['deviceID'])) {
	$deviceID=$_GET['deviceID'];
	$_SESSION['deviceid']=$deviceID;
}
else {
	$deviceID=0;
}
if(isset($_GET['month'])) {
	$_SESSION['monthStats']=$_GET['month'];
}
if(isset($_GET['year'])) {
	$_SESSION['yearStats']=$_GET['year'];
}
?>


<br>
<table>
<tr>
<td>

<?php
echo "<form action=\"./administration.php?subm=7\" method=POST>";
echo "<select name=\"deviceID\">";
echo "<option selected value=\"0\">ALL</option>";

$query_devices=mysql_query("SELECT devicename,ID FROM device");

while($row=mysql_fetch_array($query_devices)) {
	echo "<option value=\"".$row["ID"]."\">".$row["devicename"]."</option>";

}
echo "</select>";
echo "<input class=\"grey\" type=\"submit\" name=\"selectDevice\" value=\"Select Device\">";
echo "</form>";
?>
</td></tr>
<tr><td>
<?php
if($deviceID==0) {
	echo "<a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=0&graph=users_usage\">Users/Usage(Hrs)</a> | <a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=0&graph=devices_usage\">Devices/Usage(Hrs)</a> | <a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=0&graph=devices_income\">Device Income</a> | <a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=0&graph=groups_usage\">Groups/Usage(Hrs)</a>";
}
else {	
	echo "<a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=".$deviceID."&graph=time_usage\">Time/Usage(Hrs)</a> | ";
	echo "<a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=".$deviceID."&graph=device_users\">Device/Users</a> | ";
	echo "<a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=".$deviceID."&graph=device_groups\">Device/Groups</a> | ";
	echo "<a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=".$deviceID."&graph=device_group_income\">Device/Group Income</a>";
	if(isset($_GET['deviceID'])) {
	$query_devicename=mysql_query("SELECT devicename FROM device WHERE ID=".$_GET['deviceID']);
        $devicename=mysql_result($query_devicename,0,"devicename");
	echo "<br><br><center><h4>".$devicename."</h4></center>";
	}
}
?>

</td></tr>
<tr><td>

<?php
if(isset($_GET['month'])) {
	$month=$_GET['month'];
	$year=date("Y");
	$passVariable="&month=".$month;
?>
	<center><h4><a class="calendar" href="./administration.php?subm=7&deviceID=<?php echo $deviceID; ?>&graph=<?php echo $_GET['graph']; ?>&month=<?php echo $month-1; ?>"> << </a><?php echo date("F Y",mktime(0,0,0,$month,1,date("Y"))); ?><a class="calendar" href="./administration.php?subm=7&deviceID=<?php echo $deviceID; ?>&graph=<?php echo $_GET['graph']; ?>&month=<?php echo $month+1; ?>"> >> </a></h4></center>
<?php
}
elseif(isset($_GET['year'])) {
	$month=date("n");
	$year=$_GET['year'];
	$passVariable="&year=".$year;
?>
 <center><h4><a class="calendar" href="./administration.php?subm=7&deviceID=<?php echo $deviceID; ?>&graph=<?php echo $_GET['graph']; ?>&year=<?php echo $year-1; ?>"> << </a><?php echo date("Y",mktime(0,0,0,1,1,$year)); ?><a class="calendar" href="./administration.php?subm=7&deviceID=<?php echo $deviceID; ?>&graph=<?php echo $_GET['graph']; ?>&year=<?php echo $year+1; ?>"> >> </a></h4></center>
<?php
}
else {
	$month=date("n");
	$year=date("Y");
}

if(isset($_GET['deviceID'])) {
	if($_GET['deviceID']==0) {
	   switch ($_GET['graph']) {
		case "users_usage":
			echo "<img src=\"./graphs/graph_users_usage.php\">";
			break;
		case "devices_usage":
			echo "<img src=\"./graphs/graph_devices_usage.php\">";
			break;
		case "devices_income":
			echo "<img src=\"./graphs/graph_devices_income.php\">";
			break;
		case "groups_usage":
			echo "<img src=\"./graphs/graph_groups_usage.php\">";
			break;
		default:
			echo "<img src=\"./graphs/graph_users_usage.php\">";
			break;
	   }
	
	}
	else {

	  switch ($_GET['graph']) {
		case "time_usage":	
			echo "<img src=\"./graphs/graph_usage_time.php\">";
			break;
                case "device_users":
                        echo "<img src=\"./graphs/graph_device_users.php?".time()."\">";
                        break;
		case "device_groups":
                        echo "<img src=\"./graphs/graph_device_groups.php?".time()."\">";
                        break;
		case "device_group_income":
                        echo "<img src=\"./graphs/graph_device_income.php?".time()."\">";
                        break;
		default:
			echo "<img src=\"./graphs/graph_usage_time.php\">";
			break;
	  }		


	}
}


?>
</td></tr>
<tr><td>
<?php
echo "<center><table><tr><td><a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=".$deviceID."&graph=".$graph."&month=".$month."\">Month</a></td><td> | </td><td> <a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=".$deviceID."&graph=".$graph."&year=".$year."\">Year</a></td><td> | </td><td><a class=\"statistics\" href=\"./administration.php?subm=7&deviceID=".$deviceID."&graph=".$graph."&all=1\">All</a></td></tr></table></center><br><br>";
?>
</td></tr>
</table>
<?php

}
else {
	include "./denied.php";
}
}
else {
	include "./denied.php";
}
?>
