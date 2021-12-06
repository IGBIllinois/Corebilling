<?php
ob_start();	
require_once('includes/main.inc.php');
require_once 'includes/authenticate.inc.php';
ob_clean();


$json_result = json_encode(array('givenName'=>null,'sn'=>null,'mail'=>null));
header('content-type: application/json');
if (!isset($_POST['login_session_id']) || $login_session->get_session_id() != $_POST['login_session_id']) {
	echo $json_result;
	exit;
}
if (!$login_user->isAdmin()) {
	echo $json_result;
	exit;
}
elseif(isset($_POST['uid'])) {
	foreach ($_POST as $var) {
		$var = trim(rtrim($var));
	}
	$result = User::get_ldap_info($ldap,$_POST['uid']);
	if (count($result)) {
		$json_result = json_encode($result);
	}
}
echo $json_result;

?>
