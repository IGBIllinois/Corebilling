<?php
class User
{

	private $userID;
	private $username;
	private $first;
	private $last;
	private $email;
	private $departmentid;
	private $cfopl;
	private $groupid;
	private $rateid;
	private $statusid;
	private $usertypeid;
	private $sqlDataBase;
	private $grouppermid;
	private $dateAdded;
	private $certified;
	
	public function __construct(SQLDataBase $sqlDataBase)
	{
		$this->sqlDataBase = $sqlDataBase;
		$this->userID=0;
		$this->username="";
		$this->first="";
		$this->last="";
		$this->email="";
		$this->departmentid=0;
		$this->cfopl="";
		$this->groupid=0;
		$this->rateid=9;
		$this->statusid=7;
		$this->grouppermid=0;
		$this->dateAdded="";
		$this->usertypeid=3;
		$this->certified=0;
	}
	
	public function __destruct()
	{
		
	}
	
	public function CreateUser($username, $first, $last, $email,$departmentid,$cfopl,$groupid,$rateid,$statusid,$usertypeid,$grouppermid,$certified=0)
	{
		$this->username = mysql_real_escape_string($username);
		$this->first = mysql_real_escape_string($first);
		$this->last = mysql_real_escape_string($last);
		$this->email = mysql_real_escape_string($email);
		$this->departmentid = mysql_real_escape_string($departmentid);
		$this->cfopl = mysql_real_escape_string(str_replace("-","",$cfopl));
		$this->groupid = mysql_real_escape_string($groupid);
		$this->rateid = mysql_real_escape_string($rateid);
		$this->statusid = mysql_real_escape_string($statusid);
		$this->usertypeid = mysql_real_escape_string($usertypeid);
		$this->grouppermid = mysql_real_escape_string($grouppermid);
		$this->certified = mysql_real_escape_string($certified);
		$this->dateAdded = date('Y-m-d H:i:s');

		if($this->Exists($this->username)==0)
		{
			$queryAddUser = "INSERT INTO users (username, first,last,email,departmentid,cfopl,groupid,rateid,statusid,date_added,permgroupid,certified) VALUES(\"".$this->username."\",\"".$this->first."\",\"".$this->last."\",\"".$this->email."\",".$this->departmentid.",\"".$this->cfopl."\",".$this->groupid.",".$this->rateid.",".$this->statusid.",NOW(),$this->grouppermid,".$this->certified.")";
			echo $queryAddUser;
			$this->userID = $this->sqlDataBase->insertQuery($queryAddUser);

		}
	}

	public function LoadLdapUser($netid)
	{
		$info = LdapHelper::LoadIGBUser($netid);
		if($info['count']!=0)
		{
			$this->username=$info[0]["uid"][0];
			@list($firstName,$lastName) = split(' ',$info[0]["cn"][0]);
			$this->first=$firstName;
			$this->last=$lastName;
			$this->email=$info[0]["mail"][0];
			if($info['count']>1)
			{
				return $info;
			}
			else
			{
				$info =  array();
				return $info;
			}
		}
		else
		{
			$info = array();
			return $info;
		}
	}

	public function SearchLdapUser($netid)
        {
                $info = LdapHelper::SearchIGBUser($netid);
                if($info['count']!=0)
                {
                        $this->username=$info[0]["uid"][0];
                        @list($firstName,$lastName) = split(' ',$info[0]["cn"][0]);
                        $this->first=$firstName;
                        $this->last=$lastName;
                        $this->email=$info[0]["mail"][0];
                        if($info['count']>1)
                        {
                                return $info;
                        }
                        else
                        {
                                $info =  array();
                                return $info;
                        }
                }
                else
                {
                        $info = array();
                        return $info;
                }
        }

	public function LoadUser($id)
	{
		$queryUserInfo = "SELECT * FROM users WHERE ID=".$id;
		$userInfo=$this->sqlDataBase->query($queryUserInfo);
		$this->userID = $userInfo[0]["ID"];
		$this->username=$userInfo[0]["username"];
		$this->first=$userInfo[0]["first"];
		$this->last=$userInfo[0]["last"];
		$this->email=$userInfo[0]["email"];
		$this->departmentid=$userInfo[0]["departmentid"];
		$this->cfopl=str_replace("-","",$userInfo[0]["cfopl"]);
		$this->groupid=$userInfo[0]["groupid"];
		$this->rateid=$userInfo[0]["rateid"];
		$this->statusid=$userInfo[0]["statusid"];
		$this->usertypeid=$userInfo[0]["usertypeid"];
		$this->grouppermid = $userInfo[0]["permgroupid"];
		if($userInfo[0]["certified"])
		{
			$this->certified = $userInfo[0]["certified"];
		}
		else
		{
			$this->certified = 0;
		}
		$this->dateAdded = $userInfo[0]["date_added"];
	}

