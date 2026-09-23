<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="10" ){
    header("Location: ../");
    die();
}
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
  
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;

$proj_id='';$mon='';$day='';$year='';$discount='';$brand=''; $address='';$prepared_by='';$approved_by='';$checked_by='';$delivered_by='';$received_by='';$terms='';$is_paid=0; $payee='';$txItemCat=0;
if( $im_id ){
    $q = $db->select('inhouse_material','*',array('im_id'=>$im_id));
    $r = $db->fetch_array($q);
    $address = $r['address'];
    $prepared_by = $r['prepared_by'];
    $approved_by = $r['approved_by'];
    $checked_by = $r['checked_by'];
    $delivered_by = $r['delivered_by'];
    $received_by = $r['received_by'];
    $proj_id = $r['proj_id'];
    $txDate = $r['im_date'];
    $terms = $r['terms'];
    $is_paid = $r['is_paid'];
    $payee = $r['payee'];
    $txItemCat = $r['category_id'];

    $xdate = explode('-',$txDate);
    if(count($xdate)==3){
        $mon = $xdate[1];
        $day = $xdate[2];
        $year = $xdate[0];
    }
}

if( isset($_POST['btnSave']) ){
 
    $arr = array();
    $selProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : '';
    $txMon = ( isset($_POST['txMon']) && !empty($_POST['txMon']) ) ? $_POST['txMon'] : '';
    $txDay = ( isset($_POST['txDay']) && !empty($_POST['txDay']) ) ? $_POST['txDay'] : '';
    $txYear = ( isset($_POST['txYear']) && !empty($_POST['txYear']) ) ? $_POST['txYear'] : '';
    $txCheckedBy = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? strtoupper($_POST['txCheckedBy']) : '';
    $txDeliveredBy = ( isset($_POST['txDeliveredBy']) && !empty($_POST['txDeliveredBy']) ) ? strtoupper($_POST['txDeliveredBy']) : '';
    $txReceivedBy = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? strtoupper($_POST['txReceivedBy']) : '';
    $txAddress = ( isset($_POST['txAddress']) && !empty($_POST['txAddress']) ) ? $_POST['txAddress'] : '';
    $txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : ''; 
    $txPayment = ( isset($_POST['txPayment']) && !empty($_POST['txPayment']) ) ? $_POST['txPayment'] : '';
    $txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;
    $txDate = $txYear.'-'.$txMon.'-'.$txDay;
    $selPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
    $selApprovedBy = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : '';

    $rdoCharge = ( isset($_POST['rdoCharge']) && !empty($_POST['rdoCharge']) ) ? trim($_POST['rdoCharge']) : '';
    $txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? trim($_POST['txPayee']) : '';

    if($rdoCharge==1)
        $txPayee='';
    else
        $selProj = '';

    if( $txMon && $txDay && $txYear && $txPayment && $txItemCat && ($selProj || $txPayee) ){
        $arr = array('im_date'=>$txDate,'category_id'=>$txItemCat,'address'=>$txAddress,'checked_by'=>$txCheckedBy,'delivered_by'=>$txDeliveredBy,'received_by'=>$txReceivedBy,'terms'=>$txTerms,'is_paid'=>$txPayment);

        if($selPreparedBy)
            $arr = array_merge($arr,array('prepared_by'=>$selPreparedBy));
        else
            $db->query('UPDATE inhouse_material SET prepared_by=NULL WHERE im_id="'.$db->clean($im_id).'"');
        

        if($selApprovedBy)
            $arr = array_merge($arr,array('approved_by'=>$selApprovedBy));
        else
            $db->query('UPDATE inhouse_material SET approved_by=NULL WHERE im_id="'.$db->clean($im_id).'"');

        if($txPayee){
            $arr = array_merge($arr,array('payee'=>$txPayee));
            $db->query('UPDATE inhouse_material SET proj_id=NULL WHERE im_id="'.$db->clean($im_id).'"');
        }
        else
            $arr = array_merge($arr,array('proj_id'=>$selProj,'payee'=>''));
    
        $db->update('inhouse_material',$arr,array('im_id'=>$im_id));
        functions::say('Changes saved!');
        functions::sendTo($_SERVER['PHP_SELF'].'?im_id='.functions::encode($im_id));
    }
}

