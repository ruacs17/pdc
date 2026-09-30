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
   
$tmAmIn = (isset($_REQUEST['tmAmIn']) && !empty($_REQUEST['tmAmIn']) ) ? functions::decode($_REQUEST['tmAmIn']) : '';
$tmAmOut = (isset($_REQUEST['tmAmOut']) && !empty($_REQUEST['tmAmOut']) ) ? functions::decode($_REQUEST['tmAmOut']) : '';
$tmPmIn = (isset($_REQUEST['tmPmIn']) && !empty($_REQUEST['tmPmIn']) ) ? functions::decode($_REQUEST['tmPmIn']) : '';
$tmPmOut = (isset($_REQUEST['tmPmOut']) && !empty($_REQUEST['tmPmOut']) ) ? functions::decode($_REQUEST['tmPmOut']) : '';

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$eatdid = (isset($_REQUEST['eatdid']) && !empty($_REQUEST['eatdid']) ) ? functions::decode($_REQUEST['eatdid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$p_id = $rEatID['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $rEatID['confirmed'];
$attendance_ready = $rEatID['attendance_ready'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$emp_id = (isset($_REQUEST['empid']) && !empty($_REQUEST['empid']) ) ? functions::decode($_REQUEST['empid']) : 0;
$att_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'att_ready'=>1));

$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_end.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
$rStat = $db->fetch_array($qStat);
$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : 'Undefined Status';
$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <i>(Project Based)</i>' : '';
$work_status = $wrkStat.$projBased;
#$work_status = $db->getValue('emp_work_status','ews_stat',array('emp_id'=>$emp_id),'AND ews_date <= "'.$date_end.'" ORDER BY ews_date DESC, ews_id DESC LIMIT 1');


$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$emp_id));
$emp_name = $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_id'=>$emp_id));
$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
$countPos=0;$position='';
while($rPos = $db->fetch_array($qPos)):
	if($countPos)
		$position .= ' /<br>';
	$position .= $rPos['pos_name'];
	$countPos++;
endwhile;

function dayName($date=''){
	#$date = '2014-02-25';
	if($date)
		return date('l', strtotime($date));
	else
		return '';
}

