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

$trig = (isset($_REQUEST['trig']) && !empty($_REQUEST['trig']) ) ? 1 : 0;//For confirm and unconfirmed of individual attendance
$rowStart = (isset($_REQUEST['rw']) && !empty($_REQUEST['rw']) ) ? $_REQUEST['rw'] : 0; 
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
if($attendance_ready==0)
	$_SESSION['notif_indi_id']=$emp_id;
$has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id));
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
$att_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'att_ready'=>1));
$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_end.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
$rStat = $db->fetch_array($qStat);
$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : 'Undefined Status';
$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <span class="label label-info">Project Based</span>' : '';
$work_status = $wrkStat.$projBased;
#$work_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_end.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$date_end.'" ORDER BY es_date DESC, es_id DESC');
if( $db->num_rows($qSal) > 0 ){
	$rSal = $db->fetch_array($qSal);
	$txSalMonth = functions::formatMoney($rSal['es_salary'],4);
	$txSalDay = functions::formatMoney($rSal['es_daily'],4);
	$txSalHour = functions::formatMoney($rSal['es_hourly'],4);
	$txSalMin = functions::formatMoney($rSal['es_minute'],4);
}
if($trig && $eatid && $emp_id){
	if( $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'att_ready'=>1)) ){
		$_SESSION['notif_warning']='Employee attendance unverified!';
		$db->update('emp_attendance_personnel',array('att_ready'=>0),array('emp_id'=>$emp_id,'eat_id'=>$eatid));
	}
	else{
		$_SESSION['notif_success']='Employee attendance verified!';
		$db->update('emp_attendance_personnel',array('att_ready'=>1),array('emp_id'=>$emp_id,'eat_id'=>$eatid));
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid).'&empid='.functions::encode($emp_id).'&rw='.$rowStart);
	die();
}
$regularDutyHours=0; $otDutyHours=0;$totalAbsent=0; $hoursPerDay=0;

$arrDailyAttendance = array();

