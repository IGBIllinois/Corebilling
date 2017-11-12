<?php 
if(isset($_SESSION['usertype'])){
if($_SESSION['usertype']==1){

include"./includes/mysql_connect.php";
$warnings="";

if (isset($_POST['Submit']))
{
        $groupname=$_POST['groupname'];
        $description=$_POST['description'];

        $vergroup=mysql_query("SELECT ID FROM groups WHERE groupname='$groupname'");
        if(mysql_num_rows($vergroup)==0) {
                $errorchk = mysql_query("INSERT INTO groups (groupname,description) VALUES ('$groupname', '".htmlspecialchars($description,ENT_QUOTES)."')");
                if($errorchk)   {
                                $warnings.= "<font color=\"green\">Group: $groupname has been registered successfuly!</font>";
                        }
                else    {
                        $warnings.="<font color=\"red\">an error has occured and registration failed for $groupname</font>";
                }
        }
        else {
                        $warnings.="<font color=\"red\">Group name already exists.</font>";
        }

}

if (isset($_POST['Modify']))
{
        $groupname=$_POST['groupname'];
        $groupID=$_POST['groupID'];
        $description=$_POST['description'];
	$departmentid= $_POST['department'];

        $vergroup=mysql_query("SELECT ID FROM groups WHERE groupname='$groupname' AND ID!='$groupID'");
        if(mysql_num_rows($vergroup)==0) {
                $errorchk = mysql_query("UPDATE groups SET description='".htmlspecialchars($description,ENT_QUOTES)."', groupname='".htmlspecialchars($groupname,ENT_QUOTES)."', departmentid=".$departmentid."  WHERE ID='$groupID'");
                if($errorchk)   {
                                $warnings.=  "<font color=\"green\">Group: $groupname  has been modified.</font>";
                        }
                else    {
                        $warnings.=  "<font color=\"red\">an error has occured and registration failed for $groupname</font>";
                }
        }
        else {
                $warnings.= "<font color=\"red\">Not a valid group name</font>";
        }
}



if (isset($_POST['Select'])) {
        $groupID=$_POST['selectGroup'];
        $groupquery=mysql_query("SELECT groupname, description,departmentid FROM groups WHERE ID='$groupID'",$dbc);
        // echo mysql_errno($dbc). ": " .mysql_error($dbc). "\n";
        $description=mysql_result($groupquery,0,"description");
        $groupname=mysql_result($groupquery,0,"groupname");
	$departmentid=mysql_result($groupquery,0,"departmentid");
        $userquery=mysql_query("SELECT username FROM users WHERE groupid='$groupID'");
        $members="";
        $num_members=mysql_num_rows($userquery)+1;
        while ($row = mysql_fetch_assoc($userquery)) {
        	extract($row);
        	$members.="$username \n";
        }
	$announce="<center>Modify Group</center>";
	$newGroupBtn='<input name="Reset" type="submit" class="grey" id="reset" value="Reset" >';
}
else {	
	$announce="<center>New Group</center>";
	$groupname="New Group";
	$members="";
	$num_members=0;
	$description="";
	$groupID=0;
	$newGroupBtn="";
	$departmentid=0;
}

echo "<center><h4>Edit Groups</h4></center>";
echo "<br><center>".$warnings."</center><br>";
?>

<table cellspacing="20">
  <tr>

      	<td><h4>Select Group:</h4>
      	<?php
	$query = mysql_query("SELECT ID, groupname FROM groups ORDER BY groupname");
?>
      	<form action="./administration.php?subm=3" method=POST>
        <select name="selectGroup">
<?php
	while ($r = mysql_fetch_array($query))
	{
		$group= $r["groupname"];
		$ID=$r["ID"];
		echo "<option value=\"$ID\">$group</option>";
	}
?>
	</select>
        <input name="Select" type="submit" class="grey" id="Select" Value="Select" />
      </form>

      <font size="2" color="#465153">Select a group from the list above in order to view/modify information. Select New to create a new group.</font> </td>
    

	<td><div class="roundcont">
        <div class="roundtop"> <img src="./imgs/tl.gif" alt="" width="15" height="15" class="corner" style="display: none" /> </div>
        <div id="uform">

<?php


echo $announce;
?>
        <table cellspacing="20">
          <tr>
          
          <td><h5>Group Name:</h5></td>
          <td>
          
          <form action="./administration.php?subm=3" method=POST>
          
          <input name="groupname" type="text"  size="12" maxlength="12" value=<?php echo $groupname; ?>>
          </td>
          
          </tr>
	  <tr>
            <td><h5>Department:</h5></td>
            <td>
	    <select name="department" width="200">
	    <option value=0>Not Set</option>
	    <?php
	 	$queryDepartments = "SELECT departmentname,id,department_code FROM departments ORDER BY departmentname";
		$departments = $sqlDataBase->query($queryDepartments);
		foreach($departments as $id=>$department)
		{
			echo "<option value=".$department['id'];
			if($department['id']==$departmentid)
			{
				echo " SELECTED";
			}
			echo ">".$department['departmentname']."(".$department['department_code'].")</option>";
		}
		 
	    ?>
	    </select>
            </td>
          </div>

          </tr>         
 
          <tr>
            <td><h5>Description:</h5></td>
            <td><textarea name="description"  cols="25" rows="5" value=<?php echo $description; ?>><?php echo $description; ?></textarea>
            </td>
          </div>
          
          </tr>

          <tr>
            <td><h5>Members:</h5></td>
            <td><textarea readonly="readonly" name="members" rows="<?php echo $num_members; ?>" cols="9" value=<?php echo $description; ?>><?php echo $members; ?></textarea>
            </td>
          </div>
          
          </tr>

        </table>
        <center>
          <?php
echo "<input name=\"groupID\" type=\"hidden\" value=\"".$groupID."\">";

if($groupID!=0)
{
	echo '<input name="Modify" type="submit" class="grey" id="Modify" value="Modify">';
}
else {
	echo '<input name="Submit" type="submit" class="grey" id="Submit" value="Submit" >';
}
echo $newGroupBtn;
?>
        </center>
        <div class="roundbottom"> <img src="./imgs/bl.gif" alt="" 
	 width="15" height="15" class="corner" 
	 style="display: none" /> </div>
      </div></td>
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
