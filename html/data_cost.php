<?php
require_once 'includes/header.inc.php';

if(!$login_user->isAdmin()){
        echo html::error_message("You do not have permission to view this page.","403 Forbidden");
        require_once 'includes/footer.inc.php';
        exit;
}

$message = "";
if (isset($_POST['update_cost'])) {
	$data_cost = new data_cost($db);
	$result = $data_cost->update_cost($_POST['cost']);
	if (!$result) {
		$message .= "<div class='alert alert-danger'>Error Updating Cost</div>";
	}

}

$data_costs = data_cost::get_data_costs($db);
$data_costs_html = "";
if (count($data_costs)) {
	foreach ($data_costs as $data_cost) {
		$data_costs_html .= "<tr><td>" . $data_cost['cost'] . "</td><td>" . $data_cost['time_created'] . "</td></tr>";

	}
}
else {
		$data_costs_html = "<tr><td colspan='2'>No Data Costs Set</td></tr>";
}
?>
<h3>Data Cost</h3>
<form class='form' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<table class='table table-bordered table-striped table-sm'>
<thead><th>Cost (per TB)</th><th>Time Set</th></thead>
<?php echo $data_costs_html; ?>

	<tr>
		<td><div class='input-group'>
			<span class="input-group-addon"><span class='glyphicon glyphicon-usd'></span></span>
			<input class='form-control' type='text' name='cost' placeholder='0.00'></div></td>
		<td><input class='btn btn-primary' type='submit' name='update_cost' value='Update Cost'></td>
	</tr>
</table>
</form>
<br>
<?php
if (isset($message)) {
	echo $message;
}
require_once 'includes/footer.inc.php';
?>
