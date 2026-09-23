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
$count=0;
$yr=date('Y');
$supplierID='';
$yrSearch = " AND left(po_date,4)='".$yr."'";
$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : 0;
if($yr=='all')
	$yrSearch = "";
else if($yr)
	$yrSearch = " AND left(po_date,4)='".$db->clean($yr)."'";

$supplierID = (isset($_REQUEST['sid']) && !empty($_REQUEST['sid']) ) ? functions::decode($_REQUEST['sid']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Supplier Patronage Detail</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span><?php echo $db->getValue('supplier','name',array('supplierID'=>$supplierID))?></h2>
		</div>
		<div class="box-content">
			<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover table-striped" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="8%" scope="col"><div align="left">Date</div></th>
						<th width="8%" scope="col"><div align="left">Voucher No.</div></th>
						<th width="30%" scope="col"><div align="left">Charges Category</div></th>
						<th width="40%" scope="col"><div align="left">Item Detail</div></th>
						<th width="7%" scope="col"><div align="right">Amount</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$total_amount=0;$vdate='';
					$qPODetails = $db->query('SELECT * FROM po, po_item WHERE po.po_id=po_item.po_id AND po.supplierID="'.$db->clean($supplierID).'" '.$yrSearch.' ORDER BY po.po_date');
					while($rPODetails = $db->fetch_array($qPODetails)):
						$count++;
						$amount=0;
						$amount = $rPODetails['cost'] * $rPODetails['qty_delivered'];
						$disc_amount = ($rPODetails['discount']) ? $amount * ($rPODetails['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
					?>
					<tr>
						<td><?php echo functions::datearr($rPODetails['po_date']);?></td>
						<td>
							<?php
							$v = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPODetails['vp_id']));
							echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$v));
							?>
						</td>
						<td><?php echo $db->getValue('voucher_particular','vp_title',array('vp_id'=>$rPODetails['vp_id']));?></td>
						<td>
							<?php 
							echo $rPODetails['item'].' ( '.$rPODetails['qty_delivered'].' '.$rPODetails['unit'].' x ';
							if( $db->getValue('po_item','count(DISTINCT cost)',array('item'=>$rPODetails['item'])) > 1 ){
							echo functions::formatMoney($rPODetails['cost']);
							}
							else{
								echo functions::formatMoney($rPODetails['cost']);
							}
							?>
							)
						</td>
						<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td></td>
						<td></td>
						<td>&nbsp;</td>
						<td><div align="right"><strong>Total Amount</strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
					</tr>
				</tbody>
			</table><p>&nbsp;</p>
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