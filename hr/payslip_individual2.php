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

$emp_id = (isset($_REQUEST['empid']) && !empty($_REQUEST['empid']) ) ? functions::decode($_REQUEST['empid']) : '';
$eat_id = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : '';
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eat_id));
$rEatID = $db->fetch_array($qEatID);
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
#$emp_id=47;
#$eat_id=56;
#ea.*,epd.*,emp.lname,emp.fname
$q = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','ea.*,epd.*,emp.lname,emp.fname,emp.cert_id,emp.work_status,emp.emp_no,epd.sss as epd_sss',array('ea.eat_id'=>$eat_id,'emp.emp_id'=>$emp_id),'AND ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id');

#$q = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','*,epd.sss as epd_sss',array('ea.eat_id'=>$eat_id,'emp.emp_id'=>$emp_id),'AND ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id GROUP BY date_start');
#echo $db->last_query;
$r = $db->fetch_array($q);
$cert_id = $r['cert_id'];
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
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
$file = (file_exists('../img_emp/'.$fileName) && $fileName) ? $fileName : 'blank-pic.png';
$gross_pay=0; $deduction=0;$payroll_payment=0;

$emp_att = $db->select('emp_attendance_detail','*',array('eat_id'=>$eat_id,'emp_id'=>$emp_id),'AND (late_min > 0 OR under_min > 0) AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"');
#echo $db->last_query;
$total_absent=0; $absent_minutes=0; $late_minutes=0;
while($reat = $db->fetch_array($emp_att)):
	$absent_count=0;
	$am_in_assign = $reat['am_in_assign'];
	$am_in = $reat['am_in'];
	$am_out_assign = $reat['am_out_assign'];
	$am_out = $reat['am_out'];

	$pm_in_assign = $reat['pm_in_assign'];
	$pm_in = $reat['pm_in'];
	$pm_out_assign = $reat['pm_out_assign'];
	$pm_out = $reat['pm_out'];

	//Morning Absent Count
	if(empty($am_in) && empty($am_out)){//If there is no logins, meaning its definitely absent
		$absent_minutes += functions::min_diff($am_in_assign,$reat['eat_date'],$am_out_assign,$reat['eat_date']);
	}
	else if( !empty($am_in) && empty($am_out) ){//If there is morning in, but no morning out, its good as absent.
		$absent_minutes += functions::min_diff($am_in_assign,$reat['eat_date'],$am_out_assign,$reat['eat_date']);
	}
	else if( !empty($am_in) && !empty($am_out) ){//IF there are logins.
		if( strtotime($am_out) <= strtotime($am_in_assign) )//If morning out is less than or equal the morning assign in, then it's undertime the whole morning.
			$absent_minutes += functions::min_diff($am_in_assign,$reat['eat_date'],$am_out_assign,$reat['eat_date']);
		else if( strtotime($am_in) >= strtotime($am_out_assign) ){//If morning in is greater than or equal the morning assign out, then it's late the whole morning.
			$absent_minutes += functions::min_diff($am_in_assign,$reat['eat_date'],$am_out_assign,$reat['eat_date']);
		}
	}


	//Afternoon Absent Count
	if(empty($pm_in) && empty($pm_out)){//If there is no logins, meaning its definitely absent
		$absent_minutes += functions::min_diff($pm_in_assign,$reat['eat_date'],$pm_out_assign,$reat['eat_date']);
	}
	else if( !empty($pm_in) && empty($pm_out) ){//If there is afternoon in, but no afternoon out, its good as absent.
		$absent_minutes += functions::min_diff($pm_in_assign,$reat['eat_date'],$pm_out_assign,$reat['eat_date']);
	}
	else if( !empty($pm_in) && !empty($pm_out) ){//IF there are logins.
		if( strtotime($pm_out) <= strtotime($pm_in_assign) )//If afternoon out is less than or equal the afternoon assign in, then it's undertime the whole afternoon.
			$absent_minutes += functions::min_diff($pm_in_assign,$reat['eat_date'],$pm_out_assign,$reat['eat_date']);
		else if( strtotime($pm_in) >= strtotime($pm_out_assign) ){//If afternoon in is greater than or equal the afternoon assign out, then it's late the whole afternoon.
			$absent_minutes += functions::min_diff($pm_in_assign,$reat['eat_date'],$pm_out_assign,$reat['eat_date']);
		}
	}
