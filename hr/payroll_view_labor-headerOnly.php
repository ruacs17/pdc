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
$_SESSION['notif_id']=$eatid;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$p_id = $rEatID['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $rEatID['confirmed'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$eas_name = $rEatID['note'];
$worker_type = $rEatID['payroll_type'];
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
if($attendance_ready==0)
	die('Please confirm the attendance summary preview!');
if($worker_type=='admin'){
	functions::sendTo('payroll_view.php?eatid='.functions::encode($eatid));
	die();
}
if($statall && $eatid){
	if($statall==1){
		$db->update('emp_attendance_personnel',array('pay_ready'=>1),array('eat_id'=>$eatid));
		$_SESSION['notif_success']='All Employees payroll verified!';
	}
	else if($statall==2){
		$db->update('emp_attendance_personnel',array('pay_ready'=>0),array('eat_id'=>$eatid));
		$_SESSION['notif_warning']='All Employees payroll unverified!';
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}
if($emp_confirm && $eatid){
	if( $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$emp_confirm,'eat_id'=>$eatid,'pay_ready'=>1)) ){
		$_SESSION['notif_warning']='Employee payroll unverified!';
		$db->update('emp_attendance_personnel',array('pay_ready'=>0),array('emp_id'=>$emp_confirm,'eat_id'=>$eatid));
	}
	else{
		$_SESSION['notif_success']='Employee payroll confirmed!';
		$db->update('emp_attendance_personnel',array('pay_ready'=>1),array('emp_id'=>$emp_confirm,'eat_id'=>$eatid));
	}

	$_SESSION['notif_indi_id']=$emp_confirm;
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
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



$recalculate = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? $_REQUEST['pr'] : 0;
if($recalculate){
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo 'Please wait while payroll is still recalculating........<br>';
	echo '<div id="spinner"></div>';
	functions::sendTo('attendance_report_calc.php?eatid='.functions::encode($eatid).'&pr=t');
	die();
}

if( isset($_REQUEST['c']) ){
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo 'Please wait while payroll is processing........<br>';
	echo '<div id="spinner"></div>';
	$payroll_value = functions::decode($_REQUEST['c']);
	if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$eatid,'confirmed'=>0)) ){
		$db->update('emp_attendance',array('confirmed'=>1,'amount'=>$payroll_value),array('eat_id'=>$eatid));
		$confirmed=1;
		$_SESSION['notif_success']='Payroll successfully confirmed!';
		$db->delete('emp_payroll_detail',array('eat_id'=>$eatid));
	}
	else{
		$db->update('emp_attendance',array('confirmed'=>0,'amount'=>$payroll_value),array('eat_id'=>$eatid));
		$_SESSION['notif_warning']='Payroll unconfirmed!';
		$confirmed=0;
	}
}

