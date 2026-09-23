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
$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$qvoucher = $db->select('voucher','*',array('voucher_id'=>$vid));
$rVoucher = $db->fetch_array($qvoucher);
$withholding = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$vid));
$withholding_percent = ($withholding) ? $withholding / 100 : 0;
$advance_payment = $db->getValue('voucher','advance_payment',array('voucher_id'=>$vid));
$hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$vid));
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
      print_header('Voucher');
      ?>
      </td>
    </tr>
    <tr>
      <td valign='bottom'>&nbsp;</td>
    </tr>
    <tr>
      <td>
          <table width="100%" border="0">
            <tr>
              <td width="67%">&nbsp;</td>
              <td align="left">Voucher #:<strong> <?php echo $rVoucher['voucher_no'];?></strong></td>
            </tr>
            <tr>
              <td>Payee: <strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])));?></strong></td>
              <td align="left">Date:<strong> <?php echo functions::datearr($rVoucher['vdate']);?></strong></td>
            </tr>
          </table>
      </td>
    </tr>
  </thead>
  <tbody>
  <tr>
    <td height="500" valign="top">
       <table width="100%" border="1">
          <tr>
            <td align="center"><strong>PARTICULARS</strong></td>
            <td align="center"><strong>AMOUNT</strong></td>
          </tr>
          <tr>
            <td align="center">&nbsp;</td>
            <td align="center">&nbsp;</td>
          </tr>
          <?php
          //test if po is fuel
          $t_vp_id = $db->getValue('voucher_particular','vp_id',array('voucher_id'=>$vid));
          $t_po_id = $db->getValue('voucher_po_payment','po_id',array('vp_id'=>$t_vp_id));
          $t_po_type =  $db->getValue('po','po_type',array('po_id'=>$t_po_id));
              $total_amount = 0;
              $amount = 0;
              $po_id=0;
              $vp_id=0;
              $qvp = $db->select('voucher_particular','*',array('voucher_id'=>$vid));
              while($rvp = $db->fetch_array($qvp)):
                    $vp_id=$rvp['vp_id'];
                    if($rvp['vtype'] == "po"){
                        $po_id = $db->getValue('po','po_id',array('vp_id'=>$rvp['vp_id']));
                        $amount = $db->getValue('voucher_po_payment','amount',array('vp_id'=>$rvp['vp_id']));
                    }
                    else
                        $amount = $db->getValue('voucher_detail','SUM(amount_issue)',array('vp_id'=>$rvp['vp_id']));
                $total_amount += $amount;
                if($t_po_type != "fuel"){
          ?>
            <tr>
              <td class="padParLeft" height="15" style="font-size:12px;"><?php echo $rvp['vp_title'];?></td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($amount);?></td>
            </tr>
          <?php
                }//if($t_po_type != "fuel"){
          endwhile;
              $taxable_amount = $total_amount;
              $current_w_tax = ($total_amount && $withholding) ? ($total_amount / 1.12) * ($withholding / 100) : 0;
              $payable_amount = $total_amount - $current_w_tax;
              if($t_po_type=='fuel'){
          ?>
            <tr>
              <td class="padParLeft" height="15" style="font-size:12px;">P.O. Payment</td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($total_amount);?></td>
            </tr>
        <?php
              }//if($t_po_type=='fuel'){
              if($hasAdvance){
                  $va = $a = new voucherAdvance($rVoucher['voucher_id']);
                  $taxable_amount = $va->taxable_amount;
                  $payable_amount = $va->current_voucher_payable_amount;
                  $current_w_tax = $va->current_voucher_withholding_tax_amount;
              }
               ?>
            <tr>
                <td><div align="right">Total Amount &nbsp;</div></td>
                <td class="padAmLeft"><strong><?php echo functions::formatMoney($total_amount);?></strong></td>
            </tr>
            <?php if($hasAdvance){?>
            <tr>
                <td class="padParLeft" style="font-size:12px;"><div align="right">Advance Payment <i>(Taxable)</i> &nbsp;</div></td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($va->voucher_amount_without_withholding_tax);?></td>
            </tr>
            <tr>
                <td class="padParLeft" style="font-size:12px;"><div align="right">Total Taxable Payment &nbsp;</div></td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($taxable_amount);?></td>
            </tr>
            <?php }?>
            <?php if($current_w_tax || $hasAdvance){?>
            <tr>
              <td class="padParLeft" height="15" style="font-size:12px;"><div align="right">Less: Withholding Tax &nbsp;</div></td>
                <td class="padAmLeft" style="font-size:12px;"><strong><?php echo functions::formatMoney($current_w_tax);?></strong><?php echo '&nbsp;&nbsp;(<i>'.$withholding.'%)</i>';?></td>
            </tr>
            <?php }?>
            <?php if($hasAdvance){?>
            <tr>
                <td class="padParLeft" style="font-size:12px;"><div align="right">Advance Payment's Withholding Tax &nbsp;</div></td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($va->total_advance_withholding_tax_amount);?></td>
            </tr>
            <tr>
                <td class="padParLeft" style="font-size:12px;"><div align="right">Total Advance Payment &nbsp;</div></td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($va->total_advance_voucher_amount);?></td>
            </tr>
            <tr>
                <td class="padParLeft" style="font-size:12px;"><div align="right">Overall Payment &nbsp;</div></td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($va->overall_payment);?></td>
            </tr>
            <tr>
                <td class="padParLeft" style="font-size:12px;"><div align="right">Overall Withholding Tax &nbsp;</div></td>
                <td class="padAmLeft" style="font-size:12px;"><?php echo functions::formatMoney($va->overall_withholding_tax_amount);?></td>
            </tr>
            <?php }?>
            <?php if($current_w_tax || $hasAdvance){?>
            <tr>
                <td height="20">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td><div align="right">Payable Amount &nbsp;</div></td>
                <td class="padAmLeft"><strong><?php echo functions::formatMoney($payable_amount);?></strong></td>
            </tr>
            <?php }?>
        </table>
    </td>
  </tr>
  <tr>
    <td height="40" align="left"><div>Amount: <strong><?php echo ucwords(strtolower(functions::number_to_words($payable_amount)));?> only</strong></div></td>
  </tr>
  <tr>
  	<td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
              <td colspan="2">&nbsp;</td>
           		<td colspan="2"><div>CHEQUE #: <strong><?php echo $rVoucher['cheque_id']?></strong></div></td>
           </tr>
           <tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2"><div>CHEQUE DATE: <strong><?php echo ($rVoucher['cheque_date']) ? functions::datearr($rVoucher['cheque_date']) : '';?></strong></div></td>
           </tr>
           <tr>
                <td colspan="2">Prepared By</td>
                <td width="7%">&nbsp;</td>
                <td width="32%">&nbsp;</td>
           </tr>
           <tr>
                <td width="6%" height="20">&nbsp;</td>
                <td width="55%"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",mname,". ",lname)',array('user_id'=>$rVoucher['preparedBy'])));?></strong></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
           </tr>
           <tr>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               </tr>
           <tr>
               <td colspan="2">Checked By</td>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               </tr>
           <tr>
               <td height="20">&nbsp;</td>
               <td><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",mname,". ",lname)',array('user_id'=>$rVoucher['checkedBy'])));?></strong></td>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               </tr>
           <tr>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
               <td>&nbsp;</td>
           </tr>
           <tr>
           		<td colspan="2">Approved By</td>
                <td colspan="2">Received By</td>
           </tr>
           <tr>
                <td height="20">&nbsp;</td>
                <td><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",mname,". ",lname)',array('user_id'=>$rVoucher['approvedBy'])));?></strong></td>
                <td>&nbsp;</td>
                <td><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",mname,". ",lname)',array('user_id'=>$rVoucher['receivedBy'])));?></strong></td>
           </tr>
           <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>Print Name and Signature</td>
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