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
if( isset($_POST['btnSubmit']) ){
	$emp_id = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : '';
	$term = ( isset($_POST['selTerm']) && !empty($_POST['selTerm']) ) ? $_POST['selTerm'] : '';
	$_SESSION['leave_term']=$term;
	$_SESSION['leave_empID']=$emp_id;
	functions::sendTo(functions::pageName());
	die();
}
$term = (isset($_SESSION['leave_term'])) ? $_SESSION['leave_term'] : '';
$emp_id = (isset($_SESSION['leave_empID'])) ? $_SESSION['leave_empID'] : '';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Leave Monitoring</title>
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
	<style type="text/css">.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }</style>
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
					<table>
						<tr>
							<td>
								<div align="left">
									<select name="txRequestedBy" id="txRequestedBy" data-rel="chosen" style="width:400px; text-align:left;">
										<option value="">--All Employee--</option>
										<?php
										$qEU = $db->query('SELECT * FROM employee ORDER BY lname,fname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($emp_id==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td>
								<div align="left" style="padding-left:5px;">
									<select name="selTerm" id="selTerm" data-rel="chosen">
										<?php
										for($y=(date('Y') + 1);$y>=2019;$y--):
										//$ytrm = $y.'-'.($y+1);
											$ytrm = $y;
										?>
										<option value="<?php echo $ytrm ?>" <?php if($ytrm==$term)echo 'selected="selected"'; ?>><?php echo $y ?></option>
										<?php endfor; ?>
									</select>
								</div>
							</td>
							<td>
								<div align="left" style="padding-left:5px;padding-bottom:5px;"><input type="submit" class="btn btn-primary btn-small" name="btnSubmit"></div>
							</td>
						</tr>
					</table>
					<div style="padding-top:10px;"></div>
					<div class="table-wrapper">
					<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="19%" scope="col">Employee</th>
								<?php foreach($arrLeaveType as $lid):?>
								<th width="10%" scope="col"><div align="center"><?php echo $lid['leave_name'];?><div style="padding-top:10px;">( Allowed / Consumed )</div></div></th>
								<?php endforeach;?>
							</tr>
						</thead>
						<tbody>
						<?php
						$countEmp=0;
						if($emp_id)
							$qEmp = $db->select('employee','*',array('emp_id'=>$emp_id));
						else
							$qEmp = $db->query('SELECT * FROM employee WHERE emp_id IN (SELECT DISTINCT emp_id FROM leave_file_detail WHERE term="'.$db->clean($term).'") ORDER BY lname,fname');
						#$qEmp = $db->query('SELECT * FROM employee WHERE (work_status="Regular" OR work_status="Contractual" OR work_status="Probationary") ORDER BY lname,fname');
						#$qEmp = $db->query('SELECT * FROM employee WHERE emp_id IN (SELECT DISTINCT emp_id FROM leave_file_detail WHERE term="'.$db->clean($term).'") ORDER BY lname,fname');
						
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
								<td width="20%"><a id="vw<?php echo $countEmp ?>" style="cursor:pointer;" class="thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'leave_monitor_detail.php?emprq=<?php echo functions::encode($rEmp['emp_id'])?>','Leave Monitoring Detail','1')"> <?php echo $rEmp['lname'].', '.$rEmp['fname']?></a></td>
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
										#$display = $leave_allowed_days.' / '.$leave_consumed;
										$display = '<table width="50%"><tr><td width="50%"><div align="center" title="Remaining: '.($leave_allowed_days-$leave_consumed).'">'.$leave_allowed_days.'</div></td><td width="50%"><div align="center" title="Remaining: '.($leave_allowed_days-$leave_consumed).'">'.$leave_consumed.'</div></td></tr></table>';
									}
									else if( $lid['with_pay']==0){
										#$display = '~ / '.$leave_consumed;
										$display = '<table width="50%"><tr><td width="50%"><div align="center">~</div></td><td width="50%"><div align="center">'.$leave_consumed.'</div></td></tr></table>';
									}
								?>
								<td width="10%"><div align="center"><?php echo $display; #echo $qq;?></div></td>
								<?php endforeach;?>
							</tr>
						<?php endwhile;?>
						</tbody>
					</table>
					</div>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function selEMp(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?empid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
</script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>