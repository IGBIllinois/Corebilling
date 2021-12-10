<?php

class restapi {

	const valid_nouns = array('session','ldapuser');
	const RESPONSE_SUCCESS = 200;
	const RESPONSE_BADREQUEST = 400;
	const RESPONSE_UNAUTHORIZED = 401;
	const RESPONSE_FORBIDDEN = 403;
	const RESPONSE_NOTFOUND = 404;
	const RESPONSE_UNSUPORRTEDMEDIATYPE = 415;
	const VALID_MEDIATYPE = "application/json";
	private $db;
	private $ldap;
	private $current_session;


	public function __construct(PDO $db,\IGBIllinois\ldap $ldap, \IGBIllinois\session $current_session) {
		$this->db = $db;
		$this->ldap = $ldap;
		$this->current_session = $current_session;
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

	public function received_data($secure_key,$verb,$noun,$index,$json) {
		$result = json_encode(array());
		if ($noun == 'ldapuser') {
			$result = $this->api_ldapuser($secure_key,$verb,$index,$json);

		}
		elseif ($noun == 'session') {
			$result = $this->api_device_session($secure_key,$verb,$index,$json); 

		}	
		return $result;

	}
	public function verifySession($session_id) {
		if ($this->current_session->get_session_id() == $session_id) {
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

	private function api_device_session($secure_key,$verb,$index,$json) {
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

	}
}

?>
