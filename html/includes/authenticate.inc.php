<?php

$login_session = new \IGBIllinois\session(settings::get_session_name());
if (!$authenticate->VerifySession($login_session)) {
	header('Location: login.php');
}

$login_user = $authenticate->getAuthenticatedUser();

