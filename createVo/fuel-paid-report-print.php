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

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
$txSearchVo='';
$equip_id='';

$txSearchVo = ( isset($_SESSION['fr_Vo']) ) ? $_SESSION['fr_Vo'] : '';
$where = '';

    if($txSearchVo)
        $where .= ' AND voucher_no='.$db->clean($txSearchVo);

    $count=0;
    $totalUnpaidAmount=0;
    $totalAmount=0;
    $totalAmountFuel=0;
    $totalAmountOil=0;
    $totalFuelLiter=0;
    $totalOilLiter=0;
    $arrFPO = array();

if($txSearchVo){
        $qPO = $db->query('SELECT * FROM po p, po_fuel pf, voucher v, voucher_particular vp WHERE vp.voucher_id=v.voucher_id AND p.vp_id=vp.vp_id AND p.po_id=pf.po_id AND po_type="fuel" AND p.vp_id IS NOT NULL '.$where.' ORDER BY cheque_date DESC');
    while($rPO = $db->fetch_array($qPO)):
        $count++;
        $payee = $db->getValue('po_fuel','payee',array('po_id'=>$rPO['po_id']));
        $qItem = $db->select('po_item','*',array('po_id'=>$rPO['po_id']));
        while($rItem = $db->fetch_array($qItem)):
            $amountFuel = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%fuel%"');
            $totalAmountFuel += $amountFuel;

            $fuelLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%fuel%"');
            $totalFuelLiter += $fuelLiter;

            $fuelPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%fuel%"');

            $oilLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%oil%"');
            $totalOilLiter += $oilLiter;

            $oilPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%oil%"');

            $amountOil = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%oil%"');
            $totalAmountOil += $amountOil;

            $amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']));
            $totalAmount += $amount;

            $amount = ($amount) ? $amount : $db->getValue('po_item','sum( cost - (cost * (discount/100)) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']));
        endwhile;
        
        $fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

        $oilLiter = (functions::isfloat($oilLiter)) ? functions::formatMoney($oilLiter) : $oilLiter;
        
        $arrFPO[] = array('po_id'=>$rPO['po_id'],'po_no'=>$rPO['po_no'],'voucher_no'=>$rPO['voucher_no'],'vdate'=>$rPO['vdate'],'cheque_date'=>$rPO['cheque_date'],'po_date'=>$rPO['po_date'],'invoice'=>$rPO['invoice'],'proj_id'=>$rPO['proj_id'],'payee'=>$payee,'fuelLiter'=>$fuelLiter,'fuelPrice'=>$fuelPrice,'oilLiter'=>$oilLiter,'oilPrice'=>$oilPrice,'amount'=>$amount,'equip_id'=>'','tranType'=>'po');
    endwhile;
}


