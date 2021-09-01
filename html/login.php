<?php
	require_once 'includes/main.inc.php';

	if(isset($_POST['login'])) {
		$username = trim(rtrim($_POST['user_name']));
		$password = $_POST['password'];

		$loginsuccess = $authenticate->Login($username,$password);
		if( $loginsuccess ){
			header('Location: index.php');
		} else {
			$message = html::error_message('Incorrect username or password.');
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel='stylesheet' types='text/css' href='vendor/twbs/bootstrap/dist/css/bootstrap.min.css'>
		
		<script src='js/jquery/jquery-1.11.1.min.js'></script>
		<script type='text/javascript' src='vendor/twbs/bootstrap/dist/js/bootstrap.min.js'></script>
			
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title><?php echo settings::get_title(); ?></title>
	</head>
	<body OnLoad="document.login.user_name.focus();">
		<nav class="navbar navbar-inverse navbar-static-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<div class="navbar-brand">
						<?php echo settings::get_title(); ?>
					</div>
				</div>
			</div>
		</nav>
	
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<form role="form" class="form-signin" action="./login.php" method="POST"  style="margin-bottom:1em" name='login'>
						<div class="form-group">
							<label for="username">Username: </label>
							<div class="input-group">
								<input class='form-control' type='text' name='user_name' id="username" tabindex='1' placeholder='Username' value='<?php if (isset($username)) { echo $username; } ?>' autocapitalize='off'> 
								<span class="input-group-addon"><span class='glyphicon glyphicon-user'></span></span>
							</div>
						</div>
						<div class="form-group">
							<label>Password: </label>
							<div class="input-group">
								<input class='form-control' type='password' name='password' placeholder='Password' tabindex='2'>
								<span class="input-group-addon"><span class='glyphicon glyphicon-lock'></span></span>
							</div>
						</div>
						<button type='submit' name='login' class='btn btn-primary'>Login</button>
					</form>
					<?php if (isset($message)) { 
						echo $message;
					} ?>
					<div style="text-align: center;">
						<a href="http://illinoisauth.igb.illinois.edu/password">Forgot password?</a>
					</div>
				</div>
			</div>
<?php require_once 'includes/footer.inc.php'; ?>
