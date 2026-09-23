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

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$supplierID = $db->getValue('voucher','supplierID',array('voucher_id'=>$vid));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Voucher Advance Payment</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
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
<div id="spinner"></div>
<?php
function getLastVoucher($vid){
    global $db;
    $source=0;
    $parent = $db->getValue('voucher_advance_payment','voucher_id_owner',array('voucher_advances'=>$vid));
    if($parent){
        $grand = $db->getValue('voucher_advance_payment','voucher_id_owner',array('voucher_advances'=>$parent));
        if($grand)
            $source = getLastVoucher($parent);
        else
            $source = $parent;
    }
    return $source;
}
$last_voucher_id = getLastVoucher($vid);
if( isset($_REQUEST['vs']) && !empty($_REQUEST['vs']) ){
    $voucher_selected = functions::decode($_REQUEST['vs']);
    if( $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$vid,'voucher_advances'=>$voucher_selected)) ){
        $db->delete('voucher_advance_payment',array('voucher_id_owner'=>$vid));
    }
    else{
        $db->delete('voucher_advance_payment',array('voucher_id_owner'=>$vid));
        $db->insert('voucher_advance_payment',array('voucher_id_owner'=>$vid,'voucher_advances'=>$voucher_selected));
    }
    functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid));
}
?>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Advance Payment</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <table class="table table-striped table-bordered bootstrap-datatable datatable" id="vList">
                    <thead>
                        <tr>
                            <th width="5%">&nbsp;</th>
                            <th width="10%">Voucher No.</th>
                            <th width="15%">Date</th>
                            <th width="55%">Description</th>
                            <th width="20%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $qSel = $db->select('voucher_advance_payment','*',array('voucher_id_owner'=>$vid));
                    $voucher_advance_id = '';
                    if( $db->num_rows($qSel) ){
                        $rSel = $db->fetch_array($qSel);
                        $voucher_advance_id = $rSel['voucher_advances'];
                        $sel_w_tax = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$rSel['voucher_advances']));
                        $sel_w_tax_vat = $db->getValue('voucher','witholding_vat',array('voucher_id'=>$rSel['voucher_advances']));
                        $v_date = $db->getValue('voucher','vdate',array('voucher_id'=>$rSel['voucher_advances']));
                    ?>
                        <tr>
                            <td><div align="center"><input type="checkbox" name="chkVN[]" id="chk<?php echo $rSel['voucher_advances']?>" value="<?php echo functions::encode($rSel['voucher_advances'])?>" onClick="vidSel(this.value)" checked="checked"></div></td>
                            <td><?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$rSel['voucher_advances']))?></td>
                            <td><?php echo functions::datearr($v_date)?></td>
                            <td>
                                <?php
                                $amount_particular=0;
                                $qParticulars = $db->select('voucher_particular','*',array('voucher_id'=>$rSel['voucher_advances']));
                                while($rPar = $db->fetch_array($qParticulars)):
                                    echo $rPar['vp_title'].'<br>';
                                endwhile;
                                //Getting the amount of particulars inside the voucher
                                $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rSel['voucher_advances']).'"');
                                $non_po = $db->result($amount_q);
                                
                                $amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rSel['voucher_advances']).'"');
                                $po_amount = $db->result($amount_po_q);
                                $amount_particular += $non_po + $po_amount;
                                //Getting the amount of particulars inside the voucher


                                //if voucher has advance payment, it also computed the withholding tax.
                                $hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$voucher_advance_id));
                                if($hasAdvance){
                                    $va = new voucherAdvance($voucher_advance_id);
                                    $amount_particular = $va->current_voucher_payable_amount;
                                }
                                else{//Deduction of the withholding tax.
                                    if($sel_w_tax_vat==1)
                                        $current_w_tax = ($amount_particular && $sel_w_tax) ? ($amount_particular / 1.12) * ($sel_w_tax / 100) : 0;
                                    else
                                        $current_w_tax = ($amount_particular && $sel_w_tax) ? ($amount_particular) * ($sel_w_tax / 100) : 0;
                                    $amount_particular -= $current_w_tax;
                                }
                                $otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$voucher_advance_id));
                                if($otherDeduction)
                                    $amount_particular -= $otherDeduction;
                                ?>
                            </td>
                            <td><?php echo functions::formatMoney($amount_particular)?><?php echo ($sel_w_tax) ? ' <i>('.$sel_w_tax.'%)</i>' : '';?></td>
                        </tr>
                    <?php }
                        $qList = $db->select('voucher','*',array('supplierID'=>$supplierID),'AND voucher_id <> "'.$db->clean($vid).'" ORDER BY vdate DESC');
                        $hasChecked = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$vid));
                        while($r = $db->fetch_array($qList)):
                            $disabled='';
                            $isOwned = ( $db->getValue('voucher_advance_payment','count(*)',array('voucher_advances'=>$r['voucher_id']),'AND voucher_id_owner!="'.$vid.'"') ) ? 1 : 0;
                            $checked = ( $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$vid,'voucher_advances'=>$r['voucher_id'])) ) ? 'checked="checked"' : '';
                            if($hasChecked){
                                $disabled='disabled="disabled"';
                                if($checked)
                                    $disabled='';
                            }
                            $sel_w_tax = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$r['voucher_id']));
                            $sel_w_tax_vat = $db->getValue('voucher','witholding_vat',array('voucher_id'=>$r['voucher_id']));
                            if($voucher_advance_id != $r['voucher_id']){
                    ?>
                        <tr>
                            <td><div align="center"><?php if($isOwned || ($last_voucher_id==$r['voucher_id']) ){echo '--';}else{?><input type="checkbox" name="chkVN[]" id="chk<?php echo $r['voucher_id']?>" value="<?php echo functions::encode($r['voucher_id'])?>" onClick="vidSel(this.value)" <?php echo $checked?> <?php echo $disabled;?>><?php }?></div></td>
                            <td><?php echo $r['voucher_no']?></td>
                            <td><?php echo functions::datearr($r['vdate'])?></td>
                            <td>
                                <?php
                                $amount_particular=0;
                                $qParticulars = $db->select('voucher_particular','*',array('voucher_id'=>$r['voucher_id']));
                                while($rPar = $db->fetch_array($qParticulars)):
                                    echo $rPar['vp_title'].'<br>';
                                endwhile;

                                //Getting the amount of particulars inside the voucher
                                $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($r['voucher_id']).'"');
                                $non_po = $db->result($amount_q);
                                
                                $amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($r['voucher_id']).'"');
                                $po_amount = $db->result($amount_po_q);
                                $amount_particular += $non_po + $po_amount;
                                //Getting the amount of particulars inside the voucher

                                //if voucher has advance payment, it also computed the withholding tax.
                                $hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$r['voucher_id']));
                                if($hasAdvance){
                                    $va = new voucherAdvance($r['voucher_id']);
                                    $amount_particular = $va->current_voucher_payable_amount;
                                }
                                else{//Deduction of the withholding tax.
                                    if($sel_w_tax_vat==1)
                                        $current_w_tax = ($amount_particular && $sel_w_tax) ? ($amount_particular / 1.12) * ($sel_w_tax / 100) : 0;
                                    else
                                        $current_w_tax = ($amount_particular && $sel_w_tax) ? ($amount_particular) * ($sel_w_tax / 100) : 0;
                                    $amount_particular -= $current_w_tax;
                                }
                                $otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$r['voucher_id']));
                                if($otherDeduction)
                                    $amount_particular -= $otherDeduction;
                                ?>
                            </td>
                            <td><?php echo functions::formatMoney($amount_particular)?><?php echo ($r['witholding_tax']) ? ' <i>('.$r['witholding_tax'].'%)</i>' : '';?></td>
                        </tr>
                      <?php }
                        endwhile;?>
                    </tbody>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
function vidSel(v){
    window.location="<?php echo $_SERVER['PHP_SELF']?>?vid=<?php echo functions::encode($vid);?>&vs="+v;
}
</script>
<!-- end: JavaScript-->
</body>
</html>