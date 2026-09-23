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
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$proj_id = $db->getValue('po','proj_id',array('po_id'=>$po_id));
$payee = $db->getValue('po_fuel','payee',array('po_id'=>$po_id));
$equip_id = $db->getValue('po_fuel','equip_id',array('po_id'=>$po_id));
$equipment = $db->getValue('equipment','name',array('equip_id'=>$equip_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Fuel P.O. Items</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Fuel Purchase Order Item Details</h2>
		</div>
		<div class="box-content">
			<div>Charge To: <strong><?php echo ($proj_id) ? $db->getValue('project','proj_name',array('proj_id'=>$proj_id)) : $payee;?></strong></div>
			<div>
				Supplier:
				<strong>
				<?php 
				$supplierID = $db->getValue('po','supplierID',array('po_id'=>$po_id));
				echo $db->getValue('supplier','name',array('supplierID'=>$supplierID));
				?>
				</strong>
			</div>
			<div>Date: <strong><?php echo functions::datearr($db->getValue('po','po_date',array('po_id'=>$po_id)));?></strong></div>
			<div>
				<table border="0" width="55%">
					<tr>
						<td height="30" colspan="2"><strong>Equipment</strong></td>
						<td width="20%" align="center"><strong>Plate No</strong></td>
					</tr>
					<?php
					$arrUsedEquip=array();
					$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$po_id));
					while($rEqp = $db->fetch_array($qEqp)):
						$eqpName = ($rEqp['equip_id']) ? $rEqp['equip_id'] :  $rEqp['other_equip'];
						$arrUsedEquip = functions::insert_array($arrUsedEquip,$eqpName);
					endwhile;

					$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$po_id));
					$allEqp = count($arrUsedEquip);
					$cntEqp=1;
					foreach($arrUsedEquip as $eqpID):
					$cntEqpDisp = ($allEqp > 1) ? $cntEqp++.'.' : '&nbsp;';
						echo '<tr>';
						if( $eqpName = $db->getValue('equipment','name',array('equip_id'=>$eqpID)) ){
							echo '<td valign="top" width="4%">'.$cntEqpDisp.'</td>';
							echo '<td>'.$eqpName.'</td>';
							echo '<td valign="top" align="center">'.$db->getValue('equipment','plate_no',array('equip_id'=>$eqpID)).'</td>';
						}
						else{
							echo '<td>'.$cntEqpDisp.'</td>';
							echo '<td>'.$eqpID.' <i>(outsider)</i></td>';
							echo '<td>&nbsp;</td>';
						}
						echo '</tr>';
					endforeach;
					?>
				</table>
			</div><br><br>
			<form class="form-horizontal" method="post">
				<table width="100%" border="0" align="center" class="table table-hover table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="23%" scope="col"><div align="left">Item</div></th>
							<th width="7%" scope="col"><div align="center">Quantity</div></th>
							<th width="5%" scope="col"><div align="left">Unit</div></th>
							<th width="15%" scope="col"><div align="left">Brand</div></th>
							<th width="8%" scope="col"><div align="left">Estimate</div></th>
							<th width="10%" scope="col"><div align="right">Price</div></th>
							<th width="5%" scope="col"><div align="center">Discount</div></th>
							<th width="10%" scope="col"><div align="right">Amount</div></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td height="25"></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<?php
						$total_amount=0;$amount=0;
						$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
						while($rPOI = $db->fetch_array($qPOI)):
							$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
							$amount = ($amount) ? $amount : $rPOI['cost'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;

							$distance = $db->getValue('po_fuel_equipment','distance',array('po_item_id'=>$rPOI['po_item_id']));
							$days = $db->getValue('po_fuel_equipment','days',array('po_item_id'=>$rPOI['po_item_id']));
							if($days)
								$days = ($days > 1) ? $days.'days' : $days.'day';
							$distance = ($distance) ? $distance.'km' : '';

							$bgColor='';
							if($rPOI['quantity'] != $rPOI['qty_delivered'])
								$bgColor = 'bgcolor="#f5ae00"';
						?>
						<tr <?php echo $bgColor;?>>
							<td><?php echo $rPOI['item'];?></td>
							<td><div align="center"><?php echo ($rPOI['qty_delivered']) ? round($rPOI['qty_delivered'],2) : '';?></div></td>
							<td><?php echo $rPOI['unit'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><?php echo ($days && $distance) ? $distance.' / '. $days : $distance.' '. $days;?></td>
							<td><div align="right"><?php echo ($rPOI['cost']) ? functions::formatMoney($rPOI['cost']) : '';?></div></td>
							<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div align="right"><?php echo ($amount) ? functions::formatMoney($amount) : '';?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td colspan="8"><div align="right">Total Amount &nbsp;&nbsp;<strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
						</tr>
					</tbody>
				</table><p>&nbsp;</p>
				<?php
				$qPayHist = $db->select('voucher_po_payment','*',array('po_id'=>$po_id),'ORDER BY vpp_id');
				if( $db->num_rows($qPayHist) > 0 ){
					$total_payment=0;
				?>
				<div align="left">
					<table width="40%" border="0" align="left">
						<tr>
							<td colspan="3" align="left" height="35px">Payment History</td>
						</tr>
						<tr>
							<th width="30%" scope="col"><div align="left">Voucher No</div></th>
							<th width="30%" scope="col"><div align="left">Date</div></th>
							<th width="40%" scope="col"><div align="left">Payment</div></th>
						</tr>
						<?php 
						while($rPH = $db->fetch_array($qPayHist)):
						$phVid = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPH['vp_id']));
						$voucherChkDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$phVid));
						$total_payment+=$rPH['amount'];
						?>
						<tr>
							<td height="35px"><?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$phVid));?></td>
							<td><?php echo functions::datearr($voucherChkDate);?></td>
							<td><?php echo functions::formatMoney($rPH['amount']);?></td>
						</tr>
						<?php endwhile; ?>
						<tr>
							<td></td>
							<td align="right" height="35px"><strong>Total Payment</strong>&nbsp;&nbsp;</td>
							<td><strong><?php echo functions::formatMoney($total_payment);?></strong></td>
						</tr>
						<tr>
							<td></td>
							<td align="right" height="35px"><strong>Payable</strong>&nbsp;&nbsp;</td>
							<td><strong><?php echo functions::formatMoney($total_amount - $total_payment);?></strong></td>
						</tr>
					</table>
				</div>
				<?php } #if( $db->num_rows($qPayHist)>0 )?>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row--><!-- body content: end here-->
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