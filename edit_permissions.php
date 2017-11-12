<?php

$queryDevicesUse = "SELECT d.fdname, d.location, u.username, d.loggeduser,u.first, u.last, TIMESTAMPDIFF(SECOND, lasttick, NOW()) AS lastseen , unauthorized FROM users u RIGHT JOIN device d ON u.ID=d.loggeduser";
$devicesUse = $sqlDataBase->query($queryDevicesUse);

?>
<script language="JavaScript" src="includes/qTip.js" type="text/JavaScript"></script>

<?php
if(isset($_POST['selected_perm_group']))
{
	$selectedPermissionGroup = $_POST['selected_perm_group'];
	$querySelectedGroupInfo = "SELECT name,description FROM perm_group WHERE ID=".$selectedPermissionGroup;
	$selectedGroupInfo = $sqlDataBase->query($querySelectedGroupInfo);
	$groupName = $selectedGroupInfo[0]['name'];
	$groupDescription = $selectedGroupInfo[0]['description'];
}
else
{
	$selectedPermissionGroup = 0;
	$groupName = "";
        $groupDescription = "";
}

if(isset($_POST['createPermGroup']) && $selectedPermissionGroup==0)
{
		
	if($_POST['deviceIds'])
	{
		$groupName = mysql_real_escape_string($_POST['groupname']);
		$groupDescription = mysql_real_escape_string( $_POST['groupdescription']);
		
		$queryInsertGroup = "INSERT INTO perm_group (name,description)VALUES(\"".$groupName."\",\"".$groupDescription."\")";
		echo $queryInsertGroup;
		$selectedPermissionGroup = $sqlDataBase->insertQuery($queryInsertGroup);

		$deviceIds = $_POST['deviceIds'];
		foreach($deviceIds as $deviceId)
		{
			$selectedPermission = $_POST[$deviceId];
			$queryInsertDevicePerm = "INSERT INTO device_perm (deviceid,permgroupid,permissionid)VALUES(".$deviceId.",".$selectedPermissionGroup.",".$selectedPermission.")";
			echo $queryInsertDevicePerm;
			$sqlDataBase->insertQuery($queryInsertDevicePerm);
			
		}
	}
}

if(isset($_POST['editPermGroup']) && $selectedPermissionGroup)
{

        if($_POST['deviceIds'])
        {
                $groupName = mysql_real_escape_string($_POST['groupname']);
                $groupDescription = mysql_real_escape_string( $_POST['groupdescription']);

                $queryUpdateGroup = "UPDATE perm_group SET name=\"".$groupName."\", description=\"".$groupDescription."\" WHERE ID=".$selectedPermissionGroup;
                $sqlDataBase->nonSelectQuery($queryUpdateGroup);

                $deviceIds = $_POST['deviceIds'];
                foreach($deviceIds as $deviceId)
                {
                        $selectedPermission = $_POST[$deviceId];
                        $queryUpdateDevicePerm = "UPDATE device_perm SET permissionid=".$selectedPermission." WHERE deviceid=".$deviceId." AND permgroupid=".$selectedPermissionGroup;
                        $sqlDataBase->nonSelectQuery($queryUpdateDevicePerm);

                }
        }
}


?>

<center><h4>Edit Permissions</h4></center>

<form action="./administration.php?subm=15" method="POST">
<table cellspacing="20">
<tr>
	<td>
	<h4>Select Group:</h4>
	<SELECT name="selected_perm_group">
	<option value="0">New</option>
	<?php
	$queryPermissionGroups = "SELECT ID,name FROM perm_group";
	$permissionGroups = $sqlDataBase->query($queryPermissionGroups);
	foreach($permissionGroups as $id=>$permissionGroup)
	{
		echo "<option value=".$permissionGroup['ID'];
		if($selectedPermissionGroup == $permissionGroup['ID'])
		{
			echo " SELECTED";
		}
		echo ">".$permissionGroup['name']."</option>";
	}
	?>
	</SELECT>
	<input type="submit" name="selectGroup" value="Select" class="grey"><br>
	<font size="2" color="#465153">
	Select permission group to edit.
	</font>
	</td>
	<td>
		<div class="roundcont">
        	<div class="roundtop"> <img src="./imgs/tl.gif" alt="" width="15" height="15" class="corner" style="display: none"> </div>
        	<div id="uform">
		<?php
		if($selectedPermissionGroup)
		{
			echo "<center>Edit Permissions Group</center>";
		}
		else
		{
			echo "<center>New Permissions Group</center>";
		}
		?>
		<table cellspacing="20">
       		<tr>
        	  	<td><h5>Group Name:</h5></td>
        	  	<td>
        	  	<input name="groupname" type="text" size="12"  value="<?php echo $groupName; ?>">
		  	</td>
		<tr>
		</tr>
        	  	<td><h5>Group Description:</h5></td>
        	  	<td>
        	  	<TEXTAREA name="groupdescription" type="text" size="12"><?php echo $groupDescription; ?></TEXTAREA>
        	  	</td>
        	</tr>
		<tr>
		<td colspan=2>
		<table width="100%">
		<tr>
			<td><center><h5>Active</h5></center></td>
			<td><center><h5>Hidden</h5></center></td>
			<td><center><h5>Device Name</h5></center></td>
		</tr>
		<?php

		if($selectedPermissionGroup)
		{
			$queryDevicePermissions = "SELECT dp.deviceid as ID,d.fdname, dp.permissionid FROM device_perm dp, device d WHERE d.ID = dp.deviceid AND dp.permgroupid=".$selectedPermissionGroup;
			$devicePermissions = $sqlDataBase->query($queryDevicePermissions);
		}
		else
		{
			$queryAllDevices = "SELECT ID,fdname,5 as permissionid FROM device";
			$devicePermissions = $sqlDataBase->query($queryAllDevices);
		}	
		
		foreach($devicePermissions as $id=>$devicePermission)
		{
			echo "<tr>";
			echo "<td><center><input type=\"radio\" name=".$devicePermission['ID']." value=5 ".(($devicePermission['permissionid']==5)?"checked":"unchecked")."></center></td>";
			echo "<td><center><input type=\"radio\" name=".$devicePermission['ID']." value=6 ".(($devicePermission['permissionid']==6)?"checked":"unchecked")."></center></td>";
			echo "<td>".$devicePermission['fdname']."<div id=\"hiddenArea\"><input type=\"checkbox\" name=\"deviceIds[]\" value=".$devicePermission['ID']." checked></div></td>";
			echo "</tr>";
		}
		
		?>
		</table>	
		</td>
		</tr>
		</table>
		<?php
		if($selectedPermissionGroup)
                {
                        echo "<center><input type=\"submit\" name=\"editPermGroup\" value=\"Edit Group\" class=\"grey\"></center>";
                }
                else
                {
                        echo "<center><input type=\"submit\" name=\"createPermGroup\" value=\"Create Group\" class=\"grey\"></center>";
                }	
		?>
        	<div class="roundbottom"> <img src="./imgs/bl.gif" alt="" width="15" height="15" class="corner" style="display: none"> </div>
      		</div>
		</div>
	</td>
</tr>
</table>
</form>
