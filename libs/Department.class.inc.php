<?php
class Department {

	private $db;   
	private $departmentName;
	private $departmentId;
	private $departmentCode;
	private $description;

	private $log_file = null;

	public function __construct(PDO $db) {
		$this->db = $db;
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
	}

	public function __destruct() {

	}

	/** Add a new department
	* @param $departmentName
	* @param $description
	*/
	public function create($departmentName, $description,$departmentCode = "") {
		$sql = "INSERT INTO departments (department_name, description, department_code) ";
		$sql .= "VALUES(:department,:description,:department_code)";
		$parameters = array(':department'=>$departmentName,
				':description'=>$description,
				':department_code'=>$departmentCode
		);
        	$query = $this->db->prepare($sql);
		$query->execute($parameters);
        	$departmentId = $this->db->lastInsertId();
	        $this->departmentName = $departmentName;
        	$this->description = $description;
	        $this->departmentId = $departmentId;
		$this->log_file->send_log("Added department " . $departmentName);
	}

	/**
	* Update department row in database with changes made to this object
	*/
	public function update() {
		$sql = "UPDATE departments SET department_name=:departmentName,description=:description ";
		$sql .= "WHERE id=:id LIMIT 1";
		$query = $this->db->prepare($sql);
		$result = $query->execute(array(':departmentName'=>$this->departmentName,':description'=>$this->description,':id'=>$this->departmentId));
		return $result;
	}

	/** Load department by id from database into this object
	* @param $id
	*/
	public function load($id) {
		$sql= "SELECT department_name,id,department_code FROM departments WHERE id=:id LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(':id'=>$id));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		$this->departmentName = $result["department_name"];
		$this->departmentCode = $result["department_code"];
		$this->departmentId = $result["id"];
	}

	/** Get a list of available departments
	* @return array
	*/
	public static function getAllDepartments($db) {
		$sql = "SELECT department_name,id FROM departments ORDER BY department_name";
		$query = $db->prepare($sql);
		$query->execute(array());
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/** Check if a department name already exists in the database
	* @param $departmentName
	* @return bool
	*/
	public static function exists($db,$departmentName) {
		$sql = "SELECT COUNT(1) as department_exists FROM departments WHERE department_name=:department_name";
		$query = $db->prepare($sql);
		$query->execute(array(':department_name'=>$departmentName));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		return $result['department_exists'];
	}

	/**Get all members of this department
	* @return array
	*/
	public function getMembers() {
		if($this->getDepartmentId()) {
			$sql = "SELECT * FROM users WHERE department_id=:department_id AND users.status=:status_id ORDER BY user_name";
			$query = $this->db->prepare($sql);
			$query->execute(array(":department_id"=>$this->getDepartmentId(),":status_id"=>User::ACTIVE));
			return $query->fetchAll(PDO::FETCH_ASSOC);
		}
		return array();
	}

	/**
	* @param mixed $description
	*/
	public function setDescription($description) {
		if($this->description != $description){
			$this->description = $description;
			$this->log_file->send_log("Set description of department '".$this->departmentName."' to '$description'");
		}
	}

	/**
	* @return mixed
	*/
	public function getDescription() {
		return $this->description;
	}

	/**
	* @return mixed
	*/
	public function getDepartmentId() {
		return $this->departmentId;
	}

	/**
	* @param mixed $departmentName
	*/
	public function setDepartmentName($departmentName) {
		if($this->departmentName != $departmentName){
			$this->log_file->send_log("Set name of department '".$this->departmentName."' to '$departmentName'");
			$this->departmentName = $departmentName;
		}
	}

	/**
	* @return mixed
	*/
	public function getDepartmentName() {
		return $this->departmentName;
	}

	public function getDepartmentCode() {
		return $this->departmentCode;
	}
} 
