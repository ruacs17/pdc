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
$confirmed='';

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$fn = $db->getValue('emp_attendance','excelfile',array('eat_id'=>$eatid));
$p_id = $db->getValue('emp_attendance','proj_id',array('eat_id'=>$eatid));
$date_start = $db->getValue('emp_attendance','date_start',array('eat_id'=>$eatid));
$date_end = $db->getValue('emp_attendance','date_end',array('eat_id'=>$eatid));
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eatid));
$attendance_ready = $db->getValue('emp_attendance','attendance_ready',array('eat_id'=>$eatid));
if($attendance_ready==0)
    die('Please confirm the attendance summary preview!');
if( isset($_REQUEST['c']) ){
    $payroll_value = functions::decode($_REQUEST['c']);
    if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$eatid,'confirmed'=>0)) ){
        $db->update('emp_attendance',array('confirmed'=>1,'amount'=>$payroll_value),array('eat_id'=>$eatid));
        $confirmed=1;
    }
    else{
        $db->update('emp_attendance',array('confirmed'=>0,'amount'=>$payroll_value),array('eat_id'=>$eatid));
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?eatid='.functions::encode($eatid));
}

$arrSelEmp = (isset($_SESSION['arr_attendance_emp'])) ? $_SESSION['arr_attendance_emp'] : array();

$ft = $db->getValue('emp_attendance','filetype',array('eat_id'=>$eatid));
$path = '../attendance/';
$filename=$path.$fn;
$totalRow = 0;
$content = array();
$arrAttendanceRecord=array();
$arrPayrollList=array();


$qAttendance = $db->select('emp_attendance_detail','*',array('eat_id'=>$eatid),'AND emp_id IN (SELECT emp_id FROM employee) ORDER BY emp_id,eat_date ASC');
#echo $db->last_query;
while($rA = $db->fetch_array($qAttendance)):
    $totalRow++;
    $emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$rA['emp_id']));
    $otIn = ($rA['ot_in']) ? $rA['ot_in'] : $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date']));
    $otOut = ($rA['ot_out']) ? $rA['ot_out'] : $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$rA['emp_id'],'actual_start_date'=>$rA['eat_date']));

    $arrAttendanceRecord[$emp_no][$rA['eat_date']] = array('amin'=>$rA['am_in'],'amout'=>$rA['am_out'],'pmin'=>$rA['pm_in'],'pmout'=>$rA['pm_out'],'otin'=>$otIn,'otout'=>$otOut);
endwhile;



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
    $arrPayrollList[] = array('emp_id'=>$emp_id,'emp_no'=>$employee,'name'=>$name,'position'=>$position,'salary'=>$salary,'sss_contribution'=>$sss_contribution,'philhealth_contribution'=>$philhealth_contribution,'pagibig_contribution'=>$pagibig_contribution,'other_deduction'=>$other_deduction,'rate_day'=>$txSalDayDisp,'rate_hour'=>$txSalHourDisp,
        'regular_hours'=>$empRegDutyHours,'regular_amount'=>$empRegDutyAmount,'overtime_hours'=>$empOTDutyHours,'overtime_amount'=>$empOTDutyAmount,'undertime_hours'=>$empUnderDutyHours,'undertime_amount'=>$empUnderDutyAmount,'late_hours'=>$empLateDutyHours,'late_amount'=>$empLateDutyAmount,'gross_payable_amount'=>$empGrossPayableAmount,'total_amount'=>$empTotalAmount,'net_pay'=>$empNetPayableAmount,'total_deduction'=>$empDeduction,'absentAmount'=>$empAbsentAmount,'absentHours'=>$empAbsentHours);
endforeach;
if(count($arrPayrollList))
    functions::sortMultiArray($arrPayrollList,$orderBy='name',$sort_AscDesc='ASC');
