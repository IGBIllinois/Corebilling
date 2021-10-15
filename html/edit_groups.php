<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$group = new Group($db);
$department = new Department($db);
$message = "";
$groupID = 0;
if (isset($_POST['create'])) {
	foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }
	
	$groupName = $_POST['group_name'];
	$description = $_POST['description'];
	$departmentId = $_POST['department'];
	$netid = $_POST['netid'];
	if ($groupName == "") {
		$message .= html::error_message("No group name specified");
	}
	elseif (!$departmentId) {
		$message .= html::error_message("Please specify a department");
	}
	elseif (Group::exists($db,$groupName)) {
		$message .= html::error_message("Group name already exists.");
	} 
	elseif ($netid == "") {
		$message .= html::error_message("No netID specified");
	}
	elseif (!User::exists($db,$netid)) {
		$message .= html::error_message("netID " . $netid . " does not exist");
	}
	else {
		$result = $group->create($groupName, $description, $departmentId,$netid);
		if ($result) {
			$message .= html::success_message("Group " . $groupName . " successfully created.");
		}
	}
}

if (isset($_POST['Modify'])) {
	$groupName = $_POST['group_name'];
	$groupID = $_POST['groupID'];
	$description = $_POST['description'];
	$departmentId = $_POST['department'];
	if ($groupName == "") {
                $message .= html::error_message("No group name specified");
        }
	elseif (!$departmentId) {
                $message .= html::error_message("Please specify a department");
        }
	else {
		$group->load($groupID);
		$group->setDepartmentId($departmentId);
		$group->setName($groupName);
		$group->setDescription($description);
		$group->update();
		$message .= html::success_message("Group " . $groupName . " successfully updated.");
	}
}

if (isset($_POST['Select'])) {
	$groupID = $_POST['group_id'];
	$group->load($groupID);
	

}
elseif (isset($_GET['group_id']) && is_numeric($_GET['group_id'])) {
	$groupID = $_GET['group_id'];
	$group->load($groupID);
}
$members = $group->getMembers();
$members_html = "";
foreach ($members as $id => $member) {
	$members_html .= "<tr><td><a href='edit_users.php?user_id=" . $member['id'] . "'>" . $member['user_name'] . "</a></td>";
	$members_html .= "<td>" . $member['first'] . " " . $member['last'] . "</td></tr>";
}

$departments = Department::getAllDepartments($db);
$department_html = "";
foreach ($departments as $department) {
	$department_html .= "<option value=" . $department['id'];
	if ($department['id'] == $group->getDepartmentId()) {
		$department_html .= " selected='selected'";
	}
	$department_html .= ">" . $department['department_name'] . "</option>";
}

$groups_html = "";
foreach (Group::getAllGroups($db) as $groupInfo) {
	if ($groupInfo["id"] == $groupID) {
		$groups_html .= "<option value=" . $groupInfo["id"] . " selected='selected'>" . $groupInfo["group_name"] . "</option>";
	}
	else {
		$groups_html .= "<option value=" . $groupInfo["id"] . ">" . $groupInfo["group_name"] . "</option>";
	}
}
?>

<h3>Edit Groups</h3>

<form action="edit_groups.php" method="POST">
	<div class="row">
		<div class="col-md-6">
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editGroup">Group</label>
					<div class="col-sm-6">
						<select name="group_id" class="form-control">
							<option value='0'>New Group</option>
							<?php echo $groups_html; ?>
						</select>
					</div>
					<div class="col-sm-3">
						<input name="Select" type="submit" class="btn btn-primary" id="Select" Value="Select"/>
					</div>
				</div>
			</div>
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label">Group Name</label>
					<div class="col-sm-9">
						<input name="group_name" type="text" value="<?php echo $group->getName(); ?>" class="form-control" placeholer='New Group'>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">PI netid</label>
					<div class="col-sm-9">
						<input name="netid" type="text" value="<?php echo $group->getNetid(); ?>" class="form-control"<?php if($group->getNetid() != null){echo " readonly";}?>>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Department</label>
					<div class="col-sm-9">
						<select name="department" class="form-control">
							<option value=0>Not Set</option>
							<?php echo $department_html; ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Description</label>
					<div class="col-sm-9">
						<textarea name="description" class="form-control"><?php echo $group->getDescription(); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<?php
						echo "<input name=\"groupID\" type=\"hidden\" value=\"" . $group->getId() . "\">";
						if ($group->getId() != 0) {
							echo '<input name="Modify" type="submit" class="btn btn-primary" id="Modify" value="Modify">';
						} else {
							echo '<input name="create" type="submit" class="btn btn-primary" id="Submit" value="Create" >  <input name="Reset" type="submit" class="btn btn-primary" id="reset" value="Reset" >';
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>Members - <?php if (isset($members)) { echo count($members); } ?></h4>
				</div>
				<div class="panel-body">
					<table class="table table-striped table-hover table-bordered table-condensed">
						<thead><th>NetId</th><th>Full Name</th></thead>
						<tbody>
						<?php echo $members_html; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
 if (isset($message)) { echo $message; }

require_once 'includes/footer.inc.php';