$qPayee = $db->query('SELECT DISTINCT payee FROM inhouse_material');
$namesPayee='';
while($rPayee=$db->fetch_array($qPayee)):
    $string = preg_replace("/'/",'"',$rPayee['payee']);
    $namesPayee .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPayee .= '"--"';


$qCheck = $db->query('SELECT DISTINCT checked_by FROM inhouse_material');
$namesCheck='';
while($rCheck=$db->fetch_array($qCheck)):
    $string = preg_replace("/'/",'"',$rCheck['checked_by']);
    $namesCheck .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCheck .= '"--"';

$qDeliver = $db->query('SELECT DISTINCT delivered_by FROM inhouse_material');
$namesDeliver='';
while($rDeliver=$db->fetch_array($qDeliver)):
    $string = preg_replace("/'/",'"',$rDeliver['delivered_by']);
    $namesDeliver .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesDeliver .= '"--"';

$qReceive = $db->query('SELECT DISTINCT received_by FROM inhouse_material');
$namesReceive='';
while($rReceive=$db->fetch_array($qReceive)):
    $string = preg_replace("/'/",'"',$rReceive['received_by']);
    $namesReceive .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesReceive .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Material Charge Add</title>
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
    <style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Charge Add</h2>
        </div>
        <div class="box-content">
            <div align="center">
                <form method="post">
                    <table width="70%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <th width="35%" align="right" scope="row">&nbsp;</th>
                            <td width="2%">&nbsp;</td>
                            <td width="50%">&nbsp;</td>
                            <td width="13%">&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Charge To</th>
                            <td>&nbsp;</td>
                            <td style="padding: 25px 0px 4px 0px;">
                                <table border="0" width="99%">
                                    <tr>
                                        <td style="padding: 0px 5px 4px 4px"><input type="radio" name="rdoCharge" id="rdoCharge1" value="1" onclick="document.getElementById('txPayee').disabled=true;"></td>
                                        <td style="padding: 10px 5px 4px 4px">
                                            <select name="selProj" id="selProj" data-rel="chosen" style="width:700px;" onchange="document.getElementById('rdoCharge1').checked=true;document.getElementById('txPayee').disabled=true;">
                                                <option value="">--select--</option>
                                                <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                                                    while($rProj = $db->fetch_array($qProj)):
                                                ?>
                                                <option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ($rProj['proj_name']);?></option>
                                                <?php endwhile;?>
                                            </select>
                                        </td>
                                    <tr>
                                        <td style="padding: 0px 5px 10px 4px"><input type="radio"name="rdoCharge" id="rdoCharge2" value="2" onclick="document.getElementById('txPayee').disabled=false;document.getElementById('selProj').value='';"></td>
                                        <td style="padding: 10px 5px 4px 4px"><input type="text" name="txPayee" id="txPayee" style="width:680px;" value="<?php echo $payee?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPayee;?>]'/></td>
                                    </tr>
                                </table>
                                <span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Category</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left">
                                <select name="txItemCat" id="txItemCat" data-rel="chosen" style="width:400px;">
                                    <?php 
                                    $qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
                                    while($rCat = $db->fetch_array($qCat)):
                                    ?>
                                    <option value="<?php echo $rCat['item_id']?>" <?php if($txItemCat==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
                                    <?php endwhile;?>
                                </select>
                                <span class="help-inline warning" id="msgItemCat" style="font-weight:bold;" name="msgItemCat"></span>
                            </td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Address</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left"><input type="text" name="txAddress" id="txAddress" style="width:300px;" value="<?php echo $address?>" /></td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Purchase Date</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="txMon" id="txMon" style="width:80px;">
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
                                <select name="txDay" id="txDay" style="width:60px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($i==$day)echo 'selected="selected"';?>><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="txYear" id="txYear" style="width:70px;">
                                    <option value="">Year</option>
                                    <?php for($y=(date('Y') + 2);$y>=2010;$y--):?>
                                    <option value="<?php echo $y;?>" <?php if($y==$year)echo 'selected="selected"';?>><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                                <span class="help-inline warning" style="font-weight:bold;" id="msgTxdate" name="msgTxdate"></span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Prepared By</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left">
                                <div align="left">
                                    <select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:300px; text-align:left;">
                                        <option value="">--select--</option>
                                        <?php $qEU = $db->select('users','*',array(),'ORDER BY lname');
                                            while($rEU = $db->fetch_array($qEU)):
                                        ?>
                                        <option value="<?php echo $rEU['user_id']?>" <?php if($prepared_by==$rEU['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Approved By</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left">
                                <div align="left">
                                    <select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:300px; text-align:left;">
                                        <option value="">--select--</option>
                                        <?php $qEU = $db->select('equip_user','*',array(),'ORDER BY lname');
                                            while($rEU = $db->fetch_array($qEU)):
                                        ?>
                                        <option value="<?php echo $rEU['eu_id']?>" <?php if($approved_by==$rEU['eu_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Checked By</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left"><input type="text" name="txCheckedBy" id="txCheckedBy" style="width:300px;" value="<?php echo $checked_by?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCheck;?>]'/></td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Delivered By</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left"><input type="text" name="txDeliveredBy" id="txDeliveredBy" style="width:300px;" value="<?php echo $delivered_by?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesDeliver;?>]'/></td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Received By</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left"><input type="text" name="txReceivedBy" id="txReceivedBy" style="width:300px;" value="<?php echo $received_by?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReceive;?>]'/></td>
                        </tr>
                        <tr>
                            <th>Terms and Conditions</th>
                            <td>&nbsp;</td>
                            <td><textarea class="cleditor" id="txTerms" name="txTerms" rows="2"><?php echo $terms;?></textarea></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Payment Status</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace" colspan="2" align="left">
                                <select name="txPayment" id="txPayment">
                                    <option value="">--select--</option>
                                    <option value="2" <?php if($is_paid=='2')echo 'selected="selected"';?>>Paid</option>
                                    <option value="1" <?php if($is_paid=='1')echo 'selected="selected"';?>>Unpaid</option>
                                </select>
                                <span class="help-inline warning" id="msgTxPayment" style="font-weight:bold;" name="msgTxPayment"></span>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="tdSpace" colspan="2" align="left"><input type="submit" name="btnSave" id="btnSave" value="Save Purchase" class="btn btn-primary"></td>
                        </tr>
                    </table>
                </form>
            </div>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
$(document).ready(function(){
    var res = false;

    <?php echo ($payee) ? "$('#rdoCharge2').attr('checked','checked');" : "$('#txPayee').prop('disabled',true);";?>
    <?php echo ($proj_id) ? "$('#rdoCharge1').attr('checked','checked');" : "";?>
    $('#btnSave').click(function(){
        $('#msgProject').html("");
        $('#msgTxdate').html("");
        $('#msgTxPayment').html("");

        if( $('#rdoCharge1').is(':checked')==false && $('#rdoCharge2').is(':checked')==false ){
            $('#msgProject').html("Project/Payee Required!");
            res=false;
        }
        else if( $('#rdoCharge1').is(':checked') && $('#selProj').val()=="" ){
            $('#msgProject').html("Project Required!");
            $('#selProj').focus();
            res=false;
        }
        else if( $('#rdoCharge2').is(':checked') && $('#txPayee').val()=="" ){
            $('#msgProject').html("Payee Required!");
            $('#txPayee').focus();
            res=false;
        }
        else if( $('#txYear').val()=="" || $('#txMon').val()=="" || $('#txdDay').val()=="" ){
            $('#msgTxdate').html("Date Required!");
            res=false;
        }
        else if( $('#txPayment').val()=="" ){
            $('#msgTxPayment').html("Payment Status Required!");
            res=false;
        }
        else{
            res=true;
        }
        return res;
    });
});
</script>
<!-- end: JavaScript-->
</body>
</html>