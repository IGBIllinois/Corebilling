<?php
class Device
{
	const STATUS_ONLINE = 1;
	const STATUS_REPAIR = 2;
	const STATUS_DONOTTRACK = 3;
	const STATUS_OFFLINE = 4;
	const HARDDRIVE_WARNING = 80;
	const MINUTES = 60;
	private $db;
	private $deviceId = 0;
	private $shortName = "";
	private $full_name = "";
	private $location = "";
	private $description = ""; 
	private $status = 1;
	private $deviceToken;
	private $unauthorizedUser;
	private $loggedUser;
	private $ldap_group;
	private $ipaddress;
	private $log_file = null;
	private $json = null;
	private $lasttick;

	public function __construct(PDO $db) {
		$this->db = $db;
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
	}
	
	public function __destruct() {

	}

	/**Create a new Device object and insert it into the database
	* @param $dn
	* @param $name
	* @param $location
	* @param $description
	* @param $status
	*/
	public function create($dn, $name,$location, $description, $status) {
		$this->shortName = $dn;
		$this->full_name = $name;
		$this->location = $location;
		$this->description = $description;
		$this->status = $status;
		$this->deviceToken = md5(uniqid(mt_rand(), true));
		// TODO this sql query is not running
		$sql_add = "INSERT INTO device (device_name,location,description,full_device_name,status_id,device_token) ";
		$sql_add .= "VALUES(:device_name,:location,:description,:full_device_name,:status_id,:device_token)";
        	$query_add = $this->db->prepare($sql_add);
		$parameters = array(":device_name"=>$dn,
				":location"=>$location,
				":description"=>$description,
				":full_device_name"=>$name,
				":status_id"=>$status,
				":device_token"=>$this->deviceToken);
		$query_add->execute($parameters);
		$this->log_file->send_log("Created Device - " . $dn . " in location " . $location);
		$this->deviceId = $this->db->lastInsertId();

        	//Add device rates rows to device rates table with default value of 0 for all values
	        $ratesArr = Rate::getAllRates($this->db);
        	foreach ($ratesArr as $rateInfo) {
			$sql_rates = "INSERT INTO device_rate (rate,device_id,rate_id, min_use_time, rate_type_id) ";
			$sql_rates .= "VALUES(0,:device_id,:rate_id,0,0)";
			$query_rates = $this->db->prepare($sql_rates);
			$query_rates->execute(array(":device_id"=>$this->deviceId,":rate_id"=>$rateInfo["id"]));
        	}
        
		return true;

	}

	/** Load device from database into object given an authKey or id
	* @param $id
	* @param int $authKey
	*/
	public function load($id,$authKey=0) {
		$sql = "SELECT * FROM device WHERE id=:id OR (device_token=:device_token AND device_token!=\"0\") LIMIT 1";
        	$query = $this->db->prepare($sql);
		$query->execute(array(':id'=>$id,':device_token'=>$authKey));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if($result) {
			$this->shortName = $result["device_name"];
			$this->full_name = $result["full_device_name"];
			$this->location = $result["location"];
			$this->description = $result["description"];
			$this->status = $result["status_id"];
			$this->deviceToken = $result["device_token"];
			$this->loggedUser = $result["loggeduser"];
			$this->unauthorizedUser = $result["unauthorized"];
			$this->deviceId = $result['id'];
			$this->ldap_group = $result['ldap_group'];
			$this->ipaddress = $result['ipaddress'];
			$this->json = json_decode($result['json'],true);
			$this->lasttick = $result['lasttick'];
			return true;
        	}
		return false;
	}


