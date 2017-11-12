<?php

class Authentication
{
		
	function __constructor(SQLDataBase $sqlDataBase)
	{

	}

	function __destructor()
	{

	}

	function Auth($username,$password)
	{
		$ldaphost = "permauth.igb.uiuc.edu";
		$ds = ldap_connect($ldaphost);

		if ($ds) {
			$user_name = $_POST['login_name'];
			$pass_word = $_POST['pwd'];
			$binddn = "uid=$user_name,ou=people,dc=igb,dc=uiuc,dc=edu";
			$ldapbind = @ldap_bind($ds, $binddn, $pass_word);
				
			if ($ldapbind) {
				
				$querygroupid=mysql_query("SELECT groupid, grank,ID FROM users WHERE username='$user_name'");
					
				if(mysql_num_rows($querygroupid)) {
					$_SESSION['user_name'] = "$user_name";
					$groupid=mysql_result($querygroupid,0,"groupid");
					$grank=mysql_result($querygroupid,0,"grank");
					$_SESSION['userid']= mysql_result($querygroupid,0,"ID");
					$_SESSION['group']=$groupid;
					$_SESSION['grank']=$grank;
					$_SESSION['newuser']=0;
				}
				else {
					
					$logonerror=$logonerror."Your account wasn't activated for this website, please contact <b><a href=\"mailto:lheil@uiuc.edu\"><font color=\"red\" size=2>Lori Heil</font></a></b> to be activated.";
					$_SESSION['newuser']=1;
					$_SESSION['user_name']="$user_name";
				}
			}
			else{
				$logonerror=$logonerror."<b class=b>No match was found.<br />Please try again!</b>";
			}
		}
	}
}
?>
