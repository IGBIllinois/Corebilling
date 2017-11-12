<?php

class LdapAuth
{
	var $host="";
	var $peopleDN="";
	var $groupDN="";
	var $ssl="";
	var $port="";
	var $group="";
	var $error="No Error Detected";
	
	public function __construct($host,$peopleDN,$groupDN,$ssl,$port)
	{
		$this->host=$host;
		$this->peopleDN=$peopleDN;
		$this->groupDN=$groupDN;
		$this->ssl=$ssl;
		$this->port=$port;
	}
	
	public function __destruct()
	{
	
	}
	
	public function Authenticate($username,$password,$group)
	{
		if ($this->ssl == 1) {
                	$connect = ldap_connect("ldaps://" . $this->host,$this->port);
               
        	}
        	elseif ($this->ssl == 0) {
               		$connect = ldap_connect("ldap://" . $this->host,$this->port);
               
        	}
                 
        	$bindDN = "uid=" . $username . "," . $this->peopleDN;
       
       	 	$success = @ldap_bind($connect, $bindDN, $password);
		
        	if ($success == 1 && $group!="") {
                	$search = ldap_search($connect,$this->groupDN,"(cn=" . $group . ")");
                	$data = ldap_get_entries($connect,$search);
                	ldap_unbind($connect);
              
                	foreach($data[0]['memberuid'] as $groupMember) {
                       
                        	if ($username == $groupMember) {
                                	$success = 1;
                                	return $success;
                        	}
                        	else {
                                	$success = 0;
                        	}
                	}
               
        	}
		if($success == 0)
		{
			$error=ldap_error($connect);
		}	
        	return $success;
	}
	
	
	
	

}


?>
