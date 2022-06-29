<?php

class data_functions {

        const convert_terabytes = 1099511627776;
        const convert_gigabytes = 1073741824;

	public static function get_directories($db,$start = 0,$count = 0) {
		$sql = "SELECT data_dir.*, groups.group_name, groups.id as group_id, groups.netid as owner, users.id as owner_id, ";
		$sql .= "uc.cfop as cfop, ROUND(du.data_usage_bytes / 1099511627776,3) as terabytes ";
		$sql .= "FROM data_dir ";
		$sql .= "LEFT JOIN groups ON groups.id=data_dir.data_dir_group_id ";
		$sql .= "LEFT JOIN users ON users.user_name=groups.netid ";
		$sql .= "LEFT JOIN (SELECT * FROM user_cfop WHERE default_cfop=1) as uc ON uc.user_id=users.id ";
		$sql .= "LEFT JOIN (SELECT MAX(data_usage_time), data_usage.data_usage_data_dir_id, data_usage.data_usage_bytes FROM data_usage GROUP BY data_usage.data_usage_data_dir_id) as du ";
		$sql .= "ON du.data_usage_data_dir_id=data_dir.data_dir_id ";
		$sql .= "WHERE data_dir.data_dir_enabled='1' ";
		$sql .= "ORDER BY data_dir.data_dir_path ASC ";
		if ($count) {
			$sql .= "LIMIT " . $start . "," . $count;
		}
		$query = $db->prepare($sql);
                $query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as &$record) {
                        $record['cfop'] = UserCfop::formatCFOP($record['cfop']);
                }

