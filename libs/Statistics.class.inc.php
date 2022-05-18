<?php
	
class Statistics 
{
	
	public static function getReservationUsage($db,$startyear,$startmonth,$endyear,$endmonth){
		$userQuery = "SELECT u.user_name, u.id from users u ";
		$userQuery .= "left join reservation_info r on r.user_id=u.id ";
		$userQuery .= "where ((MONTH(r.start)>=:startmonth AND YEAR(r.start)=:startyear) ";
		$userQuery .= "OR YEAR(r.start)>:startyear) AND ((MONTH(r.start)<=:endmonth ";
		$userQuery .= "AND YEAR(r.start)=:endyear) OR YEAR(r.start)<:endyear) group by u.id";
		$userStmt = $db->prepare($userQuery);
		$userStmt->execute(array(':startyear'=>$startyear,':startmonth'=>$startmonth,':endyear'=>$endyear,':endmonth'=>$endmonth));
		$userArr = $userStmt->fetchAll();
		
		$resTimeQuery = "select sum(timestampdiff(SECOND,r.start,r.stop)) as res_time ";
		$resTimeQuery .= "from reservation_info r where r.user_id=:userid and ((MONTH(r.start)>=:startmonth ";
		$resTimeQuery .= "AND YEAR(r.start)=:startyear) OR YEAR(r.start)>:startyear) ";
		$resTimeQuery .= "AND ((MONTH(r.start)<=:endmonth AND YEAR(r.start)=:endyear) OR YEAR(r.start)<:endyear)";
		$resTimeStmt = $db->prepare($resTimeQuery);
		
		$usedTimeQuery = "select sum(timestampdiff(SECOND,s.start,s.stop)) as used_time from `session` s ";
		$usedTimeQuery .= "where s.user_id=:userid and ((MONTH(s.start)>=:startmonth AND YEAR(s.start)=:startyear) ";
		$usedTimeQuery .= "OR YEAR(s.start)>:startyear) AND ((MONTH(s.start)<=:endmonth ";
		$usedTimeQuery .= "AND YEAR(s.start)=:endyear) OR YEAR(s.start)<:endyear)";
		$usedTimeStmt = $db->prepare($usedTimeQuery);
		
		$missedResQuery = "select count(id) as missed_res from reservation_info ";
		$missedResQuery .= "where user_id=:userid and id not in (select r.id ";
		$missedResQuery .= "from reservation_info r ";
		$missedResQuery .= "inner join `session` s on s.start <= r.stop and s.stop >= r.start ";
		$missedResQuery .= "where r.user_id=:userid and ((MONTH(r.start)>=:startmonth AND YEAR(r.start)=:startyear) ";
		$missedResQuery .= "OR YEAR(r.start)>:startyear) AND ((MONTH(r.start)<=:endmonth AND YEAR(r.start)=:endyear) ";
		$missedResQuery .= "OR YEAR(r.start)<:endyear) and r.device_id=s.device_id and r.user_id=s.user_id) ";
		$missedResQuery .= "and ((MONTH(start)>=:startmonth AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) ";
		$missedResQuery .= "AND ((MONTH(start)<=:endmonth AND YEAR(start)=:endyear) OR YEAR(start)<:endyear) and `stop`<NOW() and deleted=0";
		$missedResStmt = $db->prepare($missedResQuery);
		
		$deletedResQuery = "select count(id) as del_res from reservation_info ";
		$deletedResQuery .= "where user_id=:userid and deleted=1 and ((MONTH(start)>=:startmonth ";
		$deletedResQuery .= "AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) AND ((MONTH(start)<=:endmonth ";
		$deletedResQuery .= "AND YEAR(start)=:endyear) OR YEAR(start)<:endyear)";
		$deletedResStmt = $db->prepare($deletedResQuery);
		
		$results = array();
		for($i=0; $i<count($userArr); $i++){
			$params = array(':userid'=>$userArr[$i]['id'],
				':startyear'=>$startyear,
				':startmonth'=>$startmonth,
				':endyear'=>$endyear,
				':endmonth'=>$endmonth
			);
			$resTimeStmt->execute($params);
			$resTimeArr = $resTimeStmt->fetch();
			
			$usedTimeStmt->execute($params);
			$usedTimeArr = $usedTimeStmt->fetch(); 
			
			$missedResStmt->execute($params);
			$missedResArr = $missedResStmt->fetch();
			
			$deletedResStmt->execute($params);
			$deletedResArr = $deletedResStmt->fetch();
			
			$results[$i] = array('user_name'=>$userArr[$i]['user_name'], 
				'res_time'=>round($resTimeArr['res_time']/3600,2), 
				'used_time'=>round($usedTimeArr['used_time']/3600,2), 
				'used_ratio'=>round($usedTimeArr['used_time']/$resTimeArr['res_time'],2), 
				'missed_res'=>$missedResArr['missed_res'], 
				'deleted_res'=>$deletedResArr['del_res']
			);
		}
		
		return $results;
	}
}
