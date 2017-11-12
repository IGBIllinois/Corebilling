<?php
if(isset($_SESSION['usertype'])){
	if($_SESSION['usertype']==1){
		
		//Check if submit line graph was clicked		
		//If so gerate the correct query string
		if(isset($_POST['submitLineGraph']))
		{	
			$queryAddLineGraph="INSERT INTO linegraphs (deviceid,userid,groupid,submiterid,dataunit,graphname,colorid)VALUES(\"".$_POST['device']."\",\"".$_POST['user']."\",\"".$_POST['group']."\",\"".$_SESSION['userid']."\",\"".$_POST['dataunit']."\",\"".$_POST['graphname']."\",\"".$_POST['color']."\")";
			$sqlDataBase->insertQuery($queryAddLineGraph);
		}
	
		if(isset($_POST['submitDateRange']))
		{
			$queryUpdateUserGraphSettings="UPDATE users SET graphstartdate=\"".Date("Y-m-d",strtotime($_POST['startDate']))."\",graphenddate=\"".Date("Y-m-d",strtotime($_POST['endDate']))."\",graphinterval=\"".$_POST['interval']."\" WHERE ID=".$_SESSION['userid'];
			$sqlDataBase->nonSelectQuery($queryUpdateUserGraphSettings);
		}

		if(isset($_POST['submitResolution']))
                {
			$hSize=640;
			$vSize=480;
			switch ($_POST['resolution']) {
				case 1:
					$hSize=320;
					$vSize=240;
					break;
				case 2:
                                        $hSize=640;
                                        $vSize=480;
                                        break;
				case 3:
                                        $hSize=1024;
                                        $vSize=768;
                                        break;
				case 4:
                                        $hSize=1280;
                                        $vSize=1024;
                                        break;
				case 5:
                                        $hSize=1600;
                                        $vSize=1200;
                                        break;
				case 6:
                                        $hSize=1920;
                                        $vSize=1400;
                                        break;
			}		
                        $queryUpdateUserGraphSettings="UPDATE users SET graphhsize=".$hSize.",graphvsize=".$vSize." WHERE ID=".$_SESSION['userid'];
                        $sqlDataBase->nonSelectQuery($queryUpdateUserGraphSettings);
                }
		
		if(isset($_GET['deletegraph']))
		{
			$queryDeleteGraph="DELETE FROM linegraphs WHERE id=".$_GET['deletegraph'];
			$sqlDataBase->nonSelectQuery($queryDeleteGraph);
		}
			
		echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"js/datepicker.js\"></SCRIPT>";
		
		echo "<h4><center>Line Graph</center></h4><br>";
		echo "<table width=100%>";
		echo "<tr><td width=200>";
		echo "<a class=\"calendar\" href=\"./showgraph.php?user=".$_SESSION['userid']."&printfriendly=true\" TARGET=\"_blank\">Printer Friendly</a><br><br>";
		echo "<div id=\"semenu\">Remove Graphs:</div>";
		echo "<table><tr class=\"title\"><td>Graph Name</td><td>Option</td></tr>";	
		$queryLineGraphs="SELECT l.id,l.graphname,c.code FROM linegraphs l, graphcolors c WHERE l.submiterid=".$_SESSION['userid']." AND c.ID=l.colorid";
                $lineGraphsArr=$sqlDataBase->query($queryLineGraphs);
		$i=0;
		if(!empty($lineGraphsArr))
		{
                foreach($lineGraphsArr as $row)
                {
                        extract($row);
                        echo "<tr class=\"d".($i%2)."\"><td><FONT color=\"".$code."\">".$graphname."</FONT></td><td><a href=\"./administration.php?subm=7&deletegraph=".$id."\">Delete</a></td></tr>";
			$i++;
                }
		}
                echo "</table><br>";
	
		echo "<div id=\"semenu\">_______________________________</div><br>";		
		//Create form for submitting lines
		echo "<form name=\"resolutionForm\" method=\"post\" action=\"./administration.php?subm=7\">";		
		echo "<div id=\"semenu\">Resolution:</div>";
		echo "<select name=\"resolution\">";
		echo "<option value=\"1\">320x240</option>";
		echo "<option value=\"2\">640x480</option>";
		echo "<option value=\"3\">1024x768</option>";
		echo "<option value=\"4\">1280x1024</option>";
		echo "<option value=\"5\">1600x1200</option>";
		echo "<option value=\"6\">1920x1400</option>";
		echo "</select>";
		echo "<br><br><input type=submit class=\"grey\" name=\"submitResolution\" value=\"Change Resolution\"><br><br>";
		echo "</form>";
		
		echo "<div id=\"semenu\">_______________________________</div><br>";	

		echo "<form name=\"dateForm\" method=\"post\" action=\"./administration.php?subm=7\">";
		echo "<div id=\"semenu\">Interval:</div>";
		echo "<select name=\"interval\">";
		echo "<option value=\"day\">Day</option>";
		echo "<option value=\"month\">Month</option>";
		echo "<option value=\"year\">Year</option>";
		echo "</select><br><br>";
		echo "<div id=\"semenu\">Start Date:</div>";
		echo "<input name=\"startDate\">"; 
		echo "<input type=button value=\"select\" onclick=\"displayDatePicker('startDate');\"><br><br>";
		echo "<div id=\"semenu\">End Date:</div>";
		echo "<input name=\"endDate\">";
		echo "<input type=button value=\"select\" onclick=\"displayDatePicker('endDate');\"><br>";
		echo "<br><input type=submit class=\"grey\" name=\"submitDateRange\" value=\"Change Date Range\"><br><br>";	
		echo "</form>";

		echo "<div id=\"semenu\">_______________________________</div><br>";

		echo "<form name=\"lineForm\" method=\"post\" action=\"./administration.php?subm=7\">";
		echo "<div id=\"semenu\">Line Name:</div>";
		echo "<input type=\"text\" name=\"graphname\"><br><br>";
		echo "<div id=\"semenu\">Device:</div>";
		echo "<select name=\"device\"><option value=\"%\">All</option>";
		
		$queryDevices="SELECT ID,devicename FROM device";
                $devicesArr=$sqlDataBase->query($queryDevices);
                foreach($devicesArr as $row)
                {
                        extract($row);
                        echo "<option value=".$ID.">".$devicename."</option>";
                }
                echo "</select><br>";
		echo "<div id=\"semenu\">User:</div>";
		echo "<select name=\"user\"><option value=\"%\">All</option>";
		$queryUsers="SELECT ID,username FROM users";
                $usersArr=$sqlDataBase->query($queryUsers);
                foreach($usersArr as $row)
                {
                        extract($row);
                        echo "<option value=".$ID.">".$username."</option>";
                }
                echo "</select><br>";

		echo "<div id=\"semenu\">Groups:</div>";
		echo "<select name=\"group\"><option value=\"%\">All</option>";
		$queryGroups="SELECT ID,groupname FROM groups";
		$groupsArr=$sqlDataBase->query($queryGroups);
		foreach($groupsArr as $row)
		{
			extract($row);
			echo "<option value=".$ID.">".$groupname."</option>";
		}
		echo "</select><br><br>";	
		echo "<div id=\"semenu\">Data Type:</div>";
		echo "<select name=\"dataunit\">";
		echo "<option value=\"0\">Hours</option>";
		echo "<option value=\"1\">Billing</option>";
		echo "</select><br><br>";
		echo "<div id=\"semenu\">Line Color:</div>";
		echo "<select name=\"color\">";
		$queryColors="SELECT ID,colorname,code FROM graphcolors";
                $colorsArr=$sqlDataBase->query($queryColors);
                foreach($colorsArr as $row)
                {
                        extract($row);
                        echo "<option value=".$ID."  style=\"background-color:".$code.";\">".$colorname."</option>";
                }
                echo "</select><br><br>"; 
		echo "<input type=submit  class=\"grey\" name=\"submitLineGraph\" value=\"Add Line Graph\"><br>";
		echo "</td><td width=400>";
		echo "<img src=\"showgraph.php?user=".$_SESSION['userid']."\" border=0>";
		echo "</td></tr></table>";
		echo "<br><br><br><br>";
		echo "</form>";

		
	}
	else {
		include "./denied.php";
	}
}
else {
	include "./denied.php";
}
?>
