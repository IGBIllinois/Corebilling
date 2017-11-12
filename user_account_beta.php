<?php 
if(isset($_SESSION['newuser'])){

		$user = new User($sqlDataBase);
		if($user->Exists($_SESSION['username']))
		{
			$user->LoadUser($user->Exists($_SESSION['username']));
		}
		else
		{
			echo "2";
			$user->LoadLdapUser($_SESSION['username']);	
		}
	
		
		// Submited New User
		if (isset($_POST['Register']))
		{
			//default settings
			$userType_User = 3;
                	$permissionGroup_none=0;
			$userStatus_disabled=7;
			$userRate_training=9;
			$user->CreateUser($_SESSION['username'],$_POST['first'],$_POST['last'],$_POST['email'],0,$_POST['cfopl'],$_POST['group'],$userRate_training,$userStatus_disabled,$userType_User,$permissionGroup_none);
		}

		//If Modified user form
		if (isset($_POST['Modify']))
		{
			$user->LoadUser($_POST['userid']);
			//$user->SetUserName($_POST['username']);
			$user->SetFirst($_POST['first']);
			$user->SetLast($_POST['last']);
			$user->SetEmail($_POST['email']);
			//$user->SetDepartmentID($_POST['department']);
			$user->SetCFOPL($_POST['cfopl']);
			$user->SetGroupID($_POST['group']);
			//$user->SetRateID($_POST['rate']);
			//$user->SetStatusID($_POST['status']);
			//$user->SetUserTypeID($_POST['usertype']);
			//$user->SetGroupPermID($_POST['permissionGroup']);
			$user->UpdateUser();
		}
	
		if(isset($_POST['searchLdapUser']) || isset($_POST['selectSearchResult']))
		{
			$searchedUser = new User($sqlDataBase);
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
		

?>

<center>
  <h4>Account Info</h4>
</center>
<table cellspacing="20">
<tr>
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
	  	<form action="./index.php?subm=4" method=POST>
 		<input name="userid" type="hidden" value="<?php echo $user->GetID(); ?>">
  		<input name="username" type="text" size="12" maxlength="20" value=<?php echo $user->GetUserName(); ?> disabled>
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
      		<td><input name="cfopl" type="text" size="25" maxlength="25" value=<?php echo $user->GetCFOPLFormated(); ?>>
      		</td>
    	</tr>
  	<tr>
    		<td><h5>Rate:</h5></td>
    		<td>
    		<select name="rate" disabled>
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
    			<select name="usertype" disabled>
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
    			<select name="status" disabled>
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
                        <select name="permissionGroup" disabled>
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
	echo '<input name="Register" type="submit" class="grey" id="Submit" value="Register" >';
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
?>
