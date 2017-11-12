<?php

include"./includes/mysql_connect.php";

$numberofweeks = 57;
$startHour = 12;
$stopHour = 17;
$description = "Blocked";
$dayOfTheWeek = 3;
$userid = 15;
$deviceid = 35;

for($i=0; $i<$numberofweeks*7 ; $i++)
{
	$dbc = 0;	
	$mkStart = mktime($startHour, 0, 0, date("n")+1, date("j")+$i, date("Y"));
	$mkStop = mktime($stopHour, 0, 0, date("n")+1, date("j")+$i, date("Y"));
	echo $mkStart;
	if(Date("N",$mkStart)==$dayOfTheWeek)
	{
		// Check for timeslot conflicts when a timeslot is select to be inputed into database
                //echo mysql_errno($dbc). ": " .mysql_error($dbc). "\n";
                $starttime=Date("H:i:s",$mkStart);
                $finishtime=Date("H:i:s",$mkStop);
                $querytimeslot=mysql_query("SELECT ID,HOUR(start) FROM event_info WHERE deviceid=".$deviceid." AND DAY(start)=".Date("j",$mkStart)." AND MONTH(start)=".Date("n",$mkStart)." AND YEAR(start)=".Date("Y",$mkStart)." AND ((TIME_TO_SEC(TIME(start))<TIME_TO_SEC(TIME('$finishtime')) AND TIME_TO_SEC(TIME('$finishtime'))<TIME_TO_SEC(TIME(stop))) OR (TIME_TO_SEC(TIME(start))<TIME_TO_SEC(TIME('$starttime')) AND TIME_TO_SEC(TIME('$starttime'))<TIME_TO_SEC(TIME(stop))) OR (TIME_TO_SEC(TIME('$starttime'))<=TIME_TO_SEC(TIME(start)) AND TIME_TO_SEC(TIME(stop))<=TIME_TO_SEC(TIME('$finishtime'))))");
                //echo mysql_errno($dbc). ": " .mysql_error($dbc). "\n";
                if(mysql_num_rows($querytimeslot)>0) 
		{
                        $starttime=mysql_result($querytimeslot,0,"HOUR(start)");
                        if($starttime!=0)
                        {
			
                        echo "<font color=\"red\" size=\"2\"><br><br />There are conflicts with the timeslot chosen. ".Date("Y-m-d H:i:s",$mkStart)."</a><br>";
                        }
                }
		else {
		//echo "SELECT ID,HOUR(start) FROM event_info WHERE deviceid=".$deviceid." AND DAY(start)=".Date("j",$mkStart)." AND MONTH(start)=".Date("n",$mkStart)." AND YEAR(start)=".Date("Y",$mkStart)." AND ((TIME_TO_SEC(TIME(start))<TIME_TO_SEC(TIME('$finishtime')) AND TIME_TO_SEC(TIME('$finishtime'))<TIME_TO_SEC(TIME(stop))) OR (TIME_TO_SEC(TIME(start))<TIME_TO_SEC(TIME('$starttime')) AND TIME_TO_SEC(TIME('$starttime'))<TIME_TO_SEC(TIME(stop))) OR (TIME_TO_SEC(TIME('$starttime'))<=TIME_TO_SEC(TIME(start)) AND TIME_TO_SEC(TIME(stop))<=TIME_TO_SEC(TIME('$finishtime'))))";
		mysql_query("INSERT INTO event_info (deviceid,userid,start,stop,description) VALUES ($deviceid,$userid,\" ".Date("Y-m-d H:i:s",$mkStart)."\",\"".Date("Y-m-d H:i:s",$mkStop)."\", \"$description\")");
		//echo "<br>INSERT INTO event_info (deviceid,userid,start,stop,description) VALUES ($deviceid,$userid, ".Date("Y-m-d H:i:s",$mkStart).",".Date("Y-m-d H:i:s",$mkStop).", $description)";
		}
	}
	
}
mysql_close();
?>
