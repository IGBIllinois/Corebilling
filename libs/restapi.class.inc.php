<?php

class restapi {

	const valid_nouns = array('session','ldapuser');
	const RESPONSE_SUCCESS = 200;
	const RESPONSE_BADREQUEST = 400;
	const RESPONSE_UNAUTHORIZED = 401;
	const RESPONSE_FORBIDDEN = 403;
	const RESPONSE_NOTFOUND = 404;
	const RESPONSE_UNSUPORRTEDCONTENTTYPE = 415;
	const VALID_CONTENTTYPE = array("application/json","application/json; charset=UTF-8");
	private $db;
	private $ldap;


	public function __construct(PDO $db,\IGBIllinois\ldap $ldap) {
		$this->db = $db;
		$this->ldap = $ldap;
	}

	public function __destruct() {

	}

	public static function parseUri($uri) {


	}
	public static function isValidNoun($noun) {
		if (in_array($noun,self::valid_nouns)) {
			return true;
		}
		return false;

	}

	public function received_data($secure_key,$verb,$noun,$index,$json,$server) {
		$result = json_encode(array());
		switch ($noun) {
			case 'ldapuser':
				$result = $this->api_ldapuser($secure_key,$verb,$index,$json);
				break;
			case 'session':
				$result = $this->api_device_session($secure_key,$verb,$index,$json,$server);
				break;
			default:
				$result = array('response_code'=>'','json'=>array());

		}

		return $result;

	}
	public function verifySession($session_id) {
		$login_session = new \IGBIllinois\session(settings::get_session_name());
		if ($login_session->get_session_id() == $session_id) {
			return true;
		}
		return false;

	}

	public function verifyDeviceKey($device_id,$device_key) {
		$device = new Device($this->db);
		$device->load($device_id);

	}


	private function api_ldapuser($secure_key,$verb,$index,$json) {
		$json_array = array();
		if (!$this->verifySession($secure_key)) {
			return array('response_code'=>self::RESPONSE_UNAUTHORIZED,
				'json'=>array());
		}
		switch ($verb) {
                        case 'OPTIONS':
                                break;
                        case 'GET':
				$json_array = User::get_ldap_info($this->ldap,$index);
				$json_array = array('response_code'=>self::RESPONSE_SUCCESS,
						'json'=>$json_array);
                                break;
                        default:
                                break;
                }


		return $json_array;


	}

	private function api_device_session($secure_key,$verb,$index,$json,$server) {

		switch ($verb) {
			case 'OPTIONS':
				break;
			case 'POST':
				$json_array = $this->api_device_start_session($secure_key,$index,$json,$server);
				break;
			default:
				break;


		}
		return $json_array;
	}

	private function api_device_start_session($secure_key,$index,$json,$server) {
		$device = new Device($this->db);
                if (!$device->load($index)) {
                        $json_array = array('result'=>false,
                                'message'=>'Device ' . $index . ' not found');
                        return array('response_code'=>self::RESPONSE_SUCCESS,'json'=>$json_array);
                }

		elseif ($device->getDeviceToken() != $secure_key) {
			$json_array = array('result'=>false,
					'message'=>'Invalid Access Key');
			
			return array('response_code'=>self::RESPONSE_UNAUTHORIZED,'json'=>$json_array);
		}
		else {
			$userId = User::exists($this->db,$json->{'username'});
			if ($userId) {
				Session::trackSession($this->db,$device->getId(), $userId,$server['REMOTE_ADDR'],json_encode($json));
				$json_array = array('result'=>true,'message'=>'Tracking Session for user ' . $json->{'username'});
			}
			else {
				//User was not found in website database so check for user exceptions
				if (in_array(strtolower($json->{'username'}), array_map('strtolower', $USER_EXCEPTIONS_ARRAY))){
                                	$device->updateLastTick('',$server['REMOTE_ADDR'],json_encode($json));
				}
				else {
                        		$device->updateLastTick($json->{'username'},$server['REMOTE_ADDR'],json_encode($json));
				}
				$json_array = array('result'=>true,'message'=>'No User Logged in');
			}
			return array('response_code'=>self::RESPONSE_SUCCESS,'json'=>$json_array);
			
		}
	}	
}

?>
