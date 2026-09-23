<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$p_id = $rEatID['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $rEatID['confirmed'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$worker_type = $rEatID['payroll_type'];
$eas_name = $rEatID['note'];
$attendance_ready = $db->getValue('emp_attendance','attendance_ready',array('eat_id'=>$eatid));
$payroll_no = $rEatID['payroll_no'];

$prepared_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['prepared_by']));
$prepared_position = position($rEatID['prepared_by']);

$checked_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['checked_by']));
$checked_position = position($rEatID['checked_by']);

$received_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['received_by']));
$received_position = position($rEatID['received_by']);

$approved_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rEatID['approved_by']));
$approved_position = position($rEatID['approved_by']);

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
if($worker_type=='admin'){
    functions::sendTo('payroll_print.php?eatid='.functions::encode($eatid));
    die();
}

$arrDateInfo=array();
$range = functions::date_diff($date_start,$date_end);
for($i=0;$i<=$range;$i++):
	$succeeding_date = functions::AddDay($date_start,$i);
	$daysName = date('D', strtotime($succeeding_date));
	$daysDate = date('d', strtotime($succeeding_date));
	$arrDateInfo[$succeeding_date]=array('dayName'=>$daysName,'dayDate'=>$daysDate,'dayFullDate'=>$succeeding_date);
endfor;
$daysCount = count($arrDateInfo);

