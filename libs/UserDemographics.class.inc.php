<?php
class UserDemographics {

	const EDULEVEL_UNDERGRAD = "Undergraduate";
	const EDULEVEL_GRAD = "Graduate";
	const EDULEVEL_POSTDOC = "Postdoc";
	const EDULEVEL_FACULTY = "Faculty";
	const EDULEVEL_OTHER = "Other";

	const GENDER_FEMALE = "Female";
	const GENDER_MALE = "Male";
	const GENDER_OTHER = "Other";

	const UNDERREP_YES = "Yes";
	const UNDERREP_NO = "No";

	private $db;
	private $user_id = 0;
	private $edulevel = "";
	private $gender = "";
	private $underrep = "";

	/**
	 * UserDemographics constructor.
	 * @param PDO $db
	 * @param null|integer $id
	 */
	public function __construct(PDO $db, $id = null) {
		$this->db = $db;

		if($id !== null){
			$this->load($id);
		}
	}

	/**
	 * Loads the demographic info for the user with the given id into the object
	 * @param integer $id user id to load
	 * @return bool whether load was successful
	 */
	public function load($id){
		$sql = "SELECT * FROM user_demographics WHERE user_id=:user_id LIMIT 1";
		$params = array(":user_id"=>$id);
		$query = $this->db->prepare($sql);

		if($query->execute($params)) {
			$result = $query->fetch(PDO::FETCH_ASSOC);
			$this->user_id = $id;
			$this->edulevel = $result['edu_level'];
			$this->gender = $result['gender'];
			$this->underrep = $result['underrepresented'];
			return true; 
		} 
		return false;
	}

	/**
	 * Updates the database with the current values stored in the object
	 * @return bool
	 */
	public function update($edu_level = "",$gender = "" ,$underrep = ""){
		$sql = "INSERT INTO user_demographics (user_id,edu_level,gender,underrepresented) ";
		$sql .= "VALUES(:id,:edu_level,:gender,:underrep) ";
		$sql .= "ON DUPLICATE KEY UPDATE user_id=:id, edu_level=:edu_level, gender=:gender, underrepresented=:underrep";
		$params = array(':id'=>$this->user_id, 
			':edu_level'=>$edu_level, 
			':gender'=>$gender, 
			':underrep'=>$underrep);
		$query = $this->db->prepare($sql);
		$result = $query->execute($params);
		if ($result) {
			$this->load($this->user_id);
		}
	}

	
	// Getters and Setters
	/**
	* @return int
	*/
	public function getUserId() {
		return $this->user_id;
	}

	/**
	* @return string
	*/
	public function getEdulevel() {
		return $this->edulevel;
	}


	/**
	* @return string
	*/
	public function getGender() {
		return $this->gender;
	}

	/**
	* @return string
	*/
	public function getUnderrep() {
		return $this->underrep;
	}

	public static function allEduLevels(){
		return array(self::EDULEVEL_UNDERGRAD,
			self::EDULEVEL_GRAD,
			self::EDULEVEL_POSTDOC,
			self::EDULEVEL_FACULTY,
			self::EDULEVEL_OTHER
		);
	}

	public static function allGenders(){
		return array(self::GENDER_FEMALE,
			self::GENDER_MALE,
			self::GENDER_OTHER
		);
	}

	public static function allUnderrepOptions(){
		return array(self::UNDERREP_YES,
			self::UNDERREP_NO);
	}

	public static function getDemographics($db,$start = 0,$count = 0) {
		$sql = "SELECT users.id,users.user_name,users.email,users.first,users.last, ";
		$sql .= "user_demographics.edu_level, user_demographics.gender,user_demographics.underrepresented ";
		$sql .= "FROM users ";
		$sql .= "LEFT JOIN user_demographics ON user_demographics.user_id=users.id ";
		$sql .= "WHERE users.status=:active ORDER BY users.user_name ASC";
		if ($count) {
			$sql .= " LIMIT " . $start . "," . $count;
		}
		$query = $db->prepare($sql);
		$params = array(':active'=>User::ACTIVE);
		$query->execute($params);
		return $query->fetchAll(PDO::FETCH_ASSOC);


	}
}