$whereVoucher = ''; 
if($txSearchVo){
    $whereVoucher = ' AND voucher_no='.$db->clean($txSearchVo);

    $qFuelVoucher = $db->query('SELECT * FROM equip_fuel ef, voucher_detail vd, voucher v, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vd.vp_id=vp.vp_id AND vd.ef_id=ef.ef_id '.$whereVoucher.' ORDER BY cheque_date DESC');

while( $rFV = $db->fetch_array($qFuelVoucher)):
    $count++;
    $oilPrice=0;
    $fuelPrice=0;
    $amount = $rFV['amount_issue'];
    $totalAmount += $amount;
    if( $db->getValue('equip_fuel','count(*)',array('ef_id'=>$rFV['ef_id']),'AND remarks LIKE "%oil%"') )
        $oilPrice = $amount;
    else
        $fuelPrice = $amount;
    $totalAmountOil += $oilPrice;
    $totalAmountFuel += $fuelPrice;
    $arrFPO[] = array('po_id'=>'','po_no'=>$rFV['voucher_no'],'voucher_no'=>$rFV['voucher_no'],'vdate'=>$rFV['vdate'],'cheque_date'=>$rFV['cheque_date'],'po_date'=>$rFV['vd_date'],'invoice'=>'','proj_id'=>$rFV['proj_id'],'payee'=>'','fuelLiter'=>'','fuelPrice'=>$fuelPrice,'oilLiter'=>'','oilPrice'=>$oilPrice,'amount'=>$amount,'equip_id'=>$rFV['equip_id'],'tranType'=>'voucher');
endwhile;
}
if($txSearchVo){
    $qPart = $db->query('SELECT * FROM voucher v, voucher_particular vp, voucher_detail vd WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_no="'.$db->clean($txSearchVo).'"');
    while($rPart = $db->fetch_array($qPart)):
        $totalAmount += $rPart['amount'];
        $arrFPO[] = array('po_id'=>'','po_no'=>$rPart['voucher_no'],'voucher_no'=>$rPart['voucher_no'],'vdate'=>$rPart['vdate'],'cheque_date'=>$rPart['cheque_date'],'po_date'=>$rPart['vd_date'],'invoice'=>'','proj_id'=>$rPart['proj_id'],'payee'=>'','fuelLiter'=>'','fuelPrice'=>'','oilLiter'=>'','oilPrice'=>'','amount'=>$rPart['amount'],'equip_id'=>'','tranType'=>'voucher');
    endwhile;
}
if(count($arrFPO))
functions::sortMultiArray($arrFPO,$orderBy='proj_id',$ascDes='DESC');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Equipment List Print</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
    </head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('FUEL REPORT');
                ?><br>
            </td>
        </tr>
    </thead>
    </tbody>
        <tr>
            <td>
                <table class="table table-bordered table-hover" style="font-size:12px">
                    <thead>
                        <tr>
                            <th width="7%">Voucher No</th>
                            <th width="9%">Cheque Date</th>
                            <th width="9%">P.O. No</th>
                            <th width="7%">Invoice</th>
                            <th width="15%"><div align="center">Equipment</div></th>
                            <th width="21%">Charge To</th>
                            <th width="7%"><div align="center">Cost</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $chargeTo='';$totalCost=0;$count=0;
                        foreach($arrFPO as $rPO):
                                $dif=0;
                                $chargeTo=($rPO['proj_id']) ? $rPO['proj_id'] : $rPO['payee'];
                                if( isset($arrFPO[$count + 1]) ){
                                    $nextCharge = ($arrFPO[$count +1]['proj_id']) ? $arrFPO[$count +1]['proj_id'] : $arrFPO[$count +1]['payee'];
                                    if($chargeTo != $nextCharge )
                                        $dif=1;
                                }
                                else
                                    $dif=1;
                                $count++;
                        ?>
                        <tr>
                            <td><?php echo $rPO['voucher_no'];?></td>
                            <td><?php echo functions::datearr($rPO['cheque_date']);?></td>
                            <td><?php echo ($rPO['tranType']=='po') ? $rPO['po_no'] : '<i>(non-po)</i>';?></td>
                            <td><?php echo $rPO['invoice'];?></td>
                            <td valign="middle">
                                <table border="0">
                                    <?php
                                    if($equip_id){
                                        if( $db->getValue('equipment','count(equip_id)',array('equip_id'=>$equip_id)) )
                                            $qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'equip_id'=>$equip_id));
                                        else
                                            $qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'other_equip'=>$equip_id));
                                    } 
                                    else
                                        $qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id']));

                                        $arrUsedEquip=array();
                                        while($rEqp = $db->fetch_array($qEqp)):
                                            $eqpName = $db->getValue('equipment','name',array('equip_id'=>$rEqp['equip_id']));
                                            $eqpName = ($eqpName) ? $eqpName :  $rEqp['other_equip'];
                                            $arrUsedEquip = functions::insert_array($arrUsedEquip,$eqpName);
                                        endwhile;
                                        sort($arrUsedEquip);
                                        $allEqp = count($arrUsedEquip);
                                        $cntEqp=1;
                                        foreach($arrUsedEquip as $eqp_name):
                                            $cntEqpDisp = ($allEqp > 1) ? '<strong>'.$cntEqp++.')</strong>' : '&nbsp;';
                                            echo '<tr>';
                                                echo '<td style="border: none;padding: 0px">'.$cntEqpDisp.'</td>';
                                                echo '<td style="border: none;padding: 0px">'.$eqp_name.'</td>';
                                            echo '</tr>';
                                        endforeach;
                                        if(($rPO['tranType']=='voucher')){
                                            echo '<tr>';
                                                echo '<td style="border: none;padding: 0px">&nbsp;</td>';
                                                echo '<td style="border: none;padding: 0px">'.$db->getValue('equipment','name',array('equip_id'=>$rPO['equip_id'])).'</td>';
                                            echo '</tr>';
                                        }
                                    ?>
                                </table>
                            </td>
                            <td><?php echo ($rPO['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id'])) : $rPO['payee'].' <i>(outsider)</i>';?></td>
                            <td><div align="center"><?php echo ($rPO['amount']) ? functions::formatMoney($rPO['amount']) : '--';?></div></td>
                        </tr>
                            <?php
                            $totalCost+=$rPO['amount'];
                            if($dif){?>
                            <tr>
                                <td height="40" >&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td><div align="center"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
                            </tr>
                            <?php $totalCost=0;
                            }
                            endforeach;
                            if($count==0){
                                echo '<tr><td colspan="7"><div align="center"><strong>-- No result --</strong></div></td></tr>';
                            }
                            else{
                            ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td><div align="right"><strong>Total</strong></div></td>
                            <td><div align="center"><strong><?php echo (functions::isfloat($totalAmount)) ? functions::formatMoney($totalAmount) : $totalAmount;?></strong></div></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
    $(document).ready(function(){
        window.print();
        setTimeout("closePrint()",200);
    });
    function closePrint(){
        window.location="fuel-paid-report.php";
    }
</script>
<!-- end: JavaScript-->
</body>
</html>