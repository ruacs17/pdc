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
$attendance_file = (isset($_REQUEST['fn']) && !empty($_REQUEST['fn']) ) ? functions::decode($_REQUEST['fn']) : '';
$arrRequest = unserialize($attendance_file);

$date_start = isset($arrRequest['date_from']) ? $arrRequest['date_from'] : '';
$date_end = isset($arrRequest['date_to']) ? $arrRequest['date_to'] : '';
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
                        <td height="30px" width="10%">Period Covered</td>
                        <td><strong><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></strong></td>
                    </tr>
                </table><br><br>
<?php
$empNos = (isset($arrRequest['empID'])) ? $arrRequest['empID'] : array();
foreach($empNos as $emp_no => $emp_lname):
    $emp_id = $db->getValue('employee','emp_id',array('emp_no'=>$emp_no));
    $has_attendance = $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id));
    $totalRow = 0;
    $totalDutyHours=0;$txSalMonth=0;$txSalDay=0;$txSalHour=0;$txSalMin=0;$regularDutyHours=0; $otDutyHours=0;
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
    $qSal = $db->select('emp_salary','*',array('emp_id'=>$emp_id),'ORDER BY es_date DESC, es_id DESC');
    if( $db->num_rows($qSal) > 0 ){
        $rSal = $db->fetch_array($qSal);
        $txSalMonth = functions::formatMoney($rSal['es_salary'],4);
        $txSalDay = functions::formatMoney($rSal['es_daily'],4);
        $txSalHour = functions::formatMoney($rSal['es_hourly'],4);
        $txSalMin = functions::formatMoney($rSal['es_minute'],4);
    }
    $regularDutyHours=0; $otDutyHours=0;$totalAbsent=0; $hoursPerDay=0;

    $arrDailyAttendance = array();

    if( $db->getValue('employee','has_attendance',array('emp_id'=>$emp_id))==0 ){
        $hoursPerDay = 480;
        $dates = functions::date_diff($date_start,$date_end);
        if($dates){
            for($i=0;$i<=$dates;$i++):
                $succeeding_date = functions::AddDay($date_start,$i);
                $daysName = date('l', strtotime($succeeding_date));
                if($daysName == 'Saturday')
                    $regularDutyHours += 240; //4hr
                else if($daysName=='Sunday')
                    ;
                else
                    $regularDutyHours += 480; // 8hr
            endfor;   
        }
    }
    else{
        $qEmpAtt = $db->select('emp_attendance_detail','*',array('emp_id'=>$emp_id),'AND eat_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" ORDER BY eat_date');
        #echo $db->last_query;
        while($rA = $db->fetch_array($qEmpAtt)):
            $amin='';$amout='';$pmin='';$pmout='';
            $amDutyHrs=0;
            $emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rA['emp_id']));
            $dailyName = functions::datearr($rA['eat_date']).'<br>('.$rA['eat_day'].')';
            $amin = ($rA['am_in']) ? functions::MilToTwelve($rA['am_in']) : '--:--';
            $amin .= ($rA['am_in_assign']) ? ' <i>('.functions::MilToTwelve($rA['am_in_assign']).')</i>' : ' (--:--)';

            $amout = ($rA['am_out']) ? functions::MilToTwelve($rA['am_out']) : '--:--';
            $amout .= ($rA['am_out_assign']) ? ' <i>('.functions::MilToTwelve($rA['am_out_assign']).')</i>' : ' (--:--)';
            $amDutyHrs = ($rA['am_in_assign'] && $rA['am_out_assign']) ? functions::min_diff($rA['am_in_assign'],$rA['eat_date'],$rA['am_out_assign'],$rA['eat_date']) : 0;

            $pmin = ($rA['pm_in']) ? functions::MilToTwelve($rA['pm_in']) : '--:--';
            $pmin .= ($rA['pm_in_assign']) ? ' <i>('.functions::MilToTwelve($rA['pm_in_assign']).')</i>' : ' (--:--)';

            $pmout = ($rA['pm_out']) ? functions::MilToTwelve($rA['pm_out']) : '--:--';
            $pmout .= ($rA['pm_out_assign']) ? ' <i>('.functions::MilToTwelve($rA['pm_out_assign']).')</i>' : ' (--:--)';

            $pmDutyHrs = ($rA['pm_in_assign'] && $rA['pm_out_assign']) ? functions::min_diff($rA['pm_in_assign'],$rA['eat_date'],$rA['pm_out_assign'],$rA['eat_date']) : 0;

            if( $hoursPerDay <= ($amDutyHrs + $pmDutyHrs) )
                $hoursPerDay = $amDutyHrs + $pmDutyHrs;
            $regularDutyHours += $rA['duty_min'];
            $otDutyHours += $rA['ot_min'];
            $totalAbsent += $rA['late_min'] + $rA['under_min'];

            $otin = ($rA['ot_in']) ? functions::MilToTwelve($rA['ot_in']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
            $otout = ($rA['ot_out']) ? functions::MilToTwelve($rA['ot_out']) : functions::MilToTwelve($db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date'])));
            $arrDailyAttendance[] = array('eatd_id'=>$rA['eatd_id'],'dailyName'=>$dailyName,'amin'=>$amin,'amout'=>$amout,'pmin'=>$pmin,'pmout'=>$pmout,'otin'=>$otin,'otout'=>$otout,'dutyHours'=>$rA['duty_min'],'otHours'=>$rA['ot_min'],'late'=>$rA['late_min'],'undertime'=>$rA['under_min']);
        endwhile;    
    }
?>
                <br><br>
                <table width="98%" border="0" class="table table-hover table-bordered" style="font-size: 12px;">
                    <tr>
                        <th width="20%">NAME</th>
                        <th width="11%">POSITION</th>
                        <th width="10%"><div align="right">Rendered Days (Hours)</div></th>
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
                        <td height="30px"><div align="left"><strong><?php echo $emp_no.' - '.$emp_name;?></strong></div></td>
                        <td><?php echo $position?></td>
                        <td>
                            <div align="right">
                                <?php
                                echo $regDays = ($regularDutyHours) ? number_format(($regularDutyHours/$hoursPerDay),2) : '0';
                                echo ($regDays > 1) ? ' day/s' : ' day';
                                echo ($regularDutyHours) ? ' ('.functions::min_to_hour($regularDutyHours).')' : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <?php
                                echo $absentDays = ($hoursPerDay) ? number_format(($totalAbsent/$hoursPerDay),2) : '0';
                                echo ($absentDays > 1) ? ' day/s' : ' day';
                                echo ($totalAbsent) ? ' ('.functions::min_to_hour($totalAbsent).')' : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <?php
                                echo $requiredDays = ( ($regularDutyHours || $totalAbsent) && $hoursPerDay) ? number_format(($regularDutyHours + $totalAbsent) / $hoursPerDay,2) : '0';
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
                                    echo $tDuty = ($totalDuty && $hoursPerDay) ? number_format(($totalDuty/$hoursPerDay),2) : '0';
                                    echo ($tDuty > 1) ? ' day/s' : ' day';
                                    echo ($totalDuty) ? ' ('.functions::min_to_hour($totalDuty).')' : '';
                                    ?>
                                </strong>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php if($has_attendance){?>
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
                        <td><?php echo $dly['dailyName'];?></td>
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
                <?php }else{?>
                    <div align="center">Attendance not applicable</div>
                <?php }?>
                <br><br><br><br>
<?php
endforeach;
?>
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