if( $has_attendance==0 ){
	$hoursPerDay = 480;
	$dates = functions::date_diff($date_start,$date_end);
	if($dates){
		for($i=0;$i<=$dates;$i++):
			$dutyHours=0;
			$succeeding_date = functions::AddDay($date_start,$i);
			$daysName = date('l', strtotime($succeeding_date));
			if($daysName == 'Saturday')
				$dutyHours = 240; //4hr
			else if($daysName=='Sunday')
				;
			else
				$dutyHours = 480; // 8hr
			$regularDutyHours += $dutyHours;
		endfor;
	}
}
else{
	$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
	#echo $db->last_query;
	$arr_emptravel=array();
	$qTrav = $db->select('travel_personnel tv, travel_order tro, travel_order_detail tod','*',array('personnel_id'=>$emp_id),'AND travel_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" AND tro.to_id=tod.to_id AND tv.to_id=tro.to_id ORDER BY act_time_from');
	while($rTrav = $db->fetch_array($qTrav)):
		$arr_emptravel[$rTrav['travel_date']]=1;
	endwhile;
	while($rA = $db->fetch_array($qEmpAtt)):
		$amin='';$amout='';$pmin='';$pmout='';
		$amDutyHrs=0;
		$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rA['emp_id']));
		$has_travel = isset($arr_emptravel[$rA['eat_date']]) ? '&nbsp;&nbsp;<span class="label label-warning" title="has travel"><i class="icon-truck icon-white"></i> Travel</span>' : '';
		$dailyName = functions::datearr($rA['eat_date']).'<br><span class="muted">('.$rA['eat_day'].')</span>'.$has_travel;
		$amin = ($rA['am_in']) ? functions::MilToTwelve($rA['am_in']) : '<span class="text-error">--:--</span>';
		$amin .= ($rA['am_in_assign']) ? ' <br><small class="muted">('.functions::MilToTwelve($rA['am_in_assign']).')</small>' : ' <br><small class="muted">(--:--)</small>';

		$amout = ($rA['am_out']) ? functions::MilToTwelve($rA['am_out']) : '<span class="text-error">--:--</span>';
		$amout .= ($rA['am_out_assign']) ? ' <br><small class="muted">('.functions::MilToTwelve($rA['am_out_assign']).')</small>' : ' <br><small class="muted">(--:--)</small>';
		$amDutyHrs = ($rA['am_in_assign'] && $rA['am_out_assign']) ? functions::min_diff($rA['am_in_assign'],$rA['eat_date'],$rA['am_out_assign'],$rA['eat_date']) : 0;

		$pmin = ($rA['pm_in']) ? functions::MilToTwelve($rA['pm_in']) : '<span class="text-error">--:--</span>';
		$pmin .= ($rA['pm_in_assign']) ? ' <br><small class="muted">('.functions::MilToTwelve($rA['pm_in_assign']).')</small>' : ' <br><small class="muted">(--:--)</small>';

		$pmout = ($rA['pm_out']) ? functions::MilToTwelve($rA['pm_out']) : '<span class="text-error">--:--</span>';
		$pmout .= ($rA['pm_out_assign']) ? ' <br><small class="muted">('.functions::MilToTwelve($rA['pm_out_assign']).')</small>' : ' <br><small class="muted">(--:--)</small>';

		$pmDutyHrs = ($rA['pm_in_assign'] && $rA['pm_out_assign']) ? functions::min_diff($rA['pm_in_assign'],$rA['eat_date'],$rA['pm_out_assign'],$rA['eat_date']) : 0;
		if($att_confirm)
			$bgColor='';
		else
			$bgColor = ( $db->getValue('emp_attendance_adjustment','count(*)',array('eatd_id'=>$rA['eatd_id'])) ) ? 'style="background-color: #fcf8e3 !important;"' : '';
		$hoursPerDay = 480;
		$regularDutyHours += $rA['duty_min'];
		$otDutyHours += $rA['ot_min'];
		$totalAbsent += $rA['late_min'] + $rA['under_min'];

		$otin = ($rA['ot_in']) ? functions::MilToTwelve($rA['ot_in']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
		$otout = ($rA['ot_out']) ? functions::MilToTwelve($rA['ot_out']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
		$arrDailyAttendance[] = array('eatd_id'=>$rA['eatd_id'],'dailyName'=>$dailyName,'amin'=>$amin,'amout'=>$amout,'pmin'=>$pmin,'pmout'=>$pmout,'otin'=>$otin,'otout'=>$otout,'dutyHours'=>$rA['duty_min'],'otHours'=>$rA['ot_min'],'late'=>$rA['late_min'],'undertime'=>$rA['under_min'],'bgColor'=>$bgColor);
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Preview</title>
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
	<style>
		.attendance-card { background: #fff; padding: 20px; border-radius: 4px; }
		.panel-summary { background: #f9f9f9; border: 1px solid #e5e5e5; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
		.table th, .table td { vertical-align: middle !important; }
		.highlight-box { border-left: 4px solid #00ba8b; background: #f4f6f8; padding: 10px 15px; margin-bottom: 15px; }
	</style>
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white file"></i><span class="break"></span>ATTENDANCE PREVIEW</h2>
			<div class="box-icon">
				<a href="#" class="btn-minimize"><i class="halflings-icon white chevron-up"></i></a>
			</div>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<?php
				$prevEmp = ( ($rowStart-1) >= 0 ) ? $db->getValue('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname LIMIT '.($rowStart-1).', 1') : '';
				$nextEmp = $db->getValue('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname LIMIT '.($rowStart+1).', 1');
				?>
				
				<!-- Navigation & Verification Header Bar -->
				<div class="row-fluid" style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
					<div class="span6">
						<?php if($prevEmp){ ?>
							<a class="btn btn-primary" title="Previous Employee" data-rel="tooltip" href="attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($prevEmp);?>&rw=<?php echo ($rowStart-1) ?>"><i class="icon-chevron-left icon-white"></i> Previous Employee</a>
						<?php } ?>
						<?php if($nextEmp){ ?>
							<a class="btn btn-primary" title="Next Employee" data-rel="tooltip" href="attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($nextEmp);?>&rw=<?php echo ($rowStart+1) ?>">Next Employee <i class="icon-chevron-right icon-white"></i></a>
						<?php } ?>
					</div>
					<div class="span6 text-right" style="padding-top: 5px;">
						<label class="checkbox inline" style="font-weight: bold; font-size: 13px;">
							<input type="checkbox" name="chkConf" id="chkConf" value="1" onClick="statIndi()" <?php if($attendance_ready){echo 'disabled';}?> <?php if($att_confirm){echo 'checked';} ?>> 
							<span class="label <?php echo ($att_confirm) ? 'label-success' : 'label-important'; ?>" style="padding: 5px 10px; font-size: 12px;"><?php echo ($att_confirm) ? '<i class="icon-ok icon-white"></i> Verified' : '<i class="icon-warning-sign icon-white"></i> Unverified';?></span>
						</label>
					</div>
				</div>

				<!-- Project & Period Info block -->
				<div class="well well-small" style="background-color: #fbfbfb;">
					<div class="row-fluid">
						<div class="span6">
							<strong>Project:</strong> <span class="text-info"><?php echo $proj_name;?></span>
						</div>
						<div class="span6 text-right">
							<strong>Period Covered:</strong> <?php echo functions::datearr($date_start).' &mdash; '.functions::datearr($date_end);?>
						</div>
					</div>
				</div>

				<?php if($txSalMonth==0){?>
					<div class="alert alert-error" style="text-align: center;">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>Warning:</strong> Employee's Salary is not set!
					</div>
				<?php }?>

				<!-- Employee Details & Summary Grid Layout -->
				<div class="row-fluid">
					<div class="span4">
						<div class="panel-summary" style="height: 140px;">
							<h5 style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 5px;"><i class="icon-user"></i> Employee Profile</h5>
							<table border="0" width="100%" style="font-size: 12px; line-height: 1.8;">
								<tr>
									<td width="30%" class="muted">ID:</td>
									<td><strong><?php echo $emp_no;?></strong></td>
								</tr>
								<tr>
									<td class="muted">Name:</td>
									<td><strong><?php echo $emp_name;?></strong></td>
								</tr>
								<tr>
									<td class="muted" valign="top">Position:</td>
									<td><strong><?php echo $position;?></strong></td>
								</tr>
								<tr>
									<td class="muted">Status:</td>
									<td><strong><?php echo ($work_status) ? $work_status : '--undefined--';?></strong></td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="span8">
						<div class="panel-summary" style="height: 140px;">
							<h5 style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 5px;"><i class="icon-time"></i> Attendance Summary Totals</h5>
							<table width="100%" border="0" class="table table-bordered table-condensed" style="font-size: 11px; background: #fff; margin-top: 10px;">
								<thead>
									<tr style="background-color:#f5f5f5">
										<th class="text-center">Rendered Days (Hours)</th>
										<th class="text-center">Absent Days (Hours)</th>
										<th class="text-center">Required Days (Hours)</th>
										<th class="text-center">Overtime Hours</th>
										<th class="text-center">Total Duty</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$amountRegularHour = ($regularDutyHours && $txSalMin) ? ($regularDutyHours * $txSalMin) : 0;
									$amountOvertimeHour = ($otDutyHours && $txSalMin) ? ($otDutyHours * $txSalMin) : 0;
									$totalDuty = ($regularDutyHours + $otDutyHours);
									?>
									<tr>
										<td class="text-center">
											<?php
											echo $regDays = ($regularDutyHours) ? number_format(($regularDutyHours/$hoursPerDay),2) : '0';
											echo ($regDays > 1) ? ' days' : ' day';
											echo ($regularDutyHours) ? '<br><small class="muted">('.functions::min_to_hour($regularDutyHours).')</small>' : '';
											?>
										</td>
										<td class="text-center">
											<?php
											echo $absentDays = ($hoursPerDay) ? number_format(($totalAbsent/$hoursPerDay),2) : '0';
											echo ($absentDays > 1) ? ' days' : ' day';
											echo ($totalAbsent) ? '<br><small class="muted">('.functions::min_to_hour($totalAbsent).')</small>' : '';
											?>
										</td>
										<td class="text-center">
											<?php
											echo $requiredDays = ( ($regularDutyHours || $totalAbsent) && $hoursPerDay) ? number_format(($regularDutyHours + $totalAbsent) / $hoursPerDay,2) : '0';
											echo ($requiredDays > 1) ? ' days' : ' day';
											echo ($regularDutyHours || $totalAbsent) ? '<br><small class="muted">('.functions::min_to_hour($regularDutyHours + $totalAbsent).')</small>' : '';
											?>
										</td>
										<td class="text-center"><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '0 hr';?></td>
										<td class="text-center">
											<strong>
												<?php
												echo $tDuty = ($totalDuty && $hoursPerDay) ? number_format(($totalDuty/$hoursPerDay),2) : '0';
												echo ($tDuty > 1) ? ' days' : ' day';
												echo ($totalDuty) ? '<br><small class="muted">('.functions::min_to_hour($totalDuty).')</small>' : '';
												?>
											</strong>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<br>
				<?php if($has_attendance){?>
				<div style="overflow-x: auto;">
					<table width="100%" align="center" border="1" class="table table-bordered table-striped table-hover" style="font-size: 12px;">
						<thead>
							<tr style="background-color:#f5f5f5">
								<td rowspan="2" style="padding-left:10px; font-weight: bold;">DATE</td>
								<td colspan="2" style="text-align: center; font-weight: bold;">Morning</td>
								<td colspan="2" style="text-align: center; font-weight: bold;">Afternoon</td>
								<td colspan="2" style="text-align: center; font-weight: bold;">Overtime</td>
								<td colspan="4" style="text-align: center; font-weight: bold;">Total</td>
							</tr>
							<tr style="background-color:#f9f9f9">
								<td width="11%" style="text-align: center !important;"><small>In<br><i>Actual (Assigned)</i></small></td>
								<td width="11%" style="text-align: center !important;"><small>Out<br><i>Actual (Assigned)</i></small></td>
								<td width="11%" style="text-align: center !important;"><small>In<br><i>Actual (Assigned)</i></small></td>
								<td width="11%" style="text-align: center !important;"><small>Out<br><i>Actual (Assigned)</i></small></td>
								<td width="7%" class="text-center"><small>In</small></td>
								<td width="7%" class="text-center"><small>Out</small></td>
								<td width="7%" class="text-center"><small>Duty</small></td>
								<td width="7%" class="text-center"><small>Overtime</small></td>
								<td width="7%" class="text-center"><small>Late</small></td>
								<td width="7%" class="text-center"><small>Under</small></td>
							</tr>
						</thead>
						<tbody>
						<?php
						$totalAbsent=0;$totalLate=0;$totalUnder=0;
						foreach($arrDailyAttendance as $dly):
							$totalAbsent += ($dly['late'] + $dly['undertime']);
							$totalLate += $dly['late'];
							$totalUnder += $dly['undertime'];
							$rID = $dly['eatd_id'];
						?>
							<tr <?php echo $dly['bgColor'];?>>
								<td height="5" style="padding-left:10px;">
									<div style="display: flex; justify-content: space-between; align-items: center;">
										<span><?php echo $dly['dailyName'];?></span>
										<a id="vw<?php echo $rID?>" class="thickbox btn btn-mini btn-info" style="cursor: pointer;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_adjust_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($emp_id);?>&eatdid=<?php echo functions::encode($rID);?>','Attendance Detail'<?php echo ($attendance_ready==1) ? ",'1'" : '' ?>)"><i class="icon-edit icon-white"></i> Edit</a>
									</div>
								</td>
								<td style="text-align: center !important;"><?php echo $dly['amin'];?></td>
								<td style="text-align: center !important;"><?php echo $dly['amout'];?></td>
								<td style="text-align: center !important;"><?php echo $dly['pmin'];?></td>
								<td style="text-align: center !important;"><?php echo $dly['pmout'];?></td>
								<td class="text-center"><?php echo $dly['otin'];?></td>
								<td class="text-center"><?php echo $dly['otout'];?></td>
								<td class="text-center"><?php echo ($dly['dutyHours']) ? functions::min_to_hour($dly['dutyHours']) : '';?></td>
								<td class="text-center"><?php echo ($dly['otHours']) ? functions::min_to_hour($dly['otHours']) : '';?></td>
								<td class="text-center"><?php echo ($dly['late']) ? '<span class="text-error">'.functions::min_to_hour($dly['late']).'</span>' : '';?></td>
								<td class="text-center"><?php echo ($dly['undertime']) ? '<span class="text-warning">'.functions::min_to_hour($dly['undertime']).'</span>' : '';?></td>
							</tr>
						<?php endforeach;?>
							<tr style="background-color: #f9f9f9; font-weight: bold;">
								<td colspan="7" class="text-right">TOTALS:</td>
								<td class="text-center"><?php echo ($regularDutyHours) ? functions::min_to_hour($regularDutyHours) : '';?></td>
								<td class="text-center"><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '';?></td>
								<td class="text-center text-error"><?php echo ($totalLate) ? functions::min_to_hour($totalLate) : '';?></td>
								<td class="text-center text-warning"><?php echo ($totalUnder) ? functions::min_to_hour($totalUnder) : '';?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php }else{?>
					<div class="alert alert-info text-center" style="padding: 20px; font-size: 14px;">
						Attendance not applicable for this employee.
					</div>
				<?php }?>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->
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
<script type="text/javascript">
function statIndi(){
	window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($emp_id);?>&rw=<?php echo $rowStart?>&trig=trig";
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