#echo '<pre>';print_r($arrPayrollList);echo '</pre>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Employee Payroll</title>
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
                <li class="active"><a href="attendance_payroll_view.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Payroll</a></li>
                <li style="display:none;"><a href="attendance_read.php?eatid=<?php echo functions::encode($eatid)?>">Attendance</a></li>
                <li><a href="attendance_select.php?eatid=<?php echo functions::encode($eatid)?>">Select Name</a></li>
                <li><a href="attendance_upload.php?eatid=<?php echo functions::encode($eatid)?>">Upload Att. Log</a></li>
            </ul>
        </div>
        <div align="right">
            <a id="prPrint" href="attendance_payroll_print.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;
        </div><br>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <div align="center" style="padding-bottom: 15px;"><h2>PAYROLL PREVIEW</h2></div>
                <table border="0" width="90%">
                    <tr>
                        <td width="10%" height="30px">Project</td>
                        <td><?php echo $proj_name;?></td>
                    </tr>
                    <tr>
                        <td height="30px">Payroll No</td>
                        <td><?php echo $db->getValue('emp_attendance','payroll_no',array('eat_id'=>$eatid));?></td>
                    </tr>
                    <tr>
                        <td height="30px">Period Covered</td>
                        <td><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end);?></td>
                    </tr>
                    <tr>
                        <td height="30px">Confirm Status</td>
                        <td>
                            <input type="checkbox" name="chkConf" id="chkConf" value="<?php echo functions::encode($totalNetAmount);?>" onClick="stat(this.value)" <?php if($confirmed)echo 'checked';?>> <?php echo ($db->getValue('emp_attendance','confirmed',array('eat_id'=>$eatid))==1) ? 'Confirmed' : 'Unconfirmed';?>
                        </td>
                    </tr>
                </table>
                <table width="98%" border="0" class="table table-hover table-bordered" style="font-size:12px">
                    <tr>
                        <th width="15%" rowspan="2">NAME</th>
                        <th width="5%" rowspan="2"><div align="center">Basic Sal</div></th>
                        <th width="5%" rowspan="2"><div align="center">Regular</div></th>
                        <th width="5%" rowspan="2"><div align="center">OT</div></th>
                        <th width="7%" rowspan="2"><div align="center">GROSS Pay</div></th>
                        <th width="7%" rowspan="2"><div align="center">NET Pay</div></th>
                        <th colspan="6" align="center"><div align="center">DEDUCTIONS</div></th>
                    </tr>
                    <tr>
                        <td width="5%"><div align="center">ABSENT</div></td>
                        <td width="5%"><div align="center">SSS</div></td>
                        <td width="5%"><div align="center">PH</div></td>
                        <td width="5%"><div align="center">HMDF</div></td>
                        <td width="5%"><div align="center">OTHERS</div></td>
                        <td width="5%"><div align="center">TOTAL DEDUCTION</div></td>
                    </tr>
                    <tr>
                        <td colspan="12">&nbsp;</td>
                    </tr>
                <?php
                $countEmp=1;
                foreach($arrPayrollList as $pd):
                ?>
                    <tr>
                        <td height="30px">
                            <a id="vw<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'attendance_read_selected.php?eatid=<?php echo functions::encode($eatid);?>&empid=<?php echo functions::encode($pd['emp_id']);?>','Attendance Detail','1')">
                            <?php echo $countEmp++.'. '.$pd['name'];?>
                            </a>
                        </td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['salary']);?></div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['regular_amount']);?></div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['overtime_amount']);?></div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['gross_payable_amount']);?></div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['net_pay']);?></div></td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['absentAmount']);?></div></td>
                        <td>
                            <div align="right">
                                <a id="ss<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Contribution" data-rel="tooltip" onclick="showThis(this.id,'payroll_contribution_manage.php?eid=<?php echo functions::encode($pd['emp_id']);?>','Contribution Detail')">
                                <?php echo functions::formatMoney($pd['sss_contribution']);?>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <a id="ph<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Contribution" data-rel="tooltip" onclick="showThis(this.id,'payroll_contribution_manage.php?eid=<?php echo functions::encode($pd['emp_id']);?>','Contribution Detail')">
                                <?php echo functions::formatMoney($pd['philhealth_contribution']);?>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <a id="pg<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Contribution" data-rel="tooltip" onclick="showThis(this.id,'payroll_contribution_manage.php?eid=<?php echo functions::encode($pd['emp_id']);?>','Contribution Detail')">
                                <?php echo functions::formatMoney($pd['pagibig_contribution']);?>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div align="right">
                                <a id="deduct<?php echo $countEmp?>" class="thickbox" style="cursor: pointer;" title="Manage Other Deduction" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_manage.php?eatid=<?php echo functions::encode($eatid);?>&eid=<?php echo functions::encode($pd['emp_id']);?>','Contribution Detail')">
                                <?php echo functions::formatMoney($pd['other_deduction']);?>
                                </a>
                            </div>
                        </td>
                        <td><div align="right"><?php echo functions::formatMoney($pd['total_deduction']);?></div></td>
                    </tr>
                <?php endforeach;?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong><?php echo ($totalRegDutyAmount) ? functions::formatMoney($totalRegDutyAmount) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalOTDutyAmount) ? functions::formatMoney($totalOTDutyAmount) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalGrossPayableAmount) ? functions::formatMoney($totalGrossPayableAmount) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalNetAmount) ? functions::formatMoney($totalNetAmount) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalAbsentAmount) ? functions::formatMoney($totalAbsentAmount) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalSSS) ? functions::formatMoney($totalSSS) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalPagibig) ? functions::formatMoney($totalPagibig) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalPH) ? functions::formatMoney($totalPH) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalOthers) ? functions::formatMoney($totalOthers) : 0;?></strong></div></td>
                        <td><div align="right"><strong><?php echo ($totalDeduction) ? functions::formatMoney($totalDeduction) : 0;?></strong></div></td>
                    </tr>
                <?php if($countEmp > 15){?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Regular</strong></div></td>
                        <td><div align="right"><strong>OT</strong></div></td>
                        <td><div align="right"><strong>GROSS Pay</strong></div></td>
                        <td><div align="right"><strong>NET Pay</strong></div></td>
                        <td><div align="right"><strong>ABSENT</strong></div></td>
                        <td><div align="right"><strong>SSS</strong></div></td>
                        <td><div align="right"><strong>PHILHEALTH</strong></div></td>
                        <td><div align="right"><strong>HMDF</strong></div></td>
                        <td><div align="right"><strong>OTHERS</strong></div></td>
                        <td><div align="right"><strong>TOTAL DEDUCTION</strong></div></td>
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