$arrMember = array();
$arrEmpAttendance = array();
$arrPayrollList = array();
$totalNetAmount=0;$totalGrossPay=0;$totalRegularPay=0;$totalOTPay=0;$totalAddonsPay=0;$totalSSSPay=0;$totalPHPay=0;$totalHMDFPay=0;$totalOthersPay=0;$totalAllDeduction=0;
$empRegularAmount=0;$empOTAmount=0;$empAbsentAmount=0;$empTotalDeduction=0;$empGrossPay=0;$empNetPay=0;$empAddons=0;$empTotalDutyMin=0;$empTotalOTMin=0;
$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','eas_id.emp_id,eas_id.pay_ready',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id GROUP BY eas_id.emp_id,eas_id.pay_ready ORDER BY lname,fname');
#$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','DISTINCT eas_id.emp_id',array('eat_id'=>$eatid,'emp.emp_id'=>'6'),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname');
while( $rEmps = $db->fetch_array($qEmps)):
	$other_deduction=0;$philhealth_contribution=0;$pagibig_contribution=0;$sss_contribution=0;$empRegularAmount=0;$empOTAmount=0;$empAbsentAmount=0;$empTotalDeduction=0;$empGrossPay=0;$empNetPay=0;$empAddons=0;$empTotalDutyMin=0;$empTotalOTMin=0;

	$arrMember[$rEmps['emp_id']]=$rEmps['pay_ready'];
	$emp_id = $rEmps['emp_id'];
	$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$emp_id));
	$name = $db->getValue('employee','concat(lname,", ",fname," ",left(mname,1),".")',array('emp_id'=>$emp_id));

	$qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'AND es_date <= "'.$date_end.'" ORDER BY es_date DESC, es_id DESC');
	$rSal = $db->fetch_array($qSal);
	$salary = (isset($rSal['es_salary']) && is_numeric($rSal['es_salary']) ) ? $rSal['es_salary'] : 0;
	$wage_day = (isset($rSal['es_daily']) && is_numeric($rSal['es_daily']) ) ? $rSal['es_daily'] : 0;
	$wage_hour = (isset($rSal['es_hourly']) && is_numeric($rSal['es_hourly']) ) ? $rSal['es_hourly'] : 0;
	$wage_minute = (isset($rSal['es_minute']) && is_numeric($rSal['es_minute']) ) ? $rSal['es_minute'] : 0;
	$sal_type = (isset($rSal['es_type']) ) ? $rSal['es_type'] : '';

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
	$es_type = $rSal['es_type'];
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	$countPos=0;$position='';
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= ($countPos>0) ? '* '.$rPos['pos_name'] : $rPos['pos_name'];
		$countPos++;
	endwhile;

	$sal_daily=0;$sal_hourly=0;$sal_minute=0;
	if($es_type){

		$sal_daily = $rSal['es_daily'];
		$sal_hourly = $rSal['es_hourly'];
		$sal_minute = $rSal['es_minute'];

		$empAddons = $db->getValue('payroll_adjustment','sum(adjustment_value)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_type'=>'addon','include'=>1));

		$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
		while($rA = $db->fetch_array($qEmpAtt)):
			$emp_regular_duty_hour = ($rA['duty_min']) ? $rA['duty_min']/60 : 0;

			$arrEmpAttendance[$emp_id][$rA['eat_date']]=array('duty_hour'=>$emp_regular_duty_hour,'duty_min'=>$rA['duty_min'],'duty_amount'=>round($rA['duty_amount'],2),'ot_min'=>$rA['ot_min'],'ot_amount'=>round($rA['ot_amount'],2));
			$empRegularAmount += $rA['duty_amount'];
			$empOTAmount += $rA['ot_amount'];
			$empAbsentAmount += $rA['absent_amount'];
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

	if( isset($_REQUEST['c']) ){
		if($confirmed==1){
			$db->insert('emp_payroll_detail',array('eat_id'=>$eatid,'emp_id'=>$emp_id,'salary'=>$salary,'gross'=>$empGrossPay,'regular'=>$empRegularAmount,'ot'=>$empOTAmount,'addons'=>$empAddons,'absent'=>$empAbsentAmount,'sss'=>$sss_contribution,'ph'=>$philhealth_contribution,'hdmf'=>$pagibig_contribution,'others'=>$other_deduction,'total_deduction'=>($empTotalDeduction+$empAbsentAmount),'net_pay'=>$empNetPay,'salary_type'=>$es_type));
		}
		else
			$db->delete('emp_payroll_detail',array('eat_id'=>$eatid,'emp_id'=>$emp_id));
	}
	$has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id));
	$bgColor = ($rEmps['pay_ready']==1) ? '' : 'bgcolor="#FBD490"';
	#$bgColor = ($has_attendance==1 && $db->getValue('emp_attendance_detail etd,emp_attendance_adjustment eat','count(*)',array('eat.emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat.eatd_id=etd.eatd_id AND eat.emp_id=etd.emp_id AND eta_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"') && $confirmed==0 ) ? 'bgcolor="#FBD490"' : '';

	$arrPayrollList[$emp_id] = array('emp_id'=>$emp_id,'emp_no'=>$emp_no,'name'=>$name,'position'=>$position,'salary'=>$salary,'salary_daily'=>$sal_daily,'salary_hourly'=>$sal_hourly,'salary_minute'=>$sal_minute,'sss_contribution'=>$sss_contribution,'philhealth_contribution'=>$philhealth_contribution,'pagibig_contribution'=>$pagibig_contribution,'other_deduction'=>$other_deduction,'addons'=>$empAddons,
        'regular_amount'=>$empRegularAmount,'duty_min'=>$empTotalDutyMin,'overtime_amount'=>$empOTAmount,'ot_min'=>$empTotalOTMin,'absent_amount'=>$empAbsentAmount,'total_deduction'=>$empTotalDeduction,'gross_pay'=>$empGrossPay,'net_pay'=>$empNetPay,'bgColor'=>$bgColor);

