<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
require_once('../class/voucher_advance.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$txPayee = '';$txbMon = '';$txbYear = '';$txVtype = '';
$arr = array();
$txPayee = ( isset($_SESSION['vPayee']) && !empty($_SESSION['vPayee']) ) ? $_SESSION['vPayee'] : '';
$txbMon = ( isset($_SESSION['vMon']) ) ? $_SESSION['vMon'] : date('m');
$txbYear = ( isset($_SESSION['vYear']) ) ? $_SESSION['vYear'] : date('Y');
$txVtype = ( isset($_SESSION['vType']) && !empty($_SESSION['vType']) ) ? $_SESSION['vType'] : '';

if($txbMon && $txbYear)
    $arr = array('LEFT(cheque_date,7)'=>$txbYear.'-'.$txbMon);
else if($txbMon)
    $arr = array('SUBSTRING(cheque_date,6,2)'=>$txbMon);
elseif($txbYear)
    $arr = array('LEFT(cheque_date,4)'=>$txbYear);

if($txPayee)
    $arr = array_merge($arr,array('supplierID'=>$txPayee));
if($txVtype)
    $arr = array_merge($arr,array('vt_id'=>$txVtype));
if(isset($_SESSION['voucher_list_print_query']))
    $qVoucher = $db->query($_SESSION['voucher_list_print_query']);
else
    $qVoucher = $db->select('voucher','*',$arr,'ORDER BY cheque_id,cheque_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Voucher Print</title>
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
    .padParLeft{padding-left:50px;}
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
            print_header('Voucher List Print');
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
                <table class="table" style="font-size:12px;" width="700">
                    <tr>
                        <th width="14%">Voucher #</th>
                        <th width="17%">Cheque #</th>
                        <th width="17%">Cheque Date</th>
                        <th width="37%">Supplier / Payee</th>
                        <th width="15%">Amount</th>
                    </tr>
                    <?php
                    $total=0;
                    while($rVouch = $db->fetch_array($qVoucher)):
                        //Getting surchages
                        $otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'surcharge'));

                        $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVouch['voucher_id']).'"');
                        $non_po = $db->result($amount_q);

                        $amount_non_po_q = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) AS total FROM voucher v,voucher_particular vp,po p,po_item pi WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=p.vp_id AND p.po_id=pi.po_id AND v.voucher_id="'.$db->clean($rVouch['voucher_id']).'"');
                        $po = $db->result($amount_non_po_q);
                        $amount = $non_po + $po + $otherSurcharge;

                        $approveStatus = $db->getValue('voucher','count(approved)',array('voucher_id'=>$rVouch['voucher_id']));
                        $style = ($approveStatus==0) ? '' : 'style="visibility:hidden;"';

                        $sel_w_tax = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$rVouch['voucher_id']));
                        $sel_w_tax_vat = $db->getValue('voucher','witholding_vat',array('voucher_id'=>$rVouch['voucher_id']));
                        $hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$rVouch['voucher_id']));
                        //if voucher has advance payment, it also computed the withholding tax.
                        if($hasAdvance){
                            $va = new voucherAdvance($rVouch['voucher_id']);
                            $amount = $va->current_voucher_payable_amount;
                        }
                        else{//Deduction of the withholding tax.
                            if($sel_w_tax_vat==1)
                                $current_w_tax = ($amount && $sel_w_tax) ? ($amount / 1.12) * ($sel_w_tax / 100) : 0;
                            else
                                $current_w_tax = ($amount && $sel_w_tax) ? ($amount) * ($sel_w_tax / 100) : 0;
                            $amount -= $current_w_tax;
                        }
                        
                        $otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'deduction'));
                        if($otherDeduction)
                            $amount -= $otherDeduction;
                        $total += $amount;
                    ?>
                    <tr>
                        <td><?php echo $rVouch['voucher_no'];?></td>
                        <td><?php echo $rVouch['cheque_id'];?></td>
                        <td><?php echo functions::datearr($rVouch['cheque_date']);?></td>
                        <td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rVouch['supplierID']));?></td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><strong><div align="right">Total Amount</div></strong></td>
                        <td><strong><?php echo functions::formatMoney($total);?></strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
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