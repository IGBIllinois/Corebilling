<?php
	$session=new Session($sqlDataBase);
	if(isset($_GET['session']))
	{
		$session->LoadSession($_GET['session']);
	}

	if(isset($_POST['createSession']))
	{
		$startTimeStamp = strtotime($_POST['startDate']." ".$_POST['starttime']);
		$start = Date("Y-m-d H:i:s",$startTimeStamp);
		$stop = Date("Y-m-d H:i:s",$startTimeStamp+($_POST['usage']*60*60));
		$session->CreateSession($_POST['userid'],$start,$_POST['stop'],$_POST['status'],$_POST['deviceid'],$_POST['description'],$_POST['cfop']);
		$session->SetRate($_POST['rate']/60);
		$session->UpdateSession();
		$session->ManualVerify();
	}

	if(isset($_POST['modifySession']))
	{
		$startTimeStamp = strtotime($_POST['startDate']." ".$_POST['starttime']);
                $start = Date("Y-m-d H:i:s",$startTimeStamp);
                $stop = Date("Y-m-d H:i:s",$startTimeStamp+($_POST['usage']*60*60));
		
		$session->SetUserID($_POST['userid']);
		$session->SetStart($start);
		$session->SetStop($stop);
		$session->SetUserID($_POST['userid']);
		$session->SetCfop($_POST['cfop']);
		$session->SetDeviceID($_POST['deviceid']);
		$session->SetRate($_POST['rate']/60);
		$session->UpdateSession();
		$session->ManualVerify();
	}
?>
<SCRIPT LANGUAGE="JavaScript" SRC="js/timepicker.js" type="text/Javascript"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="js/datepicker.js" type="text/Javascript"></SCRIPT>
<SCRIPT>
function roundNumber(num, dec) {
        var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
        return result;
}
function startCalc(){
  interval = setInterval("calc()",1);
}
function calc(){
  one = document.editForm.txtRate.value;
  two = document.editForm.txtUsage.value;
  document.editForm.txtTotal.value = roundNumber(((one * 1) * (two * 1)),2);
}
function stopCalc(){
  clearInterval(interval);
}
</SCRIPT>

<table cellpadding="0" cellspacing="1" width="800">
<tr>
	<td width="120">
	<font size="2" color="#465153">To edit a session's values please change the entries in the box to the right and hit submit. </font>
	</td>
	<td>
	<div class="roundcont">
	<div class="roundtop"> <img src="./imgs/tl.gif" alt="" width="15" height="15" class="corner" style="display: none" /> </div>
	<div id="uform">

	<center><b>Session Information</b></center><br>
	<table width="100%" cellpadding="0" cellspacing="20">
	<form name="editForm" method="post" action="./administration.php?subm=13&month=<?php echo $_GET['month']; ?>&session=<?php echo $session->GetID(); ?>&edit=true">
	<tr>
		<td>Date:</td>
		<td><input name="startDate" value="<?php echo Date("m/d/Y",strtotime($session->GetStart()));?>">
                <input type=button value="select" onclick="displayDatePicker('startDate');"></td>
	</tr>

	<tr>
		<td>Time:</td><td><input readonly='readyonly' name='starttime' id='timepicker1' 
					type='text' value='<?php echo Date("g:i a",strtotime($session->GetStart())); ?>' size=8 maxlength=8
                                        ONBLUR="validateDatePicker(this)"><IMG SRC="imgs/timepicker.gif" BORDER="0" ALT="Pick a Time!"
                                        ONCLICK="selectTime(this,document.getElementById('timepicker1'))"
                                        STYLE="cursor: hand"></td>
</tr>
<tr>
	<td>Name:</td>
	<td><select name="userid">
<?php
$queryUsers="SELECT ID,first,last FROM users ORDER BY first";
$usersArr = $sqlDataBase->query($queryUsers);
foreach($usersArr as $id=>$userToSelect)
{
	echo "<option value=".$userToSelect["ID"];
	if($userToSelect["ID"]==$session->GetUserID())
	{
		echo " SELECTED";
	}
	echo ">".$userToSelect["first"]." ".$userToSelect["last"]."</option>";
}
?>
</select>
</td></tr>
<tr><td>CFOP:</td><td><input type="text" name="cfop" value="<?php echo $session->GetCfop(); ?>" size="19" maxlength="24""> </td></tr>

<tr><td>Equipment:</td><td>
<select name="deviceid">
<?php
$queryDevices="SELECT ID, devicename FROM device ORDER BY devicename";
$devicesArr = $sqlDataBase->query($queryDevices);
foreach($devicesArr as $id=>$deviceToSelect)
{
	echo "<option value=".$deviceToSelect["ID"];
	if($deviceToSelect["ID"]==$session->GetDeviceID())
	{
		echo " SELECTED";
	}
	echo ">".$deviceToSelect["devicename"]."</option>";
}
?>
</select>
</td></tr>

<tr><td>Usage:</td><td><input type="text" name="usage" value="<?php echo round($session->GetElapsed() / 60,2); ?>" onFocus="startCalc();" onBlur="stopCalc();" size="3">Hours</td></tr>

<tr><td>Rate:</td><td><input type="text" name="rate" value="<?php echo round(60*$session->GetRate(),2); ?>" onFocus="startCalc();" onBlur="stopCalc();" size="2">($/Hr)</td></tr>

<tr><td>Total:</td><td><input type="text" readonly="readonly" name="total" value="<?php echo round(round(60*$session->GetRate(),2)*round($session->GetElapsed()/60,2),2); ?>" size="3">$</td></tr>

<tr><td>Description:</td><td><textarea name="description" cols="20" rows="4"><?php echo $session->GetDescription(); ?></textarea></td></tr>
</table>
<br>
<input type="hidden" value="<?php echo $session->GetID(); ?>" name="sessionID">
<center>
<table>
<tr>
<td>
<?php
if($session->GetID() > 0)
{
	echo "<input class=\"grey\" type=\"submit\" name=\"modifySession\" value=\"Modify\">";
}
else
{
	echo "<input class=\"grey\" type=\"submit\" name=\"createSession\" vlaue=\"Create\">";
}
?>

</form>
</td>
<td>
<form name="verifyForm" method="post" action="./administration.php?subm=13">
<input class="grey" type="submit" name="returnToBilling" value="Back To Billing">
<input type="hidden" name="monthSelected" value="<?php echo Date("n Y",strtotime($session->GetStart()));?>">
</form>
</td>
</tr>
</table>
</center>
<div class="roundbottom">
         <img src="./imgs/bl.gif" alt=""
         width="15" height="15" class="corner"
         style="display: none" />
   </div>
</div>
</div>
</td>
</tr>
</table><br><br>

