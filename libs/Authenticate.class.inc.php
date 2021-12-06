<?php

/**
 * Class Authenticate
 *
 * Used to authenticate a user using ldap and set session variables and authentication keys.
 */
class Authenticate {
	private $db;
	private $session;
	private $ldap;
	private $authenticatedUser;
	private $ipaddress;
	private $login;
	private $verified;
	private $user_id = null;
	private $key = null;
	private $timeout = null;
	private $user_info;

	public function __construct(PDO $db, \IGBIllinois\ldap $ldap) {
		$this->db = $db;
		$this->ldap = $ldap;
		$this->verified = false;
		$this->authenticatedUser = new User($this->db,$this->ldap);
	}

	public function __destruct() {
	
	}

	/** Log user in using their username and password
	* @param $userName
	* @param $password
	* @return bool
	*/
	public function Login($username, $password) {
		$userId = User::exists($this->db,$username);
		if ($userId) {
			$this->user_id = $userId;
			$this->authenticatedUser->load($userId);
			$this->get_user($username);
		}
		else {
			throw new Exception('You do not have permissions to login'); 
			return false;
		}
		if ($this->authenticatedUser->getStatus() != $this->authenticatedUser::ACTIVE) {
                        throw new Exception('You do not have permissions to login');
                        return false;
                }
	
		if (!$this->ldap->authenticate($username,$password)) {
			throw new Exception('Invalid Username or Password');
			return false;
		}
		$this->session = new \IGBIllinois\session(settings::get_session_name());
		$this->SetSession();
		return true;

	}

	/**
	* Logout user by removing their session information and marking them as unverified
	*/
	public function Logout() {
		$this->UnsetSession();
		$this->verified = false;
	}

	/** Verify the user via their session so we don't have to check LDAP every time
	*  if the session has expired then force logout the user by removing their session information
	* @return bool
	*/
	public function VerifySession($session) {
		$this->session = $session;	
		$this->load();
		if(!$this->user_id) {
			$this->verified = false;
			return false;
		}
		elseif(time() > $this->timeout + settings::get_session_timeout()) {
			$this->verified = false;
			return false;
		}
		elseif ($this->ipaddress != $_SERVER['REMOTE_ADDR']) {
			$this->verified = false;
			return false;
		}
		elseif (!$this->login) {
			$this->verified = false;
			return false;
		}
		$this->authenticatedUser->load($this->user_id);
		$this->SetSession();
                return true;
	}

	/**Sets the session informtion
	* @param $userId
	*/
	private function SetSession() {
		$session_vars = array('user_id'=> $this->user_id,
				'timeout'=>time(),
				'ipaddress'=>$_SERVER['REMOTE_ADDR'],
				'login'=>true,
				'lastpage'=>substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],"/") + 1)
			);
		$this->session->set_session($session_vars);
		$this->verified = true;
	}
    
	private function load() {
		if ($this->session->get_var('user_id')) {
			$this->user_id = $this->session->get_var('user_id');
			$this->timeout = $this->session->get_var('timeout');
			$this->ipaddress = $this->session->get_var('ipaddress');
			$this->login = $this->session->get_var('login');
		}
	}
	/**
	* Removes session information when the user logs out or expired login
	*/
	private function UnsetSession() {
		$session = new \IGBIllinois\session(settings::get_session_name());
		$session->destroy_session();
		unset($_POST);

	}

	/**
	* @return mixed
	*/
	public function getAuthenticatedUser() {
		return $this->authenticatedUser;
	}

	/**
	* @return boolean
	*/
	public function isVerified() {
		return $this->verified;
	}

	private function get_user_rdn() { 
		if (isset($this->user_info['dn'])) {
			return $this->user_info['dn'];
		}
		else {
			return false;
		}
	}
	private function get_user($username) {
		$filter = "(uid=" . $username . ")";
		$user_info = $this->ldap->search($filter);
		$this->user_info = $user_info[0];
	}

	public function getSession() {
		return $this->session;
	}

}
