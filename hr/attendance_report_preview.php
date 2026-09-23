<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$filename='';
$uploaded_file='';$filetype='';
$dateRange='';
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$eas_id='';
if($eatid){
    $uploaded_file = '../attendance/'.$db->getValue('emp_attendance','excelfile',array('eat_id'=>$eatid));
    $filetype = $db->getValue('emp_attendance','filetype',array('eat_id'=>$eatid));
    $eas_id = $db->getValue('emp_attendance','eas_id',array('eat_id'=>$eatid));
    $dateRange = $db->getValue('emp_attendance','concat(date_start,"~",date_end)',array('eat_id'=>$eatid));
    /*if( $db->getValue('emp_attendance','upload_done',array('eat_id'=>$eatid))==1 ){
        echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
        echo 'Please wait while attendance is still processing........<br>';
        echo '<div id="spinner"></div>';
        functions::sendTo('attendance_summary_view.php?eatid='.functions::encode($eatid));
        die();
    }*/   
}
$attendance_file = (isset($_REQUEST['fn']) && !empty($_REQUEST['fn']) ) ? functions::decode($_REQUEST['fn']) : '';
if($attendance_file){
    $arrRequest = unserialize($attendance_file);
    $uploaded_file = isset($arrRequest['filename']) ? $arrRequest['filename'] : '';
    $filetype = isset($arrRequest['filetype']) ? $arrRequest['filetype'] : '';
}
#$uploaded_file='../attendance/20190703.xlsx';
#$filetype='xlsx';
if( isset($_POST['btnCancel']) ){
    if( $db->getValue('emp_attendance','excelfile',array('eat_id'=>$eatid)) ){
        $db->update('emp_attendance',array('upload_done'=>1),array('eat_id'=>$eatid));
    }
    functions::sendTo('attendance_report_upload.php?eatid='.functions::encode($eatid));
    die();
}
if( isset($_POST['btnSave']) ){
    echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
    echo 'Please wait while attendance is still processing........<br>';
    echo '<div id="spinner"></div>';
    functions::sendTo('attendance_report_upload_save.php?eatid='.functions::encode($eatid));
    die();
}

