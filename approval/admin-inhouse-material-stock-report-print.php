<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
$loc_id='';$bdMon='';$cat='';
$searchedItem = isset($_SESSION['sr_search']) ? $_SESSION['sr_search'] : "";
$mon = isset($_SESSION['sr_bdMon']) ? $_SESSION['sr_bdMon'] : date('m');
$year = isset($_SESSION['sr_bdYear']) ? $_SESSION['sr_bdYear'] : date('Y');
$loc_id = isset($_SESSION['sr_selLoc']) ? $_SESSION['sr_selLoc'] : "";
$category = isset($_SESSION['sr_selCat']) ? $_SESSION['sr_selCat'] : "";
$qcategory = ($category) ? (($category=='uncat') ? ' AND category IS NULL' : ' AND category="'.$category.'"') : " AND category IS NULL";
$days_in_month = functions::daysInMonth($mon,$year);


$mf_id = $db->getValue('material_reference','mf_id',array('mf_id'=>$searchedItem)); 
$searchedItem = $db->getValue('material_reference','item',array('mf_id'=>$mf_id));
$searchedBrand = $db->getValue('material_reference','brand',array('mf_id'=>$mf_id));
$searchedUnit = $db->getValue('material_reference','unit',array('mf_id'=>$mf_id));

$arrSearch = array();$arrSearchSold = array();
if($loc_id){
	$arrSearch = array_merge(array('location'=>$loc_id),$arrSearch);
	$arrSearchSold = array_merge(array('location'=>$loc_id),$arrSearchSold);
}

if($mon && $year){
	$arrSearch = array_merge(array('left(ims_date,7)'=>$year.'-'.$mon),$arrSearch);	
	$arrSearchSold = array_merge(array('left(im_date,7)'=>$year.'-'.$mon),$arrSearchSold);
}

$arrInventory=array();$arrMaterials=array();$arrInitialQuantity=array();$arrCurrentQuantity=array();$arrDates=array();
$arrMaterialsSold=array();$arrInventorySold=array();

