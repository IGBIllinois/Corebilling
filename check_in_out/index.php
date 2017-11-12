<?php
include('../includes/mysql_connect.php');
include('header.html');
?>	
<script language="JavaScript">
function $(id) {
  return document.getElementById(id);
}
</script>
<br>
	<table width=1024 cellspacing="10" cellpadding="5" class="cont">
		<tr><td>
		<br><br>	
		<div class="roundcont">
		<div class="roundtop"> <img src="../imgs/tl.gif" alt="" width="15" height="15" class="corner" style="display: none" /> </div>
		<div id="uform">
		<div id="container">
                       
                        <?php
			if(isset($_POST['selectCheckout'])) {
			echo "<center><font size=\"5\"><b>Check Out</b></font></center>";
			echo "<table cellspacing=\"20\">";
			$sessionID=$_POST['sessionID'];
			$query_sessioninfo=mysql_query("SELECT d.devicename, s.start, TIMEDIFF(NOW(),s.start), s.description FROM session s, device d WHERE s.deviceid=d.ID AND s.ID=$sessionID");
			$session_data=mysql_fetch_array($query_sessioninfo);
                        echo "<form action=\"./index.php\" method=POST>";
                        echo "<tr><td>User: </td><td><input name=\"login_name\" type=\"text\"  size=\"12\" maxlength=\"20\" height=\"12\" ></td></tr>";
                        echo "<tr><td>Password: </td><td> <input name=\"pwd\" type=\"password\" size=\"12\" maxlength=\"20\" height=\"12\" ></td></tr>";
                        echo "<tr><td>Instrument: </td><td>".$session_data[0]."</td></tr>";
			echo "<tr><td>Start Time: </td><td>".date("M jS Y G:i",strtotime(($session_data[1])))."</td></tr>";
			echo "<tr><td>Usage: </td><td>".$session_data[2]."</td></tr>";	
                        echo "</select></td></tr>";
                        echo "<tr><td>Description</td><td><TEXTAREA name=\"description\" cols=20 rows=3>".$session_data[3]."</TEXTAREA></td></tr>";
                        echo "<tr><td></td><td><input type=\"hidden\" name=\"sessionID\" value=\"".$sessionID."\"><input class=\"grey\" type=\"submit\" name=\"submitCheckout\" value=\"Check Out\"><br><br></form><form action=\"./index.php\"><input class=\"grey\" type=\"submit\" name=\"cancelCheckout\" value=\"Cancel Checkout\"></td></tr>";
                        echo "</form>";
			}
			else {
			echo "<center><font size=\"5\"><b>Check In</b></font></center>";
			echo "<table cellspacing=\"20\">";
                        echo "<form action=\"./index.php\" method=POST>";
                        echo "<tr><td>User: </td><td><input name=\"login_name\" type=\"text\"  size=\"12\" maxlength=\"20\" height=\"12\" ></td></tr>";
                        echo "<tr><td>Password: </td><td> <input name=\"pwd\" type=\"password\" size=\"12\" maxlength=\"20\" height=\"12\" ></td></tr>";
			echo "<tr><td>Instrument: </td><td>";
			$query_available_devices=mysql_query("select distinct device.ID, device.devicename, device.scripted from device left join session on device.ID=session.deviceid where session.status != 1 or session.status is null");
			echo "<select name=\"selectDevice\">";
			while($row=mysql_fetch_array($query_available_devices)) {
				if($row[2]!=1) {
					echo "<option value=\"".$row[0]."\">".$row[1]."</option>";
				}
			}
			echo "</select></td></tr>";
			echo "<tr><td>Description</td><td><TEXTAREA name=\"description\" cols=20 rows=3></TEXTAREA></td></tr>";
			echo "<tr><td></td><td><input class=\"grey\" type=\"submit\" name=\"submitCheckin\" value=\"Check In\"></td></tr>";
			echo "</form>";
			}
                        ?>
                     
		</table>	
		<div class="roundbottom">
      		<img src="../imgs/bl.gif" alt="" width="15" height="15" class="corner" style="display: none" />
   		</div>
		</div>	
		<?php
		if(isset($_SESSION['user_name'])) {
		?>
		
		<?
		}
		?>
		</td><td>
		<!-- <div class="roundcont_reserv">
                <div class="roundtop"> <img src="../imgs/tl.gif" alt="" width="15" height="15" class="corner" style="display: none" /> </div>
                <div id="uform">
                <div id="container">
                <center><font size="5"><b>Instruments In Use</b></font></center> -->
		<center><h4>Instruments In Use</h4></center><br>
		<script type="text/javascript" src="../js/wz_tooltip.js"></script>	
		<TABLE class="checkInOut" width="100%" cellspacing="1" cellpadding="3" >
		<tr class="title">
		<td>
        		Device Name
		</td>
		<td>
        		User Name
		</td>
		<td>
        		CheckIn Time
		</td>
		<td>
		        Description
		</td>
		<td>
			Check Out
		</td>
		</tr>
	
		<?php
		$query_in_use=mysql_query("SELECT d.devicename, u.first, u.last,  s.deviceid, s.ID, s.start, s.description, s.status, d.scripted FROM session s, device d, users u WHERE s.status=1 AND d.ID=s.deviceid AND u.ID=s.userid");
		$i=0;
		if(mysql_num_rows($query_in_use)>0) {
			while($row=mysql_fetch_array($query_in_use)) {
				echo "<form action=\"./index.php\" method=POST><tr class=\"larged".($i%2)."\"><td>".$row[0]."</td>";
				echo "<td>".$row[1]." ".$row[2]."</td>";
				echo "<td>".date("M jS Y G:i",strtotime(($row[5])))."</td>";
				echo "<td>".$row[6]."</td>";
				echo "<td align=\"center\">";
				if($row[8]!=1) {	
					echo "<input class=\"grey\" type=\"submit\" name=\"selectCheckout\" value=\"Check Out\">";
					echo "<input type=\"hidden\" name=\"sessionID\" value=\"".$row[4]."\">";
				}
				echo "</td></tr></form>";
				$i++;
			}
		}
		else {
			echo "No Instruments In Use";
		}
		?>
		<!-- <div class="roundbottom">
                <img src="../imgs/bl.gif" alt="" width="15" height="15" class="corner" style="display: none" />
                </div>
                </div >-->
		</table>
		</td></tr>
		</table>
		 <center><h4>Today's Reservations</h4></center><br> 
		<?php	
		echo "<center><img src=\"http://core.igb.uiuc.edu/coreapp/graphs/graph_device_reserv.php\"></center>";
		?>

<br>
	