	/**
	* Update device object in database with getters and setters
	*/
	public function update() {
		$sql = "UPDATE device SET device_name=:device_name, ";
		$sql .= "location=:location,description=:description, ";
		$sql .= "full_device_name=:full_device_name, status_id=:status_id, ";
		$sql .= "ldap_group=:ldap_group WHERE id=:id LIMIT 1";
		$query = $this->db->prepare($sql);
		$parameters = array(":device_name"=>$this->shortName,
			":location"=>$this->location,
			":description"=>$this->description,
			":full_device_name"=>$this->full_name,
			":status_id"=>$this->status,
			":id"=>$this->deviceId,
			":ldap_group"=>$this->ldap_group);
		return $query->execute($parameters);
	}

	public function updateLastTick($username="",$ipaddress = "",$json = "{}") {
		if($username==""){
			$loggeduser = 0;
		} else {
			$loggeduser = -1;
		}
		$sql = "UPDATE device SET lasttick=NOW(), loggeduser=:loggeduser, ";
		$sql .= "unauthorized=:username, ipaddress=:ipaddress,json=:json ";
		$sql .= "WHERE id=:id LIMIT 1";
		$query = $this->db->prepare($sql);
		$parameters = array(':username'=>$username,
				':id'=>$this->deviceId,
				':loggeduser'=>$loggeduser,
				':ipaddress'=>$ipaddress,
				':json'=>$json
		);
		return $query->execute($parameters);
	}

	/**Check if device with deviceName alrady exists
	* @param $deviceName
	* @return int
	*/
	public function exists($deviceName) {
		$sql = "SELECT COUNT(1) AS exists FROM device WHERE device_name=:device_name LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(':device_name'=>$deviceName));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		return $result['exists'];
	}

