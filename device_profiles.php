<?php

if(isset($_SESSION['group'])){
include "./includes/mysql_connect.php";

?>
<script language="JavaScript" src="includes/qTip.js" type="text/JavaScript"></script>
<center><h4>Devices List</h4></center><br>
<TABLE width="100%" cellspacing="1" cellpadding="3" >
<tr class="title">
<td>
	ID#
</td>
<td>
	DeviceID
</td>
<td width=>
	Full Name
</td>
<td>
	Location
</td>
<td>
	Description
</td>
<td>
	$/Hour.
</td>
</tr>

<?php
	$result=mysql_query("SELECT ID,devicename,fdname,location,rate,description FROM device",$dbc);
	//echo mysql_errno($dbc). ": " .mysql_error($dbc). "\n";

	$i=0;
	$num=mysql_num_rows($result);
	while ($i<$num) { 
		$deviceid=mysql_result($result,$i,"ID");
		$devicename=mysql_result($result,$i,"devicename");
		$fdname=mysql_result($result,$i,"fdname");
		$location=mysql_result($result,$i,"location");
		$description=mysql_result($result,$i,"description");
		$rate=mysql_result($result,$i,"rate");
		$shortdescription=substr($description,0,30);
		if(($i%2) == 1) {
			echo "<tr class=\"d1\"><td align=\"center\">".$deviceid."</td><td align=\"center\">".$devicename."</td><td align=\"center\">".$fdname."</td><td align=\"center\">".$location."</td><td align=\"center\"><a href=\"#\" title=\" ".$description."\">".$shortdescription."...</a></td><td align=\"center\">".round(60*$rate,2)."</td></tr>";
		}
		if(($i%2) == 0) {
			echo "<tr class=\"d0\"><td align=\"center\">".$deviceid."</td><td align=\"center\">".$devicename."</td><td align=\"center\">".$fdname."</td><td align=\"center\">".$location."</td><td align=\"center\"><a href=\"#\" title=\" ".$description."\">".$shortdescription."...</a></td><td align=\"center\">".round(60*$rate,2)."</td></tr>";
		}
		$i++;
		
	} 
?>
</TABLE>
<br>

<?php
}
else {
	include "./denied.php";
}
?>