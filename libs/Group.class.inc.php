<?php
class Group {

	private $db;
    	private $groupId;
	private $groupName;
	private $description;
	private $netid;
	private $log_file = null;
	private $enabled = false;
	private $time_created;

	public function __construct(PDO $db) {
		$this->db = $db;
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());

	}
	
	public function __destruct() {
		
	}

	/**Add a group to the database and load it in the current object
	* @param $groupName
	* @param $description
	* @param $netid
	*/
	public function create($groupName, $description, $netid) {
		$groupId = 0;
		$sql = "INSERT INTO groups (group_name, description, netid)VALUES(:group_name,:description,:netid) ";
		$sql .= "ON DUPLICATE KEY UPDATE enabled=1,description=:description ";
		$query = $this->db->prepare($sql);
		$parameters = array(':group_name'=>$groupName,
				':description'=>$description,
				':netid'=>$netid,
				);
		$this->db->beginTransaction();
		$query->execute($parameters);
		$groupId = $this->db->lastInsertId();
		$this->load($groupId);
		try {
			$this->addLdapGroup();	
			$this->createGroupFolder();
		}
		catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
			$result = false;
		}
		$this->db->commit();
		$this->log_file->send_log("Added group " . $groupName . " with owner " . $netid);
		return $groupId;
	}

	/**Load a group into object from database given a group ID
	* @param $groupId
	*/
	public function load($groupId)
	{
		$sql = "SELECT * FROM groups WHERE id=:id LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(':id'=>$groupId));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		$this->groupName = $result['group_name'];
		$this->description = $result['description'];
		$this->groupId = $groupId;
		$this->netid = $result['netid'];
		$this->enabled = $result['enabled'];
		$this->time_created = $result['time_created'];
	
	}

	/**
	* Update group parameters in database
	*/
	public function update($groupName,$netid,$description,$enable = null) {
		$old_netid = $this->getNetid();
		try {
			$sql = "UPDATE `groups` SET group_name=:group_name,description=:description,netid=:netid WHERE id=:group_id LIMIT 1";
			$parameters = array(":group_name"=>$groupName,
					":description"=>$description,
					":group_id"=>$this->groupId, 
					":netid"=>$netid);
			$query = $this->db->prepare($sql);
			$query->execute($parameters);
			$old_netid = $this->getNetid();
			if ($this->getName() != $groupName) {
				$this->log_file->send_log("Group folder name " . $this->groupName . " changed to " . $groupName);
			}
			if ($this->getNetid() != $netid) {
				$this->log_file->send_log("Group folder " . $groupName . " netid changed to " . $netid);
			}
			$this->log_file->send_log("Group folder " . $this->groupName . " was update");
			$this->load($this->groupId);
			if ($old_netid != $netid) {
				$this->createGroupFolder();
			}
		}
		catch (Exception $e) {
			throw $e;
			return false;	
		}
 		return true;
	}

	/**Get a list of all groups by id and group_name
	* @return array
	*/
	public static function getAllGroups($db) {
		$sql = "SELECT id, group_name, netid FROM groups WHERE enabled='1' ORDER BY group_name";
		$query = $db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**Check if a group exists by groupName
	* @param $groupName
	* @return bool
	*/
	public static function exists($db, $groupName) {
		$queryGroup = "SELECT COUNT(*) FROM groups WHERE group_name=:group_name AND enabled=1";
		$group = $db->prepare($queryGroup);
		$group->execute(array(':group_name'=>$groupName));
		$groupCount = $group->fetchColumn();

		if($groupCount) {
			return true;
		}
		return false;
	}

	/**Get a list of all group members
	* @return array
	*/
	public function getMembers() {
		if($this->getId()) {
			$queryGroupUsers = "SELECT * FROM users u left join user_groups ug on u.id = ug.user_id WHERE ug.group_id=:group_id order by u.user_name";
			$groupUsers = $this->db->prepare($queryGroupUsers);
			$groupUsers->execute(array(":group_id"=>$this->getId()));
			$groupUsersArr = $groupUsers->fetchAll(PDO::FETCH_ASSOC);
			return $groupUsersArr;
		}
		return array();
	}


	//Getters and setters for this class

	/**
	* @return mixed
	*/
	public function getDescription() {
		return $this->description;
	}

	/**
	* @return int
	*/
	public function getId() {
		return $this->groupId;
	}


	/**
	* @return string
	*/
	public function getName() {
		return $this->groupName;
	}

	/**
	* @return mixed
	*/
	public function getNetid() {
		return $this->netid;
	}

	public function getEnabled() {
		return $this->enabled;
	}
	public function getTimeCreated() {
		return $this->time_created;
	}
	public function createGroupFolder() {
		$gid = $this->getLdapGroupName();
		if (settings::get_dataserver_enabled()) {
			try {
				$directory = settings::get_dataserver_root_dir() . "/" . $this->netid;
				data_dir::create($this->db,$this->getId(),$directory,$this->netid);
				data_dir::createDirectory($gid, $this->netid, $this->netid);
				foreach($this->getMembers() as $member) {
					data_dir::createDirectory($gid, $this->netid, $member['user_name']);
				}
				$this->log_file->send_log("Created group folder " . $directory . " for group " . $this->groupName . " with netid " . $this->netid);
			}
			catch (Exception $e) {
				$this->log_file->send_log($e->getMessage(),\IGBIllinois\log::ERROR);
				throw $e;
				return false;
			}
		}
		return true;
	}

	public function getLdapGroupName() {
		if (LDAPMAN_API_ENABLED && ($this->netid != null)) {
			return LDAPMAN_PI_PREFIX . $this->netid;
		}
		return false;
	}
	public function addLdapGroup() {
		if(LDAPMAN_API_ENABLED){
			global $ldapman;
			try {
				$gid = $this->getLdapGroupName();
				if ($ldapman->getGroup($gid) != null) {
					throw new Exception("Error ldap group " . $gid . " already exists");
					return false;
				}
				if (!$ldapman->addGroup($gid, "Core $this->netid PI group")) {
					throw new Exception("Error adding group " . $gid . " to ldap");
					return false;	
				}
				//if (!$ldapman->addGroupMember($gid, $this->netid)) {
				//	throw new Exception("Error adding user " . $this->netid . " to ldap group " . $gid);
				//	return false;
				//}
				foreach($this->getMembers() as $member) {
					if (!$ldapman->addGroupMember($gid, $member['user_name'])) {
						throw new Exception("Error adding user " . $this->netid . " to ldap group " . $gid);
        	                                return false;
					}
				}

			}
			catch (Exception $e) {
				$this->log_file->send_log($e->getMessage(),\IGBIllinois\log::ERROR);
				throw $e;
				return false;
				
			}
		}
		return true;
	}


	public function delete() {
		if ($this->getId() && $this->getEnabled()) {
			$members = $this->getMembers();
			if (count($members)) {
				throw new Exception("Can not delete group " . $this->getName() . ".  Group has " . count($members) ." members.  The group has to be empty before it can be deleted.");
				return false;
			}
			if (settings::get_dataserver_enabled()) {
				$directory = settings::get_dataserver_root_dir() . "/" . $this->netid;	
				$data_dir_id = data_dir::get_id_by_directory($this->db, $directory);
				if ($data_dir_id) {
					$data_dir = new data_dir($this->db,$data_dir_id);
					try {
						$data_dir->disable();
	
					}
					catch (Exception $e) {
						throw $e;	
						return false;
					}
				}
                        }
	
			$sql = "UPDATE groups SET enabled=0 WHERE id=:group_id LIMIT 1";
			$query = $this->db->prepare($sql);
			$result = $query->execute(array(':group_id'=>$this->getId()));
			$this->log_file->send_log("Group " . $this->getName() . " successfully deleted");
			$this->enabled = false;	
			return true;




		}
		return false;

	}

}

?>
