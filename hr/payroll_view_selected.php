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
		$amin .= ($rA['am_in_assign']) ? ' <i>('.functions::MilToTwelve($rA['am_in_assign']).')</i>' : ' (--:--)';

		$amout = ($rA['am_out']) ? functions::MilToTwelve($rA['am_out']) : '--:--';
		$amout .= ($rA['am_out_assign']) ? ' <i>('.functions::MilToTwelve($rA['am_out_assign']).')</i>' : ' (--:--)';
		$amDutyHrs = ($rA['am_in_assign'] && $rA['am_out_assign']) ? functions::min_diff($rA['am_in_assign'],$rA['eat_date'],$rA['am_out_assign'],$rA['eat_date']) : 0;

		$pmin = ($rA['pm_in']) ? functions::MilToTwelve($rA['pm_in']) : '--:--';
		$pmin .= ($rA['pm_in_assign']) ? ' <i>('.functions::MilToTwelve($rA['pm_in_assign']).')</i>' : ' (--:--)';

		$pmout = ($rA['pm_out']) ? functions::MilToTwelve($rA['pm_out']) : '--:--';
		$pmout .= ($rA['pm_out_assign']) ? ' <i>('.functions::MilToTwelve($rA['pm_out_assign']).')</i>' : ' (--:--)';

		$pmDutyHrs = ($rA['pm_in_assign'] && $rA['pm_out_assign']) ? functions::min_diff($rA['pm_in_assign'],$rA['eat_date'],$rA['pm_out_assign'],$rA['eat_date']) : 0;
		if($pay_confirm)
			$bgColor='';
		else
			$bgColor = ( $db->getValue('emp_attendance_adjustment','count(*)',array('eatd_id'=>$rA['eatd_id'])) ) ? 'bgcolor="#f5ae00"' : '';

		$regularDutyHours += $rA['duty_min'];
		$otDutyHours += $rA['ot_min'];
		$totalAbsent += $rA['late_min'] + $rA['under_min'];


		$dutyDisplay = ($rA['duty_min']) ? functions::min_to_hour($rA['duty_min']) : ''; #$dutyDisplay .= ($sal_type=='fixed') ? ' <strong>('.functions::formatMoney($rA['duty_amount']).')</strong>' : '';
		$otDisplay = ($rA['ot_min']) ? functions::min_to_hour($rA['ot_min']) : ''; #$otDisplay .= ($sal_type=='fixed' && $otDisplay) ? ' <strong>('.functions::formatMoney($rA['ot_amount']).')</strong>' : '';
		#$absentDisplay = ($rA['late_min'] || $rA['under_min']) ? functions::min_to_hour($rA['late_min'] + $rA['under_min']).' <strong>('.functions::formatMoney($rA['absent_amount']).')</strong>' : '';
		
		$absentDay=0;
		if($rA['am_absent']){
			$absentDay += $rA['am_absent'];
			$absentMin += $rA['am_absent_min'];
		}
		elseif($rA['am_absent_min']){
			#$absentMin += $rA['am_absent_min'];
		}
		if($rA['pm_absent']){
			$absentDay += $rA['pm_absent'];
			$absentMin += $rA['pm_absent_min'];
		}
		elseif($rA['pm_absent_min']){
			#$absentMin += $rA['pm_absent_min'];
		}

		$lateUnderMins = ( empty($rA['am_absent']) && empty($rA['pm_absent']) ) ? ($rA['late_min'] + $rA['under_min']) : ($rA['late_min'] + $rA['under_min']) - ($rA['am_absent_min']+$rA['pm_absent_min']);
		#$lateUnderMins = ($rA['late_min'] + $rA['under_min']) - ($rA['am_absent_min']+$rA['pm_absent_min']);
		#echo '<br>'.$rA['late_min'].' '.$rA['under_min'].' '.$rA['am_absent_min'].' '.$rA['pm_absent_min'];
		$absentDayDisplay =  ($absentDay) ? $absentDay.' (day)' : '';
		$absentHrDisplay = ($absentMin) ? functions::min_to_hour($absentMin).' (hr)' : '';
		$absentDisplay = $absentDayDisplay.' '.$absentHrDisplay;

		$empRegAmount += $rA['duty_amount'];
		$empOTAmount += $rA['ot_amount'];
		$empAbsentAmount += $rA['absent_amount'];
		if($sal_type=='flexible'){
			$es_minute = $rA['wage_minute'];
		}

		$otin = ($rA['ot_in']) ? functions::MilToTwelve($rA['ot_in']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
		$otout = ($rA['ot_out']) ? functions::MilToTwelve($rA['ot_out']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
		$arrDailyAttendance[] = array('eatd_id'=>$rA['eatd_id'],'dailyName'=>$dailyName,'amin'=>$amin,'amout'=>$amout,'pmin'=>$pmin,'pmout'=>$pmout,'otin'=>$otin,'otout'=>$otout,'dutyHours'=>$rA['duty_min'],'otHours'=>$rA['ot_min'],'late'=>$rA['late_min'],'undertime'=>$rA['under_min'],'dutyDisplay'=>$dutyDisplay,'otDisplay'=>$otDisplay,'absentDisplay'=>$absentDisplay,'bgColor'=>$bgColor,'absentDay'=>$absentDay,'absentMinutes'=>$absentMin,'lateUnderMins'=>$lateUnderMins);
	endwhile; #echo '<br>'.$absentMin;   
}
// echo '<pre>';print_r($arrDailyAttendance);
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
			<h2><i class="halflings-icon white list"></i><span class="break"></span>ATTENDANCE PAYROLL PREVIEW</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
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
				<table width="100%" border="1">
					<tr>
						<td valign="top" width="200"><img width="200" height="200" src="../img_emp/<?php echo $file;?>"></td>
						<td valign="top" style="padding-top: 10px; padding-left:5px;" width="55%">
							<table border="0" width="99%">
								<tr>
									<td width="10%" height="30px" valign="top"><i>ID No.</i></td>
									<td valign="top"><strong><?php echo $emp_no;?></strong></td>
								</tr>
								<tr>
									<td width="10%" height="30px" valign="top"><i>Name</i></td>
									<td valign="top"><strong><?php echo $emp_name;?></strong></td>
								</tr>
								<tr>
									<td valign="top" style="padding-top:10px;"><i>Position</i></td>
									<td valign="top" style="padding-top:10px;"><?php echo position($emp_id); ?></td>
								</tr>
								<tr>
									<td valign="top" style="padding-top:10px;"><i>Status</i></td>
									<td valign="top" style="padding-top:10px;">
										<?php
										$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_end.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
										$rStat = $db->fetch_array($qStat);
										$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : 'Undefined Status';
										$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <i>Project Based</i>' : '';
										$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
										echo $work_status = $wrkStat.$projBased.$wrkDate;
										?>
									</td>
								</tr>
							</table>
						</td>
						<td valign="top" style="padding-top: 10px;">
							<table border="0" width="90%">
								<tr>
									<td width="35%"><div align="right" style="padding:2px;">Salary Type:</div></td>
									<td><div align="left" style="padding:2px;"><strong><?php if($sal_type=='flexible')echo 'Monthly';elseif($sal_type=='fixed')echo 'Daily';else{echo 'Unidentified';}?></strong></div></td>
								</tr>
								<?php if($sal_type=='flexible'){ ?>
								<tr>
									<td><div align="right" style="padding:2px;">Monthly:</div></td>
									<td><div align="left" style="padding:2px;"><strong><?php echo functions::formatMoney($rSal['es_salary'])?></strong></div></td>
								</tr>
								<tr>
									<td><div align="right" style="padding:2px;">Semi-Monthly:</div></td>
									<td><div align="left" style="padding:2px;"><strong><?php echo ($rSal['es_salary']) ? functions::formatMoney($rSal['es_salary']/2): 0;?></strong></div></td>
								</tr>
								<tr style="display:none;">
									<td><div align="right" style="padding:2px;">Working Days:</div></td>
									<td><div align="left" style="padding:2px;"><strong><?php echo $rSal['working_days'] ?></strong></div></td>
								</tr>
								<?php }?>
								<tr>
									<td><div align="right" style="padding:2px;">Daily:</div></td>
									<td><div align="left" style="padding:2px;"><strong><?php echo functions::formatMoney($rSal['es_daily'],'2'); ?></strong></div></td>
								</tr>
								<tr>
									<td><div align="right" style="padding:2px;">Hourly:</div></td>
									<td><div align="left" style="padding:2px;"><strong><?php echo functions::formatMoney($rSal['es_hourly'],'2'); ?></strong></div></td>
								</tr>
								<tr>
									<td><div align="right" style="padding:2px;">Minute:</div></td>
									<td><div align="left" style="padding:2px;"><strong><?php echo functions::formatMoney($rSal['es_minute'],'2'); ?></strong></div></td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br>
				<?php if( empty($sal_type) ){echo '<div align="center" style="color:red">Warning: <strong style="font-size: 21px">Salary not set!</strong></div>';}
				else if($has_attendance==1){?>
				<table width="100%" align="center" class="table-hover" border="1" style="font-size: 12px;">
					<thead>
					<tr style="background-color:#CCC">
						<td>DATE</td>
						<td colspan="2"><div align="center">Morning</div></td>
						<td colspan="2"><div align="center">Afternoon</div></td>
						<td colspan="2"><div align="center">Overtime</div></td>
						<td colspan="4"><div align="center">Total</div></td>
					</tr>
					<tr style="background-color:#CCC">
						<td width="9%">&nbsp;</td>
						<td width="10%"><div align="center">In<br>Actual <i>(Assigned)</i></div></td>
						<td width="10%"><div align="center">Out<br>Actual <i>(Assigned)</i></div></td>
						<td width="10%"><div align="center">In<br>Actual <i>(Assigned)</i></div></td>
						<td width="10%"><div align="center">Out<br>Actual <i>(Assigned)</i></div></td>
						<td width="7%"><div align="center">In</div></td>
						<td width="7%"><div align="center">Out</div></td>
						<td width="10%"><div align="center">Duty</div></td>
						<td width="10%"><div align="center">Overtime</div></td>
						<td width="10%"><div align="center">Absent</div></td>
						<td width="10%"><div align="center">Tardy/Under</div></td>
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
						<tr <?php echo $dly['bgColor']?>>
							<td><a id="vw<?php echo $countAtt?>" class="thickbox" style="cursor: pointer;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_adjust_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($emp_id);?>&eatdid=<?php echo functions::encode($rID);?>','Attendance Detail','1')"><?php echo $dly['dailyName'];?></a></td>
							<td><div align="center"><?php echo $dly['amin'];?></div></td>
							<td><div align="center"><?php echo $dly['amout'];?></div></td>
							<td><div align="center"><?php echo $dly['pmin'];?></div></td>
							<td><div align="center"><?php echo $dly['pmout'];?></div></td>
							<td><div align="center"><?php echo $dly['otin'];?></div></td>
							<td><div align="center"><?php echo $dly['otout'];?></div></td>
							<td><div align="center"><?php echo $dly['dutyDisplay'];?></div></td>
							<td><div align="center"><?php echo $dly['otDisplay'];?></div></td>
							<td><div align="center"><?php echo $dly['absentDisplay'];?></div></td>
							<td><div align="center"><?php echo ($dly['lateUnderMins']>0) ? functions::min_to_hour($dly['lateUnderMins']) : '';?></div></td>
						</tr>
						<?php endforeach;?>
						<tr>
							<td colspan="7" align="right">Total Hours &nbsp;&nbsp;</td>
							<td><div align="center"><strong><?php echo ($regularDutyHours) ? functions::min_to_hour($regularDutyHours) : '';?></strong></div></td>
							<td><div align="center"><strong><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '';?></strong></div></td>
							<td>
								<div align="center">
									<strong><?php echo ($totalAbsentMinutes) ? functions::min_to_hour($totalAbsentMinutes) : '';?>
										<?php #echo $totalAbsentMinutes;
										// if($totalAbsentDay)
										// 	echo ($totalAbsentDay>1) ? $totalAbsentDay.' (days)' : $totalAbsentDay.' (day)';
										// if( $totalAbsentMinutes ){
										// 	$hrOnly = intval($totalAbsentMinutes / 60);
										// 	if($remMinute = ($totalAbsentMinutes % 60) ){
										// 		$hrOnly += round($remMinute / 60,2);
										// 	}
										// 	echo '<br>'.$hrOnly.'(hr)';
										// }
										?>
									</strong>
								</div>
							</td>
							<td><div align="center"><?php echo ($totalLateUnderMins) ? functions::min_to_hour($totalLateUnderMins) : ''; ?></div></td>
						</tr>
						<tr>
							<td colspan="7" align="right">Amount &nbsp;&nbsp;</td>
							<td <?php if( $sal_type=='fixed' && $regularDutyHours)echo 'style="color:#FFF; background-color:green"'; ?>><div align="center"><strong><?php echo ( $sal_type=='fixed' && $regularDutyHours) ? functions::formatMoney($regularDutyHours*$es_minute,'2') : '';?></strong> </div></td>
							<td <?php if( $otDutyHours)echo 'style="color:#FFF; background-color:green"'; ?>><div align="center"><strong><?php echo ($otDutyHours) ? functions::formatMoney($otDutyHours*$es_minute,'2') : '';?></strong> </div></td>
							<td class="rwAbsent" <?php if( $totalAbsentMinutes )echo 'style="color:#FFF; background-color:green"'; ?>><div align="center"><strong><?php echo ($totalAbsentMinutes) ? functions::formatMoney(($totalAbsentMinutes*$es_minute),'2') : '';?></strong></div></td>
							<td class="rwAbsent"><div align="center"><strong><?php echo ($totalLateUnderMins) ? functions::formatMoney($totalLateUnderMins*$es_minute,'2') : '' ?></strong></div></td>
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
<?php if( $absentView ){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('.rwAbsent').centerView();
	$('.rwAbsent').css('border','3px solid red');
});
</script>
<?php } ?>
<!-- end: JavaScript-->
</body>
</html>