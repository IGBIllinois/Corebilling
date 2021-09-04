<?php
require_once 'includes/header.inc.php';
?>
<h3>Devices In Use</h3>
<table class="table table-striped table-bordered">
	<tr class="title">
		<th>Device Name</th>
		<th>IP Address</th>
		<th>Hostname</th>
		<th>User Name</th>	
		<th>Location</th>
		<th>Status</th>
	</tr>
	
	<?php
	$devicesInUse = Device::getAllDevicesStatus($db);
	
	foreach($devicesInUse as $id=>$deviceUseInfo) {
		if($deviceUseInfo['lastseen']==null) {
			$lastSeen = "<b><font>---</font></b>";
		} elseif($deviceUseInfo['lastseen']>=4*60) {
			$lastSeen = "<span class=\"label label-danger\" style='font-size:18px; padding:.2em .6em'><span class=\"glyphicon-chevron-down glyphicon\"></span></span>";
		} else {
			$lastSeen = "<span class=\"label label-success\" style='font-size:18px; padding:.2em .6em'><span class=\"glyphicon-chevron-up glyphicon\"></span></span>";
		}
	
		if($deviceUseInfo['loggeduser']==0) {
			$loggedUser = "";
		} elseif($deviceUseInfo['loggeduser']==-1) {
			$loggedUser= "Unauthorized User (".$deviceUseInfo['unauthorized'].")";
		} else {
			$loggedUser = $deviceUseInfo['first']." ".$deviceUseInfo['last']." (".$deviceUseInfo['user_name'].")";
		}
		$ipaddress = "";
		$hostname = "";
		if ($deviceUseInfo['ipaddress'] != "000.000.000.000") {
			$ipaddress = $deviceUseInfo['ipaddress'];
			$hostname = gethostbyaddr($ipaddress);
		}
		echo "<tr><td align=\"center\">".$deviceUseInfo['full_device_name']."</td>";
		echo "<td align=\"center\"> " . $ipaddress . "</td>";
		echo "<td align=\"center\"> " . $hostname . "</td>";
		echo "<td align=\"center\">".$loggedUser."</td>";
		echo "<td align=\"center\">".$deviceUseInfo['location']."</td>";
		echo "<td align=\"center\">".$lastSeen."</td></tr>";
	}
	?>
</table>
<?php
	require_once 'includes/footer.inc.php';
