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
$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$qvoucher = $db->select('voucher','*',array('voucher_id'=>$vid));
$rVoucher = $db->fetch_array($qvoucher);
$withholding = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$vid));
$withholding_vat = $db->getValue('voucher','witholding_vat',array('voucher_id'=>$vid));
$advance_payment = $db->getValue('voucher','advance_payment',array('voucher_id'=>$vid));
$hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$vid));
$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'deduction'));
$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'surcharge'));


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

$arrVHier = (voucherHier($vid));
$arrCharges=array();
foreach($arrVHier as $vded):
	$qvd = $db->select('voucher_deduction','*',array('voucher_id'=>$vded));
	while($rvd = $db->fetch_array($qvd)):
		$d_name = $rvd['deduction_name'];
		$d_value = $rvd['deduction_value'];
		if( isset($arrCharges[$d_name]) )
			$arrCharges[$d_name] += $d_value;
		else
			$arrCharges[$d_name] = $d_value;
	endwhile;
endforeach;
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
	<link href="../css/printerfoot.css" rel="stylesheet">
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
	.padRight{padding-right:40px;}
	</style>
	<script>//window.print();</script>
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
			<td height="400" valign="top">
				<table width="100%" border="1">
					<tr>
						<td align="center" width="70%"><strong>PARTICULARS</strong></td>
						<td><div align="right" class="padRight"><strong>AMOUNT</strong></div></td>
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
					$total_amount = 0;$amount = 0;$po_id=0;$vp_id=0;
					$total_amount += $otherSurcharge;
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
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($amount);?></div></td>
					</tr>
					<?php
						}//if($t_po_type != "fuel"){
					endwhile;
						$taxable_amount = $total_amount;
						if($withholding_vat==1)
							$current_w_tax = ($total_amount && $withholding) ? ($total_amount / 1.12) * ($withholding / 100) : 0;
						else
							$current_w_tax = ($total_amount && $withholding) ? $total_amount * ($withholding / 100) : 0;
						$payable_amount = $total_amount - $current_w_tax;
						if($t_po_type=='fuel'){
					?>
					<tr>
						<td class="padParLeft" height="15" style="font-size:12px;">P.O. Payment</td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right"><?php echo functions::formatMoney($total_amount);?></div></td>
					</tr>
					<?php
						}//if($t_po_type=='fuel'){
							if($hasAdvance){
							$va = $a = new voucherAdvance($rVoucher['voucher_id']);
							$taxable_amount = $va->taxable_amount;
							$payable_amount = $va->current_voucher_payable_amount;
							$current_w_tax = $va->current_voucher_withholding_tax_amount;//It will override the wtax of the upper declaration.
						}
						if($otherDeduction)
							$payable_amount -= $otherDeduction;
					?>
					<?php 
					$qSC = $db->select('voucher_deduction','*',array('voucher_id'=>$vid,'charge_type'=>'surcharge'));
					while($rSC = $db->fetch_array($qSC)):
					?>
					<tr>
						<td class="padParLeft" height="15" style="font-size:12px;"><?php echo $rSC['deduction_name'];?></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($rSC['deduction_value']);?></div></td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td><div align="right">Total Amount &nbsp;</div></td>
						<td class="padAmLeft"><div align="right" class="padRight"><strong><?php echo $pa = functions::formatMoney($total_amount);?></strong></div></td>
					</tr>
					<?php if($otherDeduction){?>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right">Other Deductions &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><?php #echo functions::formatMoney($otherDeduction);?></td>
					</tr>
						<?php
						$otherDeductionQ  = $db->select('voucher_deduction','*',array('voucher_id'=>$vid,'charge_type'=>'deduction'));
						while($rOD = $db->fetch_array($otherDeductionQ)):
						?>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right"><i><?php echo $rOD['deduction_name'] ?></i> &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><i><?php echo functions::formatMoney($rOD['deduction_value']);?></i><?php echo isset($arrCharges[$rOD['deduction_name']]) ? '&nbsp;&nbsp;(cum. bal. '.functions::formatMoney($rOD['deduction_value'] + $arrCharges[$rOD['deduction_name']]).')' : '';?></div></td>
					</tr>
						<?php endwhile; ?>
					<?php }?>
					<?php if($hasAdvance){?>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right">Advance Payment <i>(Taxable)</i> &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($va->voucher_amount_without_withholding_tax);?></div></td>
					</tr>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right">Total Taxable Payment &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($taxable_amount);?></div></td>
					</tr>
					<?php }?>
					<?php if($current_w_tax || $hasAdvance){?>
					<tr>
						<td class="padParLeft" height="15" style="font-size:12px;"><div align="right">Less: <?php echo '<strong>(<i>'.$withholding.'%)</strong></i>&nbsp;&nbsp;&nbsp;&nbsp;';?>Withholding Tax &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><strong><?php echo functions::formatMoney($current_w_tax);?></strong></div></td>
					</tr>
					<?php }?>
					<?php if($hasAdvance){?>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right">Advance Payment's Withholding Tax &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($va->total_advance_withholding_tax_amount);?></div></td>
					</tr>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right">Total Advance Payment &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($va->total_advance_voucher_amount);?></div></td>
					</tr>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right">Overall Payment &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($va->overall_payment);?></div></td>
					</tr>
					<tr>
						<td class="padParLeft" style="font-size:12px;"><div align="right">Overall Withholding Tax &nbsp;</div></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right" class="padRight"><?php echo functions::formatMoney($va->overall_withholding_tax_amount);?></div></td>
					</tr>
					<?php }?>
					<?php if($current_w_tax || $hasAdvance || $otherDeduction ){?>
					<tr>
						<td height="20">&nbsp;</td>
					<td>&nbsp;</td>
					</tr>
					<tr>
						<td><div align="right">Payable Amount &nbsp;</div></td>
						<td class="padAmLeft"><div align="right" class="padRight"><strong><?php echo $pa = functions::formatMoney($payable_amount);?></strong></td>
					</tr>
					<?php }?>
				</table>
			</td>
		</tr>
		<?php $mtw = functions::moneyToDouble($pa); ?>
		<tr>
			<td height="40" align="left">
				<div style="display:none;">Amount: <strong><?php $ta = new MoneytoWords($payable_amount); echo $ta->words;?> Only</strong></div>
				<div>Amount: <strong><?php echo ucwords(strtolower(functions::number_to_words($mtw)))?> Pesos Only</strong></div>
			</td>
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
				<tr>
					<td height="44">Remarks:</td>
					<td>&nbsp;&nbsp;&nbsp;<strong><?php echo $rVoucher['remarks'];?></strong></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<footer>
	<div align="right">18AFD.FRM006.01-02/23</div>
</footer>
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