<?php
class User {
	private $db;

	const ACTIVE = 5, HIDDEN = 6, DISABLED = 7;
	const STATUS_TYPE_USER=2;

	private $userId;
	private $username;
	private $first;
	private $last;
	private $email;
	private $departmentId;
	private $groupId;
	private $rateid;
	private $statusid;
	private $userRoleId;
	private $dateAdded;
	private $secureKey;
	private $userCfop;
	private $certified;

	public function __construct(PDO $db)
	{
		$this->db = $db;
		$this->userId=0;
		$this->username="";
		$this->first="";
		$this->last="";
		$this->email="";
		$this->departmentId=0;
		$this->groupId=0;
		$this->rateid=9;
		$this->statusid=7;
		$this->dateAdded="";
		$this->secureKey="";
		$this->userCfop = new UserCfop($this->db);
		$this->certified = 0;
		$this->userRoleId = 3;
	}

	public function __destruct()
	{

	}

	/**Create User
	 * @param $username
	 * @param $first
	 * @param $last
	 * @param $email
	 * @param $departmentId
	 * @param $groupId
	 * @param $rateId
	 * @param $statusId
	 * @param $userRoleId
	 */
	public function create($username, $first, $last, $email,$departmentId,$groupId,$rateId,$statusId,$userRoleId,$certified)
	{
		$this->username = $username;
		$this->first = $first;
		$this->last = $last;
		$this->email = $email;
		$this->departmentId = $departmentId;
		$this->groupId = $groupId;
		$this->rateid = $rateId;
		$this->statusid = $statusId;
		$this->userRoleId = $userRoleId;
		$this->dateAdded = date('Y-m-d H:i:s');
		$this->certified = $certified;
		if(User::exists($this->db,$this->username)==0)
		{
			$queryAddUser = "INSERT INTO users (user_name, first,last,email,department_id,group_id,rate_id,status_id,date_added,secure_key,user_role_id,certified)
								   VALUES(:user_name,:first,:last,:email,:department_id,:group_id,:rate_id,:status_id,NOW(), MD5(RAND()),:user_role_id,:certified)";
			$addUserPrepare = $this->db->prepare($queryAddUser);
			$addUserPrepare->execute(array(':user_name'=>$this->username,':first'=>$this->first,':last'=>$this->last,':email'=>$this->email,':department_id'=>$this->departmentId,':group_id'=>$this->groupId,':rate_id'=>$rateId,':status_id'=>$statusId,':user_role_id'=>$this->userRoleId,':certified'=>$this->certified?1:0));
			$this->userId=$this->db->lastInsertId();
			log::log_message("Added user '$username'");
		}
	}

	/**Load user into this object
	 * @param $id
	 */
	public function load($id)
	{
		$queryUserInfo = "SELECT * FROM users WHERE id=:user_id";
		$userInfo=$this->db->prepare($queryUserInfo);
		$userInfo->execute(array(":user_id"=>$id));
		$userInfoArr = $userInfo->fetch(PDO::FETCH_ASSOC);
		$this->userId = $userInfoArr["id"];
		$this->username=$userInfoArr["user_name"];
		$this->first=$userInfoArr["first"];
		$this->last=$userInfoArr["last"];
		$this->email=$userInfoArr["email"];
		$this->departmentId=$userInfoArr["department_id"];
		$this->groupId=$userInfoArr["group_id"];
		$this->rateid=$userInfoArr["rate_id"];
		$this->statusid=$userInfoArr["status_id"];
		$this->userRoleId= $userInfoArr["user_role_id"];
		$this->dateAdded = $userInfoArr["date_added"];
		$this->secureKey = $userInfoArr['secure_key'];
		$this->certified = $userInfoArr['certified'];
	}

	/**
	 * Update user into database based on changes made to this object
	 */
	 // TODO the db should be updated on *every* set function, not just when this update function is called.
	public function update()
	{
		$queryUpdateUser = "UPDATE users SET
							user_name=:user_name,
							first=:first,
							last=:last,
							email=:email,
							department_id=:department_id,
							group_id=:group_id,
							rate_id=:rate_id,
							status_id=:status_id,
							user_role_id=:user_role_id,
							certified=:certified
							WHERE id=:user_id";
		$updateUserPrep = $this->db->prepare($queryUpdateUser);
		return $updateUserPrep->execute(array(':user_name'=>$this->username,':first'=>$this->first,':last'=>$this->last,':email'=>$this->email,':department_id'=>$this->departmentId,':group_id'=>$this->groupId,':rate_id'=>$this->rateid,':status_id'=>$this->statusid,':user_role_id'=>$this->userRoleId,':certified'=>$this->certified?1:0,':user_id'=>$this->userId));
	}

