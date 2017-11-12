<?php

if(isset($_SESSION['usertype'])){
?>
<center><h4>Users List</h4></center><br>
<TABLE class="sortable" width="100%" cellspacing="1" cellpadding="3" >
<tr class="title">
<td>
	UserID
</td>
<td>
	Full Name
</td>
<td>
	Department
</td>
<td>
	E-Mail
</td>
<td>
	Group
</td>
</tr>

<?php
	$queryUsersInfo = "SELECT u.ID,u.username,u.first,u.last,d.departmentname as department,u.email,g.groupname FROM users u , groups g, departments d WHERE u.statusid!=6 AND u.statusid!=7 AND g.ID = u.groupid AND d.id=u.departmentid";
	$usersInfo = $sqlDataBase->query($queryUsersInfo);

	$i=0;
	foreach($usersInfo as $id => $userInfo)
	{
		$userid=$userInfo['ID'];
		$username=$userInfo['username'];
		$first=$userInfo['first'];
		$last=$userInfo['last'];
		$department=$userInfo['department'];
		$email=$userInfo['email'];
		$groupname=$userInfo['groupname'];
		echo "<tr class=\"d".($i%2)."\"><td align=\"center\">".$username."</td><td align=\"center\">".$first." ".$last."</td><td align=\"center\">".$department."</td><td align=\"center\"><a href=\"mailto:".$email."\">".$email."</a></td><td align=\"center\">".$groupname."</td></tr>";
		$i++;		
	}
?>
</TABLE>
<br>
<br></br>
<?php
}
else {
	include "./denied.php";
}
?>
