<?php
if(isset($_SESSION['usertype'])){
	if($_SESSION['usertype']==1){

		$device = new Device($sqlDataBase);
	
		if (isset($_POST['Select'])) {
                        $option=$_POST['option'];
                        if ($option!='new')
                        {
				$device->LoadDevice($option);	
                        }
                }

		if(isset($_POST['ModifyDevice']))
		{
			$device->LoadDevice($_POST['deviceID']);
			$device->SetDN($_POST['dnsName']);
			$device->SetName($_POST['deviceName']);
			$device->SetLocation($_POST['location']);
			$device->SetDescription($_POST['description']);
			$device->SetStatus($_POST['status']);
			$device->SetRateType($_POST['rateType']);
			$device->UpdateDevice();
			$ratesArr = $_POST['ratesBox'];
			foreach($ratesArr as $key => $value)
			{
				$queryUpdateRate = "UPDATE devicerate SET rate=".($_POST[$value]/60)." WHERE ID=".$value;
				$sqlDataBase->nonSelectQuery($queryUpdateRate);
			}
			
		}
		
		if(isset($_POST['newRate']))
		{
			$queryAddRate="INSERT INTO rates (ratename)VALUES(\"".$_POST['newRate']."\")";
			$newRateID = $sqlDataBase->insertQuery($queryAddRate);
			$queryDevices = "SELECT ID FROM device";
			$devicesIDs = $sqlDataBase->query($queryDevices);
			foreach($devicesIDs as $id => $devicesID)
			{
				$queryAddRateToDevice = "INSERT INTO devicerate (rate,deviceid,rateid)VALUES(0,".$devicesID["ID"].",".$newRateID.")";
				$sqlDataBase->insertQuery($queryAddRateToDevice);
			}
						
		}

		if(isset($_POST['CreateNewDevice']))
		{
			$device->CreateDevice($_POST['dnsName'],$_POST['deviceName'],$_POST['location'],$_POST['description'],$_POST['status'],$_POST['rateType']);
			
			$ratesArr = $_POST['ratesBox'];
			foreach($ratesArr as $key => $value)
			{
				$queryAddRates="INSERT INTO devicerate (rate,deviceid,rateid)VALUES(".($_POST[$value] / 60).",".$device->GetID().",".$value.")";
				$sqlDataBase->insertQuery($queryAddRates);
			}
		}
		
		?>

<center>
<h4>Devices Configuration</h4>
</center>

<table cellspacing="20">
	<tr>
		<td>
		<h4>Select Device:</h4>
		<form action="./administration.php?subm=11" method=POST><select name="option">
			<option selected value='new'>New</option>
			<?php
			$queryDevices = "SELECT devicename,ID,fdname FROM device ORDER BY devicename";
			$devices = $sqlDataBase->query($queryDevices);
			foreach($devices as $id =>$deviceInfo)
			{
				echo "<option value=".$deviceInfo["ID"];
				if($device->GetID()==$deviceInfo["ID"])
				{	
					echo " selected";	
				}
				echo ">".$deviceInfo["fdname"]."</option>";
			}
			echo "</select>";

			?>
			<input name="Select" type="submit" class="grey" id="Select" Value="Select" /></form>
		</form>
		<font size="2" color="#465153">Select a device from the list above in
		order to view/modify information. Select New to create a new device.</font>
		<br>
		<br>
		<h4>Add Rate</h4>
		<form action="./administration.php?subm=11" method=POST>
		<input type="text" name="newRate"><input type="submit" class="grey" value="Add"><br>
		<font size="2" color="#465153">Enter a name for the new rate in the box above</font>
		</form>
		</td>
		<td>

		<div class="roundcont">
		<div class="roundtop"><img src="./imgs/tl.gif" alt="" width="15"
			height="15" class="corner" style="display: none" /></div>

		<div id="uform">


		<table cellspacing="20">
			<tr>
				<td>
				<h5>Device ID (<?php echo $device->GetID(); ?>):</h5>
				</td>
				<td>
				<form action="./administration.php?subm=11" method=POST>
				<input name="deviceID" type="hidden" value="<?php echo $device->GetID(); ?>">
				<input name="dnsName" type="text" size="18" maxlength="18" value=<?php echo $device->GetDN(); ?>><br>
				<font size=1 color=white>(do not use spaces or special characters)</font>
				</td>
				</tr>
			<tr>
				<td>
				<h5>Auth. Token:</h5>
				</td>
				<td>
				<font size=2>
				<?php
				echo $device->GetDeviceToken();
				?>
				</font>
				</td>
			</tr>
				<tr>
					<td>
					<h5>Device Name:</h5>
					</td>
					<td><input type="text" name="deviceName" size="25"
						value="<?php echo $device->GetName(); ?>"></td>
				</tr>
				<tr>
					<td>
					<h5>Status</h5>
					<td>
					<select name="status">
					<?php
						$queryStatusOptions = "SELECT ID,statusname FROM status WHERE type=1";
						$statusOptions = $sqlDataBase->query($queryStatusOptions);
						foreach($statusOptions as $id => $statusOption)
						{
							echo "<option value=".$statusOption["ID"];
							if($device->GetStatus()==$statusOption["ID"])
							{		
								echo " SELECTED";
							}
							echo ">".$statusOption["statusname"]."</option>";
						}

					?>
					</select>
					</td>
				</tr>
				<tr>
					<td>
					<h5>Location:</h5>
					</td>
					<td><input type="text" name="location" size="25" value="<?php echo $device->GetLocation(); ?>"></td>
				</tr>
				<tr>
					<td>
					Rate Type:
					</td>
					<td>
					<select name="rateType">
					<?php
						$queryRateTypes = "SELECT ID, ratetype FROM ratetypes";
						$rateTypes = $sqlDataBase->query($queryRateTypes);
						foreach($rateTypes as $id => $ratetype)
						{
							echo "<option value=".$ratetype['ID'];
							if($device->GetRateType()==$ratetype['ID'])
							{
								echo " SELECTED";
							}
							echo ">".$ratetype['ratetype']."</option>";
						}
					?>
					</select>
					</td>
				</tr>
					<?php
					if($device->GetID() > 0)
					{
						$queryRates = "SELECT dr.rate,r.ratename, dr.ID FROM devicerate dr, rates r WHERE dr.deviceid=".$device->GetID()." AND r.ID = dr.rateid";
						$rates = $sqlDataBase->query($queryRates);
						if($rates)
						{
						foreach($rates as $id => $rate)
						{
							echo "<tr><td><input type=\"checkbox\" name=\"ratesBox[]\" value=\"".$rate["ID"]."\" style=\"display:none;\" CHECKED><h5>".$rate["ratename"]."</h5></td><td><input type=\"text\" value=".round($rate["rate"]*60,2)." name=\"".$rate["ID"]."\" size=\"5\" maxlength=\"5\"></td></tr>";
						}
						}		
					}
					else
					{
						$queryRates = "SELECT ratename, ID FROM rates";
                                                $rates = $sqlDataBase->query($queryRates);
                                                if($rates)
                                                {
                                                	foreach($rates as $id => $rate)
                                                	{
                                                	       	 echo "<tr><td><input type=\"checkbox\" name=\"ratesBox[]\" value=\"".$rate["ID"]."\" style=\"display:none;\" CHECKED><h5>".$rate["ratename"]."</h5></td><td><input type=\"text\" value=\"0\" name=\"".$rate["ID"]."\" size=\"5\" maxlength=\"5\"></td></tr>";
                                                	}
                                                }     	
					}
					?>
				<tr>
					<td>
					<h5>Description:</h5>
					</td>
					<td><textarea name="description" cols="25" rows="5"><?php echo $device->GetDescription(); ?></textarea>
					</td>
					</div>
				<tr>

		
		</table>
		<center><?php
		if($device->GetID() > 0)
		{
			echo '<input name="ModifyDevice" type="submit" class="grey" id="Modify" value="Modify">';
		}
		else {
			echo '<input name="CreateNewDevice" type="submit" class="grey" id="Submit" value="Create" >';
		}
		echo '<input name="Reset" type="submit" class="grey" id="reset" value="reset" >';
		?></center></form>

		<div class="roundbottom"><img src="./imgs/bl.gif" alt="" width="15"
			height="15" class="corner" style="display: none" /></div>
		</div>
		
		</td>
	</tr>
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
