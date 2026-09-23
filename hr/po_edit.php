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

$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;

if( isset($_POST['btnSave']) ){
    $insertID=0;
    $arrInsert = array();
    
    $poid = ( isset($_POST['poid']) && !empty($_POST['poid']) ) ? functions::decode($_POST['poid']) : '';
    $txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
    $txProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    $txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
    $txPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
    $txApprove = ( isset($_POST['txApprove']) && !empty($_POST['txApprove']) ) ? $_POST['txApprove'] : '';
    $txTerms = '';#$txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : '';
    $txProjDetail = ( isset($_POST['txProjDetail']) && !empty($_POST['txProjDetail']) ) ? $_POST['txProjDetail'] : '';
    $txReceive = ( isset($_POST['txReceive']) && !empty($_POST['txReceive']) ) ? $_POST['txReceive'] : 0;
    $txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;
    $txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;
    $txbMonDelvry = ( isset($_POST['bdMonDelvry']) && !empty($_POST['bdMonDelvry']) ) ? $_POST['bdMonDelvry'] : '';
    $txbDayDelvry = ( isset($_POST['bdDayDelvry']) && !empty($_POST['bdDayDelvry']) ) ? $_POST['bdDayDelvry'] : '';
    $txbYearDelvry = ( isset($_POST['bdYearDelvry']) && !empty($_POST['bdYearDelvry']) ) ? $_POST['bdYearDelvry'] : '';
    $delivery_date = ($txbYearDelvry && $txbMonDelvry && $txbDayDelvry) ? $txbYearDelvry.'-'.$txbMonDelvry.'-'.$txbDayDelvry : date('Y-m-d');
    $remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
    $paymentTerm = ( isset($_POST['txPaymentTerm']) && !empty($_POST['txPaymentTerm']) ) ? trim($_POST['txPaymentTerm']) : '';
    $allowed = 1;
    if( $txProj && $txPayee && $txbMon && $txbDay && $txbYear && $poid && $txPreparedBy){
        if($txInvoice){
            if( $db->getValue('po','invoice',array('po_id'=>$poid)) != $txInvoice ){
                if( $db->getValue('po','count(vp_id)',array('invoice'=>$txInvoice)) )
                    $allowed=0;
            }
        }

        if($allowed){
            $vp_id = $db->getValue('po','vp_id',array('po_id'=>$poid));

            if($vp_id){
                $oldInvoice = $db->getValue('po','invoice',array('po_id'=>$poid));
                $newInvoice = $txInvoice;
                if( $oldInvoice != $newInvoice){
                    $vpInvoice = ($newInvoice) ? $newInvoice : ' -- ';
                    $db->update('voucher_particular',array('vp_title'=>'P.O. PAYMENT - Invoice: '.$vpInvoice),array('vp_id'=>$vp_id));
                }
            }
            $db->update('po',array('proj_id'=>$txProj,'supplierID'=>$txPayee,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'terms'=>$txTerms,'proj_detail'=>$txProjDetail,'received'=>$txReceive,'remarks'=>$remarks,'delivery_date'=>$delivery_date,'payment_term'=>$paymentTerm),array('po_id'=>$poid));
        }
        else{
            functions::say("Invoice already used in voucher!");
        }
        functions::sendTo($_SERVER['PHP_SELF'].'?po_id='.functions::encode($poid));
    }
}
$txPaymentTerm='';$txRemarks='';$payee='';$proj_id='';$monDelvry='';$dayDelvry='';$yearDelvry='';$mon='';$day='';$year='';$txInvoice='';$txPayee='';$txApprove='';$txTerms='';$txProjDetail='';$txReceive=0;$txServed=0;$txItemCat=0;
if($po_id){

    $q = $db->select('po','*',array('po_id'=>$po_id));
    $r = $db->fetch_array($q);

    $vdate = $r['po_date'];
    $xdate = explode('-',$vdate);
    if(count($xdate)==3){
        $mon = $xdate[1];
        $day = $xdate[2];
        $year = $xdate[0];
    }
    $proj_id = $r['proj_id'];
    $txInvoice = $r['invoice'];
    $txPreparedBy = $r['purchaser'];
    $txPayee = $r['supplierID'];
    $txApprove = $r['approved_by'];
    $txTerms=$r['terms'];
    $txProjDetail=$r['proj_detail'];
    $txReceive = $r['received'];
    $txItemCat = $r['category_id'];
    $txRemarks = $r['remarks'];
    $txPaymentTerm = $r['payment_term'];

    $dlvryDate = $r['delivery_date'];
    $xDDate = explode('-',$dlvryDate);
    if(count($xDDate)==3){
        $monDelvry = $xDDate[1];
        $dayDelvry = $xDDate[2];
        $yearDelvry = $xDDate[0];
    }  
}
$qItem = $db->select('po','DISTINCT payment_term',array());
$namesPaymentTerm='';
while($rItem=$db->fetch_array($qItem)):
    $string = preg_replace("/'/",'"',$rItem['payment_term']);
    $namesPaymentTerm .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPaymentTerm .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Purchase Order Update</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid sortable">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <input type="hidden" name="poid" id="poid" value="<?php echo functions::encode($po_id);?>">
                <table width="60%" align="center" border="0" style="background-color:#E4E1E1">
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">P.O Date</label>
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
                                        <?php for($y=(date('Y') + 7);$y>=2012;$y--):?>
                                        <option value="<?php echo $y;?>" <?php if($y==$year)echo 'selected="selected"';?>><?php echo $y;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Payee / Supplier</label>
                                <div class="controls">
                                    <select name="txPayee" id="txPayee" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
                                            while($rSup = $db->fetch_array($qSup)):
                                        ?>
                                        <option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" id="msgPayee" style="font-weight:bold;" name="msgPayee"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Project</label>
                                <div class="controls">
                                    <select name="selProj" id="selProj" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                                            while($rProj = $db->fetch_array($qProj)):
                                        ?>
                                        <option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                  <span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Category</label>
                                <div class="controls">
                                    <select name="txItemCat" id="txItemCat" data-rel="chosen" style="width:400px;">
                                        <?php 
                                        $qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
                                        while($rCat = $db->fetch_array($qCat)):
                                        ?>
                                        <option value="<?php echo $rCat['item_id']?>" <?php if($txItemCat==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Additional description</label>
                                <div class="controls">
                                    <input type="text" name="txProjDetail" id="txProjDetail" style="width:300px;" value="<?php echo $txProjDetail;?>" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Invoice #</label>
                                <div class="controls">
                                    <input type="text" name="txInvoice" id="txInvoice" style="width:300px;" value="<?php echo $txInvoice;?>" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Purchaser</label>
                                <div class="controls">
                                    <select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qPrep = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
                                            while($rPrep = $db->fetch_array($qPrep)):
                                        ?>
                                        <option value="<?php echo $rPrep['user_id']?>" <?php if($txPreparedBy==$rPrep['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rPrep['lname'].', '.$rPrep['fname']);?></option>
                                        <?php endwhile;?>
                                    </select> 
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Approval</label>
                                <div class="controls">
                                    <select name="txApprove" id="txApprove" data-rel="chosen" style="width:400px;">
                                        <option value="">--select--</option>
                                        <?php $qApprove = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
                                            while($rApprove = $db->fetch_array($qApprove)):
                                        ?>
                                        <option value="<?php echo $rApprove['user_id']?>" <?php if($txApprove==$rApprove['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rApprove['lname'].', '.$rApprove['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" id="msgApprove" style="font-weight:bold;" name="msgApprove"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Terms of Payment</label>
                                <div class="controls">
                                    <input type="text" name="txPaymentTerm" id="txPaymentTerm" style="width:300px;" value="<?php echo $txPaymentTerm?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPaymentTerm;?>]' />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Delivery Date</label>
                                <div class="controls">
                                    <select name="bdMonDelvry" id="bdMonDelvry" style="width:80px;">
                                        <option value="">Month</option>
                                        <option value="01" <?php if($monDelvry=='01')echo 'selected="selected"';?>>Jan</option>
                                        <option value="02" <?php if($monDelvry=='02')echo 'selected="selected"';?>>Feb</option>
                                        <option value="03" <?php if($monDelvry=='03')echo 'selected="selected"';?>>Mar</option>
                                        <option value="04" <?php if($monDelvry=='04')echo 'selected="selected"';?>>Apr</option>
                                        <option value="05" <?php if($monDelvry=='05')echo 'selected="selected"';?>>May</option>
                                        <option value="06" <?php if($monDelvry=='06')echo 'selected="selected"';?>>Jun</option>
                                        <option value="07" <?php if($monDelvry=='07')echo 'selected="selected"';?>>Jul</option>
                                        <option value="08" <?php if($monDelvry=='08')echo 'selected="selected"';?>>Aug</option>
                                        <option value="09" <?php if($monDelvry=='09')echo 'selected="selected"';?>>Sep</option>
                                        <option value="10" <?php if($monDelvry=='10')echo 'selected="selected"';?>>Oct</option>
                                        <option value="11" <?php if($monDelvry=='11')echo 'selected="selected"';?>>Nov</option>
                                        <option value="12" <?php if($monDelvry=='12')echo 'selected="selected"';?>>Dec</option>
                                    </select>
                                    <select name="bdDayDelvry" id="bdDayDelvry" style="width:60px;">
                                        <option value="">Day</option>
                                        <?php for($i=1;$i<=31;$i++):?>
                                        <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$dayDelvry)echo 'selected="selected"';?>><?php echo $i;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <select name="bdYearDelvry" id="bdYearDelvry" style="width:70px;">
                                        <option value="">Year</option>
                                        <?php for($y=(date('Y') + 7);$y>=2012;$y--):?>
                                        <option value="<?php echo $y;?>" <?php if($y==$yearDelvry)echo 'selected="selected"';?>><?php echo $y;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <span class="help-inline warning" style="font-weight:bold;" id="msgDayDelvry" name="msgDayDelvry"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Terms and Conditions</label>
                                <div class="controls">
                                    <textarea class="cleditor" id="txTerms" name="txTerms" rows="2"><?php echo $txTerms;?></textarea>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Receive Status</label>
                                <div class="controls">
                                    <select name="txReceive" id="txReceive" style="width:150px;">
                                        <option value="0">--select--</option>
                                        <option value="1" <?php if($txReceive==1)echo 'selected="selected"';?>>Received</option>
                                        <option value="0" <?php if($txReceive==0)echo 'selected="selected"';?>>Not Received</option>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <td>
                            <div class="control-group hidden-phone">
                                <label class="control-label" for="textarea2">Remarks</label>
                                <div class="controls">
                                    <textarea id="txRemarks" name="txRemarks" rows="2"><?php echo $txRemarks?></textarea>
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