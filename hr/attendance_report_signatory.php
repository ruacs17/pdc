<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$filename='';$payroll_type='';$selMonFrom='';$selDayFrom='';$selYrFrom=date('Y');$selMonTo='';$selDayTo='';$selYrTo=date('Y');

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$attendance_ready = $rEatID['attendance_ready'] ?? 0;
$prepared_by = $rEatID['prepared_by'] ?? '';
$checked_by = $rEatID['checked_by'] ?? '';
$received_by = $rEatID['received_by'] ?? '';
$approved_by = $rEatID['approved_by'] ?? '';
$payroll_no = $rEatID['payroll_no'] ?? '';
$proj_id = $rEatID['proj_id'] ?? '';
$payroll_type = $rEatID['payroll_type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Signatory Setting</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->

	<style type="text/css">
		body {
			background-color: #FAF8F5 !important;
			color: #4A3B32 !important;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
		}

		/* Container Card */
		.card-box {
			background: #FFFFFF !important;
			border: 1px solid #E6E1DA !important;
			border-radius: 8px !important;
			box-shadow: 0 4px 12px rgba(74, 59, 50, 0.05) !important;
			/*overflow: hidden !important;*/
			margin: 15px auto !important;
			max-width: 680px !important;
		}

		.card-header-custom {
			background-color: #4A3B32 !important;
			color: #F7F5F0 !important;
			padding: 16px 24px !important;
			border-bottom: 3px solid #8C6D58 !important;
		}

		.card-header-custom h2 {
			color: #F7F5F0 !important;
			margin: 0 !important;
			font-size: 16px !important;
			font-weight: 700 !important;
			letter-spacing: 0.6px !important;
			text-transform: uppercase !important;
			line-height: 1.2 !important;
		}

		.card-body-custom {
			padding: 28px 32px !important;
		}

		/* Form Structure */
		.signatory-form {
			margin: 0 !important;
		}

		.form-group-custom {
			display: flex !important;
			align-items: center !important;
			margin-bottom: 20px !important;
		}

		.form-label-custom {
			width: 32% !important;
			text-align: right !important;
			padding-right: 20px !important;
			font-size: 13px !important;
			font-weight: 700 !important;
			color: #4A3B32 !important;
			text-transform: uppercase !important;
			letter-spacing: 0.4px !important;
		}

		.form-control-wrapper {
			width: 68% !important;
		}

		/* Styled Form Inputs & Selects */
		.input-earthy, 
		.select-earthy {
			width: 340px !important;
			max-width: 340px !important;
			height: 38px !important;
			padding: 6px 12px !important;
			background-color: #FAF8F5 !important;
			border: 1px solid #D0C9C0 !important;
			border-radius: 5px !important;
			color: #3A2F28 !important;
			font-size: 13.5px !important;
			box-sizing: border-box !important;
			transition: all 0.2s ease-in-out !important;
		}

		.input-earthy:focus, 
		.select-earthy:focus {
			background-color: #FFFFFF !important;
			border-color: #8C6D58 !important;
			outline: none !important;
			box-shadow: 0 0 0 3px rgba(140, 109, 88, 0.18) !important;
		}

		/* Override Chosen plugin styles if present */
		.chzn-container {
			width: 100% !important;
			max-width: 340px !important;
		}
		.chzn-container-single .chzn-single {
			height: 36px !important;
			line-height: 36px !important;
			background: #FAF8F5 !important;
			border: 1px solid #D0C9C0 !important;
			border-radius: 5px !important;
			color: #3A2F28 !important;
			box-shadow: none !important;
		}
		.chzn-container-active .chzn-single {
			border-color: #8C6D58 !important;
			box-shadow: 0 0 0 3px rgba(140, 109, 88, 0.18) !important;
		}

		/* Action Buttons */
		.form-actions-custom {
			display: flex !important;
			justify-content: flex-end !important;
			padding-top: 15px !important;
			border-top: 1px dashed #E6E1DA !important;
			margin-top: 25px !important;
			padding-right: 30px !important;
		}

		.btn-earthy-primary {
			background-color: #8C6D58 !important;
			color: #FFFFFF !important;
			border: 1px solid #755946 !important;
			padding: 9px 24px !important;
			font-size: 13px !important;
			font-weight: 700 !important;
			text-transform: uppercase !important;
			letter-spacing: 0.5px !important;
			border-radius: 5px !important;
			cursor: pointer !important;
			box-shadow: 0 2px 4px rgba(74, 59, 50, 0.15) !important;
			transition: all 0.2s ease-in-out !important;
		}

		.btn-earthy-primary:hover {
			background-color: #4A3B32 !important;
			border-color: #3A2F28 !important;
			color: #FFFFFF !important;
			box-shadow: 0 4px 8px rgba(74, 59, 50, 0.2) !important;
		}

		/* Modern Modal Confirmation Box */
		.modal-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(40, 32, 27, 0.55);
			backdrop-filter: blur(2px);
			z-index: 9999;
			align-items: center;
			justify-content: center;
			animation: fadeIn 0.2s ease-out;
		}

		.modal-card {
			background: #FFFFFF;
			border-radius: 8px;
			width: 90%;
			max-width: 420px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
			border: 1px solid #E6E1DA;
			overflow: hidden;
			animation: slideDown 0.25s ease-out;
		}

		.modal-card-header {
			background-color: #4A3B32;
			color: #F7F5F0;
			padding: 14px 20px;
			font-size: 15px;
			font-weight: 700;
			border-bottom: 3px solid #8C6D58;
		}

		.modal-card-body {
			padding: 22px 20px;
			font-size: 14px;
			color: #4A3B32;
			line-height: 1.5;
		}

		.modal-card-footer {
			background-color: #FAF8F5;
			padding: 12px 20px;
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			border-top: 1px solid #E6E1DA;
		}

		.btn-modal-cancel {
			background-color: #EFECE6;
			color: #594C42;
			border: 1px solid #D0C9C0;
			padding: 7px 16px;
			font-size: 12px;
			font-weight: 700;
			border-radius: 4px;
			cursor: pointer;
		}
		.btn-modal-cancel:hover {
			background-color: #E2DDD5;
		}

		.btn-modal-confirm {
			background-color: #8C6D58;
			color: #FFFFFF;
			border: 1px solid #755946;
			padding: 7px 18px;
			font-size: 12px;
			font-weight: 700;
			border-radius: 4px;
			cursor: pointer;
		}
		.btn-modal-confirm:hover {
			background-color: #4A3B32;
		}

		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}

		@keyframes slideDown {
			from { transform: translateY(-15px); opacity: 0; }
			to { transform: translateY(0); opacity: 1; }
		}

		@media (max-width: 600px) {
			.form-group-custom {
				flex-direction: column !important;
				align-items: flex-start !important;
			}
			.form-label-custom {
				width: 100% !important;
				text-align: left !important;
				margin-bottom: 6px !important;
			}
			.form-control-wrapper {
				width: 100% !important;
			}
			.input-earthy, .select-earthy {
				max-width: 340px !important;
			}
			.form-actions-custom {
				padding-right: 0 !important;
				justify-content: center !important;
			}
		}
	</style>

