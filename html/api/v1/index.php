<?php

ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__ . '/../../includes/main.inc.php');

$response_code = restapi::RESPONSE_SUCCESS;
$message = "";

$verb = $_SERVER['REQUEST_METHOD'];
$json = json_decode('{}');

if ($verb != 'GET')  {
	$json = json_decode(file_get_contents('php://input'));
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$noun = "";
$index = "";
for ($i=0; $i<count($uri); $i++) {
	if ($uri[$i] === 'index.php') {
		$noun = $uri[$i+1];
		$index = $uri[$i+2];
		break;
	}
}

//No Basic Authentication sent
if (!isset($_SERVER['PHP_AUTH_PW'])) {
        $response_code = restapi::RESPONSE_UNAUTHORIZED;
        $message = 'Access Denied';

}

//Didn't receive an application/json content type
elseif ($_SERVER['CONTENT_TYPE'] != restapi::VALID_MEDIATYPE) {
        $response_code = restapi::RESPONSE_UNSUPORRTEDMEDIATYPE;
        $message = "Content-type not set to application/json";
}

//No Valid noun sent
elseif (!restapi::isValidNoun($noun)) {
	$response_code = restapi::RESPONSE_NOTFOUND;
	$message = "Invalid Resource received";
}


//Malformed json received
elseif ($json == null) {
        $response_code = restapi::RESPONSE_BADREQUEST;
	$message = "Malformed json received";
}

ob_clean();
if ($response_code != restapi::RESPONSE_SUCCESS) {
	http_response_code($response_code);
	$json_array = array('status'=>$response_code,'messsage'=>$message,$_SERVER);
	echo json_encode($json_array);
	exit;
}
ob_start();
require_once __DIR__ . '/../../includes/authenticate.inc.php';
$restapi = new restapi($db,$ldap,$login_session);

$result = $restapi->received_data($_SERVER['PHP_AUTH_PW'],$verb,$noun,$index,$json);

ob_clean();
http_response_code((int)$result['response_code']);
echo json_encode($result['json']);
?>

