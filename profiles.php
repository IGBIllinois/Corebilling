<?php
include('includes/header.html');
?>
<script src="js/sorttable.js"></script>

<table class="cont">
	<tr>
		<td>
		<div id="sub_menu_title">
		<center>
		<h3>Profiles</h3>
		</center>
		</div>
		<div id="sub_nav">
		<div id="sub_nav_button"><a href="profiles.php?subm=1">
		<h5>User Profiles</h5>
		</a></div>
		</div>
		</td>
		<td>
		<div id="content"><?php
		$submenu= 1;
		$submenu =$_GET['subm'];
		switch ($submenu) {
			case '1':
				include 'user_profiles.php';
				break;
			case '2':
				include 'device_profiles.php';
				break;
		}
		?></div>
		</td>
	</tr>
</table>
		<?php
		include('includes/footer.html')

		?>
