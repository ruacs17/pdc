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
$worker_type = $rEatID['payroll_type'];
$eas_name = $rEatID['note'];
$payroll_no = $rEatID['payroll_no'];
$att_year = $rEatID['att_year'];
$is_project = $db->getValue('project','project',array('proj_id'=>$p_id));

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
	functions::sendTo('payroll_view_labor.php?eatid='.functions::encode($eatid));
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

$attendance_ready = $db->getValue('emp_attendance','attendance_ready',array('eat_id'=>$eatid));
if($attendance_ready==0)
	die('Please confirm the attendance summary preview!');

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
		$confirmed=0;
		$_SESSION['notif_warning']='Payroll unconfirmed!';
	}
}

$arrMember = array();
$arrPayrollList = array();
$totalNetAmount=0;$totalGrossPay=0;$totalRegularPay=0;$totalOTPay=0;$totalAddonsPay=0;$totalAbsentPay=0;$totalSSSPay=0;$totalPHPay=0;$totalHMDFPay=0;$totalOthersPay=0;$totalAllDeduction=0;
$countUnassignedSalary=0;

if( $db->getValue('emp_payroll_detail','count(*)',array('eat_id'=>$eatid)) && $confirmed==1 ){
	$qEmps = $db->select('employee emp, emp_payroll_detail epd','epd.*,lname,fname,left(mname,1) as mid,emp_no',array('eat_id'=>$eatid),'AND epd.emp_id=emp.emp_id ORDER BY lname,fname');
	while($rE = $db->fetch_array($qEmps)):
		$arrMember[$rE['emp_id']]=1;
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
}
else{
	$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','eas_id.emp_id,eas_id.pay_ready',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id GROUP BY eas_id.emp_id,eas_id.pay_ready ORDER BY lname,fname');
	while( $rEmps = $db->fetch_array($qEmps)):
		$philhealth_contribution=0;$pagibig_contribution=0;$sss_contribution=0;$other_deduction=0;$empRegularAmount=0;$empOTAmount=0;$empUndertimeAmount=0;$empLateAmount=0;$empAbsentAmount=0;$empTotalDeduction=0;$empGrossPay=0;$empNetPay=0;$empAddons=0;

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

		$has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id));

		if($sal_type){

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
				else
					$other_deduction += $rD['adjustment_value'];
			endwhile;

			$empAddons = $db->getValue('payroll_adjustment','sum(adjustment_value)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_type'=>'addon','include'=>1));
			
			if($has_attendance){
				$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
				while($rA = $db->fetch_array($qEmpAtt)):
					$empRegularAmount += $rA['duty_amount'];
					$empOTAmount += $rA['ot_amount'];
					$empAbsentAmount += $rA['absent_amount'];
					$empUndertimeAmount += $rA['under_min'] * $rA['wage_minute'];
					$empLateAmount += $rA['late_min'] * $rA['wage_minute'];
				endwhile;
			}

			if($sal_type=='fixed'){
				$empGrossPay = $empRegularAmount + $empAddons + $empOTAmount + $empAbsentAmount;
			}
			else if($sal_type=='flexible'){
				$empRegularAmount = ($salary / 2);
				$empGrossPay = $empRegularAmount + $empAddons + $empOTAmount;
			}
			$empTotalDeduction += $sss_contribution;
			$empTotalDeduction += $pagibig_contribution;
			$empTotalDeduction += $philhealth_contribution;
			$empTotalDeduction += $other_deduction;
			$empTotalDeduction += $empAbsentAmount;
			$empNetPay = ($empGrossPay) - $empTotalDeduction;

			$totalGrossPay += $empGrossPay;
			$totalRegularPay += $empRegularAmount;
			$totalOTPay += $empOTAmount;
			$totalAddonsPay += $empAddons;
			$totalAbsentPay += $empAbsentAmount;
			$totalSSSPay += $sss_contribution;
			$totalPHPay += $philhealth_contribution;
			$totalHMDFPay += $pagibig_contribution;
			$totalOthersPay += $other_deduction;
			$totalAllDeduction += $empTotalDeduction;
			$totalNetAmount += $empNetPay;
		}
		else{
			$countUnassignedSalary++;
		}

		if( isset($_REQUEST['c']) ){
			if($confirmed==1){
				$db->insert('emp_payroll_detail',array('eat_id'=>$eatid,'emp_id'=>$emp_id,'salary'=>$salary,'gross'=>$empGrossPay,'regular'=>$empRegularAmount,'ot'=>$empOTAmount,'addons'=>$empAddons,'absent'=>$empAbsentAmount,'sss'=>$sss_contribution,'ph'=>$philhealth_contribution,'hdmf'=>$pagibig_contribution,'others'=>$other_deduction,'total_deduction'=>$empTotalDeduction,'net_pay'=>$empNetPay,'salary_type'=>$sal_type,'has_attendance'=>$has_attendance));
			}
			else
			$db->delete('emp_payroll_detail',array('eat_id'=>$eatid,'emp_id'=>$emp_id));
		}
		$bgColor = ($rEmps['pay_ready']==1) ? '' : 'bgcolor="#FBD490"';
		#$bgColor = ($has_attendance==1 && $db->getValue('emp_attendance_detail etd,emp_attendance_adjustment eat','count(*)',array('eat.emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat.eatd_id=etd.eatd_id AND eat.emp_id=etd.emp_id AND eta_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"') && $confirmed==0 ) ? 'bgcolor="#f5ae00"' : '';
		#$bgColor = ($has_attendance==1 && $db->getValue('emp_attendance_detail etd,emp_attendance_adjustment eat','count(*)',array('eat.emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat.eatd_id=etd.eatd_id AND eat.emp_id=etd.emp_id AND eta_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"') && $confirmed==0 ) ? 'bgcolor="#FBD490"' : '';
		$arrPayrollList[$emp_id] = array('emp_id'=>$emp_id,'emp_no'=>$emp_no,'name'=>$name,'salary'=>$salary,'sss_contribution'=>$sss_contribution,'philhealth_contribution'=>$philhealth_contribution,'pagibig_contribution'=>$pagibig_contribution,'other_deduction'=>$other_deduction,'addons'=>$empAddons,'regular_amount'=>$empRegularAmount,'overtime_amount'=>$empOTAmount,'absent_amount'=>$empAbsentAmount,'total_deduction'=>$empTotalDeduction,'gross_pay'=>$empGrossPay,'net_pay'=>$empNetPay,'bgColor'=>$bgColor,'salary_type'=>$sal_type);
	endwhile;
}

