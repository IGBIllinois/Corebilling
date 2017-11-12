<?php
class Session
{

	private $sessionID;
	private $userid;
	private $start;
	private $stop;
	private $status;
	private $deviceid;
	private $elapsed;
	private $verified;
	private $rateid;
	private $description;
	private $cfop;
	private $rate;
	
	public function __construct(SQLDataBase $sqlDataBase)
	{
		$this->sqlDataBase = $sqlDataBase;
		$this->sessionID=0;
		$this->userid=0;
		$this->start="";
		$this->stop="";
		$this->status="";
		$this->deviceid=0;
		$this->cfop="";
		$this->rateid=0;
		$this->description="";
	}
	
	public function __destruct()
	{
		
	}

	//Session tracker keeps track of session on each device
	//Used in session.php to track how long a person has been logged in
	public function TrackSession($deviceid,$userid)
	{
		$sessionID=0;

		if($userid>0)
		{
			$queryOpenSession = "SELECT ID FROM session WHERE userid=".$userid." AND deviceid=".$deviceid." AND (TIMESTAMPDIFF(MINUTE,stop,NOW()) < 15) ORDER BY ID DESC";
			$openSession = $this->sqlDataBase->query($queryOpenSession);
			
			if($openSession)
			{
				$sessionID = $openSession[0]['ID'];
				$queryUpdateSession = "UPDATE session SET stop=NOW(), elapsed=TIMESTAMPDIFF(MINUTE,start,NOW()) WHERE ID =".$openSession[0]['ID'];
				$this->sqlDataBase->nonSelectQuery($queryUpdateSession);	
			}
			else
			{
				$queryStartSession = "INSERT INTO session (userid,deviceid,start,stop,rate) VALUES (".$userid.",".$deviceid.",NOW(),NOW(),(SELECT rate FROM devicerate WHERE deviceid=".$deviceid." AND rateid=(SELECT rateid FROM users WHERE ID=".$userid." LIMIT 1)))";
				$sessionID = $this->sqlDataBase->insertQuery($queryStartSession);

			}
			$queryUpdateDeviceUser = "UPDATE device SET loggeduser=".$userid.", lasttick=NOW() WHERE ID=".$deviceid;
                        $this->sqlDataBase->nonSelectQuery($queryUpdateDeviceUser);
		}	
		else
		{
			$queryUpdateDeviceNonUser = "UPDATE device SET loggeduser=0, lasttick=NOW() WHERE ID=".$deviceid;
			$this->sqlDataBase->nonSelectQuery($queryUpdateDeviceNonUser);
		}

		return $sessionID;
	}
	
	public function CreateSession($userid,$start,$stop,$status,$deviceid,$description,$cfop)
	{
		$this->userid=$userid;
		$this->start=$start;
		$this->stop=$stop;
		$this->status=$status;
		$this->deviceid=$deviceid;
		$this->description=$description;
		$this->cfop=$cfop;
		
		$queryInsertSession="INSERT INTO session (userid,start,stop,status,deviceid,description,elapsed,cfop) VALUES(".$this->userid.",\"".$this->start."\",\"".$this->stop."\",".$this->status.",".$this->deviceid.",\"".$this->description."\",TIMESTAMPDIFF(MINUTE,\"".$this->start."\",\"".$this->stop."\",\"".$this->cfop."\")";
		$this->sessionID = $sqlDataBase->insertQuery($queryInsertSession);
	}

	public function LoadSession($id)
	{
		$querySessionInfo = "SELECT * FROM session WHERE ID=".$id;
		$sessionInfo=$this->sqlDataBase->query($querySessionInfo);
		$this->sessionID = $sessionInfo[0]["ID"];
		$this->userid=$sessionInfo[0]["userid"];
		$this->start=$sessionInfo[0]["start"];
		$this->stop=$sessionInfo[0]["stop"];
		$this->status=$sessionInfo[0]["status"];
		$this->deviceid=$sessionInfo[0]["deviceid"];
		$this->elapsed=$sessionInfo[0]["elapsed"];
		$this->verified=$sessionInfo[0]["verified"];
		$this->rate=$sessionInfo[0]["rate"];
		$this->description=$sessionInfo[0]["description"];
		$this->cfop=$sessionInfo[0]["cfop"];
	}

