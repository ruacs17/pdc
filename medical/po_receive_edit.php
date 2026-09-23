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

$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;

$q = $db->select('po','ref_id,po_date,delivery_date,supplierID,terms,payment_term,proj_detail,received,invoice,item_received_by,item_encoded_by,report_received_by,receive_no,remarks',array('ref_id'=>$ref_id),'GROUP BY ref_id');
$r = $db->fetch_array($q);
$ref_id = $r['ref_id'];
$vdate = $r['po_date'];
$xdate = explode('-',$vdate);
$mon = isset($xdate[1]) ? $xdate[1] : '';
$day = isset($xdate[2]) ? $xdate[2] : '';
$year = isset($xdate[0]) ? $xdate[0] : '';

$txInvoice = $r['invoice'];
$txPayee = $r['supplierID'];
$txTerms = $r['terms'];
$txProjDetail = $r['proj_detail'];
$txReceive = $r['received'];
$txRemarks = $r['remarks'];
$txPaymentTerm = $r['payment_term'];
$txReceivedBy = $r['item_received_by'];
$txEncodedBy = $r['item_encoded_by'];
$txReportBy = $r['report_received_by'];

$dlvryDate = $r['delivery_date'];
$xDDate = explode('-',$dlvryDate);
$monDelvry = isset($xDDate[1]) ? $xDDate[1] : '';
$dayDelvry = isset($xDDate[2]) ? $xDDate[2] : '';
$yearDelvry = isset($xDDate[0]) ? $xDDate[0] : '';
$loc_id='';
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
<?php
if( isset($_POST['btnSave']) && $ref_id ){
    $insertID=0;
    $arrInsert = array();
    $loc_id = (isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? trim($_POST['selLoc']) : "";
    $txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
    $txReceivedBy = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
    $txReportBy = ( isset($_POST['txReportBy']) && !empty($_POST['txReportBy']) ) ? $_POST['txReportBy'] : NULL;
    $txReceive = ( isset($_POST['txReceive']) && !empty($_POST['txReceive']) ) ? $_POST['txReceive'] : 0;
    $txbMonDelvry = ( isset($_POST['bdMonDelvry']) && !empty($_POST['bdMonDelvry']) ) ? $_POST['bdMonDelvry'] : '';
    $txbDayDelvry = ( isset($_POST['bdDayDelvry']) && !empty($_POST['bdDayDelvry']) ) ? $_POST['bdDayDelvry'] : '';
    $txbYearDelvry = ( isset($_POST['bdYearDelvry']) && !empty($_POST['bdYearDelvry']) ) ? $_POST['bdYearDelvry'] : '';
    $txRemarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? $_POST['txRemarks'] : '';
    $delivery_date = ($txbYearDelvry && $txbMonDelvry && $txbDayDelvry) ? $txbYearDelvry.'-'.$txbMonDelvry.'-'.$txbDayDelvry : date('Y-m-d');
    if($txReceive==0)
        $delivery_date=NULL;

    $arrRecvd = isset($_POST['rcvd']) ? $_POST['rcvd'] : array();

    $location = $db->getValue('inhouse_material_storage_location','location',array('imsl_id'=>$loc_id));

    if( $ref_id ){
        $newInvoice = $txInvoice;
        $has_invoice_update_attempt_failed=0;
        $invoice_updated=0;
        $oldInvoice = $db->getValue('po','DISTINCT invoice',array('ref_id'=>$ref_id));
        if($newInvoice != $oldInvoice){
            if($newInvoice==""){
                $db->update('po',array('invoice'=>$newInvoice),array('ref_id'=>$ref_id));
                $invoice_updated=1;
            }
            if( $db->getValue('po','count(*)',array('invoice'=>$newInvoice))==0 ){
                $db->update('po',array('invoice'=>$newInvoice),array('ref_id'=>$ref_id));
                $invoice_updated=1;
            }
            else{
                functions::say('Invoice already exist!');
                $has_invoice_update_attempt_failed=1;
            } 
        }
        //Update attached invoice on voucher particular
        $qUPO = $db->select('po','*',array('ref_id'=>$ref_id),'AND vp_id IS NOT NULL');
        while($rUPO = $db->fetch_array($qUPO)):
            $db->update('voucher_particular',array('vp_title'=>'P.O. PAYMENT - Invoice: '.$rUPO['invoice']),array('vp_id'=>$rUPO['vp_id']));
        endwhile;

        if( $db->getValue('po','count(receive_no)',array('ref_id'=>$ref_id))==0 ){//Assign Receive Number if it doesn't have one.
            $receive_no = ($txbYearDelvry && $txbMonDelvry && $txbDayDelvry) ? substr($txbYearDelvry, 2,2).$txbMonDelvry.$txbDayDelvry.(date('H') + date('i') + date('s')) : substr(date('Ymd'), 2,6).(date('H') + date('i') + date('s'));
            $db->update('po',array('receive_no'=>$receive_no),array('ref_id'=>$ref_id));
        }
        $db->update('po',array('item_received_by'=>$txReceivedBy,'report_received_by'=>$txReportBy,'invoice'=>$txInvoice,'received'=>$txReceive,'delivery_date'=>$delivery_date,'remarks'=>$txRemarks),array('ref_id'=>$ref_id));
        
        if( count($arrRecvd)>0 && $delivery_date ){
            foreach($arrRecvd as $itmID => $qty_received):
                $qItmDetail = $db->select('po_item','*',array('po_item_id'=>$itmID));
                $rItm = $db->fetch_array($qItmDetail);
                $item = $rItm['item'];
                $quantity = $rItm['qty_delivered'];
                $unit = $rItm['unit'];
                $brand = $rItm['brand'];
                if( $ims_id = $db->getValue('inhouse_material_storage','ims_id',array('poi_id'=>$itmID)) ){
                    $db->update('inhouse_material_storage',array('ims_date'=>$delivery_date,'item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location,'quantity'=>$qty_received,'poi_id'=>$itmID,'item_type'=>'medical'),array('ims_id'=>$ims_id));
                }
                else{
                    $db->insert('inhouse_material_storage',array('ims_date'=>$delivery_date,'item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location,'quantity'=>$qty_received,'poi_id'=>$itmID,'item_type'=>'medical'));
                }
            endforeach;
        }
        if($has_invoice_update_attempt_failed==0)
            functions::say("Changes Saved!");
        functions::sendTo('po_receive_view_only.php?ref_id='.functions::encode($ref_id));
    }
}
?>
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
                <table width="100%" border="0" align="center" class="table table-striped">
                    <tr>
                        <th width="23%" scope="col"><div align="left">Item</div></th>
                        <th width="10%" scope="col"><div align="left">Brand</div></th>
                        <th width="8%" scope="col"><div align="left">Unit</div></th>
                        <th width="9%" scope="col"><div align="left">Qty Request</div></th>
                        <th style="display:none;" width="9%" scope="col"><div align="left">Qty Received</div></th>
                    </tr>
                    <?php
                    $total_amount=0;$disc_amount=0;$amount=0;$dis_loc='';$count=0;
                    $qPOI = $db->query('SELECT * FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item');
                    while($rPOI = $db->fetch_array($qPOI)):
                        if($count==0)
                            $dis_loc=$db->getValue('inhouse_material_storage','location',array('poi_id'=>$rPOI['po_item_id']));
                        $count++;
                    ?>
                    <tr>
                        <td><?php echo $rPOI['item'];?></td>
                        <td><?php echo $rPOI['unit'];?></td>
                        <td><?php echo $rPOI['brand'];?></td>
                        <td><?php echo $rPOI['quantity'];?></td>
                        <td style="display:none;"><input type="number"step="any" name="rcvd[<?php echo $rPOI['po_item_id']?>]" min="0" max="<?php echo $rPOI['quantity']?>" value="<?php echo $rPOI['quantity']?>" style="width:50px;"></td>
                    </tr>
                    <?php endwhile;?>
                </table><br>
                <table width="70%" align="center" border="0" style="background-color:#E4E1E1;font-size:14px;">
                    <tr>
                        <td height="30" style="padding-left:15px"><div align="left">P.O Number</div></td>
                        <td width="75%"><div align="left"><strong><?php echo $ref_id?></strong></div></td>
                    </tr>
                    <tr>
                        <td height="30" style="padding-left:15px"><div align="left">P.O Date</div></td>
                        <td><div align="left"><strong><?php echo functions::datearr($vdate)?></strong></div></td>
                    </tr>
                    <tr>
                        <td height="30" style="padding-left:15px"><div align="left">Payee / Supplier</div></td>
                        <td><div align="left"><strong><?php echo $db->getValue('supplier','name',array('supplierID'=>$txPayee))?></strong></div></td>
                    </tr>
                    <?php if($txProjDetail){?>
                    <tr>
                        <td height="30" style="padding-left:15px"><div align="left">Additional description</div></td>
                        <td><div align="left"><strong><?php echo $txProjDetail;?></strong></div></td>
                    </tr>
                    <?php }?>
                    <?php if($txPaymentTerm){?>
                    <tr>
                        <td height="30" style="padding-left:15px"><div align="left">Terms of Payment</div></td>
                        <td><div align="left"><strong><?php echo $txPaymentTerm;?></strong></div></td>
                    </tr>
                    <?php }?>
                    <tr>
                        <td height="30" style="padding-left:15px"><div align="left">Invoice #</div></td>
                        <td><div align="left"><input type="text" name="txInvoice" id="txInvoice" style="width:136px;" value="<?php echo $txInvoice;?>" /></div></td>
                    </tr>
                    <tr>
                        <td height="55" style="padding-left:15px"><div align="left">Receive Status</div></td>
                        <td>
                            <div align="left">
                                <select name="txReceive" id="txReceive" style="width:150px;"required>
                                    <option value="0">--select--</option>
                                    <option value="1" <?php if($txReceive==1)echo 'selected="selected"';?>>Received</option>
                                    <option value="0" <?php if($txReceive==0)echo 'selected="selected"';?>>Not Received</option>
                                </select>
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
                                    <?php for($y=(date('Y') + 1);$y>=2012;$y--):?>
                                    <option value="<?php echo $y;?>" <?php if($y==$yearDelvry)echo 'selected="selected"';?>><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="55" style="padding-left:15px"><div align="left">Item Received By</div></td>
                        <td>
                            <div align="left">
                                <select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:400px;">
                                    <option value="">--select--</option>
                                    <?php $qPrep = $db->select('employee','*',array(),'ORDER BY lname');
                                          while($rPrep = $db->fetch_array($qPrep)):
                                    ?>
                                    <option value="<?php echo $rPrep['emp_id']?>" <?php if($txReceivedBy==$rPrep['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rPrep['lname'].', '.$rPrep['fname']);?></option>
                                    <?php endwhile;?>
                                </select> 
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="55" style="padding-left:15px"><div align="left">Report Received By</div></td>
                        <td>
                            <div align="left">
                                <select name="txReportBy" id="txReportBy" data-rel="chosen" style="width:400px;">
                                    <option value="">--select--</option>
                                    <?php $qRep = $db->select('employee','*',array(),'ORDER BY lname');
                                          while($rRep = $db->fetch_array($qRep)):
                                    ?>
                                    <option value="<?php echo $rRep['emp_id']?>" <?php if($txReportBy==$rRep['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rRep['lname'].', '.$rRep['fname']);?></option>
                                    <?php endwhile;?>
                                </select> 
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="55" style="padding-left:15px"><div align="left">Storage Location</div></td>
                        <td>
                            <select name="selLoc" id="selLoc" style="width:200px;" required>
                                <option value="">--select--</option>
                                <?php
                                $qLocation = $db->select('inhouse_material_storage_location','*',array());
                                while($rLoc = $db->fetch_array($qLocation)):
                                ?>
                                <option value="<?php echo $rLoc['imsl_id']?>" <?php if($dis_loc==$rLoc['location'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rLoc['location']));?></option>
                                <?php endwhile;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td height="55" style="padding-left:15px"><div align="left">Remarks</div></td>
                        <td><div align="left"><textarea name="txRemarks" id="txRemarks" style="width:386px;"><?php echo $txRemarks;?></textarea></div></td>
                    </tr>
                    <tr>
                        <td height="55"></td>
                        <td>
                            <div align="left">
                                <input type="submit" name="btnSave" id="btnSave" value="Receive" class="btn btn-primary">
                                <a href="po_receive_view_only.php?ref_id=<?php echo functions::encode($ref_id);?>" class="btn">Cancel</a>
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