$arrSelEmp = array();
$arrGroupEmp = array();
if($uploaded_file){
    $qEmpNos = $db->select('employee','*',array(),'WHERE emp_no !="" ');
    while($rEmpNos = $db->fetch_array($qEmpNos)):
        $arrSelEmp[$rEmpNos['emp_no']] = $rEmpNos['emp_no'];
    endwhile;
    $qGroupEmpNos = $db->select('emp_assign_detail ead, employee e','*',array('eas_id'=>$eas_id),'AND ead.emp_id=e.emp_id ORDER BY lname');
    while($rGroupEmpNos = $db->fetch_array($qGroupEmpNos)):
        $arrGroupEmp[$rGroupEmpNos['emp_no']] = $rGroupEmpNos['lname'].', '.$rGroupEmpNos['fname'].' '.$rGroupEmpNos['mname'];
    endwhile;
    $totalRow = 0;
    $content=array();
    if($filetype==='xlsx'){
        require_once('../class/read_excel_xlsx.php');
        if ( $xlsx = read_excel_xlsx::parse($uploaded_file) ) {
            $content = $xlsx->rows() ;
        } else {
            #echo read_excel_xlsx::parseError();
        }
        $totalRow = count($content);
    }
    if($filetype==='xls'){
        require_once('../class/read_excel_xls.php');
        if ( $xls = read_excel_xls::parse($uploaded_file) ) {
            $content = $xls->rows() ;
        } else {
            #echo read_excel_xls::parseError();
        }
        $totalRow = count($content);
    }
//Saving the record Start
    $arrAttendanceRecord=array();
    $arrAttendanceDate = isset($content[3]) ? $content[3] : array();
    #echo $dateRange = isset($content[2][2]) ? $content[2][2] : '';
    $dateRangeFrom='';$dateRangeTo='';$yearFrom='';$dateFrom='';$monFrom='';
    if($dateRange){
        $dateRangeExp = explode('~',$dateRange);
        $dateRangeFrom = isset($dateRangeExp[0]) ? trim($dateRangeExp[0]) : '';
        $dateRangeTo = isset($dateRangeExp[1]) ? trim($dateRangeExp[1]) : '';
        $yearFrom = substr($dateRangeFrom,0,4);
        $monFrom = substr($dateRangeFrom,5,2);
        $dateFrom = substr($dateRangeFrom,8,2);
    }
    $arrDate=array();
    $dates = functions::date_diff($dateRangeFrom,$dateRangeTo);
    if($dates){
        for($i=0;$i<=$dates;$i++):
            $succeeding_date = functions::AddDay($dateRangeFrom,$i);
            $daysDate = date('d', strtotime($succeeding_date));
            $arrDate[]=$daysDate;
        endfor;   
    }

/*
    foreach($arrAttendanceDate as $aAindx => $aAVal):
        if($aAVal)
            $arrDate[$aAindx]=$aAVal;
    endforeach;
*/
    $emp_no=''; $emp_name='';
    #echo '<pre>';print_r($arrDate);echo '</pre>';
    $arrEmpNos = array();
    $arrFoundEmp = array();
    if($totalRow){//has Rows
        for($t=4;$t<$totalRow; $t++):
            if( ($t%2)==0 ){
                $emp_no = isset($content[$t][2]) ? $content[$t][2] : '';
                $emp_name = isset($content[$t][10]) ? $content[$t][10] : '';

                $emp_name_reg = $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_no'=>$emp_no));
                $empname = ($emp_name_reg) ? ucwords(strtolower($emp_name_reg)) : $emp_name;
                if($emp_no)
                $arrFoundEmp[$emp_no]=$empname;

                #if( isset($arrSelEmp[$emp_no]) ){
                    $arrEmpNos['empID'][$emp_no]=$db->getValue('employee','lname',array('emp_no'=>$emp_no));
                    $countAttendance=0;
                    foreach($arrDate as $dailyIndx => $dailyVal):
                        if($dailyVal){
                            $monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
                            $dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
                            if($dateFrom <= $dailyVal)
                                $dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
                            else{
                                $monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
                                $dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
                            }
                            if( isset($arrAttendanceDaily[$dailyIndx]) && $arrAttendanceDaily[$dailyIndx]){ 
                                $countAttendance++;
                                $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
                            }
                            else{
                                $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
                            }
                        }
                    endforeach;
                    if($countAttendance==0){//No attendance in excel
                        foreach($arrDate as $dailyIndx => $dailyVal):
                            $monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
                            $dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
                            if($dateFrom <= $dailyVal)
                                $dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
                            else{
                                $monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
                                $dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
                            }
                            $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
                        endforeach;
                    }
                #}//Registered Emp Number
            }

            if( ($t%2)==1 ){
                #if( isset($arrSelEmp[$emp_no]) ){
                    $arrAttendanceDaily = isset($content[$t]) ? $content[$t] : array();
                    foreach($arrDate as $dailyIndx => $dailyVal):
                        $amIn='';$amOut='';$pmIn='';$pmOut='';$otIn='';$otOut='';
                        if( isset($arrAttendanceDaily[$dailyIndx]) && $arrAttendanceDaily[$dailyIndx]){
                            $tme = $arrAttendanceDaily[$dailyIndx];
                            if($tme){
                                $amIn='';$amOut='';$pmIn='';$pmOut='';$otIn='';$otOut='';
                                for($x=0; $x<50; $x+=5):
                                    $timePerDay = substr($tme,$x,5);
                                    if($timePerDay){
                                        if($amIn=="")
                                            $amIn=$timePerDay;
                                        elseif($amOut=='')
                                            $amOut=$timePerDay;
                                        elseif($pmIn=='')
                                            $pmIn=$timePerDay;
                                        elseif($pmOut=='')
                                            $pmOut=$timePerDay;
                                        elseif($otIn=='')
                                            $otIn=$timePerDay;
                                        elseif($otOut=='')
                                            $otOut=$timePerDay;
                                    }
                                endfor;
                                $monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
                                $dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
                                if($dateFrom <= $dailyVal)
                                    $dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
                                else{
                                    $monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
                                    $dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
                                }
                                $real_emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$emp_no));
                                $otIn = $otInFile = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dailyDate));
                                $otOut = $otOutFile = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dailyDate));
                                #$otIn = ($otInFile) ? $otInFile : $otIn;
                                #$otOut = ($otOutFile) ? $otOutFile : $otOut;
                                $arrAttendanceRecord[$emp_no][$dailyDate] = array('amin'=>$amIn,'amout'=>$amOut,'pmin'=>$pmIn,'pmout'=>$pmOut,'otin'=>$otIn,'otout'=>$otOut);
                            }
                        }
                    endforeach;
                #}//Registered Emp Number
            }
        endfor;

    }//has Rows

    asort($arrFoundEmp);
    
    $arrRegisteredGroup = array();
    $arrRegisteredOthers = array();
    $arrNotRegistered = array();

    foreach($arrGroupEmp as $enum => $ename):
        #if( isset($arrFoundEmp[$enum]) )
            $arrRegisteredGroup[$enum]=$ename;
    endforeach;
    foreach($arrFoundEmp as $enum => $ename):
        if( isset($arrSelEmp[$enum]) ){
            if( isset($arrGroupEmp[$enum]) )
                ;#$arrRegisteredGroup[$enum]=$ename;
            else
                $arrRegisteredOthers[$enum]=$ename;
        }
        else
            $arrNotRegistered[$enum]=$ename;
    endforeach;

    $arrFoundEmp=array();
    foreach($arrRegisteredGroup as $enum => $ename):
        $arrFoundEmp[$enum]=$ename;
    endforeach;
    foreach($arrRegisteredOthers as $enum => $ename):
        $arrFoundEmp[$enum]=$ename;
    endforeach;
    foreach($arrNotRegistered as $enum => $ename):
        $arrFoundEmp[$enum]=$ename;
    endforeach;
    #print_r($arrEmpNos['empID']);
    #echo '<pre>';print_r($arrAttendanceRecord);echo '</pre>';
    #echo '<pre>';print_r($arrAttendanceRecord[880046]);echo '</pre>';
    #die();
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
    <title>Employee Attendance Preview</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="attendance_report_preview.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Attendance Preview</a></li>
                <li><a href="attendance_report_upload.php?eatid=<?php echo functions::encode($eatid)?>">Upload Att. Log</a></li>
            </ul>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE PREVIEW</h2></div>
