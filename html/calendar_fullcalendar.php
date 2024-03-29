<?php
require_once 'includes/header.inc.php';
$device = new Device($db);

if (isset ($_POST ['deviceSelected'])) {
	if($_POST['deviceSelected']<0){
		$device->setDeviceId($_POST['deviceSelected']);
	} else {
		$device->load($_POST['deviceSelected']);
	}
}

$timepicker_html = "";
foreach (html::get_times() as $timepicker) {
	$timepicker_html .= "<option value='" . $timepicker . "'>" . $timepicker . "</option>";

}
?>
<h3>Reservation Calendar</h3>
<div class="panel panel-info">
	<div class="panel-heading">
		<h4>To Make a Reservation</h4>
	</div>
	<div class="panel-body">
		<ul>
			<li>Select a device from the list to view its calendar.</li>
			<li>Use < > arrows to select the month; Click on the day you would like to reserve the device for.</li>
			<li>To select the reservation time, click and drag to select a time range.</li>
		</ul>
	</div>
</div>
<div class="well clearfix">
	<div class="pull-right">
		<form class="form-inline" method="post" action="report.php">
			<input type="hidden" name="month" id="excelmonth"/>
			<input type="hidden" name="year" id="excelyear"/>
			<input type="hidden" name="user_id" value="<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>"/>
			<input type="hidden" name="login_session_id" value="<?php echo $login_session->get_session_id(); ?>"/>
			<input type="hidden" name="device_id" value="<?php echo $device->getId(); ?>"/>
			<input type="hidden" name="training" value="<?php echo isset($_POST['filterTraining'])?1:0; ?>"/>
			<select name="report_type" class="form-control">
				<option value="xlsx" selected>Excel</option>
				<option value="csv">CSV</option>
			</select>
			<input class="btn btn-primary" type="submit" name="create_cal_report" value="Download Spreadsheet"/>
		</form>
	</div>
	<form action="calendar_fullcalendar.php" method="POST" class="form-inline" name="calform">
		<div class="form-group">
			<select name="deviceSelected" class="form-control" onChange='document.calform.submit();'>
				<?php if($login_user->isAdmin()){ ?>
				<option value="-1" <?php if($device->getId()==-1) echo 'selected'; ?>>Missed Reservations</option>
				<option value="-3" <?php if($device->getId()==-3) echo 'selected'; ?>>Deleted Reservations</option>
				<option value="-2" <?php if($device->getId()==-2) echo 'selected'; ?>>All Devices</option>
				<?php } ?>
				<option value=0 <?php if(!isset($_POST['deviceSelected']) || $_POST['deviceSelected']==0) echo 'selected'; ?>>My Reservations</option>
				<?php
				$deviceList = Device::getAllDevices($db);
				foreach ($deviceList as $id => $availDevices) {
					
					// For now, let anyone schedule any device
					if ( $availDevices['status_id']==Device::STATUS_ONLINE || $availDevices['status_id']==Device::STATUS_DONOTTRACK ) {
						echo "<option value=" . $availDevices ['id'];
						if ($availDevices['id'] == $device->getId()) {
							echo " SELECTED";
						}
						echo ">" . $availDevices ['full_device_name'] . "</option>";
					}
				}
				?>
			</select>
		</div>
		<input type="hidden" name="day" id="filterday"/>
		<input type="hidden" name="month" id="filtermonth"/>
		<input type="hidden" name="year" id="filteryear"/>
		<input type="hidden" name="view" id="filterview"/>
		<?php if ($login_user->isAdmin()) { ?>
		<div class="checkbox">
			<label><input type="checkbox" name="filterTraining" onChange='document.calform.submit();' <?php if(isset($_POST['filterTraining'])){echo 'checked';} ?>> Only Show Training</label>
		</div>
		<?php } ?>
		
		&nbsp;<span class="legend-pip" style="background-color:<?php echo CAL_TRAINING_COLOR; ?>;border-color:<?php echo CAL_TRAINING_COLOR; ?>"></span> Training
		&nbsp;<span class="legend-pip" style="background-color:<?php echo CAL_MISSED_COLOR; ?>;border-color:<?php echo CAL_MISSED_COLOR; ?>"></span> Missed Reservation
        &nbsp;<span class="legend-pip" style="background-color:<?php echo CAL_ROOM_COLOR; ?>;border-color:<?php echo CAL_ROOM_COLOR; ?>"></span> Room Reserved
	</form>
	
