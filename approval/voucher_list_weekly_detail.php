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
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');

$date_start = ( isset($_REQUEST['wkF']) && !empty($_REQUEST['wkF']) ) ? functions::decode($_REQUEST['wkF']) : '';
$date_end = ( isset($_REQUEST['wkE']) && !empty($_REQUEST['wkE']) ) ? functions::decode($_REQUEST['wkE']) : '';
$vt_id = ( isset($_REQUEST['vt']) && !empty($_REQUEST['vt']) ) ? functions::decode($_REQUEST['vt']) : '';
$qVTid = ($vt_id) ? ' AND vt_id="'.$db->clean($vt_id).'"' : '';
$count=0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher List Weekly</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher List</h2>
		</div>
		<div class="box-content" align="center">
			<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
				<tr>
					<td width="50%"><div align="right"><a id="whprint" class="btn btn-small btn-info" href="voucher_list_weekly_detail_print.php?wkF=<?php echo functions::encode($date_start)?>&wkE=<?php echo functions::encode($date_end)?>&vt=<?php echo functions::encode($vt_id)?>"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div></td>
				</tr>
			</table><br><br>
			<table class="table table-striped table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="5%">Voucher #</th>
						<th width="5%">Cheque #</th>
						<th width="9%">Cheque Date</th>
						<th width="30%">Supplier / Payee</th>
						<th width="9%"><div align="right">Payable Amount</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$total=0;$advance_payment=0;$withholding_tax=0;
				$qVoucher = $db->query('SELECT * FROM voucher WHERE cheque_date BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" '.$qVTid.' ORDER BY cheque_date');
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
				?>
					<tr>
						<td><?php echo $rVouch['voucher_no'];?></td>
						<td><?php echo $rVouch['cheque_id'];?></td>
						<td><?php echo functions::datearr($rVouch['cheque_date']);?></td>
						<td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rVouch['supplierID']));?></td>
						<td><div align="right"><?php echo functions::formatMoney($payable_amount);?></div></td>
					</tr>
				<?php endwhile;?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><div align="right">Total Amount</div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($total);?></strong></div></td>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>