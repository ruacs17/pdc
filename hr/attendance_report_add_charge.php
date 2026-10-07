<?php 
require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$filename=''; $payroll_type=''; $selMonFrom=''; $selDayFrom=''; $selYrFrom=date('Y'); 
$selMonTo=''; $selDayTo=''; $selYrTo=date('Y'); $worker_type=''; $eas_name='';

$frmSummary = (isset($_REQUEST['frm']) && !empty($_REQUEST['frm']) ) ? $_REQUEST['frm'] : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$eat_id = $rEatID['eat_id'] ?? NULL;
$proj_id = $rEatID['proj_id'] ?? NULL;
$eas_name = $rEatID['note'] ?? NULL;
$worker_type = $rEatID['payroll_type'] ?? NULL;

$att_year = $rEatID['att_year'] ?? NULL;
$att_month = $rEatID['att_month'] ?? NULL;
$date_start = $rEatID['date_start'] ?? NULL;
$date_end = $rEatID['date_end'] ?? NULL;
$date_start_arr = isset($rEatID['date_start']) ? explode('-',$rEatID['date_start']) : "";
$selMonFrom = ( isset($date_start_arr[1]) ) ? $date_start_arr[1] : '';
$selDayFrom = ( isset($date_start_arr[2]) ) ? $date_start_arr[2] : '';
$selYrFrom = ( isset($date_start_arr[0]) ) ? $date_start_arr[0] : '';

$date_end_arr = isset($rEatID['date_end']) ? explode('-',$rEatID['date_end']) : "";
$selMonTo = ( isset($date_end_arr[1]) ) ? $date_end_arr[1] : '';
$selDayTo = ( isset($date_end_arr[2]) ) ? $date_end_arr[2] : '';
$selYrTo = ( isset($date_end_arr[0]) ) ? $date_end_arr[0] : '';

$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));
$count_personnel = $db->getValue('emp_attendance_personnel','count(*)',array('eat_id'=>$eatid));
if($eatid==0){
	$selYrFrom=date('Y');
	$selYrTo=date('Y');
	$att_year=date('Y');
}

