<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));

$mhd_id = (isset($_REQUEST['mhd']) && !empty($_REQUEST['mhd']) ) ? $db->clean(functions::decode($_REQUEST['mhd'])) : 0;

$q = $db->select('medicine_healthcare_duration','*',array('mhd_id'=>$mhd_id));
$r = $db->fetch_array($q);
$mhd_start=$r['mhd_start'];$mhd_end=$r['mhd_end'];$mhd_amount=$r['mhd_amount'];$active_status=$r['active_status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Healthcare Benefit Duration</title>
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
if(isset($_POST['btnSubmit'])){
    $mhd_start = ( isset($_POST['txDateStart']) && !empty($_POST['txDateStart']) ) ? trim($_POST['txDateStart']) : '';
    $mhd_end = ( isset($_POST['txDateEnd']) && !empty($_POST['txDateEnd']) ) ? trim($_POST['txDateEnd']) : '';
    $mhd_amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : 0;
    $active_status = ( isset($_POST['selStat']) && !empty($_POST['selStat']) ) ? trim($_POST['selStat']) : 0;
    if($mhd_start && $mhd_end && $mhd_amount){
        $date_diff = functions::date_diff($mhd_start,$mhd_end);
        if($date_diff <= 0){
            functions::say('Please select date properly!');
        }
        else{
            if($mhd_id){
                $db->update('medicine_healthcare_duration',array('mhd_start'=>$mhd_start,'mhd_end'=>$mhd_end,'mhd_amount'=>$mhd_amount,'active_status'=>$active_status),array('mhd_id'=>$mhd_id));
                functions::say('Changes Saved!');
                functions::sendTo($_SERVER['PHP_SELF'].'?mhd='.functions::encode($mhd_id));
            }
            else{
                $mhd_id = $db->insert('medicine_healthcare_duration',array('mhd_start'=>$mhd_start,'mhd_end'=>$mhd_end,'mhd_amount'=>$mhd_amount,'active_status'=>$active_status));
                if($mhd_id){
                    functions::say('Record Added!');
                    functions::sendTo($_SERVER['PHP_SELF'].'?mhd='.functions::encode($mhd_id));
                }
                else
                    functions::say('Please fill up the form properly!');
            }
        }
    }
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Healthcare Benefit Duration</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <div align="center">
                <div style="width:70%">
                <table class="tablea" align="center" width="50%" border="1">
                    <tr>
                        <td width="40%" style="padding: 10px;"><div align="right">Date Start</div></td>
                        <td style="padding: 10px; padding-top:20px;">
                            <div align="left">
                                <a href="javascript:NewCssCal('txDateStart')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
                                <input name="txDateStart" type="text" class="span6 mytextbox" id="txDateStart" value="<?php echo ($mhd_start) ? $mhd_start : date('Y-m-d')?>" style="width: 90px;" readonly required>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><div align="right">Date End</div></td>
                        <td style="padding: 10px; padding-top:20px;">
                            <div align="left">
                                <a href="javascript:NewCssCal('txDateEnd')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
                                <input name="txDateEnd" type="text" class="span6 mytextbox" id="txDateEnd" value="<?php echo ($mhd_end) ? $mhd_end : date('Y-m-d')?>" style="width: 90px;" readonly required>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><div align="right">Amount</div></td>
                        <td style="padding: 10px; padding-top:20px;"><div align="left"><input type="text" style="width:120px;" name="txAmount" id="txAmount" value="<?php echo ($mhd_amount) ? functions::formatMoney($mhd_amount) : '';?>" onkeyup="FormatCurrency(this);" required></div></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><div align="right">Status</div></td>
                        <td style="padding: 10px; padding-top:20px;">
                            <select name="selStat" id="selStat">
                                <option value="0" <?php if($active_status=='0')echo 'selected="selected"'; ?>>Inactive</option>
                                <option value="1" <?php if($active_status=='1')echo 'selected="selected"'; ?>>Active</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="padding: 10px;"><div align="left"><input type="submit" class="btn btn-small btn-primary" name="btnSubmit" id="btnSubmit" value=" Save "></div></td>
                    </tr>
                </table>
                </div>
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