function absentDay($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',&$am_absent=0,&$pm_absent=0,&$am_absent_min=0,&$pm_absent_min=0){
	$cDate = date('Y-m-d');

	if( $assignAmIn && $assignAmOut ){//If there is required time in and out
		//Count Minutes LATE
		if($actualAmIn){//If there is time in
			if( strtotime($actualAmIn) > strtotime($assignAmIn) &&  strtotime($actualAmIn) <= strtotime($assignAmOut) ){//Actual in is between assign in and out
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$actualAmIn,$cDate);//Minutes from assign in to actual in
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmOut) ){ // Actual in is beyond assign out
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);//Count the required minutes for morning duty.
			}
		}

		//Count Minutes UNDERTIME
		if($actualAmOut){//If there is morning time out
			if( strtotime($actualAmOut) < strtotime($assignAmOut) && strtotime($actualAmOut) > strtotime($assignAmIn) ){//Actual out is between assign in and out
				$am_absent_min += functions::min_diff($actualAmOut,$cDate,$assignAmOut,$cDate);//Minutes from Actual Out to Assign Out.
			}
			else if( strtotime($actualAmOut) < strtotime($assignAmIn) ){//Actual out is less then assign in
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);//Count the required minutes for morning duty.
			}
		}
		else if( $actualAmIn ){//if there is no morning out but there is morning time in
			if( strtotime($actualAmIn) > strtotime($assignAmOut) ){//if time in is greater than assign in
				//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualAmIn) > strtotime($assignAmIn) && strtotime($actualAmIn) < strtotime($assignAmOut)){//Actual in is between assign in and out
				$am_absent_min += functions::min_diff($actualAmIn,$cDate,$assignAmOut,$cDate);
			}
			else{//if time in is before morning assign in and after morning assign out
				$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			}
		}
		else{//If there's no actual out.
			$am_absent_min += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
		}
		if( $am_absent_min ){
			$requiredMins = functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
			if( $am_absent_min >= $requiredMins )
				$am_absent=.5;
			else
				$am_absent=0;
		}
	}

	if( $assignPmIn && $assignPmOut ){
		if($actualPmIn){
			if( strtotime($actualPmIn) > strtotime($assignPmIn) &&  strtotime($actualPmIn) <= strtotime($assignPmOut) ){//Actual in is between assign in and out
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$actualPmIn,$cDate);
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmOut) ){ // Actual in is beyond assign out
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		if($actualPmOut){
			if( strtotime($actualPmOut) < strtotime($assignPmOut) && strtotime($actualPmOut) > strtotime($assignPmIn) ){//Actual out is between assign in and out
				$pm_absent_min += functions::min_diff($actualPmOut,$cDate,$assignPmOut,$cDate);
			}
			else if( strtotime($actualPmOut) < strtotime($assignPmIn) ){//Actual out is less then assign in
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else if( $actualPmIn ){
			if( strtotime($actualPmIn) > strtotime($assignPmOut) ){//Actual in is greather than assign out.
			//Do nothing It's already been deducted by late.
			}
			else if( strtotime($actualPmIn) > strtotime($assignPmIn) && strtotime($actualPmIn) < strtotime($assignPmOut)){//Actual in is between assign in and out
				$pm_absent_min += functions::min_diff($actualPmIn,$cDate,$assignPmOut,$cDate);
			}
			else{
				$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			}
		}
		else{//If there's no actual out.
			$pm_absent_min += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
		}
		if( $pm_absent_min ){
			$requiredMins = functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
			if( $pm_absent_min >= $requiredMins )
				$pm_absent=.5;
			else
				$pm_absent=0;
		}
	}
}

function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',$actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn='',$actualOtOut='',&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
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

if( isset($_POST['btnSave']) ){
	$eat_id='';
	$qEmpAttInfo = $db->select('emp_attendance_detail','*',array('eatd_id'=>$eatdid,'eat_id'=>$eatid));
	$rEAI = $db->fetch_array($qEmpAttInfo);
	$selAmInHr = (isset($_POST['selAmInHr']) && !empty($_POST['selAmInHr']) ) ? trim($_POST['selAmInHr']) : '';
	$selAmInMn = (isset($_POST['selAmInMn']) && !empty($_POST['selAmInMn']) ) ? trim($_POST['selAmInMn']) : '';


	$selAmOutHr = (isset($_POST['selAmOutHr']) && !empty($_POST['selAmOutHr']) ) ? trim($_POST['selAmOutHr']) : '';
	$selAmOutMn = (isset($_POST['selAmOutMn']) && !empty($_POST['selAmOutMn']) ) ? trim($_POST['selAmOutMn']) : '';

	$selPmInHr = (isset($_POST['selPmInHr']) && !empty($_POST['selPmInHr']) ) ? trim($_POST['selPmInHr']) : '';
	$selPmInMn = (isset($_POST['selPmInMn']) && !empty($_POST['selPmInMn']) ) ? trim($_POST['selPmInMn']) : '';

	$selPmOutHr = (isset($_POST['selPmOutHr']) && !empty($_POST['selPmOutHr']) ) ? trim($_POST['selPmOutHr']) : '';
	$selPmOutMn = (isset($_POST['selPmOutMn']) && !empty($_POST['selPmOutMn']) ) ? trim($_POST['selPmOutMn']) : '';
	$remarks = (isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : NULL;


	$setAmIn = ($selAmInHr && $selAmInMn) ? $selAmInHr.':'.$selAmInMn : NULL;
	$setAmOut = ($selAmOutHr && $selAmOutMn) ? $selAmOutHr.':'.$selAmOutMn : NULL;
	$setPmIn = ($selPmInHr && $selPmInMn) ? $selPmInHr.':'.$selPmInMn : NULL;
	$setPmOut = ($selPmOutHr && $selPmOutMn) ? $selPmOutHr.':'.$selPmOutMn : NULL;


	$setAmIn = (isset($_POST['selAmIn']) && !empty($_POST['selAmIn']) ) ? trim($_POST['selAmIn']) : NULL;
	$setAmOut = (isset($_POST['selAmOut']) && !empty($_POST['selAmOut']) ) ? trim($_POST['selAmOut']) : NULL;
	$setPmIn = (isset($_POST['selPmIn']) && !empty($_POST['selPmIn']) ) ? trim($_POST['selPmIn']) : NULL;
	$setPmOut = (isset($_POST['selPmOut']) && !empty($_POST['selPmOut']) ) ? trim($_POST['selPmOut']) : NULL;

	$otin = ($rEAI['ot_in']) ? $rEAI['ot_in'] : NULL;
	$otout = ($rEAI['ot_out']) ? $rEAI['ot_out'] : NULL;

	$eatd_id = $db->insert('emp_attendance_adjustment',array('eatd_id'=>$rEAI['eatd_id'],'emp_id'=>$rEAI['emp_id'],'eta_date'=>$rEAI['eat_date'],'eta_day'=>$rEAI['eat_day'],
	'am_in_new'=>$setAmIn,'am_in_org'=>$rEAI['am_in'],
	'am_out_new'=>$setAmOut,'am_out_org'=>$rEAI['am_out'],
	'pm_in_new'=>$setPmIn,'pm_in_org'=>$rEAI['pm_in'],
	'pm_out_new'=>$setPmOut,'pm_out_org'=>$rEAI['pm_out'],
	'ot_in'=>$otin,'ot_out'=>$otout,
	'duty_min_org'=>$rEAI['duty_min'],'ot_min_org'=>$rEAI['ot_min'],
	'late_min_org'=>$rEAI['late_min'],'under_min_org'=>$rEAI['under_min'],
	'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'user_id'=>$user_id,'remarks'=>$remarks));
	#echo $db->last_query;

	$emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rEAI['emp_id']));
	$arrAttendanceRecord[$emp_no][$rEAI['eat_date']] = array('amin'=>$setAmIn,'amout'=>$setAmOut,'pmin'=>$setPmIn,'pmout'=>$setPmOut,'otin'=>$rEAI['ot_in'],'otout'=>$rEAI['ot_out']);


	foreach($arrAttendanceRecord as $employee => $calDate):
		$monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';$am_in='';$am_out='';$pm_in='';$pm_out='';
		$emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));

		$qTimeIn = $db->select('emp_timein','*',array('emp_id'=>$emp_id));
		while($rTI = $db->fetch_array($qTimeIn)):
			if($rTI['eti_day']=='Monday'){
				$monAmIn=$rTI['am_in'];$monAmOut=$rTI['am_out'];$monPmIn=$rTI['pm_in'];$monPmOut=$rTI['pm_out'];
			}
			if($rTI['eti_day']=='Tuesday'){
				$tueAmIn=$rTI['am_in'];$tueAmOut=$rTI['am_out'];$tuePmIn=$rTI['pm_in'];$tuePmOut=$rTI['pm_out'];
			}
			if($rTI['eti_day']=='Wednesday'){
				$wedAmIn=$rTI['am_in'];$wedAmOut=$rTI['am_out'];$wedPmIn=$rTI['pm_in'];$wedPmOut=$rTI['pm_out'];
			}
			if($rTI['eti_day']=='Thursday'){
				$thuAmIn=$rTI['am_in'];$thuAmOut=$rTI['am_out'];$thuPmIn=$rTI['pm_in'];$thuPmOut=$rTI['pm_out'];
			}
			if($rTI['eti_day']=='Friday'){
				$friAmIn=$rTI['am_in'];$friAmOut=$rTI['am_out'];$friPmIn=$rTI['pm_in'];$friPmOut=$rTI['pm_out'];
			}
			if($rTI['eti_day']=="Saturday"){
				$satAmIn=$rTI['am_in'];$satAmOut=$rTI['am_out'];$satPmIn=$rTI['pm_in'];$satPmOut=$rTI['pm_out'];
			}
		endwhile;
		foreach($calDate as $cDate => $logins):
			$am_in='';$am_out='';$pm_in='';$pm_out='';
			$dayName = dayName($cDate);
			$late=0; $undertime=0;$dutyHours=0;$otHours=0;

			if($dayName=="Monday"){
				diffComp($logins['amin'],$monAmIn,$logins['amout'],$monAmOut,$logins['pmin'],$monPmIn,$logins['pmout'],$monPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
				$am_in_assign = (isset($monAmIn) && !empty($monAmIn)) ? $monAmIn : NULL;
				$am_out_assign = (isset($monAmOut) && !empty($monAmOut)) ? $monAmOut : NULL;
				$pm_in_assign = (isset($monPmIn) && !empty($monPmIn)) ? $monPmIn : NULL;
				$pm_out_assign = (isset($monPmOut) && !empty($monPmOut)) ? $monPmOut : NULL;
			}
			if($dayName=="Tuesday"){
				diffComp($logins['amin'],$tueAmIn,$logins['amout'],$tueAmOut,$logins['pmin'],$tuePmIn,$logins['pmout'],$tuePmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
				$am_in_assign = (isset($tueAmIn) && !empty($tueAmIn)) ? $tueAmIn : NULL;
				$am_out_assign = (isset($tueAmOut) && !empty($tueAmOut)) ? $tueAmOut : NULL;
				$pm_in_assign = (isset($tuePmIn) && !empty($tuePmIn)) ? $tuePmIn : NULL;
				$pm_out_assign = (isset($tuePmOut) && !empty($tuePmOut)) ? $tuePmOut : NULL;
			}
			if($dayName=="Wednesday"){
				diffComp($logins['amin'],$wedAmIn,$logins['amout'],$wedAmOut,$logins['pmin'],$wedPmIn,$logins['pmout'],$wedPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
				$am_in_assign = (isset($wedAmIn) && !empty($wedAmIn)) ? $wedAmIn : NULL;
				$am_out_assign = (isset($wedAmOut) && !empty($wedAmOut)) ? $wedAmOut : NULL;
				$pm_in_assign = (isset($wedPmIn) && !empty($wedPmIn)) ? $wedPmIn : NULL;
				$pm_out_assign = (isset($wedPmOut) && !empty($wedPmOut)) ? $wedPmOut : NULL;
			}
			if($dayName=="Thursday"){
				diffComp($logins['amin'],$thuAmIn,$logins['amout'],$thuAmOut,$logins['pmin'],$thuPmIn,$logins['pmout'],$thuPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
				$am_in_assign = (isset($thuAmIn) && !empty($thuAmIn)) ? $thuAmIn : NULL;
				$am_out_assign = (isset($thuAmOut) && !empty($thuAmOut)) ? $thuAmOut : NULL;
				$pm_in_assign = (isset($thuPmIn) && !empty($thuPmIn)) ? $thuPmIn : NULL;
				$pm_out_assign = (isset($thuPmOut) && !empty($thuPmOut)) ? $thuPmOut : NULL;
			}
			if($dayName=="Friday"){
				diffComp($logins['amin'],$friAmIn,$logins['amout'],$friAmOut,$logins['pmin'],$friPmIn,$logins['pmout'],$friPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
				$am_in_assign = (isset($friAmIn) && !empty($friAmIn)) ? $friAmIn : NULL;
				$am_out_assign = (isset($friAmOut) && !empty($friAmOut)) ? $friAmOut : NULL;
				$pm_in_assign = (isset($friPmIn) && !empty($friPmIn)) ? $friPmIn : NULL;
				$pm_out_assign = (isset($friPmOut) && !empty($friPmOut)) ? $friPmOut : NULL;
			}
			if($dayName=="Saturday"){
				diffComp($logins['amin'],$satAmIn,$logins['amout'],$satAmOut,$logins['pmin'],$satPmIn,$logins['pmout'],$satPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
				$am_in_assign = (isset($satAmIn) && !empty($satAmIn)) ? $satAmIn : NULL;
				$am_out_assign = (isset($satAmOut) && !empty($satAmOut)) ? $satAmOut : NULL;
				$pm_in_assign = (isset($satPmIn) && !empty($satPmIn)) ? $satPmIn : NULL;
				$pm_out_assign = (isset($satPmOut) && !empty($satPmOut)) ? $satPmOut : NULL;
			}
			$am_in = (isset($logins['amin']) && !empty($logins['amin'])) ? $logins['amin'] : NULL;
			$am_out = (isset($logins['amout']) && !empty($logins['amout'])) ? $logins['amout'] : NULL;
			$pm_in = (isset($logins['pmin']) && !empty($logins['pmin'])) ? $logins['pmin'] : NULL;
			$pm_out = (isset($logins['pmout']) && !empty($logins['pmout'])) ? $logins['pmout'] : NULL;
			$ot_in = (isset($logins['otin']) && !empty($logins['otin'])) ? $logins['otin'] : NULL;
			$ot_out = (isset($logins['otout']) && !empty($logins['otout'])) ? $logins['otout'] : NULL;
			$emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
			$attendance_date = $db->getValue('emp_attendance_detail','eat_date',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eat_id'=>$eatid));
			if($attendance_date){
				$arrUpdateAtt=array('am_in_assign'=>$am_in_assign,'am_in'=>$am_in,
				'am_out_assign'=>$am_out_assign,'am_out'=>$am_out,
				'pm_in_assign'=>$pm_in_assign,'pm_in'=>$pm_in,
				'pm_out_assign'=>$pm_out_assign,'pm_out'=>$pm_out,
				'duty_min'=>$dutyHours,'ot_min'=>$otHours,'late_min'=>$late,'under_min'=>$undertime);
				$db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eat_id'=>$eatid));
				#echo '<br>'.$db->last_query;
				$db->update('emp_attendance_adjustment',array('duty_min_new'=>$dutyHours,'ot_min_new'=>$otHours,'late_min_new'=>$late,'under_min_new'=>$undertime));
				#echo '<br>'.$db->last_query;


				//Checking for absent
				$am_absent=0;$pm_absent=0;$am_absent_min=0;$pm_absent_min=0;
				$q_absent = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eat_id'=>$eatid));
				$ra = $db->fetch_array($q_absent);
				absentDay($ra['am_in'],$ra['am_in_assign'],$ra['am_out'],$ra['am_out_assign'],$ra['pm_in'],$ra['pm_in_assign'],$ra['pm_out'],$ra['pm_out_assign'],$am_absent,$pm_absent,$am_absent_min,$pm_absent_min);
				$db->update('emp_attendance_detail',array('am_absent'=>$am_absent,'pm_absent'=>$pm_absent,'am_absent_min'=>$am_absent_min,'pm_absent_min'=>$pm_absent_min),array('emp_id'=>$emp_id,'eat_date'=>$cDate,'eat_id'=>$eatid));
				//End checking absent
			}
		endforeach;
	endforeach;
	$_SESSION['notif_success']='Attendance modification saved!';
	functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid).'&empid='.functions::encode($emp_id).'&eatdid='.functions::encode($eatdid));
	die();
}
$totalRow = 0;
$content = array();
$arrAttendanceRecord = array();
$arrPayrollList = array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Management</title>
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
	<link rel="stylesheet" href="../css/timepicker.css">
	<!-- end: CSS -->
	<!-- Modern Professional Custom Overrides -->
	<style>
		:root {
			--primary-color: #4f46e5;
			--primary-hover: #4338ca;
			--success-color: #10b981;
			--background-light: #f8fafc;
			--card-bg: #ffffff;
			--text-main: #1e293b;
			--text-muted: #64748b;
			--border-color: #e2e8f0;
		}

		html, body {
			margin: 0 !important;
			padding: 0 !important;
			width: 100% !important;
			min-height: 100% !important;
			background-color: var(--background-light) !important;
			color: var(--text-main);
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		}

		.row-fluid {
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
		}

		.box {
			background: var(--card-bg);
			border: none !important;
			border-radius: 0 !important;
			box-shadow: none !important;
			margin-bottom: 0 !important;
			overflow: hidden;
			width: 100% !important;
		}

		.box-header {
			background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
			color: #ffffff !important;
			padding: 20px 24px;
			border-bottom: none !important;
			border-radius: 0 !important;
		}

		.box-header h2 {
			font-size: 16px;
			font-weight: 600;
			letter-spacing: 0.5px;
			margin: 0;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.box-header h2 i {
			color: #ffffff;
		}

		.box-content {
			padding: 24px !important;
			background-color: var(--card-bg);
		}

		/* Modern Employee Info Card Redesign */
		.employee-info-card {
			display: grid;
			grid-template-columns: 2fr 1.5fr 1.5fr;
			gap: 16px;
			background: #f8fafc;
			border: 1px solid var(--border-color);
			border-radius: 10px;
			padding: 16px 20px;
			margin-bottom: 24px;
			box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
		}

		.employee-info-item {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.employee-info-label {
			font-size: 11px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.6px;
			color: var(--text-muted);
		}

		.employee-info-value {
			font-size: 13px;
			font-weight: 600;
			color: var(--text-main);
		}

		.employee-status-badge {
			display: inline-flex;
			align-items: center;
			background-color: #ecfdf5;
			color: #047857;
			padding: 2px 10px;
			border-radius: 9999px;
			font-size: 12px;
			font-weight: 600;
			width: fit-content;
			border: 1px solid #a7f3d0;
		}

		/* Modern Table Enhancements */
		.table {
			width: 100% !important;
			border-collapse: separate !important;
			border-spacing: 0 !important;
			border: 1px solid var(--border-color) !important;
			border-radius: 10px !important;
			overflow: hidden !important;
			margin-bottom: 24px !important;
			background-color: #ffffff;
			box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
		}

		.table th {
			background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9) !important;
			color: var(--text-main) !important;
			font-weight: 600 !important;
			text-transform: uppercase !important;
			font-size: 11px !important;
			letter-spacing: 0.6px !important;
			border-bottom: 2px solid var(--border-color) !important;
			border-right: 1px solid var(--border-color) !important;
			padding: 8px 10px !important;
			vertical-align: middle !important;
		}

		.table th:last-child {
			border-right: none !important;
		}

		.table td {
			padding: 10px 12px !important;
			vertical-align: middle !important;
			border-top: 1px solid var(--border-color) !important;
			border-right: 1px solid var(--border-color) !important;
			color: var(--text-main) !important;
			font-size: 12px !important;
		}

		.table td:last-child {
			border-right: none !important;
		}

		.table-hover tbody tr {
			transition: background-color 0.15s ease;
		}

		.table-hover tbody tr:hover td {
			background-color: #f8fafc !important;
		}

		.table-striped tbody tr:nth-child(odd) td {
			background-color: #fafbfc !important;
		}

		.btn-primary {
			background-color: var(--primary-color) !important;
			background-image: none !important;
			border: none !important;
			border-radius: 6px !important;
			padding: 10px 24px !important;
			font-weight: 600 !important;
			letter-spacing: 0.5px;
			box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
			transition: background-color 0.2s, transform 0.1s;
		}

		.btn-primary:hover {
			background-color: var(--primary-hover) !important;
		}

		.btn-primary:active {
			transform: translateY(1px);
		}

		.badge-time-link {
			display: inline-block;
			background-color: #f1f5f9;
			color: var(--primary-color);
			padding: 2px 6px;
			border-radius: 4px;
			font-size: 11px;
			font-weight: 600;
			text-decoration: none;
			margin: 1px 0;
			transition: background-color 0.15s;
		}

		.badge-time-link:hover {
			background-color: #e2e8f0;
			text-decoration: none;
			color: var(--primary-hover);
		}

		/* Modern Time Log Pill Styles */
		.time-log-pill {
			display: inline-block;
			background-color: #f1f5f9;
			color: var(--text-main);
			padding: 3px 8px;
			border-radius: 4px;
			font-size: 11px;
			font-weight: 600;
			text-decoration: none;
			border: 1px solid var(--border-color);
			transition: all 0.15s ease;
			margin: 2px;
		}

		.time-log-pill:hover {
			background-color: #e2e8f0;
			color: var(--primary-color);
			text-decoration: none;
			border-color: #cbd5e1;
		}

		.time-log-pill.active {
			background-color: var(--primary-color);
			color: #ffffff;
			border-color: var(--primary-color);
		}

		.history-section-title {
			font-size: 13px;
			font-weight: 700;
			color: var(--text-muted);
			text-transform: uppercase;
			letter-spacing: 0.6px;
			margin-top: 35px;
			margin-bottom: 15px;
			text-align: center;
		}

		/* Enhanced Modern Modal Dialog Styles */
		#confirmModal {
			border-radius: 12px;
			border: none;
			box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
			overflow: hidden;
		}

		#confirmModal .modal-header {
			background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
			color: #ffffff;
			padding: 16px 20px;
			border-bottom: none;
		}

		#confirmModal .modal-header h3 {
			font-size: 16px;
			font-weight: 600;
			margin: 0;
			color: #ffffff;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		#confirmModal .modal-header .close {
			color: #ffffff;
			opacity: 0.8;
			text-shadow: none;
			margin-top: -2px;
		}

		#confirmModal .modal-header .close:hover {
			opacity: 1;
		}

		#confirmModal .modal-body {
			padding: 24px 20px;
			background-color: #ffffff;
		}

		#confirmModal .modal-body p {
			font-size: 14px;
			color: var(--text-main);
			margin: 0;
			line-height: 1.5;
		}

		#confirmModal .modal-footer {
			background-color: #f8fafc;
			padding: 14px 20px;
			border-top: 1px solid var(--border-color);
			display: flex;
			justify-content: flex-end;
			gap: 10px;
		}

		#confirmModal .modal-footer .btn {
			border-radius: 6px;
			font-weight: 600;
			padding: 8px 18px;
			font-size: 13px;
		}

		#confirmModal .modal-footer .btn-secondary {
			background: #ffffff;
			border: 1px solid var(--border-color);
			color: var(--text-muted);
		}

		#confirmModal .modal-footer .btn-secondary:hover {
			background: #f1f5f9;
			color: var(--text-main);
		}
	</style>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ATTENDANCE ADJUSTMENT</h2>
		</div>
		<div class="box-content">
			<form id="attendanceForm" class="form-horizontal" method="post">
				<!-- Redesigned Employee Info Panel -->
				<div class="employee-info-card">
					<div class="employee-info-item">
						<span class="employee-info-label">Employee Name</span>
						<span class="employee-info-value"><?php echo $emp_no.' - '.$emp_name;?></span>
					</div>
					<div class="employee-info-item">
						<span class="employee-info-label">Position</span>
						<span class="employee-info-value"><?php echo $position?></span>
					</div>
					<div class="employee-info-item">
						<span class="employee-info-label">Status</span>
						<div>
							<span class="employee-status-badge"><?php echo $work_status?></span>
						</div>
					</div>
				</div>
				<?php 
				$dateAttendance = $db->getValue('emp_attendance_detail','eat_date',array('eatd_id'=>$eatdid,'eat_id'=>$eatid));
				$qDisp = $db->select('travel_personnel tv, travel_order tro, travel_order_detail tod','*',array('personnel_id'=>$emp_id,'travel_date'=>$dateAttendance),'AND tro.to_id=tod.to_id AND tv.to_id=tro.to_id ORDER BY act_time_from');
				#echo $db->last_query;
				if($db->num_rows($qDisp)){
				?>
				<table width="100%" border="0" align="center" class="table table-striped table-bordered" style="font-size: 12px">
					<thead>
					<tr>
						<th width="8%" scope="col"><div align="center">TRAVEL DATE</div></th>
						<th width="18%" scope="col"><div align="center">ORIGIN</div></th>
						<th width="18%" scope="col"><div align="center">DESTINATION</div></th>
						<th colspan="3" scope="col"><div align="center">ACTUAL TIME </div></th>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td width="7%"><div align="center"><strong>Start</strong></div></td>
						<td width="7%"><div align="center"><strong>End</strong></div></td>
						<td width="7%"><div align="center"><strong>Duration</strong></div></td>
					</tr>
					</thead>
					<tbody>
					<?php
					#echo $db->last_query;
					while($rDisp = $db->fetch_array($qDisp)):
						$rID = $rDisp['tod_id'];
						$est_mins = ($rDisp['est_time_from'] && $rDisp['est_time_to']) ? functions::min_diff($rDisp['est_time_from'],$rDisp['travel_date'],$rDisp['est_time_to'],$rDisp['travel_date']) : 0;
					?>
					<tr>
						<td><div align="center"><?php echo functions::datearr($rDisp['travel_date']);?></div></td>
						<td><div align="center"><?php echo $rDisp['origin'];?></div></td>
						<td><div align="center"><?php echo $rDisp['destination'];?></div></td>
						<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_from']);?></div></td>
						<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_to']);?></div></td>
						<td><div align="center"><?php echo functions::min_to_hour($rDisp['mins_travel']);?></div></td>
					</tr>
					<?php endwhile;?>
					</tbody>
				</table><br><br>
				<?php }//End num_rows ?>
				<div style="padding:10px 0px 10px 0px; display:none;">
					Record from File:
					<?php
					$attRecord='';
					$arrAttR=array();
					$att_record = $db->getValue('emp_attendance_detail','att_record',array('eatd_id'=>$eatdid,'eat_id'=>$eatid));
					if($att_record){
						$arrTR = explode(' ', $att_record);
						$countTR = count($arrTR);
						$temp=2;
						foreach( $arrTR as $ar):
							if($ar)
								$arrAttR[]=$ar;
							$attRecord.='<b>'.$ar.'</b>';
							if($countTR > $temp)
								$attRecord.=' | ';
							$temp++;
						endforeach;
					}
					#echo $attRecord;
					?>
					<div align="left">
						<table border="0">
							<tr>
								<?php foreach( $arrAttR as $ar):  ?>
								<td style="padding:10px 15px 10px 0px;"><strong><?php echo functions::MilToTwelve($ar) ?></strong></td>
								<?php endforeach; ?>
							</tr>
							<tr>
								<?php
									foreach( $arrAttR as $ar):
										$tmpTime = $ar.':00';
										if(functions::valid_time($tmpTime)){
								?>
								<td>
									<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($tmpTime)?>">am in</a><br>
									<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmOut=<?php echo functions::encode($tmpTime)?>">am out</a><br>
									<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmPmIn=<?php echo functions::encode($tmpTime)?>">pm in</a><br>
									<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmPmOut=<?php echo functions::encode($tmpTime)?>">pm out</a>
								</td>
								<?php 	}
								endforeach; ?>
							</tr>
						</table>
					</div>
				</div>
				<table width="100%" align="center" border="1" class="table table-bordered" style="font-size: 12px;">
					<thead>
						<tr>
							<th height="auto" rowspan="2" width="10%" style="padding-left:10px; line-height: 1.1;">DATE</th>
							<th colspan="2" style="padding: 4px 8px; line-height: 1.1;"><div align="center">MORNING</div></th>
							<th colspan="2" style="padding: 4px 8px; line-height: 1.1;"><div align="center">AFTERNOON</div></th>
						</tr>
						<tr>
							<th width="12%" style="padding: 4px 8px; line-height: 1.1;"><div align="center">Actual In (Assigned)</div></th>
							<th width="12%" style="padding: 4px 8px; line-height: 1.1;"><div align="center">Actual Out (Assigned)</div></th>
							<th width="12%" style="padding: 4px 8px; line-height: 1.1;"><div align="center">Actual In (Assigned)</div></th>
							<th width="12%" style="padding: 4px 8px; line-height: 1.1;"><div align="center">Actual Out (Assigned)</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$qEmpAtt = $db->select('emp_attendance_detail','*',array('eatd_id'=>$eatdid,'eat_id'=>$eatid));
						#echo $db->last_query;
						$dateAttendance='';
						$countRecord=0;
						while($dly = $db->fetch_array($qEmpAtt)):
							$countRecord++;
							$am_in_default = ($dly['am_in_assign']) ? $dly['am_in_assign'] : '--:--';
							$am_in_assign = ($dly['am_in_assign']) ? functions::MilToTwelve($dly['am_in_assign']) : '--:--';
							$am_in = ($dly['am_in']);

							$am_out_default = ($dly['am_out_assign']) ? $dly['am_out_assign'] : '--:--';
							$am_out_assign = ($dly['am_out_assign']) ? functions::MilToTwelve($dly['am_out_assign']) : '--:--';
							$am_out = ($dly['am_out']);

							$pm_in_default = ($dly['pm_in_assign']) ? $dly['pm_in_assign'] : '--:--';
							$pm_in_assign = ($dly['pm_in_assign']) ? functions::MilToTwelve($dly['pm_in_assign']) : '--:--';
							$pm_in = ($dly['pm_in']);

							$pm_out_default = ($dly['pm_out_assign']) ? $dly['pm_out_assign'] : '--:--';
							$pm_out_assign = ($dly['pm_out_assign']) ? functions::MilToTwelve($dly['pm_out_assign']) : '--:--';
							$pm_out = ($dly['pm_out']);
							$dateAttendance = $dly['eat_date'];
							#echo (isset($_REQUEST['tmPmIn']) && !empty($_REQUEST['tmPmIn']) ) ? functions::decode($_REQUEST['tmPmIn']) : '';
							$am_in = (isset($_REQUEST['tmAmIn']) && !empty($_REQUEST['tmAmIn']) ) ? functions::decode($_REQUEST['tmAmIn']) : $dly['am_in'];
							$am_out = (isset($_REQUEST['tmAmOut']) && !empty($_REQUEST['tmAmOut']) ) ? functions::decode($_REQUEST['tmAmOut']) : $dly['am_out'];
							$pm_in = (isset($_REQUEST['tmPmIn']) && !empty($_REQUEST['tmPmIn']) ) ? functions::decode($_REQUEST['tmPmIn']) : $dly['pm_in'];
							$pm_out = (isset($_REQUEST['tmPmOut']) && !empty($_REQUEST['tmPmOut']) ) ? functions::decode($_REQUEST['tmPmOut']) : $dly['pm_out'];
						?>
						<tr>
							<td style="padding-left:10px;">
								<?php
								echo functions::datearr($dly['eat_date']);
								echo ' -  ('.$dly['eat_day'].')';
								?>
							</td>
							<td style="padding: 12px 8px;">
								<div align="left">
									<?php if($attendance_ready==0 && $att_confirm==0){?>
									<input id="selAmIn" type="text" name="selAmIn">&nbsp;&nbsp;&nbsp;<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($am_in_default)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&tmPmOut=<?php echo functions::encode($pm_out)?>"><i>(<?php echo $am_in_assign;?>)</i></a>
									<div style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Time log Detected:</div>
									<div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;">
										<a class="time-log-pill <?php echo ($am_in == '--:--') ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode('--:--')?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&tmPmOut=<?php echo functions::encode($pm_out)?>">--:--</a>
										<?php
											$found=0;
											foreach( $arrAttR as $ar):
												if( functions::valid_time($ar.':00') )
													$tmpTime = $ar.':00';
												else if( functions::valid_time($ar) )
													$tmpTime = $ar;
												if(functions::valid_time($tmpTime)){
													$isActive = ($am_in == $tmpTime && !$found);
													if($isActive) { $found=1; }
												?>
												<a class="time-log-pill <?php echo $isActive ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($tmpTime)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&tmPmOut=<?php echo functions::encode($pm_out)?>"><?php echo functions::MilToTwelve($ar);?></a>
												<?php
												}
											endforeach;
										}
										else{
											$am_in_disp = ($am_in) ? date('h:i a',strtotime(date('Y-m-d').' '.$am_in)) : '--:--';
											echo '<div align="center"><strong>'.$am_in_disp.'</strong> <i>('.$am_in_assign.')</i></div>';
										}
										?>
									</div>
								</div>
							</td>
							<td style="padding: 12px 8px;">
								<div align="left">
									<?php if($attendance_ready==0 && $att_confirm==0){?>
									<input id="selAmOut" type="text" name="selAmOut">&nbsp;&nbsp;&nbsp;<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($am_out_default)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&tmPmOut=<?php echo functions::encode($pm_out)?>"><i>(<?php echo $am_out_assign;?>)</i></a>
									<div style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Time log Detected:</div>
									<div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;">
										<a class="time-log-pill <?php echo ($am_out == '--:--') ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode('--:--')?>&tmPmIn=<?php echo functions::encode($pm_in)?>&tmPmOut=<?php echo functions::encode($pm_out)?>">--:--</a>
										<?php
											$found=0;
											foreach( $arrAttR as $ar):
												if( functions::valid_time($ar.':00') )
													$tmpTime = $ar.':00';
												else if( functions::valid_time($ar) )
													$tmpTime = $ar;
												if(functions::valid_time($tmpTime)){
													$isActive = ($am_out == $tmpTime && !$found);
													if($isActive) { $found=1; }
												?>
												<a class="time-log-pill <?php echo $isActive ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($tmpTime)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&tmPmOut=<?php echo functions::encode($pm_out)?>"><?php echo functions::MilToTwelve($ar);?></a>
												<?php
												}
											endforeach;
										}
										else{
											$am_out_disp = ($am_out) ? date('h:i a',strtotime(date('Y-m-d').' '.$am_out)) : '--:--';
											echo '<div align="center"><strong>'.$am_out_disp.'</strong> <i>('.$am_out_assign.')</i></div>';
										}
										?>
									</div>
								</div>
							</td>
							<td style="padding: 12px 8px;">
								<div align="left">
									<?php if($attendance_ready==0 && $att_confirm==0){ ?>
									<input id="selPmIn" type="text" name="selPmIn">&nbsp;&nbsp;&nbsp;<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($pm_in_default)?>&tmPmOut=<?php echo functions::encode($pm_out)?>"><i>(<?php echo $pm_in_assign;?>)</i></a>
									<div style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Time log Detected:</div>
									<div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;">
										<a class="time-log-pill <?php echo ($pm_in == '--:--') ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode('--:--')?>&tmPmOut=<?php echo functions::encode($pm_out)?>">--:--</a>
										<?php
											$found=0;
											foreach( $arrAttR as $ar):
												if( functions::valid_time($ar.':00') )
													$tmpTime = $ar.':00';
												else if( functions::valid_time($ar) )
													$tmpTime = $ar;
												if(functions::valid_time($tmpTime)){
													$isActive = ($pm_in == $tmpTime && !$found);
													if($isActive) { $found=1; }
												?>
												<a class="time-log-pill <?php echo $isActive ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&empid=<?php echo functions::encode($emp_id)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($tmpTime)?>&tmPmOut=<?php echo functions::encode($pm_out)?>"><?php echo functions::MilToTwelve($ar);?></a>
												<?php
												}
											endforeach;
										}
										else{
											$pm_in_disp = ($pm_in) ? date('h:i a',strtotime(date('Y-m-d').' '.$pm_in)) : '--:--';
											echo '<div align="center"><strong>'.$pm_in_disp.'</strong> <i>('.$pm_in_assign.')</i></div>';
										}
										?>
									</div>
								</div>
							</td>
							<td style="padding: 12px 8px;">
								<div align="left">
									<?php if($attendance_ready==0 && $att_confirm==0){?>
									<input id="selPmOut" type="text" name="selPmOut">&nbsp;&nbsp;&nbsp;<a class="badge-time-link" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&empid=<?php echo functions::encode($emp_id)?>&tmPmOut=<?php echo functions::encode($pm_out_default)?>"><i>(<?php echo $pm_out_assign;?>)</i></a>
									<div style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Time log Detected:</div>
									<div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;">
										<a class="time-log-pill <?php echo ($pm_out == '--:--') ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&empid=<?php echo functions::encode($emp_id)?>&tmPmOut=<?php echo functions::encode('--:--')?>">--:--</a>
										<?php
											$found=0;
											foreach( $arrAttR as $ar):
												if( functions::valid_time($ar.':00') )
													$tmpTime = $ar.':00';
												else if( functions::valid_time($ar) )
													$tmpTime = $ar;
												if(functions::valid_time($tmpTime)){
													$isActive = ($pm_out == $tmpTime && !$found);
													if($isActive) { $found=1; }
												?>
												<a class="time-log-pill <?php echo $isActive ? 'active' : ''; ?>" href="?eatid=<?php echo functions::encode($eatid)?>&eatdid=<?php echo functions::encode($eatdid)?>&tmAmIn=<?php echo functions::encode($am_in)?>&tmAmOut=<?php echo functions::encode($am_out)?>&tmPmIn=<?php echo functions::encode($pm_in)?>&tmPmOut=<?php echo functions::encode($tmpTime)?>"><?php echo functions::MilToTwelve($ar);?></a>
												<?php
												}
											endforeach;
										}
										else{
											$pm_out_disp = ($pm_out) ? date('h:i a',strtotime(date('Y-m-d').' '.$pm_out)) : '--:--';
											echo '<div align="center"><strong>'.$pm_out_disp.'</strong> <i>('.$pm_out_assign.')</i></div>';
										}
										?>
									</div>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table>
				<?php
					if($countRecord){
						if($attendance_ready==0 && $att_confirm==0){?>
				<div align="center" style="padding-top: 40px;">Reason: <textarea name="txRemarks" id="txRemarks" style="width:400px;"></textarea></div>
				<div align="center" style="padding-top: 20px;">
					<button type="button" id="btnSaveTrigger" class="btn btn-small btn-primary"> SAVE CHANGES </button>
					<input type="hidden" name="btnSave" value="1">
				</div>
				<?php
						}
						else{
							echo '<div align="center" style="padding-top: 40px;">Attendance confirmed, Cannot be modified!</div>';
						}
					}
				$qAttMod = $db->select('emp_attendance_adjustment','*',array('eatd_id'=>$eatdid),'ORDER BY change_date, change_time');
				if($db->num_rows($qAttMod)){
				?>
				<div class="history-section-title">Adjustment History</div>
				<table width="100%" align="center" border="1" class="table table-striped table-bordered" style="font-size: 12px;">
					<thead>
						<tr>
							<th rowspan="2" width="12%">Modified Time</th>
							<th rowspan="2" width="12%">Modified By</th>
							<th rowspan="2" width="15%">Remarks</th>
							<th colspan="2">Morning</th>
							<th colspan="2">Afternoon</th>
						</tr>
						<tr>
							<th width="15%">In</th>
							<th width="15%">Out</th>
							<th width="15%">In</th>
							<th width="15%">Out</th>
						</tr>
					</thead>
					<tbody>
						<?php while($rAM = $db->fetch_array($qAttMod)): ?>
						<tr>
							<td><div align="center"><?php echo functions::datearr($rAM['change_date']).' - '.functions::MilToTwelve($rAM['change_time'])?></div></td>
							<td><div align="center"><?php echo ($rAM['user_id']) ? $db->getValue('users','concat(lname,", ",fname)',array('user_id'=>$rAM['user_id'])) : 'System';?></div></td>
							<td><div align="center"><?php echo $rAM['remarks']?></div></td>
							<td>
								<div align="center">
									<?php
									if( $rAM['am_in_org'] != $rAM['am_in_new'] ){
										echo ($rAM['am_in_org']) ? functions::MilToTwelve($rAM['am_in_org']) : '(--:--)';
										echo ' -> ';
										echo ($rAM['am_in_new']) ? functions::MilToTwelve($rAM['am_in_new']) : '(--:--)';
									}
									else
										echo ($rAM['am_in_org']) ? functions::MilToTwelve($rAM['am_in_org']) : '(--:--)';
									?>
								</div>
							</td>
							<td>
								<div align="center">
									<?php
									if( $rAM['am_out_org'] != $rAM['am_out_new'] ){
										echo ($rAM['am_out_org']) ? functions::MilToTwelve($rAM['am_out_org']) : '(--:--)';
										echo ' -> ';
										echo ($rAM['am_out_new']) ? functions::MilToTwelve($rAM['am_out_new']) : '(--:--)';
									}
									else
										echo ($rAM['am_out_org']) ? functions::MilToTwelve($rAM['am_out_org']) : '(--:--)';
									?>
								</div>
							</td>
							<td>
								<div align="center">
									<?php
									if( $rAM['pm_in_org'] != $rAM['pm_in_new'] ){
										echo ($rAM['pm_in_org']) ? functions::MilToTwelve($rAM['pm_in_org']) : '(--:--)';
										echo ' -> ';
										echo ($rAM['pm_in_new']) ? functions::MilToTwelve($rAM['pm_in_new']) : '(--:--)';
									}
									else
										echo ($rAM['pm_in_org']) ? functions::MilToTwelve($rAM['pm_in_org']) : '(--:--)';
									?>
								</div>
							</td>
							<td>
								<div align="center">
									<?php
									if( $rAM['pm_out_org'] != $rAM['pm_out_new'] ){
										echo ($rAM['pm_out_org']) ? functions::MilToTwelve($rAM['pm_out_org']) : '(--:--)';
										echo ' -> ';
										echo ($rAM['pm_out_new']) ? functions::MilToTwelve($rAM['pm_out_new']) : '(--:--)';
									}
									else
										echo ($rAM['pm_out_org']) ? functions::MilToTwelve($rAM['pm_out_org']) : '(--:--)';
									?>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br>
				<?php }?>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->

