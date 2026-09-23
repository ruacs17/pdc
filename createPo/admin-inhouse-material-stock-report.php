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
#echo strtotime(date('Y-m-d'));
$loc_id='';$bdMon='';$cat='';
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : 0;
if( isset($_REQUEST['srcItm']) ){
	$_SESSION['sr_search'] = $searchedItem;
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo '<div id="spinner"></div>';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnSearch']) ){
	$_SESSION['sr_selCat'] = (isset($_POST['selCat']) && !empty($_POST['selCat'])) ? $db->clean(functions::decode($_POST['selCat'])) : '';
	$_SESSION['sr_bdMon'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon'])) ? $db->clean($_POST['bdMon']) : date('m');
	$_SESSION['sr_bdYear'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear'])) ? $db->clean($_POST['bdYear']) : date('Y');
	$_SESSION['sr_selLoc'] = (isset($_POST['selLoc']) && !empty($_POST['selLoc'])) ? $_POST['selLoc'] : "";
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo '<div id="spinner"></div>';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnReset']) ){
	$_SESSION['sr_bdMon'] = date('m');
	$_SESSION['sr_bdYear'] = date('Y');
	$_SESSION['sr_selLoc'] = "";
	$_SESSION['sr_search'] = 0;
	echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	echo '<div id="spinner"></div>';
	functions::sendTo(functions::pageName());
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Inventory Item Report</title>
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
	<script src="../js/formatCurrency.js"></script>
	<script src="../js/inputInt.js"></script>
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
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<?php
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
<div class="">
	<div class="">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white th-list"></i><span class="break"></span>Stock Inventory</h2>
		</div>
		<div class="box-content">
			<form method="post" id="frm" class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="appendedInputButtons">Search</label>
					<div class="controls">
						<select name="bdMon" id="bdMon" style="width:80px;">
							<option value="">Month</option>
							<option value="01" <?php if($mon=='01')echo 'selected="selected"';?>>Jan</option>
							<option value="02" <?php if($mon=='02')echo 'selected="selected"';?>>Feb</option>
							<option value="03" <?php if($mon=='03')echo 'selected="selected"';?>>Mar</option>
							<option value="04" <?php if($mon=='04')echo 'selected="selected"';?>>Apr</option>
							<option value="05" <?php if($mon=='05')echo 'selected="selected"';?>>May</option>
							<option value="06" <?php if($mon=='06')echo 'selected="selected"';?>>Jun</option>
							<option value="07" <?php if($mon=='07')echo 'selected="selected"';?>>Jul</option>
							<option value="08" <?php if($mon=='08')echo 'selected="selected"';?>>Aug</option>
							<option value="09" <?php if($mon=='09')echo 'selected="selected"';?>>Sep</option>
							<option value="10" <?php if($mon=='10')echo 'selected="selected"';?>>Oct</option>
							<option value="11" <?php if($mon=='11')echo 'selected="selected"';?>>Nov</option>
							<option value="12" <?php if($mon=='12')echo 'selected="selected"';?>>Dec</option>
						</select>
						<select name="bdYear" id="bdYear" style="width:70px;">
							<option value="">Year</option>
							<?php for($y=(date('Y') + 2);$y>=2012;$y--):?>
							<option value="<?php echo $y;?>" <?php if($y==$year)echo 'selected="selected"';?>><?php echo $y;?></option>
							<?php endfor;?>
						</select>
						<textarea style="width:280px;" class="span6 typeahead" name="txSrchItem" id="txSrchItem" autocomplete='off' onkeyup="searchMaterial(this.value)" rows="1"><?php echo $searchedItem; ?></textarea>
						<select name="selCat" id="selCat" style="width:300px;" required>
							<option value="">-- Select Category --</option>
							<?php
							$qType = $db->query('SELECT * FROM material_type WHERE type_name="category" ORDER BY type_desc ');
							while($rType = $db->fetch_array($qType)):
							?>
							<option value="<?php echo functions::encode($rType['type_desc'])?>" <?php if($category==$rType['type_desc']){echo 'selected="selected"';}?>><?php echo $rType['type_desc']?></option>
							<?php endwhile;?>
							<option value="<?php echo functions::encode('uncat') ?>" <?php if($category=='uncat'){echo 'selected="selected"';}?>>Uncategorized</option>
						</select>
						<select name="selLoc" id="selLoc" style="width:200px;">
							<option value="">--All Location--</option>
							<?php
							$qLocation = $db->select('inhouse_material_storage_location','*',array());
							while($rLoc = $db->fetch_array($qLocation)):
							?>
							<option value="<?php echo $rLoc['location']?>" <?php if($loc_id==$rLoc['location'])echo 'selected="selected"';?>><?php echo ucwords(strtolower($rLoc['location']));?></option>
							<?php endwhile;?>
						</select>
						&nbsp;<input type="submit" class="btn btn-primary btn-small" name="btnSearch" id="btnSearch" value="Search">
						&nbsp;<input type="submit" class="btn btn-small" name="btnReset" value="Reset">
						&nbsp;<a id="itemPrint" href="admin-inhouse-material-stock-report-print.php" class="btn btn-info btn-small" ><i class="halflings-icon white print"></i></a>
					</div>
				</div>
				<div id="materialView"></div>
			</form>
			<div class="table-wrapper">
			<div align="center">
				<div style="width:100%">
					<table border="0" class="table table-bordered table-hover"style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="20%">Item</th>
								<th width="5%">Unit</th>
								<th width="5%">Brand</th>
								<th width="3%"><div align="center">Initial</div></th>
								<?php #for($i=1; $i<=$days_in_month; $i++):?>
								<?php foreach($arrDates as $searchDate): ?>
								<th width="3%"><div align="center"><?php echo date('M, d',strtotime($searchDate));/*echo ($i<=9) ? '0'.$i : $i;*/ ?></div></th>
								<?php endforeach; ?>
								<?php #endfor; ?>
								<th width="3%"><div align="center">Present</div></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($arrMaterials as $itmDetail => $arrVal):
								$quantityInitial=$arrVal['initial'];
								$quantityNow=$arrVal['current'];
							?>
							<tr>
								<td style="padding-left:0px; padding-right:0px;"><?php echo $arrVal['item'];?></td>
								<td style="font-size:10px;"><?php echo $arrVal['unit'];?></td>
								<td style="font-size:10px;"><?php echo $arrVal['brand'];?></td>
								<td><div align="right"><?php echo $quantityInitial;?></div></td>
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
								<td><div align="right"><?php echo $quantityNow;?></div></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			</div>
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
<script type="text/javascript">
$(window).ready(function(){
		$("#spinner").fadeOut("slow");
		/*$('#btnSearch').click(function(){
			$("#spinner").show();
		});*/
		$('#frm').submit(function(){
			$("#spinner").show();
		});
});
</script>
<script>
function getXMLHTTP() { //fuction to return the xml http object
	var xmlhttp=false;
	try{
		xmlhttp=new XMLHttpRequest();
	}
	catch(e){
		try{
			xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch(e){
			try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e1){
				xmlhttp=false;
			}
		}
	}
	return xmlhttp;
}

function searchMaterial(srch) {   
	var strURL="material_reference_list.php?srch="+srch;
	var req = getXMLHTTP();

	if (req){
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					document.getElementById('materialView').innerHTML=req.responseText;            
				}else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}     
		req.open("GET", strURL, true);
		req.send(null);
	}
}
</script>
<!-- end: JavaScript-->
</body>
</html>