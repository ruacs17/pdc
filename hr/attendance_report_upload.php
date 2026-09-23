<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

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

#$logs = new Logs();
#$logs->save('visit');
$filename='';$uploaded_file='';$filetype='';
if( isset($_POST['upload']) && $eatid ){
    echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
    echo 'Please wait while attendance is still processing........<br>';
    echo '<div id="spinner"></div>';
    if( !empty($_FILES['file']['name']) ){
        $filetype = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
        $filename = date('Ymd');
        $path = '../attendance/';
        $upload=0;
        
        if ($_FILES["file"]["error"] > 0){
            echo "Error: " . $_FILES["file"]["error"] . "<br />";
        }
        else{
            if( ( $filetype==='xlsx' || $filetype==='xls' ) && ($_FILES["file"]["size"] <= 20000000) ){
                $fileExt = $filetype;
                if (file_exists($path.$filename.'.'.$fileExt)){
                    $count = 1;
                    while(file_exists($path.$filename.'.'.$fileExt)){
                        $filename = date('Ymd').'('.$count.')';
                        $uploaded_file=$path.$filename.'.'.$fileExt;
                        $count++;
                    }
                    $upload = move_uploaded_file($_FILES["file"]["tmp_name"],$uploaded_file);
                }
                else{
                    $uploaded_file=$path.$filename.'.'.$fileExt;
                    $upload = move_uploaded_file($_FILES["file"]["tmp_name"],$uploaded_file);
                }
                if($upload){

                    $totalRow = 0;
                    if($filetype==='xlsx'){
                        require_once('../class/read_excel_xlsx.php');
                        if ( $xlsx = read_excel_xlsx::parse($uploaded_file) ) {
                            $content = $xlsx->rows() ;
                        } else {
                            echo read_excel_xlsx::parseError();
                        }
                    }
                    if($filetype==='xls'){
                        require_once('../class/read_excel_xls.php');
                        if ( $xls = read_excel_xls::parse($uploaded_file) ) {
                            $content = $xls->rows() ;
                        } else {
                            echo read_excel_xls::parseError();
                        }
                    }
                    /*$dateRange = isset($content[2][2]) ? $content[2][2] : '';
                    $dateRangeFrom='';$dateRangeTo='';$yearFrom='';$dateFrom='';$monFrom='';
                    if($dateRange){
                        $dateRangeExp = explode('~',$dateRange);
                        $dateRangeFrom = isset($dateRangeExp[0]) ? trim($dateRangeExp[0]) : '';
                        $dateRangeTo = isset($dateRangeExp[1]) ? trim($dateRangeExp[1]) : '';
                    }
                    if( $dateRangeFrom!=$date_start && $dateRangeTo!=$date_end ){
                        unlink($uploaded_file);
                        functions::say('Dates in excel does not match!');
                        functions::sendTo($_SERVER['PHP_SELF'].'?eatid='.functions::encode($eatid));
                    }
                    else */if($uploaded_file){
                        $db->update('emp_attendance',array('excelfile'=>$filename.'.'.$fileExt,'filetype'=>$fileExt,'date_added'=>date('Y-m-d'),'upload_done'=>0),array('eat_id'=>$eatid));
                        functions::sendTo('attendance_report_preview.php?eatid='.functions::encode($eatid));
                    }
                }
            }
            else{
                functions::say("Inappropriate File type");
                functions::sendTo($_SERVER['PHP_SELF'].'?eatid='.functions::encode($eatid));
            }
        }
    }
    else{
        functions::say('Please upload an excel file.');
        functions::sendTo($_SERVER['PHP_SELF']);
    }
}
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">

    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
        </div>
        <?php if($eatid){?>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="attendance_report_preview.php?eatid=<?php echo functions::encode($eatid)?>">Attendance Preview</a></li>
                <li class="active"><a href="attendance_report_upload.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Upload Att. Log</a></li>
            </ul>
        </div>
        <?php }?>
        <div class="box-content">
            <form class="form-horizontal" method="post" enctype="multipart/form-data">
                <div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE UPLOAD</h2></div>
                <div align="center">
                <table border="0" width="40%">
                    <tr>
                        <td>
                            <div align="left">Project / Group : 
                                <strong>
                                <?php 
                                $qProj = $db->select('emp_assignment ea, project p','*',array('eas_id'=>$eas_id),'AND ea.proj_id=p.proj_id ORDER BY proj_name');
                                while($rProj = $db->fetch_array($qProj)):
                                    $projName = $db->getValue('project','proj_name',array('proj_id'=>$rProj['proj_id']));
                                    echo strtoupper($projName); echo ($rProj['eas_name']) ? ' ( '.$rProj['eas_name'].' )' : '';
                                endwhile;
                                ?>
                                </strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="40px" align="center">
                            <div align="left">
                                Period Cover: <strong><?php echo functions::datearr($date_start) .' - '.functions::datearr($date_end).' ('.functions::date_diff($date_start,$date_end,$includeDay1=1).' days)'?></strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php if($eas_id){?>
                            <div align="left"><strong>Group Members</strong></div>
                            <div align="left">
                            <table width="100%" align="center" class="tablea table-hover table-bordereds" border="1">
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
                </table><br><br><br>
                <table border="0">
                    <tr>
                        <td>
                            <div align="left" style="padding-bottom: 15px;">Select Excel file to upload: <input type="file" name="file" multiple></div>
                            <div align="center" style="padding-top: 40px;">
                                <input type="submit" name="upload" id="upload" value=" UPLOAD " class="btn btn-primary">
                            </div>
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
<!-- end: JavaScript-->
</body>
</html>