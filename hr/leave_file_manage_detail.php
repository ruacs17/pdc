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
$_SESSION['notif_id']=$lf_id;
$work_start='';$lc_id='';$emp_id='';$emp_name='';$date_file='';$dateFile='';$leave_type='';$leave_reason='';$withpay='';$leave_with_pay=0;$leave_allowed_days=0;$leave_remaining_days=0;$leave_allowed='';
if($lf_id){
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
		#$dateRegular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Regular'));
		$dateRegular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id),'ORDER BY ews_date LIMIT 1');//start counting in the hired date
		$yearService = functions::year_diff(date('Y-m-d'),$dateRegular);
		$leave_allowed_days = $db->getValue('leave_config_add','allowed_days',array('lc_id'=>$lc_id),'AND "'.$yearService.'" BETWEEN service_year_from AND service_year_to');
	}
	$work_start =  $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id),'ORDER BY ews_date ASC LIMIT 1');
}

$arrTerm = array();
if( $initialTerm = date('Y',strtotime($dateFile)) )
	$arrTerm[$initialTerm]=$initialTerm;

$qTrm = $db->select('leave_file_detail','DISTINCT term',array('lf_id'=>$lf_id));
while($rTrm = $db->fetch_array($qTrm)):
	$arrTerm[$rTrm['term']]=$rTrm['term'];
endwhile;

