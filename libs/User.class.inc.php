<?php

class User
{
	private $db;

	const ACTIVE = 1;
	const DISABLED = 0;
	const ROLE_ADMIN = 1;
	const ROLE_SUPERVISOR = 2;
	const ROLE_USER = 3;	
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

	public function __construct(PDO $db) {
		$this->db = $db;
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
		$departmentId,$rateId,$status,$userRoleId,$certified) {

		$this->username = $username;
		$this->first = $first;
		$this->last = $last;
		$this->email = $email;
		$this->departmentId = $departmentId;
		$this->rateid = $rateId;
		$this->status = $status;
		$this->userRoleId = $userRoleId;
		$this->certified = $certified;
		if ( User::exists($this->db, $this->username) == 0 ) {
			$sql = "INSERT INTO users (user_name, first,last,email,department_id,rate_id,status,user_role_id,certified) ";
			$sql .= "VALUES(:user_name,:first,:last,:email,:department_id,:rate_id,:status,:user_role_id,:certified)";

			try {
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
				);
				$result = $query->execute($parameters);
				$this->userId = $this->db->lastInsertId();
			}
			catch (PDOException $e) {
				echo "Database Error: " . $e->getMessage();
			}
			$this->log_file->send_log("Added user " . $username);
	        }
	}

	/**Load user into this object
	* @param $id
	*/
	public function load($id) {
		$sql = "SELECT * FROM users WHERE id=:user_id LIMIT 1";
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
    }

    /**
     * Update user into database based on changes made to this object
     */
    // TODO the db should be updated on *every* set function, not just when this update function is called.
    public function update() {
        $queryUpdateUser = "update users set
							user_name=:user_name,
							first=:first,
							last=:last,
							email=:email,
							department_id=:department_id,
							rate_id=:rate_id,
							status=:status,
							user_role_id=:user_role_id,
							certified=:certified
							where id=:user_id";
        $updateUserPrep = $this->db->prepare($queryUpdateUser);
        return $updateUserPrep->execute(
            array(
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
            ));
    }

	/**Check if a user exists by netid
	* @param PDO $db
	* @param     $username
	* @return int
	*/
	public static function exists($db, $username) {
		$sql = "SELECT id FROM users WHERE user_name=:user_name";
		$query = $db->prepare($sql);
		$query->execute(array(":user_name" => $username));
		$result = $query->fetch(PDO::FETCH_ASSOC);

		if (count($result)) {
			return $result["id"];
		} 
		return 0;

    }

    public function hasAccessTo($deviceId) {
        if ( $this->isAdmin() ) { // Admins can access everything
            return true;
        } else {
            $query = "select * from access_control where device_id=:resource_id and user_id=:user_id limit 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(array(":resource_id" => $deviceId, ":user_id" => $this->getId()));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false;
        }
    }

    public function giveAccessTo($deviceId) {
        $query = "insert into access_control (user_id, device_id) values (:userid,:deviceid)";
	$device = new Device($this->db);
	$device->load($deviceId);
        $stmt = $this->db->prepare($query);
        if ( $stmt->execute(array(":userid" => $this->getId(), ":deviceid" => $deviceId)) ) {
		$device = new Device($this->db);
		$device->load($deviceId);
		$this->log_file->send_log("Gave user '" . $this->getUsername() . "' access to device " . $device->getShortName());
        }
    }

    public function removeAccessTo($deviceId) {
        $query = "delete from access_control where user_id=:userid and device_id=:deviceid limit 1";
        $stmt = $this->db->prepare($query);
        if ( $stmt->execute(array(":userid" => $this->getId(), ":deviceid" => $deviceId)) ) {
		$device = new Device($this->db);
		$device->load($deviceId);		
		$this->log_file->send_log("Removed access to device " . $device->getShortName() . " for user " . $this->getUsername());
        }
    }

    /**List all users by id and username on the application
     * @param PDO $db
     * @return array
     */
    public static function getAllUsers($db) {
        $queryAllUsers = "SELECT id, user_name FROM users ORDER BY user_name";
        $allUsers = $db->prepare($queryAllUsers);
        $allUsers->execute();
        $allUsersArr = $allUsers->fetchAll(PDO::FETCH_ASSOC);
        return $allUsersArr;
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
		$sql .= "GROUP_CONCAT(g.group_name separator ', ') as group_name, ";
		$sql .= "uc.cfop, d.department_name, u.time_created as date_added, ";
		$sql .= "(select max(`stop`) from `session` where user_id=u.`id`) as last_login, ";
		$sql .= "CONCAT(u.last, ', ', u.first) as full_name, ";
		$sql .= "IF (u.status=:status,'Active','Disabled') as status, u.id, ud.edu_level, ud.gender, ";
		$sql .= "ud.underrepresented ";
		$sql .= "FROM users u ";
		$sql .= "LEFT JOIN user_cfop uc on (uc.user_id = u.id and uc.default_cfop=1) ";
		$sql .= "LEFT JOIN user_groups ug on (u.id=ug.user_id) ";
		$sql .= "LEFT JOIN `groups` g on (g.id=ug.group_id) ";
		$sql .= "LEFT JOIN departments d on (d.id=u.department_id) ";
		$sql .= "LEFT JOIN user_demographics ud on u.id = ud.user_id ";
		$sql .= "GROUP BY u.id";
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
		while ( $row = $stmt->fetch() ) {
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
							$this->log_file->send_log($e->getMesage(),\IGBIllinois\log::ERROR);
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
			$this->log_file->send_log("Set status of user '" . $this->username . "' to '$statusid'");
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
}

?>
