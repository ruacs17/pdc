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
$emprq = (isset($_REQUEST['emprq']) && !empty($_REQUEST['emprq']) ) ? functions::decode($_REQUEST['emprq']) : '';
$term = (isset($_SESSION['leave_term'])) ? $_SESSION['leave_term'] : '';
$emp_idSess = (isset($_SESSION['leave_empID'])) ? $_SESSION['leave_empID'] : '';
$emp_id = !empty($emprq) ? $emprq : $emp_idSess;

$arrLeaveType=array();
$qLT = $db->select('leave_config','*',array());
while($rLT = $db->fetch_array($qLT)):
	$customize = ( $db->getValue('leave_config_add','count(*)',array('lc_id'=>$rLT['lc_id'])) ) ? 1: 0;
	$arrLeaveType[]=array('leave_id'=>$rLT['lc_id'],'leave_name'=>$rLT['leave_name'],'allowed'=>$rLT['allowed_days'],'date_from_mon'=>$rLT['date_from_mon'],'date_from_day'=>$rLT['date_from_day'],'date_to_mon'=>$rLT['date_to_mon'],'date_to_day'=>$rLT['date_to_day'],'with_pay'=>$rLT['with_pay'],'customize'=>$customize);
endwhile;

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
		$date_reference = ($date_regular) ? $date_regular : $date_refer;
		$month_date_hired_reference = substr($date_reference, 4, 6);//-12-15

		$dateFile_month_date =  substr($dateFile, 5, 5);
		$date_file_year = substr($dateFile, 0, 4);
		$term = date('Y',strtotime($dateFile));
		$date_leave_start = date('Y',strtotime($dateFile)).'-01-01'; 
		$date_leave_end = date('Y',strtotime($dateFile)).'-12-31';
	}
	return array('term'=>$term,'allow_with_pay'=>$allow_with_pay,'term_start'=>$date_leave_start,'term_end'=>$date_leave_end);
}


