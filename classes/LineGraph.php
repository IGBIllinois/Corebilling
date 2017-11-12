<?php

include 'AutoLoadClasses.php';
include 'jpgraph/jpgraph.php';
include 'jpgraph/jpgraph_line.php';

class LineGraph
{

	public  $sqlDataBase;
	private $graph;

	public function __construct($sqlDataBase)
	{
		$this->$sqlDataBase = $sqlDataBase;
		$graph = new Graph(800,600);
		$graph->SetScale("textlin");
		$graph->SetY2Scale("lin");
		$graph->y2axis->SetColor("orange");
		$graph->yaxis->SetColor("blue");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,40,60);
		// Setup the titles
		$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
		$graph->title->Set('Usage/Billing Statistics');
		$graph->subtitle->SetFont(FF_ARIAL,FS_ITALIC,10);
		
		// Setup the labels to be correctly format on the X-axis
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->Set("Time");
		$graph->yaxis->title->Set("Minutes/Billing");
		$graph->SetScale("textlin");
		$graph->SetY2Scale("lin");
	}

	public function __destruct()
	{

	}

	public function GraphSize($width,$height)
	{
		$graph = new Graph($width,$height);
		$graph->SetScale("textlin");
		$graph->SetY2Scale("lin");
		$graph->SetShadow();
		$graph->img->SetMargin(40,40,20,70);
	}
	public function GetLineParams($submitter)
	{
		$sqlQueryGraphParams = "SELECT * FROM linegraphs WHERE submitter=".$submitter."";
		$assocArray = $sqlDataBase->query($sqlQueryGraphParams);
		return $assocArray;
	}

	public function DrawGraphs($paramArray,$unit, $startDate,$endDate)
	{

		$yScaleNumber=0;
		foreach($paramArray as $lineParams)
		{
			//Generate SQL query for each line
			$sql = "SELECT ";
			 
			if($paramArray['graphaxisid']==1)
			{
				$sql+="SUM(elapsed),UNIX_TIMESTAMP(start) ";
			}
			if($paramArray['graphaxisid']==2)
			{
				$sql+="SUM(elapsed*rate),UNIX_TIMESTAMP(start) ";
			}
			 
			$sql += "FROM session WHERE";
			 
			if($paramArray['deviceid']!=-1)
			{
				$sql+=" deviceid=".$paramArray['deviceid']." AND";
			}
			if($paramArray['userid']!=-1)
			{
				$sql+=" userid=".$paramArray['userid']." AND";
			}
			if($paramArray['groupid']!=-1)
			{
				$sql+=" groupid=".$paramArray['groupid']." AND";
			}
			if($paramArray['departmentid']!=-1)
			{
				$sql+=" departmentid=".$paramArray['departmentid']." AND";
			}
			$sql+=" start >=".$startDate." AND stop <=".$endDate." GROUP BY ";
			 
			if($unit==1)
			{
				$sql+="YEAR(start),MONTH(start),DAY(start)";
			}
			if($unit==7)
			{
				$sql+="YEAR(start),WEEK(start)";
			}
			if($unit==31)
			{
				$sql+="YEAR(start),MONTH(start)";
			}
			 
			$result = mysql_query( $query );
			$i = 0;
			while( $row = mysql_fetch_array( $result ) )
			{
				$yData[ $i] = $row[0] ;
				$xData[$i++]= $row[1];
			}

			if($unit==1)
			{
				$graph->subtitle->Set('Graph Of Day Usage');
				// The second paramter set to 'true' will make the library interpret the
				// format string as a date format. We use a Month + Year format
				$graph->xaxis->SetLabelFormatString('M,Y',true);
				// Get manual tick every second year
				list($tickPos,$minTickPos) = $dateUtils->getTicks($xdata,DSUTILS_YEAR2);
				$graph->xaxis->SetTickPositions($tickPos,$minTickPos);
			}
			if($unit==7)
			{
				$graph->subtitle->Set('Graph Of Week Usage');
				// The second paramter set to 'true' will make the library interpret the
				// format string as a date format. We use a Month + Year format
				$graph->xaxis->SetLabelFormatString('Y, W',true);
				// Get manual tick every second year
				list($tickPos,$minTickPos) = $dateUtils->getTicks($xdata,DSUTILS_YEAR2);
				$graph->xaxis->SetTickPositions($tickPos,$minTickPos);

			}
			 
			if($unit==31)
			{
				$graph->subtitle->Set('Graph Of Year Usage');
				// The second paramter set to 'true' will make the library interpret the
				// format string as a date format. We use a Month + Year format
				$graph->xaxis->SetLabelFormatString('M, Y',true);
				// Get manual tick every second year
				list($tickPos,$minTickPos) = $dateUtils->getTicks($xdata,DSUTILS_YEAR2);
				$graph->xaxis->SetTickPositions($tickPos,$minTickPos);
			}
			
			$graphLinePlot = new LinePlot($yData,$xData);
			// Add the plot to the graph
			if($paramArray["graphaxisid"]==1)
			{
				$graph->Add($lineplot);
			}
			if($paramArray["graphaxisid"]==2)
			{
				$graph->AddY2($lineplot2);
			}
			$lineplot2->SetColor("orange");
			$lineplot2->SetWeight(2);
			
			
			
			
		}
		
		
	}

	public function AddLineGraph($deviceid,$userid,$groupid,$departmentid,$YAxisid,$submitterid)
	{
		$sql = "INSERT INTO linegraphs (deviceid,userid,groupid,departmentid,submitterid) VALUES(".$deviceid.",".$userid.",".$groupid.",".$departmentid.",".$submitterid.")";
		$sqlDataBase->insertQuery($sql);
	}

	private function DrawLineGraph()
	{
			
	}
}

?>
