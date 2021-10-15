<?php
class Group {

	private $db;
    	private $groupId;
	private $groupName;
	private $description;
	private $departmentId;
	private $netid;
	private $log_file = null;

	public function __construct(PDO $db)
	{
		$this->db = $db;
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());

	}
	
	public function __destruct()
	{
		
	}

	/**Add a group to the database and load it in the current object
	* @param $groupName
	* @param $description
	* @param $departmentId
	* @param $netid
	*/
	public function create($groupName, $description, $departmentId,$netid) {
		$groupId = 0;
		$sql= "INSERT INTO groups (group_name, description, department_id,netid)VALUES(:group_name,:description,:department_id,:netid)";
		$query = $this->db->prepare($sql);
		$parameters = array(':group_name'=>$groupName,
				':description'=>$description,
				':department_id'=>$departmentId,
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
		$this->departmentId = $result['department_id'];
		$this->groupId = $groupId;
		$this->netid = $result['netid'];
		
	}

	/**
	* Update group parameters in database
	*/
	public function update($groupName,$netid,$departmentId,$description) {
		$old_netid = $this->getNetid();
		try {
			$sql = "UPDATE `groups` SET group_name=:group_name,description=:description, department_id=:department_id, ";
			$sql .= "netid=:netid WHERE id=:group_id LIMIT 1";
			$parameters = array(":group_name"=>$groupName,
					":description"=>$description,
					":department_id"=>$departmentId,
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
    public static function getAllGroups($db)
	{
		$queryGroupList = "SELECT id, group_name, netid FROM groups ORDER BY group_name";
        $groupList = $db->query($queryGroupList);
        $groupListArr = $groupList->fetchAll(PDO::FETCH_ASSOC);

        return $groupListArr;
	}

    /**Check if a group exists by groupName
     * @param $groupName
     * @return bool
     */
    public static function exists($db, $groupName)
    {
        $queryGroup = "SELECT COUNT(*) FROM groups WHERE group_name=:group_name";
        $group = $db->prepare($queryGroup);
        $group->execute(array(':group_name'=>$groupName));
        $groupCount = $group->fetchColumn();

        if($groupCount)
        {
            return true;
        }
        return false;
    }

    /**Get a list of all group members
     * @return array
     */
    public function getMembers()
    {
        if($this->getId())
        {
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
    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

	/**
	* @return mixed
	*/
	public function getNetid() {
		return $this->netid;
	}


	public function createGroupFolder() {
		$gid = LDAPMAN_PI_PREFIX . $this->netid;
		if (settings::get_dataserver_enabled()) {
			try {
				$directory = settings::get_dataserver_root_dir() . "/" . $this->netid;
				data_dir::create($this->db,$this->getId(),$directory);
				data_dir::createDirectory($gid, $this->netid, $this->netid);
				foreach($this->getMembers() as $member) {
					data_dir::createDirectory($gid, $this->netid, $member['user_name']);
				}
				$this->log_file->send_log("Created group folder " . $directory . " for group " . $this->groupName . " with netid " . $this->netid);
			}
			catch (Exception $e) {
				$this->log_file->send_log($e->getMessage(),2);
				throw $e;
				return false;
			}
		}
		return true;
	}

	public function addLdapGroup() {
		if(LDAPMAN_API_ENABLED){
			global $ldapman;
			try {
				$gid = LDAPMAN_PI_PREFIX . $this->netid;
				if (count($ldapman->getGroup($gid))) {
					throw new Exception("Error ldap group " . $gid . " already exists");
					return false;
				}
				if (!$ldapman->addGroup($gid, "Core $this->netid PI group")) {
					throw new Exception("Error adding group " . $gid . " to ldap");
					return false;	
				}
				if (!$ldapman->addGroupMember($gid, $this->netid)) {
					throw new Exception("Error adding user " . $this->netid . " to ldap group " . $gid);
					return false;
				}
				foreach($this->getMembers() as $member) {
					if (!$ldapman->addGroupMember($gid, $member['user_name'])) {
						throw new Exception("Error adding user " . $this->netid . " to ldap group " . $gid);
        	                                return false;
					}
				}

			}
			catch (Exception $e) {
				$this->log_file->send_log($e->getMessage(),2);
				throw $e;
				return false;
				
			}
		}
		return true;
	}

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->groupId;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->groupName;
    }
}

?>
