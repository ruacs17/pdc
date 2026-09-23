<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;
$qIH = $db->select('inhouse_material','*',array('im_id'=>$im_id));
$rIH = $db->fetch_array($qIH);
$prepared_by = $db->getValue('inhouse_material','prepared_by',array('im_id'=>$im_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Warehouse Stocks Print</title>
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
	.padParLeft{padding-left:10px;}
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
				print_header('Warehouse Issuance Stock');
				?><br>
			</td>
		</tr>
	</thead>
    <tbody>
		<tr>
			<td>
				<table width="100%" border="0">
					<tr>
						<td align="right">Order No: <strong><?php echo $rIH['im_no'];?></strong></td>
					</tr>
					<tr>
						<td align="right">Date:<strong> <?php echo functions::datearr($rIH['im_date']);?></strong></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Charge To: <strong><?php echo ($rIH['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rIH['proj_id'])) : $db->getValue('inhouse_material','payee',array('im_id'=>$im_id));?></strong></td>
					</tr>
					<?php if($rIH['address']){?>
					<tr>
						<td>Address: <strong><?php echo $rIH['address'];?></strong></td>
					</tr>
					<?php }?>
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="1" style="font-size: 12px">
					<tr>
						<th width="28%" scope="col"><div align="center">Equipment</div></th>
						<th width="23%" scope="col"><div align="center">Item</div></th>
						<th width="14%" scope="col"><div align="center">Brand</div></th>
						<th width="10%" scope="col"><div align="center">Qty</div></th>
						<th width="10%" scope="col"><div align="center">Cost</div></th>
						<th width="15%" scope="col"><div align="center">Amount</div></th>
					</tr>
					<?php
					$total_amount=0;
					$amount=0;
					$q = $db->select('inhouse_material_item','*',array('im_id'=>$im_id),'ORDER BY item');
					while($r = $db->fetch_array($q)):
						$amount = $r['cost'] * $r['quantity'];
						$disc_amount = ($r['discount']) ? $amount * ($r['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
						$eqp_id = $db->getValue('inhouse_material_fuel_equipment','equip_id',array('imi_id'=>$r['imi_id']));
						$eqpName = ($eqp_id) ? $db->getValue('equipment','name',array('equip_id'=>$eqp_id)) : $db->getValue('inhouse_material_fuel_equipment','other_equip',array('imi_id'=>$r['imi_id']));
					?>
					<tr>
						<td><div align="left" class="padParLeft"><?php echo $eqpName;?></div></td>
						<td><div align="left" class="padParLeft"><?php echo $r['item'];?></div></td>
						<td><div align="center"><?php echo $r['brand'];?></div></td>
						<td><div align="center"><?php echo $r['quantity']; echo ($r['unit']) ? ' ('.$r['unit'].')' : ''?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($r['cost']);?>&nbsp;&nbsp;</div></td>
						<td><div align="center"><?php echo functions::formatMoney($amount);?> <?php echo ($r['discount']) ? '<br><i style="font-size:11px">('.$r['discount'].'% off)</i>' : '';?>&nbsp;&nbsp;</div></td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td colspan="5"><div align="right"><strong>Total</strong>&nbsp;&nbsp;</div></td>
						<td><div align="center"><strong><?php echo functions::formatMoney($total_amount)?></strong>&nbsp;&nbsp;</div></td>
					</tr>
				</table>
			</td>
		</tr>
		<?php if($rIH['purpose']){?>
		<tr>
			<td height="20">&nbsp;</td>
		</tr>
		<tr>
			<td>Purpose: <strong><?php echo $rIH['purpose'];?></strong></td>
		</tr>
		<?php }?>
		<tr>
			<td height="20">&nbsp;</td>
		</tr>
		<tr>
			<td><?php echo trim($rIH['terms']);?></td>
		</tr>
		<?php if($rIH['remarks']){?>
		<tr>
			<td>Remarks: <strong><?php echo $rIH['remarks'];?></strong></td>
		</tr>
		<tr>
			<td height="30">&nbsp;</td>
		</tr>
		<?php }?>
		<tr>
			<td align="center">
				<table width="100%" border="0">
					<tr>
						<td align="center" width="30%">Checked By:</td>
						<td align="center" width="30%">Delivered By:</td>
						<td align="center" width="30%">Received By:</td>
					</tr>
					<tr>
						<td height="50" align="center" valign="bottom"><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rIH['checked_id']));?></strong></td>
						<td align="center" valign="bottom"><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rIH['delivered_id']));?></strong></td>
						<td height="50" align="center" valign="bottom"><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rIH['received_id']));?></strong></td>
					</tr>
					<tr>
						<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;</div></td>
						<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;</div></td>
						<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;</div></td>
					</tr>
				</table><br>
				<table width="100%" border="0">
					<tr>
						<td height="50">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td align="center">Prepared By:</td>
						<td align="center">Approved By:</td>
					</tr>
					<tr>
						<td height="50" align="center" valign="bottom"><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rIH['prepared_id']));?></strong></td>
						<td align="center" valign="bottom"><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$rIH['approved_id']));?></strong></td>
					</tr>
					<tr>
						<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
						<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<!-- end: JavaScript-->
</body>
</html>