#!/usr/bin/env perl
$noUserLogged = "0";
$deviceId = 0;
$deviceKey = "";
$interactiveUserQuery = `who | grep&nbsp;:0`;
@connectedUsers = split /\s+/, $interactiveUserQuery;
$connectedUserName = $connectedUsers[0];

if($connectedUserName) {
	#do nothing
}
else {
	$connectedUserName=$noUserLogged;
}

$wgetString = "wget -O - \"http://core.igb.illinois.edu/coreapp/session.php?deviceid=".$deviceId."&username=".$connectedUserName."&key=".$deviceKey."\" > /dev/null";

system($wgetString);
print $wgetString;
