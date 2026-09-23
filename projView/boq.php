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
$totalEquipment=0;$totalLabor=0;$totalMaterial=0;$totalDirectCost=0;$totalOCM=0;$totalProfit=0;$totalIndirectCost=0;$totalVat=0;$totalCost=0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
if($itemID=='jY'){
	$itemID=0;
	unset($_SESSION['itemID']);
}
elseif($itemID)
	$_SESSION['itemID']=$itemID;

$itemID=(isset($_SESSION['itemID'])) ? $_SESSION['itemID'] : 0;

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
	global $totalMaterial,$totalEquipment,$totalLabor,$totalDirectCost,$totalOCM,$totalProfit,$totalIndirectCost,$totalVat,$totalCost;
	$string='';
	$q = $db->select('dqc','*',array('proj_id'=>$proj_id,'itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
	while($r = $db->fetch_array($q)):
		$quantity='';
		$itemUnit='';
		$itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
		$opacity = ( $db->getValue('cost_analysis','count(*)',array('sub_item'=>$r['id'])) ) ? 'opacity:1' : "";
		$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
		$ca_id = $db->getValue('cost_analysis','ca_id',array('sub_item'=>$r['id']));

		if($level<=2){
			$it = itemTotal($r['id']);
			if( $it )
				$it = ($it<0) ? $it * -1 : $it;
			else
				$it = '';

			$quantity = ($r['total']) ? $r['total'] : $it;
			$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : itemUnitName($r['id']);

			if($level==2){
				$quantity = ($r['total']) ? $r['total'] : $it;
				$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : itemUnitName($r['id']);
				$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";     
			}

			$s = '';
			for($i=1; $i<=$level; $i++):
				$s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			endfor;

			$s .= $itemNo.'&nbsp;&nbsp;&nbsp;&nbsp;'.$itemDesc;

			$directCost = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('sub_item'=>$r['id']));
			if( $mobile = $db->getValue('cost_analysis','mobilization',array('sub_item'=>$r['id'])) ){
				;
			}
			else{
				$mobile = $db->getValue('indirect_cost','mobil',array(),' WHERE '.$db->clean($directCost). ' BETWEEN edc_from AND edc_to');
			}

			if( $ocm = $db->getValue('cost_analysis','overhead',array('sub_item'=>$r['id'])) ){
				;
			}
			else{
				$ocm = $db->getValue('indirect_cost','ocm',array(),' WHERE '.$db->clean($directCost). ' BETWEEN edc_from AND edc_to');
			}

			if( $prft = $db->getValue('cost_analysis','contractor',array('sub_item'=>$r['id'])) ){
				;
			}
			else{
				$prft = $db->getValue('indirect_cost','profit',array(),' WHERE '.$db->clean($directCost). ' BETWEEN edc_from AND edc_to');
			}

			$cost_mobile = ($mobile) ? ($mobile / 100) * $directCost : 0;
			$cost_ocm = ($ocm) ? ($ocm / 100) * $directCost : 0;
			$cost_prft = ($prft) ? ($prft / 100) * $directCost : 0;
			$indirectCost = $cost_ocm + $cost_prft + $cost_mobile;
			$vat_declare = $db->getValue('cost_analysis','vat',array('sub_item'=>$r['id']));
			$vat_percent = ($vat_declare) ? ($vat_declare/100) : 0;

			$totalIndirectCost += $indirectCost;
			
			$totalDirectCost += $directCost;
			$vat = ($directCost + $indirectCost) * $vat_percent;

			$cost = round($directCost + $indirectCost + $vat,2);
			$totalCost += $cost;
			$rate=0;
			if( is_numeric($quantity) && is_numeric($cost) ){
				if( ($quantity > 0) && ($cost > 0) )
					$rate = $cost / $quantity;
			}
			$string ='
			<tr>
				<td>&nbsp;</td>
				<td>'.$s.'</td>
				<td><div align="center">'.$itemUnit.'</div></td>
				<td><div align="right">'.$quantity.'</div></td>
				<td><div align="right">'.functions::formatMoney($rate,5).'</div></td>
				<td><div align="right"><strong>'.functions::formatMoney($cost).'</strong></div></td>
			</tr>
			';
			if($opacity)
				echo $string;
			disp($proj_id,$r['id'],$level + 1);
		}
	endwhile;
}
$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;

$qEatID = $db->select('dqc_boq_ca','*',array('proj_id'=>$p_id,'sig_type'=>'boq'));
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>BOQ</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	<style>
	a {
		opacity: 0.2;
		filter: alpha(opacity=20); /* For IE8 and earlier */
	}
	a:hover {
		opacity: 1.0;
		filter: alpha(opacity=100); /* For IE8 and earlier */
	}
	</style>
