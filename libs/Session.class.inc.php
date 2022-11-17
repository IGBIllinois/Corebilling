<?php

/**
 * Class Session
 * Used to keep create,delete and update user instrument sessions
 *
 */
class Session {
	
	private $db;
	private $sessionId = 0;
	private $userId = 0;
	private $start = "";
	private $stop = "";
	private $status = "";
	private $deviceId = 0;
	private $elapsed = 0;
	private $rateId = 0;
	private $description = "";
	private $cfopId = 0;
	private $rate;
	private CONST SESSION_TIMEOUT = 15;

	public function __construct(PDO $db) {
		$this->db = $db;

	}

	public function __destruct() {

	}

    /**Session tracker keeps track of session on each device
     * Used in session.php to track how long a person has been logged
     * @param $deviceId
     * @param $userId
     */
    public static function trackSession($db, $deviceId, $userId,$ipaddress,$json = "{}") {
        if ( $userId > 0 ) {
		$queryOpenSession = "SELECT id FROM session ";
		$queryOpenSession .= "WHERE user_id=:user_id AND device_id=:device_id ";
		$queryOpenSession .= "AND (TIMESTAMPDIFF(minute,stop,NOW()) < " . self::SESSION_TIMEOUT . ") ";
		$queryOpenSession .= "ORDER BY id DESC";

            $openSession = $db->prepare($queryOpenSession);
            $openSession->execute(array(':device_id' => $deviceId, ':user_id' => $userId));
            $openSessionArr = $openSession->fetch(PDO::FETCH_ASSOC);

		$device = new Device($db);
                $device->load($deviceId);
                $user = new User($db);
                $user->load($userId);
		$device_log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_device_log());

            if ( $openSessionArr ) {
                $queryUpdateSession = "update session set stop=NOW(), elapsed=TIMESTAMPDIFF(minute,start,NOW()) where id =:id";
                $updateSession = $db->prepare($queryUpdateSession);
                $updateSession->execute(array(':id' => $openSessionArr['id']));
            } else {
			
		$device_log->send_log("Device: " . $device->getFullName() . " - Opening session for user " . $user->getUsername());
                $userCfop = new UserCfop($db);
                $defaultCfopId = $userCfop->loadDefaultCfop($userId);
                $queryStartSession = "insert into session (user_id,device_id,start,stop,rate,rate_type_id,min_use_time,cfop_id,rate_id)
				                        select
				                          :user_id,
				                          :device_id,
				                          NOW(),
				                          NOW(), rate, rate_type_id, min_use_time,:default_cfop_id, rate_id from device_rate
				                          where device_id=:device_id
				                          and rate_id=(select rate_id from users
				                          where id=:user_id limit 1)";
                $startSession = $db->prepare($queryStartSession);
                $startSession->execute(
                    array(':user_id' => $userId, ':device_id' => $deviceId, ':default_cfop_id' => $defaultCfopId));
                $sessionId = $db->lastInsertId();
            }

            $queryUpdateDeviceUser = "update device set loggeduser=:loggeduser, lasttick=NOW(), ipaddress=:ipaddress, json=:json where id=:id";
            $updateDeviceUser = $db->prepare($queryUpdateDeviceUser);
            $updateDeviceUser->execute(array(':loggeduser' => $userId, ':id' => $deviceId, ':ipaddress' => $ipaddress,':json'=>$json));

        } else {
            $queryUpdateDeviceNonUser = "update device set loggeduser=0, lasttick=NOW(), ipaddress=:ipaddress,json=:json where id=:id";
            $updateDeviceNonUser = $db->prepare($queryUpdateDeviceNonUser);
            $updateDeviceNonUser->execute(array(':id' => $deviceId,':ipaddress'=>$ipaddress,':json'=>$json));
        }
	return true;
    }

	/**Create a new session in the database
	* @param $userId
	* @param $start
	* @param $stop
	* @param $status
	* @param $deviceId
	* @param $description
	* @param $cfop
	*/
	public function create($userId, $start, $stop, $status, $deviceId, $description, $cfop) {
		$this->userId = $userId;
		$this->start = $start;
		$this->stop = $stop;
		$this->status = $status;
		$this->deviceId = $deviceId;
		$this->description = $description;
		$this->cfopId = $cfop;

		$sql = "INSERT INTO session (user_id,start,stop,status,device_id,description,elapsed,cfop_id) ";
		$sql .= "VALUES(:user_id,:start,:stop,:status,device_id,:description,TIMESTAMPDIFF(minute,:start,:stop),:cfop_id)";

		$parameters = array(
                	':user_id' => $this->userId,
	                ':start' => $this->start,
        	        ':stop' => $this->stop,
                	':status' => $this->status,
	                ':device_id' => $this->deviceId,
        	        ':description' => $this->description,
                	':cfop_id' => $this->cfopId,
		);

		$query = $this->db->prepare($sql);
		$query->execute($paramaters);
		return $this->db->lastInsertId();
	}

	/**Load a session form the database into this object
	* @param $id
	*/
	public function load($id) {
		$sql = "SELECT * from session where id=:session_id LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(':session_id' => $id));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		$this->sessionId = $result["id"];
		$this->userId = $result["user_id"];
		$this->start = $result["start"];
		$this->stop = $result["stop"];
		$this->status = $result["status"];
		$this->deviceId = $result["device_id"];
		$this->elapsed = $result["elapsed"];
		$this->rate = $result["rate"];
		$this->description = $result["description"];
		$this->cfopId = $result["cfop_id"];
	}

	/**
	* Update the session with variables changed using the Setters
	*/
	public function update() {
		$sql = "UPDATE session SET user_id=:user_id,start=:start,stop=:stop,status=:status,";
		$sql .= "device_id=:device_id,elapsed=:elapsed,description=:description,cfop_id=:cfop_id,";
		$sql .= "rate=:rate ";
		$sql .= "WHERE id=:id LIMIT 1";
		$query = $this->db->prepare($sql);
		$parameters = array(
                	':user_id' => $this->userId,
	                ':start' => $this->start,
        	        ':stop' => $this->stop,
                	':status' => $this->status,
	                ':device_id' => $this->deviceId,
        	        ':elapsed' => $this->elapsed,
                	':description' => $this->description,
	                ':cfop_id' => $this->cfopId,
        	        ':rate' => $this->rate,
                	':id' => $this->sessionId,
		);

		$query->execute($parameters);
	}

	/**
	 * @param PDO $db
	 * @param $date
	 * @param $device
	 * @return mixed
	 */
	public static function getSessions($db, $start_date,$end_date,$device_id) {
		$sql = "SELECT u.user_name, g.group_name, s.device_id, d.device_name, s.start, s.stop ";
		$sql .= "FROM session s inner join users u on u.id=s.user_id ";
		$sql .= "INNER JOIN device d on d.id=s.device_id ";
		$sql .= "LEFT JOIN user_groups ug on u.id=ug.user_id ";
		$sql .= "LEFT JOIN `groups` g on g.id=ug.group_id ";
		$sql .= "WHERE d.id=:device_id and (DATE(s.start)=:start_date or DATE(s.stop)=:end_date)";
		$query = $db->prepare($sql);
		$parameters = array(":start_date" => $start_date->format("Y-m-d H:i:s"), 
			":end_date" => $end_date->format("Y-m-d H:i:s"),
			":device_id" => $device_id);
		$query->execute($parameters);
		return $query->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getId() {
		return $this->sessionId;
	}

	public function getUserId() {
		return $this->userId;
	}

	public function setUserId($userId) {
		$this->userId = $userId;
	}

	public function getStart() {
		return $this->start;
	}

	public function setStart($start) {
		$this->start = $start;
	}

	public function getStop() {
		return $this->stop;
	}

	public function setStop($stop) {
		$this->stop = $stop;
	}

	public function getStatus() {
		return $this->status;
	}

	public function setStatus($status) {
		$this->status = $status;
	}

	public function getDeviceId() {
		return $this->deviceId;
	}

	public function setDeviceId($deviceId) {
		$this->deviceId = $deviceId;
	}

	public function getElapsed() {
		return $this->elapsed;
	}

	public function setElapsed($elapsed) {
		$this->elapsed = $elapsed;
	}

	public function getRate() {
		return $this->rate;
	}

	public function setRate($rate) {
		$this->rate = $rate;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getCfopId() {
		return $this->cfopId;
	}

	public function setCfopId($cfopId) {
		$this->cfopId = $cfopId;
	}
}

?>
