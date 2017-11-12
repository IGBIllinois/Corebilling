<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('includes/header.html');
include('includes/browsercheck.php');
?>
	<table class="cont">
		<tr>
			<td>		
				<div id="sub_menu_title">
					<center><h3>Home</h3></center>
				</div>
				<div id="sub_nav">
					<div id="sub_nav_button">
						<a href="./index.php?subm=1"><h5>Latest News</h5></a>
					</div>
					<div id="sub_nav_button">
						<a href="./index.php?subm=3"><h5>Calendar</h5></a>
					</div>
					<div id="sub_nav_button">
                                                <a href="./index.php?subm=4"><h5>Account Info</h5></a>
                                        </div>
				</div>

			</td>
		  <td>
			  <div id="content">
			  <?php
				
					$submenu= 1;
					if (isset($_GET['subm']))	{
						$submenu =$_GET['subm'];
						}
					if(isset($_SESSION['usertype']))
					{
						switch ($submenu) {
							case '1':
								include 'news_new.php';
								break;
							case '3':
								include 'calendar_new.php';
								break;
							case '4':
                                				include 'user_account_beta.php';
                                			break;
						}
					}
					elseif(isset($_SESSION['newuser']))
					{
						if($_SESSION['newuser'])
                                        	{
                                                        include 'user_account_beta.php';
                                        	}
						else
						{
							include "./denied.php";	
						}
					}
				?>
			  </div>
			</td>
		</tr>
	</table>
<?php
include('includes/footer.html')
?>	