	public function UpdateSession()
	{
		$queryUpdateSession = "UPDATE session SET userid=".$this->userid.", start=\"".$this->start."\", stop=\"".$this->stop."\",status=\"".$this->status."\",deviceid=".$this->deviceid.", elapsed=TIMESTAMPDIFF(MINUTE, \"".$this->start."\",\"".$this->stop."\"), description=\"".$this->description."\", cfop=\"".$this->cfop."\", rate=".$this->rate." WHERE ID=".$this->sessionID;
		$this->sqlDataBase->nonSelectQuery($queryUpdateSession);
	}
	
	public function Verify()
	{
		$monthlyRate = 2;
		if($this->verified == 0)
		{
			$device = new Device($this->sqlDataBase);
			$device->LoadDevice($this->deviceid);
			if($device->GetRateType()==$monthlyRate)
			{
				$queryVerifySession = "UPDATE session s, users u, devicerate dr SET s.verified=1,s.rate = dr.rate, s.cfop = u.cfopl WHERE YEAR(start)=YEAR(\"".$this->start."\") AND MONTH(start)=MONTH(\"".$this->start."\") AND dr.deviceid=s.deviceid AND dr.rateid=u.rateid AND u.ID=s.userid AND s.deviceid=".$this->deviceid." AND s.userid=".$this->userid;
			}
			else
			{
				$queryVerifySession = "UPDATE session s, users u, devicerate dr SET s.verified=1,s.rate = dr.rate, s.cfop = u.cfopl WHERE s.ID=".$this->sessionID." AND dr.deviceid=s.deviceid AND dr.rateid=u.rateid AND u.ID=s.userid";
			}
			$this->sqlDataBase->nonSelectQuery($queryVerifySession);
		}
	}

	public function ManualVerify()
	{
		$queryVerifySession = "UPDATE session SET verified=1 WHERE ID = ".$this->sessionID;
		$this->sqlDataBase->nonSelectQuery($queryVerifySession);
	}

	public function IsVerfied()
	{
		if($this->verified=1)
		{
			return 1;
		}
		else
		{	
			return 0;
		}
	}

	public function Delete()
	{
		$queryDeleteSession = "DELETE FROM session WHERE ID=".$this->sessionID;
		$sqlDataBase->nonSelectQuery($queryDeleteSession);
	}
	
	public function GetID()
	{
		return $this->sessionID;
	}
	
	public function GetUserID()
	{
		return $this->userid;
	}

	public function SetUserID($userid)
	{
		$this->userid=$userid;
	}

	public function GetStart()
	{
		return $this->start;
	}	

	public function SetStart($start)
	{
		$this->start = $start;
	}

	public function GetStop()
	{
		return $this->stop;
	}

	public function SetStop($stop)
	{
		$this->stop = $stop;
	}

	public function GetStatus()
	{
		return $this->status;
	}	

	public function SetStatus($status)
	{
		$this->status=$status;
	}

	public function GetDeviceID()
	{
		return $this->deviceid;
	}
	
	public function SetDeviceID($deviceid)
	{
		$this->deviceid=$deviceid;
	}

	public function GetElapsed()
	{
		return $this->elapsed;
	}

	public function SetElapsed($elapsed)
	{
		$this->elapsed=$elapsed;
	}

	public function GetRate()
	{
		if($this->verified == 0)
		{
			$queryUnverifiedRate = "SELECT dr.rate FROM devicerate dr, users u WHERE u.ID = ".$this->userid." AND dr.deviceid = ".$this->deviceid." AND dr.rateid = u.rateid ";
			$unverifiedRate = $this->sqlDataBase->singleQuery($queryUnverifiedRate);
			return $unverifiedRate;
		}
		else
		{
			return $this->rate;
		}
	}
	
	public function SetRate($rate)
	{
		$this->rate=$rate;
	}

	public function GetDescription()
	{
		return $this->description;
	}

	public function SetDescription($description)	
	{
		$this->description=$description;
	}

	public function GetCfop()
	{
		return $this->cfop;
	}

	public function SetCfop($cfop)
	{
		$this->cfop=$cfop;
	}	
}
	
?>