<!-- Modern Confirmation Dialog Modal -->
<div class="modal fade hide" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="confirmModalLabel"><i class="halflings-icon white question-sign"></i> Save Attendance Changes</h3>
	</div>
	<div class="modal-body">
		<p>Are you sure you want to save these attendance adjustments? This action will update the log entries for the employee.</p>
	</div>
	<div class="modal-footer">
		<button class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button id="btnConfirmSubmit" class="btn btn-primary">Confirm & Save</button>
	</div>
</div>

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
<script src="../js/timepicker.js"></script>
<script>
$('#selAmIn').timepicker({
	time:'<?php echo ($am_in) ? $am_in : '--:--'; ?>',
	<?php if($attendance_ready==1){echo 'editable: false';} ?>
});
$('#selAmOut').timepicker({
	time:'<?php echo ($am_out) ? $am_out : '--:--'; ?>',
	<?php if($attendance_ready==1){echo 'editable: false';} ?>
});
$('#selPmIn').timepicker({
	time:'<?php echo ($pm_in) ? $pm_in : '--:--'; ?>',
	<?php if($attendance_ready==1){echo 'editable: false';} ?>
});
$('#selPmOut').timepicker({
	time:'<?php echo ($pm_out) ? $pm_out : '--:--'; ?>',
	<?php if($attendance_ready==1){echo 'editable: false';} ?>
});

$(document).ready(function() {
	$('#btnSaveTrigger').on('click', function(e) {
		e.preventDefault();
		$('#confirmModal').modal('show');
	});

	$('#btnConfirmSubmit').on('click', function() {
		$('#confirmModal').modal('hide');
		$('#attendanceForm').submit();
	});
});
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