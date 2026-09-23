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

$arr = array();
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');

$txbYear = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : '';
$txbMon = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
if($txbMon && $txbYear)
    $arr = array('LEFT(cheque_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
    $arr = array('LEFT(cheque_date,4)'=>$txbYear);
$qDate = $db->select('voucher','DISTINCT LEFT(cheque_date,7) as dte',$arr,'ORDER BY cheque_date');
$count=0;

$monthName='';  
if($txbMon && $txbYear){
    $time = mktime(0,0,0,$txbMon,1,$txbYear);
    $monthName = date('F',$time) .' '.$txbYear;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Professional Fee Print</title>
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
            print_header($monthName.' Professional Fee');
            ?><br>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table width="50%" align="center" border="1" class="table table-bordered table-hover">
                    <tr>
                        <td width="15%"><strong>Month</strong></td>
                        <td width="40%" height="30"><strong>Supplier / Payee</strong></td>
                        <td width="15%"><strong>Amount</strong></td>
                    </tr>
                    <?php
                    $curMonth='';$totalCost=0;$monthlyCost=0;$cost=0;$rank=0;$monthly=0;$total=0;
                    while($rDate = $db->fetch_array($qDate)):
                        $monthly=0;
                        $rank++;
                        $time = mktime(0,0,0,substr($rDate['dte'],5,2),1,$txbYear);
                        $monthName = date('M',$time);
                        $txbMon = date('m',$time);
                    ?>
                    <tr>
                        <td height="30"><strong><?php echo $monthName.' '.$txbYear;?></strong></td>
                        <td></td>
                        <td></td>
                    </tr>
                        <?php
                        $qVoucher = $db->select('voucher','supplierID,sum(prof_fee) as wt',array('LEFT(cheque_date,7)'=>$rDate['dte']),'GROUP BY supplierID HAVING wt > 0');
                        $prof_fee=0;
                        while($rVoucher = $db->fetch_array($qVoucher)):
                            $bySupplier=0;
                            $qPFCompute = $db->select('voucher','*',array('LEFT(cheque_date,7)'=>$rDate['dte'],'supplierID'=>$rVoucher['supplierID']));
                            while($rTC = $db->fetch_array($qPFCompute)):
                                $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rTC['voucher_id']).'"');
                                $non_po = $db->result($amount_q);

                                $amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rTC['voucher_id']).'"');
                                $po = $db->result($amount_po_q);
                                $amount = $non_po + $po;

                                $pf_percent = ($rTC['prof_fee'] > 0) ? ($rTC['prof_fee'] / 100) : 0;
                                $prof_fee = $amount * $pf_percent;
                                $bySupplier += $prof_fee;
                            endwhile;
                            $prof_fee = $bySupplier;
                            $monthly += $prof_fee;
                            $total += $prof_fee;
                            $count++;
                        ?>  
                    <tr>
                        <td>&nbsp;</td>
                        <td height="30"><?php echo $db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID']));?></td>
                        <td><?php echo functions::formatMoney($prof_fee);?></td>
                    </tr>
                    <?php endwhile;#endwhile $rVoucher?>
                    <tr>
                        <td>&nbsp;</td>
                        <td height="30"><div align='right'><strong>Monthly Professional Fee of <?php echo date('F',$time).' '.$txbYear;?></strong></div></td>
                        <td><strong><?php echo functions::formatMoney($monthly);?></strong></td>
                    </tr>  
                    <?php
                    $monthlyCost=0;
                    endwhile;#endwhile PODate?>
                    <tr>
                        <td height="30"></td>
                        <td><div align="right"><strong>Yearly Professional Fee</strong></div></td>
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
<script>
$(document).ready(function(){
    window.print();
    setTimeout("closePrint()",200);
});
function closePrint(){
    window.location="dash-prof-fee.php?y=<?php echo functions::encode($txbYear)?>&m=<?php echo functions::encode($txbMon)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>