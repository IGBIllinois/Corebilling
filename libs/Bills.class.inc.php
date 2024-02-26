<?php

class Bills {

	private $db;
    	private $groupBy = 0;
	private $userId = 0;
	private $deviceId = 0;

	const CONTINUOUS_RATE = 1;
	const MONTHLY_RATE = 2;
	const GROUP_CFOP = 1;
	const GROUP_USER = 2;
	const GROUP_DEVICE = 3;
	const GROUP_DEVICE_USER = 4;

	public function __construct(PDO $db) {
		$this->db = $db;
	}

	public function __destruct() {

	}

	/**
	* Return one month of bills
	* @param $year
	* @param $month
	* @param $rateType
	* @return mixed
	*/
	public function GetMonthCharges($year, $month, $rateType) {
		$params = array();
		$sql = "SELECT s.id, uc.cfop,s.cfop_id, s.rate, s.user_id, s.device_id,  d.full_device_name, ";
		$sql .= "u.user_name, s.start, s.stop,CONCAT(u.first,' ',u.last) as full_name, s.description,r.rate_name, ";
		$sql .= "dr.min_use_time, dr.rate_type_id, de.department_name ";

		$sql_join = "FROM `session` s ";
		$sql_join .= "LEFT JOIN users u on u.id=s.user_id ";
		$sql_join .= "LEFT JOIN device_rate dr on dr.rate_id=u.rate_id and dr.device_id=s.device_id ";
		$sql_join .= "LEFT JOIN device d on d.id=s.device_id ";
		$sql_join .= "LEFT JOIN rates r on r.id=u.rate_id ";
		$sql_join .= "LEFT JOIN user_cfop uc ON uc.id=s.cfop_id ";
		$sql_join .= "LEFT JOIN departments de on de.id=u.department_id ";

		$sql_where = "WHERE MONTH(start)=:month AND YEAR(start)=:year AND dr.rate_type_id=:rate_type_id ";
		$params[':year'] = $year;
		$params[':month'] = $month;
		$params[':rate_type_id'] = $rateType;

		if ($this->userId) {
			$sql_where .= "AND s.user_id=:user_id ";
			$params[':user_id'] = $this->userId;
		}

		if ($this->deviceId) {
			$sql_where .= "AND s.device_id=:device_id ";
			$params[':device_id'] = $this->deviceId;
		}

		switch ($this->groupBy) {
			case self::GROUP_CFOP:
				$sql_where .= "GROUP BY s.cfop ";
				$sql .= ", s.elapsed";
				break;
			case self::GROUP_DEVICE:
				$sql .= ", SUM(s.elapsed) as elapsed ";
				$sql_where .= "GROUP BY s.device_id ";
				break;
			case self::GROUP_USER:
				$sql_where .= "GROUP BY s.user_id ";
				$sql .= ", s.elapsed ";
				break;
			default:
				$sql .= ", s.elapsed ";
		}
		$sql = $sql . $sql_join . $sql_where;
		$query = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$query->execute($params);
		return $query->fetchAll(PDO::FETCH_ASSOC);

	}
    
	public function GetMonthsCharges($startyear, $startmonth, $endyear, $endmonth, $rateType = null) {
		$params = array();
		$sql = "SELECT s.id, uc.cfop,s.cfop_id, s.rate, s.user_id, s.device_id,  d.full_device_name, ";
		$sql .= "u.user_name, s.start, s.stop,CONCAT(u.first,' ',u.last) as full_name, s.description,r.rate_name, ";
		$sql .= "dr.min_use_time, GROUP_CONCAT(g.group_name separator ', ') as group_name, dr.rate_type_id, de.department_name ";

		
		$sql_joins = "FROM `session` s ";
		$sql_joins .= "left join users u on u.id=s.user_id ";
		$sql_joins .= "left join device d on d.id=s.device_id ";
		$sql_joins .= "left join device_rate dr on (dr.device_id=d.id and dr.rate_id=u.rate_id) ";
		$sql_joins .= "left join user_groups ug on ug.user_id=u.id ";
		$sql_joins .= "LEFT JOIN groups g ON (g.id=ug.group_id) ";
		$sql_joins .= "left join rates r on r.id=s.rate_id ";
		$sql_joins .= "LEFT JOIN user_cfop uc ON uc.id=s.cfop_id ";
		$sql_joins .= "left join departments de on de.id=u.`department_id`"; 

		$sql_where = " WHERE ((MONTH(start)>=:startmonth AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) AND ((MONTH(start)<=:endmonth AND YEAR(start)=:endyear) OR YEAR(start)<:endyear)";
		if($rateType !== null){
			$sql_where .= " AND dr.rate_type_id=:rate_type_id";
			$params[':rate_type_id'] = $rateType;
		}
	
		$params[':startyear'] = $startyear;
		$params[':startmonth'] = $startmonth;
		$params[':endyear'] = $endyear;
		$params[':endmonth'] = $endmonth;

		if ($this->userId) {
			$sql_where .= " AND s.user_id=:user_id";
			$params[':user_id'] = $this->userId;
		}

		if ($this->deviceId) {
			$sql_where .= " AND s.device_id=:device_id";
			$params[':device_id'] = $this->deviceId;
		}

		switch ($this->groupBy) {
			case self::GROUP_CFOP:
				$sql .= ", s.elapsed";
				$sql_where .= " GROUP BY s.cfop";
				break;
			case self::GROUP_DEVICE:
				$sql .= ", SUM(s.elapsed) as elapsed";
				$sql_where .= " GROUP BY s.device_id";
				break;
			case self::GROUP_USER:
				$sql .= ", s.elapsed";
				$sql_where .= " GROUP BY s.user_id";
				break;
			case self::GROUP_DEVICE_USER:
				$sql .= ", SUM(s.elapsed) as elapsed";
				$sql_where .= " GROUP BY s.user_id, s.device_id";
				break;
			default:
				$sql .= ", s.elapsed";
				$sql_where .= " GROUP BY s.id";
		}
		$sql = $sql . $sql_joins . $sql_where;
		$query = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$query->execute($params);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}


