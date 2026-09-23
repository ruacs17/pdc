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
$emp_id = (isset($_REQUEST['empid']) && !empty($_REQUEST['empid']) ) ? functions::decode($_REQUEST['empid']) : 0;



$totalRow = 0;
$content = array();
$arrAttendanceRecord = array();
$arrPayrollList = array();
$qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
#echo $db->last_query;
while($rA = $db->fetch_array($qEmpAtt)):
    $totalRow++;
    $emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rA['emp_id']));
    $otIn = ($rA['ot_in']) ? $rA['ot_in'] : $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date']));
    $otOut = ($rA['ot_out']) ? $rA['ot_out'] : $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date']));
    $arrAttendanceRecord[$emp_no][$rA['eat_date']] = array('eat_id'=>$rA['eat_id'],'eatd_id'=>$rA['eatd_id'],'amin'=>$rA['am_in'],'amout'=>$rA['am_out'],'pmin'=>$rA['pm_in'],'pmout'=>$rA['pm_out'],'otin'=>$otIn,'otout'=>$otOut);
endwhile;


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
#echo '<pre>';print_r($arrAttendanceRecord);echo '</pre>';
$arrDailyAttendance=array();
$totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;$regularDutyHours=0; $otDutyHours=0;
foreach($arrAttendanceRecord as $employee => $calDate):
    $totalDutyHours=0;
    $monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';$amIn='';$amOut='';$pmIn='';$pmOut='';
    $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
    $emp_name = $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_id'=>$emp_id));
    $qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
    $countPos=0;$position='';
    while($rPos = $db->fetch_array($qPos)):
        if($countPos)
            $position .= ' /<br>';
        $position .= $rPos['pos_name'];
        $countPos++;
    endwhile;

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
    $regularDutyHours=0; $otDutyHours=0;$totalAbsent=0;
    foreach($calDate as $cDate => $logins):
        $amIn='';$amOut='';$pmIn='';$pmOut='';
        $dayName = dayName($cDate);
        $late=0; $undertime=0;$dutyHours=0;$otHours=0;
        if($dayName=="Monday"){
            diffComp($logins['amin'],$monAmIn,$logins['amout'],$monAmOut,$logins['pmin'],$monPmIn,$logins['pmout'],$monPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
            $amIn=$monAmIn;$amOut=$monAmOut;$pmIn=$monPmIn;$pmOut=$monPmOut;
        }
        if($dayName=="Tuesday"){
            diffComp($logins['amin'],$tueAmIn,$logins['amout'],$tueAmOut,$logins['pmin'],$tuePmIn,$logins['pmout'],$tuePmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
            $amIn=$tueAmIn;$amOut=$tueAmOut;$pmIn=$tuePmIn;$pmOut=$tuePmOut;
        }
        if($dayName=="Wednesday"){
            diffComp($logins['amin'],$wedAmIn,$logins['amout'],$wedAmOut,$logins['pmin'],$wedPmIn,$logins['pmout'],$wedPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
            $amIn=$wedAmIn;$amOut=$wedAmOut;$pmIn=$wedPmIn;$pmOut=$wedPmOut;
        }
        if($dayName=="Thursday"){
            diffComp($logins['amin'],$thuAmIn,$logins['amout'],$thuAmOut,$logins['pmin'],$thuPmIn,$logins['pmout'],$thuPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
            $amIn=$thuAmIn;$amOut=$thuAmOut;$pmIn=$thuPmIn;$pmOut=$thuPmOut;
        }
        if($dayName=="Friday"){
            diffComp($logins['amin'],$friAmIn,$logins['amout'],$friAmOut,$logins['pmin'],$friPmIn,$logins['pmout'],$friPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
            $amIn=$friAmIn;$amOut=$friAmOut;$pmIn=$friPmIn;$pmOut=$friPmOut;
        }
        if($dayName=="Saturday"){
            diffComp($logins['amin'],$satAmIn,$logins['amout'],$satAmOut,$logins['pmin'],$satPmIn,$logins['pmout'],$satPmOut,$logins['otin'],$logins['otout'],$late,$undertime,$dutyHours,$otHours);
            $amIn=$satAmIn;$amOut=$satAmOut;$pmIn=$satPmIn;$pmOut=$satPmOut;
        }
        $totalDutyHours += $dutyHours + $otHours;
        $regularDutyHours += $dutyHours;
        $otDutyHours += $otHours;
        $totalAbsent += $late;
        $totalAbsent += $undertime;
        $dailyName = functions::datearr($cDate).'<br>('.$dayName.')';
        $d_amin = ($logins['amin']) ? functions::MilToTwelve($logins['amin']) : '--:--';
        $d_amin .= ($amIn) ? ' <i>('.functions::MilToTwelve($amIn).')</i>' : ' (--:--)';

        $d_amout = ($logins['amout']) ? functions::MilToTwelve($logins['amout']) : '--:--';
        $d_amout .= ($amOut) ? ' <i>('.functions::MilToTwelve($amOut).')</i> ' : ' (--:--)';

        $d_pmin = ($logins['pmin']) ? functions::MilToTwelve($logins['pmin']) : '--:--';
        $d_pmin .= ($pmIn) ? ' <i>('.functions::MilToTwelve($pmIn).')</i> ' : ' (--:--)';

        $d_pmout = ($logins['pmout']) ? functions::MilToTwelve($logins['pmout']) : '--:--';
        $d_pmout .= ($pmOut) ? ' <i>('.functions::MilToTwelve($pmOut).')</i> ' : ' (--:--)';

        $d_otin = ($logins['otin']) ? functions::MilToTwelve($logins['otin']) : '--:--';
        $d_otout = ($logins['otout']) ? functions::MilToTwelve($logins['otout']) : '--:--';
        $arrDailyAttendance[] = array('eat_id'=>$logins['eat_id'],'eatd_id'=>$logins['eatd_id'],'dailyName'=>$dailyName,'amin'=>$d_amin,'amout'=>$d_amout,'pmin'=>$d_pmin,'pmout'=>$d_pmout,'otin'=>$d_otin,'otout'=>$d_otout,'dutyHours'=>$dutyHours,'otHours'=>$otHours,'late'=>$late,'undertime'=>$undertime);
    endforeach;
    $amountRegularHour = ($regularDutyHours && $txSalMin) ? ($regularDutyHours * $txSalMin) : 0;
    $amountOvertimeHour = ($otDutyHours && $txSalMin) ? ($otDutyHours * $txSalMin) : 0;
endforeach;
/*echo '<pre>';
print_r($arrDailyAttendance);
echo '</pre>';*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Employee Attendance</title>
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
            <form class="form-horizontal" method="post">
                <div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE PREVIEW</h2></div>
                <table border="0" width="90%" style="font-size: 12px;">
                    <tr>
                        <td width="10%" height="30px">Project</td>
                        <td><strong><?php echo $proj_name;?></strong></td>
                    </tr>
                    <tr>
                        <td height="30px">Period Covered</td>
                        <td><strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong></td>
                    </tr>
                </table><br>
                <table width="98%" border="0" class="table table-hover table-bordered" style="font-size: 12px;">
                    <tr>
                        <th width="20%">NAME</th>
                        <th width="11%">POSITION</th>
                        <th width="10%"><div align="right">Regular Days (Hours)</div></th>
                        <th width="10%"><div align="right">Absent Days (Hours)</div></th>
                        <th width="10%"><div align="right">Required Days (Hours)</div></th>
                        <th width="10%"><div align="right">Overtime Hours</div></th>
                        <th width="10%"><div align="right">Total Duty</div></th>
                    </tr>
                        <?php
                        $amountRegularHour = ($regularDutyHours && $txSalMin) ? ($regularDutyHours * $txSalMin) : 0;
                        $amountOvertimeHour = ($otDutyHours && $txSalMin) ? ($otDutyHours * $txSalMin) : 0;
                        $totalDuty = ($regularDutyHours + $otDutyHours);
                        ?>
                    <tr>
                        <td height="30px"><div align="left"><strong><?php echo $employee.' - '.$emp_name;?></strong></div></td>
                        <td><?php echo $position?></td>
                        <td>
                            <div align="right">
                                <?php
                                echo $regDays = ($regularDutyHours) ? number_format((($regularDutyHours/60)/8),2) : '';
                                echo ($regDays > 1) ? ' day/s' : ' day';
                                echo ($regularDutyHours) ? ' ('.functions::min_to_hour($regularDutyHours).')' : '';
                                ?>
                            </div>
                        </td>
                        
                        <td>
                            <div align="right">
                                <?php
                                echo $absentDays = number_format((($totalAbsent/60)/8),2);
                                echo ($absentDays > 1) ? ' day/s' : ' day';
                                echo ($totalAbsent) ? ' ('.functions::min_to_hour($totalAbsent).')' : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <?php
                                echo $requiredDays = ($regularDutyHours || $totalAbsent) ? number_format((($regularDutyHours + $totalAbsent) / 60) / 8,2) : '';
                                echo ($requiredDays > 1) ? ' day/s' : ' day';
                                echo ($regularDutyHours || $totalAbsent) ? ' ('.functions::min_to_hour($regularDutyHours + $totalAbsent).')' : '';
                                ?>
                            </div>
                        </td>
                        <td><div align="right"><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '';?></div></td>
                        <td>
                            <div align="right">
                                <strong>
                                    <?php
                                    echo $tDuty = ($totalDuty) ? number_format((($totalDuty/60)/8),2) : '';
                                    echo ($tDuty > 1) ? ' day/s' : ' day';
                                    echo ($totalDuty) ? ' ('.functions::min_to_hour($totalDuty).')' : '';
                                    ?>
                                </strong>
                            </div>
                        </td>
                    </tr>
                </table><br>
                <table width="100%" align="center" border="1" style="font-size: 12px;">
                    <tr>
                        <td>DATE</td>
                        <td colspan="2"><div align="center">Morning</div></td>
                        <td colspan="2"><div align="center">Afternoon</div></td>
                        <td colspan="2"><div align="center">Overtime</div></td>
                        <td colspan="4"><div align="center">Total</div></td>
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
                    $totalAbsent=0;$totalLate=0;$totalUnder=0;
                    foreach($arrDailyAttendance as $dly):
                        $totalAbsent += ($dly['late'] + $dly['undertime']);
                        $totalLate += $dly['late'];
                        $totalUnder += $dly['undertime'];
                        $rID = $dly['eatd_id'];

                    ?>
                    <tr>
                        <td>
                            <a id="vw<?php echo $rID?>" class="thickbox" style="cursor: pointer;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_adjust_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($emp_id);?>&eatdid=<?php echo functions::encode($rID);?>','Attendance Detail')">
                            <?php echo $dly['dailyName'];?>
                            </a>
                        </td>
                        <td><div align="center"><?php echo $dly['amin'];?></div></td>
                        <td><div align="center"><?php echo $dly['amout'];?></div></td>
                        <td><div align="center"><?php echo $dly['pmin'];?></div></td>
                        <td><div align="center"><?php echo $dly['pmout'];?></div></td>
                        <td><div align="center"><?php echo $dly['otin'];?></div></td>
                        <td><div align="center"><?php echo $dly['otout'];?></div></td>
                        <td><div align="center"><?php echo ($dly['dutyHours']) ? functions::min_to_hour($dly['dutyHours']) : '';?></div></td>
                        <td><div align="center"><?php echo ($dly['otHours']) ? functions::min_to_hour($dly['otHours']) : '';?></div></td>
                        <td><div align="center"><?php echo ($dly['late']) ? functions::min_to_hour($dly['late']) : '';?></div></td>
                        <td><div align="center"><?php echo ($dly['undertime']) ? functions::min_to_hour($dly['undertime']) : '';?></div></td>
                    </tr>
                    <?php endforeach;?>
                    <tr>
                        <td colspan="7">&nbsp;</td>
                        <td><div align="center"><strong><?php echo ($regularDutyHours) ? functions::min_to_hour($regularDutyHours) : '';?></strong></div></td>
                        <td><div align="center"><strong><?php echo ($otDutyHours) ? functions::min_to_hour($otDutyHours) : '';?></strong></div></td>
                        <td><div align="center"><strong><?php echo ($totalLate) ? functions::min_to_hour($totalLate) : '';?></strong></div></td>
                        <td><div align="center"><strong><?php echo ($totalUnder) ? functions::min_to_hour($totalUnder) : '';?></strong></div></td>
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
<!-- end: JavaScript-->
</body>
</html>