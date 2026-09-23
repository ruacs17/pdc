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
$arr = array();
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');

$txbYear = (isset($_REQUEST['bdYear']) && !empty($_REQUEST['bdYear']) ) ? $_REQUEST['bdYear'] : date('Y');
$txbMon = (isset($_REQUEST['bdMon']) && !empty($_REQUEST['bdMon']) ) ? $_REQUEST['bdMon'] : 0;

if(isset($_POST['btnSearch'])){
    $txbYear = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : date('Y');
    $txbMon = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
}

if($txbMon && $txbYear)
     $arr = array('LEFT(cheque_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
    $arr = array('LEFT(cheque_date,4)'=>$txbYear);
$qDate = $db->select('voucher','DISTINCT LEFT(cheque_date,7) as dte',$arr,'ORDER BY cheque_date');
$count=0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Professional Fee</title>
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
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Professional Fee</h2>
        </div>
        <div class="box-content" align="center">
            <table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
                <tr>
                    <td width="50%"><div align="right"><a id="whprint" class="btn btn-info" href="dash-prof-fee-print.php?y=<?php echo functions::encode($txbYear)?>&m=<?php echo functions::encode($txbMon)?>"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div></td>
                </tr>
            </table>
            <form method="post">
                <table width="26%" border="0">
                    <tr>
                        <td align="left" style="padding: 12px 0px 4px 0px">
                            <select name="bdYear" id="bdYear" style="width:90px;">
                                <option value="">Select Year</option>
                                <?php
                                $qYr = $db->select('voucher','DISTINCT LEFT(cheque_date,4) as yr',array(),'WHERE cheque_date IS NOT NULL ORDER BY cheque_date DESC');
                                while($rYr = $db->fetch_array($qYr)):
                                ?>
                                <option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                <?php endwhile;?>
                            </select>
                            <select name="bdMon" id="bdMon" style="width:95px;">
                                <option value="">All Month</option>
                                <option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
                            </select>
                        </td>
                        <td valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary"></td>
                    </tr>
                </table><br><br><br>
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
                        $monthName = date('F',$time);
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
                        <td height="30"><?php echo '<strong>'.$db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])).'</strong>';?></td>
                        <td><a id="costdetail<?php echo $count?>" class="label label-info thickbox" title="View Professional Fee Details" data-rel="tooltip" onclick="showThis(this.id,'dash-prof-fee-detail.php?yr=<?php echo functions::encode($txbYear)?>&mn=<?php echo functions::encode($txbMon)?>&sup=<?php echo functions::encode($rVoucher['supplierID'])?>','Professional Fee Details','1')"><?php echo functions::formatMoney($prof_fee);?></a></td>
                    </tr>
                    <?php endwhile;#endwhile $rVoucher?>
                    <tr>
                      <td>&nbsp;</td>
                      <td height="30"><div align='right'><strong>Monthly Professional Fee of <?php echo $monthName.' '.$txbYear;?></strong></div></td>
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
            </form>
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
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>