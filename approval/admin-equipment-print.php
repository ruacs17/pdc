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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;

$txEngineNo='';$txChassisNo='';$txAcquiredMon='';$txAcquiredDay='';$txAcquiredYear=''; $selBrand='';$txInventoryNo='';$txUnit='';$txQuantity=0;$txStatus='';
$txMVNo='';$txPower=''; $txRate=0; $txLocation='';$txGrossWt='';$txNetWt='';$txShipWt='';$txPrice='';$out_date='';$txRemark='';$txProposedDay='';$txProposedYear=''; $txProposedMon=''; $txRemark=''; $txProposedNoYr=0; $txUsageLimit=0;$txWarranty=0;
$q = $db->select('equipment','*',array('equip_id'=>$equip_id));
$r = $db->fetch_array($q);
$txInventoryNo = $r['inventory_id'];
$selClass = $r['classification'];
$txProductivity = $r['productivity'];
$selCat = $r['category'];
$selType = $r['type'];
$txDesc = $r['equip_desc'];
$txModel = $r['model'];
$txBrand = $r['brand'];
$txSerialNo = $r['serial_no'];
$txPlateNo = $r['plate_no'];
$txEngineNo = $r['engine_no'];
$txChassisNo = $r['chassis_no'];
$txDateAcquired = functions::datearr($r['date_acquired']);
$txMVNo = $r['mvFileNo'];
$txPower = $r['power'];
$txLocation = $r['location'];
$txGrossWt = $r['gross_wt'];
$txNetWt = $r['net_wt'];
$txShipWt = $r['shipping_wt'];
$txPrice = ($r['price']) ? functions::formatMoney($r['price']) : 0;
$txRemark = $r['remarks'];
$txRate = ($r['rate']) ? functions::formatMoney($r['rate']) : 0;
$txQuantity = $r['quantity'];
$txUnit = $r['unit'];
$txStatus = $r['status'];
$liter_hour = $r['liter_hr'];
$liter_km = $r['liter_km'];

$txProposedNoYr = $db->getValue('equipment','if( IFNULL(left(proposed_life,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(proposed_life,4),0)-IFNULL(left(date_acquired,4),0),0) as proposedLife',array('equip_id'=>$r['equip_id']));
$txProposedYr = functions::datearr($r['proposed_life']);

$txWarrantyNo = $db->getValue('equipment','if( IFNULL(left(warranty,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(warranty,4),0)-IFNULL(left(date_acquired,4),0),0) as yrWarranty',array('equip_id'=>$r['equip_id']));
$txWarranty = functions::datearr($r['warranty']);

$txUsageLimit = ($r['limit_minutes'] > 0) ? $r['limit_minutes'] / 60 : 0;
$out_date = $r['out_date'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Property Detail Print</title>
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
	<style>.tdSpace{padding: 2px 10px 2px 10px;}</style>
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
				print_header('Property Details');
				?><br>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td> 
				<table width="100%" border="1" cellspacing="0" cellpadding="0" align="center" style="font-size:12px;">
					<?php 
					$fileName = $db->getValue('images','name',array('equip_id'=>$equip_id));
					$file = ($fileName) ? '../img_equip/'.$fileName : '../img_equip/blank-pic.png';
					if($fileName){
					?>
					<tr>
						<td colspan="2"><br><div align="center"><img height="400" width="400" src="<?php echo $file?>"></div><br></td>
					</tr>
					<?php }?>
					<tr>
						<th width="40%" class="tdSpace" align="right" scope="row">Property Classification</th>
						<td width="50%" class="tdSpace"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'classification','type_desc'=>$selClass));?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Property Category</th>
						<td class="tdSpace"><?php echo $selCat;?></td>
					</tr>
					<tr>
						<th width="40%" class="tdSpace" align="right" scope="row">Property Type</th>
						<td width="50%" class="tdSpace"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'type','type_desc'=>$selType));?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Brand/Made</th>
						<td class="tdSpace"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'brand','type_desc'=>$txBrand));?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Inventory Code No.</th>
						<td class="tdSpace"><?php echo $txInventoryNo;?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Description</th>
						<td class="tdSpace"><?php echo nl2br($txDesc)?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Productivity</th>
						<td class="tdSpace"><?php echo nl2br($txProductivity)?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Model</th>
						<td class="tdSpace"><?php echo $txModel?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Serial Number</th>
						<td class="tdSpace"><?php echo $txSerialNo?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Plate Number</th>
						<td class="tdSpace"><?php echo $txPlateNo?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">MV File Number</th>
						<td class="tdSpace"><?php echo $txMVNo?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Engine Number</th>
						<td class="tdSpace"><?php echo $txEngineNo?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Chassis Number</th>
						<td class="tdSpace"><?php echo $txChassisNo?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Power / Capacity</th>
						<td class="tdSpace"><?php echo nl2br($txPower)?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Gross Weight</th>
						<td class="tdSpace"><?php echo $txGrossWt?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Net Weight</th>
						<td class="tdSpace"><?php echo $txNetWt?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Shipping Weight</th>
						<td class="tdSpace"><?php echo $txShipWt?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Average Liter / Hour</th>
						<td class="tdSpace"><?php echo $liter_hour?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Average Liter / KM</th>
						<td class="tdSpace"><?php echo $liter_km?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Acquisition Cost</th>
						<td class="tdSpace"><?php echo $txPrice?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Location</th>
						<td class="tdSpace"><?php echo $txLocation?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Date Acquired</th>
						<td class="tdSpace"><?php echo $txDateAcquired;?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Warranty Expire</th>
						<td class="tdSpace"><?php echo $txWarranty.' ('.$txWarrantyNo.' Year/s)';?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Useful Life</th>
						<td class="tdSpace"><?php echo $txProposedYr.' ('.$txProposedNoYr.' Year/s)';?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Maintenance Period (No. of Hours)</th>
						<td class="tdSpace"><?php echo $txUsageLimit?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Quantity</th>
						<td class="tdSpace"><?php echo $txQuantity.' '.$txUnit;?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Status</th>
						<td class="tdSpace"><?php echo strtoupper($txStatus); echo ($txStatus=="Unserviceable" || $txStatus=="Sold" || $txStatus=="Trade In") ? ' <i>('.functions::datearr($out_date).')</i>' : "";?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Rate Per Hour</th>
						<td class="tdSpace"><?php echo $txRate?></td>
					</tr>
					<tr>
						<th class="tdSpace" align="right" scope="row">Remarks</th>
						<td class="tdSpace"><?php echo nl2br($txRemark);?></td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table><br><br>
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