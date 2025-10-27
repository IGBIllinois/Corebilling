<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$message = "";
$department = new Department($db);

if (isset($_POST['submit'])) {
	$departmentName = $_POST['department_name'];
	$description = $_POST['description'];
	$departmentId = $_POST['department_id'];
	$departmentCode = $_POST['department_code'];
	if (Department::exists($db,$departmentName)) {
		$warnings .= "Department name already exists.";
	} else {
		$result = $department->create($departmentName,$description,$departmentCode);
		if ($result) {
			$message = html::success_message("Department " . $departmentName . " successfully created");
		}
		

	}

}

if (isset($_POST['modify'])) {
	$departmentName = $_POST['department_name'];
	$departmentId = $_POST['department_id'];
	$description = $_POST['description'];

	$department->load($departmentId);
	$department->setDepartmentName($departmentName);
	$department->setDescription($description);
	$result = $department->update();
	if ($result) {
		$message =  html::success_message("Department " . $departmentName . " successfully updated");

	}
}


if (isset($_POST['select'])) {
	$departmentId = $_POST['selectDepartment'];
	$department->load($departmentId);
}
?>

<h3>Edit Departments</h3>

<form action="edit_departments.php" method="POST">
	
	<?php
	echo "<input name=\"department_id\" type=\"hidden\" value=\"" . $department->getDepartmentId() . "\">";
		?>
	<div class="row">
		<div class="col-md-6">
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editUser">Department</label>
					<div class="col-sm-5">
						<select name="selectDepartment" class="form-control">
							<?php
							$departmentList = Department::getAllDepartments($db);
							echo "<option value=\"0\">New Department</option>";
							foreach ($departmentList as $departmentInfo) {
								echo "<option value=" . $departmentInfo["id"];
								if($departmentInfo['id']==$department->getDepartmentId())
								{
									echo " selected";
								}
								echo ">" . $departmentInfo["department_name"] . "</option>";
							}
							?>
						</select>
					</div>
					<div class="col-sm-3">
						<input name="select" type="submit" class="btn btn-primary" id="Select" Value="Select"/>
					</div>
				</div>
			</div>
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label">Name</label>
					<div class="col-sm-9">
						<input type='text' name="department_name" type="text" class="form-control" value='<?php echo $department->getDepartmentName(); ?>'>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Description</label>
					<div class="col-sm-9">
						<textarea name="description" class="form-control"><?php echo $department->getDescription(); ?></textarea>
					</div>
				</div>
				<div class="form-group">
                                        <label class="col-sm-3 control-label">Department Code</label>
                                        <div class="col-sm-9">
                                                <input type='text' name="department_oode" class="form-control" value='<?php echo $department->getDepartmentCode(); ?>'>
                                        </div>
				</div>

				<div class="form-group">
                                        <div class="col-sm-9 col-sm-offset-3">
					<?php
                                                if ($department->getDepartmentId() != 0 && $department->getDepartmentId() != null) {
                                                        echo '<input name="modify" type="submit" class="btn btn-primary" id="Modify" value="Modify">';
                                                } else {
                                                        echo '<input name="create" type="submit" class="btn btn-primary" id="Submit" value="Create">';
                                                        echo '&nbsp<input name="reset" type="submit" class="btn btn-primary" id="reset" value="Reset">';
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
					<table class="table table-striped table-hover table-bordered table-condensed">
						<tr>
							<th>NetId</th>
							<th>Full Name</th>
						</tr>
						<?php
							$members = $department->getMembers();
						if (count($members)) { 
							foreach($members as $id=>$member) {
								echo "<tr><td>".$member['user_name']."</td><td>".$member['first']." ".$member['last']."</td></tr>";
							}
						}
						else {
							echo "<tr><td colspan='2'>No Members</td></tr>";
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
if (isset($message)) { echo $message; }

require_once 'includes/footer.inc.php';

