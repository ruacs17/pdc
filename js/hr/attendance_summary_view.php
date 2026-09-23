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
if($eatid)
	$_SESSION['notif_id']=$eatid;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$p_id = $rEatID['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $rEatID['confirmed'];
$attendance_ready = $rEatID['attendance_ready'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$worker_type = $rEatID['payroll_type'];
$eas_name = $rEatID['note'];
$payroll_no = $rEatID['payroll_no'];
$att_year = $rEatID['att_year'];

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
if($statall && $eatid){
	if($statall==1){
		$db->update('emp_attendance_personnel',array('att_ready'=>1),array('eat_id'=>$eatid));
		$_SESSION['notif_success']='All Employees attendance verified!';
	}
	else if($statall==2){
		$db->update('emp_attendance_personnel',array('att_ready'=>0),array('eat_id'=>$eatid));
		$_SESSION['notif_warning']='All Employees attendance unverified!';
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}
if($emp_confirm && $eatid){
	if( $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$emp_confirm,'eat_id'=>$eatid,'att_ready'=>1)) ){
		$_SESSION['notif_warning']='Employee attendance unverified!';
		$db->update('emp_attendance_personnel',array('att_ready'=>0),array('emp_id'=>$emp_confirm,'eat_id'=>$eatid));
	}
	else{
		$_SESSION['notif_success']='Employee attendance confirmed!';
		$db->update('emp_attendance_personnel',array('att_ready'=>1),array('emp_id'=>$emp_confirm,'eat_id'=>$eatid));
	}
	$_SESSION['notif_indi_id']=$emp_confirm;
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}
if( isset($_REQUEST['c']) ){
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo 'Please wait while attendance is still processing........<br>';
	echo '<div id="spinner"></div>';
	if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$eatid,'confirmed'=>0)) ){
		if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$eatid,'attendance_ready'=>0)) ){
			$db->update('emp_attendance',array('attendance_ready'=>1),array('eat_id'=>$eatid));
			$_SESSION['notif_success']='Attendance Successfully confirmed!';
			$attendance_ready=1;
			functions::sendTo('attendance_report_calc.php?eatid='.functions::encode($eatid));
			die();
		}
		else{
			$_SESSION['notif_warning']='Attendance reverted to Unconfirmed!';
			$db->update('emp_attendance',array('attendance_ready'=>0),array('eat_id'=>$eatid));
		}
	}
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
	die();
}

$recalculate = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? $_REQUEST['pr'] : 0;
if($recalculate){
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo 'Please wait while attendance is still processing........<br>';
	echo '<div id="spinner"></div>';
	$_SESSION['notif_success']='Attendance Updated!';
	functions::sendTo('attendance_report_upload_save.php?eatid='.functions::encode($eatid));
	die();
}

