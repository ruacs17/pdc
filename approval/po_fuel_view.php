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
$arr_equip = array();
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$_SESSION['notif_id_po_list']=$po_id;
$ref_id = $db->getValue('po','ref_id',array('po_id'=>$po_id));
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : 0;
$arrEquip = ( isset($_SESSION['po_arr_equip']) ) ? $_SESSION['po_arr_equip'] : '';
$poidEdt=0;
if( isset($_POST['btnAdd']) ){
	$selEquip = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : 0;
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? trim($_POST['txBrand']) : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$txDistance = ( isset($_POST['txDistance']) && !empty($_POST['txDistance']) ) ? trim($_POST['txDistance']) : '';
	$txDays = ( isset($_POST['txDays']) && !empty($_POST['txDays']) ) ? trim($_POST['txDays']) : '';
	$_SESSION['notif_warning']='Item fail to Add!';
	if( $txItem && $selEquip ){
		$po_itemID = $db->insert('po_item',array('po_id'=>$po_id,'item'=>$txItem,'quantity'=>$txQty,'qty_delivered'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost,'discount'=>$txDiscount));
		if($po_itemID){
			$_SESSION['notif_id']=$po_itemID;
			require_once('../class/class-po-history.php');
			PO_history::insertItem($po_itemID,$user_id);

			if( $db->getValue('equipment','count(*)',array('equip_id'=>$selEquip)) ){
				if($po_itemID && $po_id && $selEquip){
					$ins = $db->insert('po_fuel_equipment',array('po_id'=>$po_id,'po_item_id'=>$po_itemID,'equip_id'=>$selEquip,'days'=>$txDays,'distance'=>$txDistance));
				}
			}
			else{
				if($po_itemID && $po_id && $selEquip){
					$ins = $db->insert('po_fuel_equipment',array('po_id'=>$po_id,'po_item_id'=>$po_itemID,'other_equip'=>$selEquip,'days'=>$txDays,'distance'=>$txDistance));
				}
			}
			$_SESSION['notif_success']='New Item Added!';
			unset($_SESSION['notif_warning']);
		}
	}
	functions::sendTo(functions::pageName().'?po_id='.functions::encode($po_id));
	die();
}

if( isset($_POST['btnSave']) ){
	$txpoidEdt = ( isset($_POST['txpoidEdt']) && !empty($_POST['txpoidEdt']) ) ? functions::decode($_POST['txpoidEdt']) : '0';
	$selEquip = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : 0;
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? trim($_POST['txBrand']) : '';
	$txDistance = ( isset($_POST['txDistance']) && !empty($_POST['txDistance']) ) ? trim($_POST['txDistance']) : '';
	$txDays = ( isset($_POST['txDays']) && !empty($_POST['txDays']) ) ? trim($_POST['txDays']) : '';
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : '0';
	$_SESSION['notif_warning']='Item fail to update!';
	if( $txItem && $selEquip && $txpoidEdt ){

		require_once('../class/class-po-history.php');
		$content=PO_history::editItemBefore($txpoidEdt);
		$db->update('po_item',array('item'=>$txItem,'quantity'=>$txQty,'qty_delivered'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost,'discount'=>$txDiscount),array('po_item_id'=>$txpoidEdt));
		PO_history::editItemAfter($txpoidEdt,$user_id,$content);
		
		if( $db->getValue('equipment','count(*)',array('equip_id'=>$selEquip)) )
			$db->update('po_fuel_equipment',array('equip_id'=>$selEquip,'days'=>$txDays,'distance'=>$txDistance),array('po_item_id'=>$txpoidEdt));
		else
			$db->update('po_fuel_equipment',array('other_equip'=>$selEquip,'days'=>$txDays,'distance'=>$txDistance),array('po_item_id'=>$txpoidEdt));
		$_SESSION['notif_id']=$txpoidEdt;
		$_SESSION['notif_success']='Changes Saved!';
		unset($_SESSION['notif_warning']);
	}

	functions::sendTo(functions::pageName().'?po_id='.functions::encode($po_id));
	die();
}

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
	$poidDel = functions::decode($_REQUEST['poidDel']);

	require_once('../class/class-po-history.php');
	PO_history::deleteItem($poidDel,$user_id);

	$db->delete('po_item',array('po_item_id'=>$poidDel));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?po_id='.functions::encode($po_id));
	die();
}

