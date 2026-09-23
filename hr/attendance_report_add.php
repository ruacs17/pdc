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
$filename='';$payroll_type='';$selMonFrom='';$selDayFrom='';$selYrFrom=date('Y');$selMonTo='';$selDayTo='';$selYrTo=date('Y');

$eas_id = (isset($_REQUEST['easid']) && !empty($_REQUEST['easid']) ) ? functions::decode($_REQUEST['easid']) : 0;

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$p_id = $rEatID['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$p_id));

$attendance_ready = $rEatID['attendance_ready'];
$date_start = ($rEatID['date_start']) ? $rEatID['date_start'] : "";
$date_end = ($rEatID['date_end']) ? $rEatID['date_end'] : "";
$eas_id = ($rEatID['eas_id']) ? $rEatID['eas_id'] : $eas_id;
$worker_type = $rEatID['payroll_type'];
$eas_name = $db->getValue('emp_assignment','eas_name',array('eas_id'=>$eas_id));
$eas_proj_id = $db->getValue('emp_assignment','proj_id',array('eas_id'=>$eas_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Employee Attendance Upload</title>
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
    <script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
if( isset($_POST['btnCreate']) ){
    $upload='';$eat_id='';
    $eas_id = ( isset($_POST['selAssignment']) && !empty($_POST['selAssignment']) ) ? functions::decode($_POST['selAssignment']) : 0;
    $proj_id = $db->getValue('emp_assignment','proj_id',array('eas_id'=>$eas_id));
    $worker_type = $db->getValue('emp_assignment','worker_type',array('eas_id'=>$eas_id));
    $date_start = ( isset($_POST['txStartDate']) && !empty($_POST['txStartDate']) ) ? $_POST['txStartDate'] : '';
    $date_end = ( isset($_POST['txEndDate']) && !empty($_POST['txEndDate']) ) ? $_POST['txEndDate'] : '';

    $date_start=0;
    $selMonFrom = ( isset($_POST['selMonFrom']) ) ? trim($_POST['selMonFrom']) : '';
    $selDayFrom = ( isset($_POST['selDayFrom']) ) ? trim($_POST['selDayFrom']) : '';
    $selYrFrom = ( isset($_POST['selYrFrom']) ) ? trim($_POST['selYrFrom']) : '';
    $date_start=( !empty($selMonFrom) && !empty($selDayFrom) && !empty($selYrFrom) ) ? $selYrFrom.'-'.$selMonFrom.'-'.$selDayFrom : '';

    $selMonTo = ( isset($_POST['selMonTo']) ) ? trim($_POST['selMonTo']) : '';
    $selDayTo = ( isset($_POST['selDayTo']) ) ? trim($_POST['selDayTo']) : '';
    $selYrTo = ( isset($_POST['selYrTo']) ) ? trim($_POST['selYrTo']) : '';
    $date_end = ( !empty($selMonTo) && !empty($selDayTo) && !empty($selYrTo) ) ? $selYrTo.'-'.$selMonTo.'-'.$selDayTo : '';

    $arrField = array('eas_id'=>$eas_id,'date_added'=>date('Y-m-d'),'date_start'=>$date_start,'date_end'=>$date_end,'proj_id'=>$proj_id,'payroll_type'=>$worker_type);
    if($eas_id && $date_start && $date_end){
        if( $db->getValue('emp_attendance','count(*)',array('eas_id'=>$eas_id),'AND (date_start BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" OR date_end BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'")')==0 ){
            $qIns = $db->insertPrint('emp_attendance',$arrField);
            $db->query($qIns);
            $eat_id = $db->insert_id();
            functions::sendTo('attendance_report_upload.php?eatid='.functions::encode($eat_id));
        }
        else{
            functions::say('Date already Exist! Please choose different dates.');
        }
    }
    else
        functions::say('Please fill up the form properly!');
}
?>
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
                <?php if($eatid){?><li><a href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>">Summary</a></li><?php }?>
                <li class="active"><a href="attendance_report_add.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Create Attendance Report</a></li>
            </ul>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE REPORT</h2></div>
                <div align="center">
                <?php if($eatid){?><input type="hidden" name="txhdEatID" id="txhdEatID" value="<?php echo functions::encode($eatid)?>"><?php }?>
                <table border="0">
                    <tr>
                        <td>
                            <div align="left">
                                <select name="selAssignment" id="selAssignment" data-rel="chosen" style="width:800px;font-size:12px;" onChange="grpSel(this.value)">
                                    <option value="">--Select Project / Department / Group--</option>
                                    <?php 
                                    $qProj = $db->select('emp_assignment ea, project p','*',array(),'WHERE ea.proj_id=p.proj_id ORDER BY proj_name');
                                    while($rProj = $db->fetch_array($qProj)):
                                        $projName = $db->getValue('project','proj_name',array('proj_id'=>$rProj['proj_id']));
                                    ?>
                                    <option value="<?php echo functions::encode($rProj['eas_id'])?>" <?php if($eas_id==$rProj['eas_id'])echo 'selected="selected"';?>><?php echo strtoupper($projName); echo ($rProj['eas_name']) ? ' ( '.$rProj['eas_name'].' )' : '';?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="140px" align="center">
                            <div align="center">
                            <div align="left" style="float:left">
                                Date Start:
                                <select name="selMonFrom" id="selMonFrom" data-rel="chosen" style="width:80px;">
                                    <option value="">Month</option>
                                    <option value="01" <?php if($selMonFrom=='01')echo 'selected="selected"';?>>Jan</option>
                                    <option value="02" <?php if($selMonFrom=='02')echo 'selected="selected"';?>>Feb</option>
                                    <option value="03" <?php if($selMonFrom=='03')echo 'selected="selected"';?>>Mar</option>
                                    <option value="04" <?php if($selMonFrom=='04')echo 'selected="selected"';?>>Apr</option>
                                    <option value="05" <?php if($selMonFrom=='05')echo 'selected="selected"';?>>May</option>
                                    <option value="06" <?php if($selMonFrom=='06')echo 'selected="selected"';?>>Jun</option>
                                    <option value="07" <?php if($selMonFrom=='07')echo 'selected="selected"';?>>Jul</option>
                                    <option value="08" <?php if($selMonFrom=='08')echo 'selected="selected"';?>>Aug</option>
                                    <option value="09" <?php if($selMonFrom=='09')echo 'selected="selected"';?>>Sep</option>
                                    <option value="10" <?php if($selMonFrom=='10')echo 'selected="selected"';?>>Oct</option>
                                    <option value="11" <?php if($selMonFrom=='11')echo 'selected="selected"';?>>Nov</option>
                                    <option value="12" <?php if($selMonFrom=='12')echo 'selected="selected"';?>>Dec</option>
                                </select>
                                <select name="selDayFrom" id="selDayFrom" data-rel="chosen" style="width:80px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($selDayFrom==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="selYrFrom" id="selYrFrom" data-rel="chosen" style="width:80px;">
                                    <option value="">Year</option>
                                    <?php for($y=(date('Y')+1);$y>=(date('Y')-2);$y--):?>
                                    <option value="<?php echo $y;?>" <?php if($selYrFrom==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                            </div>
                            <div align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                Date End: 
                                <select name="selMonTo" id="selMonTo" data-rel="chosen" style="width:80px;">
                                    <option value="">Month</option>
                                    <option value="01" <?php if($selMonTo=='01')echo 'selected="selected"';?>>Jan</option>
                                    <option value="02" <?php if($selMonTo=='02')echo 'selected="selected"';?>>Feb</option>
                                    <option value="03" <?php if($selMonTo=='03')echo 'selected="selected"';?>>Mar</option>
                                    <option value="04" <?php if($selMonTo=='04')echo 'selected="selected"';?>>Apr</option>
                                    <option value="05" <?php if($selMonTo=='05')echo 'selected="selected"';?>>May</option>
                                    <option value="06" <?php if($selMonTo=='06')echo 'selected="selected"';?>>Jun</option>
                                    <option value="07" <?php if($selMonTo=='07')echo 'selected="selected"';?>>Jul</option>
                                    <option value="08" <?php if($selMonTo=='08')echo 'selected="selected"';?>>Aug</option>
                                    <option value="09" <?php if($selMonTo=='09')echo 'selected="selected"';?>>Sep</option>
                                    <option value="10" <?php if($selMonTo=='10')echo 'selected="selected"';?>>Oct</option>
                                    <option value="11" <?php if($selMonTo=='11')echo 'selected="selected"';?>>Nov</option>
                                    <option value="12" <?php if($selMonTo=='12')echo 'selected="selected"';?>>Dec</option>
                                </select>
                                <select name="selDayTo" id="selDayTo" data-rel="chosen" style="width:80px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($selDayTo==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="selYrTo" id="selYrTo" data-rel="chosen" style="width:80px;">
                                    <option value="">Year</option>
                                    <?php for($y=date('Y');$y>=1900;$y--):?>
                                    <option value="<?php echo $y;?>" <?php if($selYrTo==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                            </div>
                            </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div align="left" style="padding-top: 40px;">
                                <input type="submit" name="btnCreate" id="btnCreate" value=" SAVE " class="btn btn-primary">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php if($eas_id){?>
                            <br><br><br>
                            <div align="left"><strong>Group Members</strong></div>
                            <div align="left">
                            <table width="70%" align="center" class="tablea table-hover table-bordereds" border="1">
                                <tr>
                                    <th width="20%" style="padding: 5px"><div align="left">ID Number</div></th>
                                    <th width="40%" style="padding: 5px"><div align="left">Name</div></th>
                                <?php
                                $qDisp = $db->select('emp_assign_detail ead, employee e','*',array('eas_id'=>$eas_id),'AND ead.emp_id=e.emp_id ORDER BY lname');
                                while($rDisp = $db->fetch_array($qDisp)):
                                    $rID = $rDisp['ead_id'];
                                ?>
                                <tr>
                                    <td style="padding: 5px"><?php echo $rDisp['emp_no']?></td>
                                    <td style="padding: 5px"><?php echo $rDisp['lname'].', '.$rDisp['fname']?></td>
                                </tr>
                            <?php endwhile;?>
                            </table>
                            </div>
                            <?php }?>
                        </td>
                    </tr>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function grpSel(PiEwgD){
    if(PiEwgD)
        window.location="<?php echo $_SERVER['PHP_SELF']?>?eatid=<?php echo functions::encode($eatid)?>&easid="+PiEwgD
    else
        window.location="<?php echo $_SERVER['PHP_SELF']?>"
}
</script>
<!-- end: JavaScript-->
</body>
</html>