$totalRow = 0;
$content = array();
$arrAttendanceRecord = array();
$arrPayrollList = array();
$arrMember = array();
$qEmps = $db->select('emp_attendance_personnel eas_id, employee emp','eas_id.emp_id,eas_id.att_ready',array('eat_id'=>$eatid),'AND eas_id.emp_id=emp.emp_id GROUP BY eas_id.emp_id,eas_id.att_ready ORDER BY lname,fname');
while( $rEmps = $db->fetch_array($qEmps)):
	$arrMember[$rEmps['emp_id']]=$rEmps['att_ready'];
	$empRegDutyHours=0;$empOTDutyHours=0;$empUnderDutyHours=0;$empLateDutyHours=0;$empAbsentHours=0;
	$emp_id = $rEmps['emp_id'];
	$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$emp_id));
	$name = $db->getValue('employee','concat(lname,", ",fname," ",left(mname,1),".")',array('emp_id'=>$emp_id));

	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	$countPos=0;$position='';
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	$has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id));
	if( $has_attendance == 0 ){
		$dates = functions::date_diff($date_start,$date_end);
		if($dates){
			for($i=0;$i<=$dates;$i++):
				$succeeding_date = functions::AddDay($date_start,$i);
				$daysName = date('l', strtotime($succeeding_date));
				if($daysName == 'Saturday')
					$empRegDutyHours += 240; //4hr
				else if($daysName=='Sunday')
					;
				else
					$empRegDutyHours += 480; // 8hr
			endfor;
		}
	}
	else{
		$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_id'=>$eatid),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
		while($rA = $db->fetch_array($qEmpAtt)):    
			$amDutyHrs = ($rA['am_in_assign'] && $rA['am_out_assign']) ? functions::min_diff($rA['am_in_assign'],$rA['eat_date'],$rA['am_out_assign'],$rA['eat_date']) : 0;
			$pmDutyHrs = ($rA['pm_in_assign'] && $rA['pm_out_assign']) ? functions::min_diff($rA['pm_in_assign'],$rA['eat_date'],$rA['pm_out_assign'],$rA['eat_date']) : 0;
			$empRegDutyHours += $rA['duty_min'];
			$empOTDutyHours += $rA['ot_min'];
			$empUnderDutyHours += $rA['under_min'];
			$empLateDutyHours += $rA['late_min'];
		endwhile;
	}
	$bgColor = ($rEmps['att_ready']==1) ? '' : 'bgcolor="#FBD490"';
	#$bgColor = ($has_attendance==1 && $db->getValue('emp_attendance_adjustment eaa, emp_attendance_detail ead','count(*)',array('eaa.emp_id'=>$emp_id,'ead.eat_id'=>$eatid),'AND eaa.eatd_id=ead.eatd_id AND eta_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'"') && $attendance_ready==0 ) ? 'bgcolor="#f5ae00"' : '';
	$empAbsentHours = $empUnderDutyHours + $empLateDutyHours;
	$arrPayrollList[$emp_id] = array('emp_id'=>$emp_id,'emp_no'=>$emp_no,'name'=>$name,'position'=>$position,'regular_hours'=>$empRegDutyHours,'overtime_hours'=>$empOTDutyHours,'undertime_hours'=>$empUnderDutyHours,'late_hours'=>$empLateDutyHours,'absentHours'=>$empAbsentHours,'bgColor'=>$bgColor);
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Summary</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<?php if($attendance_ready==0){?>
			<div align="right">
				<a id="icnReupload" href="attendance_report_add_attlog.php?eatid=<?php echo functions::encode($eatid)?>&frm=1" class="btn btn-info" title="Upload Updated Att. Log"><i class="icon-upload-alt white upload-alt"></i></a>&nbsp;
				<a id="icnRefresh" href="?eatid=<?php echo functions::encode($eatid)?>&pr=t" class="btn btn-info" title="Re calculate attendance"><i class="halflings-icon white refresh"></i></a>&nbsp;
				<a id="icnSignatory" class="btn btn-info thickbox" title="Manage Signatory" data-rel="tooltip" onclick="showThis(this.id,'attendance_report_signatory.php?eatid=<?php echo functions::encode($eatid);?>','Attendance Detail')"><i class="halflings-icon white user"></i></a>&nbsp;
			</div>
			<?php }?>
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE SUMMARY PREVIEW</h2></div>
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
					<?php if($attendance_ready){?>
					<tr>
						<td height="30px">Payroll No.</td>
						<td><strong><?php echo $payroll_no;?></strong></td>
					</tr>
					<?php }?>
					<tr>
						<td height="30px">Period Covered</td>
						<td><strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong></td>
					</tr>
					<tr>
						<td height="30px">Confirm Status</td>
						<td>
							<label class="checkbox inline"><input type="checkbox" name="chkConf" id="chkConf" value="1" onClick="stat(this.value)" <?php if($attendance_ready)echo 'checked';?> <?php if($confirmed){echo 'disabled';}elseif( $db->getValue('emp_attendance_personnel','count(*)',array('att_ready'=>0,'eat_id'=>$eatid)) ){echo 'disabled';}?>> <?php echo ($attendance_ready) ? 'Confirmed' : 'Unconfirmed';?></label>
						</td>
					</tr>
				</table>
				<br><br>
				<table id="tblist" width="98%" border="0" class="table table-hover <?php if(!isset($_SESSION['notif_indi_id'])){echo 'table-bordered';} ?>" style="font-size:12px;">
					<thead>
					<tr style="background-color:#CCC">
						<th width="15%">NAME</th>
						<th width="10%"><div align="left">Position</div></th>
						<th width="10%"><div align="right">Rendered Days (Hours)</div></th>
						<th width="9%"><div align="right">Absent Days (Hours)</div></th>
						<th width="9%"><div align="right">Required Days (Hours)</div></th>
						<th width="9%"><div align="right">Overtime Hours</div></th>
						<th width="10%"><div align="right">Total Duty Hours</div></th>
						<th width="10%"><div align="center"><?php if($attendance_ready==0){?><a href="#" onClick="statAll('1')">Verify All</a>  | <a href="#" onClick="statAll('2')">Unverify All</a><?php }else{echo 'Verified';} ?></div></th>
					</tr>
					<tr class="header" id="myHeader">
						<th width="15%">NAME</th>
						<th width="10%"><div align="left">Position</div></th>
						<th width="10%"><div align="right">Rendered Days (Hours)</div></th>
						<th width="9%"><div align="right">Absent Days (Hours)</div></th>
						<th width="9%"><div align="right">Required Days (Hours)</div></th>
						<th width="9%"><div align="right">Overtime Hours</div></th>
						<th width="9%"><div align="right">Total Duty Hours</div></th>
						<th width="10%"><div align="center"><?php if($attendance_ready==0){?><a href="#" onClick="statAll('1')">Verify All</a>  | <a href="#" onClick="statAll('2')">Unverify All</a><?php }else{echo 'Verified';} ?></div></th>
					</tr>
					</thead>
					<tbody>
				<?php
				$countEmp=0;$totalDutyHours=0;
				foreach($arrMember as $memberID => $statReady):
					$countEmp++;

					$pd = isset($arrPayrollList[$memberID]) ? $arrPayrollList[$memberID] : 0;
					$totalDutyHours = ($pd['overtime_hours'] + $pd['regular_hours']);
					if($pd){
						$hrsPrDay = 480;
						$qStat = $db->select('emp_work_status','*',array('emp_id'=>$memberID),'ORDER BY ews_date DESC LIMIT 1');
						$rStat = $db->fetch_array($qStat);
						$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : 'Undefined Status';
						$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? 'Project Based' : '';
						#$wrkDate = (isset($rStat['ews_date'])) ? functions::datearr($rStat['ews_date']) : '-----';

						$has_travel = $db->getValue('travel_personnel tv, travel_order tro, travel_order_detail tod','count(*)',array('personnel_id'=>$pd['emp_id']),'AND travel_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" AND tro.to_id=tod.to_id AND tv.to_id=tro.to_id ORDER BY act_time_from');
						$has_travel = ($has_travel) ? '&nbsp;&nbsp;<i class="icon-truck" title="has travel"></i>' : '';
				?>
					<tr id="rw<?php echo $memberID?>" <?php echo $pd['bgColor']?>>
						<td height="30px"><a id="vw<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>&rw=<?php echo ($countEmp-1) ?>','Attendance Detail'<?php echo ($attendance_ready) ? ",'1'" : ''; ?>)"><?php echo $countEmp.'. '.$pd['name']; echo ($attendance_ready) ? '' : ' '.$has_travel;?></a></td>
						<td>
							<div align="left">
								<?php echo $pd['position'];?>
								<div><i>(<?php echo $wrkStat; echo ($projBased) ? ': '.$projBased : ''; ?>)</i></div>
							</div>
						</td>
						<td>
							<div align="right">
							<?php
							echo $regDays = ($pd['regular_hours']) ? number_format(($pd['regular_hours'] / $hrsPrDay),2) : '0';
							echo ($regDays > 1) ? ' day/s' : ' day';
							echo ($pd['regular_hours']) ? ' ('.functions::min_to_hour($pd['regular_hours']).')' : '';
							?>
							</div>
						</td>
						<td>
							<div align="right">
							<?php
							echo $absentDays = ($pd['absentHours']) ? number_format(($pd['absentHours'] / $hrsPrDay),2) : '0';
							echo ($absentDays > 1) ? ' day/s' : ' day';
							echo ($pd['absentHours']) ? ' ('.functions::min_to_hour($pd['absentHours']).')' : '';
							?>
							</div>
						</td>
						<td>
							<div align="right">
							<?php
							echo $requiredDays = ($pd['regular_hours'] || $pd['absentHours']) ? number_format(($pd['regular_hours'] + $pd['absentHours']) / $hrsPrDay,2) : '0';
							echo ($requiredDays > 1) ? ' day/s' : ' day';
							echo ($pd['regular_hours'] || $pd['absentHours']) ? ' ('.functions::min_to_hour($pd['regular_hours'] + $pd['absentHours']).')' : '';
							?>
							</div>
						</td>
						<td><div align="right"><?php echo ($pd['overtime_hours']) ? ' ('.functions::min_to_hour($pd['overtime_hours']).')' : '';?></div></td>
						<td>
							<div align="right">
							<?php
							echo ($totalDutyHours) ? number_format(($totalDutyHours / $hrsPrDay),2) : '0';
							echo ($totalDutyHours > 1) ? ' day/s' : ' day';
							echo ($totalDutyHours) ? ' ('.functions::min_to_hour($totalDutyHours).')' : '';
							?>
							</div>
						</td>
						<td><div align="center"><input type="checkbox" class="chkDel" name="chkDel[<?php echo $memberID; ?>]" id="chkDel[<?php echo $memberID; ?>]" value="<?php echo functions::encode($memberID); ?>" <?php if($attendance_ready){echo 'disabled';}?> onClick="statIndi(this.value)" <?php echo ($statReady) ? 'checked':''; ?>></div></td>
					</tr>
				<?php }else{?>
					<tr>
						<td height="30px"><?php echo $countEmp.'. '.$db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$memberID));?></td>
						<td><div align="right">-------</div></td>
						<td><div align="right">-------</div></td>
						<td><div align="right">-------</div></td>
						<td><div align="right">-------</div></td>
						<td><div align="right">-------</div></td>
						<td><div align="right">-------</div></td>
						<td><div align="right">-------</div></td>
					</tr>
				<?php }
				endforeach;?>
				</tbody>
				</table><br><br>
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
<?php if($countEmp==0){functions::sendTo('attendance_report_add_personnel.php?eatid='.functions::encode($eatid));} ?>
<script>
function stat(v){
	<?php if($attendance_ready==0){?>
		if(confirm('Do you want to confirmed this attendance?')){
			document.getElementById("spinner").style.display = "block";
			window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid);?>&c="+v;	
		}
		else{
			document.getElementById("chkConf").checked = false;
		}
	<?php }else{?>
		if(confirm('Do you want to unconfirmed this attendance?')){
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
	var msg = (v==1) ? 'Do you want to verify all attendance?' : 'Do you want to unverify all attendance?';
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
sticky = 270
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
<script>document.getElementById("spinner").style.display = "none";//$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
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
	$("#rw<?php echo $_SESSION['notif_indi_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_indi_id']);} ?>
<!-- end: JavaScript-->
</body>
</html>