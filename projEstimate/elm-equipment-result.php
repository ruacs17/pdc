<?php require_once('authorize.php');
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$count=0;
$srch = (isset($_REQUEST['srch']) && !empty($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : 0;
$tpe = (isset($_REQUEST['tpe']) && !empty($_REQUEST['tpe']) ) ? $_REQUEST['tpe'] : 0;
$itemID = (isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? $_REQUEST['itemID'] : 0;
if($srch){
	$q = $db->query('SELECT * FROM equipment WHERE name LIKE "%'.$srch.'%" OR classification LIKE "%'.$srch.'%" OR type LIKE "%'.$srch.'%" OR brand LIKE "%'.$srch.'%" ORDER BY name, classification, type, brand LIMIT 40');
	while($r = $db->fetch_array($q)):
		$count++;
	endwhile;
}
if($count){
?>
<table class="table table-hover table-striped table-bordered" border="0">
	<thead>
		<tr style="background-color:#CCC;">
			<td><strong>Code</strong></td>
			<td><strong>Classification</strong></td>
			<td><strong>Name</strong></td>
			<td><strong>Brand</strong></td>
			<td><strong>Rate</strong></td>
			<td>&nbsp;</td>
		</tr>
	</thead>
	<tbody>
		<?php
		if($srch){
			$q = $db->query('SELECT * FROM equipment WHERE name LIKE "%'.$srch.'%" OR classification LIKE "%'.$srch.'%" OR type LIKE "%'.$srch.'%" OR brand LIKE "%'.$srch.'%" ORDER BY name, classification, type, brand LIMIT 40');
			while($r = $db->fetch_array($q)):
				$count++;
		?>
			<tr onclick="javascript:location.href='?srcItm=<?php echo functions::encode($r['equip_id'])?>&itemID=<?php echo $itemID?>&tpe=<?php echo $tpe?>'">
				<td><?php echo $r['inventory_id']?></td>
				<td><?php echo $r['classification']?></td>
				<td><?php echo $r['name']?></td>
				<td><?php echo $r['brand']?></td>
				<td><div align="right"><?php echo functions::formatMoney($r['rate'])?></div></td>
				<td><div align="center"><a href="?srcItm=<?php echo functions::encode($r['equip_id'])?>&itemID=<?php echo $itemID?>&tpe=<?php echo $tpe?>" class="btn btn-mini btn-success">select</a></div></td>
			</tr>
		<?php endwhile;?>
		<?php if($count==0){?>
			<tr>
				<td colspan="6"><div align="center"><strong>----- No Result -----</strong></div></td>
			</tr>
		<?php }?>
		</tbody>
</table>
<br><br><br>
<?php }
}?>