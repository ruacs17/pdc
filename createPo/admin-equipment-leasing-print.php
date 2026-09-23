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
$iel_id = (isset($_REQUEST['iel_id']) && !empty($_REQUEST['iel_id']) ) ? functions::decode($_REQUEST['iel_id']) : 0;
$qIH = $db->select('inhouse_equip_leasing','*',array('iel_id'=>$iel_id));
$rIH = $db->fetch_array($qIH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Equipment Leasing</title>
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
			print_header('Equipment Leasing');
			?><br>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0">
				<tr>
					<td align="right">
						Invoice No: <strong>
						<?php
						$exp = explode('-',$rIH['iel_date']);
						$m=0;$y=0;
						if(count($exp)==3){
							$m = $exp['1'];
							$y = $exp['0'];
						}
						echo $y.$m.$rIH['iel_id'];
						?></strong>
					</td>
				</tr>
				<tr>
					<td align="right">Date:<strong> <?php echo functions::datearr($rIH['iel_date']);?></strong></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Charged To: <strong><?php echo ($rIH['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rIH['proj_id'])) : $db->getValue('inhouse_equip_leasing','payee',array('iel_id'=>$iel_id));?></strong></td>
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
			<table width="100%" border="1">
				<tr>
					<th width="50%" scope="col"><div align="center">Item</div></th>
					<th width="9%" scope="col"><div align="center">Duration<br>(Hour)</div></th>
					<th width="14%" scope="col"><div align="center">Cost</div></th>
					<th width="17%" scope="col"><div align="center">Amount</div></th>
				</tr>
				<?php
				$total_amount=0;$amount=0;
				$q = $db->select('inhouse_equip_leasing_item','*',array('iel_id'=>$iel_id),'ORDER BY ieli_id');
				while($r = $db->fetch_array($q)):
					$amount = $r['cost'] * $r['duration'];
					$disc_amount = ($r['discount']) ? $amount * ($r['discount'] / 100) : 0;
					$amount = $amount - $disc_amount;
					$total_amount += $amount;
					$equipmentName = '';
					$qEqp = $db->select('equipment','*',array('equip_id'=>$r['equip_id']));
					while($rEqp = $db->fetch_array($qEqp)):
						$equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']." ".$rEqp['serial_no']));
					endwhile;
				?>
				<tr>
					<td><div align="left" class="padParLeft"><?php echo $equipmentName;?></div></td>
					<td><div align="center"><?php echo $r['duration'];?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($r['cost']);?>&nbsp;&nbsp;</div></td>
					<td><div align="right"><?php echo functions::formatMoney($amount);?> <?php echo ($r['discount']) ? '<br><i style="font-size:11px">('.$r['discount'].'% off)</i>' : '';?>&nbsp;&nbsp;</div></td>
				</tr>
				<?php endwhile;?>
				<tr>
					<td colspan="3"><div align="right"><strong>Total</strong>&nbsp;&nbsp;</div></td>
					<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong>&nbsp;&nbsp;</div></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td height="39">&nbsp;</td>
	</tr>
	<tr>
		<td><?php echo $db->getValue('inhouse_equip_leasing','terms',array('iel_id'=>$iel_id));?></td>
	</tr>
	<tr>
		<td height="15">&nbsp;</td>
	</tr>
	<tr>
		<td align="center">
			<table width="100%" border="0">
				<tr>
					<td align="center" width="50%">Checked By:</td>
					<td align="center" width="50%">Charged By:</td>
				</tr>
				<tr>
					<td height="50" align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$rIH['checkedBy'])));?></strong></td>
					<td align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$rIH['chargedBy'])));;?></strong></td>
				</tr>
				<tr>
					<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
					<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
				</tr>
				<tr>
					<td height="50">&nbsp;</td>
					<td>&nbsp;</td>
				</tr>        
				<tr>
					<td align="center">Approved By:</td>
					<td align="center">&nbsp;</td>
				</tr>
				<tr>
					<td height="50" align="center" valign="bottom"><strong><?php echo strtoupper($db->getValue('equip_user','concat(fname," ",substring(mname,1,1),". ",lname)',array('eu_id'=>$rIH['approvedBy'])));?></strong></td>
					<td align="center" valign="bottom"></td>
				</tr>
				<tr>
					<td align="center"><div style="text-decoration:overline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Signature Over Printed Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
					<td align="center">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
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