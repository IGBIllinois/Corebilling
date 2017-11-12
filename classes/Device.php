<?php
class Device
{

	private $deviceID;
	private $dn;
	private $name;
	private $location;
	private $description; 
	private $status;
	private $deviceToken;
	private $loggedUser;
	private $unauthorizedUser;

	public function __construct(SQLDataBase $sqlDataBase)
	{
		$this->sqlDataBase = $sqlDataBase;
		$this->dn = "";
		$this->name = "";
		$this->location= "";
		$this->description= "";
		$this->deviceID = 0;
		$this->status = 1;
		$this->rateType=1;
	}
	
	public function __destruct()
	{
	}
	
	public function CreateDevice($dn, $name,$location, $description, $status, $rateType)
	{
		$this->dn = $dn;
		$this->name = $name;
		$this->location = $location;
		$this->description = $description;
		$this->status = $status;
		$this->rateType = $rateType;
		$this->deviceToken = md5(uniqid(mt_rand(), true));
		$queryAddDevice = "INSERT INTO device (devicename,location,description,fdname,statusid,rateType,devicetoken) VALUES(\"".$dn."\",\"".$location."\",\"".$description."\",\"".$name."\",".$status.",".$rateType.",\"".$this->deviceToken."\")";
		$this->deviceID = $this->sqlDataBase->insertQuery($queryAddDevice);
		$queryPermissionGroups = "SELECT ID FROM perm_group";
		$permissionGroups = $this->sqlDataBase->query($queryPermissionGroups);
		foreach($permissionGroups as $id=>$permissionGroup)
		{
			$queryAddDeviceToPermGroup = "INSERT INTO device_perm (deviceid,permgroupid,permissionid)VALUES(".$this->deviceID.",".$permissionGroup['ID'].",5)";
			$this->sqlDataBase->nonSelectQuery($queryAddDeviceToPermGroup);
		}
		
	}
	
	public function LoadDevice($id)
	{
		$queryDeviceInfo = "SELECT * FROM device WHERE ID=".$id;
		$deviceInfo=$this->sqlDataBase->query($queryDeviceInfo);
		$this->dn = $deviceInfo[0]["devicename"];
		$this->name = $deviceInfo[0]["fdname"];
		$this->location = $deviceInfo[0]["location"];
		$this->description = $deviceInfo[0]["description"];
		$this->status = $deviceInfo[0]["statusid"];
		$this->rateType = $deviceInfo[0]["ratetype"];
		$this->deviceToken = $deviceInfo[0]["devicetoken"];
		$this->loggeduser = $deviceInfo[0]["loggeduser"];
		$this->unauthorizedUser = $deviceInfo[0]["unauthorized"];
		$this->deviceID = $id;
	}

	public function UpdateDevice()
	{
		$queryUpdateDevice = "UPDATE device SET devicename=\"".$this->dn."\", location=\"".$this->location."\",description=\"".$this->description."\",fdname=\"".$this->name."\", statusid=".$this->status.", ratetype=".$this->rateType." WHERE ID=".$this->deviceID;
		echo $queryUpdateDevice;
		$this->sqlDataBase->nonSelectQuery($queryUpdateDevice);
	}

	public function UpdateLastTick($username="")
	{
		$queryUpdateLastTick = "UPDATE device SET lasttick=NOW(), loggeduser=\"".$username."\" WHERE ID=".$this->deviceID;
		$this->sqlDataBase->nonSelectQuery($queryUpdateLastTick);
	}
		
	public function Exists($devicename)
	{
		$queryDeviceCount = "SELECT COUNT(*) FROM device WHERE devicename=\"".$devicename."\"";
		$count = $this->sqlDataBase->singleQuery($queryDeviceCount);
		if($count > 0)
		{
			return 1;
		}
		else 
		{
			return 0;
		}
	}

	public function SetLoggedUser($userId,$unauthorizedUser="")
	{
		$updateLoggedUser = "UPDATE device SET loggeduser=".$userId.", unauthorized=\"".$unauthorizedUser."\" WHERE ID=".$this->deviceID;
		$this->sqlDataBase->nonSelectQuery($updateLoggedUser);
	}
	
	public function GetID()
	{
		return $this->deviceID;
	}

	public function SetDN($dn)
	{
		$this->dn = $dn;
	}

	public function getDN()
	{
		return $this->dn;
	}

	public function SetName($name)
	{
		$this->name = $name;
	}
	
	public function GetName()
	{
		return $this->name;
	}

	public function SetLocation($location)
	{
		$this->location = $location;
	}

	public function GetLocation()
	{
		return $this->location;
	}

	public function GetStatus()
	{
		return $this->status;
	}	

	public function SetStatus($status)
	{
		$this->status = $status;
	}

	public function SetDescription($description)
	{
		$this->description = $description;
	}

	public function GetDescription()
	{
		return $this->description;
	}
	
	public function SetRateType($rateType)
	{
		$this->rateType = $rateType;
	}
	
	public function GetRateType()
	{
		return $this->rateType;
	}

	public function GetDeviceToken()
	{
		return $this->deviceToken;
	}

	public function GetLoggedUser()
	{
		return $this->loggedUser();
	}
	
	public function GetUnauthorizedUser()
	{
		return $this->unauthorizedUser;
	}

	public function DeviceVisability($deviceid,$status)
	{
		
	}	

}
?>
