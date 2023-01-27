<?php

class data_dir {


	private $db;
	private $id;
	private $group_id;
	private $user_id;
	private $group;
	private $directory;
	private $time_created;
	private $enabled;
	private $exists;
	private $owner;
	private $log_file = null;

	const precentile = 0.95;
	const kilobytes_to_bytes = "1024";

	public function __construct($db,$data_dir_id = 0) {
		$this->db = $db;
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
		if ($data_dir_id != 0) {
			$this->get_data_dir($data_dir_id);
		}

	}

	public function __destruct() {
	}
	
	public function create($db,$group_id,$directory,$netid) {
		$directory = self::format_directory($directory);
		$error = false;

		if (self::data_dir_exists($db,$directory)) {
			$error = true;
			throw new Exception("Directory " . $directory . " is already in the database");
			
		}

		if ($error) {
			return array('RESULT'=>false,"MESSAGE"=>$message);
		}
		else {
			$user_id = User::getIDByUsername($db,$netid);
			$sql = "INSERT INTO data_dir(data_dir_group_id,data_dir_path,data_dir_user_id) ";
			$sql .= "VALUES(:group_id,:directory,:user_id)";
			$parameters = array(':group_id'=>$group_id,
					':directory'=>$directory,
					':user_id'=>$user_id,
					);
			$query = $db->prepare($sql);
			$query->execute($parameters);
			$id = $db->lastInsertId();
			if ($id) {
				return $id;
			}
			return false;	
		}
	}
	
	public function get_data_dir_id() {
		return $this->id;
	}
	
	public function get_directory() {
		return $this->directory;
	}

	public function get_group_id() {
		return $this->group_id;
	}

	public function get_group() {
		return $this->group;
	}
	public function get_user_id() {
		return $this->user_id;
	}
	public function get_owner() {
		return $this->owner;
	}
	public function get_enabled() {
		return $this->enabled;
	}
	public function get_time_created() {
		return $this->time_created;
	}

	public function get_dir_exists() {
		return $this->exists;
	}

	public function enable() {
                $sql = "UPDATE data_dir SET data_dir_enabled='1' ";
                $sql .= "WHERE data_dir_id='" . $this->get_data_dir_id() . "' LIMIT 1";
                $result = $this->db->non_select_query($sql);
                if ($result) {
                        $this->enabled = 1;
                }
                return $result;


	}
	public function disable() {
		$error = false;
		$message = "";
		$exists = false;
		try {
			$exists = self::remote_dir_exists($this->get_directory());
		}
		catch (Exception $e) {
			throw $e;
		}

		if ($exists) {
                        throw new Exception("Unable to delete directory.  Directory " . $this->get_directory() . " still exists.");
                }
		else {
			$sql = "UPDATE data_dir SET data_dir_enabled='0',data_dir_exists='0' ";
			$sql .= "WHERE data_dir_id=:data_dir_id LIMIT 1";
			$query = $this->db->prepare($sql);
			$result = $query->execute(array(':data_dir_id'=>$this->get_data_dir_id()));
			
			if ($result) {
				$this->enabled = 0;
				$this->log_file->send_log("Successfully disabled data directory " . $this->get_directory());
				return true;
			}
		}
		return false;

	}
	
