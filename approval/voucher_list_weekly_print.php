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

function getFullWeeksOfMonth($iYear, $iMonth, $sFirstDayOfWeek='Monday', $bExclusive = false) {

    $iYear = filter_var($iYear, FILTER_VALIDATE_INT, array('options' => array('default' => (int) date('Y')) ));
    #$iMonth = filter_var($iMonth, FILTER_VALIDATE_REGEXP, array('options' => array('default' => (int) date('m'),'regexp' => '/^([1-9]|1[012])$/') ));
    $aDay = array('monday'=>1, 'sunday'=>1);
    $sFirstDayOfWeek = filter_var($sFirstDayOfWeek , FILTER_VALIDATE_REGEXP, array(
        'options' => array(
            'default' => 'saturday',
            'regexp' => '/^monday|sunday$/'
        )
    ));
    $bExclusive =  filter_var($bExclusive,FILTER_VALIDATE_BOOLEAN);
    $oStart = new DateTime($iYear . '-' . $iMonth . '-01');

    if ($bExclusive === true || ($bExclusive === false && isset($aDay[strtolower($oStart->format('l'))]))) {
        if((int) $oStart->format('d') === 1){
            $oStart->modify('-1 day');
        }
        $oStart->modify('first ' . $sFirstDayOfWeek . ' ' . $oStart->format('H:i'));
    }
    else{
        $oStart->modify('last ' . $sFirstDayOfWeek . ' ' . $oStart->format('H:i'));
    }

    $oEnd = clone($oStart);
    if((int)$oStart->format('m')===$iMonth){
        $oEnd->modify('last day of this month');
    }else{
        $oEnd->modify('last day of next month');
    }

    $oInterval = new DateInterval('P1W7D');
    $oDaterange = new DatePeriod($oStart, $oInterval, $oEnd);

    $aDate = array();
    $i = 1;
    foreach ($oDaterange as $oDate) {
        $oTestDate = clone $oDate;
        $oLastWeekDay = $oTestDate->modify('+6 days');
        if (
                ((int) $oDate->format('m') === (int) $iMonth || (int) $oLastWeekDay->format('m') === (int) $iMonth) &&
                (($bExclusive === true && (int) $oLastWeekDay->format('m') === (int) $iMonth) ||
                ($bExclusive === false))
        ) {
            $aDate[$i]['First'] = $oDate->format('Y-m-d');
            $aDate[$i]['Last'] = $oLastWeekDay->format('Y-m-d');
        }
        $i++;
    }
    return $aDate;
}
$txbYear = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : '';
$txbMon = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$txVtype = ( isset($_REQUEST['vt']) && !empty($_REQUEST['vt']) ) ? functions::decode($_REQUEST['vt']) : '';
$monthName='';  
if($txbMon && $txbYear){
    $time = mktime(0,0,0,$txbMon,1,$txbYear);
    $monthName = date('F',$time) .' '.$txbYear;
}

if($txbMon && $txbYear)
    $arr = array('LEFT(cheque_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
    $arr = array('LEFT(cheque_date,4)'=>$txbYear);
if($txVtype)
    $arr = array_merge($arr,array('vt_id'=>$txVtype));

$qVTid = ($txVtype) ? ' AND vt_id="'.$db->clean($txVtype).'"' : '';
$qDate = $db->select('voucher','DISTINCT LEFT(cheque_date,7) as dte',$arr,'ORDER BY cheque_date');
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
    <script>window.print();</script>
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
                print_header('Voucher Weekly Payable <i>('.$voucherType.')');
                ?>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table width="100%" align="center" border="0" style="font-size: 12px">
                <?php
                $curMonth='';$totalCost=0;$monthlyCost=0;$cost=0;$rank=0;$monthly=0;$total=0;$count=0;$payable_amount=0;$weekly_payable=0;$monthly_payable=0;
                while($rDate = $db->fetch_array($qDate)):
                    $monthly=0;
                    $rank++;
                    $time = mktime(0,0,0,substr($rDate['dte'],5,2),1,$txbYear);
                    $monthName = date('F',$time);
                    $txbMon = date('m',$time);
                ?>
                    <tr>
                        <td height="30" colspan="2">
                            <br><br><strong><?php echo $monthName.' '.$txbYear;?></strong><br><br>
                            <?php $a = getFullWeeksOfMonth($txbYear, $txbMon, 'Monday');?>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Sat</th>
                                    <th>Sun</th>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Payment Amount</th>
                                </tr>
                            <?php foreach($a as $wk):  $weekly_payable=0;?>
                                <tr>
                            <?php
                                    for($i=0; $i<=6; $i++):
                                        $date_arr = explode('-',$wk['First']);
                                        $mn = ( isset($date_arr[1]) ) ? $date_arr[1] : date('m');
                                        $dy = ( isset($date_arr[2]) ) ? $date_arr[2] : date('d');
                                        $Yr = ( isset($date_arr[0]) ) ? $date_arr[0] : date('Y');
                                        $time = mktime(0,0,0,$mn,($dy + $i),$Yr);
                            ?>
                                    <td><?php echo date('M. d',$time)?></td>
                            <?php
                                    endfor;
                                    $qVoucher = $db->query('SELECT * FROM voucher WHERE cheque_date BETWEEN "'.$db->clean($wk['First']).'" AND "'.$db->clean($wk['Last']).'" '.$qVTid.' ORDER BY cheque_date');
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
                                        $monthly += $payable_amount;
                                        $weekly_payable += $payable_amount;
                                        $monthly_payable += $payable_amount;
                                    endwhile;
                            ?>
                                    <td width="20%"><?php echo functions::formatMoney($weekly_payable);?></td>
                                </tr>
                            <?php endforeach;?>
                                <tr>
                                    <td colspan="7"><div align="right"><strong>Monthly Total</strong></div></td>
                                    <td><strong><?php echo functions::formatMoney($monthly_payable);?></strong></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                <?php endwhile;#endwhile PODate?>
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
    window.location="voucher_list_weekly.php?y=<?php echo functions::encode($txbYear)?>&m=<?php echo functions::encode($txbMon)?>&vt=<?php echo functions::encode($txVtype)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>