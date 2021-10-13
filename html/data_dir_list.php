<?php
require_once 'includes/header.inc.php';

if (isset($_GET['start']) && is_numeric($_GET['start'])) {
	$start = $_GET['start'];
}
else { $start = 0;
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

		$dir_html .= "<td>" . $directory['group_name'] . "</a></td>";
		$dir_html .= "<td><a href='edit_users.php?user_id=" . $directory['owner_id'] . "'>" . $directory['owner'] . "</a></td>";
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
			<th>Time Created</th>
		</tr>
	</thead>
	<tbody>
		<?php echo $dir_html; ?>
	</tbody>
</table>
</div>
</div>
<div class='col-xs-6 col-6 col-sm-6 col-lg-6 col-xs-offset-3 col-offset-3 col-sm-offset-3 col-lg-offset-3'>
<div class='row'>
<?php echo $pages_html; ?>
</div>
</div>
<?php
require_once 'includes/footer.inc.php';
?>
