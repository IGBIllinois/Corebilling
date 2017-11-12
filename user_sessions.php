<?php
if(isset($_SESSION['userid'])) 
{
include "./includes/mysql_connect.php";
if (isset($_POST['Select']) || isset($_POST['toExcel']))
{
        $selectedUserId= $_POST['user'];
        $selectedDeviceId = $_POST['device'];
        $selectedFromMonth= $_POST['fromMonth'];
        $selectedToMonth= $_POST['toMonth'];
        $selectedFromYear= $_POST['fromYear'];
        $selectedToYear= $_POST['toYear'];
        $selectedMinutesSign = $_POST['minutesSign'];
	$selectedMinutes = $_POST['minutes'];

        $where='';
        if ($selectedUserId == 0) {
                $selectedUserIdSearch="LIKE '%'";
        }
        else {
                $selectedUserIdSearch= "=".$_POST['user'];
        }

        if($selectedDeviceId == 0) {
                $selectedDeviceIdSearch="LIKE '%'";
        }
        else {
                $selectedDeviceIdSearch = "=".$_POST['device'];
        }

        $querySelected = "SELECT u.username, d.devicename,d.fdname, s.userid, s.deviceid, s.start, s.stop, s.elapsed, s.rate, s.verified FROM session s, users u, device d WHERE s.userid $selectedUserIdSearch AND s.deviceid $selectedDeviceIdSearch AND s.start>= '$selectedFromYear-$selectedFromMonth-1' AND s.start <= '$selectedToYear-".($selectedToMonth+1)."-1' AND s.elapsed $selectedMinutesSign $selectedMinutes AND u.ID=s.userid AND d.ID=s.deviceid";
        $selectedSessionData = $sqlDataBase->query($querySelected);

	if(isset($_POST['toExcel']))
	{
		$excel= new ExcelWriter(RECORDS_PATH."user_sessions_".$selectedFromMonth."-".$selectedFromYear."_".$selectedToMonth."-".$selectedToYear."_".$selectedUserId."_".$selectedDeviceId.".xls");
		if($excel==false)
	        	echo $excel->error;

		$myArr=array("<b>User</b>","<b>Device</b>","<b>Logged On</b>","<b>Logged Off</b>","<b>Hours</b>","<b>Cost</b>");
		$excel->writeLine($myArr);

		foreach($selectedSessionData as $id=>$selectedSession)
		{
		        $myArray=array("".$selectedSession['username']."","".$selectedSession['fdname']."","".$selectedSession['start']."","".$selectedSession['stop']."","".round(($selectedSession['elapsed']/60),2)."","".(($selectedSession['verified'])?"$".round($selectedSession['elapsed']*$selectedSession['rate'],2):"Not Verified")."");
		        $excel->writeLine($myArray);

		}
	        $excel->close();
	        header("Location: ./records/user_sessions_".$selectedFromMonth."-".$selectedFromYear."_".$selectedToMonth."-".$selectedToYear."_".$selectedUserId."_".$selectedDeviceId.".xls");	
	}
}
else
{
	$selectedUserId= 0;
        $selectedDeviceId = 0;
        $selectedFromMonth= 0;
        $selectedToMonth= 0;
        $selectedFromYear= 0;
        $selectedToYear= 0;
        $selectedMinutesSign = 0;
	$selectedMinutes = 0;
}

?>

<center><h4>User/Device Logs</h4></center>

<table cellspacing="20">
<tr>
<td align="left">
<div id="secont">
	<table cellpadding="0">
	 <tr>
		<td>
			<div id="semenu">User:</div>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			$queryUsers = "SELECT ID, username FROM users ORDER BY username";
			$users = $sqlDataBase->query($queryUsers);
			?>
			<form action="./administration.php?subm=4" method=POST>
			<select name="user" style="width:150px">;
			<option selected value=0>All</option>;
			<?php
			foreach($users as $key=>$user)
			{		
				echo "<option value=".$user['ID'];
				if($selectedUserId==$user['ID'])
				{
					echo " SELECTED";
				}

				echo ">".$user['username']."</option>";
			}
			echo "</select>";
			?>
		</td>
	</tr>
	<tr>
		<td>
			<div id="semenu">Device:</div>
		</td>
	<tr>
	</tr>
		<td>
			<?php
			$queryDevices = "SELECT ID, fdname FROM device ORDER BY devicename";
			$devices = $sqlDataBase->query($queryDevices);
			?>
			
			<select name="device" style="width:150px">;
			<option selected value=0>All</option>;
			<?php
			foreach($devices as $key=>$device)
			{
				echo "<option value=".$device['ID'];
				if($selectedDeviceId==$device['ID'])
				{
					echo " SELECTED";
				}
				echo ">".$device['fdname']."</option>";
			}
			echo "</select>";
			
			?> 
		</td>
	</tr>
        <tr>
                <td>
                        <div id="semenu">From:</div>
                </td>
        <tr>
        </tr>
                <td>

                        <select name="fromMonth">;
			<?php
			
                        echo "<option selected value='".date(n)."'>".date(M)."*</option>";
			$i=1;
			while($i<=12) {
				echo $i;
				echo "<option value='".date(n,mktime(0,0,0,$i,1,2007))."'";
				if(date(n,mktime(0,0,0,$i,1,2007))==$selectedFromMonth)
				{
					echo " SELECTED";
				}
				echo ">".date(M,mktime(0,0,0,$i,1,2007))."</option>";
				$i++;
			}

			?>
                        </select>
			<select name="fromYear">
			<?php
			$queryYears="SELECT DISTINCT YEAR(start) as year_start FROM session";
			$years = $sqlDataBase->query($queryYears);

                        echo "<option selected value='".date(Y)."'>".date(Y)."*</option>";
			foreach($years as $key=>$year)
			{
				echo "<option value='".$year['year_start']."'";
				if($selectedFromYear==$year['year_start'])
				{
					echo " SELECTED";
				}
				echo ">".$year['year_start']."</option>";
			}
			?> 
                        </select>

                </td>
        </tr>
        <tr>
                <td>
                        <div id="semenu">To:</div>
                </td>
        <tr>
        </tr>
                <td>

                        <select name="toMonth">;
                        <?php

                        echo "<option selected value='".date(n)."'>".date(M)."*</option>";
                        $i=1;
                        while($i<=12) {
                                echo $i;
                                echo "<option value='".date(n,mktime(0,0,0,$i,1,2007))."'";
				if($selectedToMonth==date(n,mktime(0,0,0,$i,1,2007)))
				{
					echo " SELECTED";
				}
				echo ">".date("M",mktime(0,0,0,$i,1,2007))."</option>";
                                $i++;
                        }

                        ?>
                        </select>
                        <select name="toYear">
                        <?php
			$queryYears="SELECT DISTINCT YEAR(start) as year_start FROM session";
                        $years = $sqlDataBase->query($queryYears);

                        echo "<option selected value='".date(Y)."'>".date(Y)."*</option>";
                        foreach($years as $key=>$year)
                        {
                                echo "<option value='".$year['year_start']."'";
				if($selectedToYear==$year['year_start'])
				{
					echo " SELECTED";
				}
				echo ">".$year['year_start']."</option>";
                        }
                        ?>
                        </select>

                </td>
        </tr>
	<tr>
                <td>
                        <div id="semenu">Usage Minutes:</div>
                </td>
        <tr>
        </tr>
                <td>
                        <select name="minutesSign" style="width:40px">
                        <option selected value='>='>=></option>; 
                        <option value="<=" ><=</option>
                        </select>
			<input name="minutes" type="POST" value=0 size=4>

                </td>
        </tr>
	</div>
	</table>
	<input name="Select" type="submit" class="grey" id="Select" Value="Select" ><input name="toExcel" type="submit" class="grey" value="To Excel">
	</form>
	<br />
	<font size="2" color="#465153">To search the session logs, specifiy using the options above to get a more precise query. Select All to view all user's and/or machine's records.</font>
</td>
<td>
<center><h4>Event Viewer</h4></center>
	<table cellpadding="0" cellspacing="1" width="670" class="sortable">
	<tr class="title">
		<td width="130">
			User
		</td>
		<td width="131">
			Device
		</td>
		<td width="130">
			Logged On
		</td>
		<td width="130">
			Logged Off
		</td>
		<td width="79">
			Hours
		</td>
		<td width=70>
			Cost
		</td>
	</tr>
	</table>
	<?php
	if($browser='Firefox') {
	?>
	<div id="scroll_log_FF">
	<?php
	}
	else {
	?>
	<div id="scroll_log">
	<?php
	}
	?>
<table cellpadding="0" cellspacing="1" width="670" class="sortable">
<tr class="title">
                <td width="130">
		Sort
                </td>
                <td width="131">
		Sort
                </td>
                <td width="130">
		Sort
                </td>
                <td width="130">
		Sort
                </td>
                <td width="79">
		Sort
                </td>
                <td width=70>
		Sort
                </td>
</tr>
<?php
if(isset($selectedSessionData))
{
	$i=0;
	foreach($selectedSessionData as $key=>$sessionData)
	{
		echo "<tr class=\"d".($i%2)."\">
			<td width=\"130\" align=\"center\">
			".$sessionData['username']."
			</td>
			<td width=\"130\" align=\"center\">
			".$sessionData['fdname']."
			</td>
			<td width=\"130\" align=\"center\">
			".$sessionData['start']."
			</td>
			<td width=\"130\" align=\"center\">
			".$sessionData['stop']."
			</td>
			<td width=\"80\" align=\"center\">
			".round(($sessionData['elapsed'] / 60),2)."
			</td>
			<td width=\"70\" align=\"center\">
			".(($sessionData['rate'] && $sessionData['verified'])?"$".round($sessionData['elapsed']*$sessionData['rate'],2):"Not Verified")."
			</td>
		</tr>";
		$i++;
	}
}
?>	

</table>
</div>
</td>
</tr>
</table>

<?php
} else {
	include "./denied.php";
}
?>
