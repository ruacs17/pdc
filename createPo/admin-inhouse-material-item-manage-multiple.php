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

$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;
$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;
$_SESSION['notif_id_inhouse_list']=$im_id;
$txRequestRefID = ( isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : '';
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : '';
$searchedBrnd = (isset($_REQUEST['srcBrnd']) && !empty($_REQUEST['srcBrnd']) ) ? functions::decode($_REQUEST['srcBrnd']) : '';
$searchedUnit = (isset($_REQUEST['srcUnit']) && !empty($_REQUEST['srcUnit']) ) ? functions::decode($_REQUEST['srcUnit']) : '';
$searchedLoc = (isset($_REQUEST['srcLoc']) && !empty($_REQUEST['srcLoc']) ) ? functions::decode($_REQUEST['srcLoc']) : '';
$available = (isset($_REQUEST['srcItmQtyAvlbl']) && !empty($_REQUEST['srcItmQtyAvlbl']) ) ? functions::decode($_REQUEST['srcItmQtyAvlbl']) : 0;


if( isset($_POST['btnAdd']) ){ 
	$txItem = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : 0;
	$txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? $_POST['txUnit'] : '';
	$txBrand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? $_POST['txBrand'] : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$txLoc = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';
	$txRefID = ( isset($_POST['txRefID']) && !empty($_POST['txRefID']) ) ? functions::decode($_POST['txRefID']) : 0;

	$imCount = $db->getValue('inhouse_material','count(*)',array('ref_id'=>$txRefID));
	$eachQty = $txQty / $imCount;
	$eachCost = $txCost;
	$_SESSION['notif_warning']='Fail to Add!';
	if( $txQty && $txItem && $txCost && $txLoc){
		$qInsPO = $db->select('inhouse_material','*',array('ref_id'=>$txRefID));
		while( $rInsPO = $db->fetch_array($qInsPO)):
			$db->insert('inhouse_material_item',array('im_id'=>$rInsPO['im_id'],'item'=>$txItem,'quantity'=>$eachQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$eachCost,'discount'=>$txDiscount,'location'=>$txLoc));
		endwhile;
		unset($_SESSION['notif_warning']);
		$_SESSION['notif_success']='Item Added!';
	}
	functions::sendTo(functions::pageName().'?ref_id='.functions::encode($txRefID));
	die();
}

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) && isset($_REQUEST['ref_idel']) && !empty($_REQUEST['ref_idel']) ){
	$poidDel = functions::decode($_REQUEST['poidDel']);
	$txRefID = functions::decode($_REQUEST['ref_idel']);
	$db->delete('inhouse_material_item',array('imi_id'=>$poidDel));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?ref_id='.functions::encode($txRefID));
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
	$_SESSION['notif_warning']='Fail to Removed!';
	if( $txQty && $txItem && $txCost  && $txLoc){
		$db->update('inhouse_material_item',array('item'=>$txItem,'quantity'=>$txQty,'unit'=>$txUnit,'brand'=>$txBrand,'cost'=>$txCost,'discount'=>$txDiscount,'location'=>$txLoc),array('imi_id'=>$txpoidEdt));
		unset($_SESSION['notif_warning']);
		$_SESSION['notif_success']='Changes Saved!';
	}
	functions::sendTo(functions::pageName().'?ref_id='.functions::encode($ref_id));
	die();
}

