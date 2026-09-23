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
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;

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
	$string='';
	$q = $db->select('dqc','*',array('proj_id'=>$proj_id,'itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
	while($r = $db->fetch_array($q)):
		$itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
		$noOfSets = ($r['noOfSets']) ? $r['noOfSets'] : "";
		$noOfUnit = ($r['noOfUnit']) ? $r['noOfUnit'] : "";
		$size = ($r['size']) ? $r['size'] : "";
		$weight = ($r['weight']) ? $r['weight'] : "";
		$length = ($r['length']) ? $r['length'] : "";
		$required = ($r['required']) ? $r['required'] : "";
		$totalRequired = ($r['totalRequired']) ? $r['totalRequired'] : "";
		$noOfCutLength = ($r['noOfCutLength']) ? $r['noOfCutLength'] : "";
		$qtyFrom = ($r['lengthFrom']) ? $r['lengthFrom'] : "";
		$quantity = ($r['lengthQuantity']) ? $r['lengthQuantity'] : "";
		$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
		if( $db->getValue('dqc','count(*)',array('itemParent'=>$r['id'])) ){
			$itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";
		}

		if($level<=2){
			$it = itemTotal($r['id']);
			$it = ($it) ? functions::formatMoney($it) : '';
			$totalWt = ($r['total']) ? functions::formatMoney($r['total']) : '<strong>'.$it.'</strong>';
		}
		else{
			$totalWt = ($r['total']) ? functions::formatMoney($r['total']) : "";
		}

		$s = '';
		for($i=1; $i<=$level; $i++):
			$s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		endfor;
		$s .= $itemNo.'&nbsp;&nbsp;&nbsp;&nbsp;'.$itemDesc;
		$addSubItem = "showThis(this.id,'dqc3_item_add.php?pid=".functions::encode($proj_id)."&itemID=".functions::encode($r['id'])."','Adding DQC Item')";
		$manageItem = "showThis(this.id,'dqc3_item_manage.php?pid=".functions::encode($proj_id)."&itemID=".functions::encode($r['id'])."','Adding DQC Item')";
		$deleteItem = "dqc3.php?pid=".functions::encode($proj_id)."&delItemID=".functions::encode($r['id'])."&itemID=".functions::encode($r['itemBase']);
		$btn = '&nbsp;&nbsp;&nbsp;
		<a id="AddSubItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="'.$addSubItem.'"><i class="halflings-icon plus-sign"></i></a>
		<a id="ManageItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="'.$manageItem.'"><i class="halflings-icon pencil"></i></a>
		<a id="DeleteItem'.$r['id'].'" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="'.$deleteItem.'"><i class="halflings-icon minus-sign"></i></a>
		';
        
		$string ='
		<tr id="rw'.$r['id'].'">
			<td></td>
			<td>'.$s.$btn.'</td>
			<td><div align="right">'.$noOfSets.'</div></td>
			<td><div align="right">'.$noOfUnit.'</div></td>
			<td><div align="right">'.$size.'</div></td>
			<td><div align="right">'.$weight.'</div></td>
			<td><div align="right">'.$length.'</div></td>
			<td><div align="right">'.$required.'</div></td>
			<td><div align="right">'.$totalRequired.'</div></td>
			<td><div align="right">'.$noOfCutLength.'</div></td>
			<td><div align="right">'.$qtyFrom.'</div></td>
			<td><div align="right">'.$quantity.'</div></td>
			<td><div align="right">'.$totalWt.'</div></td>
		</tr>
		';
		echo $string;
		disp($proj_id,$r['id'],$level + 1);
	endwhile;
}
function deleteItemAndSub($p_id,$itemID){
	global $db;
	$q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>$itemID));
	while( $r = $db->fetch_array($q) ):
		$item_id = $r['id'];
		$db->delete('dqc',array('proj_id'=>$p_id,'id'=>$item_id));
		deleteItemAndSub($p_id,$item_id);
	endwhile;
	$db->delete('dqc',array('proj_id'=>$p_id,'id'=>$itemID));
}
function updateItemNo($p_id,$itemParent){
	global $db;
	$itemParentItemNo = $db->getValue('dqc','itemNo',array('proj_id'=>$p_id,'id'=>$itemParent));
	$q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>$itemParent),'ORDER BY id');
	$count=0;
	while($r = $db->fetch_array($q)):
		$count++;
		$newItemNo = ($itemParentItemNo) ? $itemParentItemNo. '.' . $count : $count;
		$db->update('dqc',array('itemNo'=>$newItemNo),array('proj_id'=>$p_id,'id'=>$r['id']));
		updateSubItem($p_id,$r['id']);
	endwhile;
}

function updateSubItem($p_id,$itemParent){
	global $db;
	$itemParentItemNo = $db->getValue('dqc','itemNo',array('proj_id'=>$p_id,'id'=>$itemParent));
	$q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>$itemParent),'ORDER BY id');
	$count=0;
	while($r = $db->fetch_array($q)):
		$count++;
		$newItemNo = $itemParentItemNo. '.' . $count;
		$db->update('dqc',array('itemNo'=>$newItemNo),array('proj_id'=>$p_id,'id'=>$r['id']));
		updateSubItem($p_id,$r['id']);
	endwhile;
}

function deleteItem($p_id,$delItemID){
	global $db;
	$itemParent = $db->getValue('dqc','itemParent',array('proj_id'=>$p_id,'id'=>$delItemID));
	deleteItemAndSub($p_id,$delItemID);
	updateItemNo($p_id,$itemParent);
}

