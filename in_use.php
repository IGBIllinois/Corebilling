<?php

$queryDevicesUse = "SELECT d.fdname, d.location, u.username, d.loggeduser,u.first, u.last, TIMESTAMPDIFF(SECOND, lasttick, NOW()) AS lastseen , unauthorized FROM users u RIGHT JOIN device d ON u.ID=d.loggeduser";
$devicesUse = $sqlDataBase->query($queryDevicesUse);

?>
<script language="JavaScript" src="includes/qTip.js" type="text/JavaScript"></script>

<center><h4>Devices In Use</h4>
<br>
</center>
<table width="100%" cellpadding="0" cellspacing="1" class="billing">
<tr class="title">
<td>
	Device Name
</td>
<td>
	User Name
</td>	
<td>
	Location
</td>
<td>
	Status
</td>
</tr>

<?php
$i=0; 
foreach($devicesUse as $id => $deviceUseInfo)
{
		if($deviceUseInfo['lastseen']==null)
		{
			$lastSeen = "<b><font>---</font></b>";
		}
		elseif($deviceUseInfo['lastseen']>=4*60)
		{
			$lastSeen = "<b><font color=\"#ff9145\">Offline</font></b>";
		}
		else
		{
			$lastSeen = "<b><font color=\"#a5c519\">Online</font></b>";
		}

		if($deviceUseInfo['loggeduser']==0)
		{
			$loggedUser = "";
		}
		elseif($deviceUseInfo['loggeduser']==-1)
		{
			$loggedUser= "Unauthorized User (".$deviceUseInfo['unauthorized'].")";
		}
		else
		{
			$loggedUser = $deviceUseInfo['first']." ".$deviceUseInfo['last']." (".$deviceUseInfo['username'].")";
		}
		echo "<tr class=\"d".($i%2)."\"><td align=\"center\">".$deviceUseInfo['fdname']."</td><td align=\"center\">".$loggedUser."</td><td align=\"center\">".$deviceUseInfo['location']."</td><td align=\"center\">".$lastSeen."</td></tr>";
	$i++;
}

echo "</table>";	
echo "<br><font size=\"2\" color=\"#465153\">*The following table displays which devices are currently being used and which users are using them.</font><br><br><br><br>";
?>
