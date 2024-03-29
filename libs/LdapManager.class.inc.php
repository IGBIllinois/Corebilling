<?php
class LdapManager {
	private $apiUrl;
	private $username;
	private $password;		
	private $code;
	private $msg;
	private $log_file = null;
	
	public function __construct($apiUrl,$username="",$password=""){
		$this->apiUrl = $apiUrl;
		$this->username = $username;
		$this->password = $password;
		$this->log_file = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_log_file());
	}
		
	private function queryPOST($data){
		$options = array(
			'http' => array(
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'method' => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context = stream_context_create($options);
		$result = file_get_contents($this->apiUrl, false, $context);
		if($result === FALSE){
			$this->code = 500;
			$this->msg = 'Error completing POST query';
			return json_decode("{'code':500,'msg':'Error completing POST query'}");
		} else {
			$data = json_decode($result);
			if($data == null){
			    return array(
			        "code"=>500,
				'msg'=>'Error completing POST query',
				'response'=>$result
				);
			}
			$this->code = $data->code;
			$this->msg = $data->msg;
			return $data;
		}
	}
		
	public function getUser($uid){
		$data = array(
			'task'=>'user',
			'uid'=>$uid
		);
		$result = self::queryPOST($data);
		if($result->code == 200){
			return $result->user;
		} else {
			return null;
		}
	}

	public function getGroup($gid){
	    $data = array(
	        'task'=>'group',
                'gid'=>$gid
            );
	    $result = self::queryPOST($data);
	    if($result->code == 200){
		        return $result->group;
            } else {
		        return null;
            }
        }

        public function addGroup($gid, $description = null){
		    $data = array(
		        'task'=>'add_group',
                'username'=>$this->username,
                'password'=>$this->password,
                'gid'=>$gid,
                'description'=>$description
            );
            $result = self::queryPOST($data);
            if($result->code == 200){
                $this->log_file->send_log("Added ldap group '$gid'");
                return true;
            } else {
                $this->log_file->send_log("Failed trying to add ldap group '$gid'",2);
                return false;
            }
        }
		
	public function getGroupMembers($gid){
		$data = array(
			'task'=>'group_members',
			'gid'=>$gid	
		);
		$result = self::queryPOST($data);
		if($result->code == 200){
			return $result->members;
		} else {
			return array();
		}
	}

	public function isMemberOf($uid,$gid){
		$data = array(
			'task' => 'member_of',
			'uid' => $uid,
			'gid' => $gid
		);
		$result = self::queryPOST($data);
		if($result->code == 200){
			return $result->memberOf;
		}
		else {
			return false;
		}
        }
		
	public function addGroupMember($gid,$uid){
		$data = array(
			'task'=>'add_to_group',
			'username'=>$this->username,
			'password'=>$this->password,
			'gid'=>$gid,
			'uid'=>$uid
		);
		$result = self::queryPOST($data);
		if($result->code == 200){
			$this->log_file->send_log("Added user '$uid' to ldap group '$gid'");
			return true;
		} 
		else {
			$this->log_file->send_log("Failed trying to add user '$uid' to ldap group '$gid' with error ".$result->code.": '".$result->msg."'",2);
			return false;
		}
	}
		
	public function removeGroupMember($gid,$uid){
		$data = array(
			'task'=>'remove_from_group',
			'username'=>$this->username,
			'password'=>$this->password,
			'gid'=>$gid,
			'uid'=>$uid
		);
		$result = self::queryPOST($data);
		if($result->code == 200){
			$this->log_file->send_log("Removed user '$uid' from ldap group '$gid'");
			return true;
		} 
		else {
			$this->log_file->send_log("Failed trying to remove user '$uid' from ldap group '$gid' with error ".$result->code.": '".$result->msg."'",2);
			return false;
		}
	}
}
