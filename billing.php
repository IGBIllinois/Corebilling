<?php
include('includes/header.html');
?>
	<table class="cont">
		<tr>
			<td>		
				<div id="sub_menu_title">
					<center><h3>Billing</h3></center>
				</div>
				<div id="sub_nav" class="nav">
					<div id="sub_nav_button">
						<a href="./billing.php?subm=1"><h5>Billing Users/Groups</h5></a>
					</div>
				</div>
			</td>
		  <td>
			  <div id="content">
			  <?php
					$submenu= 1;
					$submenu =$_GET['subm'];
					switch ($submenu) {
					case '1':
						include 'billing_ug.php';
						break;
					case '2':
						include 'billing_conf.php';
						break;
					}
				?>
			  </div>
			</td>
		</tr>
	</table>
<?php
include('includes/footer.html')

?>
