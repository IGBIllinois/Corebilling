<?php

require_once 'includes/header.inc.php';
if (!$login_user->isAdmin()) {
    echo html::error_message("You do not have permission to view this page.", "403 Forbidden");
    require_once 'includes/footer.inc.php';
    exit;
}

$selectedUser = new User($db,$ldap);
$userCfop = new UserCfop($db);
$userDepartment = new Department($db);
$rate = new Rate($db);
$group = new Group($db);

if (isset($_REQUEST['user_id'])) {
    $selectedUser->load($_REQUEST['user_id']);
	
}

if (isset($_POST['cancel_user'])) {
    header('location:list_users.php');
    exit();
}

$message = "";

//If Modified user form
if (isset($_POST['update_user'])) {
	//Update user info
	$selectedUser->setFirstName($_POST['first']);
	$selectedUser->setLastName($_POST['last']);
	$selectedUser->setEmail($_POST['email']);
	$selectedUser->setDepartmentId($_POST['department']);
	if (($_POST['user_role_id'] == User::ROLE_SUPERVISOR) || ($_POST['user_role_id'] == User::ROLE_ADMIN)) {
		$selectedUser->setSupervisorId(0);
	}
	elseif (isset($_POST['supervisor'])) {
		$selectedUser->setSupervisorId($_POST['supervisor']);
	}
        $selectedUser->setUsername($_POST['user_name']);
        $selectedUser->setRateId($_POST['rate']);
        $selectedUser->setStatus($_POST['status']);
        $selectedUser->setRoleId($_POST['user_role_id']);
        $selectedUser->setGroupIds($_POST['group'] ?? []);
        $selectedUser->setCertified(isset($_POST['safetyquiz']));
	$demographics = $selectedUser->getDemographics();
	$demographics->update($_POST['edulevel'],$_POST['gender'],$_POST['underrep']);

	$_POST['cfop_to_add'] = UserCfop::formatCfop($_POST['cfop_to_add']);
	if ($_POST['cfop_to_add'] != "---" && $_POST['cfop_to_add'] != $userCfop->loadDefaultCfop($selectedUser->getId())) {
        	// Look in old cfops to see if we're reusing an old one
		$cfopList = $selectedUser->getAllCFOPs();
		$foundcfop = false;
		for ($i = 0; $i < count($cfopList); $i++) {
			if (UserCfop::formatCfop($cfopList[$i]['cfop']) == $_POST['cfop_to_add']) {
				$foundcfop = true;
				$selectedUser->setDefaultCFOP($cfopList[$i]['id']);
				break;
			}
		}
		// Otherwise, add new cfop
		if (!$foundcfop) {
			$selectedUser->addCFOP($_POST['cfop_to_add']);
		}
	}

	$deviceList = Device::getAllDevices($db);
	foreach ($deviceList as $deviceInfo) {
        	if (isset($_POST['access']) && array_key_exists($deviceInfo['id'], $_POST['access'])) {
			if (!$selectedUser->hasAccessTo($deviceInfo['id'])) {
				if (LDAPMAN_API_ENABLED) {
					$ldapman->addGroupMember(LDAPMAN_DEVICE_PREFIX . $deviceInfo['device_name'],
						$selectedUser->getUsername());
				}
				$selectedUser->giveAccessTo($deviceInfo['id']);
			}
		} 
		else {
			if ($selectedUser->hasAccessTo($deviceInfo['id'])) {
				if (LDAPMAN_API_ENABLED) {
					$ldapman->removeGroupMember(LDAPMAN_DEVICE_PREFIX . $deviceInfo['device_name'],
						$selectedUser->getUsername());
				}
				$selectedUser->removeAccessTo($deviceInfo['id']);
			}
		}
    	}

	if ($selectedUser->update()) {
        	$message .= html::success_message("User updated successfully");
	} 
	else {
		$error = $db->errorInfo();
		$message .= html::error_message("User update failed: " . $error[2]);
	}
}

// Submitted new cfop
if (isset($_POST['add_cfop'])) {
    $selectedUser->addCFOP($_POST['cfop_to_add']);
}

