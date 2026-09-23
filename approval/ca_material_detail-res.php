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
$itemBase=0;
$cag_id=(isset($_REQUEST['cag_id']) && !empty($_REQUEST['cag_id']) ) ? functions::decode($_REQUEST['cag_id']) : 0;
$itemDescSearch=(isset($_REQUEST['itm']) && !empty($_REQUEST['itm']) ) ? $db->clean($_REQUEST['itm']) : '';
$_SESSION['temp_search']=$itemDescSearch;
if($itemDescSearch){
	$itemBase = $db->getValue('elm_rate','itemBase',array('itemParent'=>0,'itemDesc'=>'materials'));
}
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
	$q = $db->select('elm_rate','*',array('itemParent'=>$itemParent));
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
	global $cag_id;
	global $itemBase;
	global $itemDescSearch;
	$string='';
	if($itemDescSearch)
		$q = $db->select('elm_rate','*',array('itemBase'=>$itemBase),'AND itemDesc LIKE "%'.$itemDescSearch.'%" AND itemRate > 0 ORDER BY cast(itemNo as unsigned),id');
	else
		$q = $db->select('elm_rate','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned),id');
	//echo $db->last_query;
	while($r = $db->fetch_array($q)):
		$itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
		$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
		$itemRate = ($r['itemRate']) ? functions::formatMoney($r['itemRate']) : '';
		$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : "";
		if( $db->getValue('elm_rate','count(*)',array('itemParent'=>$r['id'])) ){
			$itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";
		}

		$pad=0;
		for($i=1; $i<=$level; $i++):
			$pad+=40;
		endfor;
		$btn='';
		if( $itemRate ){
			$addSubItem = "showThis(this.id,'ca_material_detail_add.php?itemID=".functions::encode($r['id'])."&cag_id=".functions::encode($cag_id)."','Adding DQC Item')";
			$btn = '<a id="AddSubItem'.$r['id'].'" class="thickbox" style="cursor:pointer" title="Add this Item" data-rel="tooltip" onclick="'.$addSubItem.'"><i class="halflings-icon plus-sign"></i></a>';
		}

		$string ='
		<tr>
			<td>
				<div style="padding-left:'.$pad.'px;">
					<div style="display:flex;">
						<div style="flex: 0;border:0px solid;">'.$itemNo.'</div>
						<div style="flex: 2;border:0px solid;padding-left:15px;">'.$itemDesc.'</div>
					</div>
				</div>
			</td>
			<td><div align="center">'.$itemUnit.'</div></td>
			<td><div align="right">'.$itemRate.'</div></td>
			<td><div align="center">'.$btn.'</div></td>
		</tr>
		';
		echo $string;
		if(empty($itemDescSearch))
			disp($r['id'],$level + 1);
	endwhile;
}
?>
		<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover table-striped" style="font-size:12px;">
			<thead>
				<tr bgcolor="#CCCCCC">
					<th width="65%" scope="col"><div align="left">Item</div></th>
					<th width="7%" scope="col"><div align="center">Unit</div></th>
					<th width="7%" scope="col"><div align="right">Rate</div></th>
					<th width="5%" scope="col">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$qList = $db->select('elm_rate','*',array('itemParent'=>0,'itemDesc'=>'materials'),'ORDER BY cast(itemNo as unsigned)');
				while($rList = $db->fetch_array($qList)):
				?>
				<tr>
					<td width="25%" ><strong><?php echo $rList['itemDesc']?></strong></td>
					<td><div align="center"><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : ''?></div></td>
					<td><div align="right"><?php echo ($rList['itemRate']) ? $rList['itemRate'] : ''?></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php disp($rList['id'],1);?>
				<?php endwhile; ?>
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
			</tbody>
		</table><p>&nbsp;</p>