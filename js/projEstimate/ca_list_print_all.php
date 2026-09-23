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
$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0; 
function itemSubTotal($itemParent,&$sum){
	global $db;
	$q = $db->select('dqc','*',array('itemParent'=>$itemParent));
	while($r = $db->fetch_array($q)):
		$sum += is_numeric($r['total']) ? $r['total'] : 0;
		itemSubTotal($r['id'],$sum);
	endwhile;
}
function itemTotal($itemParent){
	$sum=0;
	global $db;
	$q = $db->select('dqc','*',array('id'=>$itemParent));
	while($r = $db->fetch_array($q)):
		$sum += is_numeric($r['total']) ? $r['total'] : 0;
	endwhile;
	itemSubTotal($itemParent,$sum);
	return $sum;
}

function itemUnitName($itemParent){
	global $db;
	$unit='';
	$q = $db->select('dqc','*',array('itemParent'=>$itemParent));
	while($r = $db->fetch_array($q)):
		if( $r['itemUnit'] ){
			$unit = $r['itemUnit'];
			break;
		}
		else
			$unit = itemUnitName($r['id']);
	endwhile;
	if( empty($unit) ){
		$unit = $db->getValue('dqc','itemUnit',array('id'=>$itemParent));
	}
	return $unit;
}

$proj_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$qEatID = $db->select('dqc_boq_ca','*',array('proj_id'=>$proj_id,'sig_type'=>'ca'));
$rEatID = $db->fetch_array($qEatID);
$prepared_other = $rEatID['prepared_other'];
$prepared_id = $rEatID['prepared_id'];
$prepared_by = $rEatID['prepared_by'];
$prepared_by_title = $rEatID['prepared_by_title'];

$checked_other = $rEatID['checked_other'];
$checked_id = $rEatID['checked_id'];
$checked_by = $rEatID['checked_by'];
$checked_by_title =  $rEatID['checked_by_title'];

$noted_other = $rEatID['noted_other'];
$noted_id = $rEatID['noted_id'];
$noted_by = $rEatID['noted_by'];
$noted_by_title = $rEatID['noted_by_title'];

$reviewed_other = $rEatID['reviewed_other'];
$reviewed_id = $rEatID['reviewed_id'];
$reviewed_by = $rEatID['reviewed_by'];
$reviewed_by_title = $rEatID['reviewed_by_title'];

$approved_other = $rEatID['approved_other'];
$approved_id = $rEatID['approved_id'];
$approved_by = $rEatID['approved_by'];
$approved_by_title = $rEatID['approved_by_title'];

