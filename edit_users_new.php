<?php 
if(isset($_SESSION['usertype'])){
	if($_SESSION['usertype']==1){

		$ldapSearchResults=array();

		$user = new User($sqlDataBase);
		
		// Submited New User
		if (isset($_POST['Create']))
		{
			if(isset($_POST['safetyQuiz']))
			{
				$userCertified = 1;
			}
			else
			{
				$userCertified = 0;
			}
			$user->CreateUser($_POST['username'],$_POST['first'],$_POST['last'],$_POST['email'],0,$_POST['cfopl'],$_POST['group'],$_POST['rate'],$_POST['status'],$_POST['usertype'],$_POST['permissionGroup'],$userCertified);
		}

		//If Modified user form
		if (isset($_POST['Modify']))
		{
			$user->LoadUser($_POST['userid']);
			$user->SetUserName($_POST['username']);
			$user->SetFirst($_POST['first']);
			$user->SetLast($_POST['last']);
			$user->SetEmail($_POST['email']);
			//$user->SetDepartmentID($_POST['department']);
			$user->SetCFOPL($_POST['cfopl']);
			$user->SetGroupID($_POST['group']);
			$user->SetRateID($_POST['rate']);
			$user->SetStatusID($_POST['status']);
			$user->SetUserTypeID($_POST['usertype']);
			$user->SetGroupPermID($_POST['permissionGroup']);
			if(isset($_POST['safetyQuiz']))
			{
				$user->SetCertified(1);
			}
			else
			{
				$user->SetCertified(0);
			}

			$user->UpdateUser();
		}
	
		if(isset($_POST['selectCoreUser']))
		{
			$user->LoadUser($_POST['user']);
		}

		if(isset($_POST['searchLdapUser']) || isset($_POST['selectSearchResult']))
		{
			$searchedUser = new User($sqlDataBase);
			$ldapSearchResults = $searchedUser->LoadLdapUser($_POST['userldap']);
			$userid = $user->Exists($searchedUser->GetUserName());
			if($userid > 0)
			{
				$user->LoadUser($userid);
			}
			else
			{
				$user->LoadLdapUser($_POST['userldap']);
			}
		}
		
		if(isset($_POST['updateUIUCLdapDepartments']))
                {
                        $serverHost = array("ldaps://ad.uiuc.edu 636");
                        $baseDN = "OU=Campus Accounts,DC=ad,DC=uiuc,DC=edu";
                        $uiucLdap = new Ldap($serverHost);

                        if(!$uiucLdap->connect())
                        {
                                die("Error connecting: ".$uiucLdap->ldapError."\n");
                        }
			$uiucLdap->startTLS();
                        if($uiucLdap->bind(UIUC_LDAP_USER,UIUC_LDAP_PASSWORD))
                        {	
				$queryUsersToSearch = "SELECT username,ID FROM users ORDER BY username";
  				$usersToSearch = $sqlDataBase->query($queryUsersToSearch);
				foreach($usersToSearch as $id=>$userToSearch)
				{
                                	if( $sr = $uiucLdap->searchSubtree($baseDN, "CN=".$userToSearch['username'],array('cn','department','uiucEduHomeDeptCode','uiucEduStudentDepartmentName')))
                                	{
                                        	if($entry = $sr->firstEntry())
                                        	{
                                        	        if($attrs = $entry->getAttributes())
                                        	        {
								if(isset($attrs['uiucEduHomeDeptCode'][0]))
								{
									$queryHomeDepartment = "SELECT id FROM departments WHERE department_code=\"".$attrs['uiucEduHomeDeptCode'][0]."\"";
								}
								else if(isset($attrs['uiucEduStudentDepartmentName'][0]))
								{
									$queryHomeDepartment = "SELECT id FROM departments WHERE departmentname=\"".$attrs['uiucEduStudentDepartmentName'][0]."\"";
								}
								else
								{
									echo "Failed to retrieve department code attribute from UIUC database for user ".$userToSearch['username']."<br>";
								}

								$departmentIdArr = $sqlDataBase->query($queryHomeDepartment);

								if(isset($departmentIdArr))
								{
									$departmentId = $departmentIdArr[0]['id'];
								}
								else
								{
									echo $userToSearch['username'];
									if(isset($attrs['uiucEduHomeDeptCode'][0]))
									{
										echo " Department does not exist, adding ".$attrs['uiucEduHomeDeptCode'][0]." department code ".$attrs['department'][0]."<br>";

										$queryExistingDepartment = "SELECT id FROM departments WHERE department=\"".$attrs['department'][0]."\"";
										$existingDepartment = $sqlDataBase->query($queryExistingDepartment);
										if(isset($existingDepartment))
										{
											$queryInsertDepartment = "UPDATE departments SET department_code=\"".$attrs['uiucEduHomeDeptCode'][0]."\" WHERE id=".$existingDepartment[0]['id'];
										}
										else
										{
											$queryInsertDepartment = "INSERT INTO departments (departmentname,description,department_code)VALUES(\"".$attrs['department'][0]."\",\"".$attrs['department'][0]."\",\"".$attrs['uiucEduHomeDeptCode'][0]."\")";	
										}
										
									}
									else if(isset($attrs['uiucEduStudentDepartmentName'][0]))
									{
										 $queryInsertDepartment = "INSERT INTO departments (departmentname,description,department_code)VALUES(\"".$attrs['uiucEduStudentDepartmentName'][0]."\",\"".$attrs['uiucEduStudentDepartmentName'][0]."\",\"\")";
									}
									else
									{
										echo " Department does not exist: ".$userToSearch['username']."<br>";
										
									}
									echo $queryInsertDepartment."<br>";
									$departmentId = $sqlDataBase->insertQuery($queryInsertDepartment);
								}
								
								$queryUpdateUserDepartment = "UPDATE users SET departmentid=".$departmentId." WHERE ID=".$userToSearch['ID'];
								$sqlDataBase->nonSelectQuery($queryUpdateUserDepartment);
                                        	        }
							else
							{
								echo $entry->ldapError;
							}
                                	        }
                                	}
				}
                        }
			else 
			{
				die("Error connecting: ".$uiucLdap->ldapError."\n");
			}
                }

		if(isset($_POST['createExcel'])) {
			$time = time();
        		$excel= new ExcelWriter(RECORDS_PATH."users_info.xls");
        		if($excel==false)
                		echo $excel->error;
			$myArr=array("<b>NetId</b>","<b>Email</b>","<b>First</b>","<b>Last</b>","<b>CFOPL</b>","<b>Department Name</b>","<b>Group Name</b>","<b>Status</b>","<b>Date Added</b>","<b>Last Login</b>","<b>First Login</b>","<b>Group Department</b>","<b>Last Calendar</b>","<b>First Calendar</b>");
        		$excel->writeLine($myArr);

			$queryUsersInfo ="SELECT u.username,u.email,u.first,u.last, u.cfopl, d.departmentname, g.groupname, s.statusname, u.date_added, (SELECT start FROM session WHERE userid=u.ID ORDER BY start DESC LIMIT 1) as last_login, (SELECT start FROM session WHERE userid=u.ID ORDER BY start ASC LIMIT 1) as first_login, (SELECT departmentname FROM departments dp, groups gp WHERE dp.id=gp.departmentid AND gp.ID=g.ID) AS group_department, (SELECT start FROM event_info WHERE userid=u.ID ORDER BY start DESC LIMIT 1) as last_calendar,(SELECT start FROM event_info WHERE userid=u.ID ORDER BY start ASC LIMIT 1) as first_calendar FROM groups g, status s, users u LEFT JOIN departments d ON d.id=u.departmentid WHERE g.ID = u.groupid AND s.ID=u.statusid ORDER BY u.username";
			$usersInfo = $sqlDataBase->query($queryUsersInfo);
        		foreach($usersInfo as $id=> $userInfo)
        		{
                		$replace_array=array("-"," ");
                		$printcfop=str_replace($replace_array,"",$userInfo['cfopl']);
                		$printcfop=substr($printcfop,0,1)." ".substr($printcfop,1,6)." ".substr($printcfop,7,6)." ".substr($printcfop,13,6)." ".substr($printcfop,19,6);

                		$myArray=array("".$userInfo['username']."","".$userInfo['email']."", "".$userInfo['first']."", "".$userInfo['last']."", "".$printcfop."","".$userInfo['departmentname']."","".$userInfo['groupname']."","".$userInfo['statusname']."","".$userInfo['date_added']."","".$userInfo['last_login']."","".$userInfo['first_login']."","".$userInfo['group_department']."","".$userInfo['last_calendar']."","".$userInfo['first_calendar']."");
                		$excel->writeLine($myArray);

        		}

        		$excel->close();
        		header("Location: ./records/users_info.xls");

		}

?>

<center>
  <h4>Edit Users</h4>
</center>
<table cellspacing="20">
<tr>
<td>
	<h4>Add IGB User:</h4>
	<form action="./administration.php?subm=12" method=POST>
	<input name="userldap" type="text" size="12" maxlength="12" value="username" >
	<input name="searchLdapUser" type="submit" class="grey" value="search" />
	</form>
	<font size="2" color="#465153">Use the search box above to search for a user in the IGB users directory.</font> <br />	
	<?php
	if(!empty($ldapSearchResults))
	{
		echo "<br><br><h4>Search Results:</h4>";
		echo "<form action=\"./administration.php?subm=12\" method=POST>";
		echo "<select name=\"userldap\">";
		foreach($ldapSearchResults as $id => $userInfo)
		{
			if($userInfo["uid"][0]!="")
			{
				echo "<option value=\"".$userInfo["uid"][0]."\">".$userInfo["cn"][0]."</option>";
			}
		}
		echo "</select>";
		echo "<input type=\"submit\" name=\"selectSearchResult\" value=\"Select\" class=\"grey\">";
		echo "</form>";
		echo "<font size=\"2\" color=\"#465153\">Use the drop down box to view more user search results.</font> <br />";
	}

	?>

	<br>
	<h4>Core Users:</h4>
	<form action="./administration.php?subm=12" method=POST>
	<select name="user">
	<?php
		
		$queryUsers = "SELECT username,ID FROM users ORDER BY username";
		$usersArr = $sqlDataBase->query($queryUsers);
		foreach($usersArr as $id => $userToSelect)
		{
			echo "<option value=".$userToSelect["ID"];
			if($userToSelect["ID"] == $user->GetID())
			{
				echo " SELECTED";
			}
			echo ">".$userToSelect["username"]."</option>";
		}
	?>
	</select>
	<input name="selectCoreUser" type="submit" class="grey" id="search" value="Select" />
	</form>
	<br>
	
	<h4>Left IGB:</h4>
        <form action="./administration.php?subm=12" method=POST>
        <?php
	
		if(isset($_POST['checkActiveUsers']))
		{	
			echo "<select name=\"user\">";
			$statusActive=5;
			$secondsToSleep = .5;
                	$queryUsers = "SELECT username,ID FROM users WHERE statusid=".$statusActive." ORDER BY username";
                	$usersArr = $sqlDataBase->query($queryUsers);
                	foreach($usersArr as $id => $userToSelect)
                	{
				if(LdapHelper::LdapUserExists($userToSelect["username"]) == 0)
				{
                	        	echo "<option value=".$userToSelect["ID"];
                	        	if($userToSelect["ID"] == $user->GetID())
                	        	{
                	               		echo " SELECTED";
                	        	}
                	        	echo ">".$userToSelect["username"]."</option>";
					usleep($secondsToSleep*1000000);
				}	
                	}
			echo "</select>";
			echo "<input name=\"selectCoreUser\" type=\"submit\" class=\"grey\" value=\"Select\" /><br>";
			echo "<font size=\"2\" color=\"#465153\">The following users are not members of IGB.</font> <br />";
			
		}
		else
		{
			echo "<input type=\"submit\" name=\"checkActiveUsers\" value=\"Check Active Users\" class=\"grey\"><br>";
			echo "<font size=\"2\" color=\"#465153\">Search for users who are no longer members of IGB. This may take a few moments to complete.</font> <br />";
			
		}
        ?>
	<br><br>
	<input type="submit" class="grey" value="Synchronize Departments" name="updateUIUCLdapDepartments" <?php echo (isset($_POST['updateUIUCLdapDepartments']))?"disabled":""; ?> >
	<font size="2" color="#465153">Synchronize department user information with UIUC directory. Please only use this button if it is realy necessary.</font> <br />
	
	<br><br>
        <input type="submit" class="grey" value="Download Excel" name="createExcel"><br>
        <font size="2" color="#465153">Create excel file containing all users information.</font> <br />
        </form>
	


</td>
<td>
	<div class="roundcont">
		<div class="roundtop"> 
			<img src="./imgs/tl.gif" alt="" width="15" height="15" class="corner" style="display: none" /> 
		</div>
		<div id="uform">

	<table cellspacing="20">
	<tr>
  		<td><h5>User ID:</h5></td>
  		<td>
	  	<form action="./administration.php?subm=12" method=POST>
 		<input name="userid" type="hidden" value="<?php echo $user->GetID(); ?>">
  		<input name="username" type="text" size="12" maxlength="20" value=<?php echo $user->GetUserName(); ?> >
  		</td>
  	</tr>
	<tr>
      		<td><h5>First Name:</h5></td>
      		<td><input name="first" type="text" size="25" maxlength="25" value=<?php echo $user->GetFirst(); ?> >
      		</td>
    	</tr>
  	<tr>
      		<td><h5>Last Name:</h5></td>
      		<td><input name="last" type="text" size="25" maxlength="25" value=<?php echo $user->GetLast(); ?> >
      		</td>
    	</tr>
  	<tr>
      		<td><h5>E-Mail:</h5></td>
      		<td><input name="email" type="text" size="25" maxlength="25" value=<?php echo $user->GetEmail(); ?>>
      		</td>
    	</tr>
  	<tr>
      		<td><h5>Department:</h5></td>
      		<td>
                <?php
                        $queryDepartments = "SELECT ID,departmentname FROM departments WHERE id=".$user->GetDepartmentId();
                        $departmentsArr = $sqlDataBase->query($queryDepartments);
			
			echo "<textarea row=\"3\" cols=\"25\" readonly>".$departmentsArr[0]['departmentname']."</textarea>";
              		/*
			foreach($departmentsArr as $id=>$department)
                        {
                                echo "<option value=".$department['ID'];
                                if($department==$user->GetDepartmentID())
                                {
                                        echo " SELECTED";
                                }
                                echo ">".$department['departmentname']."</option>";
                        }
			*/
                ?>
      		</td>
    	</tr>
  	<tr>
      		<td><h5>CFOPL Code:</h5></td>
      		<td><input name="cfopl" type="text" size="29" maxlength="29" value=<?php echo $user->GetCFOPLFormated(); ?>>
      		</td>
    	</tr>
  	<tr>
    		<td><h5>Rate:</h5></td>
    		<td>
    		<select name="rate">
		<?php
			$queryRates = "SELECT ID,ratename FROM rates";
			$ratesArr = $sqlDataBase->query($queryRates);
			foreach($ratesArr as $id=>$rate)
			{
				echo "<option value=".$rate['ID'];
				if($rate['ID']==$user->GetRateID())
				{
					echo " SELECTED";
				}
				echo ">".$rate['ratename']."</option>";
			}
		?>
    		</select>
    		</td> 
    	</tr>
    	<tr>
    		<td>
    		<h5>Group:</h5>
    		</td>
    		<td>
    		<select name="group"> 
    		<?php
			$queryGroups = "SELECT ID, groupname FROM groups ORDER BY groupname";
			$groupsArr = $sqlDataBase->query($queryGroups);
			foreach($groupsArr as $id=> $groupToSelect)
			{
				echo "<option value=".$groupToSelect['ID'];
				if($user->GetGroupID()==$groupToSelect['ID'])
				{
					echo " SELECTED";
				}
				echo ">".$groupToSelect['groupname']."</option>";
			}
		?>
		</select>
    		</td> 
   	</tr>
    	<tr>
    		<td>
    		<h5>User Type:</h5>
    		</td> 
   	 	<td>
    			<select name="usertype">
			<?php
				$queryUserTypes="SELECT ID, typename FROM usertype";
				$userTypesArr = $sqlDataBase->query($queryUserTypes);
				foreach($userTypesArr as $id => $userType)
				{
					echo "<option value=".$userType['ID'];
					if($user->GetUserTypeID()==$userType['ID'])
					{	
						echo " SELECTED";
					}
					echo ">".$userType['typename']."</option>";
				}
			?>
			</select>
    		</td>
    	</tr>
    	<tr>
    		<td>
    		<h5>User Status:</h5>
		</td>
    		<td>
    			<select name="status">
			<?php
				$userStatus = 2;
				$queryUsersStatus = "SELECT statusname,ID FROM status WHERE type=".$userStatus;
				$usersStatusArr = $sqlDataBase->query($queryUsersStatus);
				foreach($usersStatusArr as $usersStatus)
				{	
					echo "<option value=".$usersStatus['ID'];
					if($usersStatus['ID']==$user->GetStatusID())
					{
						echo " SELECTED";
					}
					echo ">".$usersStatus['statusname']."</option>";
				}
			?>
			</select>
    		</td>
    	</tr>
	<tr>
                <td>
                <h5>Permission Group:</h5>
                </td>
                <td>
                        <select name="permissionGroup">
                        <?php
                                
                                $queryPermissionGroups = "SELECT name, ID FROM perm_group";
                                $permissionGroups = $sqlDataBase->query($queryPermissionGroups);
				echo "<option value=0>None</option>";
                                foreach($permissionGroups as $id=>$permissionGroup)
                                {
                                        echo "<option value=".$permissionGroup['ID'];
                                        if($permissionGroup['ID']==$user->GetGroupPermID())
                                        {
                                                echo " SELECTED";
                                        }
                                        echo ">".$permissionGroup['name']."</option>";
                                }
                        ?>
                        </select>
                </td>
        </tr>
	<tr>
		<td>
		<h5>Safety Quiz:</h5>
		</td>
		<td>
		<input type="checkbox" name="safetyQuiz" <?php echo (($user->GetCertified())?"checked":""); ?>>	
		</td>
	</tr>
	<tr>
                <td>
                <h5>Date Added:</h5>
                </td>
                <td>
		<?php
		echo $user->GetDateAdded();
		?>
                </td>
        </tr>
    	</div>    
</table>
<center>
  <?php
if($user->GetID() > 0)
{	
	echo '<input name="Modify" type="submit" class="grey" id="Modify" value="Modify">';
}
else {
	echo '<input name="Create" type="submit" class="grey" id="Submit" value="Create" >';
}
echo '<input name="Reset" type="submit" class="grey" id="reset" value="reset" >';
?>
</center>
</form>


<div class="roundbottom"> <img src="./imgs/bl.gif" alt="" width="15" height="15" class="corner" style="display: none" /></div>
</div>	

</td>
</tr>
</table>
<?php
}
else {
	include "./denied.php";
}
}
else {
	include "./denied.php";
}
?>