function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn=0,$actualOtOut=0,&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
	$cDate = date('Y-m-d');
	if( $assignAmIn && $assignAmOut )
		$dutyHours += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
	if( $assignPmIn && $assignPmOut )
		$dutyHours += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
	if( $actualOtIn && $actualOtOut ){
		$otTimeIn = functions::hm_to_minute($actualOtIn);
		$otTimeOut = functions::hm_to_minute($actualOtOut);
		$cDateTo = $cDate;
		if($otTimeIn > $otTimeOut){//If timefrom is bigger than timeto, then timeto is the next day.
			$cDateTo = functions::AddDay($cDate,1);
		}
		$otHours += functions::min_diff($actualOtIn,$cDate,$actualOtOut,$cDateTo);
	}

	if( $assignAmIn && $assignAmOut ){
		if($actualAmIn){
			if( strtotime($actualAmIn) > strtotime($assignAmIn) &&  strtotime($actualAmIn) <= strtotime($assignAmOut) ){//Actual in is between assign in and out
				$late += functions::min_diff($assignAmIn,$cDate,$actualAmIn,$cDate);
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmOut) ){ // Actual in is beyond assign out
				$late += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}

		if($actualAmOut){
			if( strtotime($actualAmOut) < strtotime($assignAmOut) && strtotime($actualAmOut) > strtotime($assignAmIn) ){//Actual out is between assign in and out
				$undertime += functions::min_diff($actualAmOut,$cDate,$assignAmOut,$cDate);
			}
			else if( strtotime($actualAmOut) < strtotime($assignAmIn) ){//Actual out is less then assign in
				$undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}
		else if( $actualAmIn){
			if( strtotime($actualAmIn) > strtotime($assignAmOut) ){
				//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmIn) && strtotime($actualAmIn) < strtotime($assignAmOut)){//Actual in is between assign in and out
				$undertime += functions::min_diff($actualAmIn,$cDate,$assignAmOut,$cDate);
			}
			else{
				$undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}
		else//If there's no actual out.
			$undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
	}

	if( $assignPmIn && $assignPmOut ){
		if($actualPmIn){
			if( strtotime($actualPmIn) > strtotime($assignPmIn) &&  strtotime($actualPmIn) <= strtotime($assignPmOut) ){//Actual in is between assign in and out
				$late += functions::min_diff($assignPmIn,$cDate,$actualPmIn,$cDate);
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmOut) ){ // Actual in is beyond assign out
				$late += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		if($actualPmOut){
			if( strtotime($actualPmOut) < strtotime($assignPmOut) && strtotime($actualPmOut) > strtotime($assignPmIn) ){//Actual out is between assign in and out
				$undertime += functions::min_diff($actualPmOut,$cDate,$assignPmOut,$cDate);
			}
			else if( strtotime($actualPmOut) < strtotime($assignPmIn) ){//Actual out is less then assign in
				$undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else if( $actualPmIn ){
			if( strtotime($actualPmIn) > strtotime($assignPmOut) ){//Actual in is greather than assign out.
				//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmIn) && strtotime($actualPmIn) < strtotime($assignPmOut)){//Actual in is between assign in and out
				$undertime += functions::min_diff($actualPmIn,$cDate,$assignPmOut,$cDate);
			}
			else{
				$undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else//If there's no actual out.
			$undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
	}

	$dutyHours -= $undertime + $late;
	$dutyHours = ( $dutyHours >=0 ) ? $dutyHours : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Leave Days Details</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
	<style type="text/css">
	body{font-size:12px;}
	</style>
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
<?php
$itmIDEdt = (isset($_REQUEST['itmID']) && !empty($_REQUEST['itmID']) ) ? functions::decode($_REQUEST['itmID']) : 0;
$txDateSelect='';
$chkAM='';$chkPM='';
if($itmIDEdt){
	$qDelLeave = $db->select('leave_file_detail','*',array('lfd_id'=>$itmIDEdt));
	$rDL = $db->fetch_array($qDelLeave);
	$lfd_date = $rDL['lfd_date'] ?? NULL;

	//check if there is other adjustments
	$eaa = $db->getValue('emp_attendance_adjustment','count(*)',array('emp_id'=>$emp_id,'eta_date'=>$lfd_date));
	#echo $db->last_query;
	//if there is/are adjustments
	if($eaa){
		//get the id of adjustment where remarks matches the leave type
        $eea_leave = $db->getValue('emp_attendance_adjustment','eta_id',array('emp_id'=>$emp_id,'eta_date'=>$lfd_date,'refer_id'=>$itmIDEdt));

		$qLA = $db->select('emp_attendance_adjustment','*',array('eta_id'=>$eea_leave));
		$rLA = $db->fetch_array($qLA);

		//if there are more than 1 adjustments
		if($eaa > 1 && $eea_leave){
			//check if the leave adjustment is the latest,
			//this will check and return the adjustment id if there is later record of leave adjustment
			if( $latestETA_ID = $db->getValue('emp_attendance_adjustment','eta_id',array('emp_id'=>$emp_id,'eta_date'=>$lfd_date),'AND eta_id > "'.$eea_leave.'" ORDER BY eta_id ASC LIMIT 1') ){
				//copy the originals(_org) of leave adjustment and move it to the latest.
				//get the attendance id of the about to change
				$eatd_id = $db->getValue('emp_attendance_adjustment','eatd_id',array('eta_id'=>$latestETA_ID));
				$db->update('emp_attendance_adjustment',
							array('am_in_org'=>$rLA['am_in_org'],'am_out_org'=>$rLA['am_out_org'],'pm_in_org'=>$rLA['pm_in_org'],'pm_out_org'=>$rLA['pm_out_org'],
							'duty_min_org'=>$rLA['duty_min_org'],'ot_min_org'=>$rLA['ot_min_org'],'late_min_org'=>$rLA['late_min_org'],'under_min_org'=>$rLA['under_min_org']),
							array('eta_id'=>$latestETA_ID));
				#echo '<br>Many: '.$db->last_query;
				//No need to change the attendance detail because the leave adjustment is not the latest record.
			}
			else{//means this is the latest, the leave adjustment
				//restore the original value of time in's and out's
				$is_holiday = ($rLA['is_holiday']===1) ? 3 : 2;
				$db->update('emp_attendance_detail',
							array('am_in'=>$rLA['am_in_org'],'am_out'=>$rLA['am_out_org'],'pm_in'=>$rLA['pm_in_org'],'pm_out'=>$rLA['pm_out_org'],
							'duty_min'=>$rLA['duty_min_org'],'ot_min'=>$rLA['ot_min_org'],'late_min'=>$rLA['late_min_org'],'under_min'=>$rLA['under_min_org'],'is_holiday'=>$is_holiday),
							array('eatd_id'=>$rLA['eatd_id']));
							#echo '<br>Many-One: '.$db->last_query;
			}
			//remove the leave adjustment
			$db->delete('emp_attendance_adjustment',array('eta_id'=>$eea_leave));
		}
		else if( $eaa == 1 && $eea_leave ){//if there is only one record of adjustment, this means only the leave adjustment.
			//restore the original value of time in's and out's
			$is_holiday = ($rLA['is_holiday']===1) ? 3 : 2;
			$db->update('emp_attendance_detail',
						array('am_in'=>$rLA['am_in_org'],'am_out'=>$rLA['am_out_org'],'pm_in'=>$rLA['pm_in_org'],'pm_out'=>$rLA['pm_out_org'],
						'duty_min'=>$rLA['duty_min_org'],'ot_min'=>$rLA['ot_min_org'],'late_min'=>$rLA['late_min_org'],'under_min'=>$rLA['under_min_org'],'is_holiday'=>$is_holiday),
						array('eatd_id'=>$rLA['eatd_id']));
			#echo '<br>Only One: '.$db->last_query;
			//remove the leave adjustment
			$db->delete('emp_attendance_adjustment',array('eta_id'=>$eea_leave));
		}
	}//end if there is/are adjustments
	$db->delete('leave_file_detail',array('lfd_id'=>$itmIDEdt));
	$_SESSION['notif_warning']='Leave day removed!';
	functions::sendTo(functions::pageName().'?lf='.functions::encode($lf_id));
	die();
}
if( isset($_POST['btnSelect']) ){
	$txDateSelect = (isset($_POST['txDateSelect']) && !empty($_POST['txDateSelect']) ) ? $_POST['txDateSelect'] : '';
	$term_start = date('Y',strtotime($txDateSelect)).'-01-01';
	$term_end = date('Y',strtotime($txDateSelect)).'-12-31';
	if($txDateSelect){
		if( functions::date_diff($term_end,$txDateSelect) > 0 ){//If it is after term end
			functions::say('Date selected must be on or before '.functions::datearr($term_end));
			functions::sendTo(functions::pageName().'?lf='.functions::encode($lf_id));
			die();
		}
		else if( functions::date_diff($txDateSelect,$term_start) > 0 ){//If it is before term start
			functions::say('Date selected must be on or after '.functions::datearr($term_start));
			functions::sendTo(functions::pageName().'?lf='.functions::encode($lf_id));
			die();
		}
		else{
			if( $db->getValue('leave_file_detail','count(*)',array('emp_id'=>$emp_id,'lfd_date'=>$txDateSelect)) ){
				functions::say('Date already selected!');
				functions::sendTo(functions::pageName().'?lf='.functions::encode($lf_id));
				die();
			}
		}
	}
}
if( isset($_POST['btnSave']) ){
	$txDateSelect = (isset($_POST['txDateSelect']) && !empty($_POST['txDateSelect']) ) ? $_POST['txDateSelect'] : '';
	$term_start = date('Y',strtotime($txDateSelect)).'-01-01';
	$term_end = date('Y',strtotime($txDateSelect)).'-12-31';
	$term = date('Y',strtotime($txDateSelect));
	$chkAM = (isset($_POST['chkAM']) && !empty($_POST['chkAM']) ) ? 1 : 0;
	$chkPM = (isset($_POST['chkPM']) && !empty($_POST['chkPM']) ) ? 1 : 0;
	$leave_count=0;
	if($txDateSelect && ($chkAM || $chkPM) ){

		$dayName = date('l', strtotime($txDateSelect));
		$selectedYr = date('Y', strtotime($txDateSelect));
		$qLC = $db->select('leave_config','*',array('lc_id'=>$lc_id));
		$rLC = $db->fetch_array($qLC);
		$leave_date_from = $term_start;
		$leave_date_to = $term_end;
		$consumedLeaves = $db->getValue('leave_file_detail lfd, leave_file lf','sum(leave_count)',array('lfd.emp_id'=>$emp_id,'lc_id'=>$lc_id),'AND lfd.lf_id=lf.lf_id AND lfd_date BETWEEN "'.$leave_date_from.'" AND "'.$leave_date_to.'"');
		$consumed_leave = ($consumedLeaves) ? $consumedLeaves : 0;

		$qTI = $db->select('emp_timein','*',array('emp_id'=>$emp_id,'eti_day'=>$dayName));
		$rTI = $db->fetch_array($qTI);
		$leave_count += ($chkAM) ? .5 : 0;
		$leave_count += ($chkPM) ? .5 : 0;
		$leave_mins = ( $rTI['am_in'] && $rTI['am_out']) ? functions::min_diff($rTI['am_in'],$txDateSelect,$rTI['am_out'],$txDateSelect) : 0;
		$leave_mins += ( $rTI['pm_in'] && $rTI['pm_out']) ? functions::min_diff($rTI['pm_in'],$txDateSelect,$rTI['pm_out'],$txDateSelect) : 0;
		$time_am_from = ($chkAM) ? $rTI['am_in'] : NULL;
		$time_am_to = ($chkAM) ? $rTI['am_out'] : NULL;
		$time_pm_from = ($chkPM) ? $rTI['pm_in'] : NULL;
		$time_pm_to = ($chkPM) ? $rTI['pm_out'] : NULL;

		$allow_leave_record=0;
		if($leave_allowed=='unli')
			$allow_leave_record=1;
		else if($leave_allowed_days >= ($consumed_leave+$leave_count))
			$allow_leave_record=1;

		//Allow to take a leave
		if( $allow_leave_record ){

			$lfdID = $db->insert('leave_file_detail',array('lf_id'=>$lf_id,'emp_id'=>$emp_id,'lfd_date'=>$txDateSelect,
			'time_am'=>$chkAM,'time_am_from'=>$time_am_from,'time_am_to'=>$time_am_to,
			'time_pm'=>$chkPM,'time_pm_from'=>$time_pm_from,'time_pm_to'=>$time_pm_to,
			'leave_count'=>$leave_count,'leave_mins'=>$leave_mins,'term'=>$term,'term_start'=>$term_start,'term_end'=>$term_end));

			if($lfdID){
				$cDate=$txDateSelect;
				$attendance_ready = $db->getValue('emp_attendance_detail eatd, emp_attendance eat','count(*)',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'attendance_ready'=>1),'AND eatd.eat_id=eat.eat_id');
				//check if there is attendance already
				
				if( empty($attendance_ready) ){//Attendance not yet confirmed
					//get the details of attendance
					$eatd_id = $db->getValue('emp_attendance_detail','eatd_id',array('emp_id'=>$emp_id,'eat_date'=>$cDate));
					$leave_name = $leave_type;
					$remarks = $leave_name.' : '.$leave_reason;
					if($eatd_id){
						$qATD = $db->select('emp_attendance_detail','*',array('eatd_id'=>$eatd_id));
						$rATD = $db->fetch_array($qATD);
						$dayName = $rATD['eat_day'] ?? NULL;
						$arrField = array(
						'eatd_id'=>$eatd_id,'emp_id'=>$emp_id,'eta_date'=>$cDate,'eta_day'=>$rATD['eat_day'],
						'am_in_org'=>$rATD['am_in'],'am_out_org'=>$rATD['am_out'],
						'pm_in_org'=>$rATD['pm_in'],'pm_out_org'=>$rATD['pm_out'],
						'ot_in'=>$rATD['ot_in'],'ot_out'=>$rATD['ot_out'],
						'duty_min_org'=>$rATD['duty_min'],'ot_min_org'=>$rATD['ot_min'],'late_min_org'=>$rATD['late_min'],'under_min_org'=>$rATD['under_min'],
						'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'remarks'=>$remarks);
					}

					$qLv = $db->select('leave_file_detail','*',array('lfd_id'=>$lfdID));
					$rLv = $db->fetch_array($qLv);

					$late_min_new=0;$under_min_new=0;$duty_min_new=0;$ot_min_new=0;
					$am_in_new = ($rLv['time_am_from']) ? $rLv['time_am_from'] : $rATD['am_in'];
					$am_out_new = ($rLv['time_am_to']) ? $rLv['time_am_to'] : $rATD['am_out'];
					$pm_in_new = ($rLv['time_pm_from']) ? $rLv['time_pm_from'] : $rATD['pm_in'];
					$pm_out_new = ($rLv['time_pm_to']) ? $rLv['time_pm_to'] : $rATD['pm_out'];

					//get the employee assign time in and time out
					$qETI = $db->select('emp_timein','*',array('emp_id'=>$emp_id,'eti_day'=>$dayName));
					$rETI = $db->fetch_array($qETI);
					$am_in_assign = ($rETI['am_in']) ? $rETI['am_in'] : NULL;
					$am_out_assign = ($rETI['am_out']) ? $rETI['am_out'] : NULL;
					$pm_in_assign = ($rETI['pm_in']) ? $rETI['pm_in'] : NULL;
					$pm_out_assign = ($rETI['pm_out']) ? $rETI['pm_out'] : NULL;

					if($eatd_id){
						diffComp($am_in_new,$am_in_assign,$am_out_new,$am_out_assign,$pm_in_new,$pm_in_assign,$pm_out_new,$pm_out_assign,$rATD['ot_in'],$rATD['ot_out'],$late_min_new,$under_min_new,$duty_min_new,$ot_min_new);
					}
					//update the attendance record with the new detail
					if($leave_with_pay==1){
						if($eatd_id){
							$arrField = array_merge($arrField,array('am_in_new'=>$am_in_new,'am_out_new'=>$am_out_new,'pm_in_new'=>$pm_in_new,'pm_out_new'=>$pm_out_new,'duty_min_new'=>$duty_min_new,'ot_min_new'=>$ot_min_new,'late_min_new'=>$late_min_new,'under_min_new'=>$under_min_new,'refer_id'=>$lfdID));
							//insert the original attendance record to the adjustment table
							$inserted_etaID = $db->insert('emp_attendance_adjustment',$arrField);
							$is_holiday = ( $db->getValue('emp_attendance_detail','is_holiday',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id))===1) ? 3 : 2;
							$db->update('emp_attendance_detail',array(
							'am_in'=>$am_in_new,'am_in_assign'=>$am_in_assign,'am_out'=>$am_out_new,'am_out_assign'=>$am_out_assign,
							'pm_in'=>$pm_in_new,'pm_in_assign'=>$pm_in_assign,'pm_out'=>$pm_out_new,'pm_out_assign'=>$pm_out_assign,
							'ot_in'=>$rATD['ot_in'],'ot_out'=>$rATD['ot_out'],
							'duty_min'=>$duty_min_new,'ot_min'=>$ot_min_new,'late_min'=>$late_min_new,'under_min'=>$under_min_new,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
						}
					}
					else{
						$under_min = functions::min_diff($am_in_assign,$cDate,$am_out_assign,$cDate);
						$under_min += functions::min_diff($pm_in_assign,$cDate,$pm_out_assign,$cDate);
						if($eatd_id){
							$arrField = array_merge($arrField,array('am_in_new'=>NULL,'am_out_new'=>NULL,'pm_in_new'=>NULL,'pm_out_new'=>NULL,'duty_min_new'=>0,'ot_min_new'=>$ot_min_new,'late_min_new'=>0,'under_min_new'=>$under_min,'refer_id'=>$lfdID));
							//insert the original attendance record to the adjustment table
							$inserted_etaID = $db->insert('emp_attendance_adjustment',$arrField);
							$is_holiday = ( $db->getValue('emp_attendance_detail','is_holiday',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id))===1) ? 3 : 2;
							$db->update('emp_attendance_detail',array(
							'am_in'=>NULL,'am_in_assign'=>$am_in_assign,'am_out'=>NULL,'am_out_assign'=>$am_out_assign,
							'pm_in'=>NULL,'pm_in_assign'=>$pm_in_assign,'pm_out'=>NULL,'pm_out_assign'=>$pm_out_assign,
							'ot_in'=>$rATD['ot_in'],'ot_out'=>$rATD['ot_out'],
							'duty_min'=>0,'ot_min'=>$ot_min_new,'late_min'=>0,'under_min'=>$under_min,'is_holiday'=>$is_holiday),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eatd_id'=>$eatd_id));
						}
					}
				}//end check if there is attendance already
				else{
					functions::say('attendance ready! cannot change attendance.');
				}

				$_SESSION['notif_success']='Leave day Added!';
				functions::sendTo(functions::pageName().'?lf='.functions::encode($lf_id));
				die();
			}
			else
				$_SESSION['notif_warning']='Leave day fail to add!';

		}
		else{
			$_SESSION['notif_warning']='Leave insufficient!';
		}
	}
	else{
		functions::say('Please check atleast 1 checkbox.');
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Leave Days</h2>
		</div>
		<div class="box-content">
			<form method="post"><?php #print_r($leave_avail); ?>
				<div align="left"><br>
					<div>Name:<strong> <?php echo $emp_name?></strong></div>
					<div style="padding-top:5px;">Date Filed: <strong><?php echo $date_file;?></strong></div>
					<div style="padding-top:5px;">Reason: <strong><?php echo $leave_reason;?></strong></div>
					<div style="padding-top:5px;">Type: <strong><?php echo $leave_type.' '.$withpay;?></strong></div>
					<?php
					foreach($arrTerm as $lTrm):
						$trmFrom = $lTrm.'-01-01';
						$trmTo = $lTrm.'-12-31';
						$consumedLeaves = $db->getValue('leave_file_detail lfd, leave_file lf','sum(leave_count)',array('lfd.emp_id'=>$emp_id,'lc_id'=>$lc_id,'term'=>$lTrm),'AND lfd.lf_id=lf.lf_id AND lfd_date BETWEEN "'.$trmFrom.'" AND "'.$trmTo.'"');
						$consumed_leave = ($consumedLeaves) ? $consumedLeaves : 0;
						$leave_remaining_days = $leave_allowed_days - $consumed_leave;
					?>
					<div style="padding-top:15px;">Term: <strong><?php echo functions::datearr($trmFrom).' - '.functions::datearr($trmTo);?></strong></div>
					<div>
						Allowed: 
						<strong>
							<?php
							if($leave_allowed=='unli')
								echo 'Unlimited';
							else
								echo ($leave_allowed_days>1) ? $leave_allowed_days.' days' : $leave_allowed_days.' day';
							?>
						</strong>
						<?php if($leave_allowed=='limited'){ ?>
						<div>Remaining: <strong><?php echo ($leave_remaining_days>1) ? $leave_remaining_days.' days' : $leave_remaining_days.' day';?></strong></div><br><br>
						<?php } ?>
					</div>
					<?php endforeach; ?>
					<table border='0' width="50%" align="center">
						<tr>
							<td width="50%" style="padding-top: 10px" align="right">Select Date&nbsp;&nbsp;&nbsp;<input name="txDateSelect" type="text" id="txDateSelect" value="<?php echo $txDateSelect;?>" style="width: 90px;" required></td>
							<td>&nbsp;&nbsp;&nbsp;<input type="submit" name="btnSelect" id="btnSelect" class="btn btn-info" value="Select"></td>
						</tr>
					</table>
					<table class="table table-striped" border="0">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="20%">Date</th>
								<th width="20%">Day</th>
								<th width="20%"><div align="center">Morning</div></th>
								<th width="20%"><div align="center">Afternoon</div></th>
								<th width="20%">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if($txDateSelect){
								$dayName = date('l', strtotime($txDateSelect));
								$qTI = $db->select('emp_timein','*',array('emp_id'=>$emp_id,'eti_day'=>$dayName));
								$rTI = $db->fetch_array($qTI);
							?>
							<tr>
								<td><?php echo functions::datearr($txDateSelect)?></td>
								<td><?php echo $dayName;?></td>
								<td><div align="center"><?php if ( $rTI['am_in'] && $rTI['am_out']) {?><input type="checkbox" name="chkAM" id="chkAM" value="1" <?php if($chkAM)echo 'checked="checked"';?>><?php }?></div></td>
								<td><div align="center"><?php if ( $rTI['pm_in'] && $rTI['pm_out']) {?><input type="checkbox" name="chkPM" id="chkPM" value="1" <?php if($chkPM)echo 'checked="checked"';?>><?php }?></div></td>
								<td><div align="center"><input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">&nbsp;<input type="submit" name="btnCancel" id="btnCancel" value="Cancel" class="btn btn-small"></div></td>
							</tr>
							<tr>
								<td colspan="5">&nbsp;</td>
							</tr>
							<?php }//End if($txDateSelect)?>
							<?php
							if(empty($txDateSelect)){
								$qDL = $db->select('leave_file_detail','*',array('lf_id'=>$lf_id));
								while($rDL = $db->fetch_array($qDL)):
									$rID = $rDL['lfd_id'];
							?>
							<tr>
								<td><?php echo functions::datearr($rDL['lfd_date'])?></td>
								<td><?php echo date('l', strtotime($rDL['lfd_date']));?></td>
								<td><div align="center"><?php echo ($rDL['time_am']) ? '<i class="halflings-icon ok"></i>': '';?></div></td>
								<td><div align="center"><?php echo ($rDL['time_pm']) ? '<i class="halflings-icon ok"></i>': '';?></div></td>
								<td>
									<div align="center">
										<?php
										$att_confirmed = $db->getValue('emp_attendance_detail eatd, emp_attendance eat','count(*)',array('emp_id'=>$emp_id,'eat_date'=>$rDL['lfd_date'],'attendance_ready'=>1),'AND is_holiday IN ("2","3") AND eatd.eat_id=eat.eat_id');
										if(empty($att_confirmed)){
										?>
										<a id="vwEdt<?php echo $rID?>" class="btn btn-mini btn-danger" title="Leave Detail" data-rel="tooltip" onClick="return delt()" href="?itmID=<?php echo functions::encode($rID);?>&lf=<?php echo functions::encode($lf_id);?>"><i class="halflings-icon white trash"></i></a>
										<?php } ?>
									</div>
								</td>
							</tr>
							<?php endwhile;
							}?>
						</tbody>
					</table>
				</div>
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
<script>
var eventDates = {};
<?php
$yrStart = date('Y') - 1;
$yrEnd = date('Y') + 1;
$term_start = $yrStart.'-01-01';
$term_end = $yrEnd.'-12-31';
// $yrStart = ($term_start) ? date('Y',strtotime($term_start)):'2000'; 
// $yrEnd = ($term_end) ? date('Y',strtotime($term_end)):date('Y');
$leavesDays = $db->select('leave_file_detail','*',array('emp_id'=>$emp_id));
while($rLDs = $db->fetch_array($leavesDays)):
?>
eventDates[ new Date('<?php echo date('Y/m/d',strtotime($rLDs['lfd_date'])) ?>') ] = new Date('<?php echo date('Y/m/d',strtotime($rLDs['lfd_date'])) ?>');
<?php endwhile;?>
$(document).ready(function(){
	$('#txDateSelect').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		showMonthAfterYear: true,
		yearRange:'<?php echo $yrStart ?>:<?php echo $yrEnd ?>',
		beforeShow:function(){
			var dtStart=new Date('<?php echo $work_start ?>');
			dtStart.setDate(dtStart.getDate())
			$('#txDateSelect').datepicker('option','minDate',dtStart);

			var dtEnd=new Date('<?php echo $term_end ?>');
			dtEnd.setDate(dtEnd.getDate())
			$('#txDateSelect').datepicker('option','maxDate',dtEnd);
		},
		beforeShowDay: function(date){
			var calendayDays = date.getDay();
			if(calendayDays>0){
				var highlight = eventDates[date];
				if(highlight)
					return [false,"","Already Taken!"];
				else
					return [true,'',''];				
			}
			else
				return [false,"",""];

		}
	});
});
function delt(){
	if(confirm('Do you want to remove this date?'))
		return true;
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
<!-- end: JavaScript-->
</body>
</html>