$editTrue=0;$poidEdt='';
$item='';$quantity='';$cost='';$unit='';$discount='';$brand=''; $location='';
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>In-House Warehouse Stock</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>In-House Warehouse Stock - Multiple</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<input type="hidden" name="txRefID" id="txRefID" value="<?php echo functions::encode($ref_id);?>">
				<input type="hidden" name="txpoidEdt" id="txpoidEdt" value="<?php echo functions::encode($poidEdt);?>">
				Search Item: 
				<textarea style="width:280px;" class="span6 typeahead" name="txSrchItem" id="txSrchItem" autocomplete='off' onkeyup="searchMaterial(this.value,document.getElementById('selLoc').value)" rows="1"><?php echo $item;?></textarea>
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
				<table width="100%" border="0" align="center" class="table table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="23%" scope="col"><div align="left">Item</div></th>
							<th width="10%" scope="col"><div align="center">Unit</div></th>
							<th width="12%" scope="col"><div align="center">Brand</div></th>
							<th width="10%" scope="col"><div align="center">Quantity</div></th>
							<th width="10%" scope="col"><div align="center">Price</div></th>
							<th width="8%" scope="col"><div align="center">Discount(%)</div></th>
							<th width="10%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<tr bgcolor="#faf7f7">
							<td>
								<div align="left">
									<input type="hidden" name="txItem" id="txItem" value="<?php echo ($item) ? functions::encode($item) : ''?>">
									<textarea style="width:80%;" readonly><?php echo $item?></textarea>
								</div>
								<span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
							</td>
							<td><div align="center"><input type="text" style="width:100px;text-align:center;" class="span6 typeahead" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly></div></td>
							<td><div align="center"><input type="text" style="width:230px;text-align:center;" name="txBrand" id="txBrand" value="<?php echo $brand;?>" readonly></div></td>
							<td>
								<div align="center">
									<select name="txQty" id="txQty" data-rel="chosen" style="width:100px;">
										<option value="">--select--</option>
										<?php for($i=1; $i<=$available; $i++):?>
										<option value="<?php echo $i?>" <?php if($quantity==$i){echo 'selected="selected"';}?>><?php echo $i?></option>
										<?php endfor;?>
									</select>
								</div>
								<span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
							</td>
							<td>
								<div align="center"><input type="text" style="width:120px;text-align:center;" name="txCost" id="txCost" value="<?php echo ($cost) ? functions::formatMoney($cost) : '';?>" onkeyup="FormatCurrency(this);"></div>
								<span class="help-inline warning" id="msgtxCost" style="font-weight:bold;text-align:center;" name="msgtxCost"></span>
							</td>
							<td><div align="center"><input type="text" style="width:80px;text-align:center;" name="txDiscount" id="txDiscount" value="<?php echo $discount?>" onkeypress="return checkinput(this, event);"></div></td>
							<td>
								<div align="center">
								<?php 
								if($editTrue){
								echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary"> ';
								echo '<a href="?ref_id='.functions::encode($ref_id).'" class="btn btn-mini">Cancel</a>';
								}else
								echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary">';
								?>
								</div>
							</td>
						</tr>
					</tbody>
				</table><br><br><br>
				<table width="100%">
				<?php
				$qIM = $db->select('inhouse_material','*',array('ref_id'=>$ref_id));
				while($rIM = $db->fetch_array($qIM)):
					$im_id=$rIM['im_id'];
				?>
					<tr>
						<td height="50px;"><strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rIM['proj_id']))?></strong></td>
					</tr>
					<tr>
						<td>
							<table width="100%" border="0" align="center" class="table table-striped" style="font-size:12px;">
								<thead>
									<tr style="background-color:#CCC;">
										<th width="30%" scope="col"><div align="left">Item</div></th>
										<th width="8%" scope="col"><div align="center">Unit</div></th>
										<th width="15%" scope="col"><div align="left">Brand</div></th>
										<th width="5%" scope="col"><div align="center">Quantity</div></th>
										<th width="7%" scope="col"><div align="right">Price</div></th>
										<th width="7%" scope="col"><div align="center">Discount</div></th>
										<th width="7%" scope="col"><div align="right">Amount</div></th>
										<th width="5%" scope="col">&nbsp;</th>
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
									?>
									<tr>
										<td><?php echo $rPOI['item'];?></td>
										<td><div align="center"><?php echo $rPOI['unit'];?></td>
										<td><?php echo $rPOI['brand'];?></td>
										<td><div align="center"><?php echo round($rPOI['quantity'],2);?></div></td>
										<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
										<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
										<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
										<td>
											<div align="right">
												<a id="edit<?php echo $rPOI['im_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?im_id=<?php echo functions::encode($im_id);?>&poidEdt=<?php echo functions::encode($rPOI['imi_id']);?>&ref_id=<?php echo functions::encode($ref_id);?>"><i class="halflings-icon white pencil"></i></a>
												<a id="del<?php echo $rPOI['im_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?im_id=<?php echo functions::encode($im_id);?>&poidDel=<?php echo functions::encode($rPOI['imi_id']);?>&ref_idel=<?php echo functions::encode($ref_id);?>"><i class="halflings-icon white trash"></i></a>
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
										<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
										<td></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<?php endwhile;?>
				</table>
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
		window.location="<?php echo functions::pageName()?>?proj_id="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
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
	var strURL="admin-inhouse-material-list-multiple.php?srch="+srch+"&loc="+loc+"&poidEdt=<?php echo functions::encode($poidEdt)?>&ref_id=<?php echo functions::encode($ref_id)?>";
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