	/**Check if a user exists by netid
	 * @param $username
	 * @return int
	 */
	public static function exists($db,$username)
	{
		$queryUserName = "SELECT id FROM users WHERE user_name = :user_name";
		$userName = $db->prepare($queryUserName);
		$userName->execute(array(":user_name"=>$username));
		$userNameArr = $userName->fetch(PDO::FETCH_ASSOC);

		if($userName->rowCount() > 0)
		{
			return $userNameArr["id"];
		}
		else
		{
			return 0;
		}

	}

	public function hasAccessTo($deviceId){
		if($this->isAdmin()){ // Admins can access everything
			return true;
		} else {
			$query = "SELECT * FROM access_control WHERE device_id=:resource_id AND user_id=:user_id LIMIT 1";
			$stmt = $this->db->prepare($query);
	        $stmt->execute(array(":resource_id" => $deviceId, ":user_id" => $this->getId()));
	        $result = $stmt->fetch(PDO::FETCH_ASSOC);
	        return $result !== false;
		}
	}

	public function giveAccessTo($deviceId){
		$query = "INSERT INTO access_control (user_id, device_id) VALUES (:userid,:deviceid)";
		$stmt = $this->db->prepare($query);
		if( $stmt->execute(array(":userid"=>$this->getId(),":deviceid"=>$deviceId)) ){
			log::log_message("Gave user '".$this->getUsername()."' access to device $deviceId");
		}
	}
	public function removeAccessTo($deviceId){
		$query = "DELETE FROM access_control WHERE user_id=:userid AND device_id=:deviceid LIMIT 1";
		$stmt = $this->db->prepare($query);
		if( $stmt->execute(array(":userid" => $this->getId(), ":deviceid" => $deviceId)) ){
			log::log_message("Removed access to device $deviceId for user '".$this->getUsername()."'");
		}
	}

	/**
	 * Update security key for user
	 */
	public function updateSecureKey()
	{
		$queryUpdateSecureKey = "UPDATE users SET secure_key=MD5(RAND()) WHERE id = :user_id";
		$updateSecureKey = $this->db->prepare($queryUpdateSecureKey);
		$updateSecureKey->execute(array(":user_id"=>$this->userId));

		$queryGetSecureKey = "SELECT secure_key FROM users WHERE id = :user_id";
		$secureKey = $this->db->prepare($queryGetSecureKey);
		$secureKey->execute(array(":user_id"=>$this->userId));
		$secureKeyArr = $secureKey->fetch(PDO::FETCH_ASSOC);
		$this->secureKey = $secureKeyArr['secure_key'];
	}

	/**List all users by id and username on the application
	 * @return array
	 */
	public static function getAllUsers($db) {
	   $queryAllUsers = "SELECT id, user_name FROM users ORDER BY user_name";
	   $allUsers = $db->prepare($queryAllUsers);
	   $allUsers->execute();
	   $allUsersArr = $allUsers->fetchAll(PDO::FETCH_ASSOC);
	   return $allUsersArr;
	}

	public static function getAllUsersFullInfo($db) {
		$queryAllUserInfo = "SELECT u.first, u.last, u.email, u.department_id, u.group_id, g.group_name, uc.cfop, d.department_name, u.date_added, (select max(`stop`) from `session` where user_id=u.`id`) as last_login, CONCAT(u.last, ', ', u.first) as full_name, s.statusname as status, u.id FROM users u LEFT JOIN user_cfop uc ON (uc.user_id = u.id AND uc.default_cfop=1) LEFT JOIN groups g ON (g.id=u.group_id) LEFT JOIN departments d ON (d.id=u.department_id) LEFT JOIN status s ON s.id=u.status_id";
		$allUserInfo = $db->prepare($queryAllUserInfo);
		$allUserInfo->execute();
		$allUserInfoArr = $allUserInfo->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i<count($allUserInfoArr); $i++){
			$allUserInfoArr[$i]['cfop'] = UserCfop::formatCfop($allUserInfoArr[$i]['cfop']);
			$allUserInfoArr[$i]['edit'] = '<a href="edit_users.php?user_id='.$allUserInfoArr[$i]['id'].'">Edit</a>';
		}

