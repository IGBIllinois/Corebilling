<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (!isset($_SERVER['PHP_AUTH_USER']) || (!isset($_SERVER['PHP_AUTH_PW']))) {
	http_response_code(401);
	exit;
}

require_once(__DIR__ . '/../../includes/main.inc.php');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$valid_nouns = array('session','ldapuser');

$verb = $_SERVER['REQUEST_METHOD'];

switch ($verb) {
	case 'OPTIONS':
		break;
	case 'GET':
		break;
	case 'POST':
		break;
	case 'DELETE':
		break;
	default:
		break;
}

$json = json_decode(file_get_contents('php://input'));
echo json_encode($_SERVER,JSON_PRETTY_PRINT);
?>

