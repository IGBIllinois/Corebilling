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
		$dir_html .= "<td></td>";
		$dir_html .= "<td>" . $directory['group_name'] . "</a></td>";
		$dir_html .= "<td>" . $directory['owner'] . "</td>";
		$dir_html .= "<td>" .  $directory['data_dir_time'] . "</td>";
		$dir_html .= "</tr>";
	}

}
else {
	$dir_html = "<tr><td colspan='4'>No Directories</td></tr>";
}
?>
<h3>List of Directories</h3>
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
<?php echo $pages_html; ?>


<?php
if (isset($message)) { echo $message; }
require_once 'includes/footer.inc.php';
?>
