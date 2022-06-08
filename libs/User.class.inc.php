<?php

class User
{
	private $db;

	const ACTIVE = 1;
	const DISABLED = 0;
	const ROLE_ADMIN = 1;
	const ROLE_SUPERVISOR = 2;
	const ROLE_USER = 3;
	const LDAP_ATTRIBUTES = array('uid','cn','sn','givenName','mail');	
	private $userId = 0;
	private $username = "";
	private $first = "";
	private $last = "";
	private $email = "";
	private $departmentId = 0;
	private $groupIds = array();
	private $rateid = 9;
	private $status = self::DISABLED;
	private $userRoleId = self::ROLE_USER;
	private $userCfop;
	private $certified = 0;
	private $time_created = "";
	private $demographics = null;
	private $log_file = null;
	private $ldap = null;
	private $ldap_info = array();
	private $supervisor_id = 0;
	private $suervisor_username = "";

	public function __construct(PDO $db,\IGBIllinois\ldap $ldap = null) {
		$this->db = $db;
		$this->ldap = $ldap;
		$this->userCfop = new UserCfop($this->db);
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
	}

	public function __destruct() {
	}

	/**Create User
	* @param $username
	* @param $first
	* @param $last
	* @param $email
	* @param $departmentId
	* @param $rateId
	* @param $statusId
	* @param $userRoleId
	* @param $certified
	*/
	public function create($username,$first,$last,$email,
		$departmentId = 0,$rateId,$status,$userRoleId,$certified,$supervisorId = 0) {

		$this->username = $username;
		$this->first = $first;
		$this->last = $last;
		$this->email = $email;
		$this->departmentId = $departmentId;
		$this->rateid = $rateId;
		$this->status = $status;
		$this->userRoleId = $userRoleId;
		$this->certified = $certified;

		$this->supervisor_id = 0;
		if (($userRoleId == self::ROLE_USER) && $supervisorId != 0) {
			$this->supervisor_id = $supervisorId;
		}
		elseif ($userRoleID == self::ROLE_USER && $superisorId == 0) {
			throw new Exception("Please specify a supervisor");
			return false;
		}

		if ($departmentId == 0) {
			throw new Exception("Please specify a department");
			return false;
		}
		if ( User::exists($this->db,$username)) {
			throw new Exception("User " . $username . " already exists");
			return false;
		}

		try {
			$sql = "INSERT INTO users (user_name, first,last,email,department_id,rate_id,status,user_role_id,certified,supervisor_id) ";
			$sql .= "VALUES(:user_name,:first,:last,:email,:department_id,:rate_id,:status,:user_role_id,:certified,:supervisor_id)";
				$query = $this->db->prepare($sql);
			
				$parameters = array(':user_name' => $this->username,
					':first' => $this->first,
					':last' => $this->last,
					':email' => $this->email,
					':department_id' => $this->departmentId,
					':rate_id' => $rateId,
					':status' => $status,
					':user_role_id' => $this->userRoleId,
					':certified' => $this->certified ? 1 : 0,
					':supervisor_id' => $this->supervisor_id
				);
				$result = $query->execute($parameters);
				$this->userId = $this->db->lastInsertId();
				$this->log_file->send_log("Successfully added user " . $username);
				return $this->userId;
		}
		catch (PDOException $e) {
			throw new Exception("Error adding user: " . $e->getMessage());
			return false;
		}
	}

	/**Load user into this object
	* @param $id
	*/
	public function load($id) {
		if ($id) {
			$sql = "SELECT users.*,supervisor.id as supervisor_id,supervisor.user_name as supervisor_username FROM users ";
			$sql .= "LEFT JOIN users AS supervisor ON supervisor.id=users.supervisor_id ";
			$sql .= "WHERE users.id=:user_id LIMIT 1";
			$query = $this->db->prepare($sql);
			$query->execute(array(":user_id" => $id));
			$result = $query->fetch(PDO::FETCH_ASSOC);
			$this->userId = $result["id"];
			$this->username = $result["user_name"];
			$this->first = $result["first"];
			$this->last = $result["last"];
			$this->email = $result["email"];
			$this->departmentId = $result["department_id"];
			$this->rateid = $result["rate_id"];
			$this->status = $result["status"];
			$this->userRoleId = $result["user_role_id"];
			$this->time_created = $result["time_created"];
			$this->certified = $result['certified'];
			$this->supervisor_id = $result['supervisor_id'];
			$this->supervisor_username = $result['supervisor_username'];
		}
	}