$arrMember = array();
$arrEmpAttendance = array();
$arrPayrollList = array();
$totalNetAmount=0;$totalGrossPay=0;$totalRegularPay=0;$totalOTPay=0;$totalAddonsPay=0;$totalAbsentPay=0;$totalSSSPay=0;$totalPHPay=0;$totalHMDFPay=0;$totalOthersPay=0;$totalAllDeduction=0;
$empRegularAmount=0;$empOTAmount=0;$empUndertimeAmount=0;$empLateAmount=0;$empAbsentAmount=0;$empTotalDeduction=0;$empGrossPay=0;$empNetPay=0;$empAddons=0;$empTotalDutyMin=0;$empTotalOTMin=0;
$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname');
#$qEmps = $db->select('emp_assign_detail eas_id, employee emp','DISTINCT eas_id.emp_id',array('eas_id'=>$eas_id,'emp.emp_id'=>'6'),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname');
while( $rEmps = $db->fetch_array($qEmps)):
	$other_deduction=0;$philhealth_contribution=0;$pagibig_contribution=0;$sss_contribution=0;$empRegularAmount=0;$empOTAmount=0;$empUndertimeAmount=0;$empLateAmount=0;$empAbsentAmount=0;$empTotalDeduction=0;$empGrossPay=0;$empNetPay=0;$empAddons=0;$empTotalDutyMin=0;$empTotalOTMin=0;

	$arrMember[]=$rEmps['emp_id'];
	$emp_id = $rEmps['emp_id'];
	$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$emp_id));
	#$name = $db->getValue('employee','concat(lname,", ",fname," ",left(mname,1),".")',array('emp_id'=>$emp_id));
	$name = $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$emp_id));

	$qDecd = $db->select('payroll_adjustment','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_type'=>'deduction','include'=>1));
	while($rD = $db->fetch_array($qDecd)):
		if( $rD['adjustment_name']=='SSS' )
			$sss_contribution = $rD['adjustment_value'];
		else if( $rD['adjustment_name']=='Pagibig' )
			$pagibig_contribution = $rD['adjustment_value'];
		else if( $rD['adjustment_name']=='Philhealth' )
			$philhealth_contribution = $rD['adjustment_value'];
		else if( $rD['adjustment_name']=='absences' ){
			//it is calculated down.
		}
		else{
			$other_deduction += $rD['adjustment_value'];
		}
	endwhile;

	$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$date_end.'" ORDER BY es_date DESC LIMIT 1');
	$rSal = $db->fetch_array($qSal);

	$sal_type = (isset($rSal['es_type']) ) ? $rSal['es_type'] : 0;

	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	$countPos=0;$position='';
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	$salary=0;$sal_daily=0;$sal_hourly=0;$sal_minute=0;
	if($sal_type){

		$salary = (isset($rSal['es_salary']) && is_numeric($rSal['es_salary']) ) ? $rSal['es_salary'] : 0;
		$sal_daily = (isset($rSal['es_daily']) && is_numeric($rSal['es_daily']) ) ? $rSal['es_daily'] : 0;
		$sal_hourly = (isset($rSal['es_hourly']) && is_numeric($rSal['es_hourly']) ) ? $rSal['es_hourly'] : 0;
		$sal_minute = (isset($rSal['es_minute']) && is_numeric($rSal['es_minute']) ) ? $rSal['es_minute'] : 0;

		$empAddons = $db->getValue('payroll_adjustment','sum(adjustment_value)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_type'=>'addon','include'=>1));

		$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
		while($rA = $db->fetch_array($qEmpAtt)):
			$arrEmpAttendance[$emp_id][$rA['eat_date']]=array('duty_min'=>$rA['duty_min'],'duty_amount'=>$rA['duty_amount'],'ot_min'=>$rA['ot_min'],'ot_amount'=>$rA['ot_amount']);
			$empRegularAmount += $rA['duty_amount'];
			$empOTAmount += $rA['ot_amount'];
			$empAbsentAmount += $rA['absent_amount'];
			$empUndertimeAmount += $rA['under_min'] * $rA['wage_minute'];
			$empTotalDutyMin += $rA['duty_min'];
			$empTotalOTMin += $rA['ot_min'];
		endwhile;

		$empTotalDeduction += $sss_contribution;
		$empTotalDeduction += $pagibig_contribution;
		$empTotalDeduction += $philhealth_contribution;
		$empTotalDeduction += $other_deduction;

		$empGrossPay = $empRegularAmount + $empOTAmount + $empAbsentAmount + $empAddons;
		$empNetPay = $empGrossPay - $empTotalDeduction - $empAbsentAmount;

		$totalGrossPay += $empGrossPay;
		$totalRegularPay += $empRegularAmount;
		$totalOTPay += $empOTAmount;
		$totalAddonsPay += $empAddons;
		$totalSSSPay += $sss_contribution;
		$totalPHPay += $philhealth_contribution;
		$totalHMDFPay += $pagibig_contribution;
		$totalOthersPay += $other_deduction;
		$totalAllDeduction += $empTotalDeduction;
		$totalNetAmount += $empNetPay;
	}

	$arrPayrollList[$emp_id] = array('emp_id'=>$emp_id,'emp_no'=>$emp_no,'name'=>$name,'position'=>$position,'salary'=>$salary,'salary_daily'=>$sal_daily,'salary_hourly'=>$sal_hourly,'salary_minute'=>$sal_minute,'sss_contribution'=>$sss_contribution,'philhealth_contribution'=>$philhealth_contribution,'pagibig_contribution'=>$pagibig_contribution,'other_deduction'=>$other_deduction,'addons'=>$empAddons,'regular_amount'=>$empRegularAmount,'duty_min'=>$empTotalDutyMin,'overtime_amount'=>$empOTAmount,'ot_min'=>$empTotalOTMin,'absent_amount'=>$empAbsentAmount,'total_deduction'=>$empTotalDeduction,'gross_pay'=>$empGrossPay,'net_pay'=>$empNetPay);
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Payroll Print (Labor)</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link href="../css/printerfoot.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	table { page-break-inside:auto }
	div   { page-break-inside:avoid; } /* This is the key */
	thead { display:table-header-group }
	tfoot { display:table-footer-group }
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="90%" align="center" border="1" class="tablea" cellpadding="2" style="font-size:12px;">
	<thead>
		<tr>
			<td colspan="23" align="center" style="border-top:hidden;border-left:hidden;border-right:hidden;">
				<?php 
				require_once('../class/print_header.php');
				print_header('Payroll');
				?>
			</td>
		</tr>
		<tr>
			<td colspan="23" style="border-top:hidden;border-left:hidden;border-right:hidden;">
				<table border="0" width="90%">
					<tr>
						<td width="20%" valign="top">Project / Department</td>
						<td><strong><?php echo $proj_name;?></strong></td>
					</tr>
					<tr>
						<td>Payroll No.</td>
						<td><strong><?php echo $payroll_no;?></strong></td>
					</tr>
					<tr>
						<td>Period Covered</td>
						<td><strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr style="font-size:10px">
			<th scope="col" width="18%" rowspan="4" scope="row">Name</th>
			<th scope="col" width="10%" rowspan="4" scope="row">Position</th>
			<th scope="col" width="4%" rowspan="4" scope="row"><div align="center">Rate / Day</div></th>
			<th scope="col" width="4%" rowspan="4" scope="row"><div align="center">Rate / Hour</div></th>
			<th scope="col" colspan="<?php echo ($daysCount * 2)?>" scope="row"><div align="center"><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></div></th>
			<th scope="col" rowspan="4" scope="row">No. of Hours</th>
			<th scope="col" rowspan="4" scope="row">Amount Hours</th>
			<?php if($totalOTPay){?>
			<th scope="col" width="5%" rowspan="4" scope="row">No. of Hours OT</th>
			<th scope="col" width="5%" rowspan="4" scope="row">Amount OT</th>
			<?php }?>
			<th scope="col" width="5%" rowspan="4" scope="row"><div align="center">Deductions</div></th>
			<th scope="col" width="5%" rowspan="4" scope="row"><div align="center">Net</div></th>
			<th scope="col" width="40%" rowspan="4" scope="row"><div align="center">Signature</div></th>
		</tr>
		<tr style="font-size:10px">
			<?php foreach($arrDateInfo as $dteInfo):?>
			<td scope="col" colspan="2" scope="row"><div align="center"><?php echo $dteInfo['dayDate']?></div></td>
			<?php endforeach;?>
		</tr>
		<tr style="font-size:10px">
			<?php foreach($arrDateInfo as $dteInfo):?>
			<td scope="col" colspan="2" scope="row"><div align="center"><?php echo $dteInfo['dayName']?></div></td>
			<?php endforeach;?>
		</tr>
		<tr style="font-size:10px">
			<?php foreach($arrDateInfo as $dteInfo):?>
			<td scope="col" width="3%" scope="row"><div align="center">Hour</div></td>
			<td scope="col" width="3%"><div align="center">OT</div></td>
			<?php endforeach;?>
		</tr>
	</thead>
	<tbody>
	<?php
	$countEmp=0;
	$totalDutyHours=0;$totalRegDutyAmount=0;
	foreach($arrMember as $memberID):
		$countEmp++;
		$totalRegDutyAmount=0;

		$pd = isset($arrPayrollList[$memberID]) ? $arrPayrollList[$memberID] : 0;
		$totalRegDutyAmount +=$pd['regular_amount'];
		$sal_day = $pd['salary_daily'];
		$sal_hr = $pd['salary_hourly'];
		$sal_min = $pd['salary_minute'];
		$duty_mins = $pd['duty_min'];
		$duty_mins_amount = $pd['regular_amount'];
		$ot_mins = $pd['ot_min'];
		$ot_mins_amount = $pd['overtime_amount'];
		$emp_deduction = $pd['total_deduction'];
		$emp_net_pay = $pd['net_pay'];
	?>
		<tr style="font-size:10px">
			<td height="10px"><?php echo $countEmp.'. '.$pd['name'];?></td>
			<td><div style="font-size:8px"><?php echo $pd['position'];?></div></td>
			<td><div align="center"><?php echo functions::formatMoney($sal_day);?></div></td>
			<td><div align="center"><?php echo functions::formatMoney($sal_hr);?></div></td>
			<?php
			$empDutyMins=0;
			foreach($arrDateInfo as $dteInfo):
			?>
			<td>
				<div align="center">
					<?php
					$dty_min = isset($arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['duty_min']) ? $arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['duty_min'] : 0;
					echo $dty_min = ($dty_min) ? number_format(($dty_min / 60),2) : '0';
					$empDutyMins += $dty_min;
					?>
				</div>
			</td>
			<td>
				<div align="center">
					<?php
					$ot_min = isset($arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['ot_min']) ? $arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['ot_min'] : 0;
					echo $ot_min = ($ot_min) ? number_format(($ot_min / 60),2) : '';
					?>
				</div>
			</td>
			<?php endforeach;?>
			<td><div align="center"><?php echo ($duty_mins) ? number_format(($duty_mins / 60),2) : 0;?></div></td>
			<td><div align="center"><?php echo functions::formatMoney($duty_mins_amount);?><?php #echo '<br>'.functions::formatMoney($duty_mins*$sal_min);?></div></td>
			<?php if($totalOTPay){?>
			<td><div align="right"><?php echo ($ot_mins) ? number_format(($ot_mins / 60),2) : '';?></div></td>
			<td><div align="right"><?php echo ($ot_mins_amount) ? functions::formatMoney($ot_mins_amount) : '';?></div></td>
			<?php }?>
			<td><div align="right"><?php echo ($emp_deduction) ? functions::formatMoney($emp_deduction) : '';?></div></td>
			<td><div align="right"><?php echo '<strong>'.functions::formatMoney($emp_net_pay).'</strong>';?></div></td>
			<td>&nbsp;</td>
		</tr>
		<?php #}
		endforeach;?>
		<tr height="30px" style="font-size:12px">
			<td colspan="<?php echo ($daysCount * 2) + 5?>"></td>
			<td><div align="right"><strong><?php echo functions::formatMoney($totalRegularPay);?></strong></div></td>
			<?php if($totalOTPay){?>
			<td></td>
			<td><div align="right"><strong><?php echo functions::formatMoney($totalOTPay);?></strong></div></td>
			<?php }?>
			<td><div align="right"><strong><?php echo functions::formatMoney($totalAllDeduction);?></strong></div></td>
			<td><div align="right"><strong><?php echo functions::formatMoney($totalNetAmount);?></strong></div></td>
			<td></td>
		</tr>
	</tbody>
