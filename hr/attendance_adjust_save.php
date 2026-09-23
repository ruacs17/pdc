<?php require_once('authorize.php');
?>
<html>
    <head>
        <link id="base-style" href="../css/loader.css" rel="stylesheet">
    </head>
    <body>
        Please wait while attendance is still processing........<br>
        <div id="spinner"></div>
    </body>
</html>
<?php
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$arrAttendanceRecord = array();
if( isset($_POST['btnSave']) ){
    $selAmInHr = (isset($_POST['selAmInHr']) && !empty($_POST['selAmInHr']) ) ? functoins::decode($_POST['selAmInHr']) : '';
    $qEmpAttInfo = $db->select('emp_attendance_detail','*',array('eatd_id'=>$eatdid));
    $rEAI = $db->fetch_array($qEmpAttInfo);
    $selAmInHr = (isset($_POST['selAmInHr']) && !empty($_POST['selAmInHr']) ) ? trim($_POST['selAmInHr']) : '';
    $selAmInMn = (isset($_POST['selAmInMn']) && !empty($_POST['selAmInMn']) ) ? trim($_POST['selAmInMn']) : '';

    $selAmOutHr = (isset($_POST['selAmOutHr']) && !empty($_POST['selAmOutHr']) ) ? trim($_POST['selAmOutHr']) : '';
    $selAmOutMn = (isset($_POST['selAmOutMn']) && !empty($_POST['selAmOutMn']) ) ? trim($_POST['selAmOutMn']) : '';

    $selPmInHr = (isset($_POST['selPmInHr']) && !empty($_POST['selPmInHr']) ) ? trim($_POST['selPmInHr']) : '';
    $selPmInMn = (isset($_POST['selPmInMn']) && !empty($_POST['selPmInMn']) ) ? trim($_POST['selPmInMn']) : '';

    $selPmOutHr = (isset($_POST['selPmOutHr']) && !empty($_POST['selPmOutHr']) ) ? trim($_POST['selPmOutHr']) : '';
    $selPmOutMn = (isset($_POST['selPmOutMn']) && !empty($_POST['selPmOutMn']) ) ? trim($_POST['selPmOutMn']) : '';

    $setAmIn = ($selAmInHr && $selAmInMn) ? $selAmInHr.':'.$selAmInMn : NULL;
    $setAmOut = ($selAmOutHr && $selAmOutMn) ? $selAmOutHr.':'.$selAmOutMn : NULL;
    $setPmIn = ($selPmInHr && $selPmInMn) ? $selPmInHr.':'.$selPmInMn : NULL;
    $setPmOut = ($selPmOutHr && $selPmOutMn) ? $selPmOutHr.':'.$selPmOutMn : NULL;
    $db->insertPrint('emp_attendance_adjustment',array('eatd_id'=>$rEAI['eatd_id'],'eta_date'=>$rEAI['eta_date'],'eta_day'=>$rEAI['eta_day'],
        'am_in'=>$setAmIn,'am_in_org'=>$rEAI['am_in_org'],
        'am_out'=>$setAmOut,'am_out_org'=>$rEAI['am_out_assign'],
        'pm_in'=>$setPmIn,'pm_in_assign'=>$rEAI['pm_in_assign'],
        'pm_out'=>$setPmOut,'pm_out_assign'=>$rEAI['pm_out_assign'],
        'ot_in'=>$rEAI['ot_in'],'ot_out'=>$rEAI['ot_out'],
        'dutyMin'=>$rEAI['dutyMin'],'otMin'=>$rEAI['otMin'],
        'lateMin'=>$rEAI['lateMin'],'underMin'=>$rEAI['underMin'],
        'change_date'=>date('Y-m-d'),'change_time'=>date('H:i:s'),'user_id'=>$user_id));
    
    $emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rEAI['emp_id']));
    $arrAttendanceRecord[$emp_no][$rEAI['eta_date']] = array('amin'=>$setAmIn,'amout'=>$setAmOut,'pmin'=>$setPmIn,'pmout'=>$setPmOut,'otin'=>$rEAI['ot_in'],'otout'=>$rEAI['ot_out']);
    #$db->updatePrint('emp_attendance_detail',array('am_in'=>$setAmIn,'am_out'=>$setAmOut,'pm_in'=>$setPmIn,'pm_out'=>$setPmOut));

    
}



function dayName($date=''){
    #$date = '2014-02-25';
    if($date)
        return date('l', strtotime($date));
    else
        return '';
}