	/**
	* Update user into database based on changes made to this object
	*/
	// TODO the db should be updated on *every* set function, not just when this update function is called.
	public function update() {
		$sql = "UPDATE users SET ";
		$sql .= "user_name=:user_name,first=:first,last=:last,";
		$sql .= "email=:email,department_id=:department_id,rate_id=:rate_id,";
		$sql .= "status=:status,user_role_id=:user_role_id,certified=:certified,supervisor_id=:supervisor_id ";
		$sql .= "WHERE id=:user_id LIMIT 1";

		$query = $this->db->prepare($sql);
		$paramters = array(
			':user_name' => $this->username,
			':first' => $this->first,
			':last' => $this->last,
			':email' => $this->email,
			':department_id' => $this->departmentId,
			':rate_id' => $this->rateid,
			':status' => $this->status,
			':user_role_id' => $this->userRoleId,
			':certified' => $this->certified ? 1 : 0,
			':user_id' => $this->userId,
			':supervisor_id' => $this->supervisor_id
		);
		return $query->execute($paramters);

	}

	/**Check if a user exists by netid
	* @param PDO $db
	* @param     $username
	* @return int
	*/
	public static function exists($db, $username) {
		$sql = "SELECT id FROM users WHERE user_name=:user_name LIMIT 1";
		$query = $db->prepare($sql);
		$query->execute(array(":user_name" => $username));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result['id']) {
			return $result["id"];
		} 
		return 0;

	}

	public function hasAccessTo($deviceId) {
		if ( $this->isAdmin() ) { // Admins can access everything
			return true;
		} 
		else {
			$sql = "SELECT * FROM access_control WHERE device_id=:resource_id AND user_id=:user_id LIMIT 1";
			$query = $this->db->prepare($sql);
			$parameters = array(":resource_id" => $deviceId, 
					":user_id" => $this->getId());
			$query->execute($parameters);
			$result = $query->fetch(PDO::FETCH_ASSOC);
			return $result !== false;
		}
	}

	public function giveAccessTo($deviceId) {
        	$sql = "INSERT INTO access_control(user_id, device_id) VALUES(:userid,:deviceid)";
		$query = $this->db->prepare($sql);
		$parameters = array(":userid" => $this->getId(), ":deviceid" => $deviceId);
		if ($query->execute($parameters)) {
			$device = new Device($this->db);
			$device->load($deviceId);
			$this->log_file->send_log("Gave user '" . $this->getUsername() . "' access to device " . $device->getShortName());
			return true;
		}
		return false;
	}

	public function removeAccessTo($deviceId) {
		$sql = "DELETE FROM access_control WHERE user_id=:userid AND device_id=:deviceid LIMIT 1";
		$query = $this->db->prepare($sql);
		$parameters = array(":userid" => $this->getId(), ":deviceid" => $deviceId);
		if ($query->execute($parameters)) {
			$device = new Device($this->db);
			$device->load($deviceId);		
			$this->log_file->send_log("Removed access to device " . $device->getShortName() . " for user " . $this->getUsername());
			return true;
		}
		return false;
	}

	/**List all users by id and username on the application
	* @param PDO $db
	* @return array
	*/
	public static function getUsers($db,$status = null,$role_id = false) {
		$sql = "SELECT * FROM users ";
		$parameters = array();
		if ($status !== null || $role_id) {
			$sql .= "WHERE 1=1 ";
		}
		if ($status !== null) {
			$sql .= "AND status=:status ";
			$parameters[':status'] = $status;
		}
		if ($role_id) {
			$sql .= "AND user_role_id=:role_id ";
			$parameters[':role_id'] = $role_id;
		}
		$sql .= "ORDER BY user_name";
		
		$query = $db->prepare($sql);
		$query->execute($parameters);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getSupervisors($db,$status = null) {
		return self::getUsers($db,$status,self::ROLE_SUPERVISOR);

	}
	
	public static function getAdministrators($db,$status = null) {
		return self::getUsers($db,$status,self::ROLE_ADMIN);
	}

	/**List all active users by id and username on the application
	* @param PDO $db
	* @return array
	*/
	public static function getAllActiveUsers($db) {
		$sql = "SELECT id, user_name FROM users where status=:status ORDER BY user_name";
		$query = $db->prepare($sql);
		$query->execute(array(":status"=>self::ACTIVE));
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	* @param PDO $db
	* @return mixed
	*/
	public static function getAllUsersFullInfo($db) {
		$sql = "SELECT u.first, u.last, u.email, u.department_id, ";
		$sql .= "uc.cfop, d.department_name, u.time_created as date_added, ";
		$sql .= "(select max(`stop`) from `session` where user_id=u.`id`) as last_login, ";
		$sql .= "CONCAT(u.last, ', ', u.first) as full_name, ";
		$sql .= "IF (u.status=:status,'Active','Disabled') as status, u.id, ";
		$sql .= "user_roles.role_name as role ";
		$sql .= "FROM users u ";
		$sql .= "LEFT JOIN user_roles ON user_roles.id=u.user_role_id ";
		$sql .= "LEFT JOIN user_cfop uc on (uc.user_id = u.id and uc.default_cfop=1) ";
		$sql .= "LEFT JOIN departments d on (d.id=u.department_id) ";
		$query = $db->prepare($sql);
		$query->execute(array(':status'=>self::ACTIVE));
		$result = $query->fetchAll(PDO::FETCH_ASSOC);

		for ( $i = 0; $i < count($result); $i++ ) {
			$result[$i]['cfop'] = UserCfop::formatCfop($result[$i]['cfop']);
			$result[$i]['edit'] = '<a href="edit_users.php?user_id=' . $result[$i]['id'] . '">Edit</a>';
		}
		return $result;
	}

	/**
	* @param PDO $db
	* @param     $startyear
	* @param     $startmonth
	* @param     $endyear
	* @param     $endmonth
	* @return mixed
	*/
	public static function getActiveUsers($db, $startyear, $startmonth, $endyear, $endmonth) {
		$sql = "select u.first, u.last, u.id, u.user_name, u.email, ";
		$sql .= "u.department_id, GROUP_CONCAT(distinct g.group_name separator ', ') as group_name, ";
		$sql .= "d.department_name, CONCAT(u.last, ', ', u.first) as full_name ";
		$sql .= "FROM users u ";
		$sql .= "LEFT JOIN user_groups ug on (u.id=ug.user_id) ";
		$sql .= "LEFT JOIN `groups` g on (g.id=ug.group_id) ";
		$sql .= "LEFT JOIN departments d on d.id=u.department_id ";
		$sql .= "LEFT JOIN `session` s on s.user_id=u.id ";
		$sql .= "WHERE ((MONTH(start)>=:startmonth AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) ";
		$sql .= "AND ((MONTH(start)<=:endmonth AND YEAR(start)=:endyear) OR YEAR(start)<:endyear) ";
		$sql .= "GROUP BY u.id ORDER BY u.user_name";

		$query = $db->prepare($sql);
		$parameters = array(':startyear' => $startyear,
				':startmonth' => $startmonth,
				':endyear' => $endyear,
				':endmonth' => $endmonth
			);
		$query->execute($parameters);
		return $query->fetchAll(PDO::FETCH_ASSOC);

    }

	/**Get all user roles
	* @param PDO $db
	* @return mixed
	*/
	public static function getUserRoles($db) {
		$sql = "SELECT * from user_roles";
		$query = $db->prepare($sql);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getUserRoleById($db,$id) {
		$sql = "SELECT * FROM user_roles WHERE id=:id LIMIT 1";
		$query = $db->prepare($sql);
		$query->execute(array(':id'=>$id));
		return $query->fetchAll(PDO::FETCH_ASSOC);

	}

	/**
	* @param PDO $db
	* @return mixed
	*/
	public static function getUserStatusList() {
		$status = array();
		array_push($status,array('id'=>self::DISABLED,'name'=>'Disabled'));
		array_push($status,array('id'=>self::ACTIVE,'name'=>'Active'));
		return $status;

	}

	public function addCFOP($cfop) {
		$this->userCfop->create($this->userId, $cfop, "");
	}

	public function getAllCFOPs() {
		return UserCfop::getAllCFOPs($this->db, $this->userId);
	}

	public function getDefaultCFOP() {
		$this->userCfop->loadDefaultCfop($this->userId);
		return $this->userCfop->getCfop();
	}

	public function getDefaultCFOPID() {
		$this->userCfop->loadDefaultCfop($this->userId);
		return $this->userCfop->getCfopId();

	}

	public function setDefaultCFOP($defaultCfopId) {
		$this->userCfop->load($defaultCfopId);
		$this->userCfop->setAsDefaultCFOP();
	}

	public function isAdmin() {
		return $this->getRoleId() == self::ROLE_ADMIN;
	}

	public function isSupervisor() {
		return $this->getRoleId() == self::ROLE_SUPERVISOR;
	}

	public function isUser() {
		return $this->getRoleId() == self::ROLE_USER;
	}

	public function getDemographics() {
		if ( $this->demographics === null ) {
			$this->demographics = new UserDemographics($this->db, $this->getId());
		}
		return $this->demographics;
	}

	//Getters and setters
	public function getId() {
		return $this->userId;
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($username) {
		if ( $this->username != $username ) {
			$this->log_file->send_log("Set username of user '" . $this->username . "' to '$username'");
			$this->username = $username;
		}
	}

	public function getFirstName() {
		return $this->first;
	}

	public function setFirstName($first) {
		if ( $this->first != $first ) {
			$this->first = $first;
			$this->log_file->send_log("Set first name of user '" . $this->username . "' to '$first'");
		}
	}

	public function getLastName() {
		return $this->last;
	}

	public function setLastName($last) {
		if ( $this->last != $last ) {
			$this->last = $last;
			$this->log_file->send_log("Set last name of user '" . $this->username . "' to '$last'");
		}
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		if ( $this->email != $email ) {
			$this->email = $email;
			$this->log_file->send_log("Set email of user '" . $this->username . "' to '$email'");
		}
	}

	public function getSupervisorUsernam() {
		return $this->supervisor_username;
	}
	public function getSupervisorId() {
		return $this->supervisor_id;
	}

	public function setSupervisorId($supervisorId) {
		if ($this->supervisor_id != $supervisorId) {
			$this->supervisor_id = $supervisorId;
			$this->log_file->send_log("Set Supervisor of user " . $this->username . " to " . $supervisorId);
		}

	}
	public function getDepartmentId() {
		return $this->departmentId;
	}

	public function setDepartmentId($departmentId) {
		if ( $this->departmentId != $departmentId ) {
			$this->departmentId = $departmentId;
			$this->log_file->send_log("Set department of user '" . $this->username . "' to '$departmentId'");
		}
	}

	public function getGroupIds() {
		$sql = "SELECT group_id FROM user_groups WHERE user_id=:user_id";
		$query = $this->db->prepare($sql);
		$query->execute([':user_id' => $this->getId()]);

		$ids = [];
		while ( $row = $query->fetch() ) {
			$ids[] = $row['group_id'];
		}
		return $ids;
	}

	public function setGroupIds($ids) {
		global $ldapman;
		$addStmt = $this->db->prepare('insert into user_groups (user_id, group_id) values (:user, :group)');
		$deleteStmt = $this->db->prepare('delete from user_groups where group_id=:group and user_id=:user limit 1');

		$currentIds = $this->getGroupIds();
		foreach ( $ids as $id ) {
			if ( !in_array($id, $currentIds) ) {
				// not in group; add to group
				$group = new Group($this->db);
				$group->load($id);
				$addStmt->execute([':user' => $this->getId(), ':group' => $id]);
				$this->log_file->send_log("Added user " . $this->getUsername() . " to group " . $group->getName());
				if(LDAPMAN_API_ENABLED) {
					if($group->getNetid() != null) {
						$gid = LDAPMAN_PI_PREFIX . $group->getNetid();
						$ldapman->addGroupMember($gid, $this->getUsername());
						try {
							data_dir::createDirectory($gid, $group->getNetid(), $this->getUsername());
						}
						catch (Exception $e) {
							$this->log_file->send_log($e->getMessage(),\IGBIllinois\log::ERROR);
							throw $e;
						}
					}
				}
            		}
        	}
		foreach ( $currentIds as $oldId ) {
			if ( !in_array($oldId, $ids) ) {
				// removed from group
				$group = new Group($this->db);
				$group->load($oldId);
				$deleteStmt->execute([':user' => $this->getId(), ':group' => $oldId]);
				$this->log_file->send_log("Removed user " . $this->getUsername() . " from group " . $group->getName());
				if(LDAPMAN_API_ENABLED){
					if($group->getNetid() != null) {
						$ldapman->removeGroupMember(LDAPMAN_PI_PREFIX . $group->getNetid(), $this->getUsername());
					}
				}
            		}
        	}
	}

	public function getRateId() {
		return $this->rateid;
	}

	public function setRateId($rateId) {
		if ( $this->rateid != $rateId ) {
			$this->rateid = $rateId;
			$this->log_file->send_log("Set rate of user '" . $this->username . "' to '$rateId'");
		}
	}

	public function getStatus() {
		return $this->status;
	}

	public function setStatus($status) {
		if ( $this->status != $status ) {
			$this->status = $status;
			$status_name = "";
			foreach ($this->getUserStatusList() as $status_list) {
				if ($status_list['id'] == $status) {
					$status_name = $status_list['name'];
				}
			}
			$this->log_file->send_log("Set status of user " . $this->username . " to " . $status_name);
		}
	}

	public function getRoleId() {
		return $this->userRoleId;
	}

	 public function setRoleId($usertypeid) {
		if ( $this->userRoleId != $usertypeid ) {
			$this->userRoleId = $usertypeid;
			$role = self::getUserRoleById($this->db,$usertypeid);
			$this->log_file->send_log("Set role of user " . $this->username . " to " . $role[0]['role_name']);
		}
	}

	public function getDateAdded() {
		return $this->time_created;
	}

	public function getLastLogin() {
		$sql = "SELECT MAX(`stop`) AS last_login FROM `session` WHERE user_id=:user_id";
		$query = $this->db->prepare($sql);
		$query->execute(array(":user_id"=>$this->userId));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		return $result['last_login'];
	}

	public function isCertified() {
		return $this->certified;
	}

	public function setCertified($certified) {
		if ( $this->certified != $certified ) {
			$this->certified = $certified;
			if ( $certified ) {
				$this->log_file->send_log("Certified user '" . $this->username . "'");
			} 
			else {
				$this->log_file->send_log("Un-certified user '" . $this->username . "'");
			}
		}
	}

	public static function getIDByUsername($db,$username) {
		$sql = "SELECT id FROM users where user_name=:username LIMIT 1";
		$query = $db->prepare($sql);
		$query->execute(array(':username'=>$username));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			return $result['id'];

		}
		return false;
	}

	public function get_data_dir_id() {
		$sql = "SELECT data_dir_id FROM data_dir WHERE data_dir_enabled=1 AND data_dir_user_id=:user_id LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(':user_id'=>$this->getId()));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			return $result['data_dir_id'];

		}
		return false;

	}

	public function is_ldap_user() {
		return $this->ldap->is_ldap_user($this->getUsername());

	}


	public function email_bill($year,$month) {
		$start_date = $year . $month . "01";
		$end_date = $year . $month . date('t',strtotime($start_date));

		$subject = "Test Submit";
		$to = $this->getEmail(); 
		$loader = new Twig_Loader_Filesystem(settings::get_twig_dir());
		$twig = new Twig_Environment($loader);
		$twig_variables = array(
			'css'=>settings::get_email_css_contents()
		);

		if (file_exists(settings::get_twig_dir() . "/custom/" . self::USER_BILL_TWIG)) {
			$html_message = $twig->render("custom/" . self::USER_BILL_TWIG,$twig_variables);
		}
		else {
			$html_message = $twig->render("default/" . self::USER_BILL_TWIG,$twig_variables);
		}
		$email = new \IGBIllinois\email(settings::get_smtp_host(),settings::get_smtp_port(),settings::get_smtp_username(),settings::get_smtp_password());
		$email->set_to_emails($to);
		try {
			$result = $email->send_email(settings::get_from_email(),$subject,"",$html_message);
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
		return true;
	}

	public static function get_ldap_info($ldap,$username) {
		$username = trim(rtrim($username));
		$filter = "(uid=" . $username . ")";
		$ou = settings::get_ldap_base_dn();	
		$ldap_info = $ldap->search($filter,$ou,self::LDAP_ATTRIBUTES);
		$formatted_info = self::get_ldap_attributes();
		if ($ldap_info['count'] == 1) {
			$formatted_info['dn'] = $ldap_info[0]['dn'];
			foreach ($formatted_info as $key=>$value) {
				$formatted_info[$key] = $ldap_info[0][strtolower($key)][0];

			}
		}
		return $formatted_info;
		
	}

	public static function get_ldap_attributes() {
		$formatted_info = array();
		$formatted_info['dn'] = null;
		foreach (self::LDAP_ATTRIBUTES as $attribute) {
                                $formatted_info[$attribute] = null;

		}
		return $formatted_info;

	}
}

?>
