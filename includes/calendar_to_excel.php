<?php

$excel= new ExcelWriter(RECORDS_PATH."facility_calendar_".$monthSelected."-".$yearSelected.".xls");
if($excel==false)
	echo $excel->error;

$myArr=array("<b>Start</b>","<b>End</b>","<b>Name</b>","<b>Device</b>","<b>Description</b>", "<b>E-Mail</b>","<b>Training</b>");
$excel->writeLine($myArr);

$deviceQuery = "";
if($selectedDevice > 0)
{
	$deviceQuery = " AND e.deviceid=".$selectedDevice;
}

$queryMonthReservations="SELECT e.ID, d.devicename, d.fdname, e.deviceid, u.username, u.first, u.last, u.email, e.userid, e.description, e.start AS starttime, e.stop AS stoptime, e.training FROM event_info e, device d, users u WHERE MONTH(e.start)=".$monthSelected." AND YEAR(e.start)=".$yearSelected." AND u.ID=e.userid AND d.ID=e.deviceid".$deviceQuery." ORDER BY e.start";
echo $queryMonthReservations;
$monthSelectedReservations=$sqlDataBase->query($queryMonthReservations);

foreach($monthSelectedReservations as $id=>$monthSelectedReservation)
{        
	$myArray=array("".$monthSelectedReservation['starttime']."","".$monthSelectedReservation['stoptime']."","".$monthSelectedReservation['first']." ".$monthSelectedReservation['last']."","".$monthSelectedReservation['fdname']."","".$monthSelectedReservation['description']."","".$monthSelectedReservation['email']."","".$monthSelectedReservation['training']."");
	$excel->writeLine($myArray);
	
}
        $excel->close();
        header("Location: ./records/facility_calendar_".$monthSelected."-".$yearSelected.".xls");

?>