function diffComp($actualAmIn='',$assignAmIn='',$actualAmOut='',$assignAmOut='',
        $actualPmIn='',$assignPmIn='',$actualPmOut='',$assignPmOut='',$actualOtIn,$actualOtOut,&$late=0,&$undertime=0,&$dutyHours=0,&$otHours=0){
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

    if( $assignAmIn ){
        if($actualAmIn){
            if( strtotime($actualAmIn) > strtotime($assignAmIn) ){
                $late += functions::min_diff($assignAmIn,$cDate,$actualAmIn,$cDate);
            }
        }
    }
    if($assignAmOut){
        if($actualAmOut){
            if( strtotime($actualAmOut) < strtotime($assignAmOut) ){
                if( strtotime($actualAmOut) < strtotime($assignAmIn))//If actual out is lesser the assign in
                    $undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
                else
                    $undertime += functions::min_diff($actualAmOut,$cDate,$assignAmOut,$cDate);
            }
        }
        else{
            $undertime += functions::min_diff($assignAmIn,$cDate,$assignAmOut,$cDate);
        }
    }
    if($assignPmIn){
        if($actualPmIn){
            if( strtotime($actualPmIn) > strtotime($assignPmIn) ){
                $late += functions::min_diff($assignPmIn,$cDate,$actualPmIn,$cDate);
            }
        }
    }
    if($assignPmOut){
        if($actualPmOut){
            if( strtotime($actualPmOut) < strtotime($assignPmOut) ){
                if( strtotime($actualPmOut) < strtotime($assignPmIn))//If actual out is lesser the assign in
                    $undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
                else
                    $undertime += functions::min_diff($actualPmOut,$cDate,$assignPmOut,$cDate);
            }
        }
        else{
            if($assignPmOut && $assignPmIn)
            $undertime += functions::min_diff($assignPmIn,$cDate,$assignPmOut,$cDate);
        }
    }
    $dutyHours -= $undertime + $late;
    $dutyHours = ( $dutyHours >=0 ) ? $dutyHours : 0;
}
$totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;

foreach($arrAttendanceRecord as $employee => $calDate):
    $totalDutyHours=0;
    $monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
    $am_in='';$am_out='';$pm_in='';$pm_out='';
    $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));

    $qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'ORDER BY es_date DESC, es_id DESC');
    if( $db->num_rows($qSal) > 0 ){
        $rSal = $db->fetch_array($qSal);
        $txSalMonth = functions::formatMoney($rSal['es_salary'],4);
        $txSalDay = functions::formatMoney($rSal['es_daily'],4);
        $txSalHour = functions::formatMoney($rSal['es_hourly'],4);
        $txSalMin = functions::formatMoney($rSal['es_minute'],4);
    }
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
        $totalDutyHours += $dutyHours + $otHours;
        $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
        $attendance_date = $db->getValue('emp_attendance_detail','eat_date',array('emp_id'=>$emp_id,'eat_date'=>$cDate));
        if($attendance_date){
            $arrUpdateAtt=array('am_in_assign'=>$am_in_assign,'am_out_assign'=>$am_out_assign,'pm_in_assign'=>$pm_in_assign,'pm_out_assign'=>$pm_out_assign,'dutyMin'=>$totalDutyHours,'otMin'=>$otHours,'lateMin'=>$late,'underMin'=>$undertime);
            $saved_q = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id,'eat_date'=>$cDate));
            $saved_r = $db->fetch_array($saved_q);
            $saved_am_in = $saved_r['am_in'];
            $saved_am_out = $saved_r['am_out'];
            $saved_pm_in = $saved_r['pm_in'];
            $saved_pm_out = $saved_r['pm_out'];
            if(empty($saved_am_in))
                $arrUpdateAtt = array_merge($arrUpdateAtt,array('am_in'=>$am_in));
            if(empty($saved_am_out))
                $arrUpdateAtt = array_merge($arrUpdateAtt,array('am_out'=>$am_out));
            if(empty($saved_pm_in))
                $arrUpdateAtt = array_merge($arrUpdateAtt,array('pm_in'=>$pm_in));
            if(empty($saved_pm_out))
                $arrUpdateAtt = array_merge($arrUpdateAtt,array('pm_out'=>$pm_out));
            if(count($arrUpdateAtt)){
                $db->update('emp_attendance_detail',$arrUpdateAtt,array('emp_id'=>$emp_id,'eat_date'=>$cDate));
                #echo '<br>'.$db->last_query;
            }
        }
    endforeach;
endforeach;
functions::sendTo('attendance_summary_view.php?eatid='.functions::encode($eat_id));
?>