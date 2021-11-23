<?php
	
require_once('includes/main.inc.php');
require_once 'includes/authenticate.inc.php';

if (!$login_user->isAdmin()) {
    exit;
}

header('content-type: application/json');
	
if(isset($_REQUEST['uid'])) {
	echo json_encode($ldapman->getUser($_REQUEST['uid']));
}

?>
