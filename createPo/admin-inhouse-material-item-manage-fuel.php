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
$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : '';
$searchedBrnd = (isset($_REQUEST['srcBrnd']) && !empty($_REQUEST['srcBrnd']) ) ? functions::decode($_REQUEST['srcBrnd']) : '';
$searchedUnit = (isset($_REQUEST['srcUnit']) && !empty($_REQUEST['srcUnit']) ) ? functions::decode($_REQUEST['srcUnit']) : '';
$searchedLoc = (isset($_REQUEST['srcLoc']) && !empty($_REQUEST['srcLoc']) ) ? functions::decode($_REQUEST['srcLoc']) : '';
$available = (isset($_REQUEST['srcItmQtyAvlbl']) && !empty($_REQUEST['srcItmQtyAvlbl']) ) ? functions::decode($_REQUEST['srcItmQtyAvlbl']) : 0;
$project_id = $db->getValue('inhouse_material','proj_id',array('im_id'=>$im_id));
$sel_equip='';
if( isset($_POST['btnAdd']) ){ 
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : 0;
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? $_POST['txUnit'] : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? $_POST['txBrand'] : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$txLoc = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';
	$equip_id = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
	$equip_other = ( isset($_POST['txEquipOthers']) && !empty($_POST['txEquipOthers']) ) ? trim($_POST['txEquipOthers']) : '';

	if( $txQty && $txItem && $txCost && $txLoc){
		$insID = $db->insert('inhouse_material_item',array('im_id'=>$im_id,'item'=>$txItem,'quantity'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost,'discount'=>$txDiscount,'location'=>$txLoc));
		#$db->query($qIns);
		#$insID = $db->insert_id();
		if($insID){
			if($equip_id)
				$db->insert('inhouse_material_fuel_equipment',array('im_id'=>$im_id,'imi_id'=>$insID,'equip_id'=>$equip_id));
			elseif($equip_other)
				$db->insert('inhouse_material_fuel_equipment',array('im_id'=>$im_id,'imi_id'=>$insID,'other_equip'=>$equip_other));
		}
	}
	functions::sendTo($_SERVER['PHP_SELF'].'?im_id='.functions::encode($im_id));
	die();
}

if( isset($_POST['btnSave']) ){
	$txpoidEdt = ( isset($_POST['txpoidEdt']) && !empty($_POST['txpoidEdt']) ) ? functions::decode($_POST['txpoidEdt']) : '0';
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : 0;
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? $_POST['txUnit'] : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? $_POST['txBrand'] : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : '0';
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$txLoc = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';
	$equip_id = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
	$equip_other = ( isset($_POST['txEquipOthers']) && !empty($_POST['txEquipOthers']) ) ? trim($_POST['txEquipOthers']) : '';

	if( $txQty && $txItem && $txCost && $txLoc){
		$db->update('inhouse_material_item',array('item'=>$txItem,'quantity'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost,'discount'=>$txDiscount,'location'=>$txLoc),array('imi_id'=>$txpoidEdt));

		if($equip_id)
			$db->update('inhouse_material_fuel_equipment',array('equip_id'=>$equip_id,'other_equip'=>''),array('imi_id'=>$txpoidEdt));
		elseif($equip_other){
			$db->update('inhouse_material_fuel_equipment',array('other_equip'=>$equip_other),array('imi_id'=>$txpoidEdt));
			$db->query('UPDATE inhouse_material_fuel_equipment SET equip_id=NULL WHERE imi_id="'.$db->clean($txpoidEdt).'"');
		}
	}
	functions::sendTo($_SERVER['PHP_SELF'].'?im_id='.functions::encode($im_id));
	die();
}

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
	$poidDel = functions::decode($_REQUEST['poidDel']);
	$db->delete('inhouse_material_item',array('imi_id'=>$poidDel));
	functions::sendTo($_SERVER['PHP_SELF'].'?im_id='.functions::encode($im_id));
	die();
}

