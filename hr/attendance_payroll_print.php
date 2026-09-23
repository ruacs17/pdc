<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$fn = $db->getValue('emp_attendance','excelfile',array('eat_id'=>$eatid));
$p_id = $db->getValue('emp_attendance','proj_id',array('eat_id'=>$eatid));
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$date_start = $db->getValue('emp_attendance','date_start',array('eat_id'=>$eatid));
$date_end = $db->getValue('emp_attendance','date_end',array('eat_id'=>$eatid));
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eatid));
$payroll_no = $db->getValue('emp_attendance','payroll_no',array('eat_id'=>$eatid));

if($eatid){
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



$ft = $db->getValue('emp_attendance','filetype',array('eat_id'=>$eatid));
$path = '../attendance/';
$filename=$path.$fn;
$totalRow = 0;
$content = array();

if($ft==='xlsx'){
    require_once('../class/read_excel_xlsx.php');
    if($fn){
        if ( $xlsx = read_excel_xlsx::parse($filename) ) {
            $content = $xlsx->rows() ;
        } else {
            echo read_excel_xlsx::parseError();
        }
        $totalRow = count($content);
    }
}
if($ft==='xls'){
    require_once('../class/read_excel_xls.php');
    if($fn){
        if ( $xls = read_excel_xls::parse($filename) ) {
            $content = $xls->rows() ;
        } else {
            echo read_excel_xls::parseError();
        }
        $totalRow = count($content);
    }
}

$arrAttendanceRecord=array();
$arrPayrollList=array();
$arrAttendanceDate = isset($content[3]) ? $content[3] : array();
$dateRange = isset($content[2][2]) ? $content[2][2] : '';
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
foreach($arrAttendanceDate as $aAindx => $aAVal):
    $arrDate[$aAindx]=$aAVal;
endforeach;

$emp_id=''; $emp_name='';
for($t=4;$t<$totalRow; $t++):
    #echo $t;
    #echo '<br>';
    if( ($t%2)==0 ){
        $emp_id=isset($content[$t][2]) ? $content[$t][2] : '';
        $emp_name=isset($content[$t][10]) ? $content[$t][10] : '';
        foreach($arrDate as $dailyIndx => $dailyVal):
            if( isset($arrAttendanceDaily[$dailyIndx]) && $arrAttendanceDaily[$dailyIndx]){
                $monFrom = (strlen($monFrom)==1) ? '0'.$monFrom : $monFrom;
                $dailyVal = (strlen($dailyVal)==1) ? '0'.$dailyVal : $dailyVal;
                if($dateFrom <= $dailyVal)
                    $dailyDate = $yearFrom.'-'.$monFrom.'-'.$dailyVal;
                else{
                    $monFromA = (strlen($monFrom+1)==1) ? '0'.($monFrom+1) : ($monFrom+1);
                    $dailyDate = $yearFrom.'-'.$monFromA.'-'.$dailyVal;
                }
                if( isset($arrSelEmp[$emp_id]) )
                $arrAttendanceRecord[$emp_id][$dailyDate] = array('amin'=>'','amout'=>'','pmin'=>'','pmout'=>'','otin'=>'','otout'=>'');
            }
        endforeach;
    }
    if( ($t%2)==1 ){
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
                    if( isset($arrSelEmp[$emp_id]) ){
                        $real_emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$emp_id));
                        $otIn = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dailyDate));
                        $otOut = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$real_emp_id,'actual_start_date'=>$dailyDate));
                        $arrAttendanceRecord[$emp_id][$dailyDate] = array('amin'=>$amIn,'amout'=>$amOut,'pmin'=>$pmIn,'pmout'=>$pmOut,'otin'=>$otIn,'otout'=>$otOut);
                    }
                }
            }
        endforeach;
    }
