<?php
include "excelwriter.inc.php";

$excel= new ExcelWriter("/home/web/coreapp17/records/facility_billing_".$month."-".$year.".xls");
if($excel==false)
	echo $excel->error;

$myArr=array("<b>Status</b>","<b>Date</b>","<b>Name</b>","<b>CFOP</b>","<b>Equipment</b>", "<b>Usage/Hrs</b>","<b>Rate</b>","<b>Total Amt</b>");
$excel->writeLine($myArr);



$queryMonth=mysql_query("SELECT s.ID, s.cfop, s.verified, s.rate, s.description, u.username, u.first, u.last, u.cfopl, d.devicename, s.userid, s.deviceid, s.start, s.stop, s.elapsed FROM session s, users u, device d WHERE u.ID=s.userid AND d.ID=s.deviceid AND MONTH(start)='$month' AND YEAR(start)='$year'");
while($row=mysql_fetch_assoc($queryMonth)) {
        extract($row);
        $query_userrate=mysql_query("SELECT rate FROM users WHERE ID='$userid'");
        $userrate=mysql_result($query_userrate,0,"rate");
        $query_devicerate=mysql_query("SELECT ".$userrate." FROM device WHERE ID=".$deviceid."");
        @$devicerate=mysql_result($query_devicerate,0,$userrate);
        if($verified==0) {
                $status="Unverified";
                $rate=$devicerate;
                $printcfop=$cfopl;
        }
        else {
                $status="Verified";
                $printcfop=$cfop;
        }
	$replace_array=array("-"," ");
	$printcfop=str_replace($replace_array,"",$printcfop);
	$printcfop=substr($printcfop,0,1)." ".substr($printcfop,1,6)." ".substr($printcfop,7,6)." ".substr($printcfop,13,6);
	
	$myArray=array("<Data type=number>$status</Data>","".date("n/j/Y",strtotime($start))."","".$first." ".$last."", "$printcfop","$devicename","".round($elapsed/60,2)."","".round($rate*60,2)."","".round(round($rate*60,2)*round($elapsed/60,2),2)."");
	$excel->writeLine($myArray);
	
}
        $excel->close();
        //header("Location: ./records/facility_billing_".$month."-".$year.".xls");

?>
