<?php require_once('authorize.php');
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();

$count=0;
$srch = (isset($_REQUEST['srch']) && !empty($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : 0;
$tpe = (isset($_REQUEST['tpe']) && !empty($_REQUEST['tpe']) ) ? $_REQUEST['tpe'] : 0;
$itemID = (isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? $_REQUEST['itemID'] : 0;
if($srch){
	$q = $db->query('SELECT * FROM material_reference WHERE item LIKE "%'.$srch.'%" OR brand LIKE "%'.$srch.'%" ORDER BY item, unit, brand LIMIT 1');
	while($r = $db->fetch_array($q)):
		$count++;
	endwhile;
}
if($count){
	$count=0;
?>
<table class="table table-hover" border="0">
	<thead>
		<tr style="background-color:#CCC;">
			<td width="15%"><strong>Code</strong></td>
			<td width="45%"><strong>Item</strong></td>
			<td><strong>Unit</strong></td>
			<td><strong>Brand</strong></td>
			<td width="10%">&nbsp;</td>
		</tr>
	</thead>
	<tbody>
	<?php
	if($srch){
		$q = $db->query('SELECT * FROM material_reference WHERE item LIKE "%'.$srch.'%" OR brand LIKE "%'.$srch.'%" ORDER BY item, unit, brand LIMIT 40');
		while($r = $db->fetch_array($q)):
			$count++;
	?>
		<tr onclick="javascript:location.href='?srcItm=<?php echo functions::encode($r['mf_id'])?>&itemID=<?php echo $itemID?>&tpe=<?php echo $tpe?>'">
			<td><?php echo $r['m_code']?></td>
			<td><?php echo $r['item']?></td>
			<td><?php echo $r['unit']?></td>
			<td><?php echo $r['brand']?></td>
			<td><div align="center"><a href="?srcItm=<?php echo functions::encode($r['mf_id'])?>&itemID=<?php echo $itemID?>&tpe=<?php echo $tpe?>" class="btn btn-mini btn-success">select</a></div></td>
		</tr>
	<?php endwhile;?>
	<?php if($count==0){?>
		<tr>
			<td colspan="5"><div align="center"><strong>----- No Result -----</strong></div></td>
		</tr>
	<?php }?>
	</tbody>
</table>
<br><br><br>
<?php }
}//end count?>