// Submitted New User
if (isset($_POST['create_user'])) {
	if (User::exists($db, $_POST['user_name'])) {
		$message .= html::error_message("User " . $_POST['user_name'] . " already exists in database.");
	}
	else {
		$safetyquiz = 0;
		if (isset($_POST['safetyquiz'])) {
			$safetyquiz = 1;
		}
		$supervisor_id = 0;
		if (isset($_POST['supervisor'])) {
	                $supervisor_id = $_POST['supervisor'];
		}
		try {
			$result = $selectedUser->create($_POST['user_name'],
				$_POST['first'],$_POST['last'],
				$_POST['email'],
				$_POST['department'],
				$_POST['rate'],
				$_POST['status'],
				$_POST['user_role_id'],
				$safetyquiz,
				$supervisor_id);

			if ($result) {
				if (isset($_POST['group'])) {
					$selectedUser->setGroupIds($_POST['group']);
				}
		        	$selectedUser->addCFOP($_POST['cfop_to_add']);
	        		if (isset($_POST['access'])) {
					$deviceList = Device::getAllDevices($db);
					foreach ($deviceList as $deviceInfo) {
						if (array_key_exists($deviceInfo['id'], $_POST['access'])) {
							$selectedUser->giveAccessTo($deviceInfo['id']);
						}
					}
				}
				$selectedUser->update();
	
				$demographics = $selectedUser->getDemographics();
				$demographics->update($_POST['edulevel'],$_POST['gender'],$_POST['underrep']);
	
				$_REQUEST['user_id'] = $selectedUser->getId();
	
				$message .= html::success_message("User " . $_POST['user_name'] . " added to database.");
			}
		}
		catch (Exception $e) {
                        $message .= html::error_message($e->getMessage());
                }

	}
}

if (isset($_REQUEST['user_id'])) {
    $selectedUser->load($_REQUEST['user_id']);
}
?>

<h3><?php
    echo $selectedUser->getId() > 0 ? 'Edit' : 'Add'; ?> User</h3>