</table>
<table border="0" width="100%" style="font-size:12px">
	<tr>
		<td align="center">Prepared By</td>
		<td align="center">Checked By</td>
		<td align="center">Received By</td>
		<td align="center">Approved By</td>
	</tr>
	<tr>
		<td height="40px" valign="bottom" align="center"><strong><u>&nbsp;<?php echo ($prepared_by) ? $prepared_by : '______________';?>&nbsp;</u></strong></td>
		<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo ($checked_by) ? $checked_by : '______________';?>&nbsp;</u></strong></td>
		<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo ($received_by) ? $received_by : '______________';?>&nbsp;</u></strong></td>
		<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo ($approved_by) ? $approved_by : '______________';?>&nbsp;</u></strong></td>
	</tr>
	<tr>
		<td align="center" style="font-size:10px"><i><?php echo $prepared_position?></i></td>
		<td align="center" style="font-size:10px"><i><?php echo $checked_position?></i></td>
		<td align="center" style="font-size:10px"><i><?php echo $received_position?></i></td>
		<td align="center" style="font-size:10px"><i><?php echo $approved_position?></i></td>
	</tr>
</table>
<footer>
	<div align="right">19HRD.FRM051.00-03/19</div>
</footer>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="payroll_view_labor.php?eatid=<?php echo functions::encode($eatid)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>