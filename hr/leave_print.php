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
$lf_id = (isset($_REQUEST['lf']) && !empty($_REQUEST['lf']) ) ? functions::decode($_REQUEST['lf']) : 0;

$qInfo = $db->select('leave_file','*',array('lf_id'=>$lf_id));
$rInfo = $db->fetch_array($qInfo);
$emp_id = $rInfo['emp_id'];
$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$emp_id));
$emp_name = $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$emp_id));
$dt = $rInfo['date_file'];
$date_file = functions::datearr($rInfo['date_file']);
$lc_id = $rInfo['lc_id'];
$leave_reason = $rInfo['reason'];
$approved_by = $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$rInfo['approved_by']));
$recommended_by = $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$rInfo['recommended_by']));
$reviewed_by = $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$rInfo['reviewed_by']));
$noted_by = $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$rInfo['noted_by']));

$qLC = $db->select('leave_config','*',array('lc_id'=>$lc_id));
$rLC = $db->fetch_array($qLC);
$leave_type = $rLC['leave_name'];
$withpay = ($rLC['with_pay']) ? '(With Pay)' : '(Without Pay)';
$leave_with_pay = ($rLC['with_pay']) ? $rLC['with_pay'] : 0;
$leave_allowed_days = $rLC['allowed_days'];
if($db->getValue('leave_config_add','count(*)',array('lc_id'=>$lc_id))){
	$dateRegular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Regular'));
	$yearService = functions::year_diff(date('Y-m-d'),$dateRegular);
	$leave_allowed_days = $db->getValue('leave_config_add','allowed_days',array('lc_id'=>$lc_id),'AND "'.$yearService.'" BETWEEN service_year_from AND service_year_to');
}
function leave_avail_term($emp_id,$dateFile){
	global $db;
	$term='';
	$allow_with_pay='';
	$arrResult = array();
	$not_applicable=0;
	$date_leave_start='';
	$date_leave_end='';

	$date_refer='';$date_regular='';
	$emp_work_stat = $db->getValue('employee','work_status',array('emp_id'=>$emp_id));
	if( $emp_work_stat=='Regular' ){
		$date_regular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Regular'),'ORDER BY ews_date DESC LIMIT 1');
	}
	else if( $emp_work_stat=='Contractual' ){
		$date_refer = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Contractual'),'ORDER BY ews_date DESC LIMIT 1');
	}
	else if( $emp_work_stat=='Probationary' ){
		$date_refer = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Probationary'),'ORDER BY ews_date DESC LIMIT 1');
	}
	else
		$not_applicable=1;

	if($not_applicable==0){

		$allow_with_pay=0;
		if($date_regular){
			$years_regular = functions::year_diff($date_regular,date('Y-m-d'));
			if($years_regular)
				$allow_with_pay=1;
		}

		$date_reference = ($allow_with_pay==1) ? $date_regular : $date_refer;
		$month_date_hired_reference = substr($date_reference, 4, 6);//-12-15
		$dateFile_month_date =  substr($dateFile, 5, 5);
		$date_file_year = substr($dateFile, 0, 4);

		if( functions::year_diff(($date_file_year-1).$month_date_hired_reference,$dateFile)==0 ){//less than a year
			$term = ($date_file_year-1).'-'.$date_file_year;
			$date_leave_start = ($date_file_year-1).$month_date_hired_reference;
			$date_leave_end = ($date_file_year).$month_date_hired_reference;
		}
		else{//If is year or greater
			$term = $date_file_year.'-'.($date_file_year+1);
			$date_leave_start = ($date_file_year).$month_date_hired_reference;
			$date_leave_end = ($date_file_year+1).$month_date_hired_reference;
		}
    }

    return array('term'=>$term,'allow_with_pay'=>$allow_with_pay,'term_start'=>$date_leave_start,'term_end'=>$date_leave_end);
}

$leave_avail = leave_avail_term($emp_id,$dt);
$term = isset($leave_avail['term']) ? $leave_avail['term'] : '';
$term_start = isset($leave_avail['term_start']) ? $leave_avail['term_start'] : '';
$term_end = isset($leave_avail['term_end']) ? $leave_avail['term_end'] : '';

