<?php
	require_once('includes/initializer.php');
	$authenticate->Logout();
    
	header('location:login.php');
?>
