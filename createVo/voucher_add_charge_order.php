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

function voucherHier($vid){
    global $db;
    $source=0;
    $arrVID_list=array();
    $parent = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$vid));
    if($parent){
        $grand = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$parent));
        if($grand)
            $arrVID_list = functions::insert_array(voucherHier($parent),$parent);
        else
            $arrVID_list = functions::insert_array($arrVID_list,$parent);
    }
    return $arrVID_list;
}

function getFirstVoucher($vid){
    global $db;
    $source=0;
    $parent = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$vid));
    if($parent){
        $grand = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$parent));
        if($grand)
            $source=getFirstVoucher($parent);
        else
            $source = $parent;
    }
    return $source;
}

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$approveStatus = $db->getValue('voucher','count(approved)',array('voucher_id'=>$vid));


$txVoucherPrev=$db->getValue('voucher_charge_order vco, voucher v','voucher_no',array('voucher_owner'=>$vid),'AND v.voucher_id=vco.voucher_top');
if( isset($_POST['btnVoucherPrevRemove']) ){
    $db->delete('voucher_charge_order',array('voucher_owner'=>$vid));
    functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid));
}

$order_msg='';
if( isset($_REQUEST['vs']) && !empty($_REQUEST['vs']) ){
    $voucher_selected = functions::decode($_REQUEST['vs']);

    $txVoucherPrev = $db->getValue('voucher','voucher_no',array('voucher_id'=>$voucher_selected));
    $selected_top_vid = functions::decode($_REQUEST['vs']);

    #getting the top parent of the selected voucher
    $parent_vid = getFirstVoucher($selected_top_vid);

    #for unchecking
    if( $db->getValue('voucher_charge_order','count(*)',array('voucher_owner'=>$vid,'voucher_top'=>$voucher_selected)) ){
        $db->delete('voucher_charge_order',array('voucher_owner'=>$vid,'voucher_top'=>$voucher_selected));
        #functions::say('removed');
        functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid));
        die();
    }#for checking another checkbox
    else if( $db->getValue('voucher_charge_order','count(*)',array('voucher_owner'=>$vid)) ){
        #functions::say('removed2');
        $db->delete('voucher_charge_order',array('voucher_owner'=>$vid));
    }

    if($selected_top_vid){
        #check if it already has an under (has been owned), if there is, then it cannot be added and it should display the voucher under it.
        $get_topvids_owner = $db->getValue('voucher_charge_order','voucher_owner',array('voucher_top'=>$selected_top_vid));

        #get the top of the selected voucher.
        $get_topvids_top = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$selected_top_vid));

        #if voucher id is referring to itself
        if($selected_top_vid==$vid){
            $order_msg='Voucher must not be itself!';
        }
        #if the parent top of selected voucher is equal to this voucher, so meaning it will create a loop, then don't add.
        else if($parent_vid == $vid){
            $order_msg='Cannot add voucher danger of looping';
        }
        #if there is top has been owned, display the voucher under it
        else if( $get_topvids_owner ){
            $order_msg = 'This voucher has been top of voucher: '.$db->getValue('voucher','voucher_no',array('voucher_id'=>$get_topvids_owner));
        }
        #if the selected voucher's top has no top and has not been under
        else if(empty($get_topvids_under) && empty($get_topvids_top)){
            $db->insert('voucher_charge_order',array('voucher_top'=>$selected_top_vid,'voucher_owner'=>$vid));
            #functions::say('Added!');
            functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid));
        }
        else if( !empty($get_topvids_top) ){
            #if there is top of the selected top voucher, get the parent of its top
            $parent_of_topvids_top = getFirstVoucher($get_topvids_top);
            if($parent_of_topvids_top != $vid){
                $db->insert('voucher_charge_order',array('voucher_top'=>$selected_top_vid,'voucher_owner'=>$vid));
                #functions::say('Added!');
                functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid));
            }
            else
                $order_msg='Cannot add voucher danger of looping.';
        }
    }
    else
        $order_msg='Voucher not Exist!';
    #functions::sendTo($_SERVER['PHP_SELF'].'?vid='.functions::encode($vid));
}


if( isset($_POST['btnVoucherPrev']) ){
    $txVoucherPrev = ( isset($_POST['txVoucherPrev']) ) ? trim($_POST['txVoucherPrev']) : '';
    #check if voucher exist
    $selected_top_vid = $db->getValue('voucher','voucher_id',array('voucher_no'=>$txVoucherPrev));
}
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Select Previous</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                Immediate Previous Voucher Number: <input type="text" name="txVoucherPrev" id="txVoucherPrev" value="<?php echo $txVoucherPrev?>" style="width:100px;" <?php if($approveStatus!=0){echo 'disabled';}?>><?php if($approveStatus==0){?>&nbsp;<input type="submit" name="btnVoucherPrev" id="btnVoucherPrev" value="Search"><?php }?>
                <div><strong style="color:red"><?php echo $order_msg?></strong></div><br>
                <table class="table table-striped table-bordered" id="vList">
                    <thead>
                        <tr>
                            <th width="5%">&nbsp;</th>
                            <th width="10%">Voucher No.</th>
                            <th width="15%">Date</th>
                            <th width="35%">Particulars</th>
                            <th width="20%">Deductions</th>
                            <th width="20%">Voucher Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $qList = $db->select('voucher','*',array('voucher_no'=>$txVoucherPrev));
                        $hasChecked = $db->getValue('voucher_charge_order','count(*)',array('voucher_owner'=>$vid));
                        while($r = $db->fetch_array($qList)):
                            $disabled='';
                            $checked = ( $db->getValue('voucher_charge_order','count(*)',array('voucher_owner'=>$vid,'voucher_top'=>$r['voucher_id'])) ) ? 'checked="checked"' : '';
                            if($hasChecked){
                                $disabled='disabled="disabled"';
                                if($checked)
                                    $disabled='';
                            }
                            $sel_w_tax = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$r['voucher_id']));
                            $sel_w_tax_vat = $db->getValue('voucher','witholding_vat',array('voucher_id'=>$r['voucher_id']));
                    ?>
                        <tr>
                            <td><div align="center"><?php if( ($r['voucher_id']==$vid) ){echo '--';}else{?><input type="checkbox" name="chkVN[]" id="chk<?php echo $r['voucher_id']?>" value="<?php echo functions::encode($r['voucher_id'])?>" onClick="vidSel(this.value)" <?php echo $checked?> <?php echo $disabled;?>><?php }?></div></td>
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
                            <td>
                                <?php
                                $total_adjustment=0;
                                $q = $db->select('voucher_deduction','*',array('voucher_id'=>$r['voucher_id']),'ORDER BY deduction_name');
                                while($r = $db->fetch_array($q)):
                                    echo $r['deduction_name'].' - '.functions::formatMoney($r['deduction_value']).'<br>';
                                endwhile;
                                ?>
                            </td>
                            <td><?php echo functions::formatMoney($amount_particular)?><?php echo ($r['witholding_tax']) ? ' <i>('.$r['witholding_tax'].'%)</i>' : '';?></td>
                        </tr>
                      <?php endwhile;?>
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