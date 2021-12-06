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
	$filter = "(uid=" . $_POST['uid'] . ")";
	$attributes = array('givenName','sn','mail');
	$ou = settings::get_ldap_base_dn();
	$result = $ldap->search($filter,$ou,$attributes);
	if ($result['count']) {
		$json_result = json_encode(array('givenName'=>$result[0]['givenname'][0],
				'sn'=>$result[0]['sn'][0],
				'mail'=>$result[0]['mail'][0]
			));
	}
}
echo $json_result;

?>
