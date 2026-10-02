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

$absentView = (isset($_REQUEST['absntVw']) && !empty($_REQUEST['absntVw']) ) ? 1 : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$p_id = $rEatID['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $rEatID['confirmed'];
$attendance_ready = $rEatID['attendance_ready'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$emp_id = (isset($_REQUEST['empid']) && !empty($_REQUEST['empid']) ) ? functions::decode($_REQUEST['empid']) : 0;
$has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id));
$pay_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'pay_ready'=>1));
$totalRow = 0;
$totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;$regularDutyHours=0; $otDutyHours=0;
$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$emp_id));
$emp_name = $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_id'=>$emp_id));
$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
$countPos=0;$position='';
while($rPos = $db->fetch_array($qPos)):
	if($countPos)
		$position .= ' /<br>';
	$position .= $rPos['pos_name'];
	$countPos++;
endwhile;
$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$date_end.'" ORDER BY es_date DESC, es_id DESC LIMIT 1');
$rSal = $db->fetch_array($qSal);
$es_salary = $rSal['es_salary'];
$es_daily = $rSal['es_daily'];
$es_hourly = $rSal['es_hourly'];
$es_minute = $rSal['es_minute'];
$txSalMonth = functions::formatMoney($rSal['es_salary'],4);
$txSalDay = functions::formatMoney($rSal['es_daily'],4);
$txSalHour = functions::formatMoney($rSal['es_hourly'],4);
$txSalMin = functions::formatMoney($rSal['es_minute'],4);
$sal_type = ($rSal['es_type']) ? $rSal['es_type'] : "";

$alteredBgc='#fbf2e9';
$regularDutyHours=0; $otDutyHours=0;$totalAbsent=0; $hoursPerDay=0;$empRegAmount=0;$empOTAmount=0;$empAbsentAmount=0;$totalDayAbsent=0;$totalMinAbsent=0;