$leave_date_from = $term_start;
$leave_date_to = $term_end;
$consumedLeaves = $db->getValue('leave_file_detail lfd, leave_file lf','sum(leave_count)',array('lfd.emp_id'=>$emp_id,'lc_id'=>$lc_id),'AND lfd.lf_id=lf.lf_id AND lfd_date BETWEEN "'.$leave_date_from.'" AND "'.$leave_date_to.'"');
$consumed_leave = ($consumedLeaves) ? $consumedLeaves : 0;
$leave_remaining_days = $leave_allowed_days - $consumed_leave;

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
function departmentArea($emp_id){
	global $db;
	$qSelDep = $db->select('emp_position ep, dep_position dp, department d','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id AND dp.dep_id=d.dep_id');
	$count=0;$department='';
	while($rSelDep = $db->fetch_array($qSelDep)):
		$count++;
		$department .= $rSelDep['dep_name'];
		if($count > 1)
			$department .= ',<br>';
	endwhile;
	return $department;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Leave File Print</title>
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
	</style>
	<script>window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
	<thead>
		<tr>
			<td align="center">
				<?php 
				require_once('../class/print_header.php');
				print_header('Leave Request Form');
				?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="500" valign="top">
				<div align="center">
					<table style="font-size:12px;" border="0" width="100%">
						<tr>
							<td>
								<table border="1" width="100%">
									<tr>
										<td colspan="3">Date Filed: <?php echo $date_file; ?></td>
									</tr>
									<tr>
										<td width="30%" valign="top"><div align="left">Employee No.: <div style="padding-left:10px"><?php echo $emp_no; ?></div></div></td>
										<td width="30%" valign="top"><div align="left">Employee Name: <div style="padding-left:10px"><?php echo $emp_name; ?></div></div></td>
										<td width="30%" valign="top"><div align="left">Section/Department: <div style="padding-left:10px;font-size:9px;"><?php echo departmentArea($emp_id); ?></div></div></td>
									</tr>
								</table>
								<table border="1" width="100%">
									<tr>
										<td width="60%">
											<div align="left">
												Reason for Leave/Undertime: <?php echo $leave_reason; ?><br>
												<div style="font-size:9px;"><i>Note: For absences of more than three (3) days a Medical Certificate is required upon reporting to duty.</i></div>
											</div>
										</td>
										<td width="40%" valign="top"><div align="left">Leave Type: <?php echo $leave_type; ?></div></td>
									</tr>
								</table>
								<table width="100%" border="1" style="font-size:12px;">
									<tr>
										<th width="20%">Date</th>
										<th width="20%">Day</th>
										<th width="20%"><div align="center">Time</div></th>
										<th width="20%"><div align="center">No. of Days/Hours</div></th>
									</tr>
									<?php
									$dayCount=0;$allDayCount=0;
									$qDL = $db->select('leave_file_detail','*',array('lf_id'=>$lf_id));
									while($rDL = $db->fetch_array($qDL)):
										$rID = $rDL['lfd_id'];
										$time='';
										if( !empty($rDL['time_am']) && !empty($rDL['time_pm']) ){
											$time='Whole Day'; $dayCount=1;
										}
										else if( !empty($rDL['time_am']) ){
											$time='Morning'; $dayCount=.5;
										}
										else if( !empty($rDL['time_pm']) ){
											$time='Afternoon'; $dayCount=.5;
										}
										$allDayCount += $dayCount;
									?>
									<tr>
										<td height="5px;"><div align="center"><?php echo functions::datearr($rDL['lfd_date'])?></div></td>
										<td><div align="center"><?php echo date('l', strtotime($rDL['lfd_date']));?></div></td>
										<td><div align="center"><?php echo $time;?></div></td>
										<td><div align="center"><?php echo $dayCount;?></div></td>
									</tr>
									<?php endwhile;?>
									<tr>
										<td colspan="3"><div align="right">Total Leave</div></td>
										<td><div align="center"><?php echo $allDayCount;?></div></td>
									</tr>
								</table>
								<table border="1" width="100%">
									<tr>
										<td colspan="2" width="50%"><div align="left"><i>To be filled up by Immediate Head:</i></div></td>
										<td colspan="2" width="50%"><div align="left"><i>To be filled up by HR Payroll:</i></div></td>
									</tr>
									<tr>
										<td width="20%"><label style="font-size:10px;"><input type="checkbox" disabled> Approved</label></td>
										<td width="20%"><label style="font-size:10px;"><input type="checkbox" disabled> Disapproved</label></td>
										<td width="10%"><label style="font-size:10px;"><input type="checkbox" <?php if($leave_with_pay==1){echo 'checked="checked"';} ?> disabled> With Pay</label></td>
										<td width="30%"><label style="font-size:10px;"><input type="checkbox" <?php if($leave_with_pay==0){echo 'checked="checked"';} ?>disabled> Without Pay</label></td>
									</tr>
								</table>
								<table border="1" width="100%">
									<tr>
										<td colspan="3"><div align="left">Leave Status:</div></td>
									</tr>
									<tr>
										<td width="30%"><div align="center">Credits: <?php echo ($leave_allowed_days > 0) ? $leave_allowed_days : '~'; ?></div></td>
										<td><div align="center">Used: <?php echo ($consumed_leave - $allDayCount); ?></div></td>
										<td><div align="center">Balance: <?php echo ($leave_allowed_days > 0) ? $leave_remaining_days : '~';?></div></td>
									</tr>
								</table>
								<table border="1" width="100%">
									<tr>
										<td valign="top"><div align="left">Requesting Personnel:</div></td>
										<td valign="top"><div align="left">Approved By:</div></td>
										<td valign="top"><div align="left">Noted By:</div></td>
									</tr>
									<tr>
										<td width="20%" valign="bottom" style="border-bottom-style:none;" height="30"><div align="left"><?php echo $emp_name; ?></div></td>
										<td width="20%" valign="bottom" style="border-bottom-style:none;"><div align="left"><?php echo $approved_by; ?></div></td>
										<td width="20%" valign="bottom" style="border-bottom-style:none;"><div align="left"><?php echo $noted_by; ?></div></td>
									</tr>
									<tr>
										<td width="20%" valign="top" style="border-top-style:none;"><div align="left"><div style="font-size:10px"><i><?php echo ucwords(strtolower(position($rInfo['emp_id']))); ?></i></div></div></td>
										<td width="20%" valign="top" style="border-top-style:none;"><div align="left"><div style="font-size:10px"><i><?php echo ucwords(strtolower(position($rInfo['approved_by']))); ?></i></div></div></td>
										<td width="20%" valign="top" style="border-top-style:none;"><div align="left"><div style="font-size:10px"><i><?php echo ucwords(strtolower(position($rInfo['noted_by']))); ?></i></div></div></td>
									</tr>
									<tr>
										<td><div align="left">Date:</div></td>
										<td><div align="left">Date:</div></td>
										<td><div align="left">Date:</div></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<div align="right" style="font-size:10px">18HRD.FRM011.01-03/19</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
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