endwhile;
#echo $absent_minutes;
#echo '<br>'.$db->getValuePrint('emp_attendance_detail','sum(late_min + under_min)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id),' AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"');
$basic_salary_display='--undefined--';
$payroll_payment = $r['regular'] + $r['absent'];

$salQ = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$r['date_end'].'" ORDER BY es_date DESC LIMIT 1');
$salR = $db->fetch_array($salQ);
$sal_monthly = $salR['es_salary'];
$sal_daily = $salR['es_daily'];
$sal_hourly = $salR['es_hourly'];
$sal_min = $salR['es_minute'];
if($r['salary_type']=='flexible'){
	$basic_salary_display = functions::formatMoney($sal_monthly);
	$payroll_payment = $sal_monthly / 2;
}	
else if($r['salary_type']=='fixed'){
	$sal_daily = ($sal_daily) ? $sal_daily : 0;
	$basic_salary_display = functions::formatMoney($sal_daily).' <i>(Daily)</i>';
}
#echo $basic_salary_display;
function minsto($mins=0){
	return ($mins) ? ($mins/60) : 0 ;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payslip Individual</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>Payroll</h2></div>

				<table width="99%" border='0'>
					<tr>
						<td width="40%" valign="top">
							<table border='0' width="99%">
								<tr>
									<td colspan="2"><img width="300" height="300" src="../img_emp/<?php echo $file;?>"></td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr>
									<td width="20%" height="30"><i>Name</i></td>
									<td><strong><?php echo $r['lname'].', '.$r['fname'];?></strong></td>
								</tr>
								<tr>
									<td height="30"><i>Position</i></td>
									<td><?php echo position($emp_id); ?></td>
								</tr>
								<tr>
									<td height="30"><i>Status</i></td>
									<td>
										<?php
										$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id,'ews_stat'=>$r['work_status']),'ORDER BY ews_date DESC');
										$rStat = $db->fetch_array($qStat);
										$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
										$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
										echo $wrkStat . $wrkDate;
										?>
									</td>
								</tr>
							</table>
						</td>
						<td valign="top">
							<div align="left">
								<table border="1" cellpadding="2" width="80%" style="font-size:12px;">
									<tr>
										<td width="20%"><div align="left">Payroll No.</div></td>
										<td colspan="3"><div align="left"><?php echo $r['payroll_no'];?></div></td>
									</tr>
									<tr>
										<td><div align="left">Period Cover</div></td>
										<td colspan="3"><div align="left"><?php echo functions::datearr($r['date_start']).' - '.functions::datearr($r['date_end']);?></div></td>
									</tr>
									<tr>
										<td><div align="left">Name</div></td>
										<td colspan="3"><div align="left"><?php echo $r['lname'].', '.$r['fname'];?></div></td>
									</tr>
									<tr>
										<td><div align="left">ID No.</div></td>
										<td colspan="3"><div align="left"><?php echo $r['emp_no'];?></div></td>
									</tr>
									<tr>
										<td colspan="4">&nbsp;</td>
									</tr>
									<tr>
										<td width="70%" colspan="2"><div align="left">No. of Days</div></td>
										<td width="15%"><div align="right"><?php echo functions::date_diff($r['date_start'],$r['date_end'],$includeDay1=1) ?></div></td>
										<td width="15%"></td>
									</tr>
									<tr>
										<td colspan="2"><div align="left">Regular Pay</div></td>
										<td></td>
										<td><div align="right" title="<?php echo functions::formatMoney($payroll_payment,'~');?>"><?php echo functions::formatMoney($payroll_payment); $gross_pay += ($payroll_payment) ? $payroll_payment : 0;?></div></td>
									</tr>
									<?php if($r['ot']){
										$ot_mn = $db->getValue('emp_attendance_detail','sum(ot_min)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id));
										$ot_hr = ($ot_mn) ? round($ot_mn/60,2) : 0;
										?>
									<tr>
										<td colspan="2"><div align="left">Overtime Pay</div></td>
										<td><div align="right"><?php echo $ot_hr; ?></div></td>
										<td><div align="right" title="<?php echo functions::formatMoney($r['ot'],'~');?>"><?php echo functions::formatMoney($r['ot']); $gross_pay += ($r['ot']) ? $r['ot'] : 0;?></div></td>
									</tr>
									<?php }if($r['addons']){?>
									<tr>
										<td colspan="2"><div align="left">Add-ons:</div></td>
										<td></td>
										<td>&nbsp;</td>
									</tr>
									<?php
										$qAO = $db->select('payroll_adjustment','*',array('emp_id'=>$emp_id,'eat_id'=>$eat_id,'adjustment_type'=>'addon'),'ORDER BY adjustment_name');
										while($rAO = $db->fetch_array($qAO)):
									?>
									<tr>
										<td colspan="2"><div align="left" style="padding-left: 10px;"><i><?php echo $rAO['adjustment_name']?></i></div></td>
										<td><?php if($rAO['adjustment_desc']){ ?><div align="right" style="padding-right: 0px;"><i><?php echo (is_numeric($rAO['adjustment_desc'])) ? ($rAO['adjustment_desc']/60).' (hr)' : $rAO['adjustment_desc'];?></i></div><?php } ?></td>
										<td><div align="right" title="<?php echo functions::formatMoney($rAO['adjustment_value'],'~');?>"><?php echo functions::formatMoney($rAO['adjustment_value']); $gross_pay += ($rAO['adjustment_value']) ? $rAO['adjustment_value'] : 0;?></div></td>
									</tr>
									<?php
										endwhile;
									} ?>
									<tr>
										<td colspan="2"><div align="left"><strong>Gross Pay</strong></div></td>
										<td></td>
										<td><div align="right" title="<?php echo functions::formatMoney($gross_pay,'~');?>"><strong><?php echo functions::formatMoney($gross_pay,2);?></strong></div></td>
									</tr>
									<tr>
										<td colspan="4">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="2"><div align="left"><strong>Deductions:</strong></div></td>
										<td></td>
										<td></td>
									</tr>
									<?php
									$att_absence = $db->getValue('emp_attendance_detail','sum(am_absent)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id,'am_absent'=>.5));
									$att_absence += $db->getValue('emp_attendance_detail','sum(pm_absent)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id,'pm_absent'=>.5));

									$absent_minutesx = $db->getValue('emp_attendance_detail','sum(am_absent_min)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id,'am_absent'=>.5));
									$absent_minutesx += $db->getValue('emp_attendance_detail','sum(pm_absent_min)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id,'pm_absent'=>.5));
									if($att_absence){
										$deduction += $absent_minutesx*$sal_min;
									?>
									<tr>
										<td colspan="2"><div align="left">Absences <i>(Day)</i></div></td>
										<td><div align="right"><?php echo $att_absence;?></div></td>
										<td><div align="right" title="<?php echo functions::formatMoney($absent_minutesx*$sal_min,'~'); ?>"><?php echo functions::formatMoney($absent_minutesx*$sal_min); ?></div></td>
									</tr>
									<?php } ?>
									<?php
									$att_late = $db->getValue('emp_attendance_detail','sum(am_absent_min)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id,'am_absent'=>0));
									$att_late += $db->getValue('emp_attendance_detail','sum(pm_absent_min)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id,'pm_absent'=>0));
									if($att_late){
										$deduction += $att_late*$sal_min;
									?>
									<tr>
										<td colspan="2"><div align="left">Late/Undertime <i>(Minutes)</i></div></td>
										<td><div align="right"><?php echo ($att_late);?></div></td>
										<td><div align="right" title="<?php echo functions::formatMoney($att_late*$sal_min,'~'); ?>"><?php echo functions::formatMoney($att_late*$sal_min); ?></div></td>
									</tr>
									<?php } ?>
									<?php if($r['epd_sss']){ ?>
									<tr>
										<td colspan="2"><div align="left">SSS</div></td>
										<td></td>
										<td><div align="right" title="<?php echo functions::formatMoney($r['epd_sss'],'~');?>"><?php echo functions::formatMoney($r['epd_sss']); $deduction += ($r['epd_sss']) ? $r['epd_sss'] : 0;?></div></td>
									</tr>
									<?php } ?>
									<?php if($r['hdmf']){ ?>
									<tr>
										<td colspan="2"><div align="left">HDMF</div></td>
										<td></td>
										<td><div align="right" title="<?php echo functions::formatMoney($r['hdmf'],'~');?>"><?php echo functions::formatMoney($r['hdmf']); $deduction += ($r['hdmf']) ? $r['hdmf'] : 0;?></div></td>
									</tr>
									<?php } ?>
									<?php if($r['ph']){ ?>
									<tr>
										<td colspan="2"><div align="left">PHIC</div></td>
										<td></td>
										<td><div align="right" title="<?php echo functions::formatMoney($r['ph'],'~');?>"><?php echo functions::formatMoney($r['ph']); $deduction += ($r['ph']) ? $r['ph'] : 0;?></div></td>
									</tr>
									<?php } ?>
									<?php if($r['others']){ ?>
									<tr>
										<td colspan="2"><div align="left">Others:</div></td>
										<td></td>
										<td>&nbsp;</td>
									</tr>
									<?php
										$qDO = $db->select('payroll_adjustment','*',array('emp_id'=>$emp_id,'eat_id'=>$eat_id,'adjustment_type'=>'deduction'),'ORDER BY adjustment_name');
										while($rDO = $db->fetch_array($qDO)):
											if( $rDO['adjustment_name']=='SSS' || $rDO['adjustment_name']=='Pagibig' || $rDO['adjustment_name']=='Philhealth' || $rDO['adjustment_name']=='absences' ){

											}
											else{
												$total_deduction = $db->getValue('payroll_deduction_reference','total_amount',array('pdf_id'=>$rDO['pdf_id']));
												$balance = ($total_deduction) ? $total_deduction - $db->getValue('payroll_adjustment pa, emp_attendance ea','sum(adjustment_value)',array('emp_id'=>$emp_id,'pdf_id'=>$rDO['pdf_id']),'AND ea.eat_id=pa.eat_id AND date_start <= "'.$r['date_start'].'"') : 0;
									?>
									<tr>
										<td colspan="2"><div align="left" style="padding-left: 10px;"><i><?php echo $rDO['adjustment_name']?><?php echo ($total_deduction) ? '&nbsp;&nbsp;&nbsp;&nbsp;<i style="font-size:10px;">(Balance: '.functions::formatMoney($balance).')</i>&nbsp;&nbsp;&nbsp;' : '';  ?></i></div></td>
										<td><div align="right"></div></td>
										<td><div align="right" title="<?php echo functions::formatMoney($rDO['adjustment_value'],'~');?>"><?php echo functions::formatMoney($rDO['adjustment_value']); $deduction += ($rDO['adjustment_value']) ? $rDO['adjustment_value'] : 0;?></div></td>
									</tr>
									<?php
											}
										endwhile;
									} ?>
									<tr>
										<td colspan="2"><div align="left"><strong>Total Deduction</strong></div></td>
										<td></td>
										<td><div align="right" title="<?php echo functions::formatMoney($deduction,'~');?>"><strong><?php echo functions::formatMoney($deduction,2);?></strong></div></td>
									</tr>
									<tr>
										<td colspan="2"><div align="left"><strong>NET Pay</strong></div></td>
										<td></td>
										<td><div align="right" title="<?php echo functions::formatMoney($r['net_pay'],'~');?>"><strong><?php echo functions::formatMoney($r['net_pay'],2);?></strong></div></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
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
<!-- end: JavaScript-->
</body>
</html>