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
$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;
$qpo = $db->select('po','*',array('ref_id'=>$ref_id));
$rPO = $db->fetch_array($qpo);
$requester = $db->getValue('po_fuel','requester',array('po_id'=>$rPO['po_id']));
$equip_id = (isset($_REQUEST['eqid']) && !empty($_REQUEST['eqid']) ) ? functions::decode($_REQUEST['eqid']) : 0;
$plate_no = $db->getValue('equipment','plate_no',array('equip_id'=>$equip_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Multiple Fuel P.O. Print</title>
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
<table  width="700" border="0" align="center" style="font-size:14px;">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('FUEL PURCHASE ORDER: '.$ref_id);
                ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table width="100%" border="0">
                    <tr>
                        <td align="right">P.O. No: <strong> <?php echo $ref_id;?></strong></td>
                    </tr>
                    <tr>
                        <td align="right">Date:<strong> <?php echo functions::datearr($rPO['po_date']);?></strong></td>
                    </tr>
                    <tr>
                        <td width="60%" align="left">Supplier: <strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
                    </tr>
                    <tr>
                        <td align="left"><?php echo ($rPO['proj_detail']) ? 'Purpose: <strong>'.$rPO['proj_detail'].'</strong><br><br>' : ''?></td>
                    </tr>
                    <?php
                    $count=0;
                    $qProjs = $db->query('SELECT proj_id, pf.payee as "pay",p.po_id FROM po_fuel pf, po p WHERE pf.po_id=p.po_id AND p.ref_id="'.$db->clean($ref_id).'"');
                    while($rProjs = $db->fetch_array($qProjs)):
                        $count++;
                        $projName = $db->getValue('project','proj_name',array('proj_id'=>$rProjs['proj_id']));
                        $projName = ($projName) ? $projName : $rProjs['pay'];
                        $po_id = $rProjs['po_id'];
                    ?>
                    <tr>
                        <td>
                            <br><strong>Charge To</strong><br>
                            <?php echo '<strong>'.$count.')</strong>.'.$projName?><br><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" border="1" style="font-size:12px;">
                                <tr>
                                    <th width="32%" scope="col"><div align="center">Equipment</div></th>
                                    <th width="20%" scope="col"><div align="center">Item</div></th>
                                    <th width="9%" scope="col"><div align="center">Qty</div></th>
                                    <th width="8%" scope="col"><div align="center">Unit</div></th>
                                    <th width="12%" scope="col"><div align="center">Brand</div></th>
                                    <th width="10%" scope="col"><div align="center">Cost</div></th>
                                    <th width="15%" scope="col"><div align="center">Amount</div></th>
                                </tr>
                                <?php
                                $total_amount=0;$amount=0;
                                $qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
                                while($rPOI = $db->fetch_array($qPOI)):
                                    $eqpName='';
                                    $amount = $rPOI['cost'] * $rPOI['qty_delivered'];
                                    $amount = ($amount) ? $amount : $rPOI['cost'];
                                    $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                                    $amount = $amount - $disc_amount;
                                    $total_amount += $amount;
                                    if( $eqpID = $db->getValue('po_fuel_equipment','equip_id',array('po_item_id'=>$rPOI['po_item_id'])) ){
                                        $eqpName = $db->getValue('equipment','concat(name, " ",plate_no)',array('equip_id'=>$eqpID));
                                    }
                                    elseif( $eqpID = $db->getValue('po_fuel_equipment','other_equip',array('po_item_id'=>$rPOI['po_item_id'])) ){
                                        $eqpName = $eqpID;
                                    }
                                ?>
                                <tr>
                                    <td><div align="center" class="padParLeft"><?php echo $eqpName;?></div></td>
                                    <td><div align="center"><?php echo $rPOI['item'];?></div></td>
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
                                    <td>&nbsp;</td>
                                    <td><div align="right"><strong>Total</strong></div></td>
                                    <td><div align="center"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <?php endwhile;?>
                    <tr><td colspan="4">&nbsp;</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <div align="center">---- Summary ----</div>
                <table width="100%" border="1" style="font-size:12px;">
                    <tr>
                        <th width="30%" scope="col"><div align="center">Item</div></th>
                        <th width="9%" scope="col"><div align="center">Qty</div></th>
                        <th width="10%" scope="col"><div align="center">Unit</div></th>
                        <th width="20%" scope="col"><div align="center">Brand</div></th>
                        <th width="14%" scope="col"><div align="center">Cost</div></th>
                        <th width="17%" scope="col"><div align="center">Amount</div></th>
                    </tr>
                    <?php
                    $total_amount=0;
                    $amount=0;
                    $qPOI = $db->query('SELECT pi.item,sum(quantity) as qty,sum(qty_delivered) as qty_d,unit,brand,cost,cost as t_cost,discount FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand,cost,discount');
                    while($rPOI = $db->fetch_array($qPOI)):
                        $amount = $rPOI['t_cost'] * $rPOI['qty_d'];
                        $amount = ($amount) ? $amount : $rPOI['t_cost'];
                        $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                        $amount = $amount - $disc_amount;
                        $total_amount += $amount;
                    ?>
                    <tr>
                        <td><div align="center" class="padParLeft"><?php echo $rPOI['item'];?></div></td>
                        <td><div align="center"><?php echo ($rPOI['qty_d']) ? round($rPOI['qty_d'],2) : 0;?></div></td>
                        <td><div align="center"><?php echo $rPOI['unit'];?></div></td>
                        <td><div align="center"><?php echo $rPOI['brand'];?></div></td>
                        <td><div align="center"><?php echo ($rPOI['t_cost']) ? functions::formatMoney($rPOI['t_cost']) : '';?></div></td>
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
                        <td height="50" align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('equip_user','CONCAT(fname," ",left(mname,1),". ",lname)',array('eu_id'=>$requester)));?></strong></td>
                        <td height="50" align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rPO['purchaser'])));?></strong></td>
                        <td align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rPO['approved_by'])));?></strong></td>
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