$arrDailyAttendance = array();
if( $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id))==0 ){//has no attendance assignment
	$hoursPerDay = 480;
	$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
	#echo $db->last_query;
	while($rA = $db->fetch_array($qEmpAtt)):
		$dailyName = functions::datearr($rA['eat_date']).'<br>('.$rA['eat_day'].')';
		$dutyDisplay = '<strong>('.functions::formatMoney($rA['duty_amount']).')</strong>';
		$empRegAmount+=$rA['duty_amount'];
		$arrDailyAttendance[] = array('eatd_id'=>0,'dailyName'=>$dailyName,'amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'','dutyHours'=>0,'otHours'=>0,'late'=>0,'undertime'=>0,'dutyDisplay'=>$dutyDisplay,'otDisplay'=>0,'absentDisplay'=>0,'bgColor'=>0,'absentDay'=>0,'absentMinutes'=>0,'lateUnderMins'=>0);
	endwhile;
}
else{
	$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
	#echo $db->last_query;
	while($rA = $db->fetch_array($qEmpAtt)):
		$amin='';$amout='';$pmin='';$pmout='';$isAbsentDay=0;$absentMin=0;
		$amDutyHrs=0;
		$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rA['emp_id']));
		$dailyName = functions::datearr($rA['eat_date']).'<br>('.$rA['eat_day'].')';
		
		$amin = ($rA['am_in']) ? functions::MilToTwelve($rA['am_in']) : '--:--';
		$amin .= ($rA['am_in_assign']) ? ' <br><small class="text-muted">('.functions::MilToTwelve($rA['am_in_assign']).')</small>' : ' <br><small class="text-muted">(--:--)</small>';

		$amout = ($rA['am_out']) ? functions::MilToTwelve($rA['am_out']) : '--:--';
		$amout .= ($rA['am_out_assign']) ? ' <br><small class="text-muted">('.functions::MilToTwelve($rA['am_out_assign']).')</small>' : ' <br><small class="text-muted">(--:--)</small>';
		$amDutyHrs = ($rA['am_in_assign'] && $rA['am_out_assign']) ? functions::min_diff($rA['am_in_assign'],$rA['eat_date'],$rA['am_out_assign'],$rA['eat_date']) : 0;

		$pmin = ($rA['pm_in']) ? functions::MilToTwelve($rA['pm_in']) : '--:--';
		$pmin .= ($rA['pm_in_assign']) ? ' <br><small class="text-muted">('.functions::MilToTwelve($rA['pm_in_assign']).')</small>' : ' <br><small class="text-muted">(--:--)</small>';

		$pmout = ($rA['pm_out']) ? functions::MilToTwelve($rA['pm_out']) : '--:--';
		$pmout .= ($rA['pm_out_assign']) ? ' <br><small class="text-muted">('.functions::MilToTwelve($rA['pm_out_assign']).')</small>' : ' <br><small class="text-muted">(--:--)</small>';

		$pmDutyHrs = ($rA['pm_in_assign'] && $rA['pm_out_assign']) ? functions::min_diff($rA['pm_in_assign'],$rA['eat_date'],$rA['pm_out_assign'],$rA['eat_date']) : 0;
		if($pay_confirm)
			$bgColor='';
		else
			$bgColor = ( $db->getValue('emp_attendance_adjustment','count(*)',array('eatd_id'=>$rA['eatd_id'])) ) ? 'class="row-altered"' : '';

		$regularDutyHours += $rA['duty_min'];
		$otDutyHours += $rA['ot_min'];
		$totalAbsent += $rA['late_min'] + $rA['under_min'];

		$dutyDisplay = ($rA['duty_min']) ? functions::min_to_hour($rA['duty_min']) : '';
		$otDisplay = ($rA['ot_min']) ? functions::min_to_hour($rA['ot_min']) : '';
		
		$absentDay=0;
		if($rA['am_absent']){
			$absentDay += $rA['am_absent'];
			$absentMin += $rA['am_absent_min'];
		}
		if($rA['pm_absent']){
			$absentDay += $rA['pm_absent'];
			$absentMin += $rA['pm_absent_min'];
		}

		$lateUnderMins = ( empty($rA['am_absent']) && empty($rA['pm_absent']) ) ? ($rA['late_min'] + $rA['under_min']) : ($rA['late_min'] + $rA['under_min']) - ($rA['am_absent_min']+$rA['pm_absent_min']);
		$absentDayDisplay = ($absentDay) ? $absentDay.' (day)' : '';
		$absentHrDisplay = ($absentMin) ? functions::min_to_hour($absentMin).' (hr)' : '';
		$absentDisplay = ($absentDayDisplay || $absentHrDisplay) ? $absentDayDisplay.' = '.$absentHrDisplay : '';

		$empRegAmount += $rA['duty_amount'];
		$empOTAmount += $rA['ot_amount'];
		$empAbsentAmount += $rA['absent_amount'];

		$otin = ($rA['ot_in']) ? functions::MilToTwelve($rA['ot_in']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
		$otout = ($rA['ot_out']) ? functions::MilToTwelve($rA['ot_out']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
		$arrDailyAttendance[] = array('eatd_id'=>$rA['eatd_id'],'dailyName'=>$dailyName,'amin'=>$amin,'amout'=>$amout,'pmin'=>$pmin,'pmout'=>$pmout,'otin'=>$otin,'otout'=>$otout,'dutyHours'=>$rA['duty_min'],'otHours'=>$rA['ot_min'],'late'=>$rA['late_min'],'undertime'=>$rA['under_min'],'dutyDisplay'=>$dutyDisplay,'otDisplay'=>$otDisplay,'absentDisplay'=>$absentDisplay,'bgColor'=>$bgColor,'absentDay'=>$absentDay,'absentMinutes'=>$absentMin,'lateUnderMins'=>$lateUnderMins);
	endwhile;
}

$hoursPerDay = 480;
$cert_id = $db->getValue('employee','cert_id',array('emp_id'=>$emp_id));
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
$file = (file_exists('../img_emp/'.$fileName) && $fileName) ? $fileName : 'blank-pic.png';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
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
		:root {
			--bg-canvas: #fcfaf8;
			--panel-bg: #ffffff;
			--border-subtle: #e7e0d8;
			--text-primary: #2c1d11;
			--text-muted: #78695c;
			
			/* Theme Colors */
			--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
			--theme-brown-primary: #7c401e;
			--theme-brown-hover: #5c2e14;
			--theme-brown-light: #f5ebe6;
			--theme-brown-accent: #b45309;
			--theme-brown-active-row: #f3e5dc;
			--theme-green-payout: #2e6b38;
			--theme-red-deduction: #b91c1c;
		}

		body {
			background-color: var(--bg-canvas);
			font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
			color: var(--text-primary);
			margin: 0;
			padding: 0;
		}

		.page-full-wrapper {
			width: 100% !important;
			max-width: 100% !important;
			padding: 0 !important;
			box-sizing: border-box;
		}

		.card-panel {
			width: 100%;
			background: var(--panel-bg);
			border-radius: 0;
			border: none;
			box-shadow: none;
			overflow: hidden;
			box-sizing: border-box;
		}

		.card-header-custom {
			background: var(--theme-brown-header);
			padding: 18px 25px;
			color: #ffffff;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.card-header-custom h2 {
			margin: 0;
			font-size: 18px;
			font-weight: 700;
			display: flex;
			align-items: center;
			gap: 10px;
			color: #ffffff;
		}

		.card-body-custom {
			padding: 25px;
			width: 100%;
			box-sizing: border-box;
		}

		.info-banner-grid {
			display: flex;
			gap: 20px;
			background: #f7f2ed;
			border-left: 4px solid var(--theme-brown-primary);
			padding: 14px 20px;
			border-radius: 8px;
			margin-bottom: 25px;
			font-size: 13px;
		}

		.info-banner-item {
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.info-banner-item span {
			color: var(--text-muted);
		}

		.info-banner-item strong {
			color: var(--text-primary);
			font-size: 14px;
		}

		.profile-card-grid {
			display: grid;
			grid-template-columns: 160px minmax(320px, 1fr) minmax(380px, 1fr);
			gap: 25px;
			background: #ffffff;
			border: 1px solid var(--border-subtle);
			border-radius: 12px;
			padding: 20px 25px;
			margin-bottom: 25px;
			align-items: center;
		}

		.avatar-box {
			width: 100%;
			height: 160px;
			border-radius: 10px;
			overflow: hidden;
			border: 1px solid var(--border-subtle);
			background: #fdfaf7;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.avatar-box img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.details-table {
			width: 100%;
			border-collapse: collapse;
			font-size: 13px;
		}

		.details-table td {
			padding: 7px 4px;
			vertical-align: top;
		}

		.details-table td.lbl {
			color: var(--text-muted);
			font-weight: 600;
			width: 110px;
			white-space: nowrap;
		}

		.salary-box {
			background: #fdfaf7;
			border-radius: 10px;
			padding: 16px 20px;
			border: 1px solid var(--border-subtle);
		}

		.salary-header {
			font-size: 12px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: var(--theme-brown-primary);
			margin-bottom: 12px;
			padding-bottom: 6px;
			border-bottom: 1px dashed var(--border-subtle);
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.salary-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 10px 20px;
		}

		.salary-item {
			display: flex;
			flex-direction: column;
		}

		.salary-item .lbl {
			font-size: 11px;
			color: var(--text-muted);
			font-weight: 600;
			margin-bottom: 2px;
		}

		.salary-item .val {
			font-size: 13px;
			font-weight: 700;
			color: var(--text-primary);
		}

		/* Total Deductions Summary Highlight */
		.deduction-summary-box {
			grid-column: span 2;
			margin-top: 8px;
			padding: 8px 12px;
			background: #fef2f2;
			border: 1px solid #fecaca;
			border-radius: 6px;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.deduction-summary-box .lbl {
			font-size: 12px;
			font-weight: 700;
			color: var(--theme-red-deduction);
		}

		.deduction-summary-box .val {
			font-size: 14px;
			font-weight: 800;
			color: var(--theme-red-deduction);
		}

		.legend-bar {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 12px;
			font-size: 12px;
			color: var(--text-muted);
		}

		.legend-box-altered {
			width: 18px;
			height: 18px;
			background-color: var(--theme-brown-light);
			border: 1px solid var(--border-subtle);
			border-radius: 4px;
		}

		.table-wrapper {
			width: 100%;
			border-radius: 12px;
			overflow: hidden;
			border: 1px solid var(--border-subtle);
			box-sizing: border-box;
		}

		.attendance-table {
			width: 100% !important;
			border-collapse: separate;
			border-spacing: 0;
			font-size: 12px;
			margin: 0;
		}

		.attendance-table thead tr.head-group {
			background: #eddcd0;
		}

		.attendance-table thead tr.head-group td {
			font-weight: 800;
			color: var(--theme-brown-primary);
			text-transform: uppercase;
			letter-spacing: 0.5px;
			font-size: 11px;
			padding: 8px 10px;
			border-bottom: 1px solid var(--border-subtle);
		}

		.attendance-table thead tr.head-sub {
			background: #f4ebe2;
		}

		.attendance-table thead tr.head-sub td {
			color: var(--text-muted);
			font-weight: 700;
			font-size: 11px;
			padding: 8px 10px;
			border-bottom: 1px solid var(--border-subtle);
		}

		.attendance-table tbody tr {
			transition: background 0.15s ease;
		}

		.attendance-table tbody tr:hover {
			background-color: #fcf6f0 !important;
		}

		.attendance-table tbody td {
			padding: 8px 10px;
			border-bottom: 1px solid #f2e9e1;
			vertical-align: middle;
		}

		.attendance-table tfoot tr {
			font-weight: 700;
			background: #f7f2ed;
		}

		.attendance-table tfoot td {
			padding: 12px 10px;
			border-top: 2px solid var(--border-subtle);
		}

		.row-altered {
			background-color: #fbf2e9 !important;
		}

		.payout-pill {
			display: inline-block;
			padding: 4px 12px;
			border-radius: 50px;
			background-color: var(--theme-green-payout);
			color: #ffffff;
			font-weight: 700;
			font-size: 12px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.08);
		}

		.payout-pill strong {
			color: #ffffff !important;
		}

		.payout-pill.deduction-pill {
			background-color: var(--theme-red-deduction);
		}

		.text-muted {
			color: var(--text-muted);
		}

		.btn-action-view {
			background: var(--theme-brown-light);
			color: var(--theme-brown-primary) !important;
			border-radius: 6px;
			padding: 4px 8px;
			border: 1px solid var(--border-subtle);
			display: inline-flex;
			align-items: center;
			justify-content: center;
			transition: all 0.2s;
		}

		.btn-action-view:hover {
			background: var(--theme-brown-primary);
			color: #ffffff !important;
		}

		.btn-action-view i {
			margin: 0 !important;
		}
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="page-full-wrapper">
	<div class="card-panel">
		<div class="card-header-custom">
			<h2><i class="halflings-icon white list"></i> Attendance Payroll Preview</h2>
		</div>
		<div class="card-body-custom">
			<form class="form-horizontal" method="post">
				
				<!-- Top Information Banner -->
				<div class="info-banner-grid">
					<div class="info-banner-item">
						<span>Project:</span>
						<strong><?php echo $proj_name;?></strong>
					</div>
					<div style="border-left: 1px solid var(--border-subtle); margin: 0 10px;"></div>
					<div class="info-banner-item">
						<span>Period Covered:</span>
						<strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong>
					</div>
				</div>

				<?php
				// Compute total absent and tardiness minutes & total deduction amount first
				$calcTotalAbsentMinutes = 0;
				$calcTotalLateUnderMins = 0;
				foreach($arrDailyAttendance as $dlyCalc) {
					$calcTotalAbsentMinutes += $dlyCalc['absentMinutes'];
					$calcTotalLateUnderMins += $dlyCalc['lateUnderMins'];
				}
				$grandTotalDeductionMins = $calcTotalAbsentMinutes + $calcTotalLateUnderMins;
				$grandTotalDeductionAmount = $grandTotalDeductionMins * $es_minute;
				?>

				<!-- Redesigned Employee Profile & Salary Overview Grid -->
				<div class="profile-card-grid">
					<div class="avatar-box">
						<img src="../img_emp/<?php echo $file;?>" alt="Employee Photo">
					</div>
					<div>
						<table class="details-table">
							<tr>
								<td class="lbl">ID No.</td>
								<td><strong><?php echo $emp_no;?></strong></td>
							</tr>
							<tr>
								<td class="lbl">Name</td>
								<td><strong><?php echo $emp_name;?></strong></td>
							</tr>
							<tr>
								<td class="lbl">Position</td>
								<td><?php echo position($emp_id); ?></td>
							</tr>
							<tr>
								<td class="lbl">Status</td>
								<td>
									<?php
									$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_end.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
									$rStat = $db->fetch_array($qStat);
									$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : 'Undefined Status';
									$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <small class="text-muted">(Project Based)</small>' : '';
									$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
									echo $work_status = $wrkStat.$projBased.$wrkDate;
									?>
								</td>
							</tr>
						</table>
					</div>
					<div class="salary-box">
						<div class="salary-header">
							<span>Salary Information</span>
							<span style="color: var(--text-primary); font-size: 11px;">
								Type: <strong><?php if($sal_type=='flexible')echo 'Monthly';elseif($sal_type=='fixed')echo 'Daily';else{echo 'Unidentified';}?></strong>
							</span>
						</div>
						<div class="salary-grid">
							<?php if($sal_type=='flexible'){ ?>
							<div class="salary-item">
								<span class="lbl">Monthly</span>
								<span class="val" title="<?php echo functions::formatMoney($rSal['es_salary'],'~'); ?>"><?php echo functions::formatMoney($rSal['es_salary']); ?></span>
							</div>
							<div class="salary-item">
								<span class="lbl">Semi-Monthly</span>
								<span class="val" title="<?php echo ($rSal['es_salary']) ? functions::formatMoney($rSal['es_salary']/2,'~'): 0;?>"><?php echo ($rSal['es_salary']) ? functions::formatMoney($rSal['es_salary']/2,'~'): 0;?></span>
							</div>
							<?php } ?>
							<div class="salary-item">
								<span class="lbl">Daily Rate</span>
								<span class="val" title="<?php echo functions::formatMoney($rSal['es_daily'],'~'); ?>"><?php echo functions::formatMoney($rSal['es_daily'],'3'); ?></span>
							</div>
							<div class="salary-item">
								<span class="lbl">Hourly Rate</span>
								<span class="val" title="<?php echo functions::formatMoney($rSal['es_hourly'],'~'); ?>"><?php echo functions::formatMoney($rSal['es_hourly'],'3'); ?></span>
							</div>
							<div class="salary-item">
								<span class="lbl">Minute Rate</span>
								<span class="val" title="<?php echo functions::formatMoney($rSal['es_minute'],'~'); ?>"><?php echo functions::formatMoney($rSal['es_minute'],'3'); ?></span>
							</div>
						</div>
					</div>
				</div>

				<?php if( empty($sal_type) ){
					echo '<div align="center" style="color:#b91c1c; padding: 20px; background: #fef2f2; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 20px;">Warning: <strong style="font-size: 18px">Salary not set!</strong></div>';
				} else if($has_attendance==1){ ?>

				<div class="legend-bar">
					<div class="legend-box-altered"></div>
					<span>Altered/Adjusted Attendance Record</span>
				</div>

				<div class="table-wrapper">
					<table class="attendance-table table-hover">
						<thead>
							<tr class="head-group">
								<td width="14%">DATE</td>
								<td colspan="2" align="center">Morning</td>
								<td colspan="2" align="center">Afternoon</td>
								<td colspan="2" align="center">Overtime</td>
								<td colspan="4" align="center">Total Summary</td>
							</tr>
							<tr class="head-sub">
								<td>&nbsp;</td>
								<td width="9%" align="center">In <br><small>Actual (Assigned)</small></td>
								<td width="9%" align="center">Out <br><small>Actual (Assigned)</small></td>
								<td width="9%" align="center">In <br><small>Actual (Assigned)</small></td>
								<td width="9%" align="center">Out <br><small>Actual (Assigned)</small></td>
								<td width="7%" align="center">In</td>
								<td width="7%" align="center">Out</td>
								<td width="10%" align="center">Duty</td>
								<td width="9%" align="center">Overtime</td>
								<td width="10%" align="center">Absent</td>
								<td width="10%" align="center">Tardy / Under</td>
							</tr>
						</thead>
						<tbody>
							<?php
							if( $db->getValue('emp_attendance_detail','sum(duty_min)',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"')==0 ){
								if($sal_type=='flexible')
									$empAbsentAmount = ($es_salary / 2);
							}
							$totalLateUnderMins=$totalAbsent=0;$totalLate=0;$totalUnder=0;$countAtt=0;$totalAbsentDay=0;$totalAbsentMinutes=0;
							foreach($arrDailyAttendance as $dly):
								$countAtt++;
								$totalAbsent += ($dly['late'] + $dly['undertime']);
								$totalLate += $dly['late'];
								$totalUnder += $dly['undertime'];
								$rID = $dly['eatd_id'];
								$totalAbsentDay+=$dly['absentDay'];
								$totalAbsentMinutes+=$dly['absentMinutes'];
								$totalLateUnderMins+=$dly['lateUnderMins'];
							?>
							<tr id="rw<?php echo $rID;?>" <?php echo $dly['bgColor']?>>
								<td>
									<div style="display: flex; align-items: center; justify-content: space-between;">
										<?php echo $dly['dailyName'];?>
										<a id="vw2<?php echo $countAtt?>" class="btn-action-view" style="cursor: pointer;" title="Attendance Detail View" data-rel="tooltip" href="attendance_adjust_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($emp_id);?>&eatdid=<?php echo functions::encode($rID);?>&frm=<?php echo functions::encode('attendance_adjust_selected.php')?>">
											<i class="halflings-icon search white"></i>
										</a>
									</div>
								</td>
								<td align="center"><?php echo $dly['amin'];?></td>
								<td align="center"><?php echo $dly['amout'];?></td>
								<td align="center"><?php echo $dly['pmin'];?></td>
								<td align="center"><?php echo $dly['pmout'];?></td>
								<td align="center"><?php echo $dly['otin'];?></td>
								<td align="center"><?php echo $dly['otout'];?></td>
								<td align="center"><?php echo $dly['dutyDisplay'];?></td>
								<td align="center"><?php echo $dly['otDisplay'];?></td>
								<td align="center"><?php echo $dly['absentDisplay'];?></td>
								<td align="center"><?php echo ($dly['lateUnderMins']>0) ? functions::min_to_hour($dly['lateUnderMins']) : '';?></td>
							</tr>
							<?php endforeach;?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="7" align="right" style="font-weight:700;">Total Hours &nbsp;&nbsp;</td>
								<td align="center"><strong><?php echo ($regularDutyHours) ? functions::min_to_hour($regularDutyHours) : '';?></strong></td>
								<td align="center"><strong><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '';?></strong></td>
								<td align="center"><strong><?php echo ($totalAbsentMinutes) ? functions::min_to_hour($totalAbsentMinutes) : '';?></strong></td>
								<td align="center"><strong><?php echo ($totalLateUnderMins) ? functions::min_to_hour($totalLateUnderMins) : ''; ?></strong></td>
							</tr>
							<tr>
								<td colspan="7" align="right" style="font-weight:700;">Amount &nbsp;&nbsp;</td>
								<td align="center">
									<?php if( $sal_type=='fixed' && $regularDutyHours ){ ?>
										<div class="payout-pill" title="<?php echo functions::formatMoney($regularDutyHours*$es_minute,'~'); ?>">
											<strong><?php echo functions::formatMoney($regularDutyHours*$es_minute,'3'); ?></strong>
										</div>
									<?php } ?>
								</td>
								<td align="center">
									<?php if( $otDutyHours ){ ?>
										<div class="payout-pill" title="<?php echo functions::formatMoney($otDutyHours*$es_minute,'~'); ?>">
											<strong><?php echo functions::formatMoney($otDutyHours*$es_minute,'3'); ?></strong>
										</div>
									<?php } ?>
								</td>
								<td align="center" class="rwAbsent">
									<?php if( $totalAbsentMinutes ){ ?>
										<div class="payout-pill deduction-pill" title="<?php echo functions::formatMoney(($totalAbsentMinutes*$es_minute),'~'); ?>">
											<strong><?php echo functions::formatMoney(($totalAbsentMinutes*$es_minute),'3'); ?></strong>
										</div>
									<?php } ?>
								</td>
								<td align="center" class="rwAbsent">
									<?php if( $totalLateUnderMins ){ ?>
										<div class="payout-pill deduction-pill" title="<?php echo functions::formatMoney($totalLateUnderMins*$es_minute,'~'); ?>">
											<strong><?php echo functions::formatMoney($totalLateUnderMins*$es_minute,'3'); ?></strong>
										</div>
									<?php } ?>
								</td>
							</tr>
							<!-- Optional Summary Row across Deduction Columns -->
							<?php if ( $totalAbsentMinutes && $totalLateUnderMins ) { ?>
							<tr>
								<td colspan="9" align="right" style="font-weight: 700; color: var(--theme-red-deduction);">
									Total Combined Deductions (Absent + Tardy): &nbsp;&nbsp;
								</td>
								<td colspan="2" align="center">
									<div class="payout-pill deduction-pill" style="padding: 6px 16px; font-size: 13px;">
										<strong><?php echo functions::formatMoney($grandTotalDeductionAmount, '3'); ?></strong>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tfoot>
					</table>
				</div>

				<?php }else{?>
					<div align="center" style="padding: 30px; color: var(--text-muted); background: #fdfaf7; border-radius: 8px; border: 1px solid var(--border-subtle);">Attendance not applicable</div>
				<?php }?>
			</form>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>

<?php if( $absentView ){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('.rwAbsent').centerView();
	$('.rwAbsent').css('border','2px solid #dc2626');
});
</script>
<?php } ?>
<?php if(isset($_SESSION['notif_indi_idd'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_indi_idd'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_indi_idd'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_indi_idd'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	window.setTimeout(function(){$('#rw<?php echo $_SESSION['notif_indi_idd'] ?>').css('border','1px solid black');}, 5000);
});
</script>
<?php unset($_SESSION['notif_indi_idd']);} ?>
<!-- end: JavaScript-->
</body>
</html>