if( isset($_POST['btnCreate']) ){
	$eat_id='';
	$proj_id = ( isset($_POST['selProjAssignment']) && !empty($_POST['selProjAssignment']) ) ? functions::decode($_POST['selProjAssignment']) : 0;
	$worker_type = ( isset($_POST['selPayType']) && !empty($_POST['selPayType']) ) ? $_POST['selPayType'] : '';
	$eas_name = ( isset($_POST['txNote']) && !empty($_POST['txNote']) ) ? trim($_POST['txNote']) : '';

	$date_start = ( isset($_POST['txDateFrom']) && functions::valid_date(trim($_POST['txDateFrom'])) ) ? trim($_POST['txDateFrom']) : '';
	$date_end = ( isset($_POST['txDateTo']) && functions::valid_date(trim($_POST['txDateTo'])) ) ? trim($_POST['txDateTo']) : '';
	if( empty($date_start) || empty($date_end)){
		$date_start="";$date_end="";
	}
	if($date_start && $date_end && $proj_id && $worker_type){

		$pdates = functions::date_diff($date_start,$date_end);
		$arrDt=array();
		$arrYr=array();
		if($pdates){
			for($i=0;$i<=$pdates;$i++):
				$succeeding_date = functions::AddDay($date_start,$i);
				$monthName = date('m', strtotime($succeeding_date));
				$yrName = date('Y', strtotime($succeeding_date));

				if(!isset($arrYr[$yrName]))
					$arrYr[$yrName]=0;
				$arrYr[$yrName]++;

				if(!isset($arrDt[$monthName]))
					$arrDt[$monthName]=0;
				$arrDt[$monthName]++;
			endfor;
		}
		asort($arrDt);
		foreach($arrDt as $armnth => $cntMnth)
			$att_month = $armnth;

		asort($arrYr);
		foreach($arrYr as $aryr => $cntYr)
			$att_year = $aryr;

		$dates = functions::date_diff($date_start,$date_end,$includeDay1=1);
		if($dates >=1 ){
			if($eatid){ // Update existing attendance
				$db->update('emp_attendance',array('date_added'=>date('Y-m-d'),'date_start'=>$date_start,'date_end'=>$date_end,'att_year'=>$att_year,'att_month'=>$att_month,'proj_id'=>$proj_id,'payroll_type'=>$worker_type,'note'=>$eas_name),array('eat_id'=>$eatid));
				$_SESSION['notif_success']='Changes Saved!';
				functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
				die();
			}
			else { // Insert new attendance record
				$is_project = $db->getValue('project','project',array('proj_id'=>$proj_id));
				if($is_project == 0){
					$last_payroll_no = $db->getValue('emp_attendance','payroll_count',array('proj_id'=>$proj_id,'att_year'=>$att_year),'ORDER BY payroll_count DESC LIMIT 1');
					if($last_payroll_no > 0){
						if( $db->getValue('emp_attendance','count(*)',array('proj_id'=>$proj_id,'att_year'=>$att_year,'att_month'=>$att_month,),'AND (date_start BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" OR date_end BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'")')==0 ){
							$last_payroll_no = floor($last_payroll_no + 1);
						}
						else
							$last_payroll_no += .1;
					}
					else
						$last_payroll_no = 1;
					$payroll_no = $selYrFrom.'-'.$last_payroll_no;
				}
				else{
					$last_payroll_no = $db->getValue('emp_attendance','payroll_count',array('proj_id'=>$proj_id),'ORDER BY payroll_count DESC LIMIT 1');
					if($last_payroll_no > 0){
						if( $db->getValue('emp_attendance','count(*)',array('proj_id'=>$proj_id),'AND (date_start BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" OR date_end BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'")')==0 ){
							$last_payroll_no = floor($last_payroll_no + 1);
						}
						else 
							$last_payroll_no += .1;
					}
					else
						$last_payroll_no = 1;
					$payroll_no = $last_payroll_no;
				}

				$sig_form = 'payroll';
				$prepared_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Prepared By'));
				$checked_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Checked By'));
				$received_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Received By'));
				$approved_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Approved By'));

				$prepared_by = ($prepared_by) ? $prepared_by : NULL;
				$checked_by = ($checked_by) ? $checked_by : NULL;
				$received_by = ($received_by) ? $received_by : NULL;
				$approved_by = ($approved_by) ? $approved_by : NULL;

				$eat_id = $db->insert('emp_attendance',array('date_added'=>date('Y-m-d'),'date_start'=>$date_start,'date_end'=>$date_end,'att_year'=>$att_year,'att_month'=>$att_month,'proj_id'=>$proj_id,'payroll_type'=>$worker_type,'payroll_no'=>$payroll_no,'payroll_count'=>$last_payroll_no,'note'=>$eas_name,'prepared_by'=>$prepared_by,'checked_by'=>$checked_by,'received_by'=>$received_by,'approved_by'=>$approved_by));
				if($eat_id){
					$_SESSION['notif_success']='Attendance Report Successfully Created!';
					functions::sendTo('attendance_report_add_personnel.php?eatid='.functions::encode($eat_id).'&frm='.$frmSummary);
					die();
				}
				else{
					$_SESSION['notif_warning']='Failed to create attendance record!';
				}
			}
		}
		else{
			$_SESSION['notif_warning']='Please fill up the form properly!';
		}
	}
	else{
		$_SESSION['notif_warning']='Please fill up the form properly.!';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Employee Attendance Upload</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
	
	<!-- Earthy Brown Theme & Vertical Side-by-Side Layout Styling -->
	<style type="text/css">
		/* Custom Earthy Brown Card Header */
		div.card-header-custom {
			background-color: #4A3B32 !important;
			color: #F7F5F0 !important;
			padding: 14px 20px !important;
			border-top-left-radius: 6px !important;
			border-top-right-radius: 6px !important;
			margin-bottom: 0 !important;
			border-bottom: 3px solid #8C6D58 !important;
		}
		div.card-header-custom h2 {
			color: #F7F5F0 !important;
			margin: 0 !important;
			font-size: 16px !important;
			font-weight: 600 !important;
			letter-spacing: 0.5px !important;
			line-height: 1.2 !important;
		}

		/* Main Flex Grid: Left Content, Right Sticky Wizard */
		.page-layout-grid {
			display: flex !important;
			justify-content: space-between !important;
			align-items: flex-start !important;
			padding: 20px 0 !important;
			gap: 30px !important;
		}
		
		/* Expanded Form Content Area */
		.main-content-area {
			flex: 1 !important;
			max-width: 80%;
			min-width: 0 !important;
		}

		/* Sticky Sidebar Area Pinned to Far Right */
		.sidebar-wizard-area {
			width: 250px !important;
			flex-shrink: 0 !important;
			position: sticky !important;
			top: 20px !important;
			margin-left: auto !important;
		}

		/* Vertical Process Wizard Navigation */
		.process-wizard-vertical {
			display: flex !important;
			flex-direction: column !important;
			margin: 0 !important;
			padding: 20px 15px !important;
			list-style: none !important;
			background: #FAF8F5 !important;
			border: 1px solid #E6E1DA !important;
			border-radius: 8px !important;
			box-shadow: 0 2px 8px rgba(74, 59, 50, 0.05) !important;
		}
		.wizard-title {
			font-size: 11px !important;
			text-transform: uppercase !important;
			letter-spacing: 0.8px !important;
			font-weight: 800 !important;
			color: #8C6D58 !important;
			margin-bottom: 15px !important;
			padding-bottom: 8px !important;
			border-bottom: 2px solid #E0DCD5 !important;
			text-align: center !important;
		}
		.process-step {
			position: relative !important;
			padding-bottom: 25px !important;
		}
		.process-step:last-child {
			padding-bottom: 0 !important;
		}

		/* Vertical Connecting Line */
		.process-step:not(:last-child)::after {
			content: '' !important;
			position: absolute !important;
			left: 20px !important;
			top: 20px !important;
			bottom: -25px !important;
			width: 3px !important;
			background-color: #E6E1DA !important;
			z-index: 1 !important;
			transition: background-color 0.3s ease !important;
		}
		.process-step.active:not(:last-child)::after,
		.process-step.complete:not(:last-child)::after {
			background-color: #8C6D58 !important;
		}

		.process-step a {
			display: flex !important;
			align-items: center !important;
			gap: 12px !important;
			text-decoration: none !important;
			position: relative !important;
			z-index: 2 !important;
			transition: all 0.25s ease-in-out !important;
		}
		
		/* Step Badge Icon */
		.step-icon {
			width: 40px !important;
			height: 40px !important;
			border-radius: 50% !important;
			background-color: #FFFFFF !important;
			color: #A3968A !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			font-weight: 600 !important;
			font-size: 15px !important;
			border: 2px solid #E6E1DA !important;
			box-shadow: 0 2px 4px rgba(0,0,0,0.03) !important;
			transition: all 0.25s ease-in-out !important;
			flex-shrink: 0 !important;
		}

		.step-label {
			font-size: 13px !important;
			font-weight: 600 !important;
			color: #8C7E72 !important;
			transition: all 0.25s ease-in-out !important;
			line-height: 1.2 !important;
		}

		/* Active Step Styling with Pulse Ring Effect */
		.process-step.active .step-icon {
			background-color: #4A3B32 !important;
			color: #FFFFFF !important;
			font-size: 16px !important;
			border: 3px solid #8C6D58 !important;
			transform: scale(1.12) !important;
			box-shadow: 0 0 0 4px rgba(140, 109, 88, 0.25), 0 4px 10px rgba(74, 59, 50, 0.3) !important;
			animation: pulse-ring 2.5s infinite !important;
		}
		@keyframes pulse-ring {
			0% {
				box-shadow: 0 0 0 0px rgba(140, 109, 88, 0.4), 0 4px 10px rgba(74, 59, 50, 0.3);
			}
			70% {
				box-shadow: 0 0 0 7px rgba(140, 109, 88, 0), 0 4px 10px rgba(74, 59, 50, 0.3);
			}
			100% {
				box-shadow: 0 0 0 0px rgba(140, 109, 88, 0), 0 4px 10px rgba(74, 59, 50, 0.3);
			}
		}

		.process-step.active .step-label {
			color: #4A3B32 !important;
			font-weight: 800 !important;
			font-size: 13px !important;
		}

		/* Completed State */
		.process-step.complete .step-icon {
			background-color: #FAF8F5 !important;
			color: #8C6D58 !important;
			border-color: #8C6D58 !important;
		}
		.process-step.complete .step-label {
			color: #8C6D58 !important;
		}

		/* Interactive Hover State */
		.process-step:not(.active):not(.disabled):hover .step-icon {
			background-color: #4A3B32 !important;
			color: #FFFFFF !important;
			border-color: #4A3B32 !important;
			transform: scale(1.08) !important;
			box-shadow: 0 4px 10px rgba(74, 59, 50, 0.2) !important;
		}
		.process-step:not(.active):not(.disabled):hover .step-label {
			color: #4A3B32 !important;
			font-weight: 700 !important;
		}

		.process-step.disabled a {
			cursor: not-allowed !important;
			opacity: 0.45 !important;
		}

		/* Form Container Styles */
		div.form-card-container {
			background: #FAF8F5 !important;
			border: 1px solid #E6E1DA !important;
			border-radius: 6px !important;
			padding: 25px 30px !important;
			box-shadow: 0 2px 6px rgba(74, 59, 50, 0.05) !important;
			box-sizing: border-box !important;
		}
		div.form-card-container h3 {
			margin-top: 0 !important;
			border-bottom: 2px solid #E0DCD5 !important;
			padding-bottom: 10px !important;
			color: #4A3B32 !important;
			font-size: 17px !important;
			font-weight: 600 !important;
		}
		div.form-row {
			display: flex !important;
			align-items: flex-start !important;
			margin-bottom: 18px !important;
		}
		div.form-label-col {
			width: 20% !important;
			min-width: 180px !important;
			font-weight: 600 !important;
			color: #594C42 !important;
			padding-top: 6px !important;
		}
		div.form-input-col {
			width: 80% !important;
			min-height: 40px !important; 
		}
		.wide-dropdown, 
		.chzn-container {
			width: 750px !important;
			max-width: 750px !important;
		}
		div.form-input-col input[type="text"], 
		div.form-input-col select,
		div.form-input-col textarea {
			border-radius: 4px !important;
			border: 1px solid #C8C2B9 !important;
			padding: 6px 12px !important;
			background-color: #FFFFFF !important;
			box-sizing: border-box !important;
		}
		div.form-input-col input[type="text"], 
		div.form-input-col select {
			height: 38px !important;
		}
		div.form-input-col input[type="text"]:focus, 
		div.form-input-col select:focus,
		div.form-input-col textarea:focus {
			border-color: #8C6D58 !important;
			outline: 0 !important;
		}
		
		/* Earthy Action Buttons */
		.btn-earthy-primary {
			background-color: #5C4A3E !important;
			color: #FFFFFF !important;
			border: 1px solid #4A3B32 !important;
			background-image: none !important;
			text-shadow: none !important;
			padding: 7px 18px !important;
		}
		.btn-earthy-primary:hover {
			background-color: #4A3B32 !important;
			color: #FFFFFF !important;
		}

		/* Modal Adjustments */
		.custom-modal {
			border-radius: 8px !important;
			overflow: hidden !important;
			border: 1px solid #C8C2B9 !important;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25) !important;
		}
		.custom-modal .modal-header {
			background-color: #4A3B32 !important;
			color: #F7F5F0 !important;
			padding: 12px 20px !important;
			border-bottom: 2px solid #8C6D58 !important;
		}
		.custom-modal .modal-header h3 {
			color: #F7F5F0 !important;
			margin: 0 !important;
			font-size: 16px !important;
			font-weight: 600 !important;
		}
		.custom-modal .modal-header .close {
			color: #F7F5F0 !important;
			opacity: 0.8 !important;
			text-shadow: none !important;
			margin-top: 2px !important;
		}
		.custom-modal .modal-body {
			padding: 22px 20px !important;
			background-color: #FAF8F5 !important;
			color: #4A3B32 !important;
			font-size: 14px !important;
			line-height: 1.5 !important;
		}
		.custom-modal .modal-footer {
			background-color: #EFECE6 !important;
			border-top: 1px solid #E0DCD5 !important;
			padding: 12px 20px !important;
		}

		/* --- Earthy Native Dropdown Styling --- */
		.select-earthy {
			width: 750px !important;
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
		.select-earthy:focus {
			background-color: #FFFFFF !important;
			border-color: #8C6D58 !important;
			outline: none !important;
			box-shadow: 0 0 0 3px rgba(140, 109, 88, 0.18) !important;
		}

		/* --- Earthy Chosen Dropdown Overrides --- */

		/* Mobile & Tablet Responsive Adjustments */
		@media (max-width: 992px) {
			.page-layout-grid {
				flex-direction: column-reverse !important;
				gap: 25px !important;
			}
			.main-content-area {
				max-width: 100% !important;
			}
			.sidebar-wizard-area {
				width: 100% !important;
				position: static !important;
				margin-left: 0 !important;
			}
			.process-wizard-vertical {
				flex-direction: row !important;
				justify-content: space-between !important;
			}
			.process-step {
				padding-bottom: 0 !important;
				flex: 1 !important;
			}
			.process-step:not(:last-child)::after {
				display: none !important;
			}
			div.form-row {
				flex-direction: column !important;
				align-items: flex-start !important;
			}
			div.form-label-col, div.form-input-col {
				width: 100% !important;
				margin-bottom: 5px !important;
			}
			.wide-dropdown, 
			#selProjAssignment,
			.chzn-container {
				width: 700px; !important;
			}
		}
	</style>

	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<link rel="shortcut icon" href="../img/favicon.png">
</head>
<body>
<div class="row-fluid">
	<div class="box span12">
		<div class="card-header-custom" style="display:none;">
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ATTENDANCE REPORT MANAGEMENT</h2>
		</div>
		<div class="box-content">
			
			<!-- Flex Layout Container -->
			<div class="page-layout-grid">
				
				<!-- Main Content Form Area (Left Side) -->
				<div class="main-content-area">
					<?php if($frmSummary){?>
					<div align="right" style="margin-bottom: 15px;">
						<a id="icnReupload" href="attendance_summary_view.php?eatid=<?php echo functions::encode($eat_id)?>" class="btn btn-info" title="Back To Attendance Summary"><i class="icon-arrow-left icon-white"></i> Back To Attendance Summary</a>
					</div>
					<?php } ?>

					<form class="form-horizontal" method="post" id="frmAtt">
						<div class="form-card-container">
							<h3>Step 1: Attendance Details</h3>
							
							<div class="form-row">
								<div class="form-label-col">Project / Department <span class="text-error">*</span></div>
								<div class="form-input-col">
									<select name="selProjAssignment" id="selProjAssignment" class="select-earthy" data-rel="chosen" required>
										<option value="">-- Select Project / Department --</option>
										<?php 
										$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
										while($rProj = $db->fetch_array($qProj)):
										$projName = $rProj['proj_name'];
										$projID = $rProj['proj_id'];
										?>
										<option value="<?php echo functions::encode($projID)?>" <?php if($proj_id==$projID) echo 'selected="selected"';?>><?php echo strtoupper($projName);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>

							<div class="form-row">
								<div class="form-label-col">Date Start <span class="text-error">*</span></div>
								<div class="form-input-col">
									<input type="text" style="width: 180px;" name="txDateFrom" id="txDateFrom" value="<?php echo $date_start ?>" placeholder="YYYY-MM-DD" required>
								</div>
							</div>

							<div class="form-row">
								<div class="form-label-col">Date End <span class="text-error">*</span></div>
								<div class="form-input-col">
									<input type="text" style="width: 180px;" name="txDateTo" id="txDateTo" value="<?php echo $date_end ?>" placeholder="YYYY-MM-DD" required>
								</div>
							</div>

							<div class="form-row">
								<div class="form-label-col">Worker Type <span class="text-error">*</span></div>
								<div class="form-input-col">
									<select name="selPayType" id="selPayType" style="width: 180px;" required>
										<option value="">-- Select Worker Type --</option>
										<option value="admin" <?php if($worker_type=='admin') echo 'selected="selected"';?>>Office Personnel</option>
										<option value="labor" <?php if($worker_type=='labor') echo 'selected="selected"';?>>Labor Group</option>
									</select>
								</div>
							</div>

							<div class="form-row" style="margin-bottom: 25px;">
								<div class="form-label-col">Note / Remarks</div>
								<div class="form-input-col">
									<textarea name="txNote" id="txNote" rows="3" style="width: 320px;" placeholder="Optional details or note"><?php echo $eas_name; ?></textarea>
								</div>
							</div>

							<div class="form-row" style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #E6E1DA;">
								<div class="form-label-col"></div>
								<div class="form-input-col">
									<button type="button" id="btnTriggerModal" class="btn btn-earthy-primary"><i class="icon-ok icon-white"></i> Save & Continue</button>&nbsp;&nbsp;
									<button type="submit" name="btnCreate" id="btnCreate" style="display:none;"></button>
									<?php if($eat_id){?>
										<a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eat_id)?>" class="btn btn-info">Manage Personnel <i class="icon-chevron-right icon-white"></i></a>
									<?php }?>
								</div>
							</div>
						</div>
					</form>
				</div>

				<!-- Right Vertical Process Wizard Sidebar (Pinned Right) -->
				<div class="sidebar-wizard-area">
					<ul class="process-wizard-vertical">
						<div class="wizard-title">Process Steps</div>
						<li class="process-step active">
							<a href="javascript:void(0)">
								<span class="step-icon">1</span>
								<span class="step-label">Manage Details</span>
							</a>
						</li>
						<li class="process-step <?php echo ($eat_id) ? 'complete' : 'disabled'; ?>">
							<a href="<?php echo ($eat_id) ? 'attendance_report_add_personnel.php?eatid='.functions::encode($eat_id) : '#'; ?>">
								<span class="step-icon">2</span>
								<span class="step-label">Manage Personnel</span>
							</a>
						</li>
						<li class="process-step <?php echo ($count_personnel) ? 'complete' : 'disabled'; ?>">
							<a href="<?php echo ($eat_id) ? 'attendance_report_add_attlog_option.php?eatid='.functions::encode($eat_id) : '#'; ?>">
								<span class="step-icon">3</span>
								<span class="step-label">Upload Att. Log</span>
							</a>
						</li>
						<li class="process-step <?php echo ($attendance_detail) ? 'complete' : 'disabled'; ?>">
							<a href="<?php echo ($attendance_detail) ? 'attendance_summary_view.php?eatid='.functions::encode($eat_id) : '#'; ?>">
								<span class="step-icon">4</span>
								<span class="step-label">Summary</span>
							</a>
						</li>
					</ul>
				</div>

			</div>
		</div>
	</div>
</div>

<!-- Modern Earthy Confirmation Modal -->
<div class="modal hide fade custom-modal" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="confirmModalLabel"><i class="icon-question-sign icon-white"></i> Confirm Save Action</h3>
	</div>
	<div class="modal-body">
		<p style="margin: 0;">Are you sure you want to save this attendance report detail? Please verify all dates and selection before proceeding.</p>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button class="btn btn-earthy-primary" id="btnConfirmSubmit"><i class="icon-ok icon-white"></i> Save Changes</button>
	</div>
</div>

<!-- JavaScript dependencies -->
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
$(document).ready(function(){
	// Trigger modern modal on button click
	$('#btnTriggerModal').click(function(e){
		e.preventDefault();
		
		// HTML5 Form Validation check prior to showing modal
		var form = document.getElementById('frmAtt');
		if(form.checkValidity()){
			$('#confirmModal').modal('show');
		} else {
			// Trigger browser standard validation errors if required fields are missing
			$('#btnCreate').click();
		}
	});

	// Confirm form submission inside modal
	$('#btnConfirmSubmit').click(function(){
		$('#confirmModal').modal('hide');
		$('#btnCreate').click();
	});

	$('#txDateFrom').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2019:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			var dt=new Date($('#txDateFrom').val());
			dt.setDate(dt.getDate());
			$('#txDateTo').datepicker('option','minDate',dt);
			$('#txDateTo').datepicker('option','defaultDate',$('#txDateFrom').val());
			var dt2=new Date($('#txDateTo').val());
			dt2.setDate(dt2.getDate());
			$('#txDateFrom').datepicker('option','maxDate',dt2);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate());
			$('#txDateTo').datepicker('option','minDate',dt);
			$('#txDateTo').datepicker('option','defaultDate',$('#txDateFrom').val());
		}
	});

	$('#txDateTo').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2019:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			var dt=new Date($('#txDateFrom').val());
			dt.setDate(dt.getDate());
			$('#txDateTo').datepicker('option','minDate',dt);
			$('#txDateTo').datepicker('option','defaultDate',$('#txDateFrom').val());
			var dt2=new Date($('#txDateTo').val());
			dt2.setDate(dt2.getDate());
			$('#txDateFrom').datepicker('option','maxDate',dt2);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate());
			$('#txDateFrom').datepicker('option','maxDate',dt);
		}
	});
});
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
</body>
</html>