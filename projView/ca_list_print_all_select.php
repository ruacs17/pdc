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
unset($_SESSION['temp_search']);
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

$count=0;
$subItemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0; 
$delGroup = (isset($_REQUEST['delcag_id']) && !empty($_REQUEST['delcag_id']) ) ? functions::decode($_REQUEST['delcag_id']) : 0; 
if($delGroup){
	$db->delete('ca_group',array('cag_id'=>$delGroup));
}
if( $subItemID ){
	if($p_id && $subItemID){
		if( $db->getValue('cost_analysis','count(*)',array('proj_id'=>$p_id,'sub_item'=>$subItemID))==0 ){
			$db->insert('cost_analysis',array('proj_id'=>$p_id,'sub_item'=>$subItemID));
		}
	}
}
$qDefCharge = $db->select('cost_analysis','*',array('sub_item'=>$subItemID));
$rDC = $db->fetch_array($qDefCharge);

$qEatID = $db->select('dqc_boq_ca','*',array('proj_id'=>$p_id,'sig_type'=>'ca'));
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

$overAllEDC = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('proj_id'=>$p_id));
if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$p_id,'sig_type'=>'ca')) ){
	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$p_id,'mobilization'=>NULL)) ){
		$mobile = $db->getValue('indirect_cost','mobil',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	}
	else{
		$mobile=$rEatID['mobilization'];
	}

	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$p_id,'overhead'=>NULL)) ){
		$ocm = $db->getValue('indirect_cost','ocm',array(),' WHERE '.$db->clean($overAllEDC). ' BETWEEN edc_from AND edc_to');
	}
	else{
		$ocm = $rEatID['overhead'];
	}

	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$p_id,'contractor'=>NULL)) ){
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
$owner = $db->getValue('project prj, project_client pc','pc_name',array('prj.proj_id'=>$p_id),'AND pc.pc_id=prj.pc_id');
$location = $db->getValue('project','proj_location',array('proj_id'=>$p_id));
// if (isset($_POST['btnPrint'])) {
// 	#print_r($_POST);
// 	echo $checkAll = (isset($_POST['checkAll'])) ? $_POST['checkAll'] : '';
// 	$chkprint = (isset($_POST['chkprint'])) ? $_POST['chkprint'] : '';
// 	$arrPrint = is_array($chkprint) ? $chkprint : array();
// 	#print_r($arrPrint);
// 	foreach($arrPrint as $indiPrint):
// 		echo $indiPrint;
// 		echo '<br>';
// 	endforeach;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Cost Analysis List</title>
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
	<style>
	a{
		opacity: 0.4;
		filter: alpha(opacity=20); /* For IE8 and earlier */
	}
	a:hover{
		opacity: 1.0;
		filter: alpha(opacity=100); /* For IE8 and earlier */
	}
	</style>

</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Program Of Works Print Select</h2>
		</div>
		<div class="box-content">
			<form method="post" action="ca_list_print_all.php?pid=<?php echo functions::encode($p_id)?>">
			<table width="99%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="12%">Project</td>
					<td width="2%">&nbsp;</td>
					<td width="86%"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$p_id));?></td>
				</tr>
				<tr>
					<td>Location</td>
					<td>&nbsp;</td>
					<td><?php echo $location?></td>
				</tr>
				<tr>
					<td>Owner</td>
					<td>&nbsp;</td>
					<td><?php echo $owner ?></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<table width="99%" class="table table-striped table-bordered table-hover" style="font-size:12px;" border="0" cellpadding="0" cellspacing="0">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="7%">Item No</th>
						<th width="20%">Item of Work</th>
						<th width="7%">Sub-Item No.</th>
						<th width="40%">Sub-Item of Work</th>
						<th width="7%"><div align="center">Estimated Quantities</div></th>
						<th width="7%"><div align="center">Unit</div></th>
						<th width="5%"><div align="center">Select</div></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><div align="center"><input type="checkbox" name="checkAll" id="checkAll" value="all"></div></td>
					</tr>
			<?php
			$countPrint=1;
			if($subItemID)
				$qca = $db->select('cost_analysis','*',array('sub_item'=>$subItemID));
			else
				$qca = $db->select('cost_analysis','*',array('proj_id'=>$p_id),'ORDER BY sub_item'); 
			while($rca = $db->fetch_array($qca)):
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
				$ca_id=$rca['ca_id'];

				#$ca_id = $db->getValue('cost_analysis','ca_id',array('proj_id'=>$p_id,'sub_item'=>$subItemID));

				#deleting no cag_detail
				$qGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id));
				while($rGroup = $db->fetch_array($qGroup)):
					$countDetail=0;
					$qDetail = $db->select('cag_detail','*',array('cag_id'=>$rGroup['cag_id']));
					while($rDetail = $db->fetch_array($qDetail)):
						$countDetail++;
					endwhile;
					if($countDetail==0)
						$db->delete('ca_group',array('cag_id'=>$rGroup['cag_id'],'ca_id'=>$ca_id));
				endwhile;
			?>
					<tr id="rw<?php echo $ca_id; ?>" >
						<td><div align="left"><?php echo $itemBase;?></div></td>
						<td><div align="left"><?php echo $itemBaseDesc;?></div></td>
						<td><div align="left"><?php echo $itemNo;?></div></td>
						<td><div align="left"><?php echo $itemNoDesc;?></div></td>
						<td><div align="right"><?php echo $estimatedQuantity?></div></td>
						<td><div align="center"><?php echo $itemUnit?></div></td>
						<td><div align="center"><input type="checkbox" class="chkprint" name="chkprint[<?php echo $ca_id; ?>]" id="chkprint[<?php echo $ca_id; ?>]" value="<?php echo $ca_id; ?>"></div></td>
					</tr>
			<?php endwhile;?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><div align="center"><input type="submit" class="btn btn-small btn-primary" name="btnPrint" id="btnPrint" value="Print"></div></td>
					</tr>
				</tbody>
			</table>
			</form>
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
<script type="text/javascript">
$(document).ready(function(){
	$("#checkAll").change(function() {
		if (this.checked) {
			$(".chkprint").each(function() {
				this.checked=true;
			});
		} else {
			$(".chkprint").each(function() {
				this.checked=false;
			});
		}
	});

	$(".chkprint").click(function () {
		if ($('.chkprint:checked').length == $('.chkprint').length){
			$('#checkAll').prop('checked',true);
		}
		else {
			$('#checkAll').prop('checked',false);
		}
	});
});

</script>
</body>
</html>