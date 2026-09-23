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
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Cost View</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-striped table-bordered bootstrap-datatable datatable" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="8%" scope="col"><div align="left">Date</div></th>
							<th width="8%" scope="col"><div align="left">Voucher No.</div></th>
							<th width="20%" scope="col"><div align="left">Charges Category</div></th>
							<th width="40%" scope="col"><div align="left">Item Detail</div></th>
							<th width="8%" scope="col"><div align="right">Amount</div></th>
						</tr>
					</thead>
					<tbody>
					<?php 
					$vdate='';$total_amount=0;
					$qvDetails = $db->select('voucher_detail','*',array('proj_id'=>$p_id),'ORDER BY vd_date');
					while($rvDetails = $db->fetch_array($qvDetails)):
						$total_amount += $rvDetails['amount'];
					?>
						<tr>
							<td><?php  echo functions::datearr($rvDetails['vd_date']);?></td>
							<td>
								<?php 
								$vid = $db->query('SELECT voucher_id FROM voucher_detail vd, voucher_particular vp WHERE vd.vp_id=vp.vp_id AND vd.vd_id="'.$db->clean($rvDetails['vd_id']).'"');
								echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$db->result()));
								?>
							</td>
							<td><?php echo $db->getValue('item_deduction','name',array('item_id'=>$rvDetails['category_id']));?></td>
							<td><?php echo $rvDetails['item']?></td>
							<td><div align="right"><?php echo functions::formatMoney($rvDetails['amount']);?></div></td>
						</tr>
					<?php endwhile;
					$qPODetails = $db->query('SELECT * FROM po, po_item WHERE po.po_id=po_item.po_id AND po.proj_id="'.$db->clean($p_id).'" ORDER BY po.po_date');
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
								echo $rPODetails['item'].' ( '.round($rPODetails['qty_delivered'],2).' '.$rPODetails['unit'].' x ';
								echo functions::formatMoney($rPODetails['cost']);
								?>
								)
							</td>
							<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						</tr>
					<?php endwhile;
					$qInhouseEquipment = 'SELECT * FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND iel.proj_id="'.$db->clean($p_id).'"';
					$qInhouseEquipment .= ' ORDER BY iel_date';

					$qIE = $db->query($qInhouseEquipment);
					while($rIE = $db->fetch_array($qIE)):
						$amount=0;
						$amount = $rIE['cost'] * $rIE['duration'];
						$disc_amount = ($rIE['discount']) ? $amount * ($rIE['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
						$equipmentName = '';
						$qEqp = $db->select('equipment','*',array('equip_id'=>$rIE['equip_id']));
						while($rEqp = $db->fetch_array($qEqp)):
							$equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']." ".$rEqp['serial_no']));
						endwhile;
					?>
						<tr>
							<td><?php echo functions::datearr($rIE['iel_date']);?></td>
							<td>&nbsp;</td>
							<td>Inhouse Equipment Leasing</td>
							<td><?php echo $equipmentName.' ( '.round($rIE['duration']).' hrs x '.functions::formatMoney($rIE['cost'],2).' )'?></td>
							<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						</tr>
					<?php endwhile;
					$qInhouseMaterials = 'SELECT * FROM inhouse_material im, inhouse_material_item imi WHERE im.im_id=imi.im_id AND im.proj_id="'.$db->clean($p_id).'"';
					$qInhouseMaterials .= ' ORDER BY im_date';
					$qIM = $db->query($qInhouseMaterials);
					while($rIM = $db->fetch_array($qIM)):
						$amount=0;
						$amount = $rIM['cost'] * $rIM['quantity'];
						$disc_amount = ($rIM['discount']) ? $amount * ($rIM['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
					?>
						<tr>
							<td><?php echo functions::datearr($rIM['im_date']);?></td>
							<td>&nbsp;</td>
							<td>Inhouse Warehouse Stock</td>
							<td><?php echo $rIM['item'].' ( '.round($rIM['quantity']).' '.$rIM['unit'].' x '.functions::formatMoney($rIM['cost'],2).' )'?></td>
							<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						</tr>
					<?php endwhile;?>
					<tbody>
				</table>
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" style="display:none;" > 
					<tr>
						<td width="8%"></td>
						<td width="8%"></td>
						<td width="20%">&nbsp;</td>
						<td width="25%"><div align="right"><strong>Total Amount&nbsp;&nbsp;</strong></div></td>
						<td width="10%"><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
					</tr>
				</table><p>&nbsp;</p>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>