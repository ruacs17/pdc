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

if($worker_type=='labor'){
	functions::sendTo('payroll_print_labor.php?eatid='.functions::encode($eatid));
	die();
}
$arrMember = array();
$arrPayrollList = array();
$totalNetAmount=0;$totalGrossPay=0;$totalRegularPay=0;$totalOTPay=0;$totalAddonsPay=0;$totalAbsentPay=0;$totalSSSPay=0;$totalPHPay=0;$totalHMDFPay=0;$totalOthersPay=0;$totalAllDeduction=0;
$empRegularAmount=0;$empOTAmount=0;$empUndertimeAmount=0;$empLateAmount=0;$empAbsentAmount=0;$empTotalDeduction=0;$empGrossPay=0;$empNetPay=0;$empAddons=0;


$qEmps = $db->select('employee emp, emp_payroll_detail epd','epd.*,lname,fname,left(mname,1) as mid,emp_no',array('eat_id'=>$eatid),'AND epd.emp_id=emp.emp_id ORDER BY lname,fname');
#echo $db->last_query;
while($rE = $db->fetch_array($qEmps)):
	$arrMember[]=$rE['emp_id'];
	$name = $rE['lname'].', '.$rE['fname'].' '.$rE['mid'];
	$empTotalDeduction=0;
	$empTotalDeduction += $rE['sss'];
	$empTotalDeduction += $rE['hdmf'];
	$empTotalDeduction += $rE['ph'];
	$empTotalDeduction += $rE['others'];
	$empTotalDeduction += $rE['absent'];
	$empGrossPay = $rE['gross'];
	$empAddons = $rE['addons'];
	$empNetPay = ($empGrossPay) - $empTotalDeduction;

	$totalGrossPay += $empGrossPay;
	$totalRegularPay += $rE['regular'];
	$totalOTPay += $rE['ot'];
	$totalAddonsPay += $empAddons;
	$totalAbsentPay += $rE['absent'];
	$totalSSSPay += $rE['sss'];
	$totalPHPay += $rE['ph'];
	$totalHMDFPay += $rE['hdmf'];
	$totalOthersPay += $rE['others'];
	$totalAllDeduction += $empTotalDeduction;
	$totalNetAmount += $empNetPay;
	$arrPayrollList[$rE['emp_id']] = array('emp_id'=>$rE['emp_id'],'emp_no'=>$rE['emp_no'],'name'=>$name,'salary'=>$rE['salary'],'sss_contribution'=>$rE['sss'],'philhealth_contribution'=>$rE['ph'],'pagibig_contribution'=>$rE['hdmf'],'other_deduction'=>$rE['others'],'addons'=>$rE['addons'],'regular_amount'=>$rE['regular'],'overtime_amount'=>$rE['ot'],'absent_amount'=>$rE['absent'],'total_deduction'=>$rE['total_deduction'],'gross_pay'=>$rE['gross'],'net_pay'=>$rE['net_pay'],'bgColor'=>'','salary_type'=>$rE['salary_type']);
