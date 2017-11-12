<?php
$user = new User($sqlDataBase);
$user->LoadUser($_SESSION['userid']);
$calendar = new Calendar($sqlDataBase);
$calendar->User($user);
$calendar->URL("./index.php?subm=3");

if(isset($_POST['deviceSelected']))
{
	$selectedDevice=$_POST['deviceSelected'];
}
else{
	$selectedDevice=0;
}
if(isset($_POST['monthSelected']))
{
	$monthSelected = $_POST['monthSelected'];
}
else
{
	$monthSelected = date('n');
}

if(isset($_POST['yearSelected']))
{
	$yearSelected = $_POST['yearSelected'];
}
else
{
	$yearSelected = date('Y');
}

if(isset($_POST['daySelected']))
{
	$daySelected = $_POST['daySelected'];
}
else
{
	$daySelected = date('j');
}
if(isset($_POST['description']))
{
	$description = mysql_real_escape_string($_POST['description']);
}
else
{
	$description = "";
}

if(isset($_POST['ganttView']))
{
	$selectedGanttView = $_POST['ganttView'];
}
else
{
	$selectedGanttView = "day";
}

if(isset($_GET['del']))
{
	$selectedDevice = $_GET['device'];
	$monthSelected = $_GET['month'];
	$yearSelected = $_GET['year'];
	$calendar->DeleteEvent($_GET['del']);
}

if(isset($_POST['submitReservation']))
{
	$startTimeStamp = @date('Y-m-d H:i:s',strtotime($yearSelected."-".$monthSelected."-".$daySelected." ".$_POST['start']));
	$finishTimeStamp = @date('Y-m-d H:i:s',strtotime($yearSelected."-".$monthSelected."-".$daySelected." ".$_POST['finish']));

	if(isset($_POST['traincheckbox']))
	{
		$train = 1;
	}
	else
	{
		$train = 0;
	}
	$calendar->AddEvent($startTimeStamp,$finishTimeStamp, mysql_real_escape_string($_POST['description']), $selectedDevice, $_SESSION['userid'],$train);
	
	if($_SESSION['usertype']==1)
	{
		if(isset($_POST['repeatDays']))
		{
			$repeatDays = $_POST['repeatDays'];
			for($repeatDay=1; $repeatDay<$repeatDays; $repeatDay++)
			{
				if(isset($_POST["day".date('w',(strtotime($startTimeStamp) + $repeatDay*86400))."Box"]))
				{
					$calendar->AddEvent(date('Y-m-d H:i:s',(strtotime($startTimeStamp) + $repeatDay*86400) ),date('Y-m-d H:i:s',strtotime($finishTimeStamp)+ $repeatDay        *86400), mysql_real_escape_string($_POST['description']), $selectedDevice, $_SESSION['userid'],$train);
				}			
			}
		}
	}		
}

if(isset($_POST['filterTraining']))
{
	$calendar->SetFilterTraining();
}

if(isset($_POST['exportToExcel']))
{
	include('includes/calendar_to_excel.php');
}

