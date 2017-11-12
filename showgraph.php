<?php // content="text/plain; charset=utf-8"
include("config.php");
include("classes/AutoLoadClasses.php");
include("includes/mysql_connect.php");
    
$graphData = new GraphData($sqlDataBase);

$userid = $_GET['user'];

$queryUserDateRange="SELECT graphenddate,graphstartdate,graphinterval,graphhsize,graphvsize FROM users WHERE ID=".$userid;
$userDateRange = $sqlDataBase->query($queryUserDateRange);

$endDate=$userDateRange[0]['graphenddate'];
$startDate=$userDateRange[0]['graphstartdate'];
$interval = $userDateRange[0]['graphinterval'];
$hSize = $userDateRange[0]['graphhsize'];
$vSize = $userDateRange[0]['graphvsize'];
$lineGraph = new StatsGraph($hSize,$vSize);

if(isset($_GET['printfriendly']))
{
	$lineGraph->SetGraphColors("#FFFFFF","#FFFFFF","#FFFFFF");
}

$queryUserGraphs="SELECT l.deviceid,l.userid,l.groupid, l.dataunit, l.graphname,c.colorname FROM linegraphs l, graphcolors c WHERE submiterid=".$userid." AND c.ID=l.colorid";
$userGraphs=$sqlDataBase->query($queryUserGraphs);
$yLinePlotDetected=0;
$y2LinePlotDetected=0;
foreach($userGraphs as $row)
{
	extract($row);
	$lineData = $graphData->GetLineData($deviceid,$userid,$groupid,$startDate,$endDate,$interval,$dataunit);
	$lineGraph->AddLineGraph($graphData->GetYData(),$graphname,$dataunit,$colorname);
}
$lineGraph->SetXLabels($graphData->GetXData());
$lineGraph->DrawLineGraph();
?>