	/**List all devices
	 * @param PDO $db
	 * @return array
	 */
	public static function getAllDevices($db) {
		$sql = "SELECT id, device_name, full_device_name, status_id FROM device ORDER BY full_device_name";
		$query = $db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * @param PDO $db
	 * @param int $id
	 * @return mixed
	 */
	public static function getDevicesInSameRoom($db, $id){
		$sql = "select id from device where location = (select location from device where id=:id limit 1) and id != :id";
		$query = $db->prepare($sql);
		$query->execute(array(':id'=>$id));
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getAllDevicesStatus($db) {
		$sql = "SELECT d.full_device_name, d.ipaddress, d.location, u.user_name, ";
		$sql .= "d.loggeduser,u.first, u.last, TIMESTAMPDIFF(SECOND, lasttick, NOW()) AS lastseen, ";
		$sql .= "unauthorized FROM users u ";
		$sql .= "RIGHT JOIN device d ON u.id=d.loggeduser ";
		$sql .= "WHERE d.status_id=:status_online OR d.status_id=:status_repair ORDER BY d.full_device_name";
		$query = $db->prepare($sql);
		$parameters = array(':status_online'=>self::STATUS_ONLINE,':status_repair'=>self::STATUS_REPAIR);
		$query->execute($parameters);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**Get rates list for device
	* @return array
	*/
	public function getRates() {
		$sql = "SELECT ROUND(dr.rate * :minutes,2) as rate, dr.id, dr.rate_id, dr.min_use_time, r.rate_name, dr.rate_type_id ";
		$sql .= "FROM device_rate dr, rates r ";
		$sql .= "WHERE r.id=dr.rate_id AND dr.device_id=:device_id";
		$query = $this->db->prepare($sql);
		$parameters = array(":device_id"=>$this->deviceId,
			":minutes"=>self::MINUTES);
                $query->execute($parameters);	
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**Update this device's rate
	* @param $rateId
	* @param $rate
	* @param $minTime
	* @param $rateTypeId
	*/
	public function updateRate($rateId, $rate, $minTime, $rateTypeId) {
		$rate_per_second = $rate / self::MINUTES;
		$sql = "UPDATE device_rate SET rate=:rate, min_use_time=:mintime, rate_type_id=:rate_type_id ";
		$sql .= "WHERE rate_id=:rate_id AND device_id=:device_id";
		$query = $this->db->prepare($sql);
		$parameters = array(":rate"=>$rate_per_second,":mintime"=>$minTime,":rate_id"=>$rateId,":device_id"=>$this->deviceId,":rate_type_id"=>$rateTypeId);
		return $query->execute($parameters);
	}

	public static function deviceStatusList($db) {
		$sql = "SELECT * FROM device_status";
		$query = $db->prepare($sql);
		$query->execute();
        	return $query->fetchAll(PDO::FETCH_ASSOC);

	}

	//Getters and setters for device
	public function setDeviceId($id){
		if($this->deviceId != $id){
			$this->deviceId = $id;
		}
	}

	public function getId() {
		return $this->deviceId;
	}

	public function setShortName($dn) {
		if($this->shortName != $dn){
			$this->log_file->send_log("Set short name of device '".$this->shortName."' to '$dn'");
			$this->shortName = $dn;
		}
	}

	public function getShortName() {
		return $this->shortName;
	}

	public function getIPAddress() {
		return $this->ipaddress;
	}

	public function getHostname() {
		if (filter_var($this->getIPAddress(), FILTER_VALIDATE_IP)) {
			return gethostbyaddr($this->getIPAddress());
		}
		return false;

	}
	public function getLDAPGroup() {
		if (LDAPMAN_API_ENABLED) {
                        return LDAPMAN_DEVICE_PREFIX . $this->shortName;
                }
                return false;
	
	}

	public function setLDAPGroup($ldap_group) {
		if($this->ldap_group != $ldap_group){
			$this->ldap_group = $ldap_group;
			$this->log_file->send_log("Set LDAP group of device '".$this->shortName."' to $ldap_group");
		}
	}

	public function setFullName($name) {
		if($this->full_name != $name){
			$this->full_name = $name;
			$this->log_file->send_log("Set full name of device '".$this->shortName."' to '$name'");
		}
	}
	
	public function getFullName() {
		return $this->full_name;
	}

	public function setLocation($location) {
		if($this->location != $location){
			$this->location = $location;
			$this->log_file->send_log("Set location of device '".$this->shortName."' to $location");
		}
	}

	public function getLocation() {
		return $this->location;
	}

	public function getStatus() {
		return $this->status;
	}	

	public function setStatus($status) {
		if($this->status != $status){
			$this->status = $status;
			$this->log_file->send_log("Set status of device '".$this->shortName."' to $status");
		}
	}

	public function setDescription($description) {
		if($this->description != $description){
			$this->description = $description;
			$this->log_file->send_log("Set description of device '".$this->shortName."' to '$description'");
		}
	}

	public function getDescription() {
		return $this->description;
	}

	public function getDeviceToken() {
		return $this->deviceToken;
	}

	public function regenerateToken() {
		$new_token = md5(uniqid(mt_rand(), true));
		$sql = "UPDATE device SET device_token=:device_token WHERE id=:device_id LIMIT 1";
		$query = $this->db->prepare($sql);
		$parameters = array(':device_token'=>$new_token,':device_id'=>$this->getId());
		$query->execute($parameters);
		if ($query->rowCount())	{
			$this->deviceToken = $new_token;
			$this->log_file->send_log("Device Auth Token updated for " . $this->shortName);
			return true;
		}
		return false;
	}
	public function getClientVersion() {
		if (isset($this->json['version'])) {
			return $this->json['version'];
		}
		return false;
	}
	public function getOperatingSystem() {
		if (isset($this->json['os'])) {
			return $this->json['os'];
		}
		return false;
	}
	public function getFastUserSwitchingEnabled() {
		if (isset($this->json['user_switching'])) {
			return $this->json['user_switching'];
		}
		return null;
	}
	public function getHardDrives() {
		if (isset($this->json['hard_drives'])) {
			return $this->json['hard_drives'];
		}
		return array();
	}
	public function getWindowsComputerName() {
		if (isset($this->json['computer_name'])) {
			return $this->json['computer_name'];
		}
		return false;
	}

	public function getLastTick() {
		return $this->lasttick;
			
	}
}
?>
