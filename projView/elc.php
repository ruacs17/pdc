<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
#$db->utf8();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
$count=0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
function itemSubTotal($itemParent,&$sum){
	global $db;
	$q = $db->select('elm','*',array('itemParent'=>$itemParent));
	while($r = $db->fetch_array($q)):
		$sum += $r['total'];
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
	$q = $db->select('el_capability','*',array('itemParent'=>$itemParent));
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

function disp($parentItem,$level){
	global $db;
	$string='';
	$q = $db->select('el_capability','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
	while($r = $db->fetch_array($q)):
		$itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
		$itemParent = ($r['itemParent']) ? $r['itemParent'] : "";
		$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
		$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : "";
		$slow_manhour = ($r['slow_manhour']) ? $r['slow_manhour'] : "";
		$slow_ehour = ($r['slow_ehour']) ? $r['slow_ehour'] : "";
		$med_manhour = ($r['med_manhour']) ? $r['med_manhour'] : "";
		$med_ehour = ($r['med_ehour']) ? $r['med_ehour'] : "";
		$high_manhour = ($r['high_manhour']) ? $r['high_manhour'] : "";
		$high_ehour = ($r['high_ehour']) ? $r['high_ehour'] : "";
		$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : "";
		if( $db->getValue('el_capability','count(*)',array('itemParent'=>$r['id'])) ){
			$itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";
		}

		$s = '';
		$pad=0;
		for($i=1; $i<=$level; $i++):
			$pad+=40;
		endfor;
		$addSubItem = "showThis(this.id,'elc_item_add.php?itemID=".functions::encode($r['id'])."','Adding el capability Item')";
		$manageItem = "showThis(this.id,'elc_item_manage.php?itemID=".functions::encode($r['id'])."','Adding el capability Item')";
		$deleteItem = "elc.php?delItemID=".functions::encode($r['id'])."&itemID=".functions::encode($r['itemBase']).'&delItemParent='.functions::encode($itemParent);
		$btn = '&nbsp;&nbsp;&nbsp;
		<a id="AddSubItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="'.$addSubItem.'"><i class="halflings-icon plus-sign"></i></a>
		<a id="ManageItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="'.$manageItem.'"><i class="halflings-icon pencil"></i></a>
		<a id="DeleteItem'.$r['id'].'" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="'.$deleteItem.'"><i class="halflings-icon minus-sign"></i></a>
		';
		$string ='
		<tr id="rw'.$r['id'].'">
			<td></td>
			<td>
				<div style="padding-left:'.$pad.'px;">
					<div style="display:flex;">
						<div style="flex: 0;border:0px solid;">'.$itemNo.'</div>
						<div style="flex: 2;border:0px solid;padding-left:15px;">'.$itemDesc.$btn.'</div>
					</div>
				</div>
			</td>
			<td><div align="right">'.$slow_manhour.'</div></td>
			<td><div align="right">'.$slow_ehour.'</div></td>
			<td><div align="right">'.$med_manhour.'</div></td>
			<td><div align="right">'.$med_ehour.'</div></td>
			<td><div align="right">'.$high_manhour.'</div></td>
			<td><div align="right">'.$high_ehour.'</div></td>
			<td><div align="right">'.$itemUnit.'</div></td>
		</tr>
		';
		echo $string;
		disp($r['id'],$level + 1);
	endwhile;
}
function deleteItemAndSub($itemID){
	global $db;
	$q = $db->select('el_capability','*',array('itemParent'=>$itemID));
	while( $r = $db->fetch_array($q) ):
		$item_id = $r['id'];
		$db->delete('el_capability',array('id'=>$item_id));
		deleteItemAndSub($p_id,$item_id);
	endwhile;
	$db->delete('el_capability',array('id'=>$itemID));
}
function updateItemNo($itemParent){
	global $db;
	$itemParentItemNo = $db->getValue('el_capability','itemNo',array('id'=>$itemParent));
	$q = $db->select('el_capability','*',array('itemParent'=>$itemParent),'ORDER BY id');
	$count=0;
	while($r = $db->fetch_array($q)):
		$count++;
		$newItemNo = ($itemParentItemNo) ? $itemParentItemNo. '.' . $count : $count;
		$db->update('el_capability',array('itemNo'=>$newItemNo),array('id'=>$r['id']));
		updateSubItem($r['id']);
	endwhile;
}

function updateSubItem($itemParent){
	global $db;
	$itemParentItemNo = $db->getValue('el_capability','itemNo',array('id'=>$itemParent));
	$q = $db->select('el_capability','*',array('itemParent'=>$itemParent),'ORDER BY id');
	$count=0;
	while($r = $db->fetch_array($q)):
		$count++;
		$newItemNo = $itemParentItemNo. '.' . $count;
		$db->update('el_capability',array('itemNo'=>$newItemNo),array('id'=>$r['id']));
		updateSubItem($r['id']);
	endwhile;
}

function deleteItem($delItemID){
	global $db;
	$itemParent = $db->getValue('el_capability','itemParent',array('id'=>$delItemID));

	deleteItemAndSub($delItemID);
	updateItemNo($itemParent);
}

$delItemID = (isset($_REQUEST['delItemID']) && !empty($_REQUEST['delItemID']) ) ? functions::decode($_REQUEST['delItemID']) : 0;
$delItemParent = (isset($_REQUEST['delItemParent']) && !empty($_REQUEST['delItemParent']) ) ? functions::decode($_REQUEST['delItemParent']) : 0;
if( $delItemID ){
	deleteItem($delItemID);
	$_SESSION['notif_id_list']=$delItemParent;
	functions::sendTo(functions::pageName().'?itemID='.functions::encode($itemID));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment/Manpower/Material Unit Cost</title>
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
	<script>
	function delt(){
		if(confirm('Do you want to remove this Item?'))
			return true;
		else
			return false; 
	}
	</script>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ELM Capabilities</h2>
		</div>
		<div class="box-content">
			<select name="selParentItem" id="selParentItem" style="width:350px;" onChange="itemSel(this.value)">
				<option value="">-- All Item --</option>
				<?php
				$q = $db->select('el_capability','*',array('itemParent'=>0),'ORDER BY cast(itemNo as unsigned)');
				while($r = $db->fetch_array($q)):?>
				<option value="<?php echo functions::encode($r['id'])?>" <?php if($itemID==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].'. '.$r['itemDesc']?></option>
				<?php endwhile;?>
			</select>
			<table id="tblist" width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="4%" scope="col"><div align="center">Item No.</div></th>
						<th width="42%" scope="col"><div align="left">Description</div></th>
						<th width="7%" scope="col"><div align="right">Manhours</div></th>
						<th width="7%" scope="col"><div align="right">Ehours</div></th>
						<th width="7%" scope="col"><div align="right">Manhours</div></th>
						<th width="7%" scope="col"><div align="right">Ehours</div></th>
						<th width="7%" scope="col"><div align="right">Manhours</div></th>
						<th width="7%" scope="col"><div align="right">Ehours</div></th>
						<th width="9%" scope="col"><div align="right">Unit</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if($itemID)
						$qList = $db->select('el_capability','*',array('id'=>$itemID,'itemParent'=>0),'ORDER BY cast(itemNo as unsigned)');
					else
						$qList = $db->select('el_capability','*',array('itemParent'=>0),'ORDER BY cast(itemNo as unsigned)');
					while($rList = $db->fetch_array($qList)):?>
					<tr id="rw<?php echo $rList['id']?>">
						<td><div align="center"><?php echo $rList['itemNo']?></div></td>
						<td width="25%" >
							<strong><?php echo $rList['itemDesc']?></strong>&nbsp;&nbsp;&nbsp;
							<a id="AddSubItem<?php echo $rList['id']?>" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="showThis(this.id,'elc_item_add.php?itemID=<?php echo functions::encode($rList['id']);?>','Adding ELM rate Item')"><i class="halflings-icon plus-sign"></i></a>
							<a id="ManageItem<?php echo $rList['id']?>" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'elc_item_manage.php?itemID=<?php echo functions::encode($rList['id']);?>','Adding ELM rate Item')"><i class="halflings-icon pencil"></i></a>
							<a id="deleteItem<?php echo $rList['id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="elc.php?delItemID=<?php echo functions::encode($rList['id']);?>"><i class="halflings-icon minus-sign"></i></a>
						</td>
						<td><?php echo ($rList['slow_manhour']) ? $rList['slow_manhour'] : ''?></td>
						<td><?php echo ($rList['slow_ehour']) ? $rList['slow_ehour'] : ''?></td>
						<td><?php echo ($rList['med_manhour']) ? $rList['med_manhour'] : ''?></td>
						<td><?php echo ($rList['med_ehour']) ? $rList['med_ehour'] : ''?></td>
						<td><?php echo ($rList['high_manhour']) ? $rList['high_manhour'] : ''?></td>
						<td><?php echo ($rList['high_ehour']) ? $rList['high_ehour'] : ''?></td>
						<td><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : ''?></td>
					</tr>
					<?php
						disp($rList['id'],1);
					endwhile;
					?>
					<tr>
						<td colspan="4">&nbsp;</td>
					</tr>
					<tr>
						<td><a id="addItem" title="Add Item" data-rel="tooltip" style="opacity:1.0" class="btn btn-mini thickbox" onclick="showThis(this.id,'elc_item_add.php?','Adding Item')"><i class="halflings-icon white plus"></i>Add Item</a></td>
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
			</table><p>&nbsp;</p>
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
function itemSel(PiEwgD){window.location="<?php echo functions::pageName()?>?itemID="+PiEwgD}
</script>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>