</div>
<script>

$(document).ready(function () {
	
	var initialView = '<?php echo isset($_POST['view'])?$_POST['view']:'month'; ?>';
	var initialDay = '<?php echo (isset($_POST['day'])&&is_numeric($_POST['day']))?$_POST['day']:date('d'); ?>';
	var initialMonth = '<?php echo (isset($_POST['month'])&&is_numeric($_POST['month']))?$_POST['month']:date('m'); ?>';
	var initialYear = '<?php echo (isset($_POST['year'])&&is_numeric($_POST['year']))?$_POST['year']:date('Y'); ?>';

	$('#calendar').fullCalendar({
		editable: true,
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		events: {
			url: 'calendar_api.php',
			type: 'GET',
			allDayDefault: false,
			data: {
				action: 'get_events',
				device_id: '<?php echo $device->getId(); ?>',
				user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
				login_session_id: '<?php echo $login_session->get_session_id(); ?>',
				training: '<?php echo isset($_POST['filterTraining'])?1:0; ?>'
			}
		},
		selectable: true,
		selectHelper: true,
		snapDuration: {minutes:15},
		displayEventEnd: true,
		timezone: "local",
		defaultView: initialView,
		defaultDate: $.fullCalendar.moment(initialYear+'-'+initialMonth+'-'+initialDay),
		'eventRender': function (event, element, view) {
			var finishedEarlyDate = null;
			if(event.finishedEarly != null){
				var dateParts = event.finishedEarly.split(/[^0-9]/);
				finishedEarlyDate = new Date(dateParts[0],dateParts[1]-1,dateParts[2],dateParts[3],dateParts[4],dateParts[5]);
				finishedEarlyDate = finishedEarlyDate.getTime();
			}
			if(view.name === 'agendaWeek' || view.name === 'agendaDay'){
				element.find('.fc-content').append('<div class="fc-description">'+event.description+'</div>');
			}
			if( (view.name === 'agendaWeek' || view.name === 'agendaDay') && finishedEarlyDate != null && Date.parse(event.end)>finishedEarlyDate && Date.parse(event.start)<finishedEarlyDate){
				var finishedEarlyPerc = (finishedEarlyDate-Date.parse(event.start))/(Date.parse(event.end)-Date.parse(event.start)) * 100;
				element.find('.fc-bg').before('<div class="fc-early" style="top:'+finishedEarlyPerc+'%"></div>');
			}
		},
		loading: function (isLoading,view){
			// Display loading gif
			if(isLoading){
				$("<img class='loading-gif' src='images/loading-gif.gif'>").insertAfter(".fc-today-button");
			} else {
				$(".loading-gif").remove();
			}
		},
		viewRender: function(view,element){
			if(view.name==="month"){
				// Update month, year inputs for excel button
				$("#excelmonth, #filtermonth").val( moment(view.start).add(7,'days').format('MM') );
				$("#excelyear, #filteryear").val( moment(view.start).add(7,'days').format('YYYY') );
				$("#filterday").val( moment(view.start).add(7,'days').format('DD'));
			} else {
				// Update month, year inputs for excel button
				$("#excelmonth, #filtermonth").val( view.start.format('MM') );
				$("#excelyear, #filteryear").val( view.start.format('YYYY') );
				$('#filterday').val( view.start.format('DD') );
			}
			$('#filterview').val( view.name );
		},
		select: function (start, end) {
			$('#modifyReservationModal #finishedEarlyDiv').hide();
			if (start.hasTime() && end.hasTime()) {
				if(end.format('X') < new Date().getTime()/1000){
					alert('Cannot create a reservation in the past');
					$('#calendar').fullCalendar('unselect');
					$('#calendar').fullCalendar('refetchEvents');
				} else if (<?php echo $device->getId(); ?> > 0) {
					//alert('event clicked');
					var rangeString = start.format('HH:mm:ss') + ' - ' + end.format('HH:mm:ss');
					$('#modifyReservationModal #reservationWindowTitle').html('Create Reservation');
					$('#modifyReservationModal #reservationId').val("0");
					$('#modifyReservationModal #roomReservation').hide();
					//$('#modifyReservationModal #reservationDescription').val(calEvent.description);
					$('#modifyReservationModal #reservationStartDate').val(start.format("YYYY-MM-DD"));
					$('#modifyReservationModal #reservationStartTime').val(start.format("h:mma"));
					$('#modifyReservationModal #reservationEndDate').val(end.format("YYYY-MM-DD"));
					$('#modifyReservationModal #reservationEndTime').val(end.format('h:mma'));
					$('#modifyReservationModal #reservationRange').text(rangeString);
					$('#modifyReservationModal #reservationDevice').text("<?php echo $device->getFullName(); ?>");
					$('#modifyReservationModal #reservationUsername').text("<?php echo $authenticate->getAuthenticatedUser()->getUsername(); ?>");
					$('#modifyReservationModal #reservationUserId').val("<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>");
					// Enable all fields
					$('#modifyReservationModal #reservationDescription').prop("readonly",false);
					$('#modifyReservationModal #reservationTraining').prop("disabled",false);
					$('#modifyReservationModal #reservationStartTime').prop( "readonly", false );
					$('#modifyReservationModal #reservationEndTime').prop( "readonly", false );
					$('#modifyReservationModal #deleteReservation').hide();
					$('#modifyReservationModal #updateReservation').show();
					<?php
					if($login_user->isAdmin())
					{
					?>
					$('#modifyReservationModal #trainingFormGroup').show();
					$('#modifyReservationModal #repeatFormGroup').show();

					<?php
					}
					?>
					$('#modifyReservationModal').appendTo("body").modal('show');

					$('#calendar').fullCalendar('unselect');
				} else {
					alert('No device selected: Please select a device calendar.');
					$('#calendar').fullCalendar('unselect');
					$('#calendar').fullCalendar('refetchEvents');
				}
			}
		},
		eventClick: function (calEvent, jsEvent, view) {
			// If the event is in progress and belongs to us, show the I Finished Early button
			if(calEvent.userid==<?php echo $authenticate->getAuthenticatedUser()->getId(); ?> && calEvent.start.format('X')<new Date().getTime()/1000 && calEvent.end.format('X')>new Date().getTime()/1000 && calEvent.finishedEarly == null){
				$('#modifyReservationModal #finishedEarlyDiv').show();
			} else {
				$('#modifyReservationModal #finishedEarlyDiv').hide();
			}
			// If the event is a room-reservation, show the room reservation info
            if(calEvent.masterDevice!=null){
                $('#modifyReservationModal #roomReservation').show();
                $('#modifyReservationModal #instrumentDiv').hide();
                $('#modifyReservationModal #roomReservation #roomReservationDescription').html(calEvent.masterDevice+' in use');
            } else {
                $('#modifyReservationModal #roomReservation').hide();
                $('#modifyReservationModal #instrumentDiv').show();
            }
			// Cannot edit/delete events less than 2 hours before they start
			if(<?php echo $device->getId(); ?>==-1 || calEvent.start.format('X') - 2*60*60 < new Date().getTime()/1000 || calEvent.masterDevice != null){
				$('#modifyReservationModal #reservationWindowTitle').html('Reservation Info');				
			} else {
				$('#modifyReservationModal #reservationWindowTitle').html('Edit Reservation');
			}

			$('#modifyReservationModal #reservationId').val(calEvent.id);
			$('#modifyReservationModal #reservationDescription').val(calEvent.description);
			$('#modifyReservationModal #reservationStartDate').val(calEvent.start.format("YYYY-MM-DD"));
			$('#modifyReservationModal #reservationStartTime').val(calEvent.start.format("h:mma"));
			$('#modifyReservationModal #reservationEndDate').val(calEvent.end.format("YYYY-MM-DD"));
			$('#modifyReservationModal #reservationEndTime').val(calEvent.end.format('h:mma'));
			$('#modifyReservationModal #reservationDevice').text(calEvent.device_name);
			$('#modifyReservationModal #reservationUsername').text(calEvent.username);
			$('#modifyReservationModal #reservationUserId').val(calEvent.userid);
            $('#modifyReservationModal #repeatFormGroup').hide();
			<?php
			if($login_user->isAdmin())
			{
			?>
				$('#modifyReservationModal #trainingFormGroup').show();
				if (calEvent.training==1) {

					$('#modifyReservationModal #reservationTraining').attr("checked", true);
				}
				else
				{
					$('#modifyReservationModal #reservationTraining').removeAttr("checked");
				}
				$('#modifyReservationModal #staffNotes').val(calEvent.staffNotes);
			<?php
			}

			?>
			// Can't update or delete events in the past, or that don't belong to us, unless we're an admin
            const admin = <?php echo $login_user->isAdmin()?"true":"false"; ?>;
			if( calEvent.masterDevice != null || (!admin && (calEvent.start.format('X') - 2*60*60 < new Date().getTime()/1000 || calEvent.userid!=<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>)) ){
				$('#modifyReservationModal #reservationDescription').prop("readonly",true);
				$('#modifyReservationModal #reservationTraining').prop("disabled",true);
				$('#modifyReservationModal #reservationStartTime').prop( "readonly", true );
				$('#modifyReservationModal #reservationEndTime').prop( "readonly", true );
				$('#modifyReservationModal #deleteReservation').hide();
				$('#modifyReservationModal #updateReservation').hide();
			} else {
                $('#modifyReservationModal #reservationDescription').prop("readonly", false);
                $('#modifyReservationModal #reservationTraining').prop("disabled", false);
                $('#modifyReservationModal #reservationStartTime').prop("readonly", false);
                $('#modifyReservationModal #reservationEndTime').prop("readonly", false);
                $('#modifyReservationModal #deleteReservation').show();
                $('#modifyReservationModal #updateReservation').show();
            }
			$('#modifyReservationModal').appendTo("body").modal('show');


		},
		dayClick: function (date, allDay, jsEvent, view) {
			if (allDay) {
				// Clicked on the entire day

				$('#calendar').fullCalendar('changeView', 'agendaDay');
				$('#calendar').fullCalendar('gotoDate', date);
			}
		},
		eventDrop: function (event, delta) {
			console.log(event);
			console.log(delta);
			$.ajax({
				url: 'calendar_api.php',
				data: {
					action: 'update_event_time',
					start: event.start.format("YYYY-MM-DD HH:mm:ss"),
					end: event.end.format("YYYY-MM-DD HH:mm:ss"),
					id: event.id,
					device_id: '<?php echo $device->getId(); ?>',
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
					login_session_id: '<?php echo $login_session->get_session_id(); ?>'
				},
				type: "POST",
				success: function (json) {
					$('#calendar').fullCalendar('refetchEvents');

				}
			});
		},
		eventResize: function (event) {
			$.ajax({
				url: 'calendar_api.php',
				data: {
					action: 'update_event_time',
					start: event.start.format("YYYY-MM-DD HH:mm:ss"),
					end: event.end.format("YYYY-MM-DD HH:mm:ss"),
					id: event.id,
					device_id: '<?php echo $device->getId(); ?>',
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
					login_session_id: '<?php echo $login_session->get_session_id(); ?>'
				},
				type: "POST",
				success: function (json) {
					$('#calendar').fullCalendar('refetchEvents');

				}
			});

		},
		eventMouseover: function (event, domEvent) {
			var layer = '<div id="events-layer" class="fc-transparent" style="position:absolute; width:100%; height:100%; top:-1px; text-align:right; z-index:100"></div>';
			$(this).append(layer);
			$("#delbut" + event.id).hide();
			$("#delbut" + event.id).fadeIn(300);
			$("#delbut" + event.id).click(function () {
				$.post("your.php", {eventId: event.id});
				calendar.fullCalendar('refetchEvents');
			});
			$("#edbut" + event.id).hide();
			$("#edbut" + event.id).fadeIn(300);
			$("#edbut" + event.id).click(function () {
				var title = prompt('Current Event Title: ' + event.title + '\n\nNew Event Title: ');

				if (title) {
					$.post("your.php", {eventId: event.id, eventTitle: title});
					$('#calendar').fullCalendar('refetchEvents');
				}
			});
		}
	});
	

	$('#deleteReservation').on('click', function (e) {
		// We don't want this to act as a link so cancel the link action
		e.preventDefault();

		doDeleteReservation();
	});

	function doDeleteReservation() {
		$("#modifyReservationModal").modal('hide');

		var reservationId = $('#reservationId').val();

		if (reservationId) {

			$.ajax({
				url: "calendar_api.php",
				type: "POST",
				data: {
					action: "delete_event",
					id: reservationId,
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
					login_session_id: '<?php echo $login_session->get_session_id(); ?>'
				},
                success: function(data) {
                    console.log(data);
                }
			});
			$('#calendar').fullCalendar('refetchEvents');
		}

	}

	$('#updateReservation').on('click', function (e) {
		// We don't want this to act as a link so cancel the link action
		e.preventDefault();
		
		var reservationId = $('#reservationId').val();
		var reservationStartDate = $('#reservationStartDate').val();
		var reservationEndDate = $('#reservationEndDate').val();
		var reservationStartTime = $('#reservationStartTime').val();
		var reservationEndTime = $('#reservationEndTime').val();
		var reservationUser = $('#reservationUserId').val();
		var reservationUsername = $('#reservationUsername').html();
		
		var reservationStart = reservationStartDate+' '+reservationStartTime;
		var reservationEnd = reservationEndDate+' '+reservationEndTime;
		
		$.ajax({
			url: "calendar_api.php",
			type: "POST",
			async: false,
			data: {
				action: "check_conflicts",
				start: reservationStart,
				end: reservationEnd,
				id: reservationId,
				res_user_id: reservationUser,
				device_id: '<?php echo $device->getId(); ?>',
				user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
				login_session_id: '<?php echo $login_session->get_session_id(); ?>'
			},
			success: function(data){
				console.log(data);
				if(data=="0"){
					alert('Conflict: The device is already reserved during that time.');
				} else if(data=="-1"){
					alert('Conflict: There is already a reservation for '+reservationUsername+' during that time.');
				} else {
					doUpdateReservation();
				}
			}
		});
		
	});
	
	$('#finishedEarly').on('click', function(e){
		$('#modifyReservationModal').modal('hide');
		
		var reservationId = $('#reservationId').val();
		$.ajax({
			url: "calendar_api.php",
			type: "POST",
			async: false,
			data: {
				action: "finish_early",
				id: reservationId,
				user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
				login_session_id: '<?php echo $login_session->get_session_id(); ?>'
			},
			success: function(data){
				console.log(data);
			}
		});
		$('#calendar').fullCalendar('refetchEvents');
	});

	function doUpdateReservation() {
		$("#modifyReservationModal").modal('hide');

		var reservationId = $('#reservationId').val();
		var description = $('#reservationDescription').val();
		var reservationStartDate = $('#reservationStartDate').val();
		var reservationEndDate = $('#reservationEndDate').val();
		var reservationStartTime = $('#reservationStartTime').val();
		var reservationEndTime = $('#reservationEndTime').val();
		var reservationTraining = $('#reservationTraining').is(":checked")?1:0;
		var reservationRepeatInterval = $('#reservationRepeatInterval').val();
		var reservationRepeat =  $('#reservationRepeat').val();
		const staffNotes = $('#staffNotes').val();
		
		var reservationStart = reservationStartDate+' '+reservationStartTime;
		var reservationEnd = reservationEndDate+' '+reservationEndTime;

		console.log(reservationId);
		if (reservationId) {
			$.ajax({
				url: "calendar_api.php",
				type: "POST",
				data: {
					action: "update_event_info",
					description: description,
					start: reservationStart,
					end: reservationEnd,
					id: reservationId,
					training: reservationTraining,
					device_id: '<?php echo $device->getId(); ?>',
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
					login_session_id: '<?php echo $login_session->get_session_id(); ?>',
					interval: reservationRepeatInterval,
					repeat: reservationRepeat,
                    staffNotes: staffNotes
				},
                success: function(data){
                    console.log(data);
                }
			});
			$('#calendar').fullCalendar('refetchEvents');
		} else {
			// TODO this never gets called
			$.ajax({
				url: "calendar_api.php",
				type: "POST",
				data: {
					action: "add_event",
					descriptions: description,
					start: reservationStart,
					end: reservationEnd,
					training: reservationTraining,
					device_id: '<?php echo $device->getId(); ?>',
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->getId(); ?>',
					login_session_id: '<?php echo $login_session->get_session_id(); ?>',
					interval: reservationRepeatInterval,
					repeat: reservationRepeat
				},
				success: function(data){
					console.log(data);
				}
			})
		}
	}
	
});