endwhile;
#echo ';a';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Payroll Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
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
	.padRight{padding-right:5px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="90%" border="0" align="center">
		<tr>
			<td align="center">
				<?php 
				require_once('../class/print_header.php');
				print_header('Payroll');
				?>
			</td>
		</tr>
		<tr>
			<td valign='bottom'>&nbsp;</td>
		</tr>
		<tr>
			<td>
				<table border="0" width="90%" style="font-size:12px;">
					<tr>
						<td width="20%">Project / Department</td>
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
		<tr>
			<td height="500" valign="top">
				<table width="98%" id="tbtRec" border="1" style="font-size:12px;">
					<thead>
						<tr>
							<th width="17%" rowspan="2">NAME</th>
							<th width="5%" rowspan="2"><div align="center">Basic Sal</div></th>
							<?php if($totalOTPay){?><th width="5%" rowspan="2"><div align="center">OT</div></th><?php }?>
							<?php if($totalAddonsPay){?><th width="5%" rowspan="2"><div align="center">Add-ons</div></th><?php }?>
							<th width="7%" rowspan="2"><div align="center">GROSS Pay</div></th>
							<th colspan="6" align="center"><div align="center">DEDUCTIONS</div></th>
							<th width="7%" rowspan="2"><div align="center">NET Pay</div></th>
						</tr>
						<tr>
							<td width="5%"><div align="center">ABSENT</div></td>
							<td width="5%"><div align="center">SSS</div></td>
							<td width="5%"><div align="center">PH</div></td>
							<td width="5%"><div align="center">HMDF</div></td>
							<td width="5%"><div align="center">OTHERS</div></td>
							<td width="5%"><div align="center" style="font-size:10px;">TOTAL DEDUCTION</div></td>
						</tr>
					</thead>
					<tbody>
					<?php
					$countEmp=0;$totalDutyHours=0;
					foreach($arrMember as $memberID):
						$countEmp++;
						$pd = isset($arrPayrollList[$memberID]) ? $arrPayrollList[$memberID] : 0;
						if($pd){
					?>
						<tr>
							<td height="20px"><?php echo $countEmp++.'. '.$pd['name'];?></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['salary']);?></div></td>
							<?php if($totalOTPay){?><td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['overtime_amount']);?></div></td><?php }?>
							<?php if($totalAddonsPay){?><td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['addons']);?></div></td><?php }?>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['gross_pay']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['absent_amount']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['sss_contribution']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['philhealth_contribution']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['pagibig_contribution']);?></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['other_deduction']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($pd['total_deduction']);?></div></td>
							<td class="padRight"><div align="right"><?php echo '<strong>'.functions::formatMoney($pd['net_pay']).'</strong>';?></div></td>
						</tr>
					<?php
						}
						else{
					?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<?php if($totalOTPay){?><td>&nbsp;</td><?php }?>
							<?php if($totalAddonsPay){?><td>------</td><?php }?>
							<td>------</td>
							<td>------</td>
							<td>------</td>
							<td>------</td>
							<td>------</td>
							<td>------</td>
							<td>------</td>
						</tr>
					<?php }
					endforeach;?>
						<tr>
							<td colspan="12">&nbsp;</td>
						</tr>
					<?php if($countEmp > 20){?>
						<tr>
							<td colspan="2">&nbsp;</td>
							<?php if($totalOTPay){?><td><div align="right"><strong>OT</strong></div></td><?php }?>
							<?php if($totalAddonsPay){?><td><div align="right"><strong>Add-ons</strong></div></td><?php }?>
							<td><div align="right"><strong>GROSS Pay</strong></div></td>
							<td><div align="right"><strong>ABSENT</strong></div></td>
							<td><div align="right"><strong>SSS</strong></div></td>
							<td><div align="right"><strong>PH</strong></div></td>
							<td><div align="right"><strong>HMDF</strong></div></td>
							<td><div align="right"><strong>OTHERS</strong></div></td>
							<td><div align="right"><strong>TOTAL DEDUCTION</strong></div></td>
							<td><div align="right"><strong>NET Pay</strong></div></td>
						</tr>
					<?php }//if($countEmp > 20){?>
						<tr>
							<td colspan="2">&nbsp;</td>
							<?php if($totalOTPay){?><td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalOTPay);?></strong></div></td><?php }?>
							<?php if($totalAddonsPay){?><td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalAddonsPay);?></strong></div></td><?php }?>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalGrossPay);?></strong></div></td>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalAbsentPay);?></strong></div></td>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalSSSPay);?></strong></div></td>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalPHPay);?></strong></div></td>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalHMDFPay);?></strong></div></td>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalOthersPay);?></strong></div></td>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalAllDeduction);?></strong></div></td>
							<td class="padRight"><div align="right"><strong><?php echo functions::formatMoney($totalNetAmount);?></strong></div></td>
						</tr>
					<tbody>
				</table><br>
				<table border="0" width="100%" style="font-size:12px;">
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
            </td>
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
	window.location="payroll_view.php?eatid=<?php echo functions::encode($eatid)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>