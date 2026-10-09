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

$statall = (isset($_REQUEST['statall']) && !empty($_REQUEST['statall']) ) ? $_REQUEST['statall'] : 0;
$emp_confirm = (isset($_REQUEST['cempid']) && !empty($_REQUEST['cempid']) ) ? functions::decode($_REQUEST['cempid']) : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
if($eatid)
	$_SESSION['notif_id']=$eatid;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$eat_id = $rEatID['eat_id'] ?? $eatid;
$p_id = $rEatID['proj_id'] ?? 0;
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $rEatID['confirmed'] ?? 0;
$attendance_ready = $rEatID['attendance_ready'] ?? 0;
$date_start = $rEatID['date_start'] ?? '';
$date_end = $rEatID['date_end'] ?? '';
$worker_type = $rEatID['payroll_type'] ?? '';
$eas_name = $rEatID['note'] ?? '';
$payroll_no = $rEatID['payroll_no'] ?? '';
$att_year = $rEatID['att_year'] ?? '';

$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));

$prepared_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['prepared_by'] ?? 0));
$prepared_position = position($rEatID['prepared_by'] ?? 0);

$checked_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['checked_by'] ?? 0));
$checked_position = position($rEatID['checked_by'] ?? 0);

$received_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['received_by'] ?? 0));
$received_position = position($rEatID['received_by'] ?? 0);

$approved_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['approved_by'] ?? 0));
$approved_position = position($rEatID['approved_by'] ?? 0);

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
if($statall && $eatid){
	if($statall==1){
		$db->update('emp_attendance_personnel',array('att_ready'=>1),array('eat_id'=>$eatid));
		$_SESSION['notif_success']='All Employees attendance verified!';
	}
	else if($statall==2){
		$db->update('emp_attendance_personnel',array('att_ready'=>0),array('eat_id'=>$eatid));
		$_SESSION['notif_warning']='All Employees attendance unverified!';
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}
if($emp_confirm && $eatid){
	if( $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$emp_confirm,'eat_id'=>$eatid,'att_ready'=>1)) ){
		$_SESSION['notif_warning']='Employee attendance unverified!';
		$db->update('emp_attendance_personnel',array('att_ready'=>0),array('emp_id'=>$emp_confirm,'eat_id'=>$eatid));
	}
	else{
		$_SESSION['notif_success']='Employee attendance confirmed!';
		$db->update('emp_attendance_personnel',array('att_ready'=>1),array('emp_id'=>$emp_confirm,'eat_id'=>$eatid));
	}
	$_SESSION['notif_indi_id']=$emp_confirm;
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}
if( isset($_REQUEST['c']) ){
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo 'Please wait while attendance is still processing........<br>';
	echo '<div id="spinner"></div>';
	if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$eatid,'confirmed'=>0)) ){
		if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$eatid,'attendance_ready'=>0)) ){
			$db->update('emp_attendance',array('attendance_ready'=>1),array('eat_id'=>$eatid));
			$_SESSION['notif_success']='Attendance Successfully confirmed!';
			$attendance_ready=1;
			functions::sendTo('attendance_report_calc.php?eatid='.functions::encode($eatid));
			die();
		}
		else{
			$_SESSION['notif_warning']='Attendance reverted to Unconfirmed!';
			$db->update('emp_attendance',array('attendance_ready'=>0),array('eat_id'=>$eatid));
		}
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}

$recalculate = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? $_REQUEST['pr'] : 0;
if($recalculate){
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo 'Please wait while attendance is still processing........<br>';
	echo '<div id="spinner"></div>';
	$_SESSION['notif_success']='Attendance Updated!';
	functions::sendTo('attendance_report_upload_save.php?eatid='.functions::encode($eatid));
	die();
}

$totalRow = 0;
$content = array();
$arrAttendanceRecord = array();
$arrPayrollList = array();
$arrMember = array();

