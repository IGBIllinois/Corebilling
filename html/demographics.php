<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$user_demographics = UserDemographics::getDemographics($db);
?>
	<h3>Demographics</h3>
	<?php
		echo VisualizeData::ListSessionsTableHiddenCols(
			$user_demographics,
			array('NetId', 'Email','First Name','Last Name', 'Edu. Level', 'Gender', 'Underrep.'),
			array('user_name', 'email', 'first', 'last', 'edu_level', 'gender', 'underrepresented'),
			array(),
			array('NetId', 'Email', 'First Name', 'Last Name', 'Edu. Level', 'Gender', 'Underrep.'),
			'demographics');
	?>
	</div>
	</div>

<?php
require_once 'includes/footer.inc.php';
?>
