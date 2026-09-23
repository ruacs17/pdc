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
$ownerSubItem = (isset($_REQUEST['ownersubitem']) && !empty($_REQUEST['ownersubitem']) ) ? functions::decode($_REQUEST['ownersubitem']) : 0; 
$ownerPID = (isset($_REQUEST['ownerpid']) && !empty($_REQUEST['ownerpid']) ) ? functions::decode($_REQUEST['ownerpid']) : 0; 
$subItemID = (isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$subItemIDReq = $subItemID;
$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0; 

$qDefCharge = $db->select('cost_analysis','*',array('sub_item'=>$subItemID));
$rDC = $db->fetch_array($qDefCharge);

$owner = $db->getValue('project prj, project_client pc','pc_name',array('prj.proj_id'=>$p_id),'AND pc.pc_id=prj.pc_id');
$location = $db->getValue('project','proj_location',array('proj_id'=>$p_id));
if(isset($_POST['btnCopy'])){
	$owner_ca_id = $db->getValue('cost_analysis','ca_id',array('proj_id'=>$ownerPID,'sub_item'=>$ownerSubItem));
	if($owner_ca_id){
		$db->delete('ca_group',array('ca_id'=>$owner_ca_id));

		$qca = $db->select('cost_analysis','*',array('proj_id'=>$p_id,'sub_item'=>$subItemID),'ORDER BY sub_item'); 
		while($rca = $db->fetch_array($qca)):
			$ca_id = $rca['ca_id'];
			
			$qMaterialGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'materials'));
			while($rMaterialGroup = $db->fetch_array($qMaterialGroup)):
				$owner_cag_id = $db->insert('ca_group',array('ca_id'=>$owner_ca_id,'title'=>$rMaterialGroup['title'],'type'=>$rMaterialGroup['type']));

				$qMaterialDetail = $db->select('cag_detail','*',array('cag_id'=>$rMaterialGroup['cag_id']));
				while($rMaterialDetail = $db->fetch_array($qMaterialDetail)):
					$db->insert('cag_detail',array('cag_id'=>$owner_cag_id,'element_id'=>$rMaterialDetail['element_id'],'unitMen'=>$rMaterialDetail['unitMen'],'day'=>$rMaterialDetail['day'],'unit'=>$rMaterialDetail['unit'],'quantity'=>$rMaterialDetail['quantity']));
				endwhile;

			endwhile;
			
			$qEquipmentGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'equipment'));
			while($rEquipmentGroup = $db->fetch_array($qEquipmentGroup)):

				$owner_cag_id = $db->insert('ca_group',array('ca_id'=>$owner_ca_id,'title'=>$rEquipmentGroup['title'],'type'=>$rEquipmentGroup['type']));

				$qEquipDetail = $db->select('cag_detail','*',array('cag_id'=>$rEquipmentGroup['cag_id']));
				while($rEquipDetail = $db->fetch_array($qEquipDetail)):
					$db->insert('cag_detail',array('cag_id'=>$owner_cag_id,'element_id'=>$rEquipDetail['element_id'],'unitMen'=>$rEquipDetail['unitMen'],'day'=>$rEquipDetail['day'],'unit'=>$rEquipDetail['unit'],'quantity'=>$rEquipDetail['quantity']));
				endwhile;

			endwhile;

			$qLaborGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'labor'));
			while($rLaborGroup = $db->fetch_array($qLaborGroup)):
				$owner_cag_id = $db->insert('ca_group',array('ca_id'=>$owner_ca_id,'title'=>$rLaborGroup['title'],'type'=>$rLaborGroup['type']));
				
				$qLaborDetail = $db->select('cag_detail','*',array('cag_id'=>$rLaborGroup['cag_id']));
				while($rLaborDetail = $db->fetch_array($qLaborDetail)):
					$db->insert('cag_detail',array('cag_id'=>$owner_cag_id,'element_id'=>$rLaborDetail['element_id'],'unitMen'=>$rLaborDetail['unitMen'],'day'=>$rLaborDetail['day'],'unit'=>$rLaborDetail['unit'],'quantity'=>$rLaborDetail['quantity']));
				endwhile;

			endwhile;

		endwhile;
		$_SESSION['notif_success']='Items successfully copied!';
		functions::sendTo(functions::pageName().'?ownerpid='.functions::encode($ownerPID).'&ownersubitem='.functions::encode($ownerSubItem).'&pid='.functions::encode($p_id).'&itemID='.functions::encode($subItemIDReq));
		die();
	}
	else{
		$_SESSION['notif_warning']='Copying Failed!';
	}
}
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Copying from existing Program Of Works</h2>
		</div>
		<div class="box-content">
			<table width="99%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="12%">Project</td>
					<td width="2%">&nbsp;</td>
					<td width="86%">
						<select name="selProj" id="selProj" data-rel="chosen" style="width:1224px;font-size:12px;" onChange="projSel(this.value)">
							<option value="">--Select Project--</option>
							<?php $qProj = $db->select('project','*',array('project'=>'1'),'ORDER BY proj_name');
							while($rProj = $db->fetch_array($qProj)):
							?>
							<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
							<?php endwhile;?>
						</select>
					</td>
				</tr>
				<tr>
					<td height="30">Location</td>
					<td>&nbsp;</td>
					<td><?php echo $location?></td>
				</tr>
				<tr>
					<td height="30">Owner</td>
					<td>&nbsp;</td>
					<td><?php echo $owner ?></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Item of Work</td>
					<td>&nbsp;</td>
					<td>
						<select name="selca" id="selca" data-rel="chosen" style="width:1224px;font-size:12px;" onChange="caSel(this.value)">
							<option value="">--Select Item--</option>
							<?php
							$qcasel = $db->select('cost_analysis','*',array('proj_id'=>$p_id),'ORDER BY sub_item');
							while($rcasel = $db->fetch_array($qcasel)):
								$subItemIDSel=$rcasel['sub_item'];
								$itemQ = $db->select('dqc','*',array('id'=>$subItemIDSel));
								$itemDetail = $db->fetch_array($itemQ);
								$itemBaseID = (isset($itemDetail['itemBase'])) ? $itemDetail['itemBase'] : '';
								$itemBase = $db->getValue('dqc','itemNo',array('id'=>$itemBaseID));
								$itemBaseDesc = $db->getValue('dqc','itemDesc',array('id'=>$itemBaseID));
								$itemNo = (isset($itemDetail['itemBase'])) ? $itemDetail['itemNo'] : '';
								$itemNoDesc = (isset($itemDetail['itemBase'])) ? $itemDetail['itemDesc'] : '';
								$selName = $itemBase.'. '.$itemBaseDesc.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$itemNo.'. '.$itemNoDesc;
							?>
							<option value="<?php echo functions::encode($rcasel['sub_item'])?>" <?php if($subItemID==$rcasel['sub_item'])echo 'selected="selected"';?>><?php echo $selName;?></option>
							<?php endwhile;?>
						</select>
					</td>
				</tr>
			</table>
	
			<?php
			$countPrint=1;
			$qca = $db->select('cost_analysis','*',array('proj_id'=>$p_id,'sub_item'=>$subItemID),'ORDER BY sub_item');
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

				$ca_id = $db->getValue('cost_analysis','ca_id',array('proj_id'=>$p_id,'sub_item'=>$subItemID));

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
			<table width="99%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td height="50px" colspan="3">&nbsp;</td>
				</tr>
				<tr bgcolor="#FF0000">
					<td height="2px" colspan="3"></td>
				</tr>
				<tr>
					<td height="50px" colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td width="12%">Item No</td>
					<td width="2%">&nbsp;</td>
					<td width="86%"><?php echo $itemBase;?></td>
				</tr>
				<tr>
					<td>Item of Work</td>
					<td>&nbsp;</td>
					<td><?php echo $itemBaseDesc;?></td>
				</tr>
				<tr>
					<td>Sub-Item No.</td>
					<td>&nbsp;</td>
					<td><?php echo $itemNo;?></td>
				</tr>
				<tr>
					<td>Sub-Item of Work</td>
					<td>&nbsp;</td>
					<td><?php echo $itemNoDesc;?></td>
				</tr>
				<tr>
					<td>Estimated Quantities</td>
					<td>&nbsp;</td>
					<td><?php echo $estimatedQuantity?></td>
				</tr>
				<tr>
					<td>Unit</td>
					<td>&nbsp;</td>
					<td><?php echo $itemUnit?></td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" bgcolor="#F7F7F7">
						<table id="tblist" width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['ca_list_notif'])){echo 'table-bordered';} ?>" style="font-size:12px;">
							<tr bgcolor="#CCCCCC">
								<th width="33%" scope="col">A. Materials</th>
								<th width="9%" scope="col"><div align="right">Capabilites</div></th>
								<th width="9%" scope="col"><div align="right">Unit</div></th>
								<th width="9%" scope="col"><div align="right">Quantity</div></th>
								<th width="9%" scope="col"><div align="right">Unit Cost</div></th>
								<th width="9%" scope="col"><div align="right">Cost</div></th>
							</tr>
							<?php
							$qMaterialGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'materials'));
							while($rMaterialGroup = $db->fetch_array($qMaterialGroup)):
								$countDetail=0;
							?>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr id="rw<?php echo $rMaterialGroup['cag_id']?>">
								<td>
									<strong><?php echo $rMaterialGroup['title']?>&nbsp;&nbsp;</strong>
								</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
								<?php
								$qMaterialDetail = $db->select('cag_detail','*',array('cag_id'=>$rMaterialGroup['cag_id']));
								while($rMaterialDetail = $db->fetch_array($qMaterialDetail)):
									$rate = $db->getValue('elm_rate','itemRate',array('id'=>$rMaterialDetail['element_id']));
									$elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rMaterialDetail['element_id']));
								?>
							<tr>
								<td>&nbsp;&nbsp;&nbsp;<?php echo $elementDesc;?></td>
								<td><div align="right"><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></div></td>
								<td><div align="right"><?php echo $rMaterialDetail['unit']?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rMaterialDetail['quantity'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rate)?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rate * $rMaterialDetail['quantity'])?></div></td>
							</tr>
							<?php
								endwhile;
							endwhile;?>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="5"><div align="right">Total Material Cost</div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$subItemID,'type'=>'materials')))?></strong></div></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td colspan="6" height="40px">&nbsp;</td>
							</tr>
							<tr bgcolor="#CCCCCC">
								<th width="33%" scope="col">B. Equipment</th>
								<th width="9%" scope="col"><div align="right">Capabilites</div></th>
								<th width="9%" scope="col"><div align="right">No. of Units</div></th>
								<th width="9%" scope="col"><div align="right">No. of Days</div></th>
								<th width="9%" scope="col"><div align="right">Daily Rate</div></th>
								<th width="9%" scope="col"><div align="right">Cost</div></th>
							</tr>
							<?php
							$qEquipmentGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'equipment'));
							while($rEquipmentGroup = $db->fetch_array($qEquipmentGroup)):
								$countDetail=0;
							?>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr id="rw<?php echo $rEquipmentGroup['cag_id']?>">
								<td><strong><?php echo $rEquipmentGroup['title']?>&nbsp;&nbsp;</strong></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<?php
							$qEquipDetail = $db->select('cag_detail','*',array('cag_id'=>$rEquipmentGroup['cag_id']));
							while($rEquipDetail = $db->fetch_array($qEquipDetail)):
								$rate = $db->getValue('elm_rate','itemRate',array('id'=>$rEquipDetail['element_id']));
								$elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rEquipDetail['element_id']));
							?>
							<tr>
								<td>&nbsp;&nbsp;&nbsp;<?php echo $elementDesc;?></td>
								<td><div align="right"><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rEquipDetail['unitMen'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rEquipDetail['day'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rate)?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rate * $rEquipDetail['unitMen'] * $rEquipDetail['day'])?></div></td>
							</tr>
							<?php 
								endwhile;
						endwhile;?>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="5"><div align="right">Total Equipment Cost</div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$subItemID,'type'=>'equipment')))?></strong></div></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td colspan="6" height="40px">&nbsp;</td>
							</tr>
							<tr bgcolor="#CCCCCC">
								<th width="33%" scope="col">C. Labor</th>
								<th width="9%" scope="col"><div align="left">Capabilites</div></th>
								<th width="9%" scope="col"><div align="right">No. of Units</div></th>
								<th width="9%" scope="col"><div align="right">No. of Days</div></th>
								<th width="9%" scope="col"><div align="right">Daily Rate</div></th>
								<th width="9%" scope="col"><div align="right">Cost</div></th>
							</tr>
							<?php
							$qLaborGroup = $db->select('ca_group','*',array('ca_id'=>$ca_id,'type'=>'labor'));
							while($rLaborGroup = $db->fetch_array($qLaborGroup)):
								$countDetail=0;
							?>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr id="rw<?php echo $rLaborGroup['cag_id']?>">
								<td><strong><?php echo $rLaborGroup['title']?>&nbsp;&nbsp;</strong></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
								<?php
								$qLaborDetail = $db->select('cag_detail','*',array('cag_id'=>$rLaborGroup['cag_id']));
								while($rLaborDetail = $db->fetch_array($qLaborDetail)):
									$rate = $db->getValue('elm_rate','itemRate',array('id'=>$rLaborDetail['element_id']));
									$elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$rLaborDetail['element_id']));
								?>
							<tr>
								<td>&nbsp;&nbsp;&nbsp;<?php echo $elementDesc;?></td>
								<td><div align="right"><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rLaborDetail['unitMen'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rLaborDetail['day'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rate)?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rate * $rLaborDetail['unitMen'] * $rLaborDetail['day'])?></div></td>
							</tr>
							<?php 
								endwhile;
							endwhile;?>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="5"><div align="right">Total Labor Cost</div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($db->getValue('cost_analysis_report','sum(cost)',array('sub_item'=>$subItemID,'type'=>'labor')))?></strong></div></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td colspan="6" height="40px">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="5"><div align="right">Total Estimated Direct Cost</div></td>
								<td>
									<div align="right">
									<strong>
										<?php
										$totalEDC = $db->getValue('cost_analysis_report','if(sum(cost),sum(cost),0)',array('sub_item'=>$subItemID));
										echo functions::formatMoney($totalEDC);
										?>
									</strong>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<?php endwhile;?>
			<div style="padding-top:20px;"></div>
			<form method="post">
				<?php 
				if($subItemIDReq){
					$owner_ca_id = $db->getValue('cost_analysis','ca_id',array('proj_id'=>$ownerPID,'sub_item'=>$ownerSubItem));
					if( $db->getValue('ca_group','*',array('ca_id'=>$owner_ca_id))==0 ){
				?>
				<div align="center">
					<input type="submit" class="btn btn-primary btn-small" name="btnCopy" id="btnCopy" value="COPY this Item" onClick="return ask()">
					<div style="padding-top:20px;color:red"><strong>Be advised that you may only copy once! Please be certain of the item you want to duplicate.</strong></div>
				</div>
				<?php
					}
					else{
				?>
					<div align="center" style="padding-top:20px;color:red"><strong>Duplicating of Items can be made only once.</strong></div>
				<?php
					}
				}
				?>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
	function ask(){
		if(confirm('Do you want to copy this Item?'))
			return true;
		else
			return false; 
	}
	function projSel(PiEwgD){
		if(PiEwgD)
			window.location="<?php echo functions::pageName()?>?ownerpid=<?php echo functions::encode($ownerPID);?>&ownersubitem=<?php echo functions::encode($ownerSubItem)?>&pid="+PiEwgD
		else
			window.location="<?php echo functions::pageName()?>ownerpid=<?php echo functions::encode($ownerPID);?>&ownersubitem=<?php echo functions::encode($ownerSubItem)?>&"
	}
	function caSel(PiEwgD){
		if(PiEwgD)
			window.location="<?php echo functions::pageName()?>?ownerpid=<?php echo functions::encode($ownerPID);?>&ownersubitem=<?php echo functions::encode($ownerSubItem)?>&pid=<?php echo functions::encode($p_id)?>&itemID="+PiEwgD
		else
			window.location="<?php echo functions::pageName()?>?ownerpid=<?php echo functions::encode($ownerPID);?>&ownersubitem=<?php echo functions::encode($ownerSubItem)?>&pid=<?php echo functions::encode($p_id)?>"
	}
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 4000,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 4000,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>