$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','eas_id.emp_id,eas_id.att_ready',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id GROUP BY eas_id.emp_id,eas_id.att_ready ORDER BY lname,fname');
while( $rEmps = $db->fetch_array($qEmps)):
	$totalTardy=$dayAbsent=$totalDayPresent=$totalDayAbsent=$totalAbsent=$totalDayRequired=0;
	$arrMember[$rEmps['emp_id']]=$rEmps['att_ready'];
	$empRegDutyHours=0;$empOTDutyHours=0;$empUnderDutyHours=0;$empLateDutyHours=0;$empAbsentHours=0;
	$emp_id = $rEmps['emp_id'];
	$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$emp_id));
	$name = $db->getValue('employee','concat(lname,", ",fname,", ",left(mname,1),".")',array('emp_id'=>$emp_id));

	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	$countPos=0;$position='';
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	$hasLeave=$hasHoliday=0;
	$has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id));
	if( $has_attendance == 0 ){
		$dates = functions::date_diff($date_start,$date_end);
		if($dates){
			for($i=0;$i<=$dates;$i++):
				$succeeding_date = functions::AddDay($date_start,$i);
				$daysName = date('l', strtotime($succeeding_date));
				if($daysName == 'Saturday')
					$empRegDutyHours += 240; //4hr
				else if($daysName=='Sunday')
					;
				else
					$empRegDutyHours += 480; // 8hr
			endfor;
		}
	}
	else{
		$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
		while($rA = $db->fetch_array($qEmpAtt)):   
			$amDutyHrs = ($rA['am_in_assign'] && $rA['am_out_assign']) ? functions::min_diff($rA['am_in_assign'],$rA['eat_date'],$rA['am_out_assign'],$rA['eat_date']) : 0;
			$pmDutyHrs = ($rA['pm_in_assign'] && $rA['pm_out_assign']) ? functions::min_diff($rA['pm_in_assign'],$rA['eat_date'],$rA['pm_out_assign'],$rA['eat_date']) : 0;
			$empRegDutyHours += $rA['duty_min'];
			$empOTDutyHours += $rA['ot_min'];
			$empUnderDutyHours += $rA['under_min'];
			$empLateDutyHours += $rA['late_min'];

			$totalAbsent += $rA['late_min'] + $rA['under_min'];
			$totalDayAbsent += $dayAbsent = ($rA['am_absent'] + $rA['pm_absent']); 
			$dayPresent=0;
			if( !empty($rA['am_in_assign']) && !empty($rA['am_out_assign']) ){
				$totalDayRequired+=.5;
				if( empty($rA['am_absent']) )
					$dayPresent+=.5;
			}
			if( !empty($rA['pm_in_assign']) && !empty($rA['pm_out_assign']) ){
				$totalDayRequired+=.5;
				if( empty($rA['pm_absent']) )
					$dayPresent+=.5;
			}
			$totalDayPresent+=$dayPresent;

			$totalTardy += $tardy = $rA['late_min']+$rA['under_min'];
			if($rA['am_absent'])
				$totalTardy -= $rA['am_absent_min'];
			if($rA['pm_absent'])
				$totalTardy -= $rA['pm_absent_min'];
			if($rA['is_holiday']==2)
				$hasLeave=1;
			if($rA['is_holiday']==1)
				$hasHoliday=1;
			if($rA['is_holiday']==3){
				$hasLeave=$hasHoliday=1;
			}
		endwhile;
	}
	$bgColor = ($rEmps['att_ready']==1) ? '' : 'bgcolor="#fcf8e3"';
	$empAbsentHours = $empUnderDutyHours + $empLateDutyHours;
	$arrPayrollList[$emp_id] = array('emp_id'=>$emp_id,'emp_no'=>$emp_no,'name'=>$name,'position'=>$position,'regular_hours'=>$empRegDutyHours,'overtime_hours'=>$empOTDutyHours,'undertime_hours'=>$empUnderDutyHours,'late_hours'=>$empLateDutyHours,'totalTardy'=>$totalTardy,'absentHours'=>$empAbsentHours,'totalDayPresent'=>$totalDayPresent,'totalDayAbsent'=>$totalDayAbsent,'totalDayRequired'=>$totalDayRequired,'bgColor'=>$bgColor,'hasLeave'=>$hasLeave,'hasHoliday'=>$hasHoliday);
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Employee Attendance Summary</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style-loader" href="../css/loader.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
	
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<link rel="shortcut icon" href="../img/favicon.png">

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
		
		/* Expanded Form & Summary Content Area */
		.main-content-area {
			flex: 1 !important;
			<?php if($attendance_ready==0){echo "max-width: 80% !important;";} ?>
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

		/* Active Step Styling */
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
			0% { box-shadow: 0 0 0 0px rgba(140, 109, 88, 0.4), 0 4px 10px rgba(74, 59, 50, 0.3); }
			70% { box-shadow: 0 0 0 7px rgba(140, 109, 88, 0), 0 4px 10px rgba(74, 59, 50, 0.3); }
			100% { box-shadow: 0 0 0 0px rgba(140, 109, 88, 0), 0 4px 10px rgba(74, 59, 50, 0.3); }
		}

		.process-step.active .step-label {
			color: #4A3B32 !important;
			font-weight: 800 !important;
			font-size: 13px !important;
		}

		.process-step.complete .step-icon {
			background-color: #FAF8F5 !important;
			color: #8C6D58 !important;
			border-color: #8C6D58 !important;
		}
		.process-step.complete .step-label {
			color: #8C6D58 !important;
		}

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

		/* Single Consolidated Summary Card */
		.single-summary-card {
			background: #FFFFFF !important;
			border: 1px solid #E6E1DA !important;
			border-left: 5px solid #8C6D58 !important;
			border-radius: 8px !important;
			padding: 22px 26px !important;
			margin-bottom: 20px !important;
			box-shadow: 0 2px 8px rgba(74, 59, 50, 0.04) !important;
		}

		.summary-grid-layout {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 20px 28px;
		}

		.summary-field {
			display: flex;
			flex-direction: column;
		}

		.summary-field.full-width {
			grid-column: 1 / -1;
		}

		.summary-label-text {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.7px;
			font-weight: 700;
			color: #8C6D58;
			margin-bottom: 6px;
		}

		.summary-value-text {
			font-size: 14px;
			font-weight: 600;
			color: #3A2F28;
			line-height: 1.5;
			word-break: break-word;
			overflow-wrap: break-word;
		}

		.summary-divider {
			grid-column: 1 / -1;
			border-bottom: 1px dashed #E6E1DA;
			margin: 2px 0;
		}

		/* Badges */
		.badge-earthy {
			display: inline-block;
			padding: 3px 8px;
			font-size: 11px;
			font-weight: 700;
			line-height: 1.2;
			color: #4A3B32;
			background-color: #EFECE6;
			border: 1px solid #D8D2C7;
			border-radius: 4px;
			text-transform: uppercase;
		}

		.badge-status-confirmed {
			background-color: #E2F0D9;
			color: #2E6B38;
			border: 1px solid #B8DCAB;
		}

		.badge-status-unconfirmed {
			background-color: #FFF2CC;
			color: #8A6D3B;
			border: 1px solid #FFE599;
		}

		/* Table Styling */
		.table-wrapper {
			background: #ffffff !important;
			border-radius: 6px !important;
			box-shadow: 0 2px 6px rgba(74, 59, 50, 0.05) !important;
			border: 1px solid #E6E1DA !important;
			overflow-x: auto !important;
			margin-top: 10px !important;
		}
		.table-wrapper table {
			margin-bottom: 0 !important;
			border-collapse: separate !important;
			border-spacing: 0 !important;
		}
		.table-wrapper th {
			background: #F4F0EA !important;
			color: #4A3B32 !important;
			font-weight: 700 !important;
			text-transform: uppercase !important;
			font-size: 11px !important;
			letter-spacing: 0.8px !important;
			position: sticky !important;
			top: 0 !important;
			z-index: 10 !important;
			border-bottom: 2px solid #E0DCD5 !important;
			border-top: none !important;
			padding: 12px 10px !important;
		}
		.table-wrapper td {
			padding: 10px !important;
			vertical-align: middle !important;
			color: #4A3B32 !important;
			border-top: 1px solid #EFECE6 !important;
		}
		.table-hover tbody tr:not([style*="background"]):hover > td {
			background-color: #F7F5F0 !important;
			color: #4A3B32 !important;
		}
		.table-wrapper td a {
			color: #8C6D58 !important;
			text-decoration: none !important;
		}

		/* Signature Card */
		.signature-card {
			background: #FAF8F5 !important;
			border: 1px solid #E6E1DA !important;
			border-radius: 6px !important;
			box-shadow: 0 2px 6px rgba(74, 59, 50, 0.03) !important;
			padding: 24px 20px !important;
			margin-top: 25px !important;
		}
		.signature-table td {
			vertical-align: top !important;
			padding: 0 15px !important;
		}
		.sig-role {
			font-size: 11px !important;
			text-transform: uppercase !important;
			letter-spacing: 0.8px !important;
			color: #8C6D58 !important;
			font-weight: 700 !important;
			margin-bottom: 45px !important;
		}
		.sig-container {
			display: inline-block !important;
			max-width: 200px !important;
			width: 100% !important;
		}
		.sig-line {
			border-top: 1px solid #8C6D58 !important;
			padding-top: 6px !important;
			font-size: 13px !important;
			color: #4A3B32 !important;
			margin-bottom: 4px !important;
			white-space: nowrap !important;
			overflow: hidden !important;
			text-overflow: ellipsis !important;
		}
		.sig-pos {
			font-size: 11px !important;
			color: #7A6B60 !important;
			font-style: italic !important;
		}

		.header { background: #CCC; }
		.sticky { position: fixed; top: 0; width: 97%; }
		.hideit { display: none; }

		@media (max-width: 992px) {
			.page-layout-grid {
				flex-direction: column-reverse !important;
				gap: 25px !important;
			}
			.main-content-area { max-width: 100% !important; }
			.sidebar-wizard-area {
				width: 100% !important;
				position: static !important;
				margin-left: 0 !important;
			}
			.process-wizard-vertical {
				flex-direction: row !important;
				justify-content: space-between !important;
			}
			.process-step { padding-bottom: 0 !important; flex: 1 !important; }
			.process-step:not(:last-child)::after { display: none !important; }
			.summary-grid-layout { grid-template-columns: 1fr; }
		}
	</style>
</head>
<body>
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="card-header-custom" style="display:none;">
			<h2>ATTENDANCE SUMMARY PREVIEW</h2>
		</div>
		<div class="box-content">
			<div class="page-layout-grid">

				<!-- Main Content Form Area -->
				<div class="main-content-area">
					<form class="form-horizontal" method="post">					
						<!-- Consolidated Summary Card (No Icons) -->
						<div class="single-summary-card">
							<div class="summary-grid-layout">							
								<!-- Project / Department (Full Width for Long Text) -->
								<div class="summary-field full-width">
									<?php if($attendance_ready==0){?>
									<div align="right">
										<a id="icnRefresh" href="?eatid=<?php echo functions::encode($eatid)?>&pr=t" class="btn btn-warning" title="Re calculate attendance" onClick="return askRecalc();"><i class="halflings-icon white refresh"></i></a>&nbsp;
									</div>
									<?php }?>
									<div class="summary-label-text">Project / Department</div>
									<div class="summary-value-text" style="font-size: 16px; font-weight: 700; color: #4A3B32;">
										<?php echo htmlspecialchars($proj_name); ?>
									</div>
								</div>
								<!-- Description (Full Width if present) -->
								<?php if($eas_name){ ?>
								<div class="summary-field full-width">
									<div class="summary-label-text">Description</div>
									<div class="summary-value-text">
										<?php echo htmlspecialchars($eas_name); echo ($worker_type) ? ' ('.htmlspecialchars($worker_type).')' : ''; ?>
									</div>
								</div>
								<?php } ?>
								<div class="summary-divider"></div>
								<!-- Period Covered -->
								<div class="summary-field">
									<div class="summary-label-text">Period Covered</div>
									<div class="summary-value-text">
										<?php echo functions::datearr($date_start).' &mdash; '.functions::datearr($date_end); ?>
									</div>
								</div>
								<!-- Attendance Type -->
								<div class="summary-field">
									<div class="summary-label-text">Attendance Type</div>
									<div class="summary-value-text" style="display: flex; align-items: center; gap: 8px;">
										<span class="badge-earthy"><?php echo strtoupper($worker_type); ?></span>
										<?php if($worker_type=='admin'){ echo '<span style="font-size: 12px; color: #7A6B60; font-style: italic;">(Office Personnel)</span>'; } ?>
									</div>
								</div>
								<!-- Payroll No. (If Confirmed/Ready) -->
								<?php if($attendance_ready){ ?>
								<div class="summary-field">
									<div class="summary-label-text">Payroll No.</div>
									<div class="summary-value-text">
										<?php echo htmlspecialchars($payroll_no); ?>
									</div>
								</div>
								<?php } ?>
								<!-- Confirmation Status -->
								<div class="summary-field <?php echo (!$attendance_ready) ? '' : ''; ?>">
									<div class="summary-label-text">Confirmation Status</div>
									<div class="summary-value-text">
										<div style="display: flex; align-items: center; gap: 10px;">
											<label class="checkbox inline" style="padding-left: 0; margin-bottom: 0; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
												<input type="checkbox" name="chkConf" id="chkConf" value="1" style="margin-top: 0;" onClick="stat(this.value)" <?php if($attendance_ready) echo 'checked'; ?> <?php if($confirmed){ echo 'disabled'; } elseif( $db->getValue('emp_attendance_personnel','count(*)',array('att_ready'=>0,'eat_id'=>$eatid)) ){ echo 'disabled'; } ?>>
												<span class="badge-earthy <?php echo ($attendance_ready) ? 'badge-status-confirmed' : 'badge-status-unconfirmed'; ?>">
													<?php echo ($attendance_ready) ? 'Confirmed' : 'Unconfirmed'; ?>
												</span>
											</label>
											<?php if($confirmed){ echo '<span style="font-size: 12px; color: #7A6B60; font-style: italic;">(Payroll Confirmed)</span>'; } ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="table-wrapper">
							<?php if(empty($confirmed)){ ?>
							<div align="left" style="padding: 10px 10px 0 10px;">
								<table cellspacing="4" cellpadding="6" border='0' align="left">
									<tr>
										<td width="25" height='30'><div style="background-color:#fcf8e3; width:20px; border:1px solid #E0DCD5;">&nbsp;</div></td>
										<td>Unverified</td>
									</tr>
								</table>
							</div>
							<?php } ?>
							<table id="tblist" width="100%" border="0" class="table table-hover" style="font-size:12px;">
								<thead>
									<tr>
										<th width="28%">NAME / POSITION</th>
										<?php if($worker_type=='labor'){ ?>
										<th width="10%"><div align="center">Required<br><i>(Days)</i></div></th>
										<th width="10%"><div align="center">Rendered<br><i>(Days)</i></div></th>
										<?php } ?>
										<th width="10%"><div align="center">Absent<br><i>(Days)</i></div></th>
										<th width="10%"><div align="center">Overtime<br><i>(hh:mm:ss)</i></div></th>
										<?php if($worker_type=='labor'){ ?>
										<th width="11%"><div align="center">Duty<br><i>(hh:mm:ss)</i></div></th>
										<?php } ?>
										<th width="11%"><div align="center">Tardy/Under<br><i>(hh:mm:ss)</i></div></th>
										<th width="11%"><div align="center"><?php if($attendance_ready==0){?><a href="#" onClick="statAll('1')">Verify All</a>&nbsp;|&nbsp;<a href="#" onClick="statAll('2')">Unverify All</a><?php }else{echo 'Verified';} ?></div></th>
									</tr>
								</thead>
								<tbody>
								<?php
								$countEmp=0;$totalDutyHours=0;
								foreach($arrMember as $memberID => $statReady):
									$countEmp++;

									$pd = isset($arrPayrollList[$memberID]) ? $arrPayrollList[$memberID] : 0;
									$totalDutyHours = ($pd['overtime_hours'] + $pd['regular_hours']);
									if($pd){
										$hrsPrDay = 480;
										$qStat = $db->select('emp_work_status','*',array('emp_id'=>$memberID),'ORDER BY ews_date DESC LIMIT 1');
										$rStat = $db->fetch_array($qStat);
										$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : 'Undefined Status';
										$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? 'Project Based' : '';

										$has_travel = $db->getValue('travel_personnel tv, travel_order tro, travel_order_detail tod','count(*)',array('personnel_id'=>$pd['emp_id']),'AND travel_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" AND tro.to_id=tod.to_id AND tv.to_id=tro.to_id ORDER BY act_time_from');
										$has_travel = ($has_travel) ? '&nbsp;&nbsp;<i class="icon-truck icon-info" title="Has Travel Order" style="cursor:pointer;"></i>' : '';
										$has_leave = (isset($pd['hasLeave']) && $pd['hasLeave']==1 ) ? '&nbsp;&nbsp;<i class="icon-plane icon-info" title="Has Leave Application" style="cursor:pointer;"></i>' : '';
										$has_holiday = (isset($pd['hasHoliday']) && $pd['hasHoliday']==1 ) ? '&nbsp;&nbsp;<i class="icon-gift icon-info" title="Has Holiday" style="cursor:pointer;"></i>' : '';
								?>
									<tr id="rw<?php echo $memberID?>" <?php echo $pd['bgColor']?>>
										<td height="30px" style="vertical-align: top; padding-top: 10px; padding-bottom: 10px;">
											<div style="display: flex; align-items: center; justify-content: space-between;">
												<div style="display: flex; align-items: center; gap: 8px;">
													<a id="vw<?php echo $countEmp?>" class="thickbox" style="cursor: pointer; font-weight: 600; font-size: 13px; color: #4A3B32;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>&rw=<?php echo ($countEmp-1) ?>','Personnel Attendance Detail'<?php echo ($attendance_ready) ? ",'1'" : ''; ?>)"><?php echo $countEmp.'. '.$pd['name'];?></a>
													<a id="vw2<?php echo $countEmp?>" class="thickbox" style="cursor: pointer; color: #8C6D58; text-decoration:none;" title="View Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>&rw=<?php echo ($countEmp-1) ?>','Personnel Attendance Detail'<?php echo ($attendance_ready) ? ",'1'" : ''; ?>)"><i class="icon-edit" style="font-size: 14px;"></i></a>
												</div>
												<div><?php echo $has_travel.' '.$has_leave.' '.$has_holiday;?></div>
											</div>
											<div style="padding-left: 15px; margin-top: 3px; font-size: 11.5px; color: #7A6B60; border-left: 2px solid #C8C2B9;">
												<div style="font-weight: 500; color: #594C42;"><?php echo $pd['position'];?></div>
												<div style="margin-top: 1px;">
													<span class="label" style="background-color: #E6E1DA; color: #4A3B32; font-size: 10px; padding: 1px 6px; font-weight: normal;"><?php echo $wrkStat; ?></span>
													<?php if($projBased){ ?>
														<span class="label" style="background-color: #8C6D58; color: #FFFFFF; font-size: 10px; padding: 1px 6px; font-weight: normal; margin-left: 4px;"><?php echo $projBased; ?></span>
													<?php } ?>
												</div>
											</div>
										</td>
										<?php if($worker_type=='labor'){ ?>
										<td><div align="center"><?php echo $pd['totalDayRequired'];?></div></td>
										<td><div align="center"><?php echo $pd['totalDayPresent'];?></div></td>
										<?php } ?>
										<td><div align="center"><?php echo ($pd['totalDayAbsent'] > 1) ? $pd['totalDayAbsent'] : '';?></div></td>
										<td><div align="center"><?php echo ($pd['overtime_hours']) ? functions::min_to_hour($pd['overtime_hours']) : '';?></div></td>
										<?php if($worker_type=='labor'){ ?>
										<td><div align="center"><?php echo ($totalDutyHours) ? functions::min_to_hour($totalDutyHours) : '';?></div></td>
										<?php } ?>
										<td><div align="center"><?php echo ($pd['totalTardy']) ? functions::min_to_hour($pd['totalTardy']) : '';?></div></td>
										<td><div align="center"><input type="checkbox" class="chkDel" name="chkDel[<?php echo $memberID; ?>]" id="chkDel[<?php echo $memberID; ?>]" value="<?php echo functions::encode($memberID); ?>" <?php if($attendance_ready){echo 'disabled';}?> onClick="statIndi(this.value)" <?php echo ($statReady) ? 'checked':''; ?>></div></td>
									</tr>
								<?php }else{?>
									<tr>
										<td height="30px" style="vertical-align: top;"><?php echo $countEmp.'. '.$db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$memberID));?></td>
										<td><div align="right">-------</div></td>
										<td><div align="right">-------</div></td>
										<?php if($worker_type=='labor'){ ?>
										<td><div align="right">-------</div></td>
										<td><div align="right">-------</div></td>
										<td><div align="right">-------</div></td>
										<?php } ?>
										<td><div align="right">-------</div></td>
										<td><div align="right">-------</div></td>
									</tr>
								<?php }
								endforeach;?>
								</tbody>
							</table>
						</div>
						<div class="signature-card">
							<?php if($attendance_ready==0){?>
							<div align="right" style="padding-bottom:10px;">
								<a id="icnSignatory" class="btn btn-info" title="Manage Signatory" data-rel="tooltip" href="attendance_report_signatory.php?eatid=<?php echo functions::encode($eatid);?>"><i class="halflings-icon white edit"></i></a>&nbsp;
							</div>
							<?php }?>
							<table border="0" width="100%" class="signature-table">
								<tr>
									<td align="center" width="25%">
										<div class="sig-role">Prepared By</div>
										<div class="sig-container">
											<div class="sig-line"><strong><?php echo $prepared_by?></strong></div>
											<div class="sig-pos"><?php echo $prepared_position?></div>
										</div>
									</td>
									<td align="center" width="25%">
										<div class="sig-role">Checked By</div>
										<div class="sig-container">
											<div class="sig-line"><strong><?php echo $checked_by?></strong></div>
											<div class="sig-pos"><?php echo $checked_position?></div>
										</div>
									</td>
									<td align="center" width="25%">
										<div class="sig-role">Received By</div>
										<div class="sig-container">
											<div class="sig-line"><strong><?php echo $received_by?></strong></div>
											<div class="sig-pos"><?php echo $received_position?></div>
										</div>
									</td>
									<td align="center" width="25%">
										<div class="sig-role">Approved By</div>
										<div class="sig-container">
											<div class="sig-line"><strong><?php echo $approved_by?></strong></div>
											<div class="sig-pos"><?php echo $approved_position?></div>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</form>
				</div>
				<?php if($attendance_ready==0){ ?>
				<!-- Right Vertical Process Wizard Sidebar -->
				<div class="sidebar-wizard-area">
					<ul class="process-wizard-vertical">
						<div class="wizard-title">Process Steps</div>
						<li class="process-step complete">
							<a href="<?php echo ($eatid) ? 'attendance_report_add_charge.php?eatid='.functions::encode($eatid).'&frm=1' : '#'; ?>">
								<span class="step-icon">1</span>
								<span class="step-label">Manage Details</span>
							</a>
						</li>
						<li class="process-step complete">
							<a href="<?php echo ($eatid) ? 'attendance_report_add_personnel.php?eatid='.functions::encode($eatid) : '#'; ?>">
								<span class="step-icon">2</span>
								<span class="step-label">Manage Personnel</span>
							</a>
						</li>
						<li class="process-step complete">
							<a href="<?php echo ($eatid) ? 'attendance_report_add_attlog_option.php?eatid='.functions::encode($eatid) : '#'; ?>">
								<span class="step-icon">3</span>
								<span class="step-label">Upload Att. Log</span>
							</a>
						</li>
						<li class="process-step active">
							<a href="javascript:void(0)">
								<span class="step-icon">4</span>
								<span class="step-label">Summary</span>
							</a>
						</li>
					</ul>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<!-- Custom Earthy Modal Confirmation Box -->
<div id="confirmModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true" style="border-radius: 8px; overflow: hidden;">
	<div class="modal-header" style="background-color: #4A3B32; color: #F7F5F0; padding: 12px 20px; border-bottom: 3px solid #8C6D58;">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #F7F5F0; opacity: 0.8;">×</button>
		<h3 id="confirmModalLabel" style="font-size: 16px; font-weight: 600; margin: 0; color: #F7F5F0;">Confirmation</h3>
	</div>
	<div class="modal-body" style="padding: 20px; background-color: #FAF8F5; color: #4A3B32; font-size: 14px;">
		<p id="confirmModalMessage" style="margin: 0; font-weight: 500;">Are you sure you want to proceed?</p>
	</div>
	<div class="modal-footer" style="background-color: #EFECE6; border-top: 1px solid #E6E1DA; padding: 12px 20px;">
		<button class="btn" data-dismiss="modal" aria-hidden="true" style="border-radius: 4px;">Cancel</button>
		<button id="btnConfirmSubmit" class="btn btn-primary" style="background-color: #8C6D58; border-color: #7A5F4C; border-radius: 4px;">Confirm</button>
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
<?php if($countEmp==0){functions::sendTo('attendance_report_add_personnel.php?eatid='.functions::encode($eatid));} ?>
<script>
// Helper function to invoke modern Bootstrap Modal instead of native browser confirm()
function showConfirmModal(message, title) {
	return new Promise(function(resolve) {
		$('#confirmModalLabel').text(title || 'Confirmation');
		$('#confirmModalMessage').html(message);
		
		// Remove previous click handlers before binding a new one
		$('#btnConfirmSubmit').off('click').on('click', function() {
			$('#confirmModal').modal('hide');
			resolve(true);
		});

		$('#confirmModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
			resolve(false);
		});

		$('#confirmModal').modal('show');
	});
}