//Getting all items, initial quantity and current
if($searchedItem){
	if($loc_id)
		$qMA = $db->query("SELECT ims.item,ims.unit,ims.brand, (SELECT sum(ims2.quantity) FROM inhouse_material_storage as ims2 WHERE ims.item=ims2.item AND ims.unit=ims2.unit AND ims.brand=ims2.brand AND ims.location=ims2.location AND left(ims2.ims_date,4) < '".$year.'-'.$mon."') as stckInit, (SELECT sum(ims3.quantity) FROM inhouse_material_storage as ims3 WHERE ims.item=ims3.item AND ims.unit=ims3.unit AND ims.brand=ims3.brand AND ims.location=ims3.location AND left(ims3.ims_date,4)<= '".$year.'-'.$mon."') as stckCurrent FROM inhouse_material_storage ims, material_reference mrf WHERE ims.location='".$loc_id."' AND ims.item='".$searchedItem."' AND ims.brand='".$searchedBrand."' AND ims.unit='".$searchedUnit."' AND ims.item=mrf.item AND ims.unit=mrf.unit AND ims.brand=mrf.brand ".$qcategory." GROUP BY ims.item,ims.unit,ims.brand ORDER BY ims.item LIMIT 1000");
	else
		$qMA = $db->query("SELECT ims.item,ims.unit,ims.brand, (SELECT sum(ims2.quantity) FROM inhouse_material_storage as ims2 WHERE ims.item=ims2.item AND ims.unit=ims2.unit AND ims.brand=ims2.brand AND left(ims2.ims_date,4) < '".$year.'-'.$mon."') as stckInit, (SELECT sum(ims3.quantity) FROM inhouse_material_storage as ims3 WHERE ims.item=ims3.item AND ims.unit=ims3.unit AND ims.brand=ims3.brand AND left(ims3.ims_date,4)<= '".$year.'-'.$mon."') as stckCurrent FROM inhouse_material_storage ims, material_reference mrf WHERE ims.item='".$searchedItem."' AND ims.brand='".$searchedBrand."' AND ims.unit='".$searchedUnit."' AND ims.item=mrf.item AND ims.unit=mrf.unit AND ims.brand=mrf.brand ".$qcategory." GROUP BY ims.item,ims.unit,ims.brand ORDER BY ims.item LIMIT 1000");
}
else{
	if($loc_id)
		$qMA = $db->query("SELECT ims.item,ims.unit,ims.brand, (SELECT sum(ims2.quantity) FROM inhouse_material_storage as ims2 WHERE ims.item=ims2.item AND ims.unit=ims2.unit AND ims.brand=ims2.brand AND ims.location=ims2.location AND left(ims2.ims_date,4) < '".$year.'-'.$mon."') as stckInit, (SELECT sum(ims3.quantity) FROM inhouse_material_storage as ims3 WHERE ims.item=ims3.item AND ims.unit=ims3.unit AND ims.brand=ims3.brand AND ims.location=ims3.location AND left(ims3.ims_date,4)<= '".$year.'-'.$mon."') as stckCurrent FROM inhouse_material_storage ims, material_reference mrf WHERE ims.location='".$loc_id."' AND ims.item=mrf.item AND ims.unit=mrf.unit AND ims.brand=mrf.brand ".$qcategory." GROUP BY ims.item,ims.unit,ims.brand ORDER BY ims.item LIMIT 1000");
	else
		$qMA = $db->query("SELECT ims.item,ims.unit,ims.brand, (SELECT sum(ims2.quantity) FROM inhouse_material_storage as ims2 WHERE ims.item=ims2.item AND ims.unit=ims2.unit AND ims.brand=ims2.brand AND left(ims2.ims_date,4)< '".$year.'-'.$mon."') as stckInit, (SELECT sum(ims3.quantity) FROM inhouse_material_storage as ims3 WHERE ims.item=ims3.item AND ims.unit=ims3.unit AND ims.brand=ims3.brand AND left(ims3.ims_date,4)<= '".$year.'-'.$mon."') as stckCurrent FROM inhouse_material_storage ims, material_reference mrf WHERE ims.item=mrf.item AND ims.unit=mrf.unit AND ims.brand=mrf.brand ".$qcategory." GROUP BY ims.item,ims.unit,ims.brand ORDER BY ims.item LIMIT 1000");
}
#echo '<br>'.$db->last_query;
while($rMA = $db->fetch_array($qMA)):
	$itm = $rMA['item'];
	$unt = $rMA['unit'];
	$brnd = $rMA['brand'];
	$qtyInit = $rMA['stckInit'];
	$qtyCurrent = $rMA['stckCurrent'];

	if($loc_id)
		$consumedInt = $db->getValue('inhouse_material im, inhouse_material_item imi','sum(quantity)',array('item'=>$itm,'unit'=>$unt,'brand'=>$brnd,'location'=>$loc_id),'AND im.im_id=imi.im_id AND left(im_date,4) < "'.$year.'-'.$mon.'"');
	else
		$consumedInt = $db->getValue('inhouse_material im, inhouse_material_item imi','sum(quantity)',array('item'=>$itm,'unit'=>$unt,'brand'=>$brnd),'AND im.im_id=imi.im_id AND left(im_date,4) < "'.$year.'-'.$mon.'"');
	$qtyInit = is_numeric($qtyInit) ? $qtyInit : 0;
	$consumedInt = is_numeric($consumedInt) ? $consumedInt : 0;
	$quantityInitial = $qtyInit - $consumedInt;

	if($loc_id)
		$consumedCurrent = $db->getValue('inhouse_material im, inhouse_material_item imi','sum(quantity)',array('item'=>$itm,'unit'=>$unt,'brand'=>$brnd,'location'=>$loc_id),'AND im.im_id=imi.im_id AND left(im_date,4) <= "'.$year.'-'.$mon.'"');
	else
		$consumedCurrent = $db->getValue('inhouse_material im, inhouse_material_item imi','sum(quantity)',array('item'=>$itm,'unit'=>$unt,'brand'=>$brnd),'AND im.im_id=imi.im_id AND left(im_date,4) <= "'.$year.'-'.$mon.'"');
	$qtyCurrent = is_numeric($qtyCurrent) ? $qtyCurrent : 0;
	$consumedCurrent = is_numeric($consumedCurrent) ? $consumedCurrent : 0;
	$quantityCurrent = $qtyCurrent - $consumedCurrent;
	#echo $itm.$unt.$brnd.'<br>';
	$arrMaterials[$itm.$unt.$brnd]=array('item'=>$itm,'unit'=>$unt,'brand'=>$brnd,'initial'=>$quantityInitial,'current'=>$quantityCurrent);
endwhile;
#echo '<pre>';
#print_r($arrMaterials);
if($searchedItem){
	$q = $db->select2('inhouse_material_storage ims, material_reference mrf','ims.item,ims.unit,ims.brand,ims.ims_date,ims.quantity as stck',$arrSearch,'AND ims.item=mrf.item AND ims.unit=mrf.unit AND ims.brand=mrf.brand AND ims.item=? AND ims.unit=? AND ims.brand=? '.$qcategory.' GROUP BY ims.item,ims.unit,ims.brand,ims.ims_date ORDER BY ims.item',array($searchedItem,$searchedUnit,$searchedBrand));
}
else
	$q = $db->select('inhouse_material_storage ims, material_reference mrf','ims.item,ims.unit,ims.brand,ims.ims_date,ims.quantity as stck',$arrSearch,'AND ims.item=mrf.item AND ims.unit=mrf.unit AND ims.brand=mrf.brand '.$qcategory.' GROUP BY ims.item,ims.unit,ims.brand,ims.ims_date ORDER BY ims.item');
#echo '<br>'.$db->last_query;
while($r = $db->fetch_array($q)):
	$arrDates[$r['ims_date']]=$r['ims_date'];
	$arrInventory[$r['item'].$r['unit'].$r['brand']][$r['ims_date']]=$r['stck'];
