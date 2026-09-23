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
if($eatid){
    if( !isset($_SESSION['arr_attendance_emp']) ){
        $qEmpID = $db->select('emp_attendance_detail','DISTINCT emp_id',array('eat_id'=>$eatid));
        $selEmps = array();
        while($rEmpID = $db->fetch_array($qEmpID)):
            $selEmps[$rEmpID['emp_no']]=$rEmpID['emp_no'];
        endwhile;
        $_SESSION['arr_attendance_emp']=$selEmps;
    }
}
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eatid));
$arrSelEmp = (isset($_SESSION['arr_attendance_emp'])) ? $_SESSION['arr_attendance_emp'] : array();
$fn = $db->getValue('emp_attendance','excelfile',array('eat_id'=>$eatid));
$path = '../attendance/';
$filename=$path.$fn;
#print_r($arrSelEmp);
$arrAttendanceRecord=array();
$totalRow=0;
if($fn){
    if ( $xlsx = read_excel_xlsx::parse($filename) ) {
        $content = $xlsx->rows() ;
    } else {
        echo read_excel_xlsx::parseError();
    }
    $totalRow = count($content);
}
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
                    if( isset($arrSelEmp[$emp_id]) )
                    $arrAttendanceRecord[$emp_id][$dailyDate] = array('amin'=>$amIn,'amout'=>$amOut,'pmin'=>$pmIn,'pmout'=>$pmOut,'otin'=>$otIn,'otout'=>$otOut);
                }
            }
        endforeach;
    }
endfor;


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
    if( $actualOtIn && $actualOtOut )
        $otHours += functions::min_diff($actualOtIn,$cDate,$actualOtOut,$cDate);

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
            <ul class="nav tab-menu nav-tabs">
                <li><a href="attendance_payroll_view.php?eatid=<?php echo functions::encode($eatid)?>">Payroll</a></li>
                <li class="active" style="display:none;"><a href="attendance_record.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Attendance</a></li>
                <li><a href="attendance_select.php?eatid=<?php echo functions::encode($eatid)?>">Select Name</a></li>
                <li><a href="attendance_upload.php?eatid=<?php echo functions::encode($eatid)?>">Upload Att. Log</a></li>
            </ul>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE PREVIEW</h2></div>
                <?php
                $totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;
                foreach($arrAttendanceRecord as $employee => $calDate):
                    $totalDutyHours=0;
                    $monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
                    $amIn='';$amOut='';$pmIn='';$pmOut='';
                    echo $employee;
                    $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$employee));
                    echo $db->getValue('employee','concat(" - ",lname,", ",fname," ",mname)',array('emp_id'=>$emp_id));

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
                        <td width="12%"><div align="center">In</div></td>
                        <td width="12%"><div align="center">Out</div></td>
                        <td width="12%"><div align="center">In</div></td>
                        <td width="12%"><div align="center">Out</div></td>
                        <td width="7%"><div align="center">In</div></td>
                        <td width="7%"><div align="center">Out</div></td>
                        <td width="7%"><div align="center">Duty</div></td>
                        <td width="7%"><div align="center">Overtime</div></td>
                        <td width="7%"><div align="center">Late</div></td>
                        <td width="7%"><div align="center">Under</div></td>
                    </tr>
                    <?php
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
                    ?>
                    <tr>
                        <td><?php echo functions::datearr($cDate); echo ' ('.$dayName.')'?></td>
                        <td><div align="center"><?php echo ($logins['amin']) ? functions::MilToTwelve($logins['amin']) : '--:--';echo ($amIn) ? ' <i>('.functions::MilToTwelve($amIn).')</i>' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['amout']) ? functions::MilToTwelve($logins['amout']) : '--:--'; echo ($amOut) ? ' <i>('.functions::MilToTwelve($amOut).')</i> ' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['pmin']) ? functions::MilToTwelve($logins['pmin']) : '--:--'; echo ($pmIn) ? ' <i>('.functions::MilToTwelve($pmIn).')</i> ' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['pmout']) ? functions::MilToTwelve($logins['pmout']) : '--:--'; echo ($pmOut) ? ' <i>('.functions::MilToTwelve($pmOut).')</i> ' : ' (--:--)';?></div></td>
                        <td><div align="center"><?php echo ($logins['otin']) ? functions::MilToTwelve($logins['otin']) : '--:--'?></div></td>
                        <td><div align="center"><?php echo ($logins['otout']) ? functions::MilToTwelve($logins['otout']) : '--:--'?></div></td>
                        <td><div align="center"><?php echo ($dutyHours) ? functions::min_to_hour($dutyHours) : '';?></div></td>
                        <td><div align="center"><?php echo ($otHours) ? functions::min_to_hour($otHours) : '';?></div></td>
                        <td><div align="center"><?php echo ($late) ? functions::min_to_hour($late) : '';?></div></td>
                        <td><div align="center"><?php echo ($undertime) ? functions::min_to_hour($undertime) : '';?></div></td>
                    </tr>
                    <?php endforeach;?>
                </table>
                <br><br>
                Rate Per Day: <?php echo ($txSalDay);?><br><br>
                Rate Per Hour: <?php echo $txSalHour?><br><br>
                Total Duty Hours: <?php echo ($totalDutyHours) ? functions::min_to_hour($totalDutyHours) : '';?><br><br>
                Amount Hours: <?php echo ($totalDutyHours && $txSalMin) ? functions::formatMoney($totalDutyHours * $txSalMin) : '';?><br><br>
                <br><br>
                <?php endforeach;?>
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