function askRecalc() {
	showConfirmModal('Do you want to recalculate the attendance record?', 'Recalculate Attendance').then(function(confirmed) {
		if (confirmed) {
			window.location = "?eatid=<?php echo functions::encode($eatid)?>&pr=t";
		}
	});
	return false;
}

function stat(v) {
	<?php if($attendance_ready==0){?>
		showConfirmModal('Do you want to confirm this attendance?', 'Confirm Attendance').then(function(confirmed) {
			if (confirmed) {
				document.getElementById("spinner").style.display = "block";
				window.location = "<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&c=" + v;	
			} else {
				document.getElementById("chkConf").checked = false;
			}
		});
	<?php }else{?>
		showConfirmModal('Do you want to unconfirm this attendance?', 'Unconfirm Attendance').then(function(confirmed) {
			if (confirmed) {
				document.getElementById("spinner").style.display = "block";
				window.location = "<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&c=" + v;	
			} else {
				document.getElementById("chkConf").checked = true;
			}
		});
	<?php } ?>
}

function statIndi(v) {
	window.location = "<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&cempid=" + v;
}

function statAll(v) {
	var msg = (v == 1) ? 'Do you want to verify all attendance?' : 'Do you want to unverify all attendance?';
	var title = (v == 1) ? 'Verify All Attendance' : 'Unverify All Attendance';

	showConfirmModal(msg, title).then(function(confirmed) {
		if (confirmed) {
			window.location = "<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&statall=" + v;
		}
	});
	return false;
}

window.onscroll = function() {myFunction()};
window.onload = function(){
	var header = document.getElementById("myHeader");
	if(header){ header.classList.add("hideit"); }
};

var header = document.getElementById("myHeader");
var sticky = 270;

function myFunction() {
	if(header){
		if (window.pageYOffset > sticky) {
			header.classList.add("sticky");
			header.classList.remove("hideit");
		}else{
			header.classList.remove("sticky");
			header.classList.add("hideit");
		}
	}
}
</script>
<script>
var sp = document.getElementById("spinner");
if(sp){ sp.style.display = "none"; }
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
<?php if(isset($_SESSION['notif_indi_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_indi_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_indi_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_indi_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_indi_id'] ?>").animate({borderColor:""}, 4000);
});
</script>
<?php unset($_SESSION['notif_indi_id']);} ?>
</body>
</html>