</head>
<body>
<div id="spinner"></div>
<!-- body content: start here-->
<div class="row-fluid">
	<form method="post">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>BOQ</h2>
			</div>
			<div class="box-content">
				<div align="right">
					<a id="boqrint" class="thickbox btn btn-info" style="cursor:pointer;opacity: 1;" title="Print Detail" data-rel="tooltip" onclick="showThis(this.id,'boq_print.php?pid=<?php echo functions::encode($p_id);?>&itm=<?php echo functions::encode($itemID)?>','BOQ Print','1')"><i class="halflings-icon white print"></i></a>
				</div>
				<div>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$p_id));?></strong></div><br><br>
				<select name="selParentItem" id="selParentItem" style="width:400px;" onChange="itemSel(this.value)">
					<option value="all">-- All Item --</option>
					<?php
					$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
					$q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0),$orderBy);
					while($r = $db->fetch_array($q)):?>
					<option value="<?php echo functions::encode($r['id'])?>" <?php if($itemID==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].'. '.$r['itemDesc']?></option>
					<?php endwhile;?>
				</select>
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="5%" valign="middle" scope="col"><div align="center">Item No.</div></th>
							<th width="10%" valign="middle" scope="col"><div align="left">Description</div></th>
							<th width="3%" valign="middle" scope="col"><div align="center">Unit</div></th>
							<th width="3%" valign="middle" scope="col"><div align="right">Quantity</div></th>
							<th width="3%" valign="middle" scope="col"><div align="right">Rate</div></th>
							<th width="3%" scope="col"><div align="right">Amount</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo'; 
						if($itemID)
							$qList = $db->select('dqc','*',array('id'=>$itemID,'proj_id'=>$p_id),$orderBy);
						else 
							$qList = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0),$orderBy);
						while($rList = $db->fetch_array($qList)):
						?>
						<tr>
							<td><div align="center"><?php echo $rList['itemNo']?></div></td>
							<td width="25%" ><strong><?php echo $rList['itemDesc']?></strong></td>
							<td><div align="center"><strong><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : ''?></strong></div></td>
							<td><div align="right"><strong><?php echo ($rList['total']) ? $rList['total'] : '';?></strong></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php disp($p_id,$rList['id'],1);?>
						<?php endwhile;?>
						<tr>
							<td colspan="6">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="5"><div align="right"><strong>Total Estimated Project Cost</strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
						</tr>
					</tbody>
				</table><p>&nbsp;</p>
				<table width="100%" border="0" style="font-size: 12px;">
					<tr>
						<td width="45%"><div align="center">Prepared By</div></td>
						<td width="10%"><div align="center">&nbsp;</div></td>
						<td width="45%"><div align="center">Reviewed By</div></td>
					</tr>
					<tr>
						<td height="55" valign="bottom">
							<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $prepared_by;?>&nbsp;</strong></div>
							<div align="center" class="desig"><?php echo $prepared_by_title;?></div>
						</td>
						<td><div align="center">&nbsp;</div></td>
						<td valign="bottom">
							<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $reviewed_by;?>&nbsp;</strong></div>
							<div align="center" class="desig"><?php echo $reviewed_by_title;?></div>
						</td>
					</tr>
					<tr><td colspan="3"><div style="padding-top:15px;">&nbsp;</div></td></tr>
					<tr>
						<td><div align="center">Checked By</div></td>
						<td><div align="center">&nbsp;</div></td>
						<td><div align="center">Approved By</div></td>
					</tr>
					<tr>
						<td height="55" valign="bottom">
							<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $checked_by;?>&nbsp;</strong></div>
							<div align="center" class="desig"><?php echo $checked_by_title;?></div>
						</td>
						<td><div align="center">&nbsp;</div></td>
						<td>
							<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $approved_by;?>&nbsp;</strong></div>
							<div align="center" class="desig"><?php echo $approved_by_title;?></div>
						</td>
					</tr>
					<tr><td colspan="3"><div style="padding-top:15px;">&nbsp;</div></td></tr>
					<tr>
						<td><div align="center">Noted By</div></td>
						<td><div align="center">&nbsp;</div></td>
						<td><div align="center">&nbsp;</div></td>
					</tr>
					<tr>
						<td height="55" valign="bottom">
							<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $noted_by;?>&nbsp;</strong></div>
							<div align="center" class="desig"><?php echo $noted_by_title;?></div>
						</td>
						<td></td>
						<td></td>
					</tr>
				</table>
			</div>
		</div><!--/span-->
	</form>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>function itemSel(PiEwgD){window.location="<?php echo functions::pageName()?>?pid=<?php echo functions::encode($p_id)?>&itemID="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>