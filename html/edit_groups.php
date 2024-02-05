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
	$netid = $_POST['netid'];
	if ($groupName == "") {
		$message .= html::error_message("No group name specified");
	}
	elseif (Group::exists($db,$groupName)) {
		$message .= html::error_message("Group name already exists.");
	} 
	elseif ($netid == "") {
		$message .= html::error_message("No PI Username specified");
	}
	elseif (!User::exists($db,$netid)) {
		$message .= html::error_message("Username " . $netid . " does not exist");
	}
	else {
		try {
		$result = $group->create($groupName, $description, $netid);
		if ($result) {
			$message .= html::success_message("Group " . $groupName . " successfully created.");
			$groupID = $result;
			$group->load($groupID);
		}
		}
		catch (Exception $e) {
			$message .= html::error_message($e->getMessage());
			$groupID = 0;
			$group->load($groupID);
		}
	}

}

if (isset($_POST['modify'])) {
	foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }
	$groupName = $_POST['group_name'];
	$groupID = $_POST['group_id'];
	$description = $_POST['description'];
	$netid = $_POST['netid'];
	$group->load($groupID);
	if ($groupName == "") {
                $message .= html::error_message("No group name specified");
        }
	elseif ($netid == "") {
                $message .= html::error_message("No PI username specified");
        }
        elseif (!User::exists($db,$netid)) {
                $message .= html::error_message("User " . $netid . " does not exist.  Please add user first before updating group.");
        }
	elseif (($group->getName() == $groupName) &&
		($group->getNetid() == $netid) &&
		($group->getDescription() == $description)) {
		$message .= html::error_message("No group changes made");
	}
	else {
		try {
			if($group->update($groupName,$netid,$description)) {
				$message .= html::success_message("Group " . $groupName . " successfully updated.");
			}
		}
		catch (Exception $e) {
				$message .= html::error_message($e->getMessage());
				$groupID = 0;
				$group->load($groupID);
		}
	}
}
elseif (isset($_POST['delete'])) {
	$groupID = $_POST['group_id'];
	$group->load($groupID);
	$groupName = $group->getName();
	try { 
		if ($group->delete()) {
			$message .= html::success_message("Group " . $groupName . " successfully deleted");
			$groupID = 0;
			$group->load($groupID);
		}
		
	}
	catch (Exception $e) {
		$message = html::error_message($e->getMessage());
	}
		
}
elseif (isset($_POST['reset'])) {
	unset($_POST);
}
if (isset($_POST['select'])) {
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
						<select name="group_id" class="form-control" id='group_id'>
							<option value='0'>New Group</option>
							<?php echo $groups_html; ?>
						</select>
					</div>
					<div class="col-sm-3">
						<input name="select" type="submit" class="btn btn-primary" id="Select" Value="Select"/>
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
					<label class="col-sm-3 control-label">PI Username</label>
					<div class="col-sm-9">
						<input name="netid" type="text" value="<?php echo $group->getNetid(); ?>" class="form-control"<?php if($group->getNetid() != null){echo " readonly";}?>>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Description</label>
					<div class="col-sm-9">
						<textarea name="description" class="form-control"><?php echo $group->getDescription(); ?></textarea>
					</div>
				</div>
				<?php if ($group->getId() != 0) { 
					echo "<div class='form-group'><label class='col-sm-3 control-label'>Time Created</label>";
					echo "<div class='col-sm-9'><input class='form-control' type='text' readonly value='". $group->getTimeCreated() . "'></div></div>";
					if (LDAPMAN_API_ENABLED) {
						echo "<div class='form-group'><label class='col-sm-3 control-label'>LDAP Group</label>";
						echo "<div class='col-sm-9'><input class='form-control' type='text' readonly value='" . $group->getLdapGroupName() . "'></div></div>";
					}
				} ?>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<?php
						if ($group->getId() != 0 && $group->getId() != null) {
							echo '<input name="modify" type="submit" class="btn btn-primary" id="Modify" value="Modify">';
							echo '&nbsp<input name="delete" type="submit" class="btn btn-danger" id="delete" value="Delete" onClick="return confirm_group_delete()">';
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
					<h4>Members - <?php if (isset($members)) { echo count($members); } ?></h4>
				</div>
				<div class="panel-body">
					<table class="table table-striped table-hover table-bordered table-condensed">
						<thead><tr><th>NetId</th><th>Full Name</th></tr></thead>
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

?>

<script type="text/javascript">
        $('#group_id').select2({'width':'element'});
</script>

