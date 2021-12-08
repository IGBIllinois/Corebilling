<?php

if (isset($_GET['download_linux'])) {
	$filename = 'CoreBillingService.pl';
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
	readfile('downloads/' . $filename);
	exit();

}
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
        echo html::error_message("You do not have permission to view this page.","403 Forbidden");
        require_once 'includes/footer.inc.php';
        exit;
}
?>
<h3>Client Downloads</h3>
<div class='row'>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-body">
				<table class="table table-striped table-hover table-bordered table-condensed">
					<tbody>
						<tr>
							<td>Windows</td>
							<td><a target='_blank' href='<?php echo settings::get_corebillingservice_url(); ?>'><?php echo settings::get_corebillingservice_url(); ?></a></td>
						</tr>
						<tr><td>Linux</td><td><a href='<?php echo $_SERVER['PHP_SELF']; ?>?download_linux'>CoreBillingService.pl</a></td></tr>

					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>

<?php
require_once 'includes/footer.inc.php';

?>