endwhile;
#print_r($arrInventory);
if($searchedItem){
	$qSold = $db->select2('material_reference mrf, inhouse_material im, inhouse_material_item imi','imi.item,imi.unit,imi.brand,im.im_date,sum(imi.quantity) as stck',$arrSearchSold,'AND imi.item=mrf.item AND imi.unit=mrf.unit AND imi.brand=mrf.brand '.$qcategory.' AND im.im_id=imi.im_id AND imi.item=? AND imi.brand=? AND imi.unit=? GROUP BY imi.item,imi.unit,imi.brand,im.im_date ORDER BY imi.item',array($searchedItem,$searchedBrand,$searchedUnit));
	#$qSold = $db->select('material_reference mrf, inhouse_material im, inhouse_material_item imi','imi.item,imi.unit,imi.brand,im.im_date,sum(imi.quantity) as stck',$arrSearchSold,'AND imi.item=mrf.item AND imi.unit=mrf.unit AND imi.brand=mrf.brand '.$qcategory.' AND imi.im_id=im.im_id AND imi.item="'.$searchedItem.'" AND imi.brand="'.$searchedBrand.'" AND imi.unit="'.$searchedUnit.'" GROUP BY imi.item,imi.unit,imi.brand,im.im_date ORDER BY imi.item');
	#echo '<br>'.$db->last_query;
}
else
	$qSold = $db->select('material_reference mrf, inhouse_material im, inhouse_material_item imi','imi.item,imi.unit,imi.brand,im.im_date,sum(imi.quantity) as stck',$arrSearchSold,'AND imi.item=mrf.item AND imi.unit=mrf.unit AND imi.brand=mrf.brand '.$qcategory.' AND imi.im_id=im.im_id GROUP BY imi.item,imi.unit,imi.brand,im.im_date ORDER BY imi.item');
#echo '<br>'.$db->last_query;
while($rS = $db->fetch_array($qSold)):
	$arrMaterialsSold[$rS['item'].$rS['unit'].$rS['brand']]=array('item'=>$rS['item'],'unit'=>$rS['unit'],'brand'=>$rS['brand']);
	$arrInventorySold[$rS['item'].$rS['unit'].$rS['brand']][$rS['im_date']]=$rS['stck'];
	$arrDates[$rS['im_date']]=$rS['im_date'];
endwhile;
ksort($arrMaterialsSold);
#print_r($arrInventorySold);
#echo '</pre>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Release Print</title>
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
			/*background: rgb(204,204,204);*/
			font-size: 14px;
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
			}
		}
		.spce {
		line-height: 150%;
		}
		.bisaya{color:red !important;font-style: italic;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<page size="ltr">
	<table width="700" border="0" align="center" style="font-size: 14px;">
		<tr>
			<td>
				<?php
				require_once('../class/print_header.php');
				print_header('STOCK INVENTORY');
				?>
			</td>
		</tr>
	</table><br>
	<table border="1" align="center" width="99%" style="font-size:12px;">
		<thead>
			<tr style="background-color:#CCC !important;">
				<th width="20%">Item</th>
				<th width="5%">Unit</th>
				<th width="5%">Brand</th>
				<th width="5%"><div align="center">Initial</div></th>
				<?php #for($i=1; $i<=$days_in_month; $i++):?>
				<?php foreach($arrDates as $searchDate): ?>
				<th width="3%"><div align="center"><?php echo date('M, d',strtotime($searchDate));/*echo ($i<=9) ? '0'.$i : $i;*/ ?></div></th>
				<?php endforeach; ?>
				<?php #endfor; ?>
				<th width="5%"><div align="center">Present</div></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($arrMaterials as $itmDetail => $arrVal):
				$quantityInitial=$arrVal['initial'];
				$quantityNow=$arrVal['current'];
			?>
			<tr>
				<td style="padding-left:5px; padding-right:0px;"><?php echo $arrVal['item'];?></td>
				<td style="padding-left:5px;font-size:10px;"><?php echo $arrVal['unit'];?></td>
				<td style="padding-left:5px;font-size:10px;"><?php echo $arrVal['brand'];?></td>
				<td><div align="center"><?php echo $quantityInitial;?></div></td>
				<?php #for($i=0; $i<$days_in_month; $i++): $searchDate=functions::AddDay($year.'-'.$mon.'-01',$i)?>
				<?php foreach($arrDates as $searchDate): ?>
				<td>
					<div align="center">
						<?php
						$acquired = isset( $arrInventory[$itmDetail][$searchDate] ) ? $arrInventory[$itmDetail][$searchDate] : 0;
						$sold = (isset($arrInventorySold[$itmDetail][$searchDate])) ? $arrInventorySold[$itmDetail][$searchDate] : 0;
						if($acquired || $sold)
							echo $quantityInitial += $acquired - $sold;
						?>
					</div>
				</td>
				<?php endforeach; ?>
				<?php #endfor; ?>
				<td><div align="center"><?php echo $quantityNow;?></div></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</page>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-inhouse-material-stock-report.php?";
}
</script>
<!-- end: JavaScript-->
</body>
</html>