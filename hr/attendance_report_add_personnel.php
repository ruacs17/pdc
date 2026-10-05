<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;

$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$proj_id = $rEatID['proj_id'] ?? NULL;
$date_start = $rEatID['date_start'] ?? NULL;
$date_end = $rEatID['date_end'] ?? NULL;
$worker_type = $rEatID['payroll_type'] ?? NULL;
$note = $rEatID['note'] ?? NULL;

$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));
$count_personnel = $db->getValue('emp_attendance_personnel','count(*)',array('eat_id'=>$eatid));

if( isset($_POST['btnRemoved']) && $eatid ){
	$ArrRemoved = isset($_POST['chkDel']) ? $_POST['chkDel'] : '';
	if(is_array($ArrRemoved)){
		foreach($ArrRemoved as $remID):
			$eas_emp_id = $db->getValue('emp_attendance_personnel','emp_id',array('eap_id'=>$remID));
			$db->delete('emp_attendance_personnel',array('eat_id'=>$eatid,'eap_id'=>$remID));
			$db->delete('emp_attendance_detail',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid));
			$db->delete('payroll_adjustment',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid),'AND eatd_id IS NOT NULL');
			$db->delete('payroll_premium',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid));
		endforeach;
		$_SESSION['notif_warning']='Personnel Removed!';
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}

function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
		endwhile;
		return $position;
}
function work_status($emp_id){
	global $db;
	$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'ORDER BY ews_date DESC');
	$rStat = $db->fetch_array($qStat);
	$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
	$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
	$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <i>Project Based</i>' : '';
	return $wrkStat. $projBased . $wrkDate;
}

