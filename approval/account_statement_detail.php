<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/voucher_advance.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$with_advance = (isset($_REQUEST['wad']) && !empty($_REQUEST['wad']) ) ? $_REQUEST['wad'] : 0;
$wtax = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$vid));
$wtax_vat = $db->getValue('voucher','witholding_vat',array('voucher_id'=>$vid));
$wtaxPercent=$wtax;
$advance_payment = $db->getValue('voucher','advance_payment',array('voucher_id'=>$vid));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Account Statement Detail</title>
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
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Detail</h2>
		</div>
		<div class="box-content" align="center">
			<div style="width:65%;">
			<table class="table table-hover table-striped table-bordered" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC">
						<td width="60%"><strong>PARTICULARS</strong></td>
						<td width="20%"><div align="center"><strong>AMOUNT</strong></div></td>
					</tr>
				</thead>
				<tbody>
					<?php
					$total_amount = 0;$amount = 0;$po_id=0;$vp_id = 0;
					$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'surcharge'));
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
					?>
					<tr>
						<td><?php echo $rvp['vp_title'];?></td>
						<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
					</tr>
					<?php 
					endwhile;
					$qSC = $db->select('voucher_deduction','*',array('voucher_id'=>$vid,'charge_type'=>'surcharge'));
					while($rSC = $db->fetch_array($qSC)):
						$total_amount += $rSC['deduction_value'];  
					?>
					<tr>
						<td class="padParLeft" height="15" style="font-size:12px;"><?php echo $rSC['deduction_name'];?></td>
						<td class="padAmLeft" style="font-size:12px;"><div align="right"><?php echo functions::formatMoney($rSC['deduction_value']);?></div></td>
					</tr>
					<?php endwhile;
					$wtax = ($wtax > 0) ? ($wtax / 100) : 0;
					$withholding_amount = ($wtax_vat==1) ? $wtax * ($total_amount / 1.12) : $wtax * $total_amount;
					$payable_amount = $total_amount - $withholding_amount;

					if($with_advance){
						$va = new voucherAdvance($vid);
						$withholding_amount = $va->current_voucher_withholding_tax_amount;
						$payable_amount = $va->current_voucher_payable_amount;
					}
					$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'deduction'));
					if($otherDeduction)
						$payable_amount -= $otherDeduction;
					?>
					<tr>
						<td><div align="right">Total Amount</div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($total_amount);?></strong></div></td>
					</tr>
					<?php if($otherDeduction){?>
					<tr>
						<td><div align="right">Other Deductions</div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($otherDeduction);?></strong></div></td>
					</tr>
					<?php }?>
					<?php if($with_advance){?>
					<tr>
						<td><div align="right">Advance Payment</div></td>
						<td><div align="right"><?php echo '<i>'.functions::formatMoney($va->total_advance_voucher_amount).'</i>';?></div></td>
					</tr>
					<tr>
						<td><div align="right">Total Taxable Amount</div></td>
						<td><div align="right"><?php echo '<i>'.functions::formatMoney($va->taxable_amount).'</i>';?></div></td>
					</tr>
					<tr>
						<td><div align="right">Total Payment</div></td>
						<td><div align="right"><?php echo '<strong>'.functions::formatMoney($va->overall_payment).'</strong>';?></div></td>
					</tr>
					<?php }?>
					<?php if($withholding_amount){?>
					<tr>
						<td><div align="right">Less: <i>(<?php echo $wtaxPercent;?>%)</i>&nbsp;&nbsp;Withholding Tax</div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($withholding_amount);?></strong></div></td>
					</tr>
					<tr>
						<td><div align="right">Payable Amount</div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($payable_amount);?></strong></div></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
			</div>
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
<!-- end: JavaScript-->
</body>
</html>