<?php
    $totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;
    foreach($arrFoundEmp as $employee => $empname):
        $calDate = isset( $arrAttendanceRecord[$employee] ) ? $arrAttendanceRecord[$employee] : array();
        $totalDutyHours=0;
        $monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
        $am_in='';$am_out='';$pm_in='';$pm_out='';
        $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
        if($emp_id){
            echo $employee.' - '.$db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_id'=>$emp_id));
            if( isset($arrRegisteredGroup[$employee]) )
                echo '&nbsp;&nbsp;<i class="icon-ok" title="group member"></i>';
            else if( isset($arrRegisteredOthers[$employee]) )
                echo '&nbsp;&nbsp;<i class="icon-group" title="not belong to the group"></i> *Data will not be saved!';
        }
        else{
            echo $employee.' - '.$empname.' (Not Registered) *Data will not be saved!';
        }
        if(count($calDate)==0)
            echo '<div style="background-color:red; color:yellow; width:15%;">&nbsp;&nbsp;No Attendance Record!</div>';

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
        ?>
                <table width="98%" border="1" style="font-size: 12px;">
                    <tr>
                        <td>DATE</td>
                        <td colspan="2"><div align="center">Morning</div></td>
                        <td colspan="2"><div align="center">Afternoon</div></td>
                        <td colspan="2"><div align="center">Overtime</div></td>
                        <td colspan="4"><div align="center">Diff</div></td>
                    </tr>
                    <tr>
                        <td width="9%">&nbsp;</td>
                        <td width="12%"><div align="center">In<br>Actual <i>(Assigned)</i></div></td>
                        <td width="12%"><div align="center">Out<br>Actual <i>(Assigned)</i></div></td>
                        <td width="12%"><div align="center">In<br>Actual <i>(Assigned)</i></div></td>
                        <td width="12%"><div align="center">Out<br>Actual <i>(Assigned)</i></div></td>
                        <td width="7%"><div align="center">In</div></td>
                        <td width="7%"><div align="center">Out</div></td>
                        <td width="7%"><div align="center">Duty</div></td>
                        <td width="7%"><div align="center">Overtime</div></td>
                        <td width="7%"><div align="center">Late</div></td>
                        <td width="7%"><div align="center">Under</div></td>
                    </tr>
        <?php
        $totalDutyHours=0;$totalLate=0;$totalUnder=0;
        foreach($calDate as $cDate => $logins):
            $am_in='';$am_out='';$pm_in='';$pm_out='';
            $dayName = dayName($cDate);
            $late=0; $undertime=0;$dutyHours=0;$otHours=0;
            $am_in_assign=NULL;$am_out_assign=NULL;$pm_in_assign=NULL;$pm_out_assign=NULL;

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
            #$totalDutyHours += $dutyHours + $otHours;
            $totalLate += $late;
            $totalUnder += $undertime;
            $totalDutyHours += $dutyHours;
            $otRecordIn = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate));
            $otRecordOut = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate));
            $otMins = $db->getValue('attendance_overtime_detail','total_mins',array('emp_id'=>$emp_id,'actual_start_date'=>$cDate));
            $ot_in = ($otRecordIn) ? $otRecordIn : NULL;
            $ot_out = ($otRecordOut) ? $otRecordOut : NULL;
            $otHours = ($otMins) ? $otMins : 0;
            ?>
                    <tr>
                        <td><?php echo functions::datearr($cDate); echo '<br>('.$dayName.')'?></td>
                        <td><div align="center"><?php echo ($logins['amin']) ? functions::MilToTwelve($logins['amin']) : '--:--';echo ($am_in_assign) ? ' <i>('.functions::MilToTwelve($am_in_assign).')</i>' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['amout']) ? functions::MilToTwelve($logins['amout']) : '--:--'; echo ($am_out_assign) ? ' <i>('.functions::MilToTwelve($am_out_assign).')</i> ' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['pmin']) ? functions::MilToTwelve($logins['pmin']) : '--:--'; echo ($pm_in_assign) ? ' <i>('.functions::MilToTwelve($pm_in_assign).')</i> ' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['pmout']) ? functions::MilToTwelve($logins['pmout']) : '--:--'; echo ($pm_out_assign) ? ' <i>('.functions::MilToTwelve($pm_out_assign).')</i> ' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['otin']) ? functions::MilToTwelve($logins['otin']) : '--:--'?></div></td>
                        <td><div align="center"><?php echo ($logins['otout']) ? functions::MilToTwelve($logins['otout']) : '--:--'?></div></td>
                        <td><div align="center"><?php echo ($dutyHours) ? functions::min_to_hour($dutyHours) : '';?></div></td>
                        <td><div align="center"><?php echo ($otHours) ? functions::min_to_hour($otHours) : '';?></div></td>
                        <td><div align="center"><?php echo ($late) ? functions::min_to_hour($late) : '';?></div></td>
                        <td><div align="center"><?php echo ($undertime) ? functions::min_to_hour($undertime) : '';?></div></td>
                    </tr>
            <?php
        endforeach;
        ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><div align="center"><strong><?php echo functions::min_to_hour($totalDutyHours)?></strong></div></td>
                        <td><??></td>
                        <td><div align="center"><strong><?php echo functions::min_to_hour($totalLate)?></strong></div></td>
                        <td><div align="center"><strong><?php echo functions::min_to_hour($totalUnder)?></strong></div></td>
                    </tr>
                </table><br><br>
        <?php
    endforeach;
    #functions::sendTo('attendance_upload_view.php?fn='.functions::encode($serData));
// End of saving the attendance recordwin7
}
?>
                            <div align="center" style="padding-top: 40px;">
                                <input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary">
                                <input type="submit" name="btnCancel" id="btnCancel" value=" CANCEL " class="btn">
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>