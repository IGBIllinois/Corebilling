<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
error_log(print_r($uri));
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
elseif ($_SERVER['CONTENT_TYPE'] != restapi::VALID_CONTENTTYPE) {
        $response_code = restapi::RESPONSE_UNSUPORRTEDCONTENTTYPE;
        $message = "Content-type not set to " . restapi::VALID_CONTENTTYPE;
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

if ($response_code != restapi::RESPONSE_SUCCESS) {
	$json_array = array('status'=>$response_code,'messsage'=>$message);
	ob_clean();
	http_response_code((int)$response_code);
	echo json_encode($json_array);
	exit;
}
else {
	$restapi = new restapi($db,$ldap);
	error_log($_SERVER['PHP_AUTH_PW'],0);
	$result = $restapi->received_data($_SERVER['PHP_AUTH_PW'],$verb,$noun,$index,$json,$_SERVER);

	ob_clean();
	http_response_code((int)$result['response_code']);
	echo json_encode($result['json']);
}
?>