if( isset($_REQUEST['c']) ){
	functions::sendTo('?eatid='.functions::encode($eatid));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payroll View</title>
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
	<!-- end: Favicon -->
	<style>
	/* Style the header */
	.header {
		background: #CCC;
	}
	/* The sticky class is added to the header with JS when it reaches its scroll position */
	.sticky {
		position: fixed;
		top: 0;
		width: 97%
	}
	.hideit{
		display:none;
	}
	.brdrNone{
		border:none;
	}
	.padright{padding-right: 5px;}
	.padleft{padding-left: 5px;}
	</style>
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
.table-wrapper thead tr:nth-child(2) th { background: #DDD;position: sticky; top: 20px; }
</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white th"></i><span class="break"></span>PAYROLL PREVIEW</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="right">
					<?php if($confirmed){ ?>
					<a id="prPrint" title="Print Payroll" data-rel="tooltip" href="payroll_print.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;
					<?php }else{ ?>
					<a id="prPrint" title="Cannot print unconfirm payroll" data-rel="tooltip" href="#" class="btn"><i class="halflings-icon white print"></i></a>&nbsp;
					<?php } ?>
					<?php if($confirmed==0){?>
					<a id="prReCalc" title="Recalculate Payroll" href="?eatid=<?php echo functions::encode($eatid)?>&pr=t" class="btn btn-info"><i class="halflings-icon white refresh"></i></a>&nbsp;
					<a id="icnSignatory" class="btn btn-info thickbox" title="Manage Signatory" data-rel="tooltip" onclick="showThis(this.id,'attendance_report_signatory.php?eatid=<?php echo functions::encode($eatid);?>','Attendance Detail')"><i class="halflings-icon white user"></i></a>&nbsp;
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
							<?php if($countUnassignedSalary==0){ ?>
							<label class="checkbox inline"><input type="checkbox" name="chkConf" id="chkConf" value="<?php echo functions::encode($totalNetAmount);?>" onClick="stat(this.value);" <?php if($confirmed){echo 'checked';}elseif( $db->getValue('emp_attendance_personnel','count(*)',array('pay_ready'=>0,'eat_id'=>$eatid)) ){echo 'disabled';}?>> <?php echo ($confirmed) ? 'Confirmed' : 'Unconfirmed';?></label>
							<?php }else{echo '<strong>Cannot Confirm:</strong> Unidentified Salary Type Detected!';} ?>
						</td>
					</tr>
				</table>
				<div class="table-wrapper">
				<table id="tblist" width="100%" border="1" class="tablea table-hover" style="font-size:12px">
					<thead>
						<tr>
							<th width="15%" rowspan="2">NAME</th>
							<th width="5%" rowspan="2"><div align="center">Basic Pay</div></th>
							<th width="5%" rowspan="2"><div align="center">Regular Pay</div></th>
							<th width="5%" rowspan="2"><div align="center">OT</div></th>
							<th width="5%" rowspan="2"><div align="center">Add-ons</div></th>
							<th width="7%" rowspan="2"><div align="center">GROSS Pay</div></th>
							<th colspan="6" align="center"><div align="center">DEDUCTIONS</div></th>
							<th width="7%" rowspan="2"><div align="center">NET Pay</div></th>
							<th width="8%" rowspan="2"><div align="center"><?php if($confirmed==0){?><a href="#" onClick="statAll('1')">Verify All</a>  | <a href="#" onClick="statAll('2')">Unverify All</a><?php }else{echo 'Verified';} ?></div></th>
						</tr>
						<tr>
							<th width="5%"><div align="center">ABSENT</div></th>
							<th width="5%"><div align="center">SSS</div></th>
							<th width="5%"><div align="center">PH</div></th>
							<th width="5%"><div align="center">HMDF</div></th>
							<th width="5%"><div align="center">OTHERS</div></th>
							<th width="5%"><div align="center">TOTAL DEDUCTION</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$countEmp=0;$totalDutyHours=0;
						foreach($arrMember as $memberID => $statReady):
							$countEmp++;
							$refresh = ($confirmed==1 || $statReady==1) ? '1' : '';

							$pd = isset($arrPayrollList[$memberID]) ? $arrPayrollList[$memberID] : 0;
							if($pd){
						?>
						<tr id="rw<?php echo $memberID?>" <?php echo $pd['bgColor']?>>
							<td height="30px" class="padleft"><a id="vw<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Payroll Detail" data-rel="tooltip" onclick="showThis(this.id,'payroll_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>','Attendance Detail','1')"><?php echo $countEmp.'. '.$pd['name'];?></a></td>
							<?php if($pd['salary_type']){ ?>
							<td class="padright"><div align="right"><?php echo functions::formatMoney($pd['salary']);?></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['regular_amount'],'~');?>"><?php echo functions::formatMoney($pd['regular_amount']);?></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['overtime_amount'],'~');?>"><?php echo functions::formatMoney($pd['overtime_amount']);?></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['addons'],'~');?>"><a id="addons<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Add-ons" data-rel="tooltip" onclick="showThis(this.id,'payroll_addon_manage.php?eatid=<?php echo functions::encode($eatid);?>&eid=<?php echo functions::encode($pd['emp_id']);?>&pr=pr','Add-ons Detail',<?php echo $refresh ?>)"><?php echo functions::formatMoney($pd['addons']);?></a></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['gross_pay'],'~');?>"><?php echo functions::formatMoney($pd['gross_pay']);?></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['absent_amount'],'~');?>"><a id="vwAbsnt<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" onclick="showThis(this.id,'payroll_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>&absntVw=1','Absent Detail','1')"><?php echo functions::formatMoney($pd['absent_amount']);?></a></div></td>
							<td class="padright">
								<?php $pa_id = $db->getValue('payroll_adjustment','pa_id',array('emp_id'=>$pd['emp_id'],'eat_id'=>$eatid,'adjustment_name'=>'SSS'));?>
								<div align="right" title="<?php echo functions::formatMoney($pd['sss_contribution'],'~');?>"><a id="ss<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Deduction" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_manage_benefit.php?eatid=<?php echo functions::encode($eatid);?>&eid=<?php echo functions::encode($pd['emp_id']);?>&edtID=<?php echo functions::encode($pa_id);?>&pr=pr','Contribution Detail',<?php echo $refresh ?>)"><?php echo functions::formatMoney($pd['sss_contribution']);?></a></div>
							</td>
							<td class="padright">
								<?php $pa_id_ph = $db->getValue('payroll_adjustment','pa_id',array('emp_id'=>$pd['emp_id'],'eat_id'=>$eatid,'adjustment_name'=>'Philhealth'));?>
								<div align="right" title="<?php echo functions::formatMoney($pd['philhealth_contribution'],'~');?>"><a id="ph<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Deduction" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_manage_benefit.php?eatid=<?php echo functions::encode($eatid);?>&eid=<?php echo functions::encode($pd['emp_id']);?>&edtID=<?php echo functions::encode($pa_id_ph);?>&pr=pr','Contribution Detail',<?php echo $refresh ?>)"><?php echo functions::formatMoney($pd['philhealth_contribution']);?></a></div>
							</td>
							<td class="padright">
								<?php $pa_id_pagibig = $db->getValue('payroll_adjustment','pa_id',array('emp_id'=>$pd['emp_id'],'eat_id'=>$eatid,'adjustment_name'=>'Pagibig'));?>
								<div align="right" title="<?php echo functions::formatMoney($pd['pagibig_contribution'],'~');?>"><a id="pg<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Deduction" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_manage_benefit.php?eatid=<?php echo functions::encode($eatid);?>&eid=<?php echo functions::encode($pd['emp_id']);?>&edtID=<?php echo functions::encode($pa_id_pagibig);?>&pr=pr','Contribution Detail',<?php echo $refresh ?>)"><?php echo functions::formatMoney($pd['pagibig_contribution']);?></a></div>
							</td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['other_deduction'],'~');?>"><a id="deduct<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Other Deduction" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_manage_other.php?eatid=<?php echo functions::encode($eatid);?>&eid=<?php echo functions::encode($pd['emp_id']);?>&pr=pr','Deduction Detail',<?php echo $refresh ?>)"><?php echo functions::formatMoney($pd['other_deduction']);?></a></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['total_deduction'],'~');?>"><?php echo functions::formatMoney($pd['total_deduction']);?></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($pd['net_pay'],'~');?>"><?php echo '<strong>'.functions::formatMoney($pd['net_pay']).'</strong>';?></div></td>
							<td><div align="center"><input type="checkbox" class="chkDel" name="chkDel[<?php echo $memberID; ?>]" id="chkDel[<?php echo $memberID; ?>]" value="<?php echo functions::encode($memberID); ?>" <?php if($confirmed){echo 'disabled';} ?> onClick="statIndi(this.value)" <?php echo ($statReady) ? 'checked':''; ?>></div></td>
							<?php }else{ ?>
							<td colspan="13"><div align="left">--Unidentified Salary Type--</div></td>
							<?php } ?>
						</tr>
						<?php
							}
							else{
						?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
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
							<td colspan="14">&nbsp;</td>
						</tr>
						<?php if($countEmp > 20){?>
						<tr>
							<td colspan="2" rowspan="2">&nbsp;</td>
							<td class="padright"><div align="center"><strong>Regular Pay</strong></div></td>
							<td class="padright"><div align="center"><strong>OT</strong></div></td>
							<td class="padright"><div align="center"><strong>Add-ons</strong></div></td>
							<td class="padright"><div align="center"><strong>GROSS Pay</strong></div></td>
							<td class="padright"><div align="center"><strong>ABSENT</strong></div></td>
							<td class="padright"><div align="center"><strong>SSS</strong></div></td>
							<td class="padright"><div align="center"><strong>PH</strong></div></td>
							<td class="padright"><div align="center"><strong>HMDF</strong></div></td>
							<td class="padright"><div align="center"><strong>OTHERS</strong></div></td>
							<td class="padright"><div align="center"><strong>TOTAL DEDUCTION</strong></div></td>
							<td class="padright"><div align="center"><strong>NET Pay</strong></div></td>
							<td class="padright"><div align="center">&nbsp;</div></td>
						</tr>
						<?php }//if($countEmp > 20){?>
						<tr>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalRegularPay,'~');?>"><strong><?php echo functions::formatMoney($totalRegularPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalOTPay,'~');?>"><strong><?php echo functions::formatMoney($totalOTPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalAddonsPay,'~');?>"><strong><?php echo functions::formatMoney($totalAddonsPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalGrossPay,'~');?>"><strong><?php echo functions::formatMoney($totalGrossPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalAbsentPay,'~');?>"><strong><?php echo functions::formatMoney($totalAbsentPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalSSSPay,'~');?>"><strong><?php echo functions::formatMoney($totalSSSPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalPHPay,'~');?>"><strong><?php echo functions::formatMoney($totalPHPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalHMDFPay,'~');?>"><strong><?php echo functions::formatMoney($totalHMDFPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalOthersPay,'~');?>"><strong><?php echo functions::formatMoney($totalOthersPay);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalAllDeduction,'~');?>"><strong><?php echo functions::formatMoney($totalAllDeduction);?></strong></div></td>
							<td class="padright"><div align="right" title="<?php echo functions::formatMoney($totalNetAmount,'~');?>"><strong><?php echo functions::formatMoney($totalNetAmount);?></strong></div></td>
							<td class="padright"><div align="right">&nbsp;</div></td>
						</tr>
					</tbody>
				</table><br><br>
				</div>
				<table border="0" width="100%" style="font-size:12px;">
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
<?php #$db->update('emp_attendance',array('amount'=>$totalNetAmount),array('eat_id'=>$eatid));?>
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
<script type="text/javascript">document.getElementById("spinner").style.display = "none";//$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
function stat(v){
	<?php if($confirmed==0){?>
		if(confirm('Do you want to confirmed this payroll?')){
			document.getElementById("spinner").style.display = "block";
			window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&c="+v;
		}
		else{
			document.getElementById("chkConf").checked = false;
		}
	<?php }else{?>
		if(confirm('Do you want to unconfirmed this payroll?')){
			document.getElementById("spinner").style.display = "block";
			window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&c="+v;
		}
		else{
			document.getElementById("chkConf").checked = true;
		}
	<?php } ?>
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
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;
sticky = 280
// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction() {
	if (window.pageYOffset > sticky) {
		header.classList.add("sticky");
		header.classList.remove("hideit");
	}else{
		header.classList.remove("sticky");
		header.classList.add("hideit");
	}
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
