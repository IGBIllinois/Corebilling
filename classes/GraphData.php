<?php

class GraphData
{
	private $sqlDataBase;	
	private $xData;
	private $yData;
	private $assocData;

	public function __construct(SQLDataBase $sqlDataBase)
	{
		$this->sqlDataBase=$sqlDataBase;
	}

	public function __destruct()
	{
	}
	
	public function GetLineData($device,$user,$group,$startDate,$endDate,$interval,$dataunit)
	{
		if($interval=="day")
		{
			$groupByRange="DATE(dates.date_val)";
		}
		if($interval=="month")
		{
			$groupByRange="YEAR(dates.date_val),Month(dates.date_val)";
		}
		if($interval=="year")
		{
			$groupByRange="YEAR(dates.date_val)";
		}
		
		if($dataunit==0)
		{
			$queryUnit = "SUM(h.elapsed/60) AS yresults";
		}
		if($dataunit==1)
		{
			$queryUnit = "SUM(h.elapsed*h.rate) AS yresults";
		}

		$queryData="SELECT ".$queryUnit.", dates.date_val AS date FROM (SELECT s.elapsed, s.start, u.groupid, s.rate FROM session s, users u WHERE s.userid=u.ID AND s.deviceid LIKE '".$device."' AND s.userid LIKE '".$user."' AND groupid LIKE '".$group."') AS h RIGHT OUTER JOIN (SELECT yi.num AS y, mi.num AS m, di.num AS d,DATE(CONCAT_WS('-', yi.num, mi.num, di.num)) date_val FROM year_index yi, month_index mi, day_index di WHERE yi.num BETWEEN '".Date("Y",strtotime($startDate))."' AND '".Date("Y",strtotime($endDate))."' AND mi.num BETWEEN 1 AND 12 AND di.num BETWEEN 1 AND 31 AND DATE(CONCAT_WS('-', yi.num, mi.num, di.num))  IS NOT NULL ORDER BY y, m, d) as dates ON DATE(h.start) = dates.date_val WHERE dates.date_val BETWEEN DATE('".$startDate."') AND DATE('".$endDate."') GROUP BY ".$groupByRange;
		$this->assocData = $this->sqlDataBase->query($queryData);
		return $this->assocData;;
	}	
	
	public function GetYData()
	{
		$yData=array();
		foreach($this->assocData as $row)
		{
                        extract($row);
			if($yresults==null)
			{
				$yData[]=0;
			}else{
                        	$yData[]=$yresults;
			}
                        
		}	
		return $yData;	
	}	

	public function GetXData()
	{
		$xData=array();
                foreach($this->assocData as $row)
                {
                        extract($row);
                        $xData[]=$date;
                }
		return $xData; 
	}
}

?>
