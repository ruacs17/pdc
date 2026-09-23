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
$totalEquipment=0;
$totalLabor=0;
$totalMaterial=0;
$totalDirectCost=0;
$totalOCM=0;
$totalProfit=0;
$totalIndirectCost=0;
$totalVat=0;
$totalCost=0;
$totalUnitCost=0;
$totalMobil=0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$proj_id = $p_id;
$qEatID = $db->select('dqc_boq_ca','*',array('proj_id'=>$proj_id,'sig_type'=>'pow'));
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
	$qEatID = $db->select('dqc_boq_ca','*',array('proj_id'=>$proj_id,'sig_type'=>'ca'));
	$rEatID = $db->fetch_array($qEatID);
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
function numberToRoman($number) {
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}
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
	return $unit;
}

function disp($proj_id,$parentItem,$level){
	global $db;
	global $totalMaterial,$totalEquipment,$totalLabor,$totalDirectCost,$totalOCM,$totalMobil,$totalProfit,$totalIndirectCost,$totalVat,$totalCost,$mobile,$ocm,$prft,$vat_declare,$totalUnitCost;
	$string='';
	$q = $db->select('dqc','*',array('proj_id'=>$proj_id,'itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');

	while($r = $db->fetch_array($q)):
		$quantity='';
		$itemUnit='';
		$itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
		$opacity = ( $db->getValue('cost_analysis','count(*)',array('sub_item'=>$r['id'])) ) ? 'opacity:1' : "";
		$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";

		if($level<=2){
			$it = itemTotal($r['id']);
			if( $it )
				$it = ($it<0) ? $it * -1 : $it;
			else
				$it = '';
			$qnty = ($r['total']) ? $r['total'] : $it;
			$quantity = ($r['total']) ? functions::formatMoney($r['total']) : functions::formatMoney($it);
			$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : itemUnitName($r['id']);

			if($level==2){
				$quantity = ($r['total']) ? functions::formatMoney($r['total']) : functions::formatMoney($it);
				$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : itemUnitName($r['id']);
				$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
			}
			$s = '';
			for($i=1; $i<=$level; $i++):
				$s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			endfor;

			//$arrItm = explode('.', $itemNo);
			// $itmRmn = '';$countItm=1;
			// foreach($arrItm as $sItm):
			// 	$itmRmn .= numberToRoman($sItm);
			// 	if(count($arrItm)>$countItm)
			// 		$itmRmn .='.';
			// 	$countItm++;
			// endforeach;
			// $s .= $itmRmn.'&nbsp;&nbsp;'.$itemDesc;
			$s .= $itemNo.'&nbsp;&nbsp;'.$itemDesc;

			$directCost = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('sub_item'=>$r['id']));
			$cost_mobile = ($mobile) ? ($mobile / 100) * $directCost : 0;
			$cost_ocm = ($ocm) ? ($ocm / 100) * $directCost : 0;
			$cost_prft = ($prft) ? ($prft / 100) * $directCost : 0;
			$indirectCost = $cost_ocm + $cost_prft + $cost_mobile;
			$vat_percent = ($vat_declare) ? ($vat_declare/100) : 0;


			$totalIndirectCost += $indirectCost;

			$equipment = $db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$r['id'],'type'=>'equipment'));
			$totalEquipment += $equipment;
			$material = $db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$r['id'],'type'=>'materials'));
			$totalMaterial += $material;
			$labor = $db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$r['id'],'type'=>'labor'));
			$totalLabor += $labor;

			$directCost = $db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$r['id']));
			$totalDirectCost += $directCost;
			$vat = ($directCost + $indirectCost) * $vat_percent;

			$totalMobil += $cost_mobile;
			$totalOCM += $cost_ocm;
			$totalProfit += $cost_prft;
			$totalVat += $vat;

			$cost = $directCost + $indirectCost + $vat;
			$totalCost += $cost;
			$totalUnitCost += $unitCost = ($cost && $qnty) ? $cost / $qnty : 0;
			$string ='
			<tr>
				<td>&nbsp;</td>
				<td>'.$s.'</td>
				<td><div align="center">'.$itemUnit.'</div></td>
				<td><div align="right" class="padright">'.$quantity.'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($material).'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($equipment).'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($labor).'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($directCost).'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($cost_mobile).'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($cost_ocm).'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($cost_prft).'</div></td>
				<td><div align="right" class="padright">'.functions::formatMoney($vat).'</div></td>
				<td><div align="right">'.functions::formatMoney($unitCost).'</div></td>
				<td><div align="right" class="padright"><strong>'.functions::formatMoney($cost).'</strong></div></td>
			</tr>
			';
			if($opacity)
			echo $string;
			disp($proj_id,$r['id'],$level + 1);
		}
	endwhile;
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
$arrPrint=array($itemID);
if ( isset($_POST['btnPrint']) ) {
	$checkAll = (isset($_POST['checkAll'])) ? $_POST['checkAll'] : '';
	$chkprint = (isset($_POST['chkprint'])) ? $_POST['chkprint'] : '';
	$arrPrint = is_array($chkprint) ? $chkprint : array();
	$_SESSION['pow_print']=$arrPrint;
	$count_print = count($arrPrint);
	$printing=0;
}
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
						<td align="center"><div id="DTitle"><strong>PROGRAM OF WORKS</strong></div></td>
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
							<th width="90x" height="20" valign="top" align="left">Project:</th>
							<td>&nbsp;</td>
							<td valign="top"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id));?></td>
						</tr>
						<tr>
							<th valign="top" align="left" height="20">Location:</th>
							<td valign="top">&nbsp;</td>
							<td valign="top"><?php echo $location?></td>
						</tr>
						<tr>
							<th valign="top" align="left" height="20">Owner:</th>
							<td>&nbsp;</td>
							<td valign="top"><?php echo $owner?></td>
						</tr>
					</table>
					<table width="100%" align="center" cellpadding="0" cellspacing="0" border="1" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="3%" scope="col"><div align="center">Item No.</div></th>
								<th width="35%" scope="col"><div align="left">Description</div></th>
								<th width="3%" scope="col"><div align="center">Unit</div></th>
								<th width="3%" scope="col"><div align="center">Quantity</div></th>
								<th width="3%" scope="col"><div align="center">Material</div></th>
								<th width="3%" scope="col"><div align="center">Equipment</div></th>
								<th width="3%" scope="col"><div align="center">Labor</div></th>
								<th width="3%" scope="col"><div align="center">Amount</div></th>
								<th width="3%" scope="col"><div align="center">Mobilztn<br><i>(<?php echo $mobile?>%)</i></div></th>
								<th width="3%" scope="col"><div align="center">OCM<br><i>(<?php echo $ocm?>%)</i></div></th>
								<th width="3%" scope="col"><div align="center">Profit<br><i>(<?php echo $prft?>%)</i></div></th>
								<th width="3%" rowspan="2" scope="col"><div align="center">VAT<br><i>(<?php echo $vat_declare?>%)</i></div></th>
							<th width="3%" scope="col"><div align="center">Unit Cost</div></th>
								<th width="3%" scope="col"><div align="center">Total</div></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$overAllMaterial=0;$overAllEquipment=0;$overAllLabor=0;$overAllDirectCost=0;$overAllMobil=0;$overAllOCM=0;$overAllProfit=0;$overAllVat=0;$overAllCost=0;$overAllUnitCost=0;
							foreach($arrPrint as $selected_id):
							$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo'; 
							$qList = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0,'id'=>$selected_id),$orderBy);
							while($rList = $db->fetch_array($qList)):
								$itmNo = $rList['itemNo'];
							?>
							<tr>
								<td><div align="center"><?php echo $rList['itemNo']?></div></td>
								<td width="25%"><strong><?php echo $rList['itemDesc']?></strong></td>
								<td><div align="right"><strong><?php echo ($rList['total']) ? $rList['total'] : '';?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : ''?></strong></div></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<?php
							disp($p_id,$rList['id'],1);
							endwhile;?>
							<tr>
								<td colspan="4"><div align="center">Subtotal for Item <?php echo $itmNo?></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalMaterial);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalEquipment);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalLabor);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalDirectCost);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalMobil);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalOCM);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalProfit);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalVat);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalUnitCost);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
							</tr>
							<?php
							$overAllMaterial+=$totalMaterial;$overAllEquipment+=$totalEquipment;$overAllLabor+=$totalLabor;$overAllDirectCost+=$totalDirectCost;$overAllMobil+=$totalMobil;$overAllOCM+=$totalOCM;$overAllProfit+=$totalProfit;$overAllVat+=$totalVat;$overAllCost+=$totalCost;$overAllUnitCost+=$totalUnitCost;
							$totalMaterial=0;$totalEquipment=0;$totalLabor=0;$totalDirectCost=0;$totalMobil=0;$totalOCM=0;$totalProfit=0;$totalVat=0;$totalCost=0;
							?>
							<?php endforeach; ?>
							<tr>
								<td colspan="4"><div align="center">&nbsp;</div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllMaterial);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllEquipment);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllLabor);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllDirectCost);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllMobil);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllOCM);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllProfit);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllVat);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllUnitCost);?></strong></div></td>
								<td><div align="right" class="padright"><strong><?php echo functions::formatMoney($overAllCost);?></strong></div></td>
							</tr>
						</tbody>
					</table><p>&nbsp;</p>
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
				</td>
			</tr>
		</tbody>	
	</table>
	<footer>
		<div align="right" style="font-size:12px;display:none;">18AED.FRM023.00-10/18</div>
	</footer>
	<p style="page-break-after: always;"></p>
</page>
</div>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	<?php if(empty($itemID)){?>setTimeout("closePrint()",200);<?php } ?>
});
function closePrint(){
	window.location="pow_print_select.php?pid=<?php echo functions::encode($proj_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>