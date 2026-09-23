<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$ppe_id = (isset($_REQUEST['ppe_id']) && !empty($_REQUEST['ppe_id']) ) ? functions::decode($_REQUEST['ppe_id']) : 0;

$item='';$quantity='';$cost='';$unit='';$discount='';$brand=''; $location='';
$ppei_type='';$ppei_brand=''; $ppei_qty='';$ppei_size='';$issued_count='';$issued_date=date('Y-m-d');$replacement_date='';$issuance_mode='';$remark='';$end_user='';$returned='';
if( isset($_REQUEST['txItmDel']) && !empty($_REQUEST['txItmDel']) ){
	$txItmDel = functions::decode($_REQUEST['txItmDel']);
	$db->delete('ppe_items',array('ppei_id'=>$txItmDel));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?ppe_id='.functions::encode($ppe_id));
	die();
}
$editTrue=0;
$txItmEdt='';

$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : '';
$searchedBrnd = (isset($_REQUEST['srcBrnd']) && !empty($_REQUEST['srcBrnd']) ) ? functions::decode($_REQUEST['srcBrnd']) : '';
$searchedUnit = (isset($_REQUEST['srcUnit']) && !empty($_REQUEST['srcUnit']) ) ? functions::decode($_REQUEST['srcUnit']) : '';
$searchedLoc = (isset($_REQUEST['srcLoc']) && !empty($_REQUEST['srcLoc']) ) ? functions::decode($_REQUEST['srcLoc']) : '';
#$available = (isset($_REQUEST['srcItmQtyAvlbl']) && !empty($_REQUEST['srcItmQtyAvlbl']) ) ? functions::decode($_REQUEST['srcItmQtyAvlbl']) : 0;
$available=0;
if( $searchedItem ){
	$item = $searchedItem;
	$brand = $searchedBrnd;
	$unit = $searchedUnit;
	$location = $searchedLoc;
	if($available==0){
		$consumed = $db->getValue('ppe_items','sum(quantity-return_qty)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$stored = $db->getValue('ppe_material_storage','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$available = $stored - $consumed;
	}
}

if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
	$txItmEdt = functions::decode($_REQUEST['txItmEdt']);
	$editTrue = $db->getValue('ppe_items','count(*)',array('ppei_id'=>$txItmEdt));
	$qvedt = $db->select('ppe_items','*',array('ppei_id'=>$txItmEdt));
	while($rvedt = $db->fetch_array($qvedt)):
		$item=$rvedt['item'];
		$brand=$rvedt['brand'];
		$quantity=$rvedt['quantity'];
		$unit=$rvedt['unit'];
		$location=$rvedt['location'];
		$consumed = $db->getValue('ppe_items','sum(quantity-return_qty)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$stored = $db->getValue('ppe_material_storage','sum(quantity)',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'location'=>$location));
		$available = $stored - $consumed + $quantity;
		$issued_date=$rvedt['issued_date'];
		$replacement_date=$rvedt['replacement_date'];
		$issued_count=$rvedt['issued_count'];
		$issuance_mode=$rvedt['issuance_mode'];
		$remark=$rvedt['remark'];
	endwhile;
}

$namesMode='';
$qItemMode = $db->query('SELECT DISTINCT issuance_mode as "itm" FROM ppe_item ORDER BY issuance_mode');
while($rMode=$db->fetch_array($qItemMode)):
	$string = preg_replace("/'/",'"',$rMode['itm']);
	$namesMode .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesMode .= '"--"';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>IPPE Manage</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
<?php
if( isset($_POST['btnAdd']) ){
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
	$unit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
	$quantity = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? trim($_POST['txQty']) : '';
	$brand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? trim($_POST['txBrand']) : '';
	$location = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';
	$issued_date = ( isset($_POST['txDateIssued']) && !empty($_POST['txDateIssued']) ) ? trim($_POST['txDateIssued']) : NULL;
	$issued_count = ( isset($_POST['txIssuedCount']) && !empty($_POST['txIssuedCount']) ) ? trim($_POST['txIssuedCount']) : 0;
	$replacement_date = ( isset($_POST['txDateReplace']) && !empty($_POST['txDateReplace']) ) ? trim($_POST['txDateReplace']) : NULL;
	$issuance_mode = ( isset($_POST['txIssuanceMode']) && !empty($_POST['txIssuanceMode']) ) ? strtoupper(trim($_POST['txIssuanceMode'])) : '';
	$remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';

	if( !empty($item) && !empty($quantity) && ($available >= $quantity) ){
		$ins = $db->insert('ppe_items',array('ppe_id'=>$ppe_id,'item'=>$item,'unit'=>$unit,'quantity'=>$quantity,'brand'=>$brand,'location'=>$location,'issued_date'=>$issued_date,'issued_count'=>$issued_count,'replacement_date'=>$replacement_date,'issuance_mode'=>$issuance_mode,'remark'=>$remark));
		if($ins){
			$_SESSION['notif_success']='Successfully Added!';
			$_SESSION['notif_detl_list']=$ins;
			functions::sendTo(functions::pageName().'?ppe_id='.functions::encode($ppe_id));
			die();
		}
		else
			functions::say('Please fill up the form properly.!');
	}
	else
		functions::say('Please fill up the form properly!');
}

if( isset($_POST['btnSave']) ){
	$ppei_id_Edt = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : 0;
	$item = ( isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? functions::decode($_POST['txItem']) : '';
	$unit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
	$quantity = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? trim($_POST['txQty']) : '';
	$brand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? trim($_POST['txBrand']) : '';
	$location = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';
	$issued_date = ( isset($_POST['txDateIssued']) && !empty($_POST['txDateIssued']) ) ? trim($_POST['txDateIssued']) : NULL;
	$issued_count = ( isset($_POST['txIssuedCount']) && !empty($_POST['txIssuedCount']) ) ? trim($_POST['txIssuedCount']) : 0;
	$replacement_date = ( isset($_POST['txDateReplace']) && !empty($_POST['txDateReplace']) ) ? trim($_POST['txDateReplace']) : NULL;
	$issuance_mode = ( isset($_POST['txIssuanceMode']) && !empty($_POST['txIssuanceMode']) ) ? strtoupper(trim($_POST['txIssuanceMode'])) : '';
	$remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';

	if( !empty($ppei_id_Edt) && !empty($item) && !empty($quantity) && ($available >= $quantity) ){
		$db->update('ppe_items',array('item'=>$item,'unit'=>$unit,'quantity'=>$quantity,'brand'=>$brand,'location'=>$location,'issued_count'=>$issued_count,'issued_date'=>$issued_date,'replacement_date'=>$replacement_date,'issuance_mode'=>$issuance_mode,'remark'=>$remark),array('ppei_id'=>$ppei_id_Edt,'ppe_id'=>$ppe_id));
		$_SESSION['notif_success']='Changes Saved!';
		$_SESSION['notif_detl_list']=$ppei_id_Edt;
		functions::sendTo(functions::pageName().'?ppe_id='.functions::encode($ppe_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}

?>
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
	<form method="post">
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header" data-original-title>
					<h2><i class="halflings-icon white edit"></i><span class="break"></span>IPPE Item Manage</h2>
				</div>
				<div class="box-content">
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
					<div id="materialView"></div><br>
					<table width="100%" border="0" align="center" style="font-size: 12px;">
						<tr>
							<td>
								<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>"><br><br>
								<table width="100%" border="0" align="center" class="table">
									<tr>
										<td width="40%"><div align="right"><strong>Item</strong></div></td>
										<td>
											<div align="left">
												<input type="hidden" name="txItem" id="txItem" value="<?php echo ($item) ? functions::encode($item) : ''?>">
												<textarea style="width:400px;" readonly><?php echo $item?></textarea>
												<span class="help-inline warning" id="msgtxItem" style="font-weight:bold;" name="msgtxItem"></span>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="right"><strong>Brand</strong></div></td>
										<td><div align="left"><input type="text" style="width:400px;" name="txBrand" id="txBrand" value="<?php echo $brand?>" readonly></div></td>
									</tr>
									<tr>
										<td><div align="right"><strong>Quantity</strong></div></td>
										<td>
											<div align="left">
												<select name="txQty" id="txQty" data-rel="chosen" style="width:100px;">
													<option value="">--select--</option>
													<?php for($i=1; $i<=$available; $i++):?>
													<option value="<?php echo $i?>" <?php if($quantity==$i){echo 'selected="selected"';}?>><?php echo $i?></option>
													<?php endfor;?>
												</select>
												&nbsp;<input type="text" name="txUnit" id="txUnit" value="<?php echo $unit?>" style="width:70px;" readonly>
												<span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="right"><strong>No. Of Times Issued</strong></div></td>
										<td><div align="left"><input type="text" name="txIssuedCount" id="txIssuedCount" value="<?php echo $issued_count?>"></div></td>
									</tr>
									<tr>
										<td><div align="right"><strong>Date Issued</strong></div></td>
										<td>
											<div align="left">
												<a href="javascript:NewCssCal('txDateIssued')">
													<img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
													<input name="txDateIssued" type="text" class="span6 mytextbox" id="txDateIssued" value="<?php echo $issued_date?>" style="width: 90px;" readonly>
												</a>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="right"><strong>Replacement Date</strong></div></td>
										<td>
											<div align="left">
												<a href="javascript:NewCssCal('txDateReplace')">
													<img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
													<input name="txDateReplace" type="text" class="span6 mytextbox" id="txDateReplace" value="<?php echo $replacement_date?>" style="width: 90px;" readonly>
												</a>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="right"><strong>Mode of Issuance</strong></div></td>
										<td><div align="left"><input type="text" name="txIssuanceMode" id="txIssuanceMode" value="<?php echo $issuance_mode?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesMode;?>]'></div></td>
									</tr>
									<tr>
										<td><div align="right"><strong>Remark</strong></div></td>
										<td><div align="left"><textarea name="txRemark" id="txRemark"><?php echo $remark?></textarea></div></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td>
											<div align="left">
											<?php 
											if($editTrue){
												echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary">&nbsp;';
												echo '<a href="?ppe_id='.functions::encode($ppe_id).'" class="btn btn-mini">Cancel</a>';
											}
											else
												echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-small">';
											?>
											</div>
										</td>
									</tr>
								</table>
								<table id="tblist" width="100%" border="1" style="font-size: 11px;" class="table-hover">
									<thead>
										<tr style="background-color:#CCC;">
											<th width="21%" scope="col" height="30"><div align="left" style="padding-left:5px;">Item</div></th>
											<th width="10%" scope="col"><div align="center">Brand</div></th>
											<th width="7%" scope="col"><div align="center">Quantity</div></th>
											<th width="7%" scope="col"><div align="center">Unit</div></th>
											<th width="7%" scope="col"><div align="center">No. Of Times Issued</div></th>
											<th width="8%" scope="col"><div align="center">Date Issued</div></th>
											<th width="8%" scope="col"><div align="center">Replacement Date</div></th>
											<th width="14%" scope="col"><div align="center">Mode of Issuance</div></th>
											<th width="10%" scope="col"><div align="center">Remarks</div></th>
											<th width="8%" scope="col">&nbsp;</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$qItem = $db->select('ppe_items','*',array('ppe_id'=>$ppe_id),'ORDER BY ppei_id');
										while($rItem = $db->fetch_array($qItem)):
										$rID = $rItem['ppei_id'];
										?>
										<tr id="rw<?php echo $rID;?>">
											<td height="30"><div align="left" style="padding-left:5px;"><?php echo $rItem['item'];?></div></td>
											<td><div align="center"><?php echo $rItem['brand'];?></div></td>
											<td><div align="center"><?php echo $rItem['quantity'];?></div></td>
											<td><div align="center"><?php echo $rItem['unit'];?></div></td>
											<td><div align="center"><?php echo $rItem['issued_count'];?></div></td>
											<td><div align="center"><?php echo functions::datearr($rItem['issued_date']);?></div></td>
											<td><div align="center"><?php echo functions::datearr($rItem['replacement_date']);?></div></td>
											<td><div align="center"><?php echo $rItem['issuance_mode'];?></div></td>
											<td><div align="center"><?php echo $rItem['remark'];?></div></td>
											<td>
												<div align="center">
													<a id="edit<?php echo $rID;?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?ppe_id=<?php echo functions::encode($ppe_id);?>&txItmEdt=<?php echo functions::encode($rID);?>"><i class="halflings-icon white pencil"></i></a>
													<a id="del<?php echo $rID;?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?ppe_id=<?php echo functions::encode($ppe_id);?>&txItmDel=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
												</div>
											</td>
										</tr>
										<?php endwhile;?>
									</tbody>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</form>
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
</script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxItem').html("");
		$('#msgtxQty').html("");

		if( $('#txItem').val()=="" ){
			$('#msgtxItem').html("Specify Item!");
			$('#txSrchItem').focus();
			res=false;
		}
		else if( $('#txQty').val()=="" ){
			$('#msgtxQty').html("Quantity Required!");
			$('#txQty').focus();
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
	var strURL="admin-ppe-item-search.php?srch="+srch+"&loc="+loc+"&txItmEdt=<?php echo functions::encode($txItmEdt)?>&ppe_id=<?php echo functions::encode($ppe_id)?>";
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
<?php if(isset($_SESSION['notif_detl_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_detl_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_detl_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_detl_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
});
</script>
<?php unset($_SESSION['notif_detl_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>