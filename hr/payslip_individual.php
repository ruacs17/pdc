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

#$emp_id=47;
#$eat_id=56;

$q = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','*,epd.sss as epd_sss',array('ea.eat_id'=>$eat_id,'emp.emp_id'=>$emp_id),'AND ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id GROUP BY date_start');
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



$basic_salary_display='--undefined--';
$payroll_payment = $r['regular'] + $r['absent'];
if($r['salary_type']=='flexible'){
	$sal_monthly = $db->getValue('emp_salary','es_salary',array('emp_id'=>$emp_id),'AND es_date <= "'.$r['date_end'].'" ORDER BY es_date DESC LIMIT 1');
	$basic_salary_display = functions::formatMoney($sal_monthly);
	$payroll_payment = $sal_monthly / 2;
}	
else if($r['salary_type']=='fixed'){
	$sal_daily = $db->getValue('emp_salary','es_daily',array('emp_id'=>$emp_id),'AND es_date <= "'.$r['date_end'].'" ORDER BY es_date DESC LIMIT 1');
	$sal_daily = ($sal_daily) ? $sal_daily : 0;
	$basic_salary_display = functions::formatMoney($sal_daily).' <i>(Daily)</i>';
	$payroll_payment = $r['regular'] + $r['absent'];
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
	<script src="../js/inputInt.js"></script>
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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

				<table width="99%" border='0' style="font-size:14px;">
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
								<table border="1" cellpadding="2" width="80%">
									<tr>
										<td width="20%"><div align="left">Payroll No.</div></td>
										<td colspan="2"><div align="left"><?php echo $r['payroll_no'];?></div></td>
									</tr>
									<tr>
										<td><div align="left">Period Cover</div></td>
										<td colspan="2"><div align="left"><?php echo functions::datearr($r['date_start']).' - '.functions::datearr($r['date_end']);?></div></td>
									</tr>
									<tr>
										<td><div align="left">Name</div></td>
										<td colspan="2"><div align="left"><?php echo $r['lname'].', '.$r['fname'];?></div></td>
									</tr>
									<tr>
										<td><div align="left">ID No.</div></td>
										<td colspan="2"><div align="left"><?php echo $r['emp_no'];?></div></td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3"><div align="left"><strong>Earnings:</strong></div></td>
									</tr>
									<tr>
										<td colspan="2" width="60%"><div align="left">Basic Pay</div></td>
										<td width="20%">
											<div align="right">
												<?php
												echo $basic_salary_display;
												?>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="2"><div align="left">Regular Pay</div></td>
										<td><div align="right"><?php echo functions::formatMoney($payroll_payment); $gross_pay += ($payroll_payment) ? $payroll_payment : 0;?></div></td>
									</tr>
									<?php if($r['ot']){
										$ot_mn = $db->getValue('emp_attendance_detail','sum(ot_min)',array('eat_id'=>$eat_id,'emp_id'=>$emp_id));
										$ot_hr = ($ot_mn) ? round($ot_mn/60,2) : 0;
										?>
									<tr>
										<td colspan="2"><div align="left">Regular Overtime <i>(<?php echo $ot_hr; echo ($ot_hr > 1) ? ' Hours' : ' Hour'; ?>)</i></div></td>
										<td><div align="right"><?php echo functions::formatMoney($r['ot']); $gross_pay += ($r['ot']) ? $r['ot'] : 0;?></div></td>
									</tr>
									<?php }if($r['addons']){?>
									<tr>
										<td colspan="2"><div align="left">Add-ons:</div></td>
										<td>&nbsp;</td>
									</tr>
									<?php
										$qAO = $db->select('payroll_adjustment','*',array('emp_id'=>$emp_id,'eat_id'=>$eat_id,'adjustment_type'=>'addon'),'ORDER BY adjustment_name');
										while($rAO = $db->fetch_array($qAO)):
									?>
									<tr>
										<td colspan="2">
											<div align="left" style="padding-left: 10px;"><i><?php echo $rAO['adjustment_name']?></i></div>
											<?php if($rAO['adjustment_desc']){ ?><div align="left" style="padding-left: 10px; display:none;"><i><?php echo ($rAO['adjustment_desc']) ? $rAO['adjustment_desc'] / 60 : 0;?></i></div><?php } ?>
										</td>
										<td><div align="right"><?php echo functions::formatMoney($rAO['adjustment_value']); $gross_pay += ($rAO['adjustment_value']) ? $rAO['adjustment_value'] : 0;?></div></td>
									</tr>
									<?php
										endwhile;
									} ?>
									<tr>
										<td colspan="2"><div align="left"><strong>Gross Pay</strong></div></td>
										<td><div align="right"><strong><?php echo functions::formatMoney($gross_pay,2);?></strong></div></td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3"><div align="left"><strong>Deductions:</strong></div></td>
									</tr>
									<?php if($r['absent']){ ?>
									<tr>
										<td colspan="2"><div align="left">Absences</div></td>
										<td><div align="right"><?php echo functions::formatMoney($r['absent']); $deduction += ($r['absent']) ? $r['absent'] : 0;?></div></td>
									</tr>
									<?php } ?>
									<tr>
										<td colspan="2"><div align="left">SSS</div></td>
										<td><div align="right"><?php echo functions::formatMoney($r['epd_sss']); $deduction += ($r['epd_sss']) ? $r['epd_sss'] : 0;?></div></td>
									</tr>
									<tr>
										<td colspan="2"><div align="left">HDMF</div></td>
										<td><div align="right"><?php echo functions::formatMoney($r['hdmf']); $deduction += ($r['hdmf']) ? $r['hdmf'] : 0;?></div></td>
									</tr>
									<tr>
										<td colspan="2"><div align="left">PHIC</div></td>
										<td><div align="right"><?php echo functions::formatMoney($r['ph']); $deduction += ($r['ph']) ? $r['ph'] : 0;?></div></td>
									</tr>
									<?php if($r['others']){ ?>
									<tr>
										<td colspan="2"><div align="left">Others:</div></td>
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
										<td colspan="2"><div align="left"><i><?php echo $rDO['adjustment_name']?></i></div></td>
										<td><div align="right"><?php echo ($total_deduction) ? '<i style="font-size:9px;">(Balance: '.functions::formatMoney($balance).')</i>&nbsp;&nbsp;&nbsp;' : ''; echo functions::formatMoney($rDO['adjustment_value']); $deduction += ($rDO['adjustment_value']) ? $rDO['adjustment_value'] : 0;?></div></td>
									</tr>
									<?php
											}
										endwhile;
									} ?>
									<tr>
										<td colspan="2"><div align="left"><strong>Total Deduction</strong></div></td>
										<td><div align="right"><strong><?php echo functions::formatMoney($deduction,2);?></strong></div></td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="2"><div align="left"><strong>NET Pay</strong></div></td>
										<td><div align="right"><strong><?php echo functions::formatMoney($r['net_pay'],2);?></strong></div></td>
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