$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
if( $eatid && $delID ){
	$eas_emp_id = $db->getValue('emp_attendance_personnel','emp_id',array('eap_id'=>$delID));
	$db->delete('emp_attendance_personnel',array('eap_id'=>$delID));
	$db->delete('emp_attendance_detail',array('emp_id'=>$eas_emp_id,'eat_id'=>$eatid));
	$_SESSION['notif_warning']='Personnel Removed!';
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}
if( isset($_POST['btnAdd']) ){
	$emp_id = ( isset($_POST['selEmp']) && !empty($_POST['selEmp']) ) ? trim($_POST['selEmp']) : '';
	if( $eatid && $emp_id ){
		$arrField = array('emp_id'=>$emp_id,'eat_id'=>$eatid);
		if( $db->getValue('emp_attendance_personnel','count(*)',$arrField)==0 ){
			$db->insert('emp_attendance_personnel',$arrField);
			$_SESSION['notif_success']='Personnel Added!';
			functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
			die();
		}
		else{
			$_SESSION['notif_warning']='Employee already assigned!';
			functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
			die();
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Group Select</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>

	<!-- Earthy Brown Theme & Vertical Layout Styling -->
	<style type="text/css">
		/* Card Header */
		.card-header-custom {
			background-color: #4A3B32;
			color: #F7F5F0;
			padding: 14px 20px;
			border-top-left-radius: 6px;
			border-top-right-radius: 6px;
			margin-bottom: 0;
			border-bottom: 3px solid #8C6D58;
		}
		.card-header-custom h2 {
			color: #F7F5F0 !important;
			margin: 0;
			font-size: 16px;
			font-weight: 600;
			letter-spacing: 0.5px;
			line-height: 1.2;
		}

		/* Main Grid Layout: Content Left, Wizard Pinned Right */
		.page-layout-grid {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			padding: 20px 0;
			gap: 30px;
		}
		
		/* Expanded Main Content Area */
		.main-content-area {
			flex: 1;
			max-width: 80%;
			min-width: 0;
		}

		/* Sidebar anchored to Far Right */
		.sidebar-wizard-area {
			width: 220px;
			flex-shrink: 0;
			position: sticky;
			top: 20px;
			margin-left: auto;
		}

		/* Vertical Process Wizard Navigation */
		.process-wizard-vertical {
			display: flex;
			flex-direction: column;
			margin: 0;
			padding: 20px 15px;
			list-style: none;
			background: #FAF8F5;
			border: 1px solid #E6E1DA;
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(74, 59, 50, 0.05);
		}
		.wizard-title {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.8px;
			font-weight: 800;
			color: #8C6D58;
			margin-bottom: 15px;
			padding-bottom: 8px;
			border-bottom: 2px solid #E0DCD5;
			text-align: center;
		}
		.process-step {
			position: relative;
			padding-bottom: 25px;
		}
		.process-step:last-child {
			padding-bottom: 0;
		}

		/* Continuous Vertical Connector Line */
		.process-step:not(:last-child)::after {
			content: '';
			position: absolute;
			left: 20px;
			top: 20px;
			bottom: -25px;
			width: 3px;
			background-color: #E6E1DA;
			z-index: 1;
			transition: background-color 0.3s ease;
		}
		.process-step.active:not(:last-child)::after,
		.process-step.complete:not(:last-child)::after {
			background-color: #8C6D58;
		}

		.process-step a {
			display: flex;
			align-items: center;
			gap: 12px;
			text-decoration: none;
			position: relative;
			z-index: 2;
			transition: all 0.25s ease-in-out;
		}
		
		/* Step Icon Styling */
		.step-icon {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			background-color: #FFFFFF;
			color: #A3968A;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 600;
			font-size: 15px;
			border: 2px solid #E6E1DA;
			box-shadow: 0 2px 4px rgba(0,0,0,0.03);
			transition: all 0.25s ease-in-out;
			flex-shrink: 0;
		}

		.step-label {
			font-size: 13px;
			font-weight: 600;
			color: #8C7E72;
			transition: all 0.25s ease-in-out;
			line-height: 1.2;
		}

		/* Active Step Styling */
		.process-step.active .step-icon {
			background-color: #4A3B32;
			color: #FFFFFF;
			font-size: 16px;
			border: 3px solid #8C6D58;
			transform: scale(1.12);
			box-shadow: 0 0 0 4px rgba(140, 109, 88, 0.25), 0 4px 10px rgba(74, 59, 50, 0.3);
			animation: pulse-ring 2.5s infinite;
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
			color: #4A3B32;
			font-weight: 800;
			font-size: 13px;
		}

		/* Completed State */
		.process-step.complete .step-icon {
			background-color: #FAF8F5;
			color: #8C6D58;
			border-color: #8C6D58;
		}
		.process-step.complete .step-label {
			color: #8C6D58;
		}

		/* Hover Effects */
		.process-step:not(.active):not(.disabled):hover .step-icon {
			background-color: #4A3B32;
			color: #FFFFFF;
			border-color: #4A3B32;
			transform: scale(1.08);
			box-shadow: 0 4px 10px rgba(74, 59, 50, 0.2);
		}
		.process-step:not(.active):not(.disabled):hover .step-label {
			color: #4A3B32;
			font-weight: 700;
		}

		.process-step.disabled a {
			cursor: not-allowed;
			opacity: 0.45;
		}

		/* Form Container */
		.form-card-container {
			background: #FAF8F5;
			border: 1px solid #E6E1DA;
			border-radius: 6px;
			padding: 25px 30px;
			box-shadow: 0 2px 6px rgba(74, 59, 50, 0.05);
			box-sizing: border-box;
		}

		/* Unified Summary Card Container */
		.unified-summary-card {
			background: #FFFFFF;
			border: 1px solid #E2DDD5;
			border-left: 4px solid #4A3B32;
			border-radius: 6px;
			padding: 20px;
			margin-bottom: 25px;
			box-shadow: 0 2px 5px rgba(74, 59, 50, 0.04);
		}

		/* Top Row: Full Width for Long Project Title */
		.summary-row-top {
			padding-bottom: 15px;
			margin-bottom: 15px;
			border-bottom: 1px dashed #E0DCD5;
		}

		/* Bottom Row: 3 Columns for Period, Worker Type, and Note */
		.summary-row-bottom {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 20px;
		}

		.summary-item {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.summary-item-bordered {
			padding-right: 15px;
			border-right: 1px dashed #E0DCD5;
		}

		.summary-label {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			font-weight: 700;
			color: #8C6D58;
		}
		
		.summary-value {
			font-size: 13px;
			font-weight: 600;
			color: #332B25;
			line-height: 1.4;
		}

		.summary-value-hero {
			font-size: 16px;
			font-weight: 700;
			color: #2C221E;
			word-break: break-word;
		}

		/* Badges & Tags for Metadata */
		.badge-day-count {
			background-color: #EFECE6;
			color: #4A3B32;
			font-weight: 600;
			font-size: 11px;
			padding: 2px 8px;
			border-radius: 10px;
			border: 1px solid #D8D2C7;
			margin-left: 6px;
			display: inline-block;
			vertical-align: middle;
		}
		.badge-worker-type {
			display: inline-block;
			padding: 3px 10px;
			border-radius: 12px;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.3px;
		}
		.badge-type-admin {
			background-color: #E3EBF5;
			color: #2B4C7E;
			border: 1px solid #C4D5EB;
		}
		.badge-type-labor {
			background-color: #EAF3EB;
			color: #2D6A35;
			border: 1px solid #C5E2C8;
		}
		.badge-type-default {
			background-color: #EFECE6;
			color: #594C42;
			border: 1px solid #D8D2C7;
		}

		/* Side-by-Side Control Bar */
		.controls-inline-row {
			display: flex;
			align-items: center;
			gap: 8px;
			margin-bottom: 20px;
			width: 100%;
		}
		.controls-inline-row .chzn-container {
			flex: 1;
			min-width: 400px !important;
		}
		.controls-inline-row .btn {
			white-space: nowrap;
			margin: 0 !important;
		}

		/* Buttons */
		.btn-earthy-primary {
			background-color: #5C4A3E !important;
			color: #FFFFFF !important;
			border: 1px solid #4A3B32 !important;
			background-image: none !important;
			text-shadow: none !important;
			padding: 3px 14px;
		}
		.btn-earthy-primary:hover {
			background-color: #4A3B32 !important;
			color: #FFFFFF !important;
		}

		/* Table Styling */
		.tdpadleft { padding-left: 10px; }
		.table-earthy {
			border: 1px solid #E0DCD5 !important;
			border-radius: 4px;
			overflow: hidden;
		}
		.table-earthy thead tr {
			background-color: #4A3B32 !important;
			color: #F7F5F0;
		}
		.table-earthy thead th {
			color: #F7F5F0 !important;
			border-bottom: 2px solid #8C6D58 !important;
			font-weight: 600;
		}
		.rActive {
			background-color: #E8E2D8 !important;
		}
		.rInActive {
			background-color: #FFFFFF;
		}

		/* Modal Styling */
		.custom-modal {
			border-radius: 8px !important;
			overflow: hidden;
			border: 1px solid #C8C2B9;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
		}
		.custom-modal .modal-header {
			background-color: #4A3B32;
			color: #F7F5F0;
			padding: 12px 20px;
			border-bottom: 2px solid #8C6D58;
		}
		.custom-modal .modal-header h3 {
			color: #F7F5F0;
			margin: 0;
			font-size: 16px;
			font-weight: 600;
		}
		.custom-modal .modal-header .close {
			color: #F7F5F0;
			opacity: 0.8;
			text-shadow: none;
			margin-top: 2px;
		}
		.custom-modal .modal-body {
			padding: 22px 20px;
			background-color: #FAF8F5;
			color: #4A3B32;
			font-size: 14px;
		}
		.custom-modal .modal-footer {
			background-color: #EFECE6;
			border-top: 1px solid #E0DCD5;
			padding: 12px 20px;
		}

		/* Responsive Adjustments */
		@media (max-width: 992px) {
			.page-layout-grid {
				flex-direction: column-reverse;
				gap: 25px;
			}
			.main-content-area {
				max-width: 100%;
			}
			.sidebar-wizard-area {
				width: 100%;
				position: static;
				margin-left: 0;
			}
			.process-wizard-vertical {
				flex-direction: row;
				justify-content: space-between;
			}
			.process-step {
				padding-bottom: 0;
				flex: 1;
			}
			.process-step:not(:last-child)::after {
				display: none;
			}
			.summary-row-bottom {
				grid-template-columns: 1fr;
			}
			.summary-item-bordered {
				border-right: none;
				padding-right: 0;
				padding-bottom: 10px;
				border-bottom: 1px dashed #E0DCD5;
			}
			.summary-item-bordered:last-child {
				border-bottom: none;
				padding-bottom: 0;
			}
			.controls-inline-row { flex-wrap: wrap; }
			.controls-inline-row .chzn-container { width: 100% !important; }
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
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="card-header-custom" style="display:none;">
			<h2><i class="halflings-icon white user"></i><span class="break"></span>ATTENDANCE REPORT MANAGEMENT</h2>
		</div>
		<div class="box-content">
			
			<!-- Grid Layout Container -->
			<div class="page-layout-grid">
				
				<!-- Main Left Content Area -->
				<div class="main-content-area">
					<div class="form-card-container">
						<h3 style="margin-top: 0; border-bottom: 2px solid #E0DCD5; padding-bottom: 10px; color: #4A3B32; font-size: 17px; font-weight: 600;">Step 2: Manage Assigned Personnel</h3>

						<!-- Unified Metadata Summary Card -->
						<div class="unified-summary-card">
							<!-- Full Row: Project / Department (Optimized for long text) -->
							<div class="summary-row-top">
								<div class="summary-item">
									<span class="summary-label">Project / Department</span>
									<span class="summary-value summary-value-hero"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></span>
								</div>
							</div>

							<!-- 3-Column Bottom Row: Period Cover, Worker Type, Note -->
							<div class="summary-row-bottom">
								
								<!-- Period Cover -->
								<div class="summary-item summary-item-bordered">
									<span class="summary-label">Period Cover</span>
									<span class="summary-value">
										<?php echo functions::datearr($date_start).' - '.functions::datearr($date_end); ?>
										<span class="badge-day-count"><?php echo functions::date_diff($date_start,$date_end,$includeDay1=1); ?> days</span>
									</span>
								</div>

								<!-- Worker Type -->
								<div class="summary-item summary-item-bordered">
									<span class="summary-label">Worker Type</span>
									<span class="summary-value">
										<?php 
										if($worker_type=="admin"){
											echo '<span class="badge-worker-type badge-type-admin">Office Personnel</span>';
										} elseif($worker_type=="labor"){
											echo '<span class="badge-worker-type badge-type-labor">Labor Group</span>';
										} else {
											echo '<span class="badge-worker-type badge-type-default">Undefined</span>';
										}
										?>
									</span>
								</div>

								<!-- Note / Remarks -->
								<div class="summary-item">
									<span class="summary-label">Note / Remarks</span>
									<span class="summary-value" style="font-style: italic; font-weight: normal; color: #55483F; word-break: break-word;">
										<?php echo ($note) ? $note : '<span style="color: #A3968A;">N/A</span>'; ?>
									</span>
								</div>

							</div>
						</div>

						<form class="form-horizontal" id="showform" name="showform" method="post">
							<!-- Side-by-Side Controls Bar (selEmp, btnAdd, imprt, lnkUpld) -->
							<div class="controls-inline-row">
								<select name="selEmp" id="selEmp" data-rel="chosen" style="width: 400px;">
									<option value="">-- Select Employee --</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>

								<button type="submit" name="btnAdd" id="btnAdd" class="btn btn-earthy-primary btn-small" title="Add Individual Personnel" data-rel="tooltip">
									<i class="icon-plus icon-white"></i> Add Personnel
								</button>

								<a id="imprt" class="btn btn-small btn-success" title="Import Group Personnel" href="attendance_report_add_personnel_import.php?projid=<?php echo functions::encode($proj_id);?>&eatid=<?php echo functions::encode($eatid)?>" data-rel="tooltip">
									<i class="icon-download-alt icon-white"></i> Import Personnel
								</a>

								<?php if( $count_personnel ){ ?>
								<a id="lnkUpld" href="attendance_report_add_attlog_option.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info btn-small" title="Upload the excel file of attendance log" data-rel="tooltip">
									Upload Att. Log <i class="icon-chevron-right icon-white"></i>
								</a>
								<span id="UpldWrning" class="text-error" style="display:none; font-weight:600; font-size:12px; white-space:nowrap;">(Personnel Status Unidentified)</span>
								<?php }?>
							</div>

							<!-- Personnel List Table -->
							<table id="tblist" width="100%" class="table table-bordered table-hover table-earthy" style="font-size:12px;">
								<thead>
									<tr>
										<th width="4%" style="text-align: center;">#</th>
										<th width="12%" height="30" class="tdpadleft">ID Number</th>
										<th class="tdpadleft">Name</th>
										<th class="tdpadleft">Position</th>
										<th class="tdpadleft" width="25%">Status</th>
										<th width="6%" style="text-align: center;"><input type="checkbox" name="checkAll" id="checkAll" value="all"></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$count=1;
									$cancelUpload=0;
									$qDisp = $db->select('emp_attendance_personnel ead, employee e','*',array('eat_id'=>$eatid),'AND ead.emp_id=e.emp_id ORDER BY lname,fname');
									while($rDisp = $db->fetch_array($qDisp)):
									$rID = $rDisp['eap_id'];
									$workStat=work_status($rDisp['emp_id']);
									if($workStat=='-----')
										$cancelUpload++;
									?>
									<tr>
										<td style="text-align: center;"><?php echo $count++;?></td>
										<td height="30" class="tdpadleft"><?php echo $rDisp['emp_no']?></td>
										<td class="tdpadleft"><?php echo $rDisp['lname'].', '.$rDisp['fname']?></td>
										<td class="tdpadleft"><?php echo position($rDisp['emp_id'])?></td>
										<td class="tdpadleft"><?php echo $workStat?>&nbsp;&nbsp;<a id="adcworkstat<?php echo $rID;?>" href="#" class="thickbox" title="Manage Status" onclick="showThis(this.id,'employee_add_work_stat.php?eid=<?php echo functions::encode($rDisp['emp_id'])?>&fromED=t','Work Status')"><i class="halflings-icon pencil"></i></a></td>
										<td style="text-align: center;">
											<input type="checkbox" class="chkDel" name="chkDel[<?php echo $rID; ?>]" id="chkDel[<?php echo $rID; ?>]" value="<?php echo $rID; ?>">
											<a id="del<?php echo $rID;?>" style="display:none;" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Employee" data-rel="tooltip" href="?eatid=<?php echo functions::encode($eatid);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
										</td>
									</tr>
									<?php endwhile;?>
									<?php if($count == 1){ ?>
									<tr>
										<td colspan="6" style="text-align:center; color:#8C6D58; padding: 15px;">No personnel added yet. Select an employee above to assign to this report.</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>

							<?php if($count > 1){ ?>
							<div align="right" style="margin-top:15px;">
								<button type="button" class="btn btn-small btn-danger" id="btnTriggerRemove"><i class="icon-trash icon-white"></i> Remove Selected</button>
								<input type="submit" name="btnRemoved" id="btnRemoved" style="display:none;">
							</div>
							<?php } ?>
						</form>
					</div>
				</div>

				<!-- Right Vertical Process Wizard Navigation (Pinned to Right Edge) -->
				<div class="sidebar-wizard-area">
					<ul class="process-wizard-vertical">
						<div class="wizard-title">Process Steps</div>
						<li class="process-step complete">
							<a href="attendance_report_add_charge.php?eatid=<?php echo functions::encode($eatid); ?>">
								<span class="step-icon">1</span>
								<span class="step-label">Manage Details</span>
							</a>
						</li>
						<li class="process-step active">
							<a href="javascript:void(0)">
								<span class="step-icon">2</span>
								<span class="step-label">Manage Personnel</span>
							</a>
						</li>
						<li class="process-step <?php echo ($count_personnel) ? 'complete' : 'disabled'; ?>">
							<a href="<?php echo ($count_personnel) ? 'attendance_report_add_attlog_option.php?eatid='.functions::encode($eatid) : '#'; ?>">
								<span class="step-icon">3</span>
								<span class="step-label">Upload Att. Log</span>
							</a>
						</li>
						<li class="process-step <?php echo ($attendance_detail) ? 'complete' : 'disabled'; ?>">
							<a href="<?php echo ($attendance_detail) ? 'attendance_summary_view.php?eatid='.functions::encode($eatid) : '#'; ?>">
								<span class="step-icon">4</span>
								<span class="step-label">Summary</span>
							</a>
						</li>
					</ul>
				</div>

			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->

<!-- Removal Confirmation Modal -->
<div class="modal hide fade custom-modal" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="removeModalLabel"><i class="icon-warning-sign icon-white"></i> Confirm Removal</h3>
	</div>
	<div class="modal-body">
		<p style="margin: 0;">Are you sure you want to remove the selected personnel from this attendance report?</p>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button class="btn btn-danger" id="btnConfirmRemove"><i class="icon-trash icon-white"></i> Remove</button>
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
$(window).ready(function(){
	$('#UpldWrning').hide();
	$("#spinner").fadeOut("slow");
	<?php if($cancelUpload){ ?>
		$('#lnkUpld').hide();
		$('#UpldWrning').show();
	<?php } ?>

	// Batch removal modal confirmation trigger
	$('#btnTriggerRemove').click(function(e){
		e.preventDefault();
		if ($('.chkDel:checked').length === 0) {
			alert('Please select at least one employee to remove.');
			return;
		}
		$('#removeModal').modal('show');
	});

	$('#btnConfirmRemove').click(function(){
		$('#removeModal').modal('hide');
		$('#btnRemoved').click();
	});

	$(".chkDel").each(function() {
		if(this.checked==true){
			$(this).closest('tr').addClass('rActive');
		}
	});

	if ($('.chkDel:checked').length == $('.chkDel').length && $('.chkDel').length > 0){
		$('#checkAll').prop('checked',true);
	}
	else {
		$('#checkAll').prop('checked',false);
	}

	$("#checkAll").change(function() {
		if (this.checked) {
			$(".chkDel").each(function() {
				this.checked=true;
				$(this).closest('tr').addClass('rActive');
			});
		} else {
			$(".chkDel").each(function() {
				this.checked=false;
				$(this).closest('tr').removeClass('rActive');
			});
		}
	});

	$('#tblist tbody tr').click(function(event) {
		if (event.target.type !== 'checkbox' && !$(event.target).is('i') && !$(event.target).is('a')) {
			$(':checkbox', this).trigger('click');
		}
	});

	$(".chkDel").click(function () {
		$(this).closest('tr').toggleClass('rActive');
		if ($('.chkDel:checked').length == $('.chkDel').length){
			$('#checkAll').prop('checked',true);
		}
		else {
			$('#checkAll').prop('checked',false);
		}
	});
});

<?php $delMsg = ($attendance_detail) ? "Removing this personnel will also delete their attendance logs. Do you want to continue?" : "Do you want to remove this personnel?"?>
function delt(){
	if(confirm("<?php echo $delMsg?>")) return true; 
	else return false;
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
</body>
</html>