$editTrue=0;
$poidEdt='';
$item='';$quantity='';$brand='';$cost='';$unit='';$qty_delivered='';$discount='';$eqpEdt='';$distance='';$days='';
if( isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ){
	$poidEdt = functions::decode($_REQUEST['poidEdt']);
	$editTrue = $db->getValue('po_item','count(*)',array('po_item_id'=>$poidEdt));
	$qvedt = $db->select('po_item','*',array('po_item_id'=>$poidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$item = $rvedt['item'];
	$quantity = $rvedt['quantity'];
	$qty_delivered = $rvedt['qty_delivered'];
	$unit = $rvedt['unit'];
	$brand = $rvedt['brand'];
	$cost = $rvedt['cost'];
	$discount = $rvedt['discount'];
	$distance = $db->getValue('po_fuel_equipment','distance',array('po_item_id'=>$poidEdt));
	$days = $db->getValue('po_fuel_equipment','days',array('po_item_id'=>$poidEdt));
	$eqpEdt = $db->getValue('po_fuel_equipment','equip_id',array('po_item_id'=>$poidEdt));
	$eqpEdt = ($eqpEdt) ? $eqpEdt : $db->getValue('po_fuel_equipment','other_equip',array('po_item_id'=>$poidEdt));
}

if( $searchedItem ){
	$itemID = $db->getValue('material_reference','mf_id',array('mf_id'=>$searchedItem)); 
	$item = $db->getValue('material_reference','item',array('mf_id'=>$searchedItem));
	$brand = $db->getValue('material_reference','brand',array('mf_id'=>$searchedItem));
	$unit = $db->getValue('material_reference','unit',array('mf_id'=>$searchedItem));
}

if($arrEquip)
	$arr_equip[] = $arrEquip;
$qArrEqp = $db->query('SELECT * FROM po p, po_fuel_equipment pfe WHERE p.po_id=pfe.po_id AND p.ref_id="'.$db->clean($ref_id).'"');

while($rArrEqp = $db->fetch_array($qArrEqp)):
	if( $rArrEqp['equip_id'] !="" )
		$arr_equip = functions::insert_array($arr_equip,$rArrEqp['equip_id']);
	if( $rArrEqp['other_equip'] !="" )
		$arr_equip = functions::insert_array($arr_equip,$rArrEqp['other_equip']);   
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Fuel Purchase Order Item</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Purchase Order Item Details</h2>
		</div>
		<div class="box-content">
			<form method="post">
				Search Item:
				<textarea style="width:280px;" class="span6 typeahead" name="txSrchItem" id="txSrchItem" autocomplete='off' onkeyup="searchMaterial(this.value)" rows="1"></textarea>
				<div id="materialView"></div>
				<input type="hidden" name="txpoidEdt" id="txpoidEdt" value="<?php echo functions::encode($poidEdt);?>">
				<table border="0">
					<tr>
						<td style="padding: 15px">
							<select name="selEquip" id="selEquip" data-rel="chosen" style="width:450px;">>
								<option value="">-- Select Equipment --</option>
								<?php
								foreach($arr_equip as $equipID):
								$equipName = $db->getValue('equipment','name',array('equip_id'=>$equipID));
								?>
								<option value="<?php echo functions::encode($equipID)?>" <?php if($eqpEdt==$equipID || count($arr_equip)==1)echo 'selected="selected"';?>><?php echo ($equipName) ? $equipName : $equipID;?></option>
								<?php endforeach;?>
							</select><br>
							<span class="help-inline warning" id="msgselEquip" style="font-weight:bold;" name="msgselEquip"></span>
						</td>
					</tr>
				</table>
				<table width="100%" border="0" align="center" class="table table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="23%" scope="col"><div align="left">Item</div></th>
							<th width="8%" scope="col"><div align="center">Unit</div></th>
							<th width="12%" scope="col"><div align="center">Brand</div></th>
							<th width="5%" scope="col"><div align="center">Quantity</div></th>
							<th width="5%" scope="col"><div align="center">Distance(km)</div></th>
							<th width="5%" scope="col"><div align="center">Days</div></th>
							<th width="5%" scope="col"><div align="center">Price</div></th>
							<th width="8%" scope="col"><div align="center">Discount(%)</div></th>
							<th width="10%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<tr bgcolor="#faf7f7">
							<td>
								<div align="left">
									<input type="hidden" name="txItem" id="txItem" value="<?php echo ($item) ? functions::encode($item) : ''?>">
									<textarea style="width:90%;" readonly><?php echo $item?></textarea>
									<span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
								</div>
							</td>
							<td><div align="center"><input type="text" style="width:80px;text-align:center;" class="span6 typeahead" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly></div></td>
							<td><div align="center"><input type="text" style="width:180px;text-align:center;" name="txBrand" id="txBrand" value="<?php echo $brand;?>" readonly></div></td>
							<td>
								<div align="center">
									<input type="text" style="width:50px;text-align:center;" name="txQty" id="txQty" value="<?php echo $quantity?>" onkeypress="return checkinput(this, event);">
									<span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
								</div>
							</td>
							<td><div align="center"><input type="text" style="width:50px;text-align:center;" name="txDistance" id="txDistance" value="<?php echo $distance?>" onkeypress="return checkinput(this, event);"></div></td>
							<td><div align="center"><input type="text" style="width:50px;text-align:center;" name="txDays" id="txDays" value="<?php echo $days?>" onkeypress="return checkinput(this, event);"></div></td>
							<td>
								<div align="center">
									<input type="text" style="width:120px;text-align:center;" name="txCost" id="txCost" value="<?php echo ($cost) ? functions::formatMoney($cost) : '';?>" onkeyup="FormatCurrency(this);">
									<span class="help-inline warning" id="msgtxCost" style="font-weight:bold;" name="msgtxCost"></span>
								</div>
							</td>
							<td><div align="center"><input type="text" style="width:80px;text-align:center;" name="txDiscount" id="txDiscount" value="<?php echo $discount?>" onkeypress="return checkinput(this, event);"></div></td>
							<td>
								<div align="center">
								<?php 
								if($editTrue){
									echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary"> ';
									echo '<a href="po_fuel_view.php?po_id='.functions::encode($po_id).'" class="btn btn-mini">Cancel</a>';
								}else
									echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary">';
								?>
								</div>
							</td>
						</tr>
					</tbody>
				</table><br><br><br>
				<table width="100%" border="0" align="center" class="table table-striped table-hover table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="17%" scope="col"><div align="left">Equipment</div></th>
							<th width="15%" scope="col"><div align="left">Item</div></th>
							<th width="5%" scope="col"><div align="center">Quantity</div></th>
							<th width="5%" scope="col"><div align="left">Unit</div></th>
							<th width="12%" scope="col"><div align="left">Brand</div></th>
							<th width="5%" scope="col"><div align="center">Estimate</div></th>
							<th width="5%" scope="col"><div align="right">Price</div></th>
							<th width="4%" scope="col"><div align="right">Discount</div></th>
							<th width="5%" scope="col"><div align="right">Amount</div></th>
							<th width="5%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total_amount=0;$disc_amount=0;$amount=0;
						$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
						while($rPOI = $db->fetch_array($qPOI)):
							$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
							$amount = ($amount) ? $amount : $rPOI['cost'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;

							$distance = $db->getValue('po_fuel_equipment','distance',array('po_item_id'=>$rPOI['po_item_id']));
							$days = $db->getValue('po_fuel_equipment','days',array('po_item_id'=>$rPOI['po_item_id']));
							if($days)
								$days = ($days > 1) ? $days.'days' : $days.'day';
							$distance = ($distance) ? $distance.'km' : '';

							if( $eqpID = $db->getValue('po_fuel_equipment','equip_id',array('po_item_id'=>$rPOI['po_item_id'])) )
								$eqpName = $db->getValue('equipment','name',array('equip_id'=>$eqpID));
							else
								$eqpName = $db->getValue('po_fuel_equipment','other_equip',array('po_item_id'=>$rPOI['po_item_id']));
						?>
						<tr id="<?php echo $rPOI['po_item_id'];?>">
							<td><?php echo $eqpName;?></td>
							<td><?php echo $rPOI['item'];?></td>
							<td><div align="center"><?php echo ($rPOI['qty_delivered']) ? round($rPOI['qty_delivered'],2) : '';?></div></td>
							<td><?php echo $rPOI['unit'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><div align="center"><?php echo ($days && $distance) ? $distance.' / '. $days : $distance.' '. $days;?></div></td>
							<td><div align="right"><?php echo ($rPOI['cost']) ? functions::formatMoney($rPOI['cost']) : '';?></div></td>
							<td><div align="right"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div align="right"><?php echo ($amount) ? functions::formatMoney($amount) : '';?></div></td>
							<td>
								<div align="center">
									<a id="edit<?php echo $rPOI['po_item_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="po_fuel_view.php?po_id=<?php echo functions::encode($po_id);?>&poidEdt=<?php echo functions::encode($rPOI['po_item_id']);?>"><i class="halflings-icon white pencil"></i></a>
									<a id="del<?php echo $rPOI['po_item_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="po_fuel_view.php?po_id=<?php echo functions::encode($po_id);?>&poidDel=<?php echo functions::encode($rPOI['po_item_id']);?>"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><strong></strong></td>
							<td colspan="2"><div align="right">Total Amount &nbsp;&nbsp;&nbsp;<strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
							
							<td></td>
						</tr>
					</tbody>
				</table><p>&nbsp;</p>
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
function delt(){
	if(confirm('Do you want to remove this item?'))
		return true;
	else
		return false; 
}

$(document).ready(function(){
	<?php if(isset($_SESSION['notif_id'])){ ?>
	$('#<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');

	$("#<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	<?php unset($_SESSION['notif_id']);} ?>

	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxItem').html("");
		$('#msgtxQty').html("");
		$('#msgselEquip').html("");
        
		if( $('#selEquip').val()=="" ){
			$('#msgselEquip').html("<br>Equipment Required!");
			res=false;
		}
		else if( $('#txItem').val()=="" ){
			$('#msgtxItem').html("Item Required!");
			res=false;
		}
		else
			res=true;
		return res;
	});
});
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
	var strURL="po_view_material_view.php?srch="+srch+"&poidEdt=<?php echo functions::encode($poidEdt)?>&po_id=<?php echo functions::encode($po_id)?>";
	var req = getXMLHTTP();
	if(req){
		req.onreadystatechange = function(){
			if(req.readyState == 4){
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
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
</body>
</html>