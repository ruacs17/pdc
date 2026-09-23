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
$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <i>(Project Based)</i>' : '';
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
		$has_travel = isset($arr_emptravel[$rA['eat_date']]) ? '&nbsp;&nbsp;<i class="icon-truck" title="has travel"></i>' : '';
		$dailyName = functions::datearr($rA['eat_date']).'<br>('.$rA['eat_day'].')'.$has_travel;
		$amin = ($rA['am_in']) ? functions::MilToTwelve($rA['am_in']) : '--:--';
		$amin .= ($rA['am_in_assign']) ? ' <i>('.functions::MilToTwelve($rA['am_in_assign']).')</i>' : ' (--:--)';

		$amout = ($rA['am_out']) ? functions::MilToTwelve($rA['am_out']) : '--:--';
		$amout .= ($rA['am_out_assign']) ? ' <i>('.functions::MilToTwelve($rA['am_out_assign']).')</i>' : ' (--:--)';
		$amDutyHrs = ($rA['am_in_assign'] && $rA['am_out_assign']) ? functions::min_diff($rA['am_in_assign'],$rA['eat_date'],$rA['am_out_assign'],$rA['eat_date']) : 0;

		$pmin = ($rA['pm_in']) ? functions::MilToTwelve($rA['pm_in']) : '--:--';
		$pmin .= ($rA['pm_in_assign']) ? ' <i>('.functions::MilToTwelve($rA['pm_in_assign']).')</i>' : ' (--:--)';

		$pmout = ($rA['pm_out']) ? functions::MilToTwelve($rA['pm_out']) : '--:--';
		$pmout .= ($rA['pm_out_assign']) ? ' <i>('.functions::MilToTwelve($rA['pm_out_assign']).')</i>' : ' (--:--)';

		$pmDutyHrs = ($rA['pm_in_assign'] && $rA['pm_out_assign']) ? functions::min_diff($rA['pm_in_assign'],$rA['eat_date'],$rA['pm_out_assign'],$rA['eat_date']) : 0;
		if($att_confirm)
			$bgColor='';
		else
			$bgColor = ( $db->getValue('emp_attendance_adjustment','count(*)',array('eatd_id'=>$rA['eatd_id'])) ) ? 'bgcolor="#f5ae00"' : '';
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
	<!-- end: CSS -->
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ATTENDANCE PREVIEW</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<?php
				$prevEmp = ( ($rowStart-1) >= 0 ) ? $db->getValue('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname LIMIT '.($rowStart-1).', 1') : '';
				$nextEmp = $db->getValue('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname LIMIT '.($rowStart+1).', 1');
				?>
				<table width="100%">
					<tr>
						<td width="50%"><div align="left"><?php if($prevEmp){ ?><a class="btn btn-info btn-large" title="Previous Employee" data-rel="tooltip" href="attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($prevEmp);?>&rw=<?php echo ($rowStart-1) ?>"><i class="icon-circle-arrow-left"></i></a><?php } ?></div></td>
						<td width="50%"><div align="right"><?php if($nextEmp){ ?><a class="btn btn-info btn-large" title="Next Employee" data-rel="tooltip" href="attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($nextEmp);?>&rw=<?php echo ($rowStart+1) ?>"><i class="icon-circle-arrow-right"></i></a><?php } ?></div></td>
					</tr>
				</table>
				<div align="right" style="padding:0px 50px 0px 0px;">
					<label class="checkbox inline" style="font-size:14px;"><input type="checkbox" name="chkConf" id="chkConf" value="1" onClick="statIndi()" <?php if($attendance_ready){echo 'disabled';}?> <?php if($att_confirm){echo 'checked';} ?>> <?php echo ($att_confirm) ? 'Verified' : 'Unverified';?></label>
				</div>
				<table border="0" width="90%" style="font-size: 12px;">
					<tr>
						<td width="10%" height="30px">Project</td>
						<td><strong><?php echo $proj_name;?></strong></td>
					</tr>
					<tr>
						<td height="30px">Period Covered</td>
						<td><strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong></td>
					</tr>
				</table><br>
				<?php if($txSalMonth==0){?><br><div align="center" style="color:red;text-decoration:blink">Warning: <strong style="font-size: 21px">Empolyee's Salary not set!</strong></div><br><?php }?>
				<table width="100%">
					<tr>
						<td>
							<table border="0" width="90%" style="font-size: 12px;">
								<tr>
									<td height="30px">ID:</td>
									<td><div align="left"><strong><?php echo $emp_no;?></strong></div></td>
								</tr>
								<tr>
									<td height="30px">Name:</td>
									<td><div align="left"><strong><?php echo $emp_name;?></strong></div></td>
								</tr>
								<tr>
									<td height="30px" valign="top"><div align="left">Position:</div></td>
									<td><div align="left"><strong><?php echo $position;?></strong></div></td>
								</tr>
								<tr>
									<td height="30px"><div align="left">Status:</div></td>
									<td><div align="left"><strong><?php echo ($work_status) ? $work_status : '--undefined--';?></strong></div></td>
								</tr>
							</table>
						</td>
						<td>
							<table width="98%" border="0" class="table table-bordered" style="font-size: 12px;">
								<thead>
									<tr style="background-color:#CCC">
										<th width="10%"><div align="center">Rendered Days (Hours)</div></th>
										<th width="10%"><div align="center">Absent Days (Hours)</div></th>
										<th width="10%"><div align="center">Required Days (Hours)</div></th>
										<th width="10%"><div align="center">Overtime Hours</div></th>
										<th width="10%"><div align="center">Total Duty</div></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$amountRegularHour = ($regularDutyHours && $txSalMin) ? ($regularDutyHours * $txSalMin) : 0;
									$amountOvertimeHour = ($otDutyHours && $txSalMin) ? ($otDutyHours * $txSalMin) : 0;
									$totalDuty = ($regularDutyHours + $otDutyHours);
									?>
									<tr>
										<td>
											<div align="center">
												<?php
												echo $regDays = ($regularDutyHours) ? number_format(($regularDutyHours/$hoursPerDay),2) : '0';
												echo ($regDays > 1) ? ' day/s' : ' day';
												echo ($regularDutyHours) ? ' ('.functions::min_to_hour($regularDutyHours).')' : '';
												?>
											</div>
										</td>
										<td>
											<div align="center">
												<?php
												echo $absentDays = ($hoursPerDay) ? number_format(($totalAbsent/$hoursPerDay),2) : '0';
												echo ($absentDays > 1) ? ' day/s' : ' day';
												echo ($totalAbsent) ? ' ('.functions::min_to_hour($totalAbsent).')' : '';
												?>
											</div>
										</td>
										<td>
											<div align="center">
												<?php
												echo $requiredDays = ( ($regularDutyHours || $totalAbsent) && $hoursPerDay) ? number_format(($regularDutyHours + $totalAbsent) / $hoursPerDay,2) : '0';
												echo ($requiredDays > 1) ? ' day/s' : ' day';
												echo ($regularDutyHours || $totalAbsent) ? ' ('.functions::min_to_hour($regularDutyHours + $totalAbsent).')' : '';
												?>
											</div>
										</td>
										<td><div align="center"><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '';?></div></td>
										<td>
											<div align="center">
												<strong>
													<?php
													echo $tDuty = ($totalDuty && $hoursPerDay) ? number_format(($totalDuty/$hoursPerDay),2) : '0';
													echo ($tDuty > 1) ? ' day/s' : ' day';
													echo ($totalDuty) ? ' ('.functions::min_to_hour($totalDuty).')' : '';
													?>
												</strong>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
<br>
				<?php if($has_attendance){?>
				<table width="100%" align="center" border="1" class="table-hover" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC">
							<td rowspan="2" style="padding-left:7px;">DATE</td>
							<td colspan="2"><div align="center">Morning</div></td>
							<td colspan="2"><div align="center">Afternoon</div></td>
							<td colspan="2"><div align="center">Overtime</div></td>
							<td colspan="4"><div align="center">Total</div></td>
						</tr>
						<tr style="background-color:#CCC">
							<td width="12%"><div align="center">In<br>Actual <i>(Assigned)</i></div></td>
							<td width="12%"><div align="center">Out<br>Actual <i>(Assigned)</i></div></td>
							<td width="12%"><div align="center">In<br>Actual <i>(Assigned)</i></div></td>
							<td width="12%"><div align="center">Out<br>Actual <i>(Assigned)</i></div></td>
							<td width="7%"><div align="center">In</div></td>
							<td width="7%"><div align="center">Out</div></td>
							<td width="7%"><div align="center">Duty</div></td>
							<td width="7%"><div align="center">Overtime</div></td>
							<td width="7%"><div align="center">Late</div></td>
							<td width="7%"><div align="center">Under</div></td>
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
						<td height="5" style="padding-left:7px;"><a id="vw<?php echo $rID?>" class="thickbox" style="cursor: pointer;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_adjust_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($emp_id);?>&eatdid=<?php echo functions::encode($rID);?>','Attendance Detail'<?php echo ($attendance_ready==1) ? ",'1'" : '' ?>)"><?php echo $dly['dailyName'];?></a></td>
						<td><div align="center"><?php echo $dly['amin'];?></div></td>
						<td><div align="center"><?php echo $dly['amout'];?></div></td>
						<td><div align="center"><?php echo $dly['pmin'];?></div></td>
						<td><div align="center"><?php echo $dly['pmout'];?></div></td>
						<td><div align="center"><?php echo $dly['otin'];?></div></td>
						<td><div align="center"><?php echo $dly['otout'];?></div></td>
						<td><div align="center"><?php echo ($dly['dutyHours']) ? functions::min_to_hour($dly['dutyHours']) : '';?></div></td>
						<td><div align="center"><?php echo ($dly['otHours']) ? functions::min_to_hour($dly['otHours']) : '';?></div></td>
						<td><div align="center"><?php echo ($dly['late']) ? functions::min_to_hour($dly['late']) : '';?></div></td>
						<td><div align="center"><?php echo ($dly['undertime']) ? functions::min_to_hour($dly['undertime']) : '';?></div></td>
					</tr>
					<?php endforeach;?>
						<tr>
							<td colspan="7">&nbsp;</td>
							<td><div align="center"><strong><?php echo ($regularDutyHours) ? functions::min_to_hour($regularDutyHours) : '';?></strong></div></td>
							<td><div align="center"><strong><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '';?></strong></div></td>
							<td><div align="center"><strong><?php echo ($totalLate) ? functions::min_to_hour($totalLate) : '';?></strong></div></td>
							<td><div align="center"><strong><?php echo ($totalUnder) ? functions::min_to_hour($totalUnder) : '';?></strong></div></td>
						</tr>
					</tbody>
				</table>
				<?php }else{?>
					<div align="center">Attendance not applicable</div>
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