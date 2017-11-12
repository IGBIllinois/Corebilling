<?php
if(isset($_GET['deviceid']) && isset($_GET['date']))
{
	include('classes/AutoLoadClasses.php');
	include ("graph_lib/src/jpgraph.php"); 
	include ("graph_lib/src/jpgraph_gantt.php"); 
	include ("config.php");
	include('includes/mysql_connect.php');
	$deviceid = mysql_real_escape_string($_GET['deviceid']);
	$date = mysql_real_escape_string($_GET['date']);
	$view = mysql_real_escape_string($_GET['view']);
	
	$graph = new GanttGraph (0,0, "auto");
	if($view=="day")
	{
		$queryDeviceReservations = "SELECT ei.start,ei.stop,u.first,u.last, ei.training FROM event_info ei, users u WHERE u.ID=ei.userid AND deviceid=".$deviceid." AND DATE(start)=\"".$date."\" ORDER BY start ASC";
		$deviceReservations = $sqlDataBase->query($queryDeviceReservations);
		$graph->scale->hour->SetIntervall("01:00");
		$graph->scale->hour->grid->Show();
		$graph->scale->hour->grid->SetColor( 'black' );
		$graph->ShowHeaders( GANTT_HHOUR | GANTT_HDAY);
		$graph->scale->day->SetStyle(DAYSTYLE_LONGDAYDATE1);
	}elseif($view=="week")
	{
		$weekNum = Date("W",strtotime($date));
		$year = Date("Y",strtotime($date));
		$queryDeviceReservations = "SELECT ei.start,ei.stop,u.first,u.last, ei.training FROM event_info ei, users u WHERE u.ID=ei.userid AND deviceid=".$deviceid." AND WEEK(start)=\"".$weekNum."\" AND YEAR(start)=\"".$year."\" ORDER BY start ASC";
                $deviceReservations = $sqlDataBase->query($queryDeviceReservations);
		$graph->ShowHeaders( GANTT_HHOUR | GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
		$graph->scale->day->SetStyle(DAYSTYLE_SHORTDAYDATE1);
		$graph->scale->hour->SetIntervall("04:00");
		$graph->scale->hour->grid->Show();
                $graph->scale->hour->grid->SetColor( 'black' );
		
	}elseif($view=="month")
	{
		$monthNum = Date("n",strtotime($date));
		$year = Date("Y",strtotime($date));
                $queryDeviceReservations = "SELECT ei.start,ei.stop,u.first,u.last, ei.training FROM event_info ei, users u WHERE u.ID=ei.userid AND deviceid=".$deviceid." AND MONTH(start)=\"".$monthNum."\" AND YEAR(start)=\"".$year."\" ORDER BY start ASC";
                $deviceReservations = $sqlDataBase->query($queryDeviceReservations);
		$graph->ShowHeaders(GANTT_HDAY | GANTT_HMONTH | GANTT_HWEEK);
		$graph->scale->day->SetStyle(DAYSTYLE_SHORT);
		$graph->scale->hour->SetIntervall("24:00");
		$graph->scale->day->grid->Show();
                $graph->scale->day->grid->SetColor( 'black' );	
	}
	$graph->SetColor('#ffffff');
       	$graph->SetMarginColor('#d3d1d2');
        $graph->SetFrame(true,'#d3d1d2');
	// Add title and subtitle 
	//$graph->title-> Set(Date("F jS Y",strtotime($date)));

	// Instead of week number show the date for the first day in the week 
	// on the week scale 
	$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY2); 
	$graph->scale->week->SetFont(FF_FONT1,FS_BOLD);

	$graph->scale->day->SetFont(FF_FONT1,FS_BOLD);


	$graph->scale->SetWeekStart(0);

	// Use the short name of the month together with a 2 digit year 
	// on the month scale 
	//$graph->scale-> month-> SetStyle( MONTHSTYLE_SHORTNAME); 
	
	$graph->scale->hour->SetStyle(HOURSTYLE_H24);

	// Format the bar for the first activity 
	// ($row,$title,$startdate,$enddate)
	$i=0;
	if($deviceReservations)
	{ 
		foreach($deviceReservations as $id=>$deviceReservation)
		{
			$activity = new GanttBar ($i,$deviceReservation['first']." ".$deviceReservation['last'], $deviceReservation['start'], $deviceReservation['stop']);
			//$activity = new GanttBar (0,"", $deviceReservation['start'], $deviceReservation['stop']);
			// Yellow diagonal line pattern on a red background
			if($deviceReservation['training'])
			{
				$activity ->SetPattern(BAND_RDIAG, "yellow");
	                        $activity ->SetFillColor ("red");
			}
			else
			{
				$activity ->SetPattern(BAND_RDIAG, "yellow"); 
				$activity ->SetFillColor ("blue"); 
			}
	
			// Finally add the bar to the graph 
			$graph->Add( $activity); 
			$i++;
		}
	}
	else
	{
		$activity = new GanttBar(0,"No Reservations",$date,$date);
		$activity ->SetPattern(BAND_RDIAG, "white");
                $activity ->SetFillColor ("white");
		$graph->Add( $activity);
	}
	// ... and display it
	$graph->Stroke(); 
}
?>
