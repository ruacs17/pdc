<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/voucher_advance.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$arr = array();
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');

$date_start = ( isset($_REQUEST['wkF']) && !empty($_REQUEST['wkF']) ) ? functions::decode($_REQUEST['wkF']) : '';
$date_end = ( isset($_REQUEST['wkE']) && !empty($_REQUEST['wkE']) ) ? functions::decode($_REQUEST['wkE']) : '';
$vt_id = ( isset($_REQUEST['vt']) && !empty($_REQUEST['vt']) ) ? functions::decode($_REQUEST['vt']) : '';
$qVTid = ($vt_id) ? ' AND vt_id="'.$db->clean($vt_id).'"' : '';

$txbYear = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : '';
$txbMon = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$txVtype = ( isset($_REQUEST['vt']) && !empty($_REQUEST['vt']) ) ? functions::decode($_REQUEST['vt']) : '';
$count=0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Voucher Weekly Payable Print</title>
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
<?php $voucherType =  ($txVtype) ? $db->getValue('voucher_type','vt_name',array('vt_id'=>$txVtype)) : 'All Voucher Type'?>
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php 
                require_once('../class/print_header.php');
                print_header('Voucher Weekly Payable <i>('.$voucherType.')</strong><br><i>'.functions::datearr($date_start).' - '.functions::datearr($date_end).'</i><strong>');
                ?>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle">&nbsp;</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table border="1" style="font-size: 12px">
                    <thead>
                        <tr style="background-color:#E4E1E1">
                            <th height="30px" width="5%" style="text-align:left;padding-left:5px;">Voucher #</th>
                            <th width="5%" style="text-align:left;padding-left:5px;">Cheque #</th>
                            <th width="9%" style="text-align:left;padding-left:5px;">Cheque Date</th>
                            <th width="30%" style="text-align:left;padding-left:5px;">Supplier / Payee</th>
                            <th width="9%" style="text-align:left;padding-left:5px;">Payable Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $total=0;$advance_payment=0;$withholding_tax=0;
                    $qVoucher = $db->query('SELECT * FROM voucher WHERE cheque_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" '.$qVTid.' ORDER BY cheque_date');
                    while($rVouch = $db->fetch_array($qVoucher)):
                        $payable_amount=0;

                        $otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'surcharge'));
                        $payable_amount += $otherSurcharge;

                        $amount_q = $db->query('SELECT SUM(vd.amount) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVouch['voucher_id']).'"'.$qVTid);
                        $non_po = $db->result($amount_q);
                        $payable_amount += $non_po;

                        $amount_po_q = $db->query('SELECT sum(amount) FROM voucher v,voucher_particular vp, voucher_po_payment vpp WHERE v.voucher_id=vp.voucher_id AND vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rVouch['voucher_id']).'"'.$qVTid);
                        $po = $db->result($amount_po_q);
                        $payable_amount += $po;

                        $withholding = $rVouch['witholding_tax'];
                        $withholding_vat = $rVouch['witholding_vat'];
                        $withholding_percent = ($withholding) ? $withholding / 100 : 0;
                        $advance_payment = $rVouch['advance_payment'];

                        $hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$rVouch['voucher_id']));
                        //if voucher has advance payment, it also computed the withholding tax.
                        if($hasAdvance){
                            $va = new voucherAdvance($rVouch['voucher_id']);
                            $payable_amount = $va->current_voucher_payable_amount;
                        }
                        else{//Deduction of the withholding tax.
                            if($withholding_vat==1)
                                $w_tax = ($withholding_percent && $payable_amount) ? (($payable_amount) / 1.12) * $withholding_percent : 0;
                            else
                                $w_tax = ($withholding_percent && $payable_amount) ? $payable_amount * $withholding_percent : 0;
                            $payable_amount -= $w_tax;
                        }

                        $otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'deduction'));
                        if($otherDeduction)
                            $payable_amount -= $otherDeduction;
                        $total += $payable_amount;
                    ?>
                        <tr>
                            <td height="30px" style="text-align:left;padding-left:5px;"><?php echo $rVouch['voucher_no'];?></td>
                            <td style="text-align:left;padding-left:5px;"><?php echo $rVouch['cheque_id'];?></td>
                            <td style="text-align:left;padding-left:5px;"><?php echo functions::datearr($rVouch['cheque_date']);?></td>
                            <td style="text-align:left;padding-left:5px;"><?php echo $db->getValue('supplier','name',array('supplierID'=>$rVouch['supplierID']));?></td>
                            <td style="text-align:right;padding-right:5px;"><?php echo functions::formatMoney($payable_amount);?></td>
                        </tr>
                    <?php endwhile;?>
                        <tr>
                            <td height="30px">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td><div align="right">Total Amount</div></td>
                            <td style="text-align:right;padding-right:5px;"><strong><?php echo functions::formatMoney($total);?></strong></td>
                        </tr>
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
    window.location="voucher_list_weekly_detail.php?wkF=<?php echo functions::encode($date_start)?>&wkE=<?php echo functions::encode($date_end)?>&vt=<?php echo functions::encode($vt_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>