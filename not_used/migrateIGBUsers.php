<?php
include('config.php');
include('classes/AutoLoadClasses.php');
include('includes/mysql_connect.php');

$results = mysql_query("SELECT username,email,first,last FROM users");


while($row = mysql_fetch_assoc($results))
{
	extract($row);
	$infoIGB = LdapHelper::SearchIGBUser($username);
	$infoCore = LdapHelper::SearchCoreUser($username);
	if($infoCore["count"]>0)
	{
		echo $username." Already Exsists";
	}
		print("<pre>");
		print_r($infoIGB);
		print("<pre>");
	
	if($infoIGB["count"]>0 && $infoCore["count"] == 0)
	{	
		list($firstName,$lastName) = split(' ',$infoIGB[0]["cn"][0]);
		
		echo "</br>1".$firstName;
		echo "</br>2".$lastName;
		echo "</br>3".$infoIGB[0]["uid"][0];
		echo "</br>4".$infoIGB[0]["mail"][0];
	
		LdapHelper::AddIGBUser($infoIGB[0]["cn"][0],$infoIGB[0]["gecos"][0],$infoIGB[0]["gidnumber"][0],$infoIGB[0]["homedirectory"][0],$infoIGB[0]["loginshell"][0],$infoIGB[0]["mail"][0],$infoIGB[0]["sambapwdlastset"][0],$infoIGB[0]["sn"][0],$infoIGB[0]["uid"][0],$infoIGB[0]["uidnumber"][0], $infoIGB[0]["userpassword"][0],$infoIGB[0]["sambalmpassword"][0],$infoIGB[0]["sambantpassword"][0]);	
	}
	

}



?>