<?php
if( isset($_POST['btnSave']) ){
	$prepared_by = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? trim($_POST['txPreparedBy']) : NULL;
	$checked_by = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? trim($_POST['txCheckedBy']) : NULL;
	$received_by = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? trim($_POST['txReceivedBy']) : NULL;
	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? trim($_POST['txApprovedBy']) : NULL;
	$payroll_no = ( isset($_POST['txPayrollNo']) ) ? trim($_POST['txPayrollNo']) : NULL;

	$arrField = array('prepared_by'=>$prepared_by,'checked_by'=>$checked_by,'received_by'=>$received_by,'approved_by'=>$approved_by);
	if( $eatid ){
		$db->update('emp_attendance',$arrField,array('eat_id'=>$eatid));
		if($attendance_ready){//For payroll ready only
			$db->update('emp_attendance',$arrField,array('eat_id'=>$eatid));
			$countPN = $db->getValue('emp_attendance','count(*)',array('payroll_no'=>$payroll_no,'payroll_type'=>$payroll_type),'AND eat_id!="'.$db->clean($eatid).'" AND proj_id="'.$db->clean($proj_id).'"');

			if( $countPN==0 ){
				$db->update('emp_attendance',array('payroll_no'=>$payroll_no),array('eat_id'=>$eatid));
				$_SESSION['notif_success']='Changes Saved!';
				functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
				die();
			}
			else{
				functions::say('Payroll Number already exist!');
			}
		}
		else{
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
			die();
		}
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="card-box">
		<div class="card-header-custom">
			<h2>Attendance Report Signatory</h2>
		</div>
		<div class="card-body-custom">
			<form id="signatoryForm" class="signatory-form" method="post" onSubmit="return handleFormSubmit(event)">
				
				<?php if($attendance_ready){?>
				<div class="form-group-custom">
					<label class="form-label-custom" for="txPayrollNo">Payroll No.</label>
					<div class="form-control-wrapper">
						<input type="text" name="txPayrollNo" id="txPayrollNo" class="input-earthy" value="<?php echo htmlspecialchars($payroll_no)?>">
					</div>
				</div>
				<?php }?>

				<div class="form-group-custom">
					<label class="form-label-custom" for="txPreparedBy">Prepared By</label>
					<div class="form-control-wrapper">
						<select name="txPreparedBy" id="txPreparedBy" class="select-earthy" data-rel="chosen">
							<option value="">-- select employee --</option>
							<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
							while($rEU = $db->fetch_array($qEU)):
							?>
							<option value="<?php echo $rEU['emp_id']?>" <?php if($prepared_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
							<?php endwhile;?>
						</select>
					</div>
				</div>

				<div class="form-group-custom">
					<label class="form-label-custom" for="txCheckedBy">Checked By</label>
					<div class="form-control-wrapper">
						<select name="txCheckedBy" id="txCheckedBy" class="select-earthy" data-rel="chosen">
							<option value="">-- select employee --</option>
							<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
							while($rEU = $db->fetch_array($qEU)):
							?>
							<option value="<?php echo $rEU['emp_id']?>" <?php if($checked_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
							<?php endwhile;?>
						</select>
					</div>
				</div>

				<div class="form-group-custom">
					<label class="form-label-custom" for="txReceivedBy">Received By</label>
					<div class="form-control-wrapper">
						<select name="txReceivedBy" id="txReceivedBy" class="select-earthy" data-rel="chosen">
							<option value="">-- select employee --</option>
							<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
							while($rEU = $db->fetch_array($qEU)):
							?>
							<option value="<?php echo $rEU['emp_id']?>" <?php if($received_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
							<?php endwhile;?>
						</select>
					</div>
				</div>

				<div class="form-group-custom">
					<label class="form-label-custom" for="txApprovedBy">Approved By</label>
					<div class="form-control-wrapper">
						<select name="txApprovedBy" id="txApprovedBy" class="select-earthy" data-rel="chosen">
							<option value="">-- select employee --</option>
							<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
							while($rEU = $db->fetch_array($qEU)):
							?>
							<option value="<?php echo $rEU['emp_id']?>" <?php if($approved_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
							<?php endwhile;?>
						</select>
					</div>
				</div>

				<div class="form-actions-custom">
					<input type="hidden" name="btnSave" value="1">
					<button type="submit" class="btn-earthy-primary">Save Signatories</button>
					&nbsp;&nbsp;
					<a href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid);?>" class="btn btn-warning" style="border-radius: 5px !important;padding-top: 7px !important;">BACK</a>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modern Offline Confirmation Modal -->
<div id="confirmModal" class="modal-overlay">
	<div class="modal-card">
		<div class="modal-card-header">Confirm Changes</div>
		<div class="modal-card-body">
			Are you sure you want to save these signatory changes?
		</div>
		<div class="modal-card-footer">
			<button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
			<button type="button" class="btn-modal-confirm" onclick="confirmFormSubmit()">Confirm & Save</button>
		</div>
	</div>
</div>

<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/fullcalendar.min.js'></script>
<script src='../js/jquery.dataTables.min.js'></script>
<script src="../js/excanvas.js"></script>
<script src="../js/jquery.flot.js"></script>
<script src="../js/jquery.flot.pie.js"></script>
<script src="../js/jquery.flot.stack.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.chosen.min.js"></script>
<script src="../js/jquery.uniform.min.js"></script>
<script src="../js/jquery.cleditor.min.js"></script>
<script src="../js/jquery.noty.js"></script>
<script src="../js/jquery.elfinder.min.js"></script>
<script src="../js/jquery.raty.min.js"></script>
<script src="../js/jquery.iphone.toggle.js"></script>
<script src="../js/jquery.uploadify-3.1.min.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/jquery.imagesloaded.js"></script>
<script src="../js/jquery.masonry.min.js"></script>
<script src="../js/jquery.knob.modified.js"></script>
<script src="../js/jquery.sparkline.min.js"></script>
<script src="../js/counter.js"></script>
<script src="../js/retina.js"></script>
<script src="../js/custom.js"></script>

<script type="text/javascript">
var formToSubmit = null;

function handleFormSubmit(event) {
	event.preventDefault();
	formToSubmit = event.target;
	openModal();
	return false;
}

function openModal() {
	var modal = document.getElementById('confirmModal');
	if (modal) {
		modal.style.display = 'flex';
	}
}

function closeModal() {
	var modal = document.getElementById('confirmModal');
	if (modal) {
		modal.style.display = 'none';
	}
	formToSubmit = null;
}

function confirmFormSubmit() {
	if (formToSubmit) {
		var form = formToSubmit;
		formToSubmit = null;
		form.submit();
	}
}
</script>

<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>

<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>