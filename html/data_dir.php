<?php
require_once 'includes/header.inc.php';

if(!$login_user->isAdmin()){
        echo html::error_message("You do not have permission to view this page.","403 Forbidden");
        require_once 'includes/footer.inc.php';
        exit;
}


if (isset($_GET['data_dir_id']) && (is_numeric($_GET['data_dir_id']))) {
	$data_dir_id = $_GET['data_dir_id'];
}
$message = "";
$data_dir = new data_dir($db,$data_dir_id);

require_once 'includes/header.inc.php';

?>


<h3>Data Directory - <?php echo $data_dir->get_directory(); ?></h3>

<div class='row span6'>
<table class='table table-bordered table-condensed'>
<tr>
	<td>Directory</td>
	<td><?php echo $data_dir->get_directory(); ?></td>
</tr>
<tr>
	<td>Enabled</td>
	<td><?php
	if ($data_dir->get_enabled()) {
		echo "<span class='glyphicon glyphicon-ok'></span>";
	}
	else {
		echo "<span class='glyphicon glyphicon-remove'></span>";
	}
	?>
	</td>
</tr>
<tr>
	<td>Time Added</td>
	<td><?php echo $data_dir->get_time_created(); ?></td>
</tr>
<tr>
	<td>Currently Exists</td>
	<td>
	<?php
	if ($data_dir->get_dir_exists()) {
		echo "<span class='glyphicon glyphicon-ok'></span>";
	}
	else {
        	echo "<span class='glyphicon glyphicon-remove'></span>";
        }
	?>
	</td>
</tr>
<tr>
	<td>Group</td>
	<td><?php $data_dir->get_group(); ?></td>
</tr>

</table>
</div>
<div class='row span6'>
<?php

if (isset($message)) { echo $message; } 
?>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