<?php
if ($selectedUser->getId() > 0 && !$selectedUser->is_ldap_user()) {
    echo html::error_message(
        "This user does not have an IGB account. Any changes made to their device access will not take effect. Please make sure to get their IGB account created before making changes here.",
        "No IGB Account"
    );
}
?>
<form action="edit_users.php" method='POST'>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>User Info</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Netid</label>
                                    <div class="col-sm-10">
                                        <input name="user_name" id="user_name" type="text" class="form-control" value='<?php
                                        echo $selectedUser->getUsername(); ?>'>
					<input type='hidden' name='login_session_id' id='login_session_id' value='<?php echo $login_session->get_session_id(); ?>'>
                                        <input type="hidden" name="user_id" value="<?php
                                        if (isset($_REQUEST['user_id'])) {
                                            echo $_REQUEST['user_id'];
                                        } ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">First</label>
                                    <div class="col-sm-10">
                                        <input name="first" id="first" type="text" class="form-control" value="<?php
                                        echo $selectedUser->getFirstName(); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Last</label>
                                    <div class="col-sm-10">
                                        <input name="last" id="last" type="text" class="form-control" value="<?php
                                        echo $selectedUser->getLastName(); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Mail</label>
                                    <div class="col-sm-10">
                                        <input name="email" id="email" type="email" class="form-control" value='<?php
                                        echo $selectedUser->getEmail(); ?>'>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Depart.</label>
                                    <div class="col-sm-10">
                                        <select name="department" class="form-control" id="depart-select">
                                            <option value=""></option>
                                            <?php
                                            $departmentsList = Department::getAllDepartments($db);
                                            foreach ($departmentsList as $departmentInfo) {
                                                echo "<option value=" . $departmentInfo['id'];
                                                if ($departmentInfo['id'] == $selectedUser->getDepartmentId()) {
                                                    echo " SELECTED";
                                                }
                                                echo " >" . $departmentInfo['department_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">CFOP</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="cfop" name="cfop_to_add" placeholder="1-xxxxxx-xxxxxx-xxxxxx" value="<?php
                                        if ($selectedUser->getId() > 0) {
                                            echo UserCfop::formatCfop($selectedUser->getDefaultCFOP());
                                        } ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-horizontal">
				<div class='form-group'>
					<label class='col-sm-2 control-label' for='edutUser'>Supervisor</label>
					<div class='col-sm-10'>
						<select name="supervisor" class="form-control" id="supervisor-select" 
							<?php if ($selectedUser->isSupervisor()) { echo 'disabled'; }?>>
						<option value=""></option>
						<?php
                                            foreach (User::getSupervisors($db) as $supervisor) {
                                                echo "<option value=" . $supervisor['id'];
                                                if ($supervisor['id'] == $selectedUser->getSupervisorId()) {
                                                    echo " selected='selected'";
                                                }
                                                echo " >" . $supervisor['user_name'] . "</option>";
                                            }
                                            ?>
                                        </select>

					</div>
				</div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Rate</label>
                                    <div class="col-sm-10">
                                        <select name="rate" class="form-control">
                                            <?php

                                            $listRates = Rate::getAllRates($db);
                                            foreach ($listRates as $id => $rate) {
                                                echo "<option value=" . $rate['id'];
                                                if ($rate['id'] == $selectedUser->getRateId()) {
                                                    echo " SELECTED";
                                                }
                                                echo ">" . $rate['rate_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Group</label>
                                    <div class="col-sm-10">
                                        <select name="group[]" class="form-control" id="group-select" multiple>
                                            <?php
                                            $listGroups = Group::getAllGroups($db);
                                            $userGroups = $selectedUser->getGroupIds();
						
                                            foreach ($listGroups as $id => $groupToSelect) {
                                                echo "<option value=" . $groupToSelect['id'];
                                                if (in_array($groupToSelect['id'], $userGroups)) {
                                                    echo " selected='selected'";
                                                }
                                                echo ">" . $groupToSelect['group_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Role</label>
                                    <div class="col-sm-10">
                                        <select name="user_role_id" class="form-control">
                                            <?php
                                            $userRolesList = User::getUserRoles($db);
                                            foreach ($userRolesList as $userRole) {
                                                echo "<option value=" . $userRole['id'];
                                                if ($selectedUser->getRoleId() == $userRole['id']) {
                                                    echo " SELECTED";
                                                }
                                                echo ">" . $userRole['role_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="editUser">Status</label>
                                    <div class="col-sm-10">
                                        <select name="status" class="form-control">
                                            <?php
                                            $statusList = User::getUserStatusList();

                                            foreach ($statusList as $usersStatus) {
                                                echo "<option value=" . $usersStatus['id'];
                                                if ($usersStatus['id'] == $selectedUser->getStatus()) {
                                                    echo " selected='selected'";
                                                }
                                                echo ">" . $usersStatus['name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Safety Quiz</label>
                                    <div class="col-sm-10">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="safetyquiz" <?php
                                                if ($selectedUser->isCertified()) {
                                                    echo " checked";
                                                } ?>></label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if ($selectedUser->getId() > 0) { ?>
                                    <div class="form-group" style="margin-bottom:0">
                                        <label class="col-sm-2 control-label" for="editUser">Created</label>
                                        <div class="col-sm-10">
                                            <h5><?php
                                                echo $selectedUser->getDateAdded(); ?></h5>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label" for="editUser">Last Login</label>
                                        <div class="col-sm-10">
                                            <h5><?php
                                                echo $selectedUser->getLastLogin(); ?></h5>
                                        </div>
                                    </div>
                                    <?php
                                } ?>
                            </div>
                        </div> <!-- .col-sm-6 -->
                    </div> <!-- .row -->
                </div>
            </div> <!-- .panel -->
        </div>
    </div> <!-- .row -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Demographic Info</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="form-horizontal">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="edulevel-select">Education Level</label>
                                    <div class="col-sm-9">
                                        <select name="edulevel" class="form-control" id="edulevel-select">
                                            <option value="">No response</option>
                                            <?php
                                            foreach (UserDemographics::allEduLevels() as $eduLevel) {
                                                $selected = ($selectedUser->getDemographics()->getEdulevel()
                                                             == $eduLevel) ? 'selected' : '';
                                                echo "<option value='$eduLevel' $selected>$eduLevel</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="underrep-select">Underrepresented</label>
                                    <div class="col-sm-9">
                                        <select name="underrep" class="form-control" id="underrep-select">
                                            <option value="">No response</option>
                                            <?php
                                            foreach (UserDemographics::allUnderrepOptions() as $option) {
                                                $selected = ($selectedUser->getDemographics()->getUnderrep()
                                                             == $option) ? 'selected' : '';
                                                echo "<option value='$option' $selected>$option</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="gender-select">Gender</label>
                                    <div class="col-sm-9">
                                        <select name="gender" class="form-control" id="gender-select">
                                            <option value="">No response</option>
                                            <?php
                                            foreach (UserDemographics::allGenders() as $gender) {
                                                $selected = ($selectedUser->getDemographics()->getGender()
                                                             == $gender) ? 'selected' : '';
                                                echo "<option value='$gender' $selected>$gender</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Device Access</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?php
                        if ($selectedUser->isAdmin()) {
                            echo "<div class='col-sm-12'>Admins have access to all devices.</div>";
                        } else {
                            $deviceList = Device::getAllDevices($db);
                            foreach ($deviceList as $deviceInfo) {
                                if ($deviceInfo['status_id'] == Device::STATUS_ONLINE || $deviceInfo['status_id'] == Device::STATUS_DONOTTRACK) {
                                    $checked = "";
                                    if ($selectedUser->hasAccessTo($deviceInfo['id'])) {
                                        $checked = " checked='checked'";
                                    }
                                    echo "<div class='col-sm-2'><label><input type='checkbox'" . $checked
                                         . " name='access[" . $deviceInfo['id'] . "]'/> "
                                         . $deviceInfo['full_device_name'] . "</label></div>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    echo $message; ?>

    <div class="form-group">
        <div class="col-sm-12">
            <?php
            if ($selectedUser->getId() > 0) {
                echo '<input name="update_user" type="submit" class="btn btn-primary" value="Update User">';
            } else {
                echo '<input name="create_user" type="submit" class="btn btn-primary" value="Create User" >';
            }
            ?>
            <input name="cancel_user" type="submit" class="btn btn-default" value="Cancel"/>
        </div>
    </div>

</form>

<script type="text/javascript">
	$('#depart-select').select2({
		placeholder: 'Select a Department'
	});
	$('#supervisor-select').select2({
		placeholder: 'Select a Supervisor'
	}),
	$('#group-select').select2({
		placeholder: 'Select a Group'
	});
    $('#copy-button').click(function (e) {
        var cfop = $('#cfop').text();
        var $textarea = $('#cfop-copy-area');
        $textarea.val(cfop);
        var showTextArea = true;
        if (document.queryCommandSupported('copy')) {
            showTextArea = false;
            $textarea.removeClass('hidden');
            $textarea[0].select();

            try {
                var success = document.execCommand('copy');
            } catch (err) {
                showTextArea = true;
            }

            $textarea.addClass('hidden');
            window.getSelection().removeAllRanges();
        }
        if (showTextArea) {
            $textarea.removeClass('hidden');
        }
        e.preventDefault();
    });

    var seqnum = 0;
    $('#user_name').on('change', function () {
        var $this = $(this);
        seqnum++;
        var currentseqnum = seqnum;
        $.ajax('api/v1/index.php/ldapuser/' + $this.val(), {
            method: 'get',
	    datatype: 'json',
	    contentType: 'application/json',
	    headers: { "Authorization": "Basic " + btoa('' + ":" + $('#login_session_id').val()) },
            success: function (response) {
                if (seqnum == currentseqnum) {
                    if (response != null) {
                        $('#first').val(response.givenName);
                        $('#last').val(response.sn);
                        $('#email').val(response.mail);
                    } else {
                        $('#first').val("");
                        $('#last').val("");
                        $('#email').val("");
                    }
                }
            }
        });
    });

</script>
<?php
require_once 'includes/footer.inc.php';
?>
