<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	
	require_once('main.inc.php');
	
	require_once 'authenticate.inc.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset='utf-8'>
                <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

		<link rel='stylesheet' type='text/css' href='vendor/twbs/bootstrap/dist/css/bootstrap.min.css'>
			
		<link href="css/fullcalendar.css" rel='stylesheet' />
		<link href="css/fullcalendar.print.css" rel='stylesheet' media='print' />
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/jszip-2.5.0/pdfmake-0.1.18/dt-1.10.12/b-1.2.1/b-colvis-1.2.1/b-html5-1.2.1/b-print-1.2.1/r-2.1.0/se-1.2.0/datatables.min.css"/>
		<link rel="stylesheet" href="includes/select2/css/select2.css" type="text/css" />
		<link rel="stylesheet" href="includes/select2/css/select2-bootstrap.css" type="text/css" />
		<link href="css/jquery.timepicker.css" rel="stylesheet" />
		<link href="css/jquery-ui.css" rel="stylesheet" />
		<link href="css/jquery-ui.theme.css" rel="stylesheet" />
		<link href="css/main.inc.css" rel="stylesheet" />
	
		<script type='text/javascript' src='vendor/components/jquery/jquery.min.js'></script>
		<script type='text/javascript' src='vendor/components/jqueryui/jquery-ui.min.js'></script>
		<script type="text/javascript" src="includes/select2/js/select2.full.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/v/bs/jszip-2.5.0/pdfmake-0.1.18/dt-1.10.12/b-1.2.1/b-colvis-1.2.1/b-html5-1.2.1/b-print-1.2.1/r-2.1.0/se-1.2.0/datatables.min.js"></script>
		<script type="text/javascript" src="js/jquery.timepicker.js"></script>
		<script src='js/jquery/fnDisplayRow.js' type="text/javascript"></script>
		<script src='js/full_calendar/moment.min.js' type="text/javascript"></script>
		<script src='js/full_calendar/fullcalendar.min.js' type="text/javascript"></script>
		<script type='text/javascript' src='vendor/twbs/bootstrap/dist/js/bootstrap.min.js'></script>
		<script src='js/highcharts/highcharts.js' type="text/javascript"></script>
		<script src='js/highcharts/exporting.js' type="text/javascript"></script>
		<script src='js/main.inc.js' type="text/javascript"></script>
	
		<title><?php echo settings::get_title(); ?></title>
	</head>
	<body>
	
		<nav class="navbar navbar-inverse navbar-static-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#archive-accounting-nav-collapse" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<div class="navbar-brand">
						<?php echo settings::get_title(); ?>
					</div>
<!--
					<?php if ($login_user->isAdmin()) { ?>
					<p class="navbar-text">
						Last activity: <?php echo strftime("%m/%d/%Y %H:%M:%S",$authenticate->lastActivity); ?>, session method: <?php echo $authenticate->sessMethod; ?>
					</p>
					<?php } ?>
-->
				</div>
				<div class="collapse navbar-collapse" id="archive-accounting-nav-collapse">
					<a type="button" class="btn btn-danger btn-sm navbar-btn navbar-right hidden-xs" style="margin-right:0" href="logout.php">Logout</a>
					<a type="button" class="btn btn-danger btn-sm btn-block visible-xs" style="margin-bottom:7px" href="logout.php">Logout</a>
				</div>
			</div>
		</nav>
		
		<div class="container-fluid">
			<div class="row">
				<?php if ($authenticate->isVerified()) { ?>
	                        <div class="col-2 col-md-2">
					<ul class="nav nav-pills nav-stacked">
                                        	<li><a href="index.php">News</a></li>
	                                        <li><a href="user_billing.php">User Bill</a></li>
        	                                <li><a href="calendar_fullcalendar.php">Calendar</a>
                	                        <li><a href="in_use.php">Device Status</a></li>
                        	                <?php if ($login_user->isAdmin()){ ?>

                                	        <hr>
                                        	<li><a href="list_users.php">Users</a></li>
	                                        <li><a href="edit_groups.php">Groups</a></li>
        	                                <li><a href="edit_departments.php">Departments</a></li>
                	                        <li><a href="edit_devices.php">Devices</a></li>
                        	                <hr>
                                	        <li><a href="facility_billing.php">Facility Billing</a></li>
                                        	<li><a href="facility_demographics.php">Facility Demographics</a></li>
	                                        <li><a href="active_users.php">Active Users</a></li>
        	                                <li><a href="reservation_use.php">Reservation Usage Data</a></li>
                	                        <li><a href="usage_compare.php">Reservation-Usage Comparison</a></li>
						<li><a href='logs.php'>View Logs</a></li>
                        	                <li><a href='about.php'>About</a></li>
						<?php } ?>
                                 	</ul>
                        	</div>
				<?php } ?>

				<div class="col-10 col-md-10">
