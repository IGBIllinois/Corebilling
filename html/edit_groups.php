<?php
require_once 'includes/header.inc.php';
$access = $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Edit Groups'));
if($access != AccessControl::PERM_ADMIN){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$group = new Group($sqlDataBase);
$department = new Department($sqlDataBase);

if (isset($_POST['Submit'])) {
	$groupName = $_POST['group_name'];
	$description = $_POST['description'];
	$departmentId = $_POST['department'];

	if ($group->Exists($groupName)) {
		$warnings .= html::error_message("Group name already exists.");
	} else {
		$group->AddGroup($groupName, $description, $departmentId);
	}
}

if (isset($_POST['Modify'])) {
	$groupName = $_POST['group_name'];
	$groupID = $_POST['groupID'];
	$description = $_POST['description'];
	$departmentId = $_POST['department'];

	$group->LoadGroup($groupID);
	$group->setDepartmentId($departmentId);
	$group->setGroupName($groupName);
	$group->setDescription($description);
	$group->UpdateGroup();
}


if (isset($_POST['Select'])) {
	$groupID = $_POST['selectGroup'];
	$group->LoadGroup($groupID);

	$announce = "<h4>Modify Group:</h4>Modify group details, click modify to apply changes.";
	$newGroupBtn = ' <input name="Reset" type="submit" class="btn btn-primary" id="reset" value="Reset" >';
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
						<select name="selectGroup" class="form-control">
							<?php
							$groupList = $group->GetGroupsList();
							echo "<option value=\"0\">New Group</optionv>";
							foreach ($groupList as $groupInfo) {
								echo "<option value=" . $groupInfo["id"] . ">" . $groupInfo["group_name"] . "</option>";
							}
							?>
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
						<input name="group_name" type="text" value="<?php echo $group->getGroupName(); ?>" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Department</label>
					<div class="col-sm-9">
						<select name="department" class="form-control">
							<option value=0>Not Set</option>
							<?php
							$departmentList = $department->GetDepartmentList();
							foreach ($departmentList as $departmentInfo) {
								echo "<option value=" . $departmentInfo['id'];
								if ($departmentInfo['id'] == $group->getDepartmentId()) {
									echo " SELECTED";
								}
								echo ">" . $departmentInfo['department_name'] . "</option>";
							}
							?>
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
						echo "<input name=\"groupID\" type=\"hidden\" value=\"" . $group->getGroupId() . "\">";
						if ($group->getGroupId() != 0) {
							echo '<input name="Modify" type="submit" class="btn btn-primary" id="Modify" value="Modify">';
						} else {
							echo '<input name="Submit" type="submit" class="btn btn-primary" id="Submit" value="Create" >  <input name="Reset" type="submit" class="btn btn-primary" id="reset" value="Reset" >';
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>Members</h4>
				</div>
				<div class="panel-body">
					<table class="table table-striped table-hover">
						<th>NetId</th>
						<th>Full Name</th>
						<?php
						$members = $group->GetMembers();
						foreach ($members as $id => $member) {
							echo "<tr><td>" . $member['user_name'] . "</td><td>" . $member['first'] . " " . $member['last'] . "</td></tr>";
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
	require_once 'includes/footer.inc.php';