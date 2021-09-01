<?php
	require_once('includes/main.inc.php');
	$authenticate->Logout();
    
	header('location:login.php');
?>
