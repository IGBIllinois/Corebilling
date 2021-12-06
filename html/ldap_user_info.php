<?php
	
require_once('includes/main.inc.php');
require_once 'includes/authenticate.inc.php';

if (!$login_user->isAdmin()) {
    exit;
}

header('content-type: application/json');
	
if(isset($_POST['uid'])) {
	foreach ($_POST as $var) {
		$var = trim(rtrim($var));
	}
	$filter = "(uid=" . $_POST['uid'] . ")";
	$attributes = array('givenName','sn','mail');
	$ou = settings::get_ldap_base_dn();
	$result = $ldap->search($filter,$ou,$attributes);
	$json_result = array('givenName'=>null,'sn'=>null,'mail'=>null);
	if ($result['count']) {
		$json_result = array('givenName'=>$result[0]['givenname'][0],
				'sn'=>$result[0]['sn'][0],
				'mail'=>$result[0]['mail'][0]
			);
	}
	echo json_encode($json_result);
}

?>