		return $result;
	}

	public static function get_all_directories($db) {
		$sql = "SELECT data_dir.*, groups.group_name, groups.id as group_id, groups.netid as owner ";
                $sql .= "FROM data_dir ";
                $sql .= "LEFT JOIN groups ON groups.id=data_dir.data_dir_group_id ";
                $sql .= "WHERE data_dir_enabled='1' ";
                $sql .= "ORDER BY data_dir.data_dir_path ASC ";
		$query = $db->prepare($sql);
		$query->execute();
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
		if (count($result)) {
                	for ($i=0;$i<count($result);$i++) {
                        	if (is_dir($result[$i]['data_dir_path'])) {
                                	$result[$i]['dir_exists'] = true;
	                        }
        	                else {
                	                $result[$i]['dir_exists'] = false;
                        	}
	                }
                return $result;
		}
	}

	public static function get_dir_report($db) {
		$sql = "SELECT data_dir.data_dir_path as 'Path', ";
		$sql .= "groups.group_name as 'Group', ";
		$sql .= "users.user_name as 'Owner', ";
		$sql .= "users.email as 'Email', ";
		$sql .= "uc.cfop as 'CFOP', ";
		$sql .= "ROUND(du.data_usage_bytes / 1099511627776,3) as 'Terabytes', ";
		$sql .= "data_dir_time as 'Time Created' ";
                $sql .= "FROM data_dir ";
                $sql .= "LEFT JOIN groups ON groups.id=data_dir.data_dir_group_id ";
		$sql .= "LEFT JOIN users ON users.user_name=groups.netid ";
		$sql .= "LEFT JOIN (SELECT * FROM user_cfop WHERE default_cfop=1) as uc ON uc.user_id=users.id ";
		$sql .= "LEFT JOIN (SELECT MAX(data_usage_time), data_usage.data_usage_data_dir_id, data_usage.data_usage_bytes FROM data_usage GROUP BY data_usage.data_usage_data_dir_id) as du ";
		$sql .= "ON du.data_usage_data_dir_id=data_dir.data_dir_id ";
                $sql .= "WHERE data_dir.data_dir_enabled='1' ";
		$sql .= "ORDER BY data_dir.data_dir_path ASC ";
		error_log($sql);
		$query = $db->prepare($sql);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as &$record) {
                        $record['CFOP'] = UserCfop::formatCFOP($record['CFOP']);
                }
		return $result;




	}
	public static function get_num_directories($db) {
		$sql = "SELECT count(1) as count FROM data_dir ";
		$sql .= "WHERE data_dir_enabled='1' ";
		$query = $db->prepare($sql);
                $query->execute();
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
		return $result[0]['count'];
	}

	public static function get_current_data_cost_by_type($db,$type) {
		 $sql = "SELECT data_cost.data_cost_id as id, ";
                $sql .= "data_cost.data_cost_type as type, ";
                $sql .= "ROUND(data_cost_value,2) as cost, ";
                $sql .= "data_cost_time as time ";
                $sql .= "FROM data_cost ";
                $sql .= "WHERE data_cost_enabled='1' ";
		$sql .= "AND data_cost_type='" . $type . "'";
                $sql .= "ORDER BY type ";
		$result = $db->query($sql);
		if (count($result) == 1) {
			return $result[0];
		}
                return false;


	}
	public static function get_data_bill($db,$month,$year,$minimum_bill = 0.00) {
		$sql = "SELECT data_dir.data_dir_path as 'Directory', ";
	        $sql .= "ROUND(data_bill.data_bill_avg_bytes / 1099511627776,3) as 'Terabytes', ";
        	$sql .= "ROUND(data_cost.data_cost_value,2) as 'Rate ($/Terabyte)', ";
        	$sql .= "ROUND(data_bill.data_bill_total_cost,2) as 'Total Cost', ";
	        $sql .= "ROUND(data_bill.data_bill_billed_cost,2) as 'Billed Cost', ";
        	$sql .= "groups.group_name as 'Group', ";
		$sql .= "users.user_name as 'User', ";
	        $sql .= "user_cfop.cfop as 'CFOP' ";
        	$sql .= "FROM data_bill ";
		$sql .= "LEFT JOIN users ON users.id=data_bill.data_bill_user_id ";
	        $sql .= "LEFT JOIN user_cfop ON user_cfop.id=data_bill.data_bill_cfop_id ";
        	$sql .= "LEFT JOIN groups ON groups.id=data_bill.data_bill_group_id ";
	        $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
        	$sql .= "LEFT JOIN data_cost ON data_cost_id=data_bill_data_cost_id ";
	        $sql .= "WHERE YEAR(data_bill.data_bill_date)=:year ";
        	$sql .= "AND MONTH(data_bill.data_bill_date)=:month ";
	        $sql .= "AND ROUND(data_bill.data_bill_total_cost,2)>:minimum_bill ";
		$sql .= "ORDER BY Directory ASC";
		$query = $db->prepare($sql);
		$query->execute(array(':year'=>$year,
				':month'=>$month,
				':minimum_bill'=>$minimum_bill)
			);
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as &$record) {
			$record['CFOP'] = UserCfop::formatCFOP($record['CFOP']);
		}
		return $result;
	}

	public static function get_existing_dirs() {
		$root_dirs = settings::get_root_data_dirs();
		
		$existing_dirs = array();
		foreach ($root_dirs as $dir) {
			
			$found_dirs = array();
			$found_dirs = array_diff(scandir($dir), array('..','.'));
			foreach ($found_dirs as $key=>&$value) {
				if (is_link($dir . "/" . $value)) {
					unset($found_dirs[$key]);
				}
				else {
					$value = $dir . "/" . $value;
				}
			}
			if (count($found_dirs)) {
				$existing_dirs = array_merge($existing_dirs,$found_dirs);
			}
			
			
		}
		return $existing_dirs;
		
	}
	public static function get_unmonitored_dirs($db) {
		$full_monitored_dirs = self::get_all_directories($db);

		$existing_dirs = self::get_existing_dirs();
		$monitored_dirs = array();
		foreach ($full_monitored_dirs as $dir) {
			array_push($monitored_dirs,$dir['data_dir_path']);
			
		}
		
		$unmonitored_dirs = array_diff($existing_dirs,$monitored_dirs);
		return $unmonitored_dirs;
		
		
		
	}

	public static function bytes_to_terabytes($bytes = 0) {
                return round($bytes / self::convert_terabytes,3);

        }
        public static function bytes_to_gigabytes($bytes = 0) {
                return round($bytes / self::convert_gigabytes,3);
        }

	public static function get_total_cost($db,$start_date,$end_date,$format = 0) {
                $sql = "SELECT SUM(data_bill_total_cost) as total_cost ";
                $sql .= "FROM data_bill ";
                $sql .= "WHERE data_bill_date BETWEEN :start_date AND :end_date ";
                $query = $db->prepare($sql);
                $query->execute(array(':start_date'=>$start_date,
                                ':end_date'=>$end_date));
                $result = $query->fetch(PDO::FETCH_ASSOC);
                $cost = 0;
                if (count($result)) {
                        $cost = $result['total_cost'];
                        if ($format) {
                                $cost = number_format($result['total_cost'],2);
                        }
                }
                return $cost;
        }

        public static function get_billed_cost($db,$start_date,$end_date,$format = 0) {
                $sql = "SELECT SUM(data_bill_billed_cost) as billed_cost ";
                $sql .= "FROM data_bill ";
                $sql .= "WHERE data_bill_date BETWEEN :start_date AND :end_date ";
                $query = $db->prepare($sql);
                $query->execute(array(':start_date'=>$start_date,':end_date'=>$end_date));
                $result = $query->fetch(PDO::FETCH_ASSOC);
                $cost = 0;
                if (count($result)) {
                        $cost = $result['billed_cost'];
                        if ($format) {
                                $cost = number_format($result['billed_cost'],2);
                        }
                }
                return $cost;

        }

	public static function get_total_size($db,$start_date,$end_date,$format = 0) {
		$sql = "SELECT SUM(data_bill_avg_bytes) as total_size ";
                $sql .= "FROM data_bill ";
		$sql .= "WHERE data_bill_date BETWEEN :start_date AND :end_date ";
                $query = $db->prepare($sql);
                $query->execute(array(':start_date'=>$start_date,':end_date'=>$end_date));
		$result = $query->fetch(PDO::FETCH_ASSOC);
                $total_size = 0;
                if (count($result)) {
                        $total_size = self::bytes_to_terabytes($result['total_size']);
                        if ($format) {
                                $total_size = number_format(self::bytes_to_terabytes($result['total_size']),2);
                        }
                }
                return $total_size;


	}
}
?>
