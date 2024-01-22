<?php
class UserCfop {
	const DEFAULT_CFOP=1;
	const NON_DEFAULT_CFOP=0;
	const ACTIVE_CFOP=1;
	const NON_ACTIVE_CFOP=0;

	private $db;
	private $userId;
	private $cfop;
	private $userCfopId;
	private $active;
	private $default;
	private $createdDate;
	private $log_file = null;

	public function __construct(PDO $db) {
		$this->db = $db;
		$this->userCfopId = 0;
		$this->default = 1;
		$this->active = 1;
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
	}

	public function __destruct() {

	}

	/** Create CFOP to charge
	* @param $userId
	* @param $cfop
	*/
	public function create($userId, $cfop) {
		$this->userId = $userId;
		$this->cfop = $cfop;

		$sql = "INSERT INTO user_cfop (user_id,cfop)VALUES(:user_id,:cfop)";
		$query = $this->db->prepare($sql);
		$parameters = array(':user_id'=>$this->userId,
			':cfop'=>$this->cfop
		);
		$query->execute($parameters);
		$this->userCfopId =$this->db->lastInsertId();
		$this->load($this->userCfopId);
		$this->setAsDefaultCFOP();
		$this->log_file->send_log("Added CFOP " . $cfop . " for user " . User::getUsernameByID($this->db,$userId));
	}

	/**Load User CFOP from cfop id
	* @param $userCfopId
	*/
	public function load($userCfopId) {
		$sql = "SELECT * FROM user_cfop WHERE id=:user_cfop_id LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(':user_cfop_id'=>$userCfopId));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			$this->userId = $result['user_id'];
			$this->cfop = $result['cfop'];
			$this->createdDate = $result['created'];
			$this->userCfopId = $userCfopId;
		}
	}

	/**
	* Set this cfop as default cfop for user
	*/
	public function setAsDefaultCFOP() {
		if(!$this->default){
			$this->log_file->send_log("Set default CFOP for user ".$this->userId." to '".$this->cfop."'");
		}
		//mark all other user cfopls as not default
		$sql_remove = "UPDATE user_cfop SET default_cfop=:default_cfop WHERE user_id=:user_id";
		$query_remove = $this->db->prepare($sql_remove);
		$remove_parameters = array(':user_id'=>$this->userId,
                                ':default_cfop'=>UserCfop::NON_DEFAULT_CFOP
                );
		$query_remove->execute($remove_parameters);

		//mark current cfop as default
		$sql_default = "UPDATE user_cfop SET default_cfop=:default_cfop WHERE id=:user_cfop_id";
		$query_default = $this->db->prepare($sql_default);
		$parameters = array(':user_cfop_id'=>$this->userCfopId,
                                ':default_cfop'=>UserCfop::DEFAULT_CFOP
                );
		$query_default->execute($parameters);
	}

	/**Load default CFOPfor given user id
	* @param $userId
	* @return int
	*/
	public function loadDefaultCfop($userId) {
		$sql = "SELECT id FROM user_cfop WHERE user_id=:user_id AND default_cfop=:default_cfop";
		$query = $this->db->prepare($sql);
		$parameters = array(':user_id'=>$userId,
				':default_cfop'=>UserCfop::DEFAULT_CFOP
		);
		$query->execute($parameters);
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if($result) {
			$userCfopId = $result['id'];
			$this->load($userCfopId);
			return $userCfopId;
		}
		return false;
	}

	/** List available CFOPs for user id which are currently active
	* @param $userId
	* @return array
	*/
	public static function getAllCFOPs($db,$userId) {
		$sql = "SELECT * FROM user_cfop WHERE user_id=:user_id AND active=:active";
		$query = $db->prepare($sql);
		$params = array(':user_id'=>$userId,
			':active'=>UserCfop::ACTIVE_CFOP
		);
		$query->execute($params);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**Clean up and add spaces between numbers
	* @param $cfop
	* @return mixed|string
	*/
	public static function formatCfop($cfop) {
		if ($cfop) {
			$cfop = str_replace("-", "", $cfop);
		}
		if(strlen($cfop)<=19) {
			$cfop = substr($cfop, 0, 1) . "-" . substr($cfop, 1, 6) . "-" . substr($cfop, 7, 6) . "-" . substr($cfop, 13, 6);
		} 
		else {
			$cfop = substr($cfop, 0, 1) . "-" . substr($cfop, 1, 6) . "-" . substr($cfop, 7, 6) . "-" . substr($cfop, 13, 6)."-".substr($cfop, 19);
		}
		return $cfop;
	}

	// Getters and setters

	/**
	* @param mixed $cfop
	*/
	public function setCfop($cfop) {
		$this->cfop = $cfop;
	}

	/**
	* @return mixed
	*/
	public function getCfop() {
		return $this->cfop;
	}

	public function getCfopId() {
		return $this->userCfopId;
	}

	/**
	* @param mixed $userId
	*/
	public function setUserId($userId) {
		$this->userId = $userId;
	}

	/**
	* @return mixed
	*/
	public function getUserId() {
		return $this->userId;
	}

} 