$calendar->Device($selectedDevice);
?>
<div id="content">
<form action="./index.php?subm=3" method=POST>
<table width="980">
	<tr>
		<td><center><h4>Reservations Calendar</h4></center></td>
	</tr>
	<tr>
		<td>
        	        <?php
        	        if($selectedDevice != 0)
        	        {
        	        ?>
			<table width="100%">
			<tr>
				<td>
				<h4>Step 2:</h4><font size="2" color="#465153">(Create reservation)</font>
        	        	<table>
        	        	<tr valign=center>
        	        	        <td>
        	        	        <font size="3" color="#465153">Day: </font>
        	        	        </td>
        	        	        <td>
        	        	        <select name="daySelected" onchange="this.form.submit()">
        	        	        <?php
        	        	                $daysInMonth = date('t',mktime(0,0,0,$monthSelected,1,$yearSelected));
        	        	                for($d = 1; $d <= $daysInMonth; $d++)
        	        	                {
        	        	                        echo "<option value=".$d;
        	        	                        if($daySelected ==  $d)
        	        	                        {
        	        	                                echo " SELECTED";
        	        	                        }
        	        	                        echo ">".Date("F jS",mktime(0,0,0,$monthSelected,$d,$yearSelected))."</option>";
        	        	                }
		                        ?>
		                        </select>
		                        <SCRIPT LANGUAGE="JavaScript" SRC="js/timepicker.js" type="text/Javascript"></SCRIPT>
		                        </td>
       		         	</tr>
       		         	<tr valign=center>
       		         	        <td><font size="3" color="#465153">Start Time: </font></td>
        		 	               <td><input readonly='readonly' name='start' id='timepicker1' type='text' value='12:00 pm' size=8 maxlength=8 ONBLUR="validateDatePicker(this)">
       		         	        <IMG SRC="imgs/timepicker.gif" BORDER="0" ALT="Pick a Time!" ONCLICK="selectTime(this,document.getElementById('timepicker1'))" STYLE="cursor: hand"></td>
       		         	</tr>
       		         	<tr valign=center>
       		         	        <td><font size="3" color="#465153">End Time: </font></td>
       		         	        <td><input readonly='readyonly' name='finish' id='timepicker2' type='text' value='12:00 pm' size=8 maxlength=8 ONBLUR="validateDatePicker(this)">
       		         	        <IMG SRC="imgs/timepicker.gif" BORDER="0" ALT="Pick a Time!" ONCLICK="selectTime(this,document.getElementById('timepicker2'))" STYLE="cursor: hand"></td>
       		         	</tr>
       			 	        <?php
        	       	 		if($_SESSION['usertype']==1)
        	       	 		{
					        echo "<tr><td><font size=\"3\" color=\"#465153\">Repeat #:</font></td><td><input size=5 type=\"text\" name=\"repeatDays\" value=\"0\"> Days</td></tr>";
						echo "<tr><td><font size=\"3\" color=\"#465153\">Days:</font></td><td>
							Sun:<input type=\"checkbox\" name=\"day0Box\" value=\"sunday\">
							Mon:<input type=\"checkbox\" name=\"day1Box\" value=\"monday\">
							Tue:<input type=\"checkbox\" name=\"day2Box\" value=\"tuesday\">
							Wed:<input type=\"checkbox\" name=\"day3Box\" value=\"wednesday\">
							Thu:<input type=\"checkbox\" name=\"day4Box\" value=\"thursday\">
							Fri:<input type=\"checkbox\" name=\"day5Box\" value=\"friday\">
							Sat:<input type=\"checkbox\" name=\"day6Box\" value=\"saturday\"></td></tr>";
        	         		        echo "<tr><td><font size=\"3\" color=\"#465153\">Training:</font></td><td><input type=\"checkbox\" name=\"traincheckbox\" value=\"train\"></td></tr>";
		                	}
        	        		?>
        	        	<tr>
		                	<td><font size="3" color="#465153">Description: </font></td>
		                	<td><textarea rows=3 cols="20" name="description"><?php echo $description; ?></textarea></td>
		                </tr>
		                <tr>
		                	<td></td>
		                	<td><input type="submit" name="submitReservation" value="Create Reservation" class="grey"></td>
		                </tr>
	        	        </table>

				</td>
				<tr>
				</tr>
                        	<td>
				<?php
					echo "<br><font size=\"2\" color=\"#465153\">(Reservations Gantt Chart View)</font><br>";
					echo "<input type=\"radio\" value=\"day\" name=\"ganttView\" onchange=\"this.form.submit()\" ".(($selectedGanttView=="day")?"CHECKED":"").">Day <input type=\"radio\" value=\"week\" name=\"ganttView\" onchange=\"this.form.submit()\" ".(($selectedGanttView=="week")?"CHECKED":"").">Week <input type=\"radio\" value=\"month\" name=\"ganttView\" onchange=\"this.form.submit()\" ".(($selectedGanttView=="month")?"CHECKED":"").">Month<br>";
                        		echo "<img src=\"ganttchart.php?deviceid=".$selectedDevice."&date=".Date("Y-m-d",mktime(0,0,0,$monthSelected,$daySelected,$yearSelected))."&view=".$selectedGanttView."\">";
				?>
                        	</td>
                	</tr>
                	</table>
	        <?php
	        }
	        ?>
		</td>
	</tr>
	<tr>
		<td>
			<h4>Step 1:</h4><font size="2" color="#465153">(Select a device calendar to view)</font>
			<table>
			<tr>
				<td>
				<select name="deviceSelected">
				<option value=0>All Devices</option>
				<?php
				$queryDevices = "SELECT fdname,ID FROM device WHERE statusid=1 ORDER BY fdname";
				$devices = $sqlDataBase->query($queryDevices);
				foreach($devices as $id=>$assoc)
				{
					if($user->GetDevicePerm($assoc['ID']))
					{
						echo "<option value=".$assoc['ID'];
						if($assoc['ID']==$selectedDevice)
						{
							echo " SELECTED";
						}
							echo ">".$assoc['fdname']."</option>";
						}
					}
				?>
				</select>
				</td>
				<td>
				<select name="monthSelected">
				<?php
				for($i=1;$i<=12; $i++)
				{
					echo "<option value=".$i;
					if($monthSelected == $i)
					{
						echo " SELECTED";
					}
					echo ">".Date("F", mktime(0, 0, 0, $i+1, 0, 0, 0))."</option>";
				}
				?>
				</select>
				</td>
				<td>
				<select name="yearSelected">
				<?php
				for($yearIter = (date('Y') - 10); $yearIter<=(date('Y')+10); $yearIter++)
				{
					echo "<option value=".$yearIter;
					if($yearSelected == $yearIter)
					{
						echo " SELECTED";
					}
					echo ">".$yearIter."</option>";
				}
				?>
				</select>
				</td>
				<td>
				<input type="submit" name="selectDevice" value="Select Device Calendar" class="grey">
				<?php
				if($_SESSION['usertype']==1)
				{
					echo "<input type=\"submit\" name=\"exportToExcel\" value=\"To Excel\" class=\"grey\">";
					echo "<input type=\"submit\" name=\"filterTraining\" value=\"Filter Training\" class=\"grey\">";
				}

				?>
				</td>
			</tr>
			</table>
		
		<?php
		echo $calendar->calendar($monthSelected,$yearSelected);
		?>
		</td>
	</tr>
</table>
</form>
</div>
<br><br><br>
