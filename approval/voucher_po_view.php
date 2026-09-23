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

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$vp_id = (isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : 0;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$edt = (isset($_REQUEST['edt']) && !empty($_REQUEST['edt']) ) ? 1 : 0;
$po_type = $db->getValue('po','po_type',array('po_id'=>$po_id));
$proj_id = $db->getValue('po','proj_id',array('po_id'=>$po_id));
$approved = $db->getValue('voucher','count(approved)',array('voucher_id'=>$vid));
if($approved==0){$_SESSION['notif_id2_list']=$vp_id;}
if( isset($_POST['btnSave']) && $po_id && $vp_id ){
	$payment = ( isset($_POST['txPayment']) && !empty($_POST['txPayment']) ) ? functions::moneyToDouble($_POST['txPayment']) : '0';
	
	$total_amount=0;$amount=0;
	$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
	while($rPOI = $db->fetch_array($qPOI)):
		$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
		$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
		$amount = $amount - $disc_amount;
		$total_amount += $amount;
	endwhile;

	$paid=0;
	$qPayHist = $db->select('voucher_po_payment','*',array('po_id'=>$po_id));
	while($rPH = $db->fetch_array($qPayHist)):
		if($vp_id!=$rPH['vp_id'])
			$paid += $rPH['amount'];
	endwhile;

	$payable = $total_amount - $paid;
	if($payable >= $payment){
		$db->update('voucher_po_payment',array('amount'=>$payment),array('vp_id'=>$vp_id,'po_id'=>$po_id));
		$_SESSION['notif_success']='Changes Saved!';
	}
	else{
		functions::say('Payment must not be greater than Amount Payable!');
	}
	functions::sendTo($_SERVER['REQUEST_URI']);
	die();
}
$payment = $db->getValue('voucher_po_payment','amount',array('vp_id'=>$vp_id,'po_id'=>$po_id));
$total_payment = $db->getValue('voucher_po_payment','round(sum(amount),2)',array('po_id'=>$po_id));
$service_invoice='';
if($po_type=='service'){
	$vp_title = $db->getValue('voucher_particular','vp_title',array('vp_id'=>$vp_id));
	$vpt = explode(':', $vp_title);
	$service_invoice = isset($vpt[1]) ? trim($vpt[1]) : '';
}

if( isset($_POST['txInvoice']) ){
	$new_service_invoice = trim($_POST['txInvoice']);
	$new_vp_title = 'P.O. PAYMENT - Invoice: '.$new_service_invoice;
	$db->update('voucher_particular',array('vp_title'=>$new_vp_title),array('vp_id'=>$vp_id));

	$po_invoice = $db->getValue('po','invoice',array('po_id'=>$po_id));
	$str_inv = '';
	if($po_invoice){
		$pos = strpos($po_invoice,$service_invoice);
		if($pos){
			$str_inv = str_replace($service_invoice, $new_service_invoice, $po_invoice);
		}
		else
		$str_inv = $po_invoice.'/'.$new_service_invoice;
	}
	else
		$str_inv = $new_service_invoice;

	$db->update('po',array('invoice'=>$str_inv),array('po_id'=>$po_id));

	$_SESSION['notif_success']='Invoice saved!';
	functions::sendTo(functions::pageName().'?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id).'&po_id='.functions::encode($po_id));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Items</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
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
	<style type="text/css">
		body{font-size:12px; background-color: #f5f7fa;}
		.box { border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
		
		/* Meta Summary Card Flex Layout */
		.meta-card { 
			background: #ffffff; 
			padding: 18px 20px; 
			border-radius: 6px; 
			margin-bottom: 20px; 
			border: 1px solid #dcdcdc; 
			box-shadow: 0 1px 2px rgba(0,0,0,0.03);
			box-sizing: border-box; 
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
		}
		.meta-left-col {
			flex: 1;
			margin-right: 20px;
		}
		.meta-item {
			display: flex;
			margin-bottom: 8px;
			align-items: baseline;
		}
		.meta-item:last-child {
			margin-bottom: 0;
		}
		.meta-label {
			width: 110px;
			flex-shrink: 0;
			color: #737373;
			font-weight: 600;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.5px;
		}
		.meta-value {
			flex: 1;
			color: #262626;
			font-size: 13px;
		}
		
		.meta-right-col {
			display: flex;
			flex-direction: column;
			align-items: flex-end;
			flex-shrink: 0;
		}
		.meta-right-col .meta-item {
			width: 270px;
			justify-content: flex-start;
		}
		.meta-right-col .meta-label {
			text-align: right;
			padding-right: 15px;
			width: 100px;
			flex-shrink: 0;
		}
		.meta-right-col .meta-value {
			text-align: left;
			flex: 1;
		}

		.po-badge {
			background-color: #004a99;
			color: #ffffff;
			padding: 2px 8px;
			border-radius: 3px;
			font-weight: bold;
		}

		#editInv i {
			transition: transform 0.2s ease, color 0.2s ease;
		}
		#editInv:hover i {
			color: #004a99;
			transform: scale(1.2);
		}

		/* PO Items Table Styles */
		.table-po {
			width: 100%;
			font-size: 12px;
			margin-bottom: 20px;
			border-collapse: separate;
			border-spacing: 0;
			border: 1px solid #dcdcdc;
			border-radius: 6px;
			overflow: hidden;
			background: #ffffff;
			box-shadow: 0 1px 2px rgba(0,0,0,0.02);
		}
		.table-po th {
			background-color: #f8f9fa;
			color: #495057;
			font-weight: 600;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.5px;
			padding: 12px 14px;
			border-bottom: 2px solid #dee2e6;
		}
		.table-po tbody tr {
			transition: background-color 0.15s ease-in-out;
		}
		.table-po td {
			padding: 11px 14px;
			vertical-align: middle;
			border-top: 1px solid #eef0f2;
			color: #333333;
		}
		
		.table-po tbody tr:nth-child(even):not(.discrepancy):not(.total-row) td {
			background-color: #f8fafc;
		}
		.table-po tbody tr:nth-child(odd):not(.discrepancy):not(.total-row) td {
			background-color: #ffffff;
		}
		.table-po tbody tr:hover td {
			background-color: #f1f5f9 !important;
		}
		.table-po tr.discrepancy td {
			background-color: #fff9db;
			transition: background-color 0.15s ease-in-out;
		}
		.table-po tr.discrepancy:hover td {
			background-color: #fef08a !important;
		}
		.table-po tr.total-row td {
			background-color: #f8f9fa;
			font-weight: bold;
			border-top: 2px solid #dee2e6;
			color: #111111;
		}

		/* Enhanced Payment History Card Timeline Layout */
		.history-container {
			background: #ffffff;
			border: 1px solid #dcdcdc;
			border-radius: 6px;
			padding: 16px;
			margin-bottom: 20px;
			box-shadow: 0 1px 2px rgba(0,0,0,0.02);
		}
		.history-header {
			font-weight: 600;
			color: #495057;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.5px;
			margin-bottom: 12px;
			padding-bottom: 8px;
			border-bottom: 2px solid #dee2e6;
		}
		.payment-card {
			display: flex;
			justify-content: space-between;
			align-items: center;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 4px;
			padding: 10px 14px;
			margin-bottom: 8px;
			transition: all 0.15s ease-in-out;
		}
		.payment-card:hover {
			background: #f1f5f9;
			border-color: #cbd5e1;
		}
		.payment-card.active-payment {
			background: #fffbeb;
			border-color: #fde047;
		}
		.payment-info {
			display: flex;
			flex-direction: column;
		}
		.payment-voucher {
			font-weight: bold;
			color: #1e293b;
			font-size: 12px;
		}
		.payment-date {
			color: #64748b;
			font-size: 11px;
			margin-top: 2px;
		}
		.payment-amount-box {
			text-align: right;
			min-width: 100px;
		}
		.payment-amount {
			font-weight: bold;
			color: #0f172a;
			font-size: 13px;
		}
		.active-badge {
			display: inline-block;
			background: #fef08a;
			color: #854d0e;
			font-size: 10px;
			font-weight: bold;
			padding: 1px 6px;
			border-radius: 3px;
			margin-left: 6px;
			text-transform: uppercase;
		}
		
		/* Transparent History Summary Section */
		.history-summary {
			display: flex;
			flex-direction: column;
			gap: 8px;
			padding: 10px 4px 0 4px;
			margin-top: 12px;
			background: transparent;
			border: none;
			border-top: 1px dashed #cbd5e1;
			font-size: 12px;
		}
		.history-summary-item {
			display: flex;
			justify-content: flex-end;
			align-items: center;
		}
		.summary-label {
			color: #475569;
			font-weight: 700;
			text-align: left;
			margin-right: 15px;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.3px;
		}
		.summary-value {
			font-weight: bold;
			color: #0f172a;
			text-align: right;
			min-width: 100px;
			font-size: 13px;
		}
		.summary-value.remaining {
			color: #b45309;
			font-size: 14px;
		}

		/* Full-Width Action Panel Styling (Border Removed) */
		.action-card {
			background: transparent;
			border: none;
			padding: 10px 0;
			border-radius: 0;
			box-shadow: none;
			display: flex;
			align-items: center;
			justify-content: flex-end;
			width: 100%;
			box-sizing: border-box;
		}
		.action-card .action-label {
			font-weight: 600;
			color: #495057;
			margin-right: 12px;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.5px;
		}
		.action-card input[type="text"] {
			height: 30px;
			padding: 4px 10px;
			margin-bottom: 0;
			border-radius: 4px;
			border: 1px solid #ccc;
			box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
			font-size: 13px;
			font-weight: bold;
			color: #004a99;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}
		.action-card input[type="text"]:focus {
			border-color: #004a99;
			box-shadow: 0 0 5px rgba(0,74,153,0.25);
		}
		.action-card .btn-primary {
			background-color: #004a99;
			border-color: #003875;
			padding: 6px 14px;
			font-weight: 600;
			border-radius: 4px;
			text-shadow: none;
			transition: background-color 0.2s ease;
		}
		.action-card .btn-primary:hover {
			background-color: #003875;
		}
		.payment-static-value {
			font-size: 13px;
			font-weight: bold;
			color: #0f172a;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			padding: 5px 12px;
			border-radius: 4px;
			display: inline-block;
			text-align: right;
			min-width: 120px;
		}

		.text-center { text-align: center; }
		.text-right { text-align: right; }
		.text-left { text-align: left; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12" style="margin-left:0;">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Item Details</h2>
		</div>
		<div class="box-content" style="overflow: hidden;">
			
			<!-- Meta Details Card -->
			<div class="meta-card">
				<!-- Left Column: Project & Supplier Information -->
				<div class="meta-left-col">
					<div class="meta-item">
						<div class="meta-label">Project:</div>
						<div class="meta-value"><strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id));?></strong></div>
					</div>
					<div class="meta-item">
						<div class="meta-label">Supplier:</div>
						<div class="meta-value"><strong><?php 
							$supplierID = $db->getValue('po','supplierID',array('po_id'=>$po_id));
							echo $db->getValue('supplier','name',array('supplierID'=>$supplierID));
						?></strong></div>
					</div>
				</div>

				<!-- Right Column: P.O. Number, Date & Invoice anchored to the far right -->
				<div class="meta-right-col">
					<div class="meta-item">
						<div class="meta-label">P.O. Number:</div>
						<div class="meta-value"><span class="po-badge"><?php echo $db->getValue('po','po_no',array('po_id'=>$po_id));?></span></div>
					</div>
					<div class="meta-item">
						<div class="meta-label">Date:</div>
						<div class="meta-value"><strong><?php echo functions::datearr($db->getValue('po','po_date',array('po_id'=>$po_id)));?></strong></div>
					</div>
					<div class="meta-item">
						<div class="meta-label">Invoice:</div>
						<div class="meta-value">
							<?php if($po_type=='service'){ ?>
								<?php echo $service_invoice; ?>&nbsp;<a id="editInv" title="Modify this Invoice" data-rel="tooltip" href="?vid=<?php echo functions::encode($vid)?>&vpid=<?php echo functions::encode($vp_id)?>&po_id=<?php echo functions::encode($po_id)?>&edt=1" ><i class="halflings-icon pencil"></i></a>
								<?php if($edt){ ?>
								<form method="post" style="display:inline-block; margin-top: 5px; margin-bottom: 0;">
									<input type="text" name="txInvoice" value="<?php echo $service_invoice ?>" style="width: 120px; margin-bottom: 0; height: 20px;">
									<input type="submit" name="btnInvoice" value="Save" class="btn btn-primary btn-mini">
									<a class="btn btn-mini" href="?vid=<?php echo functions::encode($vid)?>&vpid=<?php echo functions::encode($vp_id)?>&po_id=<?php echo functions::encode($po_id)?>">Cancel</a>
								</form>
								<?php } ?>
							<?php } else { ?> 
								<strong><?php echo $db->getValue('po','invoice',array('po_id'=>$po_id));?></strong>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>

			<form class="form-horizontal" method="post">
				<!-- Improvised PO Items Table -->
				<table class="table-po">
					<thead>
						<tr>
							<th width="25%"><div class="text-left">Item</div></th>
							<?php if($po_type!="service"){ ?><th width="8%"><div class="text-center">Qty Request</div></th><?php } ?>
							<?php if($po_type!="service"){ ?><th width="8%"><div class="text-center">Qty Delivered</div></th><?php } ?>
							<th width="6%"><div class="text-center">Unit</div></th>
							<th width="16%"><div class="text-left">Brand</div></th>
							<th width="11%"><div class="text-right">Price</div></th>
							<th width="6%"><div class="text-center">Discount</div></th>
							<th width="12%"><div class="text-right">Amount</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total_amount=0;$amount=0;
						$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
						while($rPOI = $db->fetch_array($qPOI)):
							$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;
							$rowClass='';
							if($rPOI['quantity'] != $rPOI['qty_delivered'])
								$rowClass = 'class="discrepancy"';
						?>
						<tr <?php echo $rowClass;?>>
							<td><?php echo $rPOI['item'];?></td>
							<?php if($po_type!="service"){ ?><td><div class="text-center"><?php echo number_format($rPOI['quantity'],2);?></div></td><?php } ?>
							<?php if($po_type!="service"){ ?><td><div class="text-center"><?php echo number_format($rPOI['qty_delivered'],2);?></div></td><?php } ?>
							<td><div class="text-center"><?php echo $rPOI['unit'];?></div></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><div class="text-right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
							<td><div class="text-center"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div class="text-right"><?php echo functions::formatMoney($amount);?></div></td>
						</tr>
						<?php endwhile;?>
						<tr class="total-row">
							<td colspan="<?php echo ($po_type=="service") ? 5 : 7 ?>"><div class="text-right">Total Amount:</div></td>
							<td><div class="text-right" style="color: #004a99;"><?php echo functions::formatMoney($total_amount)?></div></td>
						</tr>
					</tbody>
				</table>

				<!-- Payment History Container (Half-width card) -->
				<?php
				$qPayHist = $db->select('voucher_po_payment','*',array('po_id'=>$po_id),'ORDER BY vpp_id');
				if( $db->num_rows($qPayHist)>0 ){
				?>
				<div class="row-fluid">
					<div class="span6" style="margin-left: 0;">
						<div class="history-container">
							<div class="history-header">Payment History</div>
							
							<div class="history-list">
								<?php
								$total_payment=0; $individual_payment=0;
								while($rPH = $db->fetch_array($qPayHist)):
									$phVid = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPH['vp_id']));
									$voucherChkDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$phVid));
									$individual_payment=$rPH['amount'];
									$total_payment+=$individual_payment;
									$isActive = ($vp_id == $rPH['vp_id']);
								?>
								<div class="payment-card <?php echo $isActive ? 'active-payment' : ''; ?>">
									<div class="payment-info">
										<div class="payment-voucher">
											Voucher No: <?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$phVid));?>
											<?php if($isActive){ ?><span class="active-badge">Current Payment</span><?php } ?>
										</div>
										<div class="payment-date"><?php echo functions::datearr($voucherChkDate);?></div>
									</div>
									<div class="payment-amount-box">
										<div class="payment-amount"><?php echo functions::formatMoney($individual_payment);?></div>
									</div>
								</div>
								<?php endwhile; ?>
							</div>

							<div class="history-summary">
								<div class="history-summary-item">
									<span class="summary-label">Total Payment:</span>
									<span class="summary-value"><?php echo functions::formatMoney($total_payment);?></span>
								</div>
								<div class="history-summary-item">
									<span class="summary-label">Remaining Payable:</span>
									<span class="summary-value remaining"><?php echo functions::formatMoney($total_amount - $total_payment);?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>

				<!-- Action Save Panel (Extended to full width, borderless) -->
				<div class="row-fluid" style="margin-bottom: 20px;">
					<div class="span12" style="margin-left: 0;">
						<div class="action-card">
							<span class="action-label">Payment Made:</span>
							<?php if($approved==0){?>
								<input type="text" name="txPayment" id="txPayment" value="<?php echo functions::formatMoney($payment)?>" onkeyup="FormatCurrency(this);" style="text-align: right; width: 160px; margin-right: 10px;">
								<input type="submit" name="btnSave" id="btnSave" value="Save Changes" class="btn btn-primary">
							<?php }else{?>
								<div class="payment-static-value"><?php echo functions::formatMoney($payment)?></div>
							<?php } ?>
						</div>
					</div>
				</div>
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

<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>

<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>