$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$delItemID = (isset($_REQUEST['delItemID']) && !empty($_REQUEST['delItemID']) ) ? functions::decode($_REQUEST['delItemID']) : 0;
if( $delItemID && $p_id){
	deleteItem($p_id,$delItemID);
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo('dqc3.php?pid='.functions::encode($p_id).'&itemID='.functions::encode($itemID));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>DQC3</title>
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
	a{
		opacity: 0.4;
		filter: alpha(opacity=20); /* For IE8 and earlier */
	}
	a:hover {
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
<div id="spinner"></div>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>DQC</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="#" style="opacity:.8">DQC 3</a></li>
				<li><a href="dqc2.php?pid=<?php echo functions::encode($p_id);?>">DQC 2</a></li>
				<li><a href="dqc.php?pid=<?php echo functions::encode($p_id);?>">DQC 1</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$p_id));?></strong></div><br><br>
			<select name="selParentItem" id="selParentItem" style="width:50%;" onChange="itemSel(this.value)">
				<option value="">-- All Item --</option>
				<?php
				$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'itemParent'=>0,'dqc_type'=>'3')) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
				$q = $db->select('dqc','*',array('proj_id'=>$p_id,'itemParent'=>0,'dqc_type'=>'3'),$orderBy);
				while($r = $db->fetch_array($q)):?>
				<option value="<?php echo functions::encode($r['id'])?>" <?php if($itemID==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].'. '.$r['itemDesc']?></option>
				<?php endwhile;?>
			</select>
			<table id="tblist" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-striped <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="4%" scope="col"><div align="center">Item No.</div></th>
						<th width="30%" scope="col"><div align="left">Description</div></th>
						<th width="5%" scope="col"><div align="right">No. Of Sets</div></th>
						<th width="5%" scope="col"><div align="right">No. of Units</div></th>
						<th width="5%" scope="col"><div align="right">Size</div></th>
						<th width="5%" scope="col"><div align="right">Unit Wt.</div></th>
						<th width="5%" scope="col"><div align="right">Bar Length</div></th>
						<th width="5%" scope="col"><div align="right">Required No.</div></th>
						<th width="5%" scope="col"><div align="right">Total Reqd No.</div></th>
						<th width="5%" scope="col"><div align="right">No. of Cut/Length</div></th>
						<th width="5%" scope="col"><div align="right">From</div></th>
						<th width="5%" scope="col"><div align="right">Qty</div></th>
						<th width="9%" scope="col"><div align="right">Total Wt.(kgs)</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'dqc_type'=>'3','itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo'; 
				if($itemID)
					$qList = $db->select('dqc','*',array('id'=>$itemID,'proj_id'=>$p_id,'itemParent'=>0,'dqc_type'=>'3'),$orderBy);
				else 
					$qList = $db->select('dqc','*',array('proj_id'=>$p_id,'dqc_type'=>'3','itemParent'=>0),$orderBy);
				while($rList = $db->fetch_array($qList)):
				?>
					<tr id="rw<?php echo $rList['id']?>">
						<td><div align="center"><?php echo $rList['itemNo']?></div></td>
						<td width="25%" >
							<strong><?php echo $rList['itemDesc']?></strong>&nbsp;&nbsp;&nbsp;
							<a id="AddSubItem<?php echo $rList['id']?>" class="thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="showThis(this.id,'dqc3_item_add.php?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($rList['id']);?>','Adding DQC Item')"><i class="halflings-icon plus-sign"></i></a>
							<a id="ManageItem<?php echo $rList['id']?>" class="thickbox" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'dqc3_item_manage.php?pid=<?php echo functions::encode($p_id);?>&itemID=<?php echo functions::encode($rList['id']);?>','Adding DQC Item')"><i class="halflings-icon pencil"></i></a>
							<a id="deleteItem<?php echo $rList['id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="dqc3.php?pid=<?php echo functions::encode($p_id);?>&delItemID=<?php echo functions::encode($rList['id']);?>"><i class="halflings-icon minus-sign"></i></a>
						</td>
						<td><div align="right"><?php echo ($rList['noOfSets']) ? $rList['noOfSets'] : '';?></div></td>
						<td><div align="right"><?php echo ($rList['noOfUnit']) ? $rList['noOfUnit'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['size']) ? $rList['size'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['weight']) ? $rList['weight'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['length']) ? $rList['length'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['required']) ? $rList['required'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['totalRequired']) ? $rList['totalRequired'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['noOfCutLength']) ? $rList['noOfCutLength'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['lengthFrom']) ? $rList['lengthFrom'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['lengthQuantity']) ? $rList['lengthQuantity'] : ''?></div></td>
						<td><div align="right"><?php echo ($rList['total']) ? $rList['total'] : ''?></div></td>
					</tr>
					<?php 
						disp($p_id,$rList['id'],1);
					endwhile;
					?>
					<tr>
						<td colspan="13">&nbsp;</td>
					</tr>
					<tr>
						<td><a id="addItem" title="Add Item" data-rel="tooltip" style="opacity:1.0" class="btn btn-mini thickbox" onclick="showThis(this.id,'dqc3_item_add.php?pid=<?php echo functions::encode($p_id);?>','Adding DQC Item')"><i class="halflings-icon white plus"></i>Add Item</a></td>
						<td colspan="12">&nbsp;</td>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>function itemSel(PiEwgD){window.location="<?php echo functions::pageName()?>?pid=<?php echo functions::encode($p_id)?>&itemID="+PiEwgD}</script>
<!-- end: JavaScript-->
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
</body>
</html>