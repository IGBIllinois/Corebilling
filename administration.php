<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('includes/header.html');
?>	
	<TABLE class="cont">
		<tr>
			<td>		
				<div id="sub_menu_title">
					<center><h3>Administration</h3></center>
				</div>
				<div id="sub_nav">
					<div id="sub_nav_button">
						<a href="./administration.php?subm=3"><h5>Edit Groups</h5></a>
					</div>
					<div id="sub_nav_button">
                                                <a href="./administration.php?subm=12"><h5>Edit Users</h5></a>
                                        </div>
					<div id="sub_nav_button">
                                                <a href="./administration.php?subm=11"><h5>Edit Devices</h5></a>
                                        </div>
					<div id="sub_nav_button">
                                                <a href="./administration.php?subm=15"><h5>Edit Permissions</h5></a>
                                        </div>
					<div id="sub_nav_button">
						<a href="./administration.php?subm=4"><h5>User/Device Logs</h5></a>
					</div>
					<div id="sub_nav_button">
						<a href="./administration.php?subm=5"><h5>Devices In Use</h5></a>
					<div id="sub_nav_button">
						<a href="./administration.php?subm=7"><h5>Statistics</h5></a>
					</div>
					<div id="sub_nav_button">
                                                <a href="./administration.php?subm=13"><h5>Facility Billing</h5></a>
                                        </div>


				</div>
			</td>
		  <td>
			  <div id="content">
				<?php
					if($_SESSION['usertype']==1)
					{
						$submenu= 1;
						$submenu =$_GET['subm'];
						switch ($submenu) {
						case '1':
							include 'edit_users.php';
							break;
						case '2':
							include 'edit_devices.php';
							break;
						case '3':
							include 'edit_groups.php';
							break;
						case '4':
							include 'user_sessions.php';
							break;
						case '5':
							include 'in_use.php';
							break;						
						case '7':
							include 'dev_statistics.php';
							break;
						case '8':
							include 'facility_billing.php';
							break;
						case '9':
							include 'logon_txt.php';
							break;
						case '10':
							include 'line_graphs.php';
							break;
						case '11':
							include 'edit_device_new.php';
							break;
						case '12':
							include 'edit_users_new.php';
							break;
						case '13':
							include "facility_billing_new.php";
							break;
						case '14':
							include "user_sessions_new.php";
							break;
						case '15':
                                                        include "edit_permissions.php";
                                                        break;
						}
					}
					else
					{
						echo "<br><br><center><h4>You Do Not Have Permission To View This Page...</h4></center>";
					}
				?>
			  </div>
			</td>
		</tr>
	</table>
<?php
include('includes/footer.html')

?>	