	/**Return available months for billing
	* @return mixed
	*/
	public function GetAvailableBillingMonths() {
		$sql = "SELECT DISTINCT DATE_FORMAT(start,'%M %Y') AS mon_yr, ";
		$sql .= "MONTH(start) AS month, YEAR(start) AS year ";
		$sql .= "FROM session ORDER BY start DESC";
		$query = $this->db->prepare($sql);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**Receive an array of session ids, set each one to the user's default cfop
	* @param $sessionIdArray
	*/
	public function SetToDefaultCFOP($sessionIdArray) {
		//Load user cfop and session objects
		$userCfop = new UserCfop($this->db);
		$billSession = new Session($this->db);

		//Cycle through each session bill for the month
		foreach($sessionIdArray as $sessionId) {
			//Update each session with the user's default CFOP
			$billSession->load($sessionId);
			$userDefaultCfopId = $userCfop->loadDefaultCfop($billSession->getUserId());
			$billSession->setCfopId($userDefaultCfopId);
			$billSession->update();
		}
	}

	/**Get excel file from year month and rate type
	* @param $year
	* @param $month
	* @param $rateType
	*/
	public function GetExcelCharges($year, $month, $rateType) {
		$monthUsageArr = $this->GetMonthCharges($year, $month, $rateType);
		\IGBIllinois\report::create_excel_2007_report($monthUsageArr, "facilities_billing");
	}

	/**Calculate total to bill for given rate type
	* @param $elapsed
	* @param $rateTypeId
	* @param $rate
	* @param $min_use_time
	* @return mixed
	*/
	public function CalcTotal($elapsed, $rateTypeId, $rate, $min_use_time) {
		switch($rateTypeId) {
			case Bills::CONTINUOUS_RATE:
				$total = $this->CalcContinuous($elapsed,$rate,$min_use_time);
				break;
			case Bills::MONTHLY_RATE:
				$total = $this->CalcMonthly($elapsed,$rate,$min_use_time);
				break;
		}

		return $total;
	}

	/**Calculate continuous rate
	* @param $elapsed
	* @param $rate
	* @param $min_use_time
	* @return mixed
	*/
	private function CalcContinuous($elapsed,$rate,$min_use_time) {
		if($min_use_time > $elapsed) {
			$elapsed = $min_use_time;
		}

		return $elapsed * $rate;
	}

	/**Calculate monthly rate
	* @param $elapsed
	* @param $rate
	* @param $min_use_time
	* @return mixed
	*/
	private function CalcMonthly($elapsed, $rate, $min_use_time) {
		return $rate * 60;
	}

	/**Set which column you would like to group the bills by
	* Options available are GROUP_CFOP, GROUP_USER, GROUP_DEVICE
	* @param mixed $groupBy
	*/
	public function setGroupBy($groupBy) {
		$this->groupBy = $groupBy;
	}

	/**
	* @param mixed $deviceId
	*/
	public function setDeviceid($deviceId) {
		$this->deviceId = $deviceId;
	}

	/**
	* @param mixed $userId
	*/
	public function setUserId($userId) {
		$this->userId = $userId;
	}
}


?>
