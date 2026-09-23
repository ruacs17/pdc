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
$txPayee = '';$txbMon = '';$txbYear = '';$txVtype = '';

$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? $db->clean(functions::decode($_REQUEST['yr'])) : 0;
$mn = (isset($_REQUEST['mn']) && !empty($_REQUEST['mn']) ) ? $db->clean(functions::decode($_REQUEST['mn'])) : 0;
$supplierID = (isset($_REQUEST['sup']) && !empty($_REQUEST['sup']) ) ? functions::decode($_REQUEST['sup']) : 0;

$arr = array();
if($yr && $mn)
	$arr = array('LEFT(vdate,7)'=>$yr.'-'.$mn);
elseif($yr)
	$arr = array('LEFT(vdate,4)'=>$yr);

$arr = array_merge($arr,array('supplierID'=>$supplierID));
$qVoucher = $db->select('voucher','*',$arr,'HAVING witholding_tax > 0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Witholding Tax</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Witholding Tax</h2>
		</div><br><br>
		<div>Supplier: <strong><?php echo $db->getValue('supplier','name',array('supplierID'=>$supplierID));?></strong></div><br>
		<div class="box-content" align="center">
			<table class="table table-bordered table-striped table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="15%">Voucher #</th>
						<th width="15%">Cheque #</th>
						<th width="10%">Voucher Date</th>
						<th width="10%"><div align="right">Amount</div></th>
						<th width="10%"><div align="right">Withholding Tax</div></th>
						<th width="10%"><div align="right">Payable Amount</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$total=0;$wtax=0;$totalWitholding=0;$totalPayableAmount=0;$voucher_advance_payment=0;$totalVoucherAmount=0;
				while($rVouch = $db->fetch_array($qVoucher)):
					$payableAmount=0;$voucher_amount=0;
					//Getting surchages
					$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'surcharge'));
					$payableAmount += $otherSurcharge;
					$voucher_amount += $otherSurcharge;

					$amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVouch['voucher_id']).'"');
					$non_po = $db->result($amount_q);
					$payableAmount += $non_po;
					$voucher_amount += $non_po;

					$amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rVouch['voucher_id']).'"');
					$po = $db->result($amount_po_q);
					$payableAmount += $po;
					$voucher_amount += $po;
					$totalVoucherAmount += $voucher_amount;

					$wtax = ($rVouch['witholding_tax'] > 0) ? ($rVouch['witholding_tax'] / 100) : 0;
					$wtax_vat = $rVouch['witholding_vat'];

					$witholding = ($wtax_vat==1) ? ($payableAmount / 1.12) * $wtax : $payableAmount * $wtax;

					$payableAmount -= $witholding;
					$voucher_advance_payment=0;
					if( $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$rVouch['voucher_id'])) ){
						$va = new voucherAdvance($rVouch['voucher_id']);
						$witholding = $va->current_voucher_withholding_tax_amount;
						$payableAmount = $va->current_voucher_payable_amount;
						$voucher_advance_payment = $va->voucher_amount_without_withholding_tax;
					}
					$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id']));
					if($otherDeduction)
						$payableAmount -= $otherDeduction;

					$totalWitholding += $witholding;
					$totalPayableAmount += $payableAmount;
				?>
					<tr>
						<td><?php echo $rVouch['voucher_no'];?></td>
						<td><?php echo $rVouch['cheque_id'];?></td>
						<td><?php echo functions::datearr($rVouch['vdate']);?></td>
						<td>
							<div align="right">
							<?php 
							if($voucher_advance_payment){
							echo functions::formatMoney($voucher_amount);
							echo ' + <i class="font-size:10px">('.functions::formatMoney($voucher_advance_payment).' Taxable Advance Payment)</i>';
							}else
							echo functions::formatMoney($voucher_amount);
							?>
							</div>
						</td>
						<td><div align="right"><?php echo ($rVouch['witholding_tax']) ? '<i>('.$rVouch['witholding_tax'].'%)</i>' : '<i>0%</i>';?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo functions::formatMoney($witholding);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($payableAmount);?></div></td>
					</tr>
				<?php endwhile;?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><div align="right"><strong>Total</strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalVoucherAmount);?></strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalWitholding);?></strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalPayableAmount);?></strong></div></td>
					</tr>
				</tbody>
			</table>
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