endfor;
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
$totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;$countEmp=1;
$totalRegAmount=0;$totalOTAmount=0;$totalAmount=0;
foreach($arrAttendanceRecord as $employee => $calDate):
    $empRegAmount=0;$empOTAmout=0;$empTotalAmount=0;
    $totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;
    $txSalMonthDisp=0;$txSalDayDisp=0;$txSalHourDisp=0;$txSalMinDisp=0;
    $totalRegularHours=0;$totalOvertime=0;$totalLate=0;$totalUndertime=0;
    $monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
    $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
    $name = $db->getValue('employee','concat(lname,", ",fname," ",left(mname,1),".")',array('emp_id'=>$emp_id));

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
        $totalDutyHours += $dutyHours + $otHours;
        $totalRegularHours += $dutyHours;
        $totalOvertime += $otHours;
        $totalLate += $late;
        $totalUndertime += $undertime;
        $empRegAmount = ($totalRegularHours && $txSalMin) ? $totalRegularHours * $txSalMin : 0;
        $empOTAmout = ($totalOvertime && $txSalMin) ? $totalOvertime * $txSalMin : 0;
    endforeach;
    $totalRegAmount += $empRegAmount;
    $totalOTAmount += $empOTAmout;
    $totalAmount += ($empRegAmount + $empOTAmout);
    $empTotalAmount = ($empRegAmount + $empOTAmout);
    $qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
    $countPos=0;$position='';
    while($rPos = $db->fetch_array($qPos)):
        if($countPos)
            $position .= ' /<br>';
        $position .= $rPos['pos_name'];
        $countPos++;
    endwhile;
    $arrPayrollList[] = array('emp_id'=>$emp_id,'emp_no'=>$employee,'name'=>$name,'position'=>$position,'rate_day'=>$txSalDayDisp,'rate_hour'=>$txSalHourDisp,'regular_hours'=>$totalRegularHours,'regular_amount'=>$empRegAmount,'overtime_hours'=>$totalOvertime,'overtime_amount'=>$empOTAmout,'total_amount'=>($empTotalAmount));
endforeach;
if(count($arrPayrollList))
    functions::sortMultiArray($arrPayrollList,$orderBy='name',$sort_AscDesc='ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Payroll Print</title>
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
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="97%" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('PAYROLL No. '.$payroll_no);
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table border="0" width="100%" style="font-size: 12px;">
                    <tr>
                        <td width="25%">Project / Department:</td>
                        <td><?php echo $proj_name;?></td>
                    </tr>
                    <tr>
                        <td>Payroll No:</td>
                        <td><?php echo $payroll_no;?></td>
                    </tr>
                    <tr>
                        <td>Period Covered:</td>
                        <td><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></td>
                    </tr>
                </table>
                <table width="100%" border="1" style="font-size: 10px;">
                    <tr>
                        <th width="19%">NAME</th>
                        <th width="12%">POSITION</th>
                        <th width="5%"><div align="center">Rate / Day</div></th>
                        <th width="5%"><div align="center">Rate / Hour</div></th>
                        <th width="5%"><div align="center">Regular Hours</div></th>
                        <th width="5%"><div align="center">Amount</div></th>
                        <th width="5%"><div align="center">Overtime Hours</div></th>
                        <th width="5%"><div align="center">Amount</div></th>
                        <th width="7%"><div align="center">Total Amount</div></th>
                    </tr>
                <?php
                $countEmp=1;
                foreach($arrPayrollList as $pd):
                ?>
                    <tr>
                        <td height="30px"><?php echo $countEmp++.'. '.$pd['name'];?></td>
                        <td><?php echo $pd['position']?></td>
                        <td><div align="right"><?php echo $pd['rate_day'];?>&nbsp;&nbsp;</div></td>
                        <td><div align="right"><?php echo $pd['rate_hour'];?>&nbsp;&nbsp;</div></td>
                        <td><div align="right"><?php echo functions::min_to_hour($pd['regular_hours']);?>&nbsp;&nbsp;</div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['regular_amount']);?>&nbsp;&nbsp;</div></td>
                        <td><div align="right"><?php echo functions::min_to_hour($pd['overtime_hours']);?>&nbsp;&nbsp;</div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['overtime_amount']);?>&nbsp;&nbsp;</div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['total_amount']);?>&nbsp;&nbsp;</div></td>
                    </tr>
                <?php endforeach;?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong><?php echo ($totalRegAmount) ? functions::formatMoney($totalRegAmount) : 0;?></strong>&nbsp;&nbsp;</div></td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong><?php echo ($totalOTAmount) ? functions::formatMoney($totalOTAmount) : 0;?></strong>&nbsp;&nbsp;</div></td>
                        <td><div align="right"><strong><?php echo ($totalAmount) ? functions::formatMoney($totalAmount) : 0;?></strong>&nbsp;&nbsp;</div></td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<footer>
    <div align="right"></div>
</footer>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
    window.print();
    setTimeout("closePrint()",200);

});
function closePrint(){
    window.location="attendance_payroll_view.php?eatid=<?php echo functions::encode($eatid)?>";
}
</script><!-- end: JavaScript-->
</body>
</html>