	public function UpdateUser()
	{
		$queryUpdateUser = "UPDATE users SET username=\"".$this->username."\",first=\"".$this->first."\",last=\"".$this->last."\",email=\"".$this->email."\",departmentid=".$this->departmentid.",cfopl=\"".str_replace("-","",$this->cfopl)."\",groupid=".$this->groupid." ,rateid=".$this->rateid.",statusid=".$this->statusid.", usertypeid=".$this->usertypeid.", permgroupid=".$this->grouppermid.", certified=".$this->certified." WHERE ID=".$this->userID;
		echo $queryUpdateUser;
		$this->sqlDataBase->nonSelectQuery($queryUpdateUser);
	}
	
	public function Exists($username)
	{
		$queryUserName = "SELECT COUNT(*) AS count  FROM users WHERE username = \"".$username."\"";
		$count = $this->sqlDataBase->singleQuery($queryUserName);
		if($count > 0)
		{
			$queryUserID = "SELECT ID FROM users WHERE username = \"".$username."\"";
			return $this->sqlDataBase->singleQuery($queryUserID);
		}
		else
		{
			return 0;
		}
		
	}

	public function GetDevicePerm($deviceid)
	{
		if($this->grouppermid)
		{
			$active = 5;
			$queryDevicePermission = "SELECT COUNT(*) FROM device_perm WHERE permgroupid=".$this->grouppermid." AND deviceid=".$deviceid." AND permissionid=".$active;
			$devicePermission = $this->sqlDataBase->singleQuery($queryDevicePermission);
			if($devicePermission)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			//Pass the permission test if user does not have a permission group associated with him
			return 1;
		}
	}	
	
	public function GetID()
	{
		return $this->userID;
	}

	public function GetUserName()
	{
		return $this->username;
	}

	public function SetUserName($username)
	{
		$this->username=mysql_real_escape_string($username);
	}

	public function GetFirst()
	{
		return $this->first;
	}

	public function SetFirst($first)
	{
		$this->first=mysql_real_escape_string($first);
	}

	public function GetLast()
	{
		return $this->last;
	}

	public function SetLast($last)
	{
		$this->last=mysql_real_escape_string($last);
	}

	public function GetEmail()
	{
		return $this->email;
	}

	public function SetEmail($email)
	{
		$this->email = mysql_real_escape_string($email);
	}

	public function GetDepartmentID()
	{
		return $this->departmentid;
	}
	
	public function SetDepartmentID($departmentid)
	{
		$this->departmentid = $departmentid;
	}

	public function GetCFOPL()
	{
		return $this->cfopl;
	}

	public function GetCFOPLFormated()
	{
		$formatedCfopl = substr($this->cfopl,0,1)."-".substr($this->cfopl,1,6)."-".substr($this->cfopl,7,6)."-".substr($this->cfopl,13,6);
		if(strlen($this->cfopl)>19)
		{
			$formatedCfopl .= "-".substr($this->cfopl,19,6);
		}
		return $formatedCfopl;
	}

	public function SetCFOPL($cfopl)
	{
		$this->cfopl = mysql_real_escape_string(str_replace("-","",$cfopl));
	}

	public function GetGroupID()
	{
		return $this->groupid;
	}

	public function SetGroupID($groupid)
	{
		$this->groupid = mysql_real_escape_string($groupid);
	}

	public function GetRateID()
	{
		return $this->rateid;
	}

	public function SetRateID($rateid)
	{
		$this->rateid = mysql_real_escape_string($rateid);
	}
	
	public function GetStatusID()
	{
		return $this->statusid;
	}

	public function SetStatusID($statusid)
	{
		$this->statusid = mysql_real_escape_string($statusid);
	}
		
	public function GetUserTypeID()
	{
		return $this->usertypeid;
	}

	public function SetUserTypeID($usertypeid)
	{
		$this->usertypeid = mysql_real_escape_string($usertypeid);
	}
	
	public function GetGroupPermID()
	{
		return $this->grouppermid;
	}

	public function SetGroupPermID($grouppermid)
	{
		$this->grouppermid = mysql_real_escape_string($grouppermid);
	}

	public function GetDateAdded()
	{
		return $this->dateAdded;
	}	

	public function GetCertified()
        {
                return $this->certified;
        }

        public function SetCertified($certified)
        {
                $this->certified = $certified;
        }

}
	
?>