		return $allUserInfoArr;
	}

	public static function getActiveUsers($db,$startyear,$startmonth,$endyear,$endmonth)
	{
		$queryAllUserInfo = "SELECT u.first, u.last, u.id, u.user_name, u.email, u.department_id, u.group_id, g.group_name, d.department_name, CONCAT(u.last, ', ', u.first) as full_name
								from users u left join groups g on g.id=u.group_id left join departments d on d.id=u.department_id left join `session` s on s.user_id=u.id
								where u.`status_id`=5 and ((MONTH(start)>=:startmonth AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) AND ((MONTH(start)<=:endmonth AND YEAR(start)=:endyear) OR YEAR(start)<:endyear)
								group by u.user_name";
		$allUserInfo = $db->prepare($queryAllUserInfo);
		$allUserInfo->execute(array(':startyear'=>$startyear, ':startmonth'=>$startmonth, ':endyear'=>$endyear, ':endmonth'=>$endmonth));
		$allUserInfoArr = $allUserInfo->fetchAll(PDO::FETCH_ASSOC);

		return $allUserInfoArr;
	}

	/**Get all user roles
	 * @return mixed
	 */
	public static function getUserRoles($db)
	{
		$queryUserRoles = "SELECT * FROM user_roles";
		$userRoles = $db->prepare($queryUserRoles);
		$userRoles->execute();
		return $userRoles->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getUserStatusList($db)
	{
		$queryUserStatusList = "SELECT * FROM status WHERE type=:type";
		$userStatusList = $db->prepare($queryUserStatusList);
		$userStatusList->execute(array(':type'=>User::STATUS_TYPE_USER));
		$userStatusListArr = $userStatusList->fetchAll(PDO::FETCH_ASSOC);

		return $userStatusListArr;

	}

	public function addCFOP($cfop)
	{
		$this->userCfop->create($this->userId, $cfop, "");
	}

	public function getAllCFOPs()
	{
		return UserCfop::getAllCFOPs($db,$this->userId);
	}

	public function getDefaultCFOP(){
		$this->userCfop->loadDefaultCfop($this->userId);
		return $this->userCfop->getCfop();
	}

	public function setDefaultCFOP($defaultCfopId)
	{
		$this->userCfop->load($defaultCfopId);
		$this->userCfop->setAsDefaultCFOP();
	}

	public function isAdmin(){
		 return $this->getRoleId()==1;
	}
	public function isSupervisor(){
		 return $this->getRoleId()==2;
	}

	//Getters and setters
	public function getId()
	{
		return $this->userId;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function setUsername($username)
	{
		if($this->username != $username){
			log::log_message("Set username of user '".$this->username."' to '$username'");
			$this->username=$username;
		}
	}

	public function getFirstName()
	{
		return $this->first;
	}

	public function setFirstName($first)
	{
		if($this->first != $first){
			$this->first=$first;
			log::log_message("Set first name of user '".$this->username."' to '$first'");
		}
	}

	public function getLastName()
	{
		return $this->last;
	}

	public function setLastName($last)
	{
		if($this->last != $last){
			$this->last=$last;
			log::log_message("Set last name of user '".$this->username."' to '$last'");
		}
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		if($this->email != $email){
			$this->email = $email;
			log::log_message("Set email of user '".$this->username."' to '$email'");
		}
	}

	public function getDepartmentId()
	{
		return $this->departmentId;
	}

	public function setDepartmentId($departmentId)
	{
		if($this->departmentId != $departmentId){
			$this->departmentId = $departmentId;
			log::log_message("Set department of user '".$this->username."' to '$departmentId'");
		}
	}

	public function getGroupId()
	{
		return $this->groupId;
	}

	public function setGroupId($groupId)
	{
		if($this->groupId != $groupId){
			$this->groupId = $groupId;
			log::log_message("Set group of user '".$this->username."' to '$groupId'");
		}
	}

	public function getRateId()
	{
		return $this->rateid;
	}

	public function setRateId($rateId)
	{
		if($this->rateid != $rateId){
			$this->rateid = $rateId;
			log::log_message("Set rate of user '".$this->username."' to '$rateId'");
		}
	}

	public function getStatusId()
	{
		return $this->statusid;
	}

	public function setStatusId($statusid)
	{
		if($this->statusid != $statusid){
			$this->statusid = $statusid;
			log::log_message("Set status of user '".$this->username."' to '$statusid'");
		}
	}

	public function getRoleId()
	{
		return $this->userRoleId;
	}

	public function setRoleId($usertypeid)
	{
		if($this->userRoleId != $usertypeid){
			$this->userRoleId = $usertypeid;
			log::log_message("Set role of user '".$this->username."' to '$usertypeid'");
		}
	}

	public function getDateAdded()
	{
		return $this->dateAdded;
	}

	public function getLastLogin(){
		$query = "select max(`stop`) as last_login from `session` where user_id=?";
		$stmt = $this->db->prepare($query);
		$stmt->execute(array($this->userId));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row['last_login'];
	}

	public function getSecureKey()
	{
		return $this->secureKey;
	}

	public function isCertified()
	{
		return $this->certified;
	}
	public function setCertified($certified)
	{
		if($this->certified != $certified){
			$this->certified = $certified;
			if($certified){
				log::log_message("Certified user '".$this->username."'");
			} else {
				log::log_message("Un-certified user '".$this->username."'");
			}
		}
	}
}

?>
