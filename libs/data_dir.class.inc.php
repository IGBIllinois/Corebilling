<?php

class data_dir {


	private $db;
	private $id;
	private $group_id;
	private $directory;
	private $time_created;
	private $enabled;
	private $exists;

	const precentile = 0.95;
	const kilobytes_to_bytes = "1024";

	public function __construct($db,$data_dir_id = 0) {
		$this->db = $db;

		if ($data_dir_id != 0) {
			$this->get_data_dir($data_dir_id);
		}

	}

	public function __destruct() {
	}
	
	public function create($project_id,$directory,$default = 0) {
		$directory = $this->format_directory($directory);
		$this->project = new project($this->db,$project_id);

		$error = false;

		if ($this->data_dir_exists($directory)) {
			$error = true;
			$message .= "<div class='alert'>Directory " . $directory . " is already in the database</div>";
		}

		if ($error) {
			return array('RESULT'=>false,"MESSAGE"=>$message);
		}
		else {
			$sql = "INSERT INTO data_dir(data_dir_project_id,data_dir_path,data_dir_default) ";
			$sql .= "VALUES('" . $this->project->get_project_id() . "','" . $directory . "'";
			$sql .= ",'" . $default . "')";
			$result = $this->db->insert_query($sql);
			return array('RESULT'=>true,
					"data_dir_id"=>$result,
					"MESSAGE"=>"<div class='alert alert-success'>Directory " . $directory . " successfully added</div>"
			);
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
		if (is_dir($this->get_directory())) {
                        $message = "Unable to delete directory.  Directory " . $this->get_directory() . " still exists.";
                        $error = true;
                }
		if (!$error) {
			$sql = "UPDATE data_dir SET data_dir_enabled='0' ";
			$sql .= "WHERE data_dir_id='" . $this->get_data_dir_id() . "' LIMIT 1";
			$result = $this->db->non_select_query($sql);
			if ($result) {
				$this->enabled = 0;
				$message = "Successfully remove directory " . $this->get_directory() . ".";
			}
		}
		return array('RESULT'=>$result,'MESSAGE'=>$message);


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
			return true;
		}
		return false;
	}

	private function format_directory($directory) {
		if (strrpos($directory,"/") == strlen($directory) -1) {
			return substr($directory,0,strlen($directory)-1);
		}
		else {
			return $directory;
		}

	}


	private function data_dir_exists($directory) {
		$sql = "SELECT count(1) as count FROM data_dir ";
		$sql .= "WHERE data_dir_path LIKE '" . $directory . "%' ";
		$sql .= "AND data_dir_enabled='1'";
		$result = $this->db->query($sql);

		if ($result[0]['count']) {
			return true;
		}
		else { return false;
		}

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

		$group = new Group($this->db);
		$group->load($this->get_group_id());

                $data_cost = new data_cost($this->db);
		$sql = "INSERT INTO data_usage(data_usage_data_dir_id,data_usage_bytes) ";
		$sql .= "VALUES(:data_usage_data_dir_id,:data_usage_bytes,:data_usage_files) ";
                $parameters = array(':data_usage_data_dir_id'=>$this->get_data_dir_id(),
                                ':data_usage_group_id'=>$this->get_group_id(),
                                ':data_usage_bytes'=>$bytes
                                );
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
		$sql .= "WHERE MONTH(data_usage_time)='" . $month . "' ";
		$sql .= "AND YEAR(data_usage_time)='" . $year . "' ";
		$sql .= "AND data_usage_data_dir_id=" . $this->get_data_dir_id() . " ";
		$sql .= "ORDER BY data_usage_bytes DESC";
		$result = $this->db->query($sql);
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
		$sql .= "WHERE data_bill.data_bill_date='" . $bill_date ."' ";
		$sql .= "AND data_bill_data_dir_id='" . $this->get_data_dir_id() . "' ";
		$sql .= "LIMIT 1";
		$check_exists = $this->db->query($sql);
		$result = true;
		$insert_id = 0;
		if ($check_exists[0]['count']) {
			$result = false;
			$message = "Data Bill: Directory: " . $this->get_directory() . " Bill already calculated";
		}
		else {
	                $project = new project($this->db,$this->get_project_id());
			$data_cost_result = data_functions::get_current_data_cost_by_type($this->db,'standard');
        	        $data_cost = new data_cost($this->db,$data_cost_result['id']);
			$total_cost = $data_cost->calculate_cost($bytes);
			$billed_cost = 0;
			if ($project->get_bill_project()) {
				$billed_cost = $total_cost;
			}
        	        $insert_array = array('data_bill_data_dir_id'=>$this->get_data_dir_id(),
                	                'data_bill_project_id'=>$project->get_project_id(),
                        	        'data_bill_cfop_id'=>$project->get_cfop_id(),
                                	'data_bill_data_cost_id'=>$data_cost_result['id'],
	                                'data_bill_avg_bytes'=>$bytes,
					'data_bill_total_cost'=>$total_cost,
					'data_bill_billed_cost'=>$billed_cost,
					'data_bill_date'=>$bill_date
	                                );
        	        $insert_id = $this->db->build_insert('data_bill',$insert_array);
			$message = "Data Bill: Directory: " . $this->get_directory() . " Successfully added data bill";
		}
		return array('RESULT'=>$result,'MESSAGE'=>$message,'id'=>$insert_id);
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
			$query->execute();
			if ($this->db->rowCount() ) {
				return true;
			}

		}



		}


	}

}
