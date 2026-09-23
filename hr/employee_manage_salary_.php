<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$saltype = (isset($_REQUEST['saltype']) && !empty($_REQUEST['saltype']) ) ? $_REQUEST['saltype'] : "";
$itemEdt = (isset($_REQUEST['eeEdt']) && !empty($_REQUEST['eeEdt']) ) ? functions::decode($_REQUEST['eeEdt']) : 0;
$itemDel = (isset($_REQUEST['eeDlt']) && !empty($_REQUEST['eeDlt']) ) ? functions::decode($_REQUEST['eeDlt']) : 0;
$selSalType = $db->getValue('emp_salary','es_type',array('emp_id'=>$eid),'ORDER BY es_date DESC LIMIT 1');
if($saltype){
    $db->update('emp_salary',array('es_type'=>$saltype),array('emp_id'=>$eid));
    functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Employee Salary</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <script src="../js/formatCurrency.js"></script>
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
                <div align="center" style="padding-bottom: 15px;"><h2>SALARY DETAILS</h2></div>
                <div align="left" style="padding-bottom: 15px;"><strong><?php echo $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_id'=>$eid)); ?></strong></div>
                <table width="80%" align="center" border="0" class="table table-bordered table-hover" style="background-color:#E4E1E1; font-size: 12px;">
                    <tr>
                        <td width="10%"><strong>DATE</strong></td>
                        <td width="10%"><strong>MONTHLY</strong></td>
                        <td width="10%"><strong>DAYS PER MONTH</strong></td>
                        <td width="10%"><strong>DAILY</strong></td>
                        <td width="10%"><strong>HOURLY</strong></td>
                        <td width="10%"><strong>MINUTE</strong></td>
                        <td width="10%"><strong>Type</strong></td>
                    </tr>
                    <?php
                        $q = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC');
                        while($r = $db->fetch_array($q)):
                            $rID = $r['es_id'];
                    ?>
                    <tr style="background-color:#ececec">
                        <td><?php echo functions::datearr($r['es_date'])?></td>
                        <td><?php echo functions::formatMoney($r['es_salary'])?></td>
                        <td><?php echo $r['working_days']?></td>
                        <td><?php echo functions::formatMoney($r['es_daily'],4)?></td>
                        <td><?php echo functions::formatMoney($r['es_hourly'],4)?></td>
                        <td><?php echo functions::formatMoney($r['es_minute'],4)?></td>
                        <td><?php echo $r['es_type']?></td>
                    </tr>
                    <?php endwhile;?>
                </table>
                <br><br><br>
                <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
                    <tr>
                        <td>
                            <div align="center">
                                <select name="selSalType" id="selSalType" onChange="itmSel(this.value)">
                                    <option value="">--select-</option>
                                    <option value="fixed" <?php if($selSalType=='fixed'){echo 'selected="selected"';} ?>>(Labor) Daily Fixed Wage</option>
                                    <option value="flexible" <?php if($selSalType=='flexible'){echo 'selected="selected"';} ?>>(Office Personnel) Monthly</option>
                                </select>
                            </div>
                        </td>
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
<script>
function itmSel(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?eid=<?php echo functions::encode($eid)?>&saltype="+PiEwgD}
</script>
<!-- end: JavaScript-->
</body>
</html>