function leaveDetails($lf_id){
	global $db;

	$qInfo = $db->select('leave_file','*',array('lf_id'=>$lf_id));
	$rInfo = $db->fetch_array($qInfo);
	$emp_id = $rInfo['emp_id'];
	$emp_name = $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$emp_id));
	$dt = $rInfo['date_file'];
	$dateFile = $rInfo['date_file'];
	$date_file = functions::datearr($rInfo['date_file']);
	$lc_id = $rInfo['lc_id'];
	$leave_reason = $rInfo['reason'];

	$qLC = $db->select('leave_config','*',array('lc_id'=>$lc_id));
	$rLC = $db->fetch_array($qLC);
	$leave_type = $rLC['leave_name'];
	$withpay = ($rLC['with_pay']) ? '(With Pay)' : '(Without Pay)';
	$leave_with_pay = ($rLC['with_pay']) ? $rLC['with_pay'] : 0;
	$leave_allowed = ($rLC['allowed_days']==0) ? 'unli' : 'limited';
	$leave_allowed_days = $rLC['allowed_days'];
	if($db->getValue('leave_config_add','count(*)',array('lc_id'=>$lc_id))){
		$dateRegular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Regular'));
		$yearService = functions::year_diff(date('Y-m-d'),$dateRegular);
		$leave_allowed_days = $db->getValue('leave_config_add','allowed_days',array('lc_id'=>$lc_id),'AND "'.$yearService.'" BETWEEN service_year_from AND service_year_to');
	}

	$leave_avail = leave_avail_term($emp_id,$dateFile);
	$term = isset($leave_avail['term']) ? $leave_avail['term'] : '';
	$term_start = isset($leave_avail['term_start']) ? $leave_avail['term_start'] : '';
	$term_end = isset($leave_avail['term_end']) ? $leave_avail['term_end'] : '';

	$leave_date_from = $term_start;
	$leave_date_to = $term_end;
	$consumedLeaves = $db->getValue('leave_file_detail lfd, leave_file lf','sum(leave_count)',array('lfd.emp_id'=>$emp_id,'lc_id'=>$lc_id,'term'=>$term),'AND lfd.lf_id=lf.lf_id AND lfd_date BETWEEN "'.$leave_date_from.'" AND "'.$leave_date_to.'" AND lf.date_file <= "'.$dateFile.'"');
	$consumed_leave = ($consumedLeaves) ? $consumedLeaves : 0;
	$leave_remaining_days = $leave_allowed_days - $consumed_leave;

	if($leave_allowed=='unli')
		$leaveAllowed = 'Unlimited';
	else
		$leaveAllowed = ($leave_allowed_days>1) ? $leave_allowed_days.' days' : $leave_allowed_days.' day';
	$leaveRemaining="";
	if($leave_allowed=='limited')
		$leaveRemaining =  ($leave_remaining_days>1) ? $leave_remaining_days.' days' : $leave_remaining_days.' day';

	$leaveTerm = functions::datearr($term_start).' - '.functions::datearr($term_end);
	$res = array('date_file'=>$date_file,'reason'=>$leave_reason,'leave_type'=>$leave_type.' '.$withpay,'leave_allowed'=>$leaveAllowed,'leave_remaining'=>$leaveRemaining,'term'=>$leaveTerm);
	return $res;
}
$qEmp = $db->select('employee','*',array('emp_id'=>$emp_id));
$rEmp = $db->fetch_array($qEmp);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Leave</title>
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
	<style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Leave Details</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<div align="left" style="padding-bottom:20px;">Employee: <strong><?php echo $rEmp['lname'].', '.$rEmp['fname']?></strong> </div>

					<table class="table table-bordered" style="font-size: 12px;">
						<thead>
							<tr style="background-color:#CCC">
								<?php foreach($arrLeaveType as $lid):?>
								<th width="20%" scope="col"><div align="center"><?php echo $lid['leave_name'];?></div></th>
								<?php endforeach;?>
							</tr>
						</thead>
						<tbody>
						<?php
						$countEmp=0;
						if($emp_id)
							$qEmp = $db->select('employee','*',array('emp_id'=>$emp_id));
						while($rEmp = $db->fetch_array($qEmp)):
							$countEmp++;
							$date_regular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$rEmp['emp_id'],'ews_stat'=>'Regular'),'ORDER BY ews_date DESC LIMIT 1');
							$allow_with_pay=0;
							if($date_regular){
								$years_regular = functions::year_diff($date_regular,date('Y-m-d'));
								if($years_regular)
									$allow_with_pay=1;
							}
						?>
							<tr>
								<?php
								$leave_allowed_days=0;$consumedLeaves=0;
								foreach($arrLeaveType as $lid):

									if($date_regular){
										$yearService = functions::year_diff(date('Y-m-d'),$date_regular);
										$leave_allowed_days = $db->getValue('leave_config_add','allowed_days',array('lc_id'=>$lid['leave_id']),'AND "'.$yearService.'" BETWEEN service_year_from AND service_year_to');
									}
									else
										$leave_allowed_days = $lid['allowed'];

									$consumedLeaves = $db->getValue('leave_file_detail lfd, leave_file lf','sum(leave_count)',array('lfd.emp_id'=>$rEmp['emp_id'],'lc_id'=>$lid['leave_id'],'term'=>$term),'AND lfd.lf_id=lf.lf_id');
									$leave_consumed = ($consumedLeaves) ? $consumedLeaves : 0;
									$display='';
									if($allow_with_pay==1 && $lid['with_pay']==1){
										$leave_allowed_days = ($leave_allowed_days) ? $leave_allowed_days : 0;
										$display = $leave_allowed_days.' / '.$leave_consumed;
									}
									else if( $lid['with_pay']==0){
										$display = '~ / '.$leave_consumed;
									}
								?>
								<td>
									<?php
									$arrLD = array();
									$qLFID = $db->select('leave_file lf, leave_file_detail lfd, leave_config lc','lc.lc_id,lf.lf_id,date_file,reason',array('lf.emp_id'=>$emp_id,'term'=>$term,'lc.lc_id'=>$lid['leave_id']),'AND lf.lf_id=lfd.lf_id AND lc.lc_id=lf.lc_id GROUP BY lc.lc_id,lf.lf_id,date_file,reason ORDER BY date_file');
									#echo $db->last_query;
									while($rLFID = $db->fetch_array($qLFID)):
										$arrLD = leaveDetails($rLFID['lf_id']);
									?>
									<div>Date Filed: <strong><?php echo $arrLD['date_file'];?></strong></div>
									<div>Reason: <strong><?php echo $arrLD['reason'];?></strong></div>
									<div>Leave Type: <strong><?php echo $arrLD['leave_type'];?></strong></div>
									<div style="display:none;">Leave Allowed: <strong><?php echo $arrLD['leave_allowed'];?></strong></div>
									<div style="display:none;">Leave Term: <strong><?php echo $arrLD['term'];?></strong></div>
									<div align="center" style="padding-top:10px;">
										<div style="width:90%">
											<table class="table table-striped table-bordered" border="0">
												<thead>
												<tr style="background-color:#CCC">
													<th width="20%">Date</th>
													<th width="25%">Day</th>
													<th width="20%"><div align="center">Morning</div></th>
													<th width="20%"><div align="center">Afternoon</div></th>
													<th width="15%"><div align="center">Day Count</div></th>
												</tr>
												</thead>
												<tbody>
												<?php
												$totalLeave=0;
													$qDL = $db->select('leave_file_detail','*',array('lf_id'=>$rLFID['lf_id']));
													while($rDL = $db->fetch_array($qDL)):
														$rID = $rDL['lfd_id'];
														$dayCount = ($rDL['time_am']) ? .5 : 0;
														$dayCount += ($rDL['time_pm']) ? .5 : 0;
														$totalLeave+=$dayCount;
												?>
												<tr>
													<td><?php echo functions::datearr($rDL['lfd_date'])?></td>
													<td><?php echo date('l', strtotime($rDL['lfd_date']));?></td>
													<td><div align="center"><?php echo ($rDL['time_am']) ? '<i class="halflings-icon ok"></i>': '';?></div></td>
													<td><div align="center"><?php echo ($rDL['time_pm']) ? '<i class="halflings-icon ok"></i>': '';?></div></td>
													<td><div align="center"><?php echo $dayCount ?></div></td>
												</tr>
												<?php endwhile;?>
												<tr>
													<td colspan="4"><div align="right"><strong>Total</strong></div></td>
													<td><div align="center"><strong><?php echo $totalLeave ?></strong></div></td>
												</tr>
												</tbody>
											</table>
										</div>
									</div>
									<?php if($arrLD['leave_remaining']){ ?><div style="display:none;">Leave Remaining: <strong><?php echo $arrLD['leave_remaining'];?></strong></div><?php } ?>
									<hr widtd="100%" style="background-color:#000">
									<?php endwhile; ?>
									<div style="display:none;" align="center" style="padding-bottom:10px;"><?php echo $display;?></div>
									<?php if($display){ ?>
									<?php if($leave_allowed_days){ ?><div align="left">Allowed: <strong><?php echo $leave_allowed_days; echo ($leave_allowed_days>1) ? ' Days' : ' Day'; ?></strong></div><?php } ?>
									<div align="left">Consumed: <strong><?php echo $leave_consumed; echo ($leave_consumed>1) ? ' Days' : ' Day';?></strong></div>
									<?php if($leave_allowed_days){ ?><div align="left">Remaining: <strong><?php echo $leave_allowed_days - $leave_consumed; echo ($leave_allowed_days>1) ? ' Days' : ' Day';?></strong></div><?php } ?>
									<?php }else{echo '<div align="center">-- Not Applicable --</div>';} ?>
								</td>
								<?php endforeach;?>
							</tr>
						<?php endwhile;?>
						</tbody>
					</table>
				</form>
			</div>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>