	private function get_data_dir($data_dir_id) {
		$sql = "SELECT data_dir.*, groups.group_name, groups.netid as owner FROM data_dir ";
		$sql .= "LEFT JOIN groups ON groups.id=data_dir.data_dir_group_id ";
		$sql .= "WHERE data_dir_id=:data_dir_id ";
		$sql .= "LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute(array(':data_dir_id'=>$data_dir_id));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			$this->id = $result['data_dir_id'];
			$this->directory = $result['data_dir_path'];
			$this->time_created = $result['data_dir_time'];
			$this->group_id = $result['data_dir_group_id'];
			$this->group = $result['group_name'];
			$this->enabled = $result['data_dir_enabled'];
			$this->exists = $result['data_dir_exists'];
			$this->owner = $result['owner'];
			$this->user_id = $result['data_dir_user_id'];
			return true;
		}
		return false;
	}

	private static function format_directory($directory) {
		if (strrpos($directory,"/") == strlen($directory) -1) {
			return substr($directory,0,strlen($directory)-1);
		}
		else {
			return $directory;
		}

	}


	private static function data_dir_exists($db, $directory) {
		$sql = "SELECT count(1) as count FROM data_dir ";
		$sql .= "WHERE data_dir_path=:directory ";
		$sql .= "AND data_dir_enabled='1'i LIMIT 1";
		$query = $db->prepare($sql);
		$query->execute(array(':directory'=>$directory));	
		$result = $query->fetch(PDO::FETCH_ASSOC);

		if ($result['count']) {
			return true;
		}
		return false;

	}
	
	private function check_sub_dir($directory) {
		$directory = substr($directory,1,strlen($directory));
		$directories = explode("/",$directory);

		for ($i=0; $i < count($directories); $i++) {
			$sub_dir = "";

			for ($j=0; $j<=$i; $j++) {
				$sub_dir .= "/" . $directories[$j];
			}
			if($this->data_dir_exists($sub_dir)) {
				return true;
			}
		}
		return false;

	}
	
	public function add_usage($bytes) {

                $data_cost = new data_cost($this->db);
		$sql = "INSERT INTO data_usage(data_usage_data_dir_id,data_usage_bytes) ";
		$sql .= "VALUES(:data_usage_data_dir_id,:data_usage_bytes) ";
                $parameters = array(':data_usage_data_dir_id'=>$this->get_data_dir_id(),
                                ':data_usage_bytes'=>$bytes);
		$query = $this->db->prepare($sql);
		$query->execute($parameters);
		$insert_id = $this->db->lastInsertId();
		if ($insert_id) {
			return $insert_id;

		}
		return false;

	}	

	public function get_usage($month,$year) {
		$sql = "SELECT * FROM data_usage ";
		$sql .= "LEFT JOIN data_dir ON data_dir_id=data_usage_data_dir_id ";
		$sql .= "WHERE MONTH(data_usage_time)=:month ";
		$sql .= "AND YEAR(data_usage_time)=:year ";
		$sql .= "AND data_usage_data_dir_id=:data_dir_id ";
		$sql .= "ORDER BY data_usage_bytes DESC";
		$parameters = array(':month'=>$month,
			':year'=>$year,
			':data_dir_id'=>$this->get_data_dir_id());
		$query = $this->db->prepare($sql);
		$query->execute($parameters);
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
		if (count($result) < $days_in_month) {
			$diff = $days_in_month - count($result);
			$empty_array = array();
			for ($i=0;$i<$diff;$i++) {
				array_push($empty_array,array('data_usage_bytes'=>0));
			}
			$result = array_merge($empty_array,$result);	
		}
		$slice = round(count($result)*self::precentile,0,PHP_ROUND_HALF_DOWN);
		return array_slice($result,0,$slice);
	}

	public function add_data_bill($month,$year,$bytes) {
		$bill_date = $year . "-" . $month . "-01 00:00:00";
		$sql = "SELECT count(1) as count ";
		$sql .= "FROM data_bill ";
		$sql .= "WHERE data_bill.data_bill_date=:bill_date ";
		$sql .= "AND data_bill_data_dir_id=:data_dir_id ";
		$sql .= "LIMIT 1";
		$parameters = array(':bill_date'=>$bill_date,
				':data_dir_id'=>$this->get_data_dir_id());
		$query = $this->db->prepare($sql);
		$query->execute($parameters);
		$check_exists = $query->fetch(PDO::FETCH_ASSOC);
		$result = true;
		$insert_id = 0;
		if ($check_exists['count']) {
			$result = false;
			$message = "Data Bill: Directory: " . $this->get_directory() . " Bill already calculated";
		}
		else {
			$user = new User($this->db);
			$user->load($this->get_user_id());
			
        	        $data_cost = new data_cost($this->db);
			$total_cost = $data_cost->calculate_cost($bytes);
			$billed_cost = $total_cost;
			if ($user->getDefaultCFOPID() != '') {
				$billed_cost = $total_cost;
			}
			$insert_sql = "INSERT INTO data_bill(data_bill_data_dir_id,data_bill_data_cost_id,data_bill_group_id,";
			$insert_sql .= "data_bill_user_id,data_bill_cfop_id,data_bill_avg_bytes,data_bill_total_cost,data_bill_billed_cost,data_bill_date) ";
			$insert_sql .= "VALUES(:data_dir_id,:data_cost_id,:group_id,:user_id,:cfop_id,:bytes,:total_cost,:billed_cost,:bill_date)";
			
        	        $parameters = array(':data_dir_id'=>$this->get_data_dir_id(),
					':data_cost_id'=>$data_cost->get_data_cost_id(),
                	                ':group_id'=>$this->get_group_id(),
					':user_id'=>$this->get_user_id(),
                        	        ':cfop_id'=>$user->getDefaultCFOPID(),
	                                ':bytes'=>$bytes,
					':total_cost'=>$total_cost,
					':billed_cost'=>$billed_cost,
					':bill_date'=>$bill_date
	                                );
			$query = $this->db->prepare($insert_sql);
			$query->execute($parameters);
        	        $insert_id = $this->db->lastInsertId();
			return true;
		}
		return false;
	}

	public function get_data_bill($month,$year) {
		$sql = "SELECT data_bill.*,data_cost.data_cost_value as cost, user_cfop.cfop as cfop FROM data_bill ";
		$sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_bill.data_bill_data_cost_id ";
		$sql .= "LEFT JOIN user_cfop ON user_cfop.id=data_bill_cfop_id ";
		$sql .= "WHERE data_bill_data_dir_id=:data_dir_id ";
		$sql .= "AND MONTH(data_bill_date)=:month AND YEAR(data_bill_date)=:year LIMIT 1";
		$parameters = array(':data_dir_id'=>$this->get_data_dir_id(),
				':month'=>$month,
				':year'=>$year);
		$query = $this->db->prepare($sql);
		$query->execute($parameters);
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result) {

			return $result;

		}
		return false;


	}
	public function update_dir_exists($exists) {
		if (!is_bool($exists)) { 
			return false;
		}
		if ($this->get_dir_exists() == $exists) {
			return true;
		}
		else {
			$sql = "UPDATE data_dir SET data_dir_exists=:exists WHERE data_dir_id=:data_dir_id LIMIT 1";
			$parameters = array(':exists'=>$exists,
					':data_dir_id'=>$this->get_data_dir_id()
			);
			$query = $this->db->prepare($sql);
			$query->execute($parameters);
			if ($query->rowCount() ) {
				return true;
			}

		}

	}

	public static function createDirectory($gid, $pi, $user){
		if(settings::get_dataserver_enabled()) {
                	$safeGid = escapeshellarg($gid);
	                $safePi = escapeshellarg($pi);
        	        $safeUser = escapeshellarg($user);
                	$exec = "sudo -u " . settings::get_su_user() . " ../bin/addCoreServerDir.sh " . $safeGid . " " . $safePi . " " . $safeUser . " 2>&1";
	                $exit_status = 1;
        	        $output_array = array();
                	$output = exec($exec,$output_array,$exit_status);
			
	                if ($exit_status) {
        	                throw new Exception("Error Creating directory for user " . $user . ", " . end($output_array));
                	}
	                return $exit_status;
		}
		return false;

        }

	public static function remote_dir_exists($directory) {
		if(settings::get_dataserver_enabled()) {
			$safeDirectory = escapeshellarg($directory);
			$exec = "sudo -u " . settings::get_su_user() . " ../bin/CoreServerDirExists.sh " . $safeDirectory . " 2>&1";
			$exit_status = 01;
			$output_array = array();
			$output = exec($exec,$output_array,$exit_status);
			if ($exit_status) {
				throw new Exception("Error checking if directory exists for " . $directory . ", " . end($output_array));
				return false;
			}
			return end($output_array);
		
		}
		return false;


	}
	public static function get_id_by_directory($db, $directory) {
		$sql = "SELECT data_dir_id FROM data_dir WHERE data_dir_path=:data_dir_path LIMIT 1";
		$query = $db->prepare($sql);
		$query->execute(array(':data_dir_path'=>$directory));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			return $result['data_dir_id'];
		}
		return false;

	}
}
