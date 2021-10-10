<?php

class data_cost {
	
	////////////////Private Variables//////////
	private $db; //database object
	private $id = 0;
	private $cost;
	private $time_created;
	private $enabled = 0;

	const bytes_to_terabytes = 1099511627776;	

	////////////////Public Functions///////////
	
	public function __construct($db) {
		$this->db = $db;
		$this->get_data_cost();
	}
	public function __destruct() {
	}
	
	public function get_data_cost_id() {
		return $this->id;
	}
	public function get_cost() {
		return $this->cost;
	}
	public function get_formatted_cost() {
		return number_format($this->get_cost(),2);
	}
	public function get_time_created() {
		return $this->time_created;
	}
	public function get_enabled() {
		return $this->enabled;
	}


	public function calculate_cost($bytes) {
		$terabytes = $this->convert_terabytes($bytes);
		return $terabytes * $this->get_cost();

	}

	public function update_cost($cost) {
                if (!is_numeric($cost)) {
                        return false;
                }
		elseif ($this->get_cost() == $cost) {
			return false;
		}
                if ($this->disable()) {
                        $parameters = array(':data_cost_value'=>$cost);
                        $sql = "INSERT INTO data_cost(data_cost_value) VALUES(:data_cost_value)";
                        $query = $this->db->prepare($sql);
                        $query->execute($parameters);
                        $id = $this->db->lastInsertId();
                        if ($id) {
                                return $id;

                        }
                }
                return false;
        }
	
	/////////////////Private Functions///////////
	
	private function get_data_cost() {
		$sql = "SELECT * FROM data_cost ";
		$sql .= "WHERE data_cost_enabled='1' LIMIT 1";
		$query = $this->db->prepare($sql);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			$this->id = $result['data_cost_id'];
			$this->cost = $result['data_cost_value'];
			$this->time_created = $result['data_cost_time_created'];
			$this->enabled = $result['data_cost_enabled'];
		}

		
	}
	
	private function convert_terabytes($bytes) {
		return $bytes / self::bytes_to_terabytes;
	}

	private function disable() {
                $sql = "UPDATE data_cost SET data_cost_enabled='0'";
                $query = $this->db->prepare($sql);
                return $query->execute();

        }

	////////////////Public Static Functions////////////////
	public static function get_data_costs($db) {
                $sql = "SELECT data_cost.data_cost_id as id, ";
                $sql .= "ROUND(data_cost_value,2) as cost, ";
                $sql .= "data_cost_time_created as time_created ";
                $sql .= "FROM data_cost ";
                $sql .= "ORDER BY data_cost_time_created DESC ";
		$query = $db->prepare($sql);
                $query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
        }


}
