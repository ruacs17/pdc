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
$count=0;
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$item=(isset($_REQUEST['item']) && !empty($_REQUEST['item']) ) ? functions::decode($_REQUEST['item']) : 0;
$unit=(isset($_REQUEST['unt']) && !empty($_REQUEST['unt']) ) ? functions::decode($_REQUEST['unt']) : "";
$brand=(isset($_REQUEST['brnd']) && !empty($_REQUEST['brnd']) ) ? functions::decode($_REQUEST['brnd']) : "";
$material_type=(isset($_REQUEST['src']) && !empty($_REQUEST['src']) ) ? functions::decode($_REQUEST['src']) : "";
$quantity='';
$total_amount = 0;
$dte = date('Y-m-d');
if( isset($_POST['btnAdd']) && $p_id && $item ){
    $pw_date = (isset($_POST['txDateDelvr']) && !empty($_POST['txDateDelvr']) ) ? $_POST['txDateDelvr'] : "";
    $quantity = (isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : "";

    if( $pw_date && $quantity){
        if( $db->getValue('inhouse_material_item','count(*)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand)) || $db->getValue('po_item','count(*)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand)) )
            $db->insert('project_warehouse',array('proj_id'=>$p_id,'pw_date'=>$pw_date,'item'=>$item,'unit'=>$unit,'brand'=>$brand,'quantity'=>$quantity));
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?pid='.functions::encode($p_id).'&item='.functions::encode($item).'&unt='.functions::encode($unit).'&brnd='.functions::encode($brand));
}

$arrDate = array();
$qItemPO = $db->query('SELECT DISTINCT po_date FROM po_item pi,po p WHERE p.po_id=pi.po_id AND p.proj_id="'.$db->clean($p_id).'" AND pi.item="'.$db->clean($item).'" AND pi.unit="'.$db->clean($unit).'" AND pi.brand="'.$db->clean($brand).'" ORDER BY po_date');
while($rPO = $db->fetch_array($qItemPO)):
    if( $rPO['po_date'] ){
        if( !isset($arrDate[$rPO['po_date']]['delivered']) )
            $arrDate[$rPO['po_date']]['delivered']='po';
    }
endwhile;
$qItemIM = $db->query('SELECT DISTINCT im_date FROM inhouse_material im,inhouse_material_item imi WHERE im.im_id=imi.im_id AND im.proj_id="'.$db->clean($p_id).'" AND item="'.$db->clean($item).'" AND unit="'.$db->clean($unit).'" AND brand="'.$db->clean($brand).'" ORDER BY im_date');
while($rIM = $db->fetch_array($qItemIM)):
    if( $rIM['im_date'] ){
        if( !isset($arrDate[$rIM['im_date']]['delivered']) )
            $arrDate[$rIM['im_date']]['delivered']='inhouse';
    }
endwhile;
$qConsumed = $db->select('project_warehouse','DISTINCT pw_date',array('proj_id'=>$p_id,'item'=>$item,'unit'=>$unit,'brand'=>$brand));
while($rC = $db->fetch_array($qConsumed)):
    if( $rC['pw_date'] ){
        if( !isset($arrDate[$rC['pw_date']]['consumed']) )
            $arrDate[$rC['pw_date']]['consumed']='warehouse';
    }
endwhile;
ksort($arrDate);
#if(count($arrDate))
#functions::sortMultiArray($arrDate,$orderBy='',$ascDes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Item Inventory Report</title>
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Item Inventory Report</h2>
        </div>
        <div class="box-content">
            <div><h2><strong><?php echo $item;?></strong></h2></div>
            <div align="left" style="padding-left: 0px;font-size:12px;"><?php echo $brand;?></div><br>
            <form method="post">
                <table width="100%" border="0" align="center" style="display:none;">
                    <tr bgcolor="#faf7f7">
                        <th scope="col"><div align="center">Date</div></th>
                        <th scope="col"><div align="center">Quantity</div></th>
                        <th scope="col">&nbsp;</th>
                    </tr>
                    <tr bgcolor="#faf7f7">
                        <td valign="top">
                            <div align="center">
                                <a href="javascript:NewCssCal('txDateDelvr')">
                                    <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                                </a>
                                <input name="txDateDelvr" type="text" style="width:85px;" class="span6 mytextbox" id="txDateDelvr" value="<?php echo $dte?>" readonly>
                            </div>
                        </td>
                        <td valign="top">
                            <div align="center"><input type="text" style="width:50px;" name="txQty" id="txQty" value="<?php echo $quantity?>" placeholder="0" onkeypress="return checkinput(this, event);"></div>
                            <span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
                        </td>
                        <td valign="top"><div align="center"><input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary"></div></td>
                    </tr>
                </table><p>&nbsp;</p>
            </form>
            <table width="95%" border="0" align="center" id="asbcd" name="asbcd" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;" >
                <thead>
                    <tr>
                        <th scope="col"><div align="left">Date</div></th>
                        <th scope="col"><div align="center">Delivered</div></th>
                        <th scope="col"><div align="center">Consumed</div></th>
                        <th scope="col"><div align="center">On-stock</div></th>
                    </tr>
                    <tr>
                        <td colspan="4" height="25"></td>
                    </tr>
                </thead>   
                <tbody>
                <?php
                $countQty=0;$qtyBalance=0;
                foreach($arrDate as $date => $arrStat):
                    $qtyDeliver=0;
                    $qtyConsumed=0;
                    foreach($arrStat as $consumedDelivered => $po_im_wh):
                        if($consumedDelivered=='delivered'){
                            if($po_im_wh=='po')
                                $qtyDeliver += $db->getValue('po_item pi,po p','sum(quantity)',array('proj_id'=>$p_id,'po_date'=>$date,'item'=>$item,'unit'=>$unit,'brand'=>$brand),'AND p.po_id=pi.po_id');
                            else if($po_im_wh=='inhouse')
                                $qtyDeliver += $db->getValue('inhouse_material im,inhouse_material_item imi','sum(quantity)',array('proj_id'=>$p_id,'im_date'=>$date,'item'=>$item,'unit'=>$unit,'brand'=>$brand),'AND im.im_id=imi.im_id');
                        }
                        if($consumedDelivered =='consumed'){
                            $qtyConsumed += $db->getValue('project_warehouse','sum(quantity)',array('proj_id'=>$p_id,'pw_date'=>$date,'item'=>$item,'unit'=>$unit,'brand'=>$brand));
                        }
                    endforeach;

                    $qtyBalance += $qtyDeliver;
                    $qtyBalance -= $qtyConsumed;
                    $qtyDeliver = ( functions::isfloat($qtyDeliver) ) ? number_format($qtyDeliver,2) : $qtyDeliver;
                    $qtyConsumed= ( functions::isfloat($qtyConsumed) ) ? number_format($qtyConsumed,2) : $qtyConsumed;
                ?>
                    <tr>
                        <td><?php echo functions::datearr($date);?></td>
                        <td><div align="center"><?php echo ($qtyDeliver) ? $qtyDeliver : ''?></div></td>
                        <td><div align="center"><?php echo ($qtyConsumed) ? $qtyConsumed : ''?></div></td>
                        <td><div align="center"><strong><?php echo ( functions::isfloat($qtyBalance) ) ? number_format($qtyBalance,2) : $qtyBalance?></strong></div></td>
                    </tr>
                <?php endforeach;?>
                    <tr>
                        <td colspan="3"><div align="right">Remaining Stock <?php echo ($unit) ? ' in <i>('.$unit.')</i>' : ''?></div></td>
                        <td><div align="center"><strong><?php echo ( functions::isfloat($qtyBalance) ) ? number_format($qtyBalance,2) : $qtyBalance?></strong></div></td>
                    </tr>
                <tbody>
            </table>
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
<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
<script src="../js/inputInt.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
$(document).ready(function(){
    var res = false;
    $('#btnAdd').click(function(){
        $('#msgtxQty').html("");
        
        if( $('#txQty').val()=="" ){
            $('#msgtxQty').html("Required!");
            $('#txQty').focus();
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