<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$mrq_id = (isset($_REQUEST['mrq_id']) && !empty($_REQUEST['mrq_id']) ) ? functions::decode($_REQUEST['mrq_id']) : 0;

$q = $db->select('medicine_request','*',array('mrq_id'=>$mrq_id));
$r = $db->fetch_array($q);

$xdate = explode('-',$r['mrq_date']);
$mon = isset($xdate[1]) ? $xdate[1] : "";
$day = isset($xdate[2]) ? $xdate[2] : "";
$year = isset($xdate[0]) ? $xdate[0] : "";
$mrq_no = $r['mrq_no'];
$requested_by = $r['emp_id'];
$released_by = $r['released_by'];
$claimed = $r['claimed'];
$proj_id = $r['proj_id'];
$txClaim='';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>MEDICAL REQUEST</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
    if( isset($_POST['btnSave']) ){
        $txProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
        $txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
        $txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
        $txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
        $mrq_date = ( $txbMon && $txbDay && $txbYear ) ? $txbYear.'-'.$txbMon.'-'.$txbDay : NULL;

        $txClaim = ( isset($_POST['txClaim']) && !empty($_POST['txClaim']) ) ? $_POST['txClaim'] : '';
        $txRequestedBy = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : '';
        $txReleasedBy = ( isset($_POST['txReleasedBy']) && !empty($_POST['txReleasedBy']) ) ? $_POST['txReleasedBy'] : '';

        if( $txProj && $mrq_date && $txRequestedBy && $txReleasedBy ){
            
            if($mrq_id){
                $db->update('medicine_request',array('mrq_date'=>$mrq_date,'emp_id'=>$txRequestedBy,'released_by'=>$txReleasedBy,'claimed'=>$txClaim,'proj_id'=>$txProj),array('mrq_id'=>$mrq_id));
                functions::say('Changes saved!');
                functions::sendTo($_SERVER['PHP_SELF'].'?mrq_id='.functions::encode($mrq_id));
            }
            else{
                $mrq_id = $db->insert('medicine_request',array('mrq_date'=>$mrq_date,'emp_id'=>$txRequestedBy,'released_by'=>$txReleasedBy,'claimed'=>$txClaim,'proj_id'=>$txProj));
                if($mrq_id){
                    $mrq_no = $db->getValue('medicine_request','concat(substring(mrq_date,1,4),substring(mrq_date,6,2),mrq_id) as dte',array('mrq_id'=>$mrq_id));
                    $db->update('medicine_request',array('mrq_no'=>$mrq_no),array('mrq_id'=>$mrq_id));
                    functions::sendTo('request_item_manage.php?mrq_id='.functions::encode($mrq_id));
                }
            }
        }
        else{
            functions::say('Please fill up the form properly!');
        }
    }
    ?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid sortable">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Medicine Request Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <input type="hidden" name="poid" id="poid" value="<?php echo functions::encode($po_id);?>">
                <table width="65%" align="center" border="0" style="background-color:#E4E1E1">
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Requisition Date</label>
                                <div class="controls">
                                    <select name="bdMon" id="bdMon" style="width:80px;">
                                        <option value="">Month</option>
                                        <option value="01" <?php if($mon=='01')echo 'selected="selected"';?>>Jan</option>
                                        <option value="02" <?php if($mon=='02')echo 'selected="selected"';?>>Feb</option>
                                        <option value="03" <?php if($mon=='03')echo 'selected="selected"';?>>Mar</option>
                                        <option value="04" <?php if($mon=='04')echo 'selected="selected"';?>>Apr</option>
                                        <option value="05" <?php if($mon=='05')echo 'selected="selected"';?>>May</option>
                                        <option value="06" <?php if($mon=='06')echo 'selected="selected"';?>>Jun</option>
                                        <option value="07" <?php if($mon=='07')echo 'selected="selected"';?>>Jul</option>
                                        <option value="08" <?php if($mon=='08')echo 'selected="selected"';?>>Aug</option>
                                        <option value="09" <?php if($mon=='09')echo 'selected="selected"';?>>Sep</option>
                                        <option value="10" <?php if($mon=='10')echo 'selected="selected"';?>>Oct</option>
                                        <option value="11" <?php if($mon=='11')echo 'selected="selected"';?>>Nov</option>
                                        <option value="12" <?php if($mon=='12')echo 'selected="selected"';?>>Dec</option>
                                    </select>
                                    <select name="bdDay" id="bdDay" style="width:60px;">
                                        <option value="">Day</option>
                                        <?php for($i=1;$i<=31;$i++):?>
                                        <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$day)echo 'selected="selected"';?>><?php echo $i;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <select name="bdYear" id="bdYear" style="width:70px;">
                                        <option value="">Year</option>
                                        <?php for($y=(date('Y') + 1);$y>=2012;$y--):?>
                                        <option value="<?php echo $y;?>" <?php if($y==$year)echo 'selected="selected"';?>><?php echo $y;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><br>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Requested By</label>
                                <div class="controls">
                                    <select name="txRequestedBy" id="txRequestedBy" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
                                        while($rEU = $db->fetch_array($qEU)):
                                        ?>
                                        <option value="<?php echo $rEU['emp_id']?>" <?php if($requested_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><br>
                            <div class="control-group">
                                <label class="control-label" for="textarea2">Project</label>
                                <div class="controls">
                                    <select name="selProj" id="selProj" data-rel="chosen" style="width:600px;">
                                        <option value="">--select--</option>
                                        <?php $qProj = $db->select('project p, medicine_distribution md','*',array(),'WHERE p.proj_id=md.proj_id ORDER BY proj_name');
                                              while($rProj = $db->fetch_array($qProj)):
                                        ?>
                                        <option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id']){echo 'selected="selected"';} ?>><?php echo ($rProj['proj_name']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><br>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Released By</label>
                                <div class="controls">
                                    <select name="txReleasedBy" id="txReleasedBy" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
                                        while($rEU = $db->fetch_array($qEU)):
                                        ?>
                                        <option value="<?php echo $rEU['emp_id']?>" <?php if($released_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><br>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Claim Status</label>
                                <div class="controls">
                                    <select name="txClaim" id="txClaim" style="width:150px;">
                                        <option value="2" <?php if($txClaim==2)echo 'selected="selected"';?>>Not Claim</option>
                                        <option value="1" <?php if($txClaim==1)echo 'selected="selected"';?>>Claimed</option>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="controls">
                                <input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">
                                <button class="btn">Cancel</button>
                            </div>
                        </td>
                    </tr> 
                    <tr><td>&nbsp;</td></tr>           
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
<script>
$(document).ready(function(){
    var res = false;

    <?php if($txReceive==1){?>
    $('#bdMonDelvry').show();
    $('#bdDayDelvry').show();
    $('#bdYearDelvry').show();
    <?php }else{?>
    $('#bdMonDelvry').hide();
    $('#bdDayDelvry').hide();
    $('#bdYearDelvry').hide();
    <?php }?>
    $('#txReceive').change(function(){
        if( $('#txReceive').val()=="1" ){
            $('#bdMonDelvry').show();
            $('#bdDayDelvry').show();
            $('#bdYearDelvry').show();
        }
        else{
            $('#bdMonDelvry').hide();
            $('#bdDayDelvry').hide();
            $('#bdYearDelvry').hide();
        }
    });

    $('#btnSave').click(function(){
        $('#msgBdate').html("");
        $('#msgPayee').html("");
        $('#msgProject').html("");
        $('#msgPrepare').html("");
        $('#msgApprove').html("");
        
        if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
            $('#msgBdate').html("Date Required!");
            res=false;
        }
        else if( $('#txPayee').val()=="" ){
            $('#txPayee').focus();
            $('#msgPayee').html("Supplier Required!");
            res=false;
        }
        else if( $('#selProj').val()=="" ){
            $('#msgProject').html("Project Required!");
            $('#selProj').focus();
            res=false;
        }
        else if( $('#txPreparedBy').val()=="" ){
            $('#msgPrepare').html("Purchase Required!");
            $('#txPreparedBy').focus();
            res=false;
        }
        else if( $('#txApprove').val()=="" ){
            $('#msgApprove').html("Approval Required!");
            $('#txApprove').focus();
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