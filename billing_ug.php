<?php
if(isset($_SESSION['usertype'])) {
	$typeAdmin = 1;
	$typeSupervisor = 2;
	$typeUser = 3;
	
	echo "<center><h4>Group and Users Billing</h4></center><br>";
	
	$user = new User($sqlDataBase);
	$user->LoadUser($_SESSION['userid']);

	if(isset($_POST['monthSelected']))
	{
        	list($month, $year) = split(" ",$_POST['monthSelected']);
	}
	else{
        	$month=Date("n");
        	$year=Date("Y");
	}

	if(isset($_POST['selectUserDate']))
	{
		$selectedUser=$_POST['selectedUser'];
	}	
	else{
		$selectedUser=$_SESSION['userid'];
	}

	$userToView = new User($sqlDataBase);
	$userToView->LoadUser($selectedUser);

	if($user->GetUserTypeID() == $typeAdmin)
	{
		$queryUsers = "SELECT username, ID FROM users ORDER BY username";
	}
	
	if($user->GetUserTypeID() == $typeSupervisor)
	{
		$queryUsers = "SELECT username, ID FROM users WHERE groupid=(SELECT groupid FROM users WHERE ID=".$_SESSION['userid']." ORDER BY username)";
	}

	if($user->GetUserTypeID() == $typeUser)
	{
		$queryUsers = "SELECT username, ID FROM users WHERE ID=".$_SESSION['userid']." ORDER BY username";;
	}

	$availUsers = $sqlDataBase->query($queryUsers);

?>
<form action="./billing.php?subm=1"  method=POST>
<select name="selectedUser">
<?php
foreach($availUsers as $id=>$assoc)
{
	echo "<option value=".$assoc['ID'];
	if($selectedUser==$assoc['ID'])
	{	
		echo " SELECTED ";
	}
	echo ">".$assoc['username']."</option>";
}

?>
</select>
<?php
$availableMonthsQuery = "SELECT distinct DATE_FORMAT(start,'%M %Y') as mon_yr, MONTH(start) AS month, YEAR(start) AS year FROM session ORDER BY start DESC";
$availableMonths = $sqlDataBase->query($availableMonthsQuery);

?>

<select name="monthSelected">
<?php
foreach($availableMonths as $id=>$assoc)
{
        echo "<option value=\"".$assoc['month']." ".$assoc['year']."\"";
        if($assoc['month']==$month && $assoc['year']==$year)
        {
                echo " SELECTED";
        }
        echo ">".$assoc['mon_yr']."</option>";
}

?>
</select>
<input type="submit" name="selectUserDate" value="View Billing">
</form>
<br>

<table width="500">
	<tr>
		<td>
		<table width="350" class="billing">
			<tr class="title">
				<td align="left" class="billingtitle" width="50%">User Information:</td>
			</tr>
			<tr>
				<td class="billing">
				<table width="95%" cellpadding="0">
					<tr>
						<td align="left">
						<div id="billingtext">ID: <br />
						Name: <br />
						E-Mail:<br />
						CFOPL Code: <br />
						</div>
						</td>
						<td align="right">
						<div id="billingtext"><?php
						echo $userToView->GetUserName()."<br>";
						echo $userToView->GetFirst()." ".$userToView->GetLast()."<br>";
						echo $userToView->GetEmail()."<br>";
						echo $userToView->GetCfopl()."<br>";
						?></div>
						</td>
					</tr>
				</table>
				</td>
			</tr>
		</table>

		<br>
		<h4>Monthly Billing</h4>
		<table cellpadding="0" cellspacing="1" width="950" class="sortable">
                        <tr class="title">
                                <td>
                                         Status
                                </td>
                                <td>
                                        Date
                                </td>
                                <td>
                                        CFOP
                                </td>
                                <td>
                                        Equipment
                                </td>
                                <td>
                                        Usage(hrs)
                                </td>
                                <td>
                                        Rate
                                </td>
                                <td>
                                        Description
                                </td>
                                <td>
                                        Total
                                </td>
                        </tr>

                        <?php
                        $queryMonth = "SELECT s.ID, u.cfopl AS ucfopl, s.cfop AS scfopl, s.verified, dr.rate AS drate, s.rate AS srate, d.fdname, u.username, s.start, s.stop, s.elapsed, u.first, u.last, s.description FROM  session s, users u, devicerate dr, device d WHERE d.ID = s.deviceid  AND dr.deviceid=s.deviceid AND dr.rateid=u.rateid AND u.ID=s.userid AND MONTH(start)=".$month." AND YEAR(start)=".$year." AND s.userid=".$selectedUser." AND d.ratetype=2 GROUP BY s.deviceid, s.userid";
$monthSessions = $sqlDataBase->query($queryMonth);

                        $i=0;
                        if($monthSessions)
                        {
                        foreach($monthSessions as $id=>$assoc) {
                                if($assoc['verified']==0) {
                                        $status="Unverified";
                                        $rate=$assoc['drate'];
                                        $check="unchecked";
                                        $class="f";
                                        $printcfop=$assoc['ucfopl'];
                                }
                                else {
                                        $status="Verified";
                                        $rate=$assoc['srate'];
                                        $check="checked";
                                        $class="e";
                                        $printcfop=$assoc['scfopl'];
                                }
                                $replace_array=array("-"," ");
                                $printcfop=str_replace($replace_array,"",$printcfop);
                                $printcfop=substr($printcfop,0,1)." ".substr($printcfop,1,6)." ".substr($printcfop,7,6)." ".substr($printcfop,13,6);
                                echo "<tr class=\"".$class."".($i%2)."\"><td width=\"100\"><input type=\"checkbox\" name=\"verify[]\" value=\"".$assoc['ID']."\" ".$check." disabled /> ".$status."</td><td width=\"130\">".$assoc['start']."</td><td width=\"150\">".$printcfop."</td><td width=\"130\">".$assoc['fdname']."</td><td width=\"79\" align=\"center\">".round(($assoc['elapsed']/60),2)."</td><td align=\"center\"  width=\"70\">".round(($rate*60),2)."</td><td>".$assoc['description']."</td><td align=\"center\" width=\"70\">$".round($rate*60,2)."</td></tr>";

                                $i++;
                        }
                        }
                        ?>
                </table>

		<br>
		<h4>Usage Billing:</h4>
		<table cellpadding="0" cellspacing="1" width="950" class="sortable">
        		<tr class="title">
                		<td>
                       			 Status
                		</td>
                		<td>
                        		Date
                		</td>
                		<td>
                        		CFOP
                		</td>
                		<td>
                        		Equipment
                		</td>
                		<td>
                        		Usage(hrs)
                		</td>
                		<td>
                        		Rate
                		</td>
                		<td>
                        		Description
                		</td>
                		<td>
                        		Total
                		</td>
        		</tr>

			<?php
			$queryMonth = "SELECT s.ID, u.cfopl AS ucfopl, s.cfop AS scfopl, s.verified, dr.rate AS drate, s.rate AS srate, d.fdname, u.username, s.start, s.stop, s.elapsed, u.first, u.last, s.description FROM  session s, users u, devicerate dr, device d WHERE d.ID = s.deviceid  AND dr.deviceid=s.deviceid AND dr.rateid=u.rateid AND u.ID=s.userid AND MONTH(start)=".$month." AND YEAR(start)=".$year." AND s.userid=".$selectedUser." AND d.ratetype=1";
$monthSessions = $sqlDataBase->query($queryMonth);

			$i=0;
			if($monthSessions)
			{
			foreach($monthSessions as $id=>$assoc) {
        			if($assoc['verified']==0) {
                			$status="Unverified";
                			$rate=$assoc['drate'];
                			$check="unchecked";
                			$class="f";
                			$printcfop=$assoc['ucfopl'];
        			}
        			else {
                			$status="Verified";
                			$rate=$assoc['srate'];
                			$check="checked";
                			$class="e";
                			$printcfop=$assoc['scfopl'];
        			}
        			$replace_array=array("-"," ");
        			$printcfop=str_replace($replace_array,"",$printcfop);
        			$printcfop=substr($printcfop,0,1)." ".substr($printcfop,1,6)." ".substr($printcfop,7,6)." ".substr($printcfop,13,6);
        			echo "<tr class=\"".$class."".($i%2)."\"><td width=\"100\"><input type=\"checkbox\" name=\"verify[]\" value=\"".$assoc['ID']."\" ".$check." disabled /> ".$status."</td><td width=\"130\">".$assoc['start']."</td><td width=\"150\">".$printcfop."</td><td width=\"130\">".$assoc['fdname']."</td><td width=\"79\" align=\"center\">".round(($assoc['elapsed']/60),2)."</td><td align=\"center\"  width=\"70\">".round(($rate*60),2)."</td><td>".$assoc['description']."</td><td align=\"center\" width=\"70\">$".round((round($rate*60,2)*round($assoc['elapsed']/60,2)),2)."</td></tr>";

				$i++;
			}
			}
			?>
		</table>
	</tr>
</table>
</center>

		<?php

} else {
	include "./denied.php";
}
?>
