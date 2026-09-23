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
$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;
#$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;
$qIH = $db->select('inhouse_material','*',array('ref_id'=>$ref_id));
$rIH = $db->fetch_array($qIH);
$prepared_by = $db->getValue('inhouse_material','prepared_by',array('ref_id'=>$ref_id));
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
	<tr>
		<td>
			<?php 
			require_once('../class/print_header.php');
			print_header('Warehouse Issuance Stock');
			?><br>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0">
				<tr>
					<td align="right">Order No: <strong><?php echo $rIH['ref_id'];?></strong></td>
				</tr>
				<tr>
					<td align="right">Date:<strong> <?php echo functions::datearr($rIH['im_date']);?></strong></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>
						<strong>Charge To:</strong><br>
						<table border="0" width="90%">
							<tr>
								<td width="4%"></td>
								<td></td>
							</tr>
							<?php 
							$count=0;
							$qProjs = $db->query('SELECT proj_id, payee as "pay" FROM inhouse_material_fuel_equipment pf, inhouse_material p WHERE pf.im_id=p.im_id AND p.ref_id="'.$db->clean($ref_id).'"');
							while($rProjs = $db->fetch_array($qProjs)):
							$count++;
							$projName = $db->getValue('project','proj_name',array('proj_id'=>$rProjs['proj_id']));
							$projName = ($projName) ? $projName : $rProjs['pay'];
							?>
							<tr>
								<td valign="top"><?php echo $count.'.'?></td>
								<td><?php echo $projName?></td>
							</tr>
							<?php endwhile;?>
						</table>
					</td>
				</tr>
				<tr>
					<td><br>
						<table border="0" width="90%">
							<tr>
								<td height="30" colspan="2"><strong>Equipment</strong></td>
								<td width="20%" align="center"><strong>Plate No</strong></td>
							</tr>
							<?php
							$qEqp = $db->query('SELECT * FROM inhouse_material_fuel_equipment fl, inhouse_material im WHERE im.im_id=fl.im_id AND im.ref_id="'.$db->clean($ref_id).'"');
							$allEqp = $db->num_rows($qEqp);
							$cntEqp=1;
							while($rEqp = $db->fetch_array($qEqp)):
								$cntEqpDisp = ($allEqp > 1) ? $cntEqp++.'.' : '&nbsp;';
								echo '<tr>';
									if( $eqpName = $db->getValue('equipment','name',array('equip_id'=>$rEqp['equip_id'])) ){
										echo '<td valign="top" width="4%">'.$cntEqpDisp.'</td>';
										echo '<td>'.$eqpName.'</td>';
										echo '<td valign="top" align="center">'.$db->getValue('equipment','plate_no',array('equip_id'=>$rEqp['equip_id'])).'</td>';
									}
									else{
										echo '<td>'.$cntEqpDisp.'</td>';
										echo '<td>'.$rEqp['other_equip'].' <i>(outsider)</i></td>';
										echo '<td>&nbsp;</td>';
									}
								echo '</tr>';
								endwhile;
							?>
						</table>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="1">
				<tr>
					<th width="36%" scope="col"><div align="center">Item</div></th>
					<th width="9%" scope="col"><div align="center">Qty</div></th>
					<th width="10%" scope="col"><div align="center">Unit</div></th>
					<th width="14%" scope="col"><div align="center">Brand</div></th>
					<th width="14%" scope="col"><div align="center">Cost</div></th>
					<th width="17%" scope="col"><div align="center">Amount</div></th>
				</tr>
				<?php
				$total_amount=0;
				$amount=0;
				#$q = $db->select('inhouse_material_item','*',array('ref_id'=>$ref_id),'ORDER BY item');
				$q = $db->query('SELECT pi.item,sum(quantity) as qty,sum(quantity) as qty_d,unit,brand,cost,cost as t_cost,discount FROM inhouse_material p, inhouse_material_item pi WHERE p.im_id=pi.im_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item');
				while($r = $db->fetch_array($q)):
					$amount = $r['t_cost'] * $r['qty_d'];
					$disc_amount = ($r['discount']) ? $amount * ($r['discount'] / 100) : 0;
					$amount = $amount - $disc_amount;
					$total_amount += $amount;
				?>
				<tr>
					<td><div align="left" class="padParLeft"><?php echo $r['item'];?></div></td>
					<td><div align="center"><?php echo $r['qty_d'];?></div></td>
					<td><div align="center"><?php echo $r['unit'];?></div></td>
					<td><div align="center"><?php echo $r['brand'];?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($r['t_cost']);?>&nbsp;&nbsp;</div></td>
					<td><div align="right"><?php echo functions::formatMoney($amount);?> <?php echo ($r['discount']) ? '<br><i style="font-size:11px">('.$r['discount'].'% off)</i>' : '';?>&nbsp;&nbsp;</div></td>
				</tr>
				<?php endwhile;?>
				<tr>
					<td colspan="5"><div align="right"><strong>Total</strong>&nbsp;&nbsp;</div></td>
					<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong>&nbsp;&nbsp;</div></td>
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
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<!-- end: JavaScript-->
</body>
</html>