$editTrue=0;
$poidEdt='';
$item='';$quantity='';$cost='';$unit='';$discount='';$brand=''; $location='';$other_eqp='';
if( isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ){
	$poidEdt = functions::decode($_REQUEST['poidEdt']);
	$editTrue = $db->getValue('inhouse_material_item','count(*)',array('imi_id'=>$poidEdt));
	$qvedt = $db->select('inhouse_material_item','*',array('imi_id'=>$poidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$item = $rvedt['item'];
	$quantity = $rvedt['quantity'];
	$unit = $rvedt['unit'];
	$brand = $rvedt['brand'];
	$cost = $rvedt['cost'];
	$discount = $rvedt['discount'];
	$location = $rvedt['location'];
	$sel_equip = $db->getValue('inhouse_material_fuel_equipment','equip_id',array('imi_id'=>$poidEdt));
	$other_eqp = $db->getValue('inhouse_material_fuel_equipment','other_equip',array('imi_id'=>$poidEdt));
	if($available==0){
		$consumed = $db->getValue('inhouse_material_item','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$stored = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$available = ($stored + $quantity) - $consumed;
	}
}

if( $searchedItem ){
	$item = $searchedItem;
	$brand = $searchedBrnd;
	$unit = $searchedUnit;
	$location = $searchedLoc;
	if($available==0){
		$consumed = $db->getValue('inhouse_material_item','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$stored = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$available = $stored - $consumed;
	}
}
$qOtherEquip = $db->query('SELECT DISTINCT other_equip FROM inhouse_material_fuel_equipment WHERE other_equip <> "" ');
$namesOtherEquip='';
while($rOtherEquip=$db->fetch_array($qOtherEquip)):
	$string = preg_replace("/'/",'"',$rOtherEquip['other_equip']);
	$namesOtherEquip .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesOtherEquip .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>In-House Warehouse Stock - Equipment Fuel</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>In-House Warehouse Stock - Equipment Fuel</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="left"><br>
					<div>Project / Payee:
						<strong>
						<?php 
						if($project_id){
							$qProj = $db->select('project','*',array('proj_id'=>$project_id));
							while($rProj = $db->fetch_array($qProj)):
								echo strtoupper($rProj['proj_name']);
								echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
							endwhile;
						}
						else
							echo $db->getValue('inhouse_material','payee',array('im_id'=>$im_id));
						?>
						</strong>
					</div>
					<div>Date: <strong><?php echo functions::datearr($db->getValue('inhouse_material','im_date',array('im_id'=>$im_id)));?></strong></div><br><br>
				</div>
				<input type="hidden" name="txpoidEdt" id="txpoidEdt" value="<?php echo functions::encode($poidEdt);?>">
				Search Item: 
				<textarea style="width:480px;" class="span6 typeahead" name="txSrchItem" id="txSrchItem" autocomplete='off' onkeyup="searchMaterial(this.value,document.getElementById('selLoc').value)" rows="1"><?php echo $item;?></textarea>
				<select name="selLoc" id="selLoc" onChange="searchMaterial(document.getElementById('txSrchItem').value,this.value)">
					<option value="">--All Location--</option>
					<?php
					$qLoc = $db->select('inhouse_material_storage_location','*',array());
					while($rLoc = $db->fetch_array($qLoc)):
					?>
					<option value="<?php echo functions::encode($rLoc['location'])?>" <?php if($location==$rLoc['location']){echo 'selected="selected"';}?>><?php echo $rLoc['location'];?></option>
					<?php endwhile;?>
				</select>
				<div id="materialView"></div>
				<input type="text" style="width:100px; display:none;" class="span6 typeahead" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly>
				<table width="100%" border="0" align="center" class="table table-striped">
					<tr>
						<th scope="col"><div align="left">Equipment</div></th>
						<th scope="col"><div align="center">Item</div></th>
						<th scope="col"><div align="center">Brand</div></th>
						<th scope="col"><div align="center">Quantity</div></th>
						<th scope="col"><div align="center">Price</div></th>
						<th width="8%" scope="col"><div align="center">Discount(%)</div></th>
						<th scope="col">&nbsp;</th>
					</tr>
					<tr bgcolor="#faf7f7">
						<td>
							<div align="left">
								<table border="0" class="">
									<tr>
										<td style="padding: 10px 5px 5px 5px"><input type="radio" name="rdoEquip" id="rdoEquip1" value="1" onclick="document.getElementById('txEquipOthers').disabled=true;"></td>
										<td style="padding: 10px 0px 0px 4px">
											<select name="selEquip" id="selEquip" data-rel="chosen" style="width:340px;font-size:12px;height:60px;" onchange="document.getElementById('rdoEquip1').checked=true;document.getElementById('txEquipOthers').disabled=true;">
												<option value="">-- Select Equipment --</option>
												<?php $qEquip = $db->select('equipment','*',array(),'ORDER BY type');
												while($rEquip = $db->fetch_array($qEquip)):
												?>
												<option value="<?php echo functions::encode($rEquip['equip_id'])?>" <?php if($sel_equip==$rEquip['equip_id'])echo 'selected="selected"';?>><?php echo strtolower($rEquip['inventory_id']).' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
												<?php endwhile;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 12px 5px 5px 5px"><input type="radio"name="rdoEquip" id="rdoEquip2" value="2" onclick="document.getElementById('txEquipOthers').disabled=false;"></td>
										<td style="padding: 10px 0px 0px 4px"><input type="text" name="txEquipOthers" id="txEquipOthers" value="<?php echo $other_eqp?>" style="width:340px;" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesOtherEquip;?>]'/></td>
									</tr>
								</table>
							</div>
						</td>
						<td>
							<div align="left">
								<input type="hidden" name="txItem" id="txItem" value="<?php echo ($item) ? functions::encode($item) : ''?>">
								<textarea readonly><?php echo $item?></textarea>
							</div>
							<span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
						</td>
						<td><div align="left"><textarea style="width:100px;" name="txBrand" id="txBrand" readonly><?php echo $brand;?></textarea></div></td>
						<td>
							<div style="float: left;">
								<select name="txQty" id="txQty" data-rel="chosen" style="width:100px;">
									<option value="">--select--</option>
									<?php for($i=1; $i<=$available; $i++):?>
									<option value="<?php echo $i?>" <?php if($quantity==$i){echo 'selected="selected"';}?>><?php echo $i?></option>
									<?php endfor;?>
								</select>
							</div>
							<div style="float: right; padding-top: 2px;"><?php echo ($unit) ? '('.$unit.')' : '';?></div>
							<span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
						</td>
						<td>
							<div align="center"><input type="text" style="width:120px;" name="txCost" id="txCost" value="<?php echo ($cost) ? functions::formatMoney($cost) : '';?>" onkeyup="FormatCurrency(this);"></div>
							<span class="help-inline warning" id="msgtxCost" style="font-weight:bold;" name="msgtxCost"></span>
						</td>
						<td><div align="center"><input type="text" style="width:80px;" name="txDiscount" id="txDiscount" value="<?php echo $discount?>" onkeypress="return checkinput(this, event);"></div></td>
						<td>
							<div align="center">
								<?php 
								if($editTrue){
									echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary"> ';
									echo '<a href="?im_id='.functions::encode($im_id).'" class="btn btn-mini">Cancel</a>';
								}else
									echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary">';
								?>
							</div>
						</td>
					</tr>
				</table><br><br><br>
				<table width="100%" border="0" align="center" class="table table-striped">
					<thead>
						<tr bgcolor="#ccc">
							<th width="16%" scope="col"><div align="left">Equipment</div></th>
							<th width="14%" scope="col"><div align="left">Item</div></th>
							<th width="10%" scope="col"><div align="left">Brand</div></th>
							<th width="9%" scope="col"><div align="left">Quantity</div></th>
							<th width="7%" scope="col"><div align="left">Price</div></th>
							<th width="7%" scope="col"><div align="left">Discount</div></th>
							<th width="8%" scope="col"><div align="left">Amount</div></th>
							<th width="7%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total_amount=0;
						$disc_amount=0;
						$amount=0;
						$qPOI = $db->select('inhouse_material_item','*',array('im_id'=>$im_id));
						while($rPOI = $db->fetch_array($qPOI)):
							$amount = $rPOI['cost'] * $rPOI['quantity'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;
							$eqp_id = $db->getValue('inhouse_material_fuel_equipment','equip_id',array('imi_id'=>$rPOI['imi_id']));
							$eqpName = ($eqp_id) ? $db->getValue('equipment','name',array('equip_id'=>$eqp_id)) : $db->getValue('inhouse_material_fuel_equipment','other_equip',array('imi_id'=>$rPOI['imi_id']));
						?>
						<tr>
							<td><?php echo $eqpName?></td>
							<td><?php echo $rPOI['item'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><?php echo round($rPOI['quantity'],2); echo ($rPOI['unit']) ? ' ('.$rPOI['unit'].')' : ''?></td>
							<td><?php echo functions::formatMoney($rPOI['cost']);?></td>
							<td><?php echo $rPOI['discount'];?>%</td>
							<td><?php echo functions::formatMoney($amount);?></td>
							<td>
								<div align="center">
									<a id="edit<?php echo $rPOI['im_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?im_id=<?php echo functions::encode($im_id);?>&poidEdt=<?php echo functions::encode($rPOI['imi_id']);?>"><i class="halflings-icon white pencil"></i></a>
									<a id="del<?php echo $rPOI['im_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?im_id=<?php echo functions::encode($im_id);?>&poidDel=<?php echo functions::encode($rPOI['imi_id']);?>"><i class="halflings-icon white trash"></i></a>
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
							<td><div align="right"><strong>Total Amount</strong></div></td>
							<td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
							<td></td>
						</tr>
					</tbody>
				</table>
				<p>&nbsp;</p>
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
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
}

$(document).ready(function(){
	<?php echo ($other_eqp) ? "$('#rdoEquip2').attr('checked','checked');" : "$('#txEquipOthers').prop('disabled',true);\n";?>
	<?php echo ($sel_equip) ? "$('#rdoEquip1').attr('checked','checked');\n" : "";?>
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxItem').html("");
		$('#msgtxQty').html("");
		$('#msgtxQtyDel').html("");

		if( $('#txItem').val()=="" ){
			$('#msgtxItem').html("Specify Item!");
			$('#txItem').focus();
			res=false;
		}
		else if( $('#txQty').val()=="" ){
			$('#msgtxQty').html("Required!");
			$('#txQty').focus();
			res=false;
		}
		else if( $('#txCost').val()=="" ){
			$('#msgtxCost').html("Required!");
			$('#txCost').focus();
			res=false;
		}
		else
			res=true;

		return res;
	});
});
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo $_SERVER['PHP_SELF']?>?proj_id="+PiEwgD
	else
		window.location="<?php echo $_SERVER['PHP_SELF']?>"
}
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
  
function searchMaterial(srch,loc) {   
	var strURL="admin-inhouse-material-list.php?srch="+srch+"&loc="+loc+"&poidEdt=<?php echo functions::encode($poidEdt)?>&im_id=<?php echo functions::encode($im_id)?>";
	var req = getXMLHTTP();
	if(req){
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					document.getElementById('materialView').innerHTML=req.responseText;
				}
				else{
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
<!-- end: JavaScript-->
</body>
</html>