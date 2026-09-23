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
$equip_id = (isset($_REQUEST['eqid']) && !empty($_REQUEST['eqid']) ) ? functions::decode($_REQUEST['eqid']) : 0;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$qpo = $db->select('po','*',array('po_id'=>$po_id));
$rPO = $db->fetch_array($qpo);
$chargeTo = ($rPO['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id'])) : $db->getValue('po_fuel','payee',array('po_id'=>$po_id));
$plate_no = $db->getValue('equipment','plate_no',array('equip_id'=>$equip_id));
$requested_by = $db->getValue('po_fuel','requested_by',array('po_id'=>$po_id));

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
    <title>P.O. Fuel Print</title>
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
                print_header('FUEL PURCHASE ORDER');
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table width="100%" border="0">
                    <tr>
                        <td width="12%" align="right">&nbsp;</td>
                        <td width="50%" align="right">&nbsp;</td>
                        <td width="15%" align="right">P.O. No:</td>
                        <td width="25%" align="left"><strong><?php echo $rPO['po_no'];?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" height="20">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="left" colspan="2">Date: <strong> <?php echo functions::datearr($rPO['po_date']);?></strong></td>
                        <td align="left">&nbsp;</td>
                        <td align="left">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="4" height="20">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">Charge To:</td>
                        <td align="left"><strong><?php echo $chargeTo;?></strong></td>
                        <td align="left">&nbsp;</td>
                        <td align="left">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="4" height="20">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <table border="0" width="90%" style="font-size:12px;">
                                <tr>
                                    <td height="30" colspan="2"><strong>Equipment</strong></td>
                                    <td width="20%" align="left"><strong>Plate No</strong></td>
                                </tr>
                                <?php 
                                if($equip_id){
                                    if( $db->getValue('equipment','count(equip_id)',array('equip_id'=>$equip_id)) )
                                        $qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'equip_id'=>$equip_id));
                                    else
                                        $qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'other_equip'=>$equip_id));
                                } 
                                else
                                    $qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id']));
                                $allEqp = $db->num_rows($qEqp);
                                $cntEqp=1;
                                while($rEqp = $db->fetch_array($qEqp)):
                                    $cntEqpDisp = ($allEqp > 1) ? $cntEqp++.'.' : '&nbsp;';
                                    echo '<tr>';
                                    if( $eqpName = $db->getValue('equipment','name',array('equip_id'=>$rEqp['equip_id'])) ){
                                        echo '<td valign="top" width="4%">'.$cntEqpDisp.'</td>';
                                        echo '<td>'.$eqpName.'</td>';
                                        echo '<td valign="top" align="left">'.$db->getValue('equipment','plate_no',array('equip_id'=>$rEqp['equip_id'])).'</td>';
                                    }
                                    else{
                                        echo '<td>'.$cntEqpDisp.'</td>';
                                        echo '<td>'.$rEqp['other_equip'].' <i>(outsider)</i></td>';
                                        echo '<td>&nbsp;</td>';
                                    }
                                    echo '</tr>';
                                endwhile;
                                ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" height="20">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Supplier:</td>
                        <td colspan="3"><strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" height="20">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Purpose:</td>
                        <td colspan="3"><?php echo $rPO['proj_detail']?></td>
                    </tr>
                    <tr>
                        <td colspan="4" height="20">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" border="1">
                    <tr>
                        <th width="30%" scope="col"><div align="center">Item</div></th>
                        <th width="9%" scope="col"><div align="center">Qty</div></th>
                        <th width="10%" scope="col"><div align="center">Unit</div></th>
                        <th width="20%" scope="col"><div align="center">Brand</div></th>
                        <th width="14%" scope="col"><div align="center">Cost</div></th>
                        <th width="17%" scope="col"><div align="center">Amount</div></th>
                    </tr>
                    <?php
                    $total_amount=0;$amount=0;
                    $qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
                    while($rPOI = $db->fetch_array($qPOI)):
                        $amount = $rPOI['cost'] * $rPOI['qty_delivered'];
                        $amount = ($amount) ? $amount : $rPOI['cost'];
                        $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                        $amount = $amount - $disc_amount;
                        $total_amount += $amount;
                    ?>
                    <tr>
                        <td><div align="center" class="padParLeft"><?php echo $rPOI['item'];?></div></td>
                        <td><div align="center"><?php echo ($rPOI['qty_delivered']) ? round($rPOI['qty_delivered'],2) : '';?></div></td>
                        <td><div align="center"><?php echo $rPOI['unit'];?></div></td>
                        <td><div align="center"><?php echo $rPOI['brand'];?></div></td>
                        <td><div align="center"><?php echo ($rPOI['cost']) ? functions::formatMoney($rPOI['cost']) : '';?></div></td>
                        <td><div align="center"><?php echo ($amount) ? functions::formatMoney($amount) : '';?> <?php echo ($rPOI['discount']) ? '<br><i style="font-size:11px">('.$rPOI['discount'].'% off)</i>' : '';?></div></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Total</strong></div></td>
                        <td><div align="center"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
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
                      <td align="center">Requested By:</td>
                      <td align="center">Prepared By:</td>
                      <td align="center">Approved By:</td>
                  </tr>
                    <tr>
                      <td height="50" align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$requested_by)));?></strong></td>
                      <td height="50" align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rPO['purchaser'])));?></strong></td>
                      <td align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rPO['approved_by'])));?></strong></td>
                  </tr>
                </table>
                <div style="padding:5px;">&nbsp;</div>
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
    <div align="right">18PMD.FRM033.00-10/18</div>
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