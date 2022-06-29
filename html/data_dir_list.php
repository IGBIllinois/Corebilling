<?php
require_once 'includes/header.inc.php';

$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
	$start = $_GET['start'];
}

$num_dirs = data_functions::get_num_directories($db);
$count = 30;
$pages_url = $_SERVER['PHP_SELF'];
$pages_html = html::get_pages_html($pages_url,$num_dirs,$start,$count);

$directories = data_functions::get_directories($db,$start,$count);
$dir_html = "";
if (count($directories)) {
	foreach ($directories as $directory) {
		$dir_html .= "<tr>";
		$dir_html .= "<td><a href='data_dir.php?data_dir_id=" . $directory['data_dir_id'] . "'>" . $directory['data_dir_path'] . "</a></td>";
		if ($directory['data_dir_exists']) {
	                $dir_html .= "<td><span class='glyphicon glyphicon-ok'></span></td>";
        	}
        	else {
                	$dir_html .= "<td><span class='glyphicon glyphicon-remove'></span></td>";
        	}

		$dir_html .= "<td><a href='edit_groups.php?group_id=" . $directory['group_id'] . "'>" . $directory['group_name'] . "</a></td>";
		$dir_html .= "<td><a href='edit_users.php?user_id=" . $directory['owner_id'] . "'>" . $directory['owner'] . "</a></td>";
		$dir_html .= "<td>" . $directory['cfop'] . "</td>";
		$dir_html .= "<td>" . $directory['terabytes'] . "</td>";
		$dir_html .= "<td>" .  $directory['data_dir_time'] . "</td>";
		$dir_html .= "</tr>";
	}

}
else {
	$dir_html = "<tr><td colspan='4'>No Directories</td></tr>";
}
?>
<h3>List of Directories</h3>
<div class='col-xs-10 col-10 col-sm-10 col-lg-10'>
<div class='row'>
<table class='table table-striped table-condensed table-bordered'>
	<thead>
		<tr>
			<th>Directory</th>
			<th>Currently Exists</th>
			<th>Group</th>
			<th>Owner</th>
			<th>CFOP</th>
			<th>Terabytes</th>
			<th>Time Created</th>
		</tr>
	</thead>
	<tbody>
		<?php echo $dir_html; ?>
	</tbody>
</table>
</div>
<form class='form-inline' method='post' action='report.php'>
	<select class='form-control' name='report_type'>
		<option value='xlsx'>Excel (.xlsx)</option>
		<option value='csv'>CSV (.csv)</option>
	</select>
	<input class='btn btn-primary' type='submit'
	name='create_data_dir_report' value='Download Directory List'>
</form>
</div>
<div class='col-xs-6 col-6 col-sm-6 col-lg-6 col-xs-offset-3 col-offset-3 col-sm-offset-3 col-lg-offset-3'>
<div class='row'>
<?php echo $pages_html; ?>
</div>
</div>
<?php
require_once 'includes/footer.inc.php';
?>
