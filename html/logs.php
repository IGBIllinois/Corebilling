<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
        echo html::error_message("You do not have permission to view this page.","403 Forbidden");
        require_once 'includes/footer.inc.php';
        exit;
}
$log_name = 'corebilling.log';
$log_contents = "";
if (isset($_POST['select_log'])) {
	$log_name = $_POST['log_name'];
}

if ($log_name == 'corebilling.log') {
	$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
	$log_contents = $log->get_log();
}
if ($log_name == 'device.log') {
	$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_device_log());
	$log_contents = $log->get_log();
}

?>
<h3>Logs</h3>
<form class='form-inline' method='post' name='log' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<div class='form-group'>
	<select class='form-control' name='log_name' id='log_name'>
		<option value='corebilling.log' <?php if ($log_name == "corebilling.log") echo "selected='selected'"; ?>>corebilling.log</option>
		<option value='device.log' <?php if ($log_name == "device.log") echo "selected='selected'"; ?>>device.log</option>
	</select>
</div>
<input class='btn btn-primary' type='submit' name='select_log' value='Select'>
</form>
<br>
<textarea class='form-control' rows='50' readonly><?php echo $log_contents; ?></textarea>

<?php
require_once 'includes/footer.inc.php';

?>
