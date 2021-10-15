<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$group = new Group($db);
$department = new Department($db);

if (isset($_POST['Submit'])) {
	$groupName = $_POST['group_name'];
	$description = $_POST['description'];
	$departmentId = $_POST['department'];

	if (Group::exists($db,$groupName)) {
		$warnings .= html::error_message("Group name already exists.");
	} else {
		$group->create($groupName, $description, $departmentId);
	}
}

if (isset($_POST['Modify'])) {
	$groupName = $_POST['group_name'];
	$groupID = $_POST['groupID'];
	$description = $_POST['description'];
	$departmentId = $_POST['department'];
	$netid = $_POST['netid'];

	$group->load($groupID);
	$group->setDepartmentId($departmentId);
	$group->setName($groupName);
	$group->setDescription($description);
	$group->setNetid($netid);
	$group->update();
}

$groupID = 0;
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
							<?php
							$groupList = Group::getAllGroups($db);
							echo "<option value=\"0\">New Group</optionv>";
							foreach ($groupList as $groupInfo) {
								if ($groupInfo["id"] == $groupID) {
									echo "<option value=" . $groupInfo["id"] . " selected='selected'>" . $groupInfo["group_name"] . "</option>";
								}
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
							<?php
							$departmentList = Department::getAllDepartments($db);
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
						echo "<input name=\"groupID\" type=\"hidden\" value=\"" . $group->getId() . "\">";
						if ($group->getId() != 0) {
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
	require_once 'includes/footer.inc.php';
