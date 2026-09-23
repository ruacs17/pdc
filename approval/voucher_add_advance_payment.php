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
    <style>
        body { background-color: #f0f2f5; padding: 25px 15px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; }
        
        /* Contrained clean layout width */
        .page-wrapper { max-width: 1050px; margin: 0 auto; }
        
        .box { border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #dcdcdc; background: #fff; }
        .box-header { background: #004a99 !important; color: white !important; border-top-left-radius: 5px; border-top-right-radius: 5px; padding: 12px 15px; }
        .box-header h2 { color: white !important; font-size: 16px; margin: 0; line-height: 20px; }
        
        /* Card-Style Table Layout (Compatible with DataTables search/pagination) */
        table.voucher-card-table { width: 100% !important; border-collapse: separate !important; border-spacing: 0 10px !important; margin-top: 5px; }
        
        /* Unified Header Bar Styling (No Inner Vertical Lines) */
        table.voucher-card-table thead th { 
            background: #f4f6f9 !important; 
            color: #555; 
            font-size: 11px; 
            text-transform: uppercase; 
            font-weight: 700; 
            padding: 10px 15px; 
            border-top: 1px solid #e1e4e8 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            border-left: none !important;
            border-right: none !important;
            letter-spacing: 0.5px; 
        }
        table.voucher-card-table thead th:first-child { 
            border-left: 1px solid #e1e4e8 !important; 
            border-top-left-radius: 4px; 
            border-bottom-left-radius: 4px; 
        }
        table.voucher-card-table thead th:last-child { 
            border-right: 1px solid #e1e4e8 !important; 
            border-top-right-radius: 4px; 
            border-bottom-right-radius: 4px; 
        }
        
        /* Individual Card Row */
        table.voucher-card-table tr.voucher-card-row { background: #ffffff; transition: all 0.2s ease-in-out; cursor: pointer; }
        table.voucher-card-table tr.voucher-card-row td { padding: 14px 15px; vertical-align: middle; border-top: 1px solid #d8dde6; border-bottom: 1px solid #d8dde6; border-left: none; border-right: none; }
        table.voucher-card-table tr.voucher-card-row td:first-child { border-left: 1px solid #d8dde6; border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
        table.voucher-card-table tr.voucher-card-row td:last-child { border-right: 1px solid #d8dde6; border-top-right-radius: 6px; border-bottom-right-radius: 6px; }
        
        table.voucher-card-table tr.voucher-card-row:hover td { border-color: #004a99; background-color: #fafbfc; }
        
        /* Selected State (Unified Box Look without Inner Vertical Lines) */
        table.voucher-card-table tr.voucher-card-row.selected td { 
            background-color: #f0f6fc !important; 
            border-top: 2px solid #004a99 !important; 
            border-bottom: 2px solid #004a99 !important; 
            border-left: none !important;
            border-right: none !important;
            font-weight: 600; 
        }
        table.voucher-card-table tr.voucher-card-row.selected td:first-child { 
            border-left: 2px solid #004a99 !important; 
            border-top-left-radius: 6px; 
            border-bottom-left-radius: 6px; 
        }
        table.voucher-card-table tr.voucher-card-row.selected td:last-child { 
            border-right: 2px solid #004a99 !important; 
            border-top-right-radius: 6px; 
            border-bottom-right-radius: 6px; 
        }
        
        /* Disabled State */
        table.voucher-card-table tr.voucher-card-row.disabled-card td { background-color: #f8f9fa !important; border-color: #e9ecef !important; opacity: 0.55; cursor: not-allowed; color: #888; }
        
        /* Component Specifics */
        .vc-no { font-weight: 700; color: #004a99; font-size: 13px; display: block; }
        .vc-date { font-size: 11px; color: #666; margin-top: 2px; display: block; }
        .vc-amount { font-weight: 700; color: #24292e; font-size: 14px; }
        .vc-tax-badge { display: inline-block; background: #e1e4e8; color: #444; font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: 600; margin-top: 3px; }
        .vc-tax-badge.active-tax { background: #dbedff; color: #004a99; }
    </style>
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
<div class="page-wrapper">
    <div class="row-fluid">
        <div class="box span12" style="margin-left:0;">
            <div class="box-header" data-original-title>
                <h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Advance Payment Selection</h2>
            </div>
            <div class="box-content" style="background: #ffffff; padding: 20px;">
                
                <div class="alert alert-info" style="margin-bottom: 20px; border-left: 4px solid #004a99; background: #f4f8fb; color: #003366;">
                    <i class="halflings-icon info-sign"></i> Select an eligible previous voucher.
                </div>

                <form class="form-horizontal" method="post">
                    <table class="table voucher-card-table datatable">
                        <thead>
                            <tr>
                                <th style="text-align: center;" width="5%">Select</th>
                                <th width="20%">Voucher No. & Date</th>
                                <th>Description / Particulars</th>
                                <th style="text-align: right;" width="20%">Amount</th>
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
                            <tr class="voucher-card-row selected" onclick="vidSel('<?php echo functions::encode($rSel['voucher_advances']);?>')">
                                <td style="text-align: center;">
                                    <input type="checkbox" name="chkVN[]" id="chk<?php echo $rSel['voucher_advances']?>" value="<?php echo functions::encode($rSel['voucher_advances'])?>" onClick="event.stopPropagation(); vidSel(this.value)" checked="checked">
                                </td>
                                <td>
                                    <span class="vc-no"><?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$rSel['voucher_advances']))?></span>
                                    <span class="vc-date"><i class="halflings-icon calendar" style="font-size: 10px;"></i> <?php echo functions::datearr($v_date)?></span>
                                </td>
                                <td>
                                    <?php
                                    $amount_particular=0;
                                    $qParticulars = $db->select('voucher_particular','*',array('voucher_id'=>$rSel['voucher_advances']));
                                    while($rPar = $db->fetch_array($qParticulars)):
                                        echo '<div style="margin-bottom: 2px;"><i class="halflings-icon tag" style="font-size: 10px; opacity: 0.5;"></i> '.$rPar['vp_title'].'</div>';
                                    endwhile;
                                    //Getting the amount of particulars inside the voucher
                                    $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rSel['voucher_advances']).'"');
                                    $non_po = $db->result($amount_q);
                                    
                                    $amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rSel['voucher_advances']).'"');
                                    $po_amount = $db->result($amount_po_q);
                                    $amount_particular += $non_po + $po_amount;
                                    
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
                                <td style="text-align: right;">
                                    <div class="vc-amount"><?php echo functions::formatMoney($amount_particular)?></div>
                                    <?php echo ($sel_w_tax) ? '<span class="vc-tax-badge active-tax">'.$sel_w_tax.'% Tax</span>' : '';?>
                                </td>
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
                                    $isDisabledCard = ($isOwned || ($last_voucher_id==$r['voucher_id']) || ($hasChecked && empty($checked))) ? true : false;
                        ?>
                            <tr class="voucher-card-row <?php echo $isDisabledCard ? 'disabled-card' : ''; ?>" <?php echo !$isDisabledCard ? 'onclick="vidSel(\''.functions::encode($r['voucher_id']).'\')"' : ''; ?>>
                                <td style="text-align: center;">
                                    <?php if($isOwned || ($last_voucher_id==$r['voucher_id']) ){echo '<span class="muted" style="font-weight: bold;">—</span>';}else{?>
                                        <input type="checkbox" name="chkVN[]" id="chk<?php echo $r['voucher_id']?>" value="<?php echo functions::encode($r['voucher_id'])?>" onClick="event.stopPropagation(); vidSel(this.value)" <?php echo $checked?> <?php echo $disabled;?>>
                                    <?php }?>
                                </td>
                                <td>
                                    <span class="vc-no"><?php echo $r['voucher_no']?></span>
                                    <span class="vc-date"><i class="halflings-icon calendar" style="font-size: 10px;"></i> <?php echo functions::datearr($r['vdate'])?></span>
                                </td>
                                <td>
                                    <?php
                                    $amount_particular=0;
                                    $qParticulars = $db->select('voucher_particular','*',array('voucher_id'=>$r['voucher_id']));
                                    while($rPar = $db->fetch_array($qParticulars)):
                                        echo '<div style="margin-bottom: 2px;"><i class="halflings-icon tag" style="font-size: 10px; opacity: 0.3;"></i> '.$rPar['vp_title'].'</div>';
                                    endwhile;

                                    //Getting the amount of particulars inside the voucher
                                    $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($r['voucher_id']).'"');
                                    $non_po = $db->result($amount_q);
                                    
                                    $amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($r['voucher_id']).'"');
                                    $po_amount = $db->result($amount_po_q);
                                    $amount_particular += $non_po + $po_amount;

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
                                <td style="text-align: right;">
                                    <div class="vc-amount"><?php echo functions::formatMoney($amount_particular)?></div>
                                    <?php echo ($r['witholding_tax']) ? '<span class="vc-tax-badge">'.$r['witholding_tax'].'% Tax</span>' : '';?>
                                </td>
                            </tr>
                          <?php }
                            endwhile;?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div><!--/span-->
    </div><!--/row-->
</div>
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