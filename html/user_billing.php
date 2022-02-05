<?php
require_once 'includes/header.inc.php';

$rateTypesList = array(Bills::CONTINUOUS_RATE => "Continuous", Bills::MONTHLY_RATE => "Monthly");

$userToBill = new User($db,$ldap);
$bills = new Bills($db);
$userCfop = new UserCfop($db);

$selectableUsersList = array();
if($login_user->isAdmin()) {
	$selectableUsersList = User::getUsers($db);
} 
elseif($login_user->isSupervisor()) {
	$group = new Group($db);
	$group->load($authenticate->getAuthenticatedUser()->getGroupId());
	$selectableUsersList = $group->getMembers();
} 

if (isset($_POST['monthSelected'])) {
	list($month, $year) = explode(" ", $_POST['monthSelected']);
} 
else {
	$month = Date("n");
	$year = Date("Y");
}

if (isset($_POST['selectedUser'])) {
	$userToBill->load($_POST['selectedUser']);
} 
else {
	$userToBill->load($authenticate->getAuthenticatedUser()->getId());
}

$data_dir_id = $userToBill->get_data_dir_id();
$data_html = "";
if ($data_dir_id) {
	$data_dir = new data_dir($db,$data_dir_id);
	$data_bill = $data_dir->get_data_bill($month,$year);
	if ($data_bill) {
		$data_html = "<div class='panel panel-default'>";
	        $data_html .= "<div class='panel-heading'>";
        	$data_html .= "<h4>Data Usage Billing</h4></div>";
	        $data_html .= "<div class='panel-body'>";
        	$data_html .= "<table class='table table-striped table-hover'>";
		$data_html .= "<thead><tr>";
		$data_html .= "<th>Directory</th><th>Group</th><th>Terabytes</th>";
		$data_html .= "<th>Cost Per Terabyte</th><th>Billed Amount</th><th>CFOP</th>";
		$data_html .= "</thead>";
		$data_html .= "<tr>";
		$data_html .= "<td>" . $data_dir->get_directory() . "</td>";
		$data_html .= "<td>" . $data_dir->get_group() . "</td>";
		$data_html .= "<td>" . data_functions::bytes_to_terabytes($data_bill['data_bill_avg_bytes']) . "</td>";
		$data_html .= "<td>" . number_format($data_bill['cost'],2) . "</td>";
		$data_html .= "<td>$" . number_format($data_bill['data_bill_billed_cost'],2) . "</td>";
		$data_html .= "<td>" . UserCfop::formatCfop($data_bill['cfop']) . "</td>";	
		$data_html .= "</tr>";
		$data_html .= "</table>";
		$data_html .= "</div></div>";
	}	

}
?>

<h3>User Billing</h3>

<div class="panel panel-info">
	<div class="panel-body">
		<p>Your instrument usage bill is reported below. Billing is charged on a monthly cycle</p>
	
		<p>Please contact us to report any inconsistencies you find.</p>
	</div>
</div>
<form action="user_billing.php" method="POST" class="form-inline well">
	<div class="form-group">
		<select name="selectedUser" class="form-control" id="selectUser">
			<?php
			if (empty($selectableUsersList)) {
				echo "<option value=" . $userToBill->getId() . ">" . $userToBill->getUsername() . "</option>";
			} else {
				foreach ($selectableUsersList as $id => $availUser) {
					echo "<option value=" . $availUser['id'];
					if ($userToBill->getId() == $availUser['id']) {
						echo " selected='selected'";
					}
					echo ">" . $availUser['user_name'] . "</option>";
				}
			}
			?>
		</select>
	</div>
	<div class="form-group">
		<select name="monthSelected" class="form-control" id="selectMonth">
			<?php
			$availableBillingMonths = $bills->GetAvailableBillingMonths();
			foreach ($availableBillingMonths as $id => $charge) {
				echo "<option value=\"" . $charge['month'] . " " . $charge['year'] . "\"";
				if ($charge['month'] == $month && $charge['year'] == $year) {
					echo " selected='selected'";
				}
				echo ">" . $charge['mon_yr'] . "</option>";
			}

			?>
		</select>
	</div>
	<div class="form-group">
		<input type="submit" name="selectUserDate" value="View Billing" class="btn btn-primary">
	</div>
</form>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4>Account Information</h4>
	</div>
	<div class="panel-body">
		<table class="table table-striped">
			<tr>
				<th>Netid: </th>
				<td><?php echo $userToBill->getUsername();?></td>
			</tr>
			<tr>
				<th>Name:</th>
				<td><?php echo $userToBill->getFirstName()." ".$userToBill->getLastName();?></td>
			</tr>
			<tr>
				<th>E-Mail:</th>
				<td><?php echo $userToBill->getEmail();?></td>
			</tr>
			<tr>
				<th>CFOP:</th>
				<td><?php echo $userToBill->getDefaultCFOP();?></td>
			</tr>
		</table>
	</div>
</div>

<?php
foreach ($rateTypesList as $rateTypeId => $rateTypeName) { ?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4><?php echo $rateTypeName;?> Billing</h4>
	</div>
	<div class="panel-body">
		<table class="table table-striped table-hover">
			<tr>
				<th>Session id</th>
				<th>Date</th>
				<th>CFOP</th>
				<th>Equipment</th>
				<th>Usage(hrs)</th>
				<th>Rate</th>
				<th>Total</th>
			</tr>
			<?php
				$i = 0;
				$bills->setUserId($userToBill->getId());
				if ($rateTypeId == Bills::MONTHLY_RATE) {
					$bills->setGroupBy(Bills::GROUP_DEVICE);
				}
				$monthCharges = $bills->GetMonthCharges($year, $month, $rateTypeId);

				foreach ($monthCharges as $id => $charge) {
					$rate = $charge['rate'];

					echo "<tr>
						<td>" . $charge['id'] . "</td>
						<td>" . $charge['start'] . "</td>
						<td>" . UserCfop::formatCfop($charge['cfop']) . "</td>
						<td>" . $charge['full_device_name'] . "</td>
						<td>" . round(($charge['elapsed'] / 60), 2) . "</td>
						<td>$" . round(($rate * 60), 2) . "</td>
						<td>$" . number_format($bills->CalcTotal($charge['elapsed'],$rateTypeId,$rate,$charge['min_use_time']),2) . "</td>
					</tr>";
				}
				?>
		</table>
	</div>
</div>
<script type="text/javascript">
	$('#selectUser').select2({'width':'element'});
	$('#selectMonth').select2({'width':'element'});
</script>
<?php
}
echo $data_html;

require_once 'includes/footer.inc.php'; ?>
