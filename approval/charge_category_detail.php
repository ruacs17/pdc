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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Item Details</h2>
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
				<?php if( $db->getValue('po_fuel_equipment','count(*)',array('po_id'=>$po_id)) ){ ?>
				<table border="0" width="55%">
					<tr>
						<td height="30" colspan="2"><strong>Equipment</strong></td>
						<td width="20%" align="center"><strong>Plate No</strong></td>
					</tr>
					<?php
					$arrUsedEquip=array();
					$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$po_id));
					while($rEqp = $db->fetch_array($qEqp)):
						#$eqpName = $db->getValue('equipment','name',array('equip_id'=>$rEqp['equip_id']));
						$eqpName = ($rEqp['equip_id']) ? $rEqp['equip_id'] :  $rEqp['other_equip'];
						$arrUsedEquip = functions::insert_array($arrUsedEquip,$eqpName);
					endwhile;

					$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$po_id));
					#$allEqp = $db->num_rows($qEqp);
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
				<?php } ?>
			</div><br><br>
			<form class="form-horizontal" method="post">
				<table width="100%" border="0" align="center" class="table table-bordered table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="30%" scope="col"><div align="left">Item</div></th>
							<th width="7%" scope="col"><div align="left">Quantity</div></th>
							<th width="5%" scope="col"><div align="left">Unit</div></th>
							<th width="15%" scope="col"><div align="left">Brand</div></th>
							<th width="7%" scope="col"><div align="right">Price</div></th>
							<th width="5%" scope="col"><div align="center">Discount</div></th>
							<th width="7%" scope="col"><div align="right">Amount</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$total_amount=0;$amount=0;
					$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
					while($rPOI = $db->fetch_array($qPOI)):
						$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
						$amount = ($amount) ? $amount : $rPOI['cost'];
						$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
						$bgColor='';
						if($rPOI['quantity'] != $rPOI['qty_delivered'])
							$bgColor = 'bgcolor="#f5ae00"';
					?>
						<tr <?php echo $bgColor;?>>
							<td><?php echo $rPOI['item'];?></td>
							<td><?php echo ($rPOI['qty_delivered']) ? round($rPOI['qty_delivered'],2) : '';?></td>
							<td><?php echo $rPOI['unit'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><div align="right"><?php echo ($rPOI['cost']) ? functions::formatMoney($rPOI['cost']) : '';?></div></td>
							<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div align="right"><?php echo ($amount) ? functions::formatMoney($amount) : '';?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td colspan="6"><div align="right"><strong>Total Amount</strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
						</tr>
					</tbody>
				</table><p>&nbsp;</p>
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