$overAllEDC = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('proj_id'=>$proj_id));
if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id,'sig_type'=>'ca')) ){
	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id,'mobilization'=>NULL)) ){
		$mobile = $db->getValue('indirect_cost','mobil',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	}
	else{
		$mobile=$rEatID['mobilization'];
	}

	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id,'overhead'=>NULL)) ){
		$ocm = $db->getValue('indirect_cost','ocm',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	}
	else{
		$ocm = $rEatID['overhead'];
	}

	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id,'contractor'=>NULL)) ){
		$prft = $db->getValue('indirect_cost','profit',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	}
	else{
		$prft = $rEatID['contractor'];
	}
	$vat_declare = $rEatID['vat'];
}
else{
	$mobile = $db->getValue('indirect_cost','mobil',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	$ocm = $db->getValue('indirect_cost','ocm',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	$prft = $db->getValue('indirect_cost','profit',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	$vat_declare=5;
}
$owner = $db->getValue('project prj, project_client pc','pc_name',array('prj.proj_id'=>$proj_id),'AND pc.pc_id=prj.pc_id');
$location = $db->getValue('project','proj_location',array('proj_id'=>$proj_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Cost Analysis Print</title>
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
	<style type="text/css">
		body {
			background: white;
			/*background: rgb(204,204,204);*/
			font-size: 12px;
			font-family: Tahoma;
		}
		table{border-collapse: collapse;}
		page[size="ltr"] {
			background: white;
			/*width: 21.6cm;
			height: 29.7cm;
			display: block;
			margin: 0 auto;
			margin-bottom: 0.5cm;
			box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);*/
		}
		@media print {
			body, page[size="ltr"] {
				margin: 0;
				box-shadow: 0;color:red;
				-webkit-print-color-adjust: exact;
			}
    .pagebreak {
        clear: both;
        page-break-after: always;
    }
		}
		.spce {
		line-height: 150%;
		}
		.titlehead{background-color:red !important; font-weight: bold;}
		.padright{padding-right: 3px;}
	</style>
	<style type="text/css">
		#hName{font-size: 14px; font-family: Tahoma;font-weight: bolder;}
		#hAddress{font-size: 9px; font-family: Tahoma; line-height: 14px;}
		#DTitle{font-size: 14px; font-family: Tahoma;}
	</style>
	<link href="../css/printerfoot.css" rel="stylesheet">
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div align="center">
<?php
$ca_count=0;
if ( isset($_POST['btnPrint']) ) {
	$checkAll = (isset($_POST['checkAll'])) ? $_POST['checkAll'] : '';
	$chkprint = (isset($_POST['chkprint'])) ? $_POST['chkprint'] : '';
	$arrPrint = is_array($chkprint) ? $chkprint : array();
	$_SESSION['ca_print']=$arrPrint;
	$ca_all = count($arrPrint);
	foreach($arrPrint as $selected_caid):
		$qca = $db->select('cost_analysis','*',array('proj_id'=>$proj_id,'ca_id'=>$selected_caid));
		while($rca = $db->fetch_array($qca)):
		$ca_id = $rca['ca_id'];
		$ca_count++;
		$subItemID=$rca['sub_item'];
		$itemQ = $db->select('dqc','*',array('id'=>$subItemID));
		$itemDetail = $db->fetch_array($itemQ);
		$itemBaseID = (isset($itemDetail['itemBase'])) ? $itemDetail['itemBase'] : '';
		$itemBase = $db->getValue('dqc','itemNo',array('id'=>$itemBaseID));
		$itemBaseDesc = $db->getValue('dqc','itemDesc',array('id'=>$itemBaseID));
		$itemNo = (isset($itemDetail['itemBase'])) ? $itemDetail['itemNo'] : '';
		$itemNoDesc = (isset($itemDetail['itemBase'])) ? $itemDetail['itemDesc'] : '';
		$estimatedQuantity = itemTotal($subItemID);
		$itemUnit = itemUnitName($subItemID);
	?>
<page size="ltr">
	<table width="90%" border="0" align="center">
		<thead>
			<tr>
				<td>
				<table width="100%" border="1" align="center">
					<tr>
						<td align="center">
							<table width="100%" border="0" align="center">
								<tr>
									<td width="160" align="right"><img src="../img/header-logo.jpg" width="90" height="90"></td>
									<td align="center">
										<div id="hName">PHILKONSTRAK DEVELOPMENT CORPORATION</div>
										<div id="hAddress">
											<div>Design &bull; Estimate &bull; Construct &bull; Develop</div>
											<div>Door 3 MGR Building, Apitong Street, Sunrise Village Extension Pardo, Cebu City</div>
											<div>Tel / Fax # (Main Office) (032) 236-0992; 412-9907; Cell# 0933-453-3109; 0932-904-2475</div>
											<div>(Bohol Coordinating Office) (038) 509- 9204; 544-0271 Cell# 0917-303-5874</div>
											<div>E-mail Add: <a href="#">philkonstrak@hotmail.com</a>; <a href="#">philkonstrakdevtcorp@gmail.com</a></div>
											<div>Website: www.philkonstrak.com</div>
										</div>
									</td>
									<td width="115">&nbsp;</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="center"><div id="DTitle"><strong>Detailed Unit Price Analysis</strong></div></td>
					</tr>
				</table>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td height="500" valign="top">
					<div>&nbsp;</div>
					<table width="100%" class="spce" border="0" cellpadding="0" cellspacing="0" style="font-size:12px;">
						<tr>
							<th width="110px" height="20" valign="top" align="left">Project:</th>
							<td height="25">&nbsp;</td>
							<td height="25" valign="top"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id));?></td>
						</tr>
						<tr>
							<th height="20" valign="top" align="left">Location:</th>
							<td valign="top">&nbsp;</td>
							<td valign="top"><?php echo $location?></td>
						</tr>
						<tr>
							<th height="20" valign="top" align="left">Owner:</th>
							<td>&nbsp;</td>
							<td valign="top"><?php echo $owner?></td>
						</tr>
						<tr>
							<th height="20" align="left">Item No:</th>
							<td>&nbsp;</td>
							<td><?php echo $itemBase.'. '.$itemBaseDesc;?></td>
						</tr>
						<tr>
							<th height="20" align="left">Sub-Item No:</th>
							<td>&nbsp;</td>
							<td><?php echo $itemNo.'. '.$itemNoDesc;?></td>
						</tr>
						<tr>
							<th height="20" align="left">Quantity:</th>
							<td>&nbsp;</td>
							<td><?php echo $estimatedQuantity?></td>
						</tr>
						<tr>
							<th height="20" align="left">Unit:</th>
							<td>&nbsp;</td>
							<td><?php echo $itemUnit?></td>
						</tr>
					</table>
					<table class="spce" width="100%" border="0" style="font-size:12px">
						<tr style="background-color:#fabf8f !important">
							<td><div align="left"><strong>A. Material</strong></div></td>
						</tr>
						<tr>
							<td>
								<table class="spce" width="100%" border="1">
									<tr>
										<th style="width:60%"><div align="left">Description</div></th>
										<th style="width:10%"><div align="center">Qty</div></th>
										<th style="width:8%"><div align="center">Unit</div></th>
										<th style="width:11%" class="padright"><div align="right">Unit Cost</div></th>
										<th style="width:11%" class="padright"><div align="right">Amount</div></th>
									</tr>
									<?php
									$materialCost=0;
									$qMaterialGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'materials'));
									while($rMaterialGroup = $db->fetch_array($qMaterialGroup)):
										$countDetail=0;
										$qMaterialDetail = $db->select('cag_detail','*',array('cag_id'=>$rMaterialGroup['cag_id']));
										while($rMaterialDetail = $db->fetch_array($qMaterialDetail)):
											$rate = $db->getValue('elm_rate','itemRate',array('id'=>$rMaterialDetail['element_id']));
											$elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rMaterialDetail['element_id']));
											$materialCost += $rate * $rMaterialDetail['quantity'];
									?>
									<tr>
										<td style="padding-left:7px;"><?php echo $elementDesc;?></td>
										<td><div align="center"><?php echo functions::formatMoney($rMaterialDetail['quantity'])?></div></td>
										<td><div align="center"><?php echo $rMaterialDetail['unit']?></div></td>
										<td class="padright"><div align="right"><?php echo functions::formatMoney($rate)?></div></td>
										<td class="padright"><div align="right"><?php echo functions::formatMoney($rate * $rMaterialDetail['quantity'])?></div></td>
									</tr>
									<?php
										endwhile;
									endwhile;
									?>
									<tr>
										<td colspan="5" class="padright"><div align="right">Material Cost: &nbsp;&nbsp;&nbsp;<strong><?php echo functions::formatMoney($materialCost)?></strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr style="background-color:#fabf8f !important">
							<td><div align="left"><strong>B. Equipment</strong></div></td>
						</tr>
						<tr>
							<td>
								<table class="spce" width="100%" border="1">
									<tr>
										<th style="width:60%"><div align="left">Description</div></th>
										<th style="width:10%"><div align="center">Qty</div></th>
										<th style="width:8%"><div align="center">Day/s</div></th>
										<th style="width:11%" class="padright"><div align="right">Rate/Day</div></th>
										<th style="width:11%" class="padright"><div align="right">Amount</div></th>
									</tr>
									<?php
									$equipmentCost=0;
									$qEquipmentGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'equipment'));
									while($rEquipmentGroup = $db->fetch_array($qEquipmentGroup)):
										$countDetail=0;
										$qEquipDetail = $db->select('cag_detail','*',array('cag_id'=>$rEquipmentGroup['cag_id']));
										while($rEquipDetail = $db->fetch_array($qEquipDetail)):
											$rate = $db->getValue('elm_rate','itemRate',array('id'=>$rEquipDetail['element_id']));
											$elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rEquipDetail['element_id']));
											$equipmentCost += $rate * $rEquipDetail['unitMen'] * $rEquipDetail['day'];
									?>
										<tr>
											<td style="padding-left:7px;"><?php echo $elementDesc;?></td>
											<td><div align="center"><?php echo functions::formatMoney($rEquipDetail['unitMen'])?></div></td>
											<td><div align="center"><?php echo functions::formatMoney($rEquipDetail['day'])?></div></td>
											<td class="padright"><div align="right"><?php echo functions::formatMoney($rate)?></div></td>
											<td class="padright"><div align="right"><?php echo functions::formatMoney($rate * $rEquipDetail['unitMen'] * $rEquipDetail['day'])?></div></td>
										</tr>
									<?php
										endwhile;
									endwhile;
									?>
									<tr>
										<td colspan="5" class="padright"><div align="right">Equipment Cost: &nbsp;&nbsp;&nbsp;<strong><?php echo functions::formatMoney($equipmentCost)?></strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr style="background-color:#fabf8f !important">
							<td><div align="left"><strong>C. Labor</strong></div></td>
						</tr>
						<tr>
							<td>
								<table class="spce" width="100%" border="1">
									<tr>
										<th style="width:60%"><div align="left">Description</div></th>
										<th style="width:10%"><div align="center">Qty</div></th>
										<th style="width:8%"><div align="center">Day/s</div></th>
										<th style="width:11%" class="padright"><div align="right">Rate/Day</div></th>
										<th style="width:11%" class="padright"><div align="right">Amount</div></th>
									</tr>
									<?php
									$laborCost=0;
									$qLaborGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'labor'));
									while($rLaborGroup = $db->fetch_array($qLaborGroup)):
										$countDetail=0;
										$qLaborDetail = $db->select('cag_detail','*',array('cag_id'=>$rLaborGroup['cag_id']));
										while($rLaborDetail = $db->fetch_array($qLaborDetail)):
											$rate = $db->getValue('elm_rate','itemRate',array('id'=>$rLaborDetail['element_id']));
											$elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rLaborDetail['element_id']));
											$laborCost += $rate * $rLaborDetail['unitMen'] * $rLaborDetail['day'];
									?>
										<tr>
											<td style="padding-left:7px;"><?php echo $elementDesc;?></td>
											<td><div align="center"><?php echo functions::formatMoney($rLaborDetail['unitMen'])?></div></td>
											<td><div align="center"><?php echo functions::formatMoney($rLaborDetail['day'])?></div></td>
											<td class="padright"><div align="right"><?php echo functions::formatMoney($rate)?></div></td>
											<td class="padright"><div align="right"><?php echo functions::formatMoney($rate * $rLaborDetail['unitMen'] * $rLaborDetail['day'])?></div></td>
										</tr>
									<?php
										endwhile;
									endwhile;
									?>
									<tr>
										<td colspan="5" class="padright"><div align="right">Labor Cost: &nbsp;&nbsp;&nbsp;<strong><?php echo functions::formatMoney($laborCost)?></strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<table class="spce" width="100%" border="1">
								<?php
								$totalEDC = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('sub_item'=>$subItemID));
								#$vat_declare = $vat;
								$vat_percent = ($vat_declare) ? ($vat_declare/100) : 0;
								$cost_mobile = ($mobile) ? ($mobile / 100) * $totalEDC : 0;
								$cost_ocm = ($ocm) ? ($ocm / 100) * $totalEDC : 0;
								$cost_prft = ($prft) ? ($prft / 100) * $totalEDC : 0;
								$totalIndirectCost = $cost_ocm + $cost_prft + $cost_mobile;
								$vat = ($totalIndirectCost + $totalEDC) * $vat_percent;
								?>
									<tr>
										<td colspan="4"><div align="right"><strong>Total Direct Cost (A+B+C)</strong></div></td>
										<td style="width:11%" class="padright"><div align="right"><strong><?php echo functions::formatMoney($totalEDC); ?></strong></div></td>
									</tr>
									<tr>
										<td colspan="5"><div align="left"><strong>Indirect Cost</strong></div></td>
									</tr>
									<tr>
										<td colspan="4"><div align="right">Mobilization/Demobilization (<?php echo $mobile?>% of Direct Cost)</div></td>
										<td class="padright"><div align="right"><strong><?php echo functions::formatMoney($cost_mobile); ?></strong></div></td>
									</tr>
									<tr>
										<td colspan="4"><div align="right">Overhead, Contengencies & Miscellaneous (<?php echo $ocm?>% of Direct Cost)</div></td>
										<td class="padright"><div align="right"><strong><?php echo functions::formatMoney($cost_ocm); ?></strong></div></td>
									</tr>
									<tr>
										<td colspan="4"><div align="right">Contractor's Profit (<?php echo $prft?>% of Direct Cost)</div></td>
										<td class="padright"><div align="right"><strong><?php echo functions::formatMoney($cost_prft); ?></strong></div></td>
									</tr>
									<tr>
										<td colspan="4"><div align="right"><strong>Total Indirect Cost</strong></div></td>
										<td class="padright"><div align="right"><strong><?php echo functions::formatMoney($totalIndirectCost); ?></strong></div></td>
									</tr>
									<tr>
										<td colspan="4"><div align="right">VAT (<?php echo $vat_declare;?>% of Direct Cost+Indirect Cost)</div></td>
										<td class="padright"><div align="right"><strong><?php echo functions::formatMoney($vat); ?></strong></div></td>
									</tr>
									<tr>
										<td colspan="4"><div align="right"><strong>Total Construction Cost</strong></div></td>
										<td class="padright"><div align="right"><strong><?php echo functions::formatMoney($totalEDC + $totalIndirectCost + $vat); ?></strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>

					<?php if($ca_all==$ca_count){ ?>
					<table width="100%" border="0" style="font-size: 12px;page-break-inside: avoid;">
						<tr>
							<td width="45%"><div align="center">Prepared By</div></td>
							<td width="10%"><div align="center">&nbsp;</div></td>
							<td width="45%"><div align="center">Reviewed By</div></td>
						</tr>
						<tr>
							<td height="50" valign="bottom">
								<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $prepared_by;?>&nbsp;</strong></div>
								<div align="center" class="desig"><?php echo $prepared_by_title;?></div>
							</td>
							<td><div align="center"></div></td>
							<td valign="bottom">
								<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $reviewed_by;?>&nbsp;</strong></div>
								<div align="center" class="desig"><?php echo $reviewed_by_title;?></div>
							</td>
						</tr>
						<tr><td colspan="3"><div style="padding-top:5px;"></div></td></tr>
						<tr>
							<td><div align="center">Checked By</div></td>
							<td><div align="center">&nbsp;</div></td>
							<td><div align="center">Approved By</div></td>
						</tr>
						<tr>
							<td height="50" valign="bottom">
								<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $checked_by;?>&nbsp;</strong></div>
								<div align="center" class="desig"><?php echo $checked_by_title;?></div>
							</td>
							<td><div align="center"></div></td>
							<td>
								<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $approved_by;?>&nbsp;</strong></div>
								<div align="center" class="desig"><?php echo $approved_by_title;?></div>
							</td>
						</tr>
						<tr><td colspan="3"><div style="padding-top:5px;"></div></td></tr>
						<tr>
							<td><div align="center">Noted By</div></td>
							<td><div align="center">&nbsp;</div></td>
							<td><div align="center">&nbsp;</div></td>
						</tr>
						<tr>
							<td height="50" valign="bottom">
								<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $noted_by;?>&nbsp;</strong></div>
								<div align="center" class="desig"><?php echo $noted_by_title;?></div>
							</td>
							<td></td>
							<td></td>
						</tr>
					</table>
					<?php } ?>
				</td>
			</tr>
		</tbody>	
	</table>
	<footer>
		<div align="right" style="font-size:12px;">18AED.FRM023.00-10/18</div>
	</footer>
	<p style="page-break-after: always;"></p>
</page>
<?php 
		endwhile;
	endforeach;
}
?>
</div>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="ca_list_print_all_select.php?pid=<?php echo functions::encode($proj_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>