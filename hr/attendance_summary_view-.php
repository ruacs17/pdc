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
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$p_id = $rEatID['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $rEatID['confirmed'];
$attendance_ready = $rEatID['attendance_ready'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$eas_id = $rEatID['eas_id'];
$worker_type = $rEatID['payroll_type'];
$eas_name = $db->getValue('emp_assignment','eas_name',array('eas_id'=>$eas_id));
if( isset($_REQUEST['c']) ){
    $payroll_value = functions::decode($_REQUEST['c']);
    if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$eatid,'attendance_ready'=>0)) ){
        $db->update('emp_attendance',array('attendance_ready'=>1,'amount'=>$payroll_value),array('eat_id'=>$eatid));
        $attendance_ready=1;
    }
    else{
        $db->update('emp_attendance',array('attendance_ready'=>0,'amount'=>$payroll_value),array('eat_id'=>$eatid));
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?eatid='.functions::encode($eatid));
}
/*if($eatid){
    if( !isset($_SESSION['arr_attendance_emp']) ){
        $qEmpID = $db->select('emp_attendance_detail ead, employee e','DISTINCT emp_no',array('eat_id'=>$eatid),'AND ead.emp_id=e.emp_id');
        $selEmps = array();
        while($rEmpID = $db->fetch_array($qEmpID)):
            $selEmps[$rEmpID['emp_no']]=$rEmpID['emp_no'];
        endwhile;
        $_SESSION['arr_attendance_emp']=$selEmps;
    }
}
$arrSelEmp = (isset($_SESSION['arr_attendance_emp'])) ? $_SESSION['arr_attendance_emp'] : array();
*/
$totalRow = 0;
$content = array();
$arrAttendanceRecord = array();
$arrPayrollList = array();
$arrMember = array();
$qEmps = $db->select('emp_assign_detail eas_id, employee emp','DISTINCT eas_id.emp_id',array('eas_id'=>$eas_id),'AND eas_id.emp_id=emp.emp_id ORDER BY lname,fname');
while( $rEmps = $db->fetch_array($qEmps)):
    $arrMember[]=$rEmps['emp_id'];
    $qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$rEmps['emp_id']),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY emp_id');
    while($rA = $db->fetch_array($qEmpAtt)):
        $totalRow++;
        $emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rA['emp_id']));
        $otIn = ($rA['ot_in']) ? $rA['ot_in'] : $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date']));
        $otOut = ($rA['ot_out']) ? $rA['ot_out'] : $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date']));
        $arrAttendanceRecord[$emp_no][$rA['eat_date']] = array('eat_id'=>$rA['eat_id'],'amin'=>$rA['am_in'],'amout'=>$rA['am_out'],'pmin'=>$rA['pm_in'],'pmout'=>$rA['pm_out'],'otin'=>$otIn,'otout'=>$otOut);
    endwhile;
endwhile;

function dayName($date=''){
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
        }
        else if( $actualPmIn){
            if( strtotime($actualPmIn) > strtotime($assignPmOut) ){
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
#echo '<pre>';print_r($arrAttendanceRecord);echo '</pre>';
$totalGrossPayableAmount=0;$totalAbsentAmount=0;$totalRegDutyAmount=0;$totalOTDutyAmount=0;$totalNetAmount=0;$totalSSS=0;$totalPagibig=0;$totalPH=0;$totalOthers=0;$totalDeduction=0;

$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;$countEmp=1;
$empRegDutyHours=0;$empRegDutyAmount=0;$empOTDutyHours=0;$empOTDutyAmount=0;$empUnderDutyHours=0;$empUnderDutyAmount=0;$empLateDutyHours=0;$empLateDutyAmount=0;$empAbsentHours=0;$empAbsentAmount=0;$empGrossPayableAmount=0;$empNetPayableAmount=0;$empDeduction=0;
foreach($arrAttendanceRecord as $employee => $calDate):

    $empRegDutyHours=0;$empRegDutyAmount=0;$empOTDutyHours=0;$empOTDutyAmount=0;$empUnderDutyHours=0;$empUnderDutyAmount=0;$empLateDutyHours=0;$empLateDutyAmount=0;$empGrossPayableAmount=0;$empNetPayableAmount=0;$empDeduction=0;$empAbsentAmount=0;$empAbsentHours=0;

    $empTotalAmount=0;$salary=0;$sss_contribution=0;$philhealth_contribution=0;$pagibig_contribution=0;$other_deduction=0;$total_deduction=0;

    $txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;
    $txSalMonthDisp=0;$txSalDayDisp=0;$txSalHourDisp=0;$txSalMinDisp=0;
    $monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
    $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
    $name = $db->getValue('employee','concat(lname,", ",fname," ",left(mname,1),".")',array('emp_id'=>$emp_id));
    $salary = $db->getValue('employee','salary',array('emp_id'=>$emp_id));
    $sss_contribution = $db->getValue('employee','sss_contribution',array('emp_id'=>$emp_id));
    $philhealth_contribution = $db->getValue('employee','philhealth_contribution',array('emp_id'=>$emp_id));
    $pagibig_contribution = $db->getValue('employee','pagibig_contribution',array('emp_id'=>$emp_id));
    $other_deduction = $db->getValue('payroll_deduction','sum(deduct_value)',array('emp_id'=>$emp_id,'eat_id'=>$eatid));

    $qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'ORDER BY es_date DESC, es_id DESC');
    if( $db->num_rows($qSal) > 0 ){
        $rSal = $db->fetch_array($qSal);
        $txSalMonth = functions::formatMoney($rSal['es_salary'],4);
        $txSalDay = functions::formatMoney($rSal['es_daily'],4);
        $txSalHour = functions::formatMoney($rSal['es_hourly'],4);
        $txSalMin = functions::formatMoney($rSal['es_minute'],4);
        $txSalMonthDisp = functions::formatMoney($rSal['es_salary'],2);
        $txSalDayDisp = functions::formatMoney($rSal['es_daily'],2);
        $txSalHourDisp = functions::formatMoney($rSal['es_hourly'],2);
        $txSalMinDisp = functions::formatMoney($rSal['es_minute'],2);
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
        $dayName = dayName($cDate);
        $late=0; $undertime=0;$dutyHours=0;$otHours=0;
        if($dayName=="Monday"){
            diffComp($logins['amin'],$monAmIn,$logins['amout'],$monAmOut,$logins['pmin'],$monPmIn,$logins['pmout'],$monPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
        }
        if($dayName=="Tuesday"){
            diffComp($logins['amin'],$tueAmIn,$logins['amout'],$tueAmOut,$logins['pmin'],$tuePmIn,$logins['pmout'],$tuePmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
        }
        if($dayName=="Wednesday"){
            diffComp($logins['amin'],$wedAmIn,$logins['amout'],$wedAmOut,$logins['pmin'],$wedPmIn,$logins['pmout'],$wedPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
        }
        if($dayName=="Thursday"){
            diffComp($logins['amin'],$thuAmIn,$logins['amout'],$thuAmOut,$logins['pmin'],$thuPmIn,$logins['pmout'],$thuPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
        }
        if($dayName=="Friday"){
            diffComp($logins['amin'],$friAmIn,$logins['amout'],$friAmOut,$logins['pmin'],$friPmIn,$logins['pmout'],$friPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
        }
        if($dayName=="Saturday"){
            diffComp($logins['amin'],$satAmIn,$logins['amout'],$satAmOut,$logins['pmin'],$satPmIn,$logins['pmout'],$satPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
        }
        $empRegDutyHours += $dutyHours;
        $empOTDutyHours += $otHours;
        $empUnderDutyHours += $undertime;
        $empLateDutyHours += $late;
    endforeach;
    $empGrossPayableAmount += $empRegDutyAmount = ($empRegDutyHours && $txSalMin) ? $empRegDutyHours * $txSalMin : 0;
    $empGrossPayableAmount += $empOTDutyAmount = ($empOTDutyHours && $txSalMin) ? $empOTDutyHours * $txSalMin : 0;
    $empGrossPayableAmount += $empUnderDutyAmount = ($empUnderDutyHours && $txSalMin) ? $empUnderDutyHours * $txSalMin : 0;
    $empGrossPayableAmount += $empLateDutyAmount = ($empLateDutyHours && $txSalMin) ? $empLateDutyHours * $txSalMin : 0;
    $totalGrossPayableAmount += $empGrossPayableAmount;
    $total_deduction+=$sss_contribution;
    $total_deduction+=$philhealth_contribution;
    $total_deduction+=$pagibig_contribution;
    $total_deduction+=$other_deduction;

    $totalSSS += $sss_contribution;
    $totalPagibig += $pagibig_contribution;
    $totalPH += $philhealth_contribution;
    $totalOthers += $other_deduction;

    $empDeduction += $sss_contribution;
    $empDeduction += $philhealth_contribution;
    $empDeduction += $pagibig_contribution;
    $empDeduction += $other_deduction;

    $empAbsentHours = $empUnderDutyHours + $empLateDutyHours;
    $empAbsentAmount = $empUnderDutyAmount + $empLateDutyAmount;
    $empDeduction += $empAbsentAmount;
    $totalAbsentAmount += $empAbsentAmount;

    $empNetPayableAmount = $empGrossPayableAmount - $empDeduction;
    $empTotalAmount = ($empRegDutyAmount + $empOTDutyAmount);

    $totalRegDutyAmount += $empRegDutyAmount;
    $totalOTDutyAmount += $empOTDutyAmount;

    $totalNetAmount += $empNetPayableAmount;

    $totalDeduction += $empDeduction;
    
    $qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
    $countPos=0;$position='';
    while($rPos = $db->fetch_array($qPos)):
        if($countPos)
            $position .= ' /<br>';
        $position .= $rPos['pos_name'];
        $countPos++;
    endwhile;
    $arrPayrollList[$emp_id] = array('emp_id'=>$emp_id,'emp_no'=>$employee,'name'=>$name,'position'=>$position,'salary'=>$salary,'sss_contribution'=>$sss_contribution,'philhealth_contribution'=>$philhealth_contribution,'pagibig_contribution'=>$pagibig_contribution,'other_deduction'=>$other_deduction,'rate_day'=>$txSalDayDisp,'rate_hour'=>$txSalHourDisp,
        'regular_hours'=>$empRegDutyHours,'regular_amount'=>$empRegDutyAmount,'overtime_hours'=>$empOTDutyHours,'overtime_amount'=>$empOTDutyAmount,'undertime_hours'=>$empUnderDutyHours,'undertime_amount'=>$empUnderDutyAmount,'late_hours'=>$empLateDutyHours,'late_amount'=>$empLateDutyAmount,'gross_payable_amount'=>$empGrossPayableAmount,'total_amount'=>$empTotalAmount,'net_pay'=>$empNetPayableAmount,'total_deduction'=>$empDeduction,'absentAmount'=>$empAbsentAmount,'absentHours'=>$empAbsentHours);
endforeach;
#echo '<pre>';print_r($arrPayrollList);echo '</pre>';
/*if(count($arrPayrollList))
    functions::sortMultiArray($arrPayrollList,$orderBy='name',$sort_AscDesc='ASC');*/

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
                <li class="active"><a href="attendance_payroll_view.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Summary</a></li>
                <li><a href="attendance_upload.php?eatid=<?php echo functions::encode($eatid)?>">Upload Att. Log</a></li>
            </ul>
        </div>
        <div align="right" style="display:none;">
            <a id="prPrint" href="attendance_payroll_print.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;
        </div>
        <div class="box-content">
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
                    <tr>
                        <td height="30px">Period Covered</td>
                        <td><strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong></td>
                    </tr>
                    <tr>
                        <td height="30px">Confirm Status</td>
                        <td>
                            <input type="checkbox" name="chkConf" id="chkConf" value="<?php echo functions::encode($totalNetAmount);?>" onClick="stat(this.value)" <?php if($attendance_ready)echo 'checked';?>> <?php echo ($db->getValue('emp_attendance','attendance_ready',array('eat_id'=>$eatid))==1) ? 'Confirmed' : 'Unconfirmed';?>
                        </td>
                    </tr>
                </table>


                <table width="98%" border="0" class="table table-hover table-bordered" style="font-size:12px">
                    <tr>
                        <th width="15%">NAME</th>
                        <th width="10%"><div align="center">Position</div></th>
                        <th width="10%"><div align="center">Regular Days (Hours)</div></th>
                        <th width="9%"><div align="center">Absent Days (Hours)</div></th>
                        <th width="9%"><div align="center">Required Days (Hours)</div></th>
                        <th width="9%"><div align="center">Overtime Hours</div></th>
                        <th width="10%"><div align="center">Total Duty Hours</div></th>
                    </tr>
                    <tr>
                        <td colspan="7">&nbsp;</td>
                    </tr>
                <?php
                $countEmp=0;$totalDutyHours=0;
                foreach($arrMember as $memberID):
                    $countEmp++;
                    $pd = isset($arrPayrollList[$memberID]) ? $arrPayrollList[$memberID] : 0;
                    $totalDutyHours = ($pd['overtime_hours'] + $pd['regular_hours']);
                    if($pd){
                ?>
                    <tr>
                        <td height="30px">
                            <a id="vw<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_view_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>','Attendance Detail','1')">
                            <?php echo $countEmp.'. '.$pd['name'];?>
                            </a>
                        </td>
                        <td><div align="left"><?php echo $pd['position'];?></div></td>
                        <td>
                            <div align="right">
                                <?php

                                echo $regDays = ($pd['regular_hours']) ? number_format((($pd['regular_hours'] / 60) / 8),2) : 0;
                                echo ($regDays > 1) ? ' day/s' : ' day';
                                echo ($pd['regular_hours']) ? ' ('.functions::min_to_hour($pd['regular_hours']).')' : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <?php
                                echo $absentDays = ($pd['absentHours']) ? number_format((($pd['absentHours'] / 60) / 8),2) : '';
                                echo ($absentDays > 1) ? ' day/s' : ' day';
                                echo ($pd['absentHours']) ? ' ('.functions::min_to_hour($pd['absentHours']).')' : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <?php
                                echo $requiredDays = ($pd['regular_hours'] || $pd['absentHours']) ? number_format((($pd['regular_hours'] + $pd['absentHours']) / 60) / 8,2) : '';
                                echo ($requiredDays > 1) ? ' day/s' : ' day';
                                echo ($pd['regular_hours'] || $pd['absentHours']) ? ' ('.functions::min_to_hour($pd['regular_hours'] + $pd['absentHours']).')' : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <?php
                                #echo $otDays = ($pd['overtime_hours']) ? number_format((($pd['overtime_hours'] / 60) / 8),2) : 0;
                                #echo ($otDays) ? ' day/s' : ' day';
                                echo ($pd['overtime_hours']) ? ' ('.functions::min_to_hour($pd['overtime_hours']).')' : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <?php
                                echo ($totalDutyHours) ? number_format((($totalDutyHours / 60) / 8),2) : '';
                                echo ' day/s';
                                echo ($totalDutyHours) ? ' ('.functions::min_to_hour($totalDutyHours).')' : '';
                                ?>
                            </div>
                        </td>
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
                    </tr>
                <?php }
                endforeach;?>
                <?php if($countEmp > 15){?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Position</strong></div></td>
                        <td><div align="right"><strong>Regular</strong></div></td>
                        <td><div align="right"><strong>OT</strong></div></td>
                        <td><div align="right"><strong>GROSS Pay</strong></div></td>
                        <td><div align="right"><strong>ABSENT</strong></div></td>
                        <td><div align="right"><strong>ABSENT</strong></div></td>
                    </tr>
                <?php }?>
                </table>
            </form>
        </div>
    </div><!--/span-->
</div><!--/row-->
<?php $db->update('emp_attendance',array('amount'=>$totalNetAmount),array('eat_id'=>$eatid));?>
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
function stat(v){
    window.location="<?php echo $_SERVER['PHP_SELF']?>?eatid=<?php echo functions::encode($eatid);?>&c="+v;
}
</script>
<!-- end: JavaScript-->
</body>
</html>