</script>
<div id="calendar"></div>
<!-- Modal -->
<div class="modal fade" id="modifyReservationModal" tabindex="-1">

	<div class="modal-dialog">

		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
						class="sr-only">Close</span></button>
				<h3 class="modal-title" id="reservationWindowTitle"><?php if($device->getId()==-1){echo 'Reservation Info';}else{echo 'Edit Reservation';} ?></h3>
			</div>
			<div class="modal-body">
				<form id="editReservationForm" class="form-horizontal">

					<div class="form-group">
						<label class="col-sm-3 control-label">User</label>

						<div class="col-sm-9">
							<div class="controls controls-row" id="reservationUsername" style="margin-top:5px">

							</div>
							<input type="hidden" name="reservationUserId" id="reservationUserId">
						</div>
					</div>

                    <div class="form-group" id="roomReservation">
                        <label class="col-sm-3 control-label">Info</label>
                        <div class="col-sm-9" id="roomReservationDescription" style="margin-top: 5px">

                        </div>
                    </div>

					<div class="form-group" id="instrumentDiv">
						<label class="col-sm-3 control-label">Instrument</label>

						<div class="col-sm-9">
							<div class="controls controls-row" id="reservationDevice" style="margin-top:5px">

							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label" for="reservationDescription">Description</label>

						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="reservationDescription"
								   id="reservationDescription" <?php if($device->getId()==-1){echo 'readonly';} ?>>
							<input type="hidden" name="reservationId" id="reservationId">
							<input type="hidden" name="reservationStart" id="reservationStart">
							<input type="hidden" name="reservationEnd" id="reservationEnd">
						</div>
					</div>

					<div id="trainingFormGroup" class="form-group" style="display: none;">
						<label class="col-sm-3 control-label" for="reservationTraining">Training</label>

						<div class="col-sm-9">
							<div class="checkbox">
								<label>
									<input type="checkbox" value="" name="reservationTraining" id="reservationTraining">
								</label>
							</div>
						</div>
					</div>

					<div id="repeatFormGroup" class="form-group" style="display: none;">
						<label class="col-sm-3 control-label" for="reservationRepeat">Repeat</label>

						<div class="col-sm-2">
							<select name="reservationRepeat" id="reservationRepeat" class="form-control">
								<?php
								for ($repeat = 0; $repeat <= 30; $repeat++) {
									echo "<option value=" . $repeat . " >" . $repeat . "</option>";
								}
								?>
							</select>
						</div>
						<div class="col-sm-3">
							<select name="reservationRepeatInterval" id="reservationRepeatInterval" class="form-control">
								<option value="1">Daily</option>
								<option value="7">Weekly</option>
								</select>
							</div>
						<div class="col-sm-3">

						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Start</label>

						<div class="col-sm-9">
							<input type="hidden" name="reservationStartDate" id="reservationStartDate">
							<select name='reservationStartTime' id='reservationStartTime' class='form-control'>
								<?php echo $timepicker_html; ?>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-3 control-label">End</label>
						<div class="col-sm-9">
							<input type="hidden" name="reservationEndDate" id="reservationEndDate">
							<select name='reservationEndTime' id='reservationEndTime' class='form-control'>
								<?php echo $timepicker_html; ?>
							</select>
						</div>
					</div>

					<div class="form-group" id="finishedEarlyDiv" style="display:none">
						<div class="col-sm-3"></div>
						<div class="col-sm-9">
							<button type="button" class="btn btn-info" id="finishedEarly">I Finished Early!</button><br/>
							Click here if you are finished with the instrument, to let other users know it's available.
						</div>
					</div>

                    <?php if($login_user->isAdmin()){ ?>
                    <div class="form-group" id="staffNotesDiv">
                        <label class="col-sm-3 control-label" for="staffNotes">Staff Notes</label>
                        <div class="col-sm-9">
                            <textarea name="staffNotes" id="staffNotes" class="form-control"></textarea>
                        </div>
                    </div>
                    <?php } ?>
					
					<input type="hidden" name="reservationId" id="reservationId">
					<input type="hidden" name="reservationStart" id="reservationStart">
					<input type="hidden" name="reservationEnd" id="reservationEnd">
				</form>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<?php if($device->getId()!=-1){ ?>
				<button type="submit" id="deleteReservation" class="btn btn-primary">Delete</button>
				<button type="submit" id="updateReservation" class="btn btn-primary">Save</button>
				<?php } ?>
			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
	
</script>
<?php
	require_once 'includes/footer.inc.php';
