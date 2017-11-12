<?php
class LdapHelper
{

private static $coreLdapServer = "core.igb.uiuc.edu";
private static $igbLdapServer = "permauth.igb.uiuc.edu";
private static $localSid = "S-1-5-21-2633904392-3164975673-4142410721";

static public function AddIGBUser($cn,$gecos,$gidNumber,$homeDirectory,$loginShell,$mail,$sambaPwdLastSet,$sn,$uid,$uidNumber,$userPassword,$sambaLMPassword,$sambaNTPassword)
{
	$ds = ldap_connect(self::$coreLdapServer);
	if($ds)
	{
		$r = ldap_bind($ds,"cn=ldapadmin,dc=core,dc=igb,dc=uiuc,dc=edu","gecko09");

		$info["cn"]=$uid;
		$info["displayName"] = $uid;
		$info["gecos"]=$gecos;
		$info["gidNumber"] = 513;
		$info["givenName"] = $cn;
		$info["homeDirectory"]="\\home\\".$uid;
		$info["loginShell"]=$loginShell;
		$info["mail"] = $mail;
		$info["mailLocalAddress"]=$uid;	
		
		$info["objectClass"][0]="top";
		$info["objectClass"][1]="person";
		$info["objectClass"][2]="organizationalPerson";
		$info["objectClass"][3]="inetOrgPerson";
		$info["objectClass"][4]="posixAccount";
		$info["objectClass"][5]="shadowAccount";
		$info["objectClass"][6]="sambaSamAccount";
		$info["objectClass"][7]="inetLocalMailRecipient";
		
		$info["sambaAcctFlags"]="[U]";
		$info["sambaKickoffTime"]="2147483647";
		$info["sambaLMPassword"]=$sambaLMPassword;
		$info["sambaLogoffTime"]="2147483647";
		$info["sambaLogonTime"]="0";
		$info["sambaNTPassword"]=$sambaNTPassword;
		$info["sambaPrimaryGroupSID"]=self::$localSid."-513";
		$info["sambaPwdCanChange"]="0";
		$info["sambaPwdLastSet"]=time();
		$info["sambaPwdMustChange"]="2147483647";
		$info["sambaSID"]=self::$localSid."-".$uidNumber;
		$info["shadowLastChange"]=time();
		$info["shadowMax"]="45";
		$info["sn"] = $uid;
		$info["uid"] = $uid;
		$info["uidNumber"] = $uidNumber;
		$info["userPassword"] = $userPassword;
		echo "<pre>";
		print_r($info);
		echo "</pre>";

		$r = ldap_add($ds,"uid=" . $uid .",ou=Users,dc=core,dc=igb,dc=uiuc,dc=edu",$info);

		ldap_close($ds);

		return true;
	}
	else {
		return false;
	}
}

static public function AddUIUser($userName, $password)
{
	
}

static public function AddIndustrialUser($userName, $password)
{

}

static public function InsertComputer($displayName)
{
	$ds = ldap_connect(self::$coreLdapServer);
        if($ds)
        {
                $r = ldap_bind($ds,"cn=ldapadmin,dc=core,dc=igb,dc=uiuc,dc=edu","gecko09");
		$hours_elapsed = floor(time() / 360);
		$info["cn"]="computer";
		$info["displayName"]=$displayName."$";
		$info["objectClass"][0]="sambaSamAccount";
		$info["objectClass"][1]="account";
		$info["sambaAcctFlags"]="[W  ]";
		$info["sambaPwdCanChange"]=$hours_elapsed;
		$info["sambaPwdLastSet"]=$hours_elapsed;
		$info["sambaPwdMustChange"]="9223372036854775807";
		$info["sambaSID"]= self::$localSid."-1003";
		$info["uid"]=$displayName."$";
		
		//$r = ldap_add($ds,"uid=" . $displayName ."$,ou=Computers,dc=core,dc=igb,dc=uiuc,dc=edu",$info);		
	}

}

static public function ChangeIGBPassword($userName,$password)
{
	

}

static public function SearchIGBUser($userName)
{
	$ldaphost = self::$igbLdapServer;
	$ds = ldap_connect($ldaphost);
	$binddn = "ou=people,dc=igb,dc=uiuc,dc=edu";
	$r = ldap_bind($ds,"uid=coreldapsync,ou=People,dc=igb,dc=uiuc,dc=edu","gecko09");
	$justthese = array("cn","gecos","gidNumber","homeDirectory","loginShell","mail","sambaPwdLastSet","sn","uid","uidNumber","userPassword","sambaLMPassword","sambaNTPassword");
	$filter="(|(cn=$userName*)(uid=$userName*)(sn=$userName*))";
	
	$sr = ldap_search($ds, $binddn, $filter, $justthese);
	$info = ldap_get_entries($ds,$sr);

	ldap_close($ds);
	return $info;	
}

static public function LoadIGBUser($userName)
{
        $ldaphost = self::$igbLdapServer;
        $ds = ldap_connect($ldaphost);
        $binddn = "ou=people,dc=igb,dc=uiuc,dc=edu";
        $r = ldap_bind($ds,"uid=coreldapsync,ou=People,dc=igb,dc=uiuc,dc=edu","gecko09");
        $justthese = array("cn","gecos","gidNumber","homeDirectory","loginShell","mail","sambaPwdLastSet","sn","uid","uidNumber","userPassword","sambaLMPassword","sambaNTPassword");
        $filter="uid=".$userName;

        $sr = ldap_search($ds, $binddn, $filter, $justthese);
        $info = ldap_get_entries($ds,$sr);

        ldap_close($ds);
        return $info;
}

static public function LdapUserExists($userName)
{
	$ldaphost = self::$igbLdapServer;
        $ds = ldap_connect($ldaphost);
        $binddn = "ou=people,dc=igb,dc=uiuc,dc=edu";
        $r = ldap_bind($ds,"uid=coreldapsync,ou=People,dc=igb,dc=uiuc,dc=edu","gecko09");
        $justthese = array("cn","gecos","gidNumber","homeDirectory","loginShell","mail","sambaPwdLastSet","sn","uid","uidNumber","userPassword","sambaLMPassword","sambaNTPassword");
        $filter="uid=".$userName;

        $sr = ldap_search($ds, $binddn, $filter, $justthese);
        $info = ldap_get_entries($ds,$sr);
	echo $userName;
        ldap_close($ds);
	if($info['count']>0)
	{
		return 1;
	}
        else
	{
		return 0;
	}
}

static public function searchCoreUser($userName)
{
	$ldaphost = self::$coreLdapServer;
        $ds = ldap_connect($ldaphost);
        $binddn = "ou=Users,dc=core,dc=igb,dc=uiuc,dc=edu";
        $justthese = array("uid","userPassword","mail","cn");
        $filter = "(uid=$userName)";

        $sr = ldap_search($ds, $binddn, $filter, $justthese);
        $info = ldap_get_entries($ds,$sr);

	ldap_close($ds);
        return $info;

}

static public function GetNextAvailableUID()
{
	$ldaphost = self::$coreLdapServer;
	$ds = ldap_connect($ldaphost);
	$binddn = "dc=core,dc=igb,dc=uiuc,dc=edu";
	$justthese = array("uidNumber");
	$filter = "(sambaDomainName=CORETESTING)";
	
	$sr = ldap_search($ds, $binddn, $filter, $justthese);
	$info = ldap_get_entries($ds,$sr);
	
	ldap_close($ds);
	return $info[0][uidNumber];
}

static public function AuthenticateAD($userName)
{
	
}

static public function UpdateIGBPassword($userName)
{
	
}

}
?>