endwhile;

if( isset($_REQUEST['c']) ){
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payroll View - Labor</title>
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
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	<style type="text/css">
	.padright{padding-right: 5px;}
	.padleft{padding-left: 5px;}
	</style>
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
.table-wrapper thead tr:nth-child(2) th { background: #DDD;position: sticky; top: 20px;}
.table-wrapper thead tr:nth-child(3) th { background: #DDD;position: sticky; top: 40px; }
.table-wrapper thead tr:nth-child(4) th { background: #DDD;position: sticky; top: 60px; }
</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white th"></i><span class="break"></span>PAYROLL PREVIEW (LABOR)</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="right">
					<?php if($confirmed==1){?>
					<a id="prPrint" title="Print Payroll" href="payroll_print_labor.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;
					<?php }else{ ?>
					<a id="prPrint" title="Cannot print unconfirm payroll" data-rel="tooltip" href="#" class="btn"><i class="halflings-icon white print"></i></a>&nbsp;
					<?php } ?>
					<?php if($confirmed==0){?>
					<a id="prRecalc" title="Recalculate Payroll" data-rel="tooltip" href="?eatid=<?php echo functions::encode($eatid)?>&pr=t" class="btn btn-info"><i class="halflings-icon white refresh"></i></a>&nbsp;
					<a id="icnSignatory" class="btn btn-info thickbox" title="Manage Signatory" data-rel="tooltip" data-rel="tooltip" onclick="showThis(this.id,'attendance_report_signatory.php?eatid=<?php echo functions::encode($eatid);?>','Attendance Detail')"><i class="halflings-icon white user"></i></a>&nbsp;
					<?php }?>
				</div>
				<table border="0" width="90%">
					<tr>
						<td width="15%" height="30px">Project / Department</td>
						<td><strong><?php echo $proj_name;?></strong></td>
					</tr>
					<?php if($eas_name){?>
					<tr>
						<td height="30px">Description</td>
						<td><strong><?php echo $eas_name; echo ($worker_type) ? ' ('.$worker_type.')' : '';?></strong></td>
					</tr>
					<?php }?>
					<tr>
						<td height="30px">Payroll No.</td>
						<td><strong><?php echo $payroll_no;?></strong></td>
					</tr>
					<tr>
						<td height="30px">Period Covered</td>
						<td><strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong></td>
					</tr>
					<tr>
						<td height="30px">Confirm Status</td>
						<td>
							<label class="checkbox inline"><input type="checkbox" name="chkConf" id="chkConf" value="<?php echo functions::encode($totalNetAmount);?>" onClick="stat(this.value);document.getElementById('spinner').style.display = 'block';" <?php if($confirmed){echo 'checked';}elseif( $db->getValue('emp_attendance_personnel','count(*)',array('pay_ready'=>0,'eat_id'=>$eatid)) ){echo 'disabled';}?>> <?php echo ($confirmed) ? 'Confirmed' : 'Unconfirmed';?></label>
						</td>
					</tr>
				</table>
				<div class="table-wrapper">
				<table width="100%" border="1" class="tablea table-hover" style="font-size:11px">
					<thead>
						<tr style="background: #CCC">
							<th width="17%" rowspan="4" scope="row">Name</th>
							<th width="10%" rowspan="4" scope="row">Position</th>
							<th width="5%" rowspan="4" scope="row">Rate / Day</th>
							<th width="5%" rowspan="4" scope="row">Rate / Hour</th>
							<th colspan="<?php echo ($daysCount * 2)?>" scope="row"><div align="center"><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></div></th>
							<th width="5%" rowspan="4" scope="row">No. of Hours</th>
							<th width="7%" rowspan="4" scope="row">Amount Hours</th>
							<th width="7%" rowspan="4" scope="row">No. of Hours OT</th>
							<th width="7%" rowspan="4" scope="row">Amount OT</th>
							<th width="5%" rowspan="4" scope="row">Deductions</th>
							<th width="10%" rowspan="4" scope="row">Net</th>
							<th width="8%" rowspan="4"><div align="center"><?php if($confirmed==0){?><a href="#" onClick="statAll('1')">Verify All</a>  | <a href="#" onClick="statAll('2')">Unverify All</a><?php }else{echo 'Verified';} ?></div></th>
						</tr>
						<tr style="background: #CCC">
							<?php foreach($arrDateInfo as $dteInfo):?>
							<th colspan="2" scope="row"><div align="center"><?php echo $dteInfo['dayDate']?></div></th>
							<?php endforeach;?>
						</tr>
						<tr style="background: #CCC">
							<?php foreach($arrDateInfo as $dteInfo):?>
							<th colspan="2" scope="row"><div align="center"><?php echo $dteInfo['dayName']?></div></th>
							<?php endforeach;?>
						</tr>
						<tr style="background: #CCC">
							<?php foreach($arrDateInfo as $dteInfo):?>
							<th width="4%" scope="row"><div align="center">Hour</div></th>
							<th width="4%"><div align="center">OT</div></th>
							<?php endforeach;?>
						</tr>
					</thead>
					<tbody>
					<?php
					$countEmp=0;
					$totalDutyHours=0;$totalRegDutyAmount=0;
					foreach($arrMember as $memberID => $statReady):
						$countEmp++;
						$totalRegDutyAmount=0;
						$refresh = ($confirmed==1 || $statReady==1) ? '1' : '';
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
						#$totalDutyHours = ($pd['overtime_hours'] + $pd['regular_hours']);
						#if($pd){
					?>
					<tr id="rw<?php echo $memberID?>" <?php echo $pd['bgColor']?>>
						<td height="30px" class="padleft">
							<a id="vw<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Payroll Detail" data-rel="tooltip" onclick="showThis(this.id,'payroll_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>','Attendance Detail','1')"><?php echo $countEmp.'. '.$pd['name'];#echo $countEmp.'. '.$pd['emp_id'].' - '.$pd['name'];?></a>
						</td>
						<td class="padleft"><?php echo $pd['position'];?></td>
						<td><div align="center" <?php if(empty($sal_day)){echo 'title="Salary Undefined"';}else{echo 'title="'.functions::formatMoney($sal_day,"~").'"';} ?>><?php echo ($sal_day) ? functions::formatMoney($sal_day) : '??';?></div></td>
						<td><div align="center" title="<?php echo functions::formatMoney($sal_hr,'~'); ?>"><?php echo functions::formatMoney($sal_hr);?></div></td>
						<?php
						foreach($arrDateInfo as $dteInfo):
							$dty_hrs = (isset($arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['duty_hour'])) ? $arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['duty_hour'] : 0;
							$dty_min = (isset($arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['duty_min'])) ? $arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['duty_min'] : 0;
							$ot_min = (isset($arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['ot_min'])) ? $arrEmpAttendance[$pd['emp_id']][$dteInfo['dayFullDate']]['ot_min'] : 0;						
						?>
						<td><div align="center" title="<?php echo ($dty_min) ? ($dty_min / 60) : ''; ?>"><?php echo ($dty_hrs) ? number_format($dty_hrs,2) : '-';?></div></td>
						<td><div align="center" title="<?php echo ($ot_min) ? ($ot_min / 60) : ''; ?>"><?php echo $ot_min = ($ot_min) ? number_format(($ot_min / 60),2) : '';?></div></td>
						<?php endforeach;?>
						<td><div align="center" title="<?php echo ($duty_mins) ? ($duty_mins / 60) : "";?>"><?php echo ($duty_mins) ? number_format(($duty_mins / 60),2) : 0;?></div></td>
						<td><div align="center" title="<?php echo functions::formatMoney($duty_mins_amount,'~');?>"><?php echo functions::formatMoney($duty_mins_amount);?></div></td>
						<td><div align="center" title="<?php echo ($ot_mins) ? ($ot_mins / 60) : '';?>"><?php echo ($ot_mins) ? number_format(($ot_mins / 60),2) : '';?></div></td>
						<td><div align="center" title="<?php echo ($ot_mins_amount) ? functions::formatMoney($ot_mins_amount,'~') : '';?>"><?php echo ($ot_mins_amount) ? functions::formatMoney($ot_mins_amount) : '';?></div></td>
						<td><div align="center" title="<?php echo functions::formatMoney($emp_deduction,'~'); ?>"><a id="deduct<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Other Deduction" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_manage_labor.php?eatid=<?php echo functions::encode($eatid);?>&eid=<?php echo functions::encode($pd['emp_id']);?>&absntAmnt=<?php echo functions::encode($pd['absent_amount'])?>&pr=pr','Contribution Detail',<?php echo $refresh ?>)"><?php echo ($emp_deduction) ? functions::formatMoney($emp_deduction) : '0';?></a></div></td>
						<td><div align="center" title="<?php echo functions::formatMoney($emp_net_pay,'~') ?>"><?php echo '<strong>'.functions::formatMoney($emp_net_pay).'</strong>';?></div></td>
						<td><div align="center"><input type="checkbox" class="chkDel" name="chkDel[<?php echo $memberID; ?>]" id="chkDel[<?php echo $memberID; ?>]" value="<?php echo functions::encode($memberID); ?>"  onClick="statIndi(this.value)" <?php if($confirmed){echo 'disabled';} ?> <?php echo ($statReady) ? 'checked':''; ?>></div></td>
					</tr>
					<?php #}
					endforeach;?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td colspan="<?php echo ($daysCount * 2)?>"></td>
						<td></td>
						<td><div align="center" title="<?php echo functions::formatMoney($totalRegularPay,'~'); ?>"><strong><?php echo functions::formatMoney($totalRegularPay);?></strong></div></td>
						<td></td>
						<td><div align="center" title="<?php echo functions::formatMoney($totalOTPay,'~'); ?>"><strong><?php echo functions::formatMoney($totalOTPay);?></strong></div></td>
						<td><div align="center" title="<?php echo functions::formatMoney($totalAllDeduction,'~'); ?>"><strong><?php echo functions::formatMoney($totalAllDeduction);?></strong></div></td>
						<td><div align="center" title="<?php echo functions::formatMoney($totalNetAmount,'~'); ?>"><strong><?php echo functions::formatMoney($totalNetAmount);?></strong></div></td>
						<td></td>
					</tr>
					</tbody>
				</table>
				</div><br><br>
				<table border="0" width="100%">
					<tr>
						<td align="center">Prepared By</td>
						<td align="center">Checked By</td>
						<td align="center">Received By</td>
						<td align="center">Approved By</td>
					</tr>
					<tr>
						<td height="60px" valign="bottom" align="center"><strong><u>&nbsp;<?php echo $prepared_by?>&nbsp;</u></strong></td>
						<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $checked_by?>&nbsp;</u></strong></td>
						<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $received_by?>&nbsp;</u></strong></td>
						<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $approved_by?>&nbsp;</u></strong></td>
					</tr>
					<tr>
						<td align="center" style="font-size:10px"><i><?php echo $prepared_position?></i></td>
						<td align="center" style="font-size:10px"><i><?php echo $checked_position?></i></td>
						<td align="center" style="font-size:10px"><i><?php echo $received_position?></i></td>
						<td align="center" style="font-size:10px"><i><?php echo $approved_position?></i></td>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">document.getElementById("spinner").style.display = "none";//</script>
<script>
function stat(v){
	window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&c="+v;
}
function statIndi(v){
	window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&cempid="+v;
}
function statAll(v){
	var msg = (v==1) ? 'Do you want to verify all?' : 'Do you want to unverify all?';
	if(confirm(msg))
		window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&statall="+v;
	else
		return false;
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
<?php if(isset($_SESSION['notif_indi_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_indi_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_indi_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_indi_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	window.setTimeout(function(){$('#rw<?php echo $_SESSION['notif_indi_id'] ?>').css('border','1px solid black');}, 5000);
});
</script>
<?php unset($_SESSION['notif_indi_id']);} ?>
<!-- end: JavaScript-->
</body>
</html>