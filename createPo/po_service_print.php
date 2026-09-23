<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$qpo = $db->select('po','*',array('po_id'=>$po_id));
$rPO = $db->fetch_array($qpo);
$cat_id = $db->getValue('item_deduction','name',array('item_id'=>$rPO['category_id']));

if($rPO['po_id']){
    require_once('../class/class-po-history.php');
    PO_history::poPrint($rPO['po_id'],$user_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Service Order Print</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <link href="../css/printerfoot.css" rel="stylesheet">
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
    <style type="text/css">
    .padParLeft{padding-left:10px;}
    .padAmLeft{padding-left:60px;}
    </style>
    <script>window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('SERVICE ORDER');
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table width="100%" border="0">
                    <tr>
                        <td align="right">P.O. No: <strong> <?php echo $rPO['po_no'];?></strong></td>
                    </tr>
                    <tr>
                        <td align="right">Date:<strong> <?php echo functions::datearr($rPO['po_date']);?></strong></td>
                    </tr>
                    <tr>
                        <td width="67%" align="left">
                            <strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID'])));?></strong>
                            <div style="font-size:12px;"><i><?php echo strtoupper($db->getValue('supplier','address',array('supplierID'=>$rPO['supplierID'])));?></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left">
                            <strong> 
                            <?php
                            $contactPhoneNo = $db->getValue('supplier','contactPhoneNo',array('supplierID'=>$rPO['supplierID']));
                            $contactPhoneNo2 = $db->getValue('supplier','contactPhoneNo2',array('supplierID'=>$rPO['supplierID']));
                            $contactCellNo = $db->getValue('supplier','contactCellNo',array('supplierID'=>$rPO['supplierID']));
                            $contactCellNo2 = $db->getValue('supplier','contactCellNo2',array('supplierID'=>$rPO['supplierID']));
                            echo $db->getValue('supplier','contactPhoneNo',array('supplierID'=>$rPO['supplierID']));
                            if($contactPhoneNo2) echo ' / '.$contactPhoneNo2;
                            if($contactCellNo) echo ' / '.$contactCellNo;
                            if($contactCellNo2) echo ' / '.$contactCellNo2;     
                            ?>
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Attention: <strong><?php echo $db->getValue('supplier','contactPerson',array('supplierID'=>$rPO['supplierID']));?></strong></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;We would like to avail of your services for <strong><u><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id']));?> <?php echo ($rPO['proj_detail']) ? " - ".$rPO['proj_detail'] : "";?></u></strong><i style="font-size: 10px;">(<?php echo $cat_id; ?>)</i>. 
                            As herein described below based on your quotation in the total amount of <strong><u>
                            <?php
                            $totAmount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$po_id));
                            $ta = new MoneytoWords($totAmount);
                            echo $ta->words;
                            ?>
                            Only.</u></strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" border="1">
                    <tr>
                        <th width="36%" scope="col"><div align="center">Service Description</div></th>
                        <th width="17%" scope="col"><div align="center">Cost</div></th>
                        <th width="20%" scope="col"><div align="center">Amount</div></th>
                    </tr>
                    <?php
                    $total_amount=0;
                    $amount=0;
                    $qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
                    while($rPOI = $db->fetch_array($qPOI)):
                        $amount = $rPOI['cost'] * $rPOI['qty_delivered'];
                        $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                        $amount = $amount - $disc_amount;
                        $total_amount += $amount;
                    ?>
                    <tr>
                        <td><div align="left" class="padParLeft"><?php echo $rPOI['item'];?></div></td>
                        <td><div align="center"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
                        <td><div align="center"><?php echo functions::formatMoney($amount);?> <?php echo ($rPOI['discount']) ? '<br><i style="font-size:11px">('.$rPOI['discount'].'% off)</i>' : '';?></div></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Total</strong></div></td>
                        <td><div align="center"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><?php echo $db->getValue('po','terms',array('po_id'=>$rPO['po_id']));?></td>
        </tr>
        <tr>
            <td>
                <table width="100%" border="0">
                    <tr>
                        <td width="15%" height="41" align="right" valign="top">&nbsp;</td>
                        <td width="85%" valign="bottom">We hope that you facilitate our order the soonest possible time.</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td height="39">&nbsp;</td>
        </tr>
        <tr>
            <td align="center">
                <table width="100%" border="0">
                    <tr>
                        <td align="center">Prepared By:</td>
                        <td align="center">Approved By:</td>
                    </tr>
                    <tr>
                        <td height="50" align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rPO['purchaser'])));?></strong></td>
                        <td align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rPO['approved_by'])));?></strong></td>
                    </tr>
                    <tr>
                        <td align="center">Maker</td>
                        <td align="center">CEO</td>
                    </tr>
                </table>
                <table width="100%" border="0">
                    <tr>
                        <td align="center" width="30%">&nbsp;</td>
                        <td align="center">Conforme:</td>
                        <td align="center" width="30%">&nbsp;</td>
                    </tr>
                    <tr>
                        <td height="30" align="center">&nbsp;</td>
                        <td align="center">
                            <div style="padding:2px;">&nbsp;</div>
                            <div><strong><?php echo ($rPO['conforme']) ? $rPO['conforme'] : '&nbsp;'; ?></strong></div>
                            <div style="text-decoration:overline;">Signature over Printed Name</div>
                            <div>Date: _________</div>
                        </td>
                        <td align="center">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<footer>
    <div align="right">18PMD.FRM016.00-10/18</div>
</footer>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
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
<!-- end: JavaScript-->
</body>
</html>