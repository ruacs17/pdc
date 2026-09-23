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
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : 0;
$poidEdt=0;
if( isset($_POST['btnAdd']) ){
    $txQty = 1;
    $txQtyDel = 1;
    $txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? trim($_POST['txItem']) : '';
    $txUnit = 'Unit';
    $txBrand = '';
    $txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
    $txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
    if( $txQty && $txItem && $txCost ){
        $db->insert('po_item',array('po_id'=>$po_id,'item'=>$txItem,'quantity'=>$txQty,'qty_delivered'=>$txQtyDel,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost,'discount'=>$txDiscount));
    }
    functions::sendTo('po_service_view.php?po_id='.functions::encode($po_id));
}

if( isset($_POST['btnSave']) ){
    $txpoidEdt = ( isset($_POST['txpoidEdt']) && !empty($_POST['txpoidEdt']) ) ? functions::decode($_POST['txpoidEdt']) : '0';
    $txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : '';
    $txQtyDel = ( isset($_POST['txQtyDel']) && !empty($_POST['txQtyDel']) ) ? $_POST['txQtyDel'] : '';
    $txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
    $txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
    $txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? trim($_POST['txBrand']) : '';
    $txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
    $txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : '0';
    if( $txQty && $txItem && $txCost){
        $db->update('po_item',array('item'=>$txItem,'quantity'=>$txQty,'qty_delivered'=>$txQtyDel,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost,'discount'=>$txDiscount),array('po_item_id'=>$txpoidEdt));
    }
    functions::sendTo('po_view.php?po_id='.functions::encode($po_id));
}

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
    $poidDel = functions::decode($_REQUEST['poidDel']);
    $db->delete('po_item',array('po_item_id'=>$poidDel));
    functions::sendTo('po_view.php?po_id='.functions::encode($po_id));
}

$editTrue=0;
$poidEdt='';
$item='';$quantity='';$brand='';$cost='';$unit='';$qty_delivered='';$discount='';
if( isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ){
    $poidEdt = functions::decode($_REQUEST['poidEdt']);
    $editTrue = $db->getValue('po_item','count(*)',array('po_item_id'=>$poidEdt));
    $qvedt = $db->select('po_item','*',array('po_item_id'=>$poidEdt));
    $rvedt = $db->fetch_array($qvedt);
    $item = $rvedt['item'];
    $quantity = $rvedt['quantity'];
    $qty_delivered = $rvedt['qty_delivered'];
    $unit = $rvedt['unit'];
    $brand = $rvedt['brand'];
    $cost = $rvedt['cost'];
    $discount = $rvedt['discount'];
}

