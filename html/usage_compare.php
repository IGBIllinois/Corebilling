<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}
$device = new Device($db);
$start_date = new DateTime(date('Y-m-d') . " 00:00:00");
$end_date = new DateTime(date('Y-m-d') . " 23:59:59");
$default_device = 43; // Load the NMR by default TODO find a better way to grab the default

if (isset ($_GET['device'])) {
	$device->load($_GET['device']);
} else {
	$device->load($default_device);
}

if(isset($_GET['start_date'])){
	$start_date = DateTime::createFromFormat("Y-m-d H:i:s",$_GET['start_date'] . " 00:00:00");
}
if(isset($_GET['end_date'])) {
	$end_date = DateTime::createFromFormat("Y-m-d H:i:s",$_GET['end_date'] . " 00:00:00");
}
$reservations = Reservation::getEventsInRange($db,$start_date->format("Y-m-d H:i:s"), $end_date->format("Y-m-d H:i:s"), null, $device->getId(), false);
$sessions = Session::getSessions($db, $start_date, $end_date,$device->getId());
?>

	<h3>Usage Comparison<?php if($device->getId()!=0){echo " - ".$device->getFullName();} ?> - <?php echo $start_date->format("m/d/Y"); ?> - <?php echo $end_date->format("m/d/Y"); ?></h3>
<div class="well">
	<form method="GET" name="calform" class="form-inline">
		<div class="form-group">
			<select name="device" class="form-control" onChange='document.calform.submit();'>
				<?php
				$deviceList = Device::getAllDevices($db);
				foreach ($deviceList as $id => $availDevices) {
					if (($availDevices['status_id']==Device::STATUS_ONLINE || $availDevices['status_id']==Device::STATUS_DONOTTRACK)) {
						echo "<option value=" . $availDevices ['id'];
						if ($availDevices['id'] == $device->getId()) {
							echo " selected";
						}
						echo ">" . $availDevices ['full_device_name'] . "</option>";
					}
				}
				?>
			</select>
		</div>
		<div class="form-group">
			<input type="text" id="start_date" name="start_date" class="form-control" onChange='document.calform.submit();' value="<?php echo $start_date->format("Y-m-d"); ?>" />
			<input type="text" id="end_date" name="end_date" class="form-control" onChange='document.calform.submit();' value="<?php echo $end_date->format("Y-m-d"); ?>" />
		</div>
	</form>
</div>
<div class="row">
	<div class="col-xs-3 col-sm-2 col-lg-1">
		<div style="height: 100px"><h4>Reservations</h4></div>
		<div style="height: 100px"><h4>Usage</h4></div>
	</div>
	<div class="col-xs-9 col-sm-10 col-lg-11">
		<div class="comprow" id="resrow">
			<?php
			for($i = 0; $i<count($reservations); $i++){
				$start = strtotime($reservations[$i]['starttime']);
				$stop = strtotime($reservations[$i]['stoptime']);
				$startofday = strtotime($start_date->format("Y-m-d")." 00:00:00");
				$width = ($stop - $start) / (24*60*60) * 100;
				$left = ($start - $startofday) / (24*60*60) * 100;
				$startstr = DateTime::createFromFormat("Y-m-d H:i:s",$reservations[$i]['starttime'])->format("g:i a");
				$stopstr = DateTime::createFromFormat("Y-m-d H:i:s",$reservations[$i]['stoptime'])->format("g:i a");
				$username = $reservations[$i]['user_name'];
				$group = $reservations[$i]['group_name'];
				
				echo "<div class='compcell' style='width: $width%; left: $left%' data-container='body' data-toggle='popover' data-placement='top' title='$username' data-content='$startstr - $stopstr<br/>Group: $group'>$username<br/>$startstr - $stopstr<br/>Group: $group</div>";
			}
			?>
		</div>
		<div class="comprow" id="sessrow">
			<?php
			for($i = 0; $i<count($sessions); $i++){
				$start = strtotime($sessions[$i]['start']);
				$stop = strtotime($sessions[$i]['stop']);
				$startofday = strtotime($start_date->format("Y-m-d H:i:s"));
				$endofday = strtotime($end_date->format("Y-m-d H:i:s"));
				$right = ($endofday - $stop) / (24*60*60) * 100;
				$left = ($start - $startofday) / (24*60*60) * 100;
				$style = "";
				if($right < 0){
					$right = 0;
					$style .= " border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;";
				}
				if($left < 0){
					$left = 0;
					$style .= " border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;";
				}
				$startstr = DateTime::createFromFormat("Y-m-d H:i:s",$sessions[$i]['start'])->format("g:i a");
				$stopstr = DateTime::createFromFormat("Y-m-d H:i:s",$sessions[$i]['stop'])->format("g:i a");
				$username = $sessions[$i]['user_name'];
				$group = $sessions[$i]['group_name'];
				echo "<div class='compcell' style='right: $right%; left: $left%;$style' data-container='body' data-toggle='popover' data-placement='bottom' title='$username' data-content='$startstr - $stopstr<br/>Group: $group'>$username<br/>$startstr - $stopstr<br/>Group: $group</div>";
			}
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('[data-toggle="popover"]').popover({html:true});
		$('select').select2({'width':'element'});
		start_date = $('#start_date').datepicker({
			dateFormat: "yy-mm-dd"
		}).datepicker("setDate",$('#start_date').val());
		end_date = $('#end_date').datepicker({
			defaultDate: "+1w",
			dateFormat: "yy-mm-dd",
		}).datepicker("setDate",$('#end_date').val());
	});
</script>
<?php
	require_once 'includes/footer.inc.php';
	?>
