<script> 
function CheckAll()
{
count = document.verifyForm.elements.length;
    for (i=0; i < count; i++) 
	{
    if(document.verifyForm.elements[i].checked == 1)
    	{document.verifyForm.elements[i].checked = 0; }
    else {document.verifyForm.elements[i].checked = 1;}
	}
}
</script>
<?php
if(isset($_SESSION['userid'])) {
include "./includes/mysql_connect.php";

$rateUsage = 1;
$rateMonthly = 2;

if(isset($_POST['monthSelected']))
{
	list($month, $year) = split(" ",$_POST['monthSelected']);
}
else{
	$month=Date("n");
	$year=Date("Y");
}

if(isset($_POST['createExcel']) || isset($_POST['createSumExcel']) ) {
	$excel= new ExcelWriter(RECORDS_PATH."facility_billing_".$month."-".$year.".xls");
	if($excel==false)
        	echo $excel->error;
	
	$myArr=array("<b>Charge Type</b>","<b>Date</b>","<b>NetId</b>" ,"<b>Name</b>","<b>CFOP</b>","<b>Equipment</b>", "<b>Usage/Hrs</b>","<b>Rate</b>","<b>Rate Name</b>","<b>Total Amt</b>");
	$excel->writeLine($myArr);
	
	$queryMonthUsage="SELECT s.ID, s.cfop, s.rate, s.description, u.username, u.first, u.last, d.fdname, s.userid, s.deviceid, s.start, s.stop, s.elapsed, d.ratetype, rt.ratetype AS ratetypename, r.ratename FROM session s, users u, device d, ratetypes rt, rates r WHERE r.ID=u.rateid AND u.ID=s.userid AND d.ID=s.deviceid AND MONTH(start)=".$month." AND YEAR(start)=".$year." AND s.verified=1 AND rt.ID = d.ratetype AND d.ratetype=1";
	$monthCharges = $sqlDataBase->query($queryMonthUsage);

	foreach($monthCharges as $id=> $charge)
	{
        	$replace_array=array("-"," ");
        	$printcfop=str_replace($replace_array,"",$charge['cfop']);
        	$printcfop=substr($printcfop,0,1)."-".substr($printcfop,1,6)."-".substr($printcfop,7,6)."-".substr($printcfop,13,6);
		if(strlen($charge['cfop'])>19)
		{
			$printcfop .= "-".substr($printcfop,19,6);
		}
		
		$total = round(round($charge['rate']*60,2)*round($charge['elapsed']/60,2),2);

        	$myArray=array("".$charge['ratetypename']."","".date("n/j/Y",strtotime($charge['start']))."","".$charge['username']."","".$charge['first']." ".$charge['last']."", "".$printcfop."","".$charge['fdname']."","".round($charge['elapsed']/60,2)."","".round($charge['rate']*60,2)."","".$charge['ratename']."","".$total."");
        	$excel->writeLine($myArray);
		print_r($myArray);

	}

	$queryMonthlyCharges="SELECT s.ID, s.cfop, s.rate, s.description, u.username, u.first, u.last, d.fdname, s.userid, s.deviceid, s.start, s.stop, s.elapsed, d.ratetype, rt.ratetype AS ratetypename, r.ratename FROM session s, users u, device d, ratetypes rt, rates r WHERE r.ID=u.rateid AND u.ID=s.userid AND d.ID=s.deviceid AND MONTH(start)=".$month." AND YEAR(start)=".$year." AND s.verified=1 AND rt.ID = d.ratetype AND d.ratetype=2 GROUP BY s.deviceid, s.userid";
     	$monthlyCharges = $sqlDataBase->query($queryMonthlyCharges);
		
	foreach($monthlyCharges as $id=> $charge)
        {
                $replace_array=array("-"," ");
                $printcfop=str_replace($replace_array,"",$charge['cfop']);
                $printcfop=substr($printcfop,0,1)."-".substr($printcfop,1,6)."-".substr($printcfop,7,6)."-".substr($printcfop,13,6);
		if(strlen($charge['cfop'])>19)
		{
			$printcfop .= "-".substr($printcfop,19,6);
		}

                $total = round(round($charge['rate']*60,2));

                $myArray=array("".$charge['ratetypename']."","".date("n/j/Y",strtotime($charge['start']))."","".$charge['username']."","".$charge['first']." ".$charge['last']."", "".$printcfop."","".$charge['fdname']."","".round($charge['elapsed']/60,2)."","".round($charge['rate']*60,2)."","".$charge['ratename']."","".$total."");
                $excel->writeLine($myArray);
                print_r($myArray);
        }
	

        $excel->close();
	
        header("Location: ./records/facility_billing_".$month."-".$year.".xls");

}

if(isset($_POST['submitVerify'])) {
	$verifyArr=$_POST['verify'];
        foreach($verifyArr as $key => $value)
        {
		$session = new Session($sqlDataBase);
		$session->LoadSession($value);
		$session->Verify();
       	}
}


echo "<center><h4>Facility Billing</h4></center>";

if(isset($_GET['edit'])) {
	include "edit_session.php";
}
else {

$availableMonthsQuery = "SELECT distinct DATE_FORMAT(start,'%M %Y') as mon_yr, MONTH(start) AS month, YEAR(start) AS year FROM session ORDER BY start DESC";
$availableMonths = $sqlDataBase->query($availableMonthsQuery);

?>
<br>
<form name="verifyForm" method="post" action="./administration.php?subm=13">
<select name="monthSelected">
<?php
foreach($availableMonths as $id=>$assoc)
{
	echo "<option value=\"".$assoc['month']." ".$assoc['year']."\"";
	if($assoc['month']==$month && $assoc['year']==$year)
	{
		echo " SELECTED";
	}
	echo ">".$assoc['mon_yr']."</option>";
}

?>
</select>
<input  class="grey" type="submit" name="selectMonth" value="Select Billing Period">
<br><br>
<input class="grey"  name="btn" type="button" onclick="CheckAll()" value="Check/Uncheck All">
<input class="grey" type="submit" name="submitVerify" value="Verify Checked">
<input class="grey" type="submit" name="createExcel" value="Create Excel">
<input class="grey" type="submit" name="createSession" value="Create Session">
<br><br>
<h4>Billed Monthly:</h4>
        <table cellpadding="0" cellspacing="1" width="950" class="sortable">
        <tr class="title">
                <td>
                        Status
                </td>
                <td>
                        Name
                </td>
                <td>
                        CFOP
                </td>
                <td>
                        Equipment
                </td>
                <td>
                        Hours
                </td>
                <td>
                        Rate
                </td>
		<td>
			Rate Type
		</td>
                <td>
                        Description
                </td>
                <td>
                        Total
                </td>
                <td>
                        Option
                </td>
        </tr>
<?php


$queryMonthly = "SELECT s.ID, u.cfopl AS ucfopl, s.cfop AS scfopl, s.verified, dr.rate AS drate, s.rate AS srate, d.fdname, u.username, SUM(s.elapsed) AS elapsed, u.first, u.last, s.description, r.ratename FROM  session s, users u, devicerate dr, device d, rates r WHERE d.ID = s.deviceid  AND dr.deviceid=s.deviceid AND r.ID=dr.rateid AND dr.rateid=u.rateid AND u.ID=s.userid AND MONTH(start)=".$month." AND YEAR(start)=".$year." AND d.ratetype=".$rateMonthly." GROUP BY s.deviceid, s.userid";
$monthSessions = $sqlDataBase->query($queryMonthly);

$i=0;
if($monthSessions)
{
foreach($monthSessions as $id=>$assoc) {
        if($assoc['verified']==0) {
                $status="Unverified";
                $rate=$assoc['drate'];
                $check="unchecked";
                $class="f";
                $printcfop=$assoc['ucfopl'];
        }
        else {
                $status="Verified";
                $rate=$assoc['srate'];
                $check="checked";
                $class="e";
                $printcfop=$assoc['scfopl'];
        }
        $replace_array=array("-"," ");
        $printcfop=str_replace($replace_array,"",$printcfop);
        $printcfop=substr($printcfop,0,1)." ".substr($printcfop,1,6)." ".substr($printcfop,7,6)." ".substr($printcfop,13,6);

        echo "<tr class=\"".$class."".($i%2)."\"><td width=\"100\"><input type=\"checkbox\" name=\"verify[]\" value=\"".$assoc['ID']."\" ".$check." /> ".$status."</td><td width=\"130\">".$assoc['first']." ".$assoc['last']."</td><td width=\"150\">".$printcfop."</td><td width=\"130\">".$assoc['fdname']."</td><td width=\"79\" align=\"center\">".round(($assoc['elapsed']/60),2)."</td><td align=\"center\"  width=\"70\">".round(($rate*60),2)."</td><td align=\"center\"  width=\"70\">".$assoc['ratename']."</td><td>".$assoc['description']."</td><td align=\"center\" width=\"70\">$".(round($rate*60))."</td><td align=\"center\"><a href=\"./administration.php?subm=13&edit=true&session=".$assoc['ID']."\">Edit</a></td></tr>";

$i++;
}
}


?>
</table>
<br>
<h4>Billed Usage:</h4>
        <table cellpadding="0" cellspacing="1" width="950" class="sortable" id="anyid">
        <tr class="title">
		<td>
			Status
		</td>
                <td>
                        Date
                </td>
                <td>
                        Name
                </td>
                <td>
                        CFOP
                </td>
                <td>
                        Equipment
                </td>
                <td>
                        Hours
                </td>
                <td>
                        Rate
                </td>
		<td>
			Rate Type
		</td>
		<td>
			Description
		</td>
		<td>
			Total
		</td>
		<td>
			Option
		</td>
        </tr>
<!--	<table cellpadding="0" cellspacing="1" width="899"> -->

<?php

$queryMonthUsage = "SELECT s.ID, u.cfopl AS ucfopl, s.cfop AS scfopl, s.verified, dr.rate AS drate, s.rate AS srate, d.fdname, u.username, s.start, s.stop, s.elapsed, u.first, u.last, s.description, r.ratename FROM  session s, users u, devicerate dr, device d, rates r WHERE d.ID = s.deviceid  AND dr.deviceid=s.deviceid AND r.ID=dr.rateid AND dr.rateid=u.rateid AND u.ID=s.userid AND MONTH(start)=".$month." AND YEAR(start)=".$year." AND d.ratetype=".$rateUsage;
$monthSessions = $sqlDataBase->query($queryMonthUsage);

$i=0;
if($monthSessions)
{
foreach($monthSessions as $id=>$assoc) {
	if($assoc['verified']==0) {
		$status="Unverified";
		$rate=$assoc['drate'];
		$check="unchecked";
		$class="f";
		$printcfop=$assoc['ucfopl'];
	}
	else {
		$status="Verified";
		$rate=$assoc['srate'];
		$check="checked";
		$class="e";
		$printcfop=$assoc['scfopl'];
	}
	$replace_array=array("-"," ");
        $printcfop=str_replace($replace_array,"",$printcfop);
        $printcfop=substr($printcfop,0,1)." ".substr($printcfop,1,6)." ".substr($printcfop,7,6)." ".substr($printcfop,13,6);

	echo "<tr class=\"".$class."".($i%2)."\"><td width=\"100\"><input type=\"checkbox\" name=\"verify[]\" value=\"".$assoc['ID']."\" ".$check." /> ".$status."</td><td width=\"130\">".$assoc['start']."</td><td width=\"130\">".$assoc['first']." ".$assoc['last']."</td><td width=\"150\">".$printcfop."</td><td width=\"130\">".$assoc['fdname']."</td><td width=\"79\" align=\"center\">".round(($assoc['elapsed']/60),2)."</td><td align=\"center\"  width=\"70\">".round(($rate*60),2)."</td><td align=\"center\"  width=\"70\">".$assoc['ratename']."</td><td>".$assoc['description']."</td><td align=\"center\" width=\"70\">$".round((round($rate*60,2)*round($assoc['elapsed']/60,2)),2)."</td><td align=\"center\"><a href=\"./administration.php?subm=13&edit=true&session=".$assoc['ID']."\">Edit</a></td></tr>";

$i++;
}
}


?>

</table>
<br>
<input class="grey"  name="btn" type="button" onclick="CheckAll()" value="Check/Uncheck All">  

<input class="grey" type="submit" name="submitVerify" value="Verify Checked">
<input class="grey" type="submit" name="createExcel" value="Create Excel">
</form>

<?php
}
} else {
        include "./denied.php";
}
?>

