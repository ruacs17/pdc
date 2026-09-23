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
function position($emp_id){
    global $db;
    $countPos=0;$position='';
    $qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
    while($rPos = $db->fetch_array($qPos)):
        if($countPos)
            $position .= ' /<br>';
        $position .= $rPos['pos_name'];
        $countPos++;
    endwhile;
    return $position;
}
$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;

$qpo = $db->select('po','ref_id,po_date,delivery_date,supplierID,invoice,item_received_by,item_encoded_by,report_received_by,receive_no,remarks',array('ref_id'=>$ref_id),'GROUP BY ref_id');
$rPO = $db->fetch_array($qpo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Receiving Report</title>
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
    <script>//window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div align="right"><a id="adca" class="btn btn-info" href="po_receive_edit.php?ref_id=<?php echo functions::encode($ref_id)?>">Update</a>&nbsp;<a id="poPrint" href="po_receive_print.php?ref_id=<?php echo functions::encode($ref_id)?>" class="btn btn-info">Print</a></div>
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('RECEIVING REPORT');
                ?>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table width="100%" border="0" style="font-size:11px;">
                    <tr>
                        <td align="left" height="4">Invoice:<strong> <?php echo $rPO['invoice'];?></strong></td>
                        <td align="left" width="25%">Date Received:<strong> <?php echo functions::datearr($rPO['delivery_date']);?></strong></td>
                    </tr>
                    <tr>
                        <td align="left">Provider: <strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
                        <td align="left">R.R. No: <strong> <?php echo $rPO['receive_no'];?></strong></td>
                    </tr>
                    <tr>
                        <td align="left">Address: <strong><?php echo strtoupper($db->getValue('supplier','address',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
                        <td align="left">P.O. No: <strong> <?php echo $rPO['ref_id'];?></strong></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" border="1" style="font-size:11px;">
                    <tr>
                        <th width="40%" scope="col"><div align="center">Item</div></th>
                        <th width="15%" scope="col"><div align="center">Qty</div></th>
                        <th width="14%" scope="col"><div align="center">Brand</div></th>
                        <th width="10%" scope="col"><div align="center">Cost</div></th>
                        <th width="20%" scope="col"><div align="center">Amount</div></th>
                    </tr>
                    <?php
                    $total_amount=0;
                    $amount=0;
                    $qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,sum(qty_delivered) as qty_d,unit,brand,cost,discount FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item');
                    while($rPOI = $db->fetch_array($qPOI)):
                        $amount = $rPOI['cost'] * $rPOI['qty_d'];
                        $disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
                        $amount = $amount - $disc_amount;
                        $total_amount += $amount;
                    ?>
                    <tr>
                        <td><div align="left" class="padParLeft"><?php echo $rPOI['items'];?></div></td>
                        <td><div align="center"><?php echo round($rPOI['qty_d'],2).' <i>('.$rPOI['unit'].')</i>';?></div></td>
                        <td><div align="center"><?php echo $rPOI['brand'];?></div></td>
                        <td><div align="center"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
                        <td><div align="center"><?php echo functions::formatMoney($amount);?> <?php echo ($rPOI['discount']) ? ' <i style="font-size:11px">('.$rPOI['discount'].'% off)</i>' : '';?></div></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td colspan="3"><div align="center">--Nothing Follows--</div></td>
                        <td><div align="right"><strong>Total</strong></div></td>
                        <td><div align="center"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php if($rPO['remarks']){?>
        <tr style="font-size:11px;">
            <td><strong>Remarks:</strong> <?php echo $rPO['remarks'];?></td>
        </tr>
        <?php }?>
        <tr>
            <td align="center">
                <table width="100%" border="1" style="font-size:10px;">
                    <tr>
                        <td align="left" width="33%">
                            Item Received By:<br>
                            <strong><?php echo strtoupper($db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rPO['item_received_by'])));?></strong><br>
                            <?php echo position($rPO['item_received_by']);?>
                        </td>
                        <td align="left" width="33%">
                            Report Received By:<br>
                            <strong><?php echo strtoupper($db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rPO['report_received_by'])));?></strong><br>
                            <?php echo position($rPO['report_received_by']);?>
                        </td>
                    </tr>
                    <tr>
                        <td align="left">Date</td>
                        <td align="left">Date</td>
                    </tr>
                </table>
                <div align="right">18PMD.FRM016.00-10/18</div>
            </td>
        </tr>
    </tbody>
</table>
</body>
</html>