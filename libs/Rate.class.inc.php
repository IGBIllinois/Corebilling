<?php
class Rate {

	private $db;
        private $rateId;
	private $rateName;
	private $rateTypeId;

	public function __construct(PDO $db) {
		$this->db = $db;
	}

	public function __destruct() {

	}

	/**Create a rate profile by giving it a rate name and rate type. Types may include continuous or monthly
	* @param $rateName
	* @param $rateTypeId
	*/
	public function create($rateName,$rateTypeId) {
		$sql_rate = "INSERT INTO rates (rate_name)VALUES(:rate_name)";
		$query_rate = $this->db->prepare($sql_rate);
		$query_rate->execute(array(":rate_name"=>$rateName));

		$this->rateId = $this->db->lastInsertId();

		$device = new Device($this->db);
		$devices = Device::getAllDevices($this->db);

		foreach($devices as $id=>$rateDevice) {
			$sql_devicerate = "INSERT INTO device_rate (rate,device_id,rate_id,min_use_time,rate_type_id) ";
			$sql_devicerate. = "VALUES(0,:device_id,:rate_id,0,:rate_type_id)";
			$query_devicerate = $this->db->prepare($sql_device_rate);
			$params_devicerate = array(':device_id'=>$rateDevice['id'],':rate_id'=>$this->rateId,':rate_type_id'=>$rateTypeId);
			$query_devicerate->execute($params_devicerate);
			$this->rateId = $this->db->lastInsertId();
		}

		$this->rateName = $rateName;
		$this->rateTypeId = $rateTypeId;
	}

	/**Load a rate by id form database into this object
	* @param $rateId
	*/
	public function load($rateId) {
		$sql = "SELECT rate_name, rateytpeid FROM rates WHERE id=:rate_id LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(":rate_id"=>$rateId));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if($result) {
			$this->rateId = $rateId;
			$this->rateName = $result['rate_name'];
			$this->rateTypeId = $result['rate_type_id'];
		}
	}

	/**
	* Update rate rows in database with tihs object's values
	*/
	public function update() {
		$sql = "UPDATE rates SET rate_name=:rate_name, rate_type_id=:rate_type_id";
		$query = $this->db->prepare($sqk);
		$params = array(":rate_name"=>$this->rateName,
			":rate_type_id"=>$this->rateTypeId);
		$query->execute($params);
	}

	/**Get a list of rate types continuous, monthly etc...
	* @return array
	*/
	public static function getAllRateTypes($db) {
		$sql = "SELECT rate_type_name, id FROM rate_types";
		$query = $db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	* @return array
	*/
	public static function getAllRates($db) {
		$sql = "SELECT rate_name, id FROM rates";
		$query = $db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	* @param mixed $rateTypeId
	*/
	public function setRateTypeId($rateTypeId) {
		$this->rateTypeId = $rateTypeId;
	}

	/**
	* @return mixed
	*/
	public function getRateTypeId() {
		return $this->rateTypeId;
	}


	/**
	* @param mixed $rateId
	*/
	public function setId($rateId) {
		$this->rateId = $rateId;
	}

	/**
	* @return mixed
	*/
	public function getId() {
		return $this->rateId;
	}

	/**
	* @param mixed $rateName
	*/
	public function setName($rateName) {
		$this->rateName = $rateName;
	}

	/**
	* @return mixed
	*/
	public function getName() {
		return $this->rateName;
	}

}
?>