if( $searchedItem ){
    $itemID = $db->getValue('material_reference','mf_id',array('mf_id'=>$searchedItem)); 
    $item = $db->getValue('material_reference','item',array('mf_id'=>$searchedItem));
    $brand = $db->getValue('material_reference','brand',array('mf_id'=>$searchedItem));
    $unit = $db->getValue('material_reference','unit',array('mf_id'=>$searchedItem));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Purchase Order Item</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>P.O Service Item Details</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="po_service_view_document.php?po_id=<?php echo functions::encode($po_id)?>">Document</a></li>
                <li class="active"><a href="po_service_view.php?po_id=<?php echo functions::encode($po_id)?>" style="opacity:.9">Service Detail</a></li>
            </ul>
            <form method="post">
                <input type="hidden" name="txpoidEdt" id="txpoidEdt" value="<?php echo functions::encode($poidEdt);?>">
                <table width="100%" border="0" align="center" class="table table-striped">
                    <tr>
                        <th width="23%" scope="col"><div align="left">Service Description</div></th>
                        <th width="10%" scope="col"><div align="center">Amount</div></th>
                        <th width="8%" scope="col"><div align="center">Discount(%)</div></th>
                        <th width="10%" scope="col">&nbsp;</th>
                    </tr>
                    <tr bgcolor="#faf7f7">
                        <td>
                            <div align="left">
                                <textarea name="txItem" id="txItem"><?php echo $item?></textarea>
                            </div>
                            <span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
                        </td>
                        <td>
                            <div align="center"><input type="text" style="width:120px;" name="txCost" id="txCost" value="<?php echo ($cost) ? functions::formatMoney($cost) : '';?>" onkeyup="FormatCurrency(this);"></div>
                            <span class="help-inline warning" id="msgtxCost" style="font-weight:bold;" name="msgtxCost"></span>
                        </td>
                        <td><div align="center"><input type="text" style="width:80px;" name="txDiscount" id="txDiscount" value="<?php echo $discount?>" onkeypress="return checkinput(this, event);"></div></td>
                        <td>
                            <div align="center">
                            <?php 
                            if($editTrue){
                                 echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary"> ';
                                 echo '<a href="?po_id='.functions::encode($po_id).'" class="btn btn-mini">Cancel</a>';
                            }else
                                 echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary">';
                            ?>
                            </div>
                        </td>
                    </tr>
                </table><br><br><br>
                <table width="100%" border="0" align="center" class="table table-striped">
                    <tr>
                        <th width="23%" scope="col"><div align="left">Service Description</div></th>
                        <th width="8%" scope="col"><div align="left">Cost</div></th>
                        <th width="9%" scope="col"><div align="left">Discount</div></th>
                        <th width="8%" scope="col"><div align="left">Amount</div></th>
                        <th width="8%" scope="col">&nbsp;</th>
                    </tr>
                    <?php
                    $total_amount=0;$disc_amount=0;$amount=0;
                    $qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
                    while($rPOI = $db->fetch_array($qPOI)):
                        $amount = $rPOI['cost'] * $rPOI['qty_delivered'];
                        $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                        $amount = $amount - $disc_amount;
                        $total_amount += $amount;
                    ?>
                    <tr>
                        <td><?php echo $rPOI['item'];?></td>
                        <td><?php echo functions::formatMoney($rPOI['cost']);?></td>
                        <td><?php echo $rPOI['discount'];?>%</td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                        <td>
                            <div align="center">
                                <a id="edit<?php echo $rPOI['po_item_id'];?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="?po_id=<?php echo functions::encode($po_id);?>&poidEdt=<?php echo functions::encode($rPOI['po_item_id']);?>"><i class="halflings-icon white pencil"></i></a>
                                <a id="del<?php echo $rPOI['po_item_id'];?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="?po_id=<?php echo functions::encode($po_id);?>&poidDel=<?php echo functions::encode($rPOI['po_item_id']);?>"><i class="halflings-icon white trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td></td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Total Amount</strong></div></td>
                        <td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
                        <td></td>
                    </tr>
                </table><p>&nbsp;</p>
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
function delt(){
    if(confirm('Do you want to remove this?'))
        return true;
    else
        return false; 
}

$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxItem').html("");
        $('#msgtxQty').html("");
        $('#msgtxQtyDel').html("");
        
        if( $('#txItem').val()=="" ){
            $('#msgtxItem').html("Item Required!");
            res=false;
        }
        else if( $('#txCost').val()=="" ){
            $('#msgtxCost').html("Required!");
            $('#txCost').focus();
            res=false;
        }
        else
            res=true;

        return res;
    });
});

function getXMLHTTP(){ //fuction to return the xml http object
    var xmlhttp=false;  
    try{
        xmlhttp=new XMLHttpRequest();
    }
    catch(e){   
        try{      
            xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(e){
            try{
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch(e1){
                xmlhttp=false;
            }
        }
    }
    return xmlhttp;
}

function searchMaterial(srch) {   
    var strURL="po_view_material_view.php?srch="+srch+"&poidEdt=<?php echo functions::encode($poidEdt)?>&po_id=<?php echo functions::encode($po_id)?>";
    var req = getXMLHTTP();
    if(req){
        req.onreadystatechange = function() {
            if(req.readyState == 4){
                // only if "OK"
                if(req.status == 200){            
                    document.getElementById('materialView').innerHTML=req.responseText;            
                }else{
                    alert("Problem while using XMLHTTP:\n" + req.statusText);
                }
            }
        }     
        req.open("GET", strURL, true);
        req.send(null);
    }
}
</script>
<!-- end: JavaScript-->
</body>
</html>