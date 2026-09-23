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
$jv_id = ( isset($_REQUEST['jv']) && !empty($_REQUEST['jv']) ) ? functions::decode($_REQUEST['jv']) : '';
$qjv = $db->select('journal_voucher','*',array('jv_id'=>$jv_id));
$rjv = $db->fetch_array($qjv);
$txApprovedBy=$rjv['approved_by'];$txPreparedBy=$rjv['prepared_by'];$txRemarks=$rjv['remarks'];$selProj=$rjv['proj_id'];
$xdate = explode('-',$rjv['jv_date']);
$selMon = isset($xdate[1]) ? $xdate[1] : '';
$selDay = isset($xdate[2]) ? $xdate[2] : '';
$selYear = isset($xdate[0]) ? $xdate[0] : '';
if($jv_id==""){
    $selYear=date('Y');
    $selMon=date('m');
    $selDay=date('d');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Journal Statement Form</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <script src="../js/formatCurrency.js"></script>
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
if( isset($_POST['btnAdd']) ){
    $txRemarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
    $selProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    $txApprovedBy = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : '';
    $txPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
    $selMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $selDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $selYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $jv_date = ($selMon && $selDay && $selYear) ? $selYear.'-'.$selMon.'-'.$selDay : NULL;

    if( $jv_date && $selProj && $txPreparedBy ){
        if($jv_id){//For Edit
            $db->update('journal_voucher',array('proj_id'=>$selProj,'jv_date'=>$jv_date,'prepared_by'=>$txPreparedBy,'approved_by'=>$txApprovedBy,'remarks'=>$txRemarks),array('jv_id'=>$jv_id));
            functions::say('Changes Saved!');
        }
        else{
            $insertID = $db->insert('journal_voucher',array('proj_id'=>$selProj,'jv_date'=>$jv_date,'prepared_by'=>$txPreparedBy,'approved_by'=>$txApprovedBy,'remarks'=>$txRemarks));
            if($insertID){
                $jv_no = $db->getValue('journal_voucher','concat(substring(jv_date,1,4),substring(jv_date,6,2),jv_id) as dte',array('jv_id'=>$insertID));
                $db->update('journal_voucher',array('jv_no'=>$jv_no),array('jv_id'=>$insertID));
                functions::say("Statement Added!");
                functions::sendTo("journal_voucher_manage_item.php?jv=".functions::encode($insertID));
            }
            else{
                functions::say("Please try again later!");
            }
        }
    }
    else{
        functions::say("Please fill up the form properly!");
    }
}
?>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid sortable">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Journal Statement Manage Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">           
                <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
                    <tr>
                        <td width="17%" height="30">Journal Date</td>
                        <td width="43%">
                            <select name="bdMon" id="bdMon" style="width:80px;" required>
                                <option value="">Month</option>
                                <option value="01" <?php if($selMon=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($selMon=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($selMon=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($selMon=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($selMon=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($selMon=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($selMon=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($selMon=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($selMon=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($selMon=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($selMon=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($selMon=='12')echo 'selected="selected"';?>>Dec</option>
                            </select>
                            <select name="bdDay" id="bdDay" style="width:60px;" required>
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($selDay==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                            <select name="bdYear" id="bdYear" style="width:70px;" required>
                                <option value="">Year</option>
                                <?php for($y=(date('Y')+2);$y>=2012;$y--):?>
                                <option value="<?php echo $y;?>" <?php if($selYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                                <?php endfor;?>
                            </select>
                            <span class="help-inline warning" id="msgDate" style="font-weight:bold;" name="msgDate"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30">Project / Department</td>
                        <td>
                            <select name="selProj" id="selProj" data-rel="chosen" style="width:700px;">
                                <option value="">--select--</option>
                                <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                                      while($rProj = $db->fetch_array($qProj)):
                                ?>
                                <option value="<?php echo $rProj['proj_id']?>" <?php if($selProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
                                <?php endwhile;?>
                            </select>
                            <span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30">Prepared By</td>
                        <td>
                            <select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:400px;" required>
                                <option value="">--select--</option>
                                <?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
                                while($rEU = $db->fetch_array($qEU)):
                                ?>
                                <option value="<?php echo $rEU['emp_id']?>" <?php if($txPreparedBy==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                <?php endwhile;?>
                            </select> 
                            <span class="help-inline warning" id="msgPreparedBy" style="font-weight:bold;" name="msgPreparedBy"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30">Approved By</td>
                        <td>
                            <select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:400px;" required>
                                <option value="">--select--</option>
                                <?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
                                while($rEU = $db->fetch_array($qEU)):
                                ?>
                                <option value="<?php echo $rEU['emp_id']?>" <?php if($txApprovedBy==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                <?php endwhile;?>
                            </select> 
                            <span class="help-inline warning" id="msgApprovedBy" style="font-weight:bold;" name="msgApprovedBy"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30">Explanation</td>
                        <td><textarea type="text" name="txRemarks" id="txRemarks"><?php echo $txRemarks; ?></textarea></td>
                    </tr>
                </table>
                <div align="center">
                    <input type="submit" name="btnAdd" id="btnAdd" value=" SAVE " class="btn btn-primary">
                    <?php if($jv_id){?>&nbsp;&nbsp;&nbsp;<a href="journal_voucher_manage_item.php?jv=<?php echo functions::encode($jv_id)?>" class="btn btn-info">Go To Particulars</a><?php } ?>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
    var res = false;
    $('#btnAdd').click(function(){
        $('#msgDate').html("");
        $('#msgPreparedBy').html("");
        $('#msgApprovedBy').html("");
        $('#msgProject').html("");


        if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
            $('#msgDate').html("Date Required!");
            res=false;
        }
        else if( $('#selProj').val()=="" ){
            $('#selProj').focus();
            $('#msgProject').html("Project Required!");
            res=false;
        }
        else if( $('#txPreparedBy').val()=="" ){
            $('#txPreparedBy').focus();
            $('#msgPreparedBy').html("Prepared By Required!");
            res=false;
        }
        else if( $('#txApprovedBy').val()=="" ){
            $('#txApprovedBy').focus();
            $('#msgApprovedBy').html("Approved By Required!");
            res=false;
        }
        else
            res=true;
        return res;
    });
});
</script>
<!-- end: JavaScript-->
</body>
</html>