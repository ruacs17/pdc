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
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$searchedItem = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? functions::decode($_REQUEST['mf_id']) : 0;
$itemEdt = (isset($_REQUEST['itemEdt']) && !empty($_REQUEST['itemEdt']) ) ? functions::decode($_REQUEST['itemEdt']) : 0;

$poidEdt=0;


$item='';$quantity='';$brand='';$cost='';$unit='';$qty_delivered='';$discount='';
#if( $searchedItem ){
$qRef = $db->select('material_reference','*',array('mf_id'=>$searchedItem));
$rRef = $db->fetch_array($qRef);
$itemID = $rRef['mf_id'] ;
$item = $rRef['item'];
$brand = $rRef['brand'];
$unit = $rRef['unit'];
$category = ($rRef['category']) ? $rRef['category'] : '--undefined--';
$classification = ($rRef['classification']) ? $rRef['classification'] : '--undefined--';
$type = ($rRef['mtype']) ? $rRef['mtype'] : '--undefined--';


$qI = $db->select('inhouse_material_storage','*',array('ims_id'=>$itemEdt));
$rI = $db->fetch_array($qI);
$cost=$rI['price'];
$quantity=$rI['quantity'];
$ims_date=$rI['ims_date'];
$location=$rI['location'];

$txRawNo = $rI['raw_no'];
$txStandNo = $rI['stand_no'];
$txDividerNo = $rI['divider_no'];
$txSequenceNo = $rI['sequence_no'];
$txSeriesNo = $rI['series_no'];
if($ims_date){
	$_SESSION['notif_id_list2']=$ims_date;
}

if( isset($_POST['btnAdd']) ){

	$txItem = ( isset($_POST['txItemID']) && !empty($_POST['txItemID']) ) ? functions::decode($_POST['txItemID']) : 0;
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? functions::moneyToDouble($_POST['txQty']) : 0;
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	$ims_date = ( isset($_POST['txDate']) && functions::valid_date($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$locID = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';

	$txRawNo = ( isset($_POST['txRawNo']) && !empty($_POST['txRawNo']) ) ? trim($_POST['txRawNo']) : '';
	$txStandNo = ( isset($_POST['txStandNo']) && !empty($_POST['txStandNo']) ) ? trim($_POST['txStandNo']) : '';
	$txDividerNo = ( isset($_POST['txDividerNo']) && !empty($_POST['txDividerNo']) ) ? trim($_POST['txDividerNo']) : '';
	$txSequenceNo = ( isset($_POST['txSequenceNo']) && !empty($_POST['txSequenceNo']) ) ? trim($_POST['txSequenceNo']) : '';
	$txSeriesNo = ( isset($_POST['txSeriesNo']) && !empty($_POST['txSeriesNo']) ) ? trim($_POST['txSeriesNo']) : '';
	
	$_SESSION['notif_warning']='Stock Adding Fail!';
	if( $txItem && $ims_date && $locID){

		if( $db->getValue('material_reference','count(*)',array('mf_id'=>$txItem)) ){

			$qRef = $db->select('material_reference','*',array('mf_id'=>$txItem));
			$rRef = $db->fetch_array($qRef);
			$itemName = $rRef['item'];
			$brand = $rRef['brand'];
			$unit = $rRef['unit'];
			$location = $db->getValue('inhouse_material_storage_location','location',array('imsl_id'=>$locID));

			$db->update('inhouse_material_storage',array('item'=>$itemName,'unit'=>$unit,'brand'=>$brand,'price'=>$txCost,'ims_date'=>$ims_date,'quantity'=>$txQty,'location'=>$location,'raw_no'=>$txRawNo,'stand_no'=>$txStandNo,'divider_no'=>$txDividerNo,'sequence_no'=>$txSequenceNo,'series_no'=>$txSeriesNo),array('ims_id'=>$itemEdt));
			$_SESSION['notif_success']='Inventory Updated!';
			unset($_SESSION['notif_warning']);
		}
		functions::sendTo(functions::pageName().'?mf_id='.functions::encode($searchedItem).'&itemEdt='.functions::encode($itemEdt));
		die();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Material Stock Add</title>
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
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Stock Update</h2>
		</div>
		<div class="box-content">
			<div align="center">
			<div class="box-content" style="width:750px;">
				<form class="form-horizontal" method="post">
					<fieldset>
						<div class="control-group">
							<label class="control-label" for="txItemID"><strong>Item</strong></label>
							<div class="controls" align="left">
								<input type="hidden" name="txItemID" id="txItemID" value="<?php echo ($searchedItem) ? functions::encode($searchedItem) : ''?>">
								<textarea style="width:80%;" rows="2" name="txItemNme" id="txItemNme" readonly><?php echo $item?></textarea>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="txUnit"><strong>Unit</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:70px;" class="span6 typeahead" name="txUnit" id="txUnit" value="<?php echo $unit?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txBrand"><strong>Brand</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:150px;" name="txBrand" id="txBrand" value="<?php echo $brand;?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txQty"><strong>Quantity</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:80px;" name="txQty" id="txQty" value="<?php echo $quantity?>" onkeypress="return checkinput(this, event);" onkeyup="FormatCurrency(this);" <?php echo empty($searchedItem) ? 'disabled' : ''?> required>
								<span class="help-inline warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></span>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txCost"><strong>Price</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;;" name="txCost" id="txCost" value="<?php echo ($cost) ? functions::formatMoney($cost) : '';?>" onkeyup="FormatCurrency(this);">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txDate"><strong>Date</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width: 80px;" name="txDate" id="txDate" value="<?php echo ($ims_date) ? $ims_date : date('Y-m-d') ?>" <?php echo empty($searchedItem) ? 'disabled' : ''?> required>
								<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txBrand"><strong>Storage Location</strong></label>
							<div class="controls" align="left">
								<select name="selLoc" id="selLoc" style="width:400px;" readonly>
									<option value="">--select--</option>
									<?php 
									$q=$db->select('inhouse_material_storage_location','*',array(),'ORDER BY location');
									while($r = $db->fetch_array($q)):
									?>
									<option value="<?php echo functions::encode($r['imsl_id'])?>" <?php if($location==$r['location']){echo 'selected="selected"';} ?>><?php echo $r['location']?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="msgLoc" style="font-weight:bold;" name="msgLoc"></span>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txCat"><strong>Category</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:385px;" name="txCat" id="txCat" value="<?php echo $category?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txRawNo"><strong>Raw No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txRawNo" id="txRawNo" value="<?php echo $txRawNo;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txClass"><strong>Classification</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:385px;" name="txClass" id="txClass" value="<?php echo $classification;?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txStandNo"><strong>Stand No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txStandNo" id="txStandNo" value="<?php echo $txStandNo;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txDividerNo"><strong>Divider No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txDividerNo" id="txDividerNo" value="<?php echo $txDividerNo;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txType"><strong>Type</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:385px;" name="txType" id="txType" value="<?php echo $type;?>" readonly>
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txSequenceNo"><strong>Sequence No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txSequenceNo" id="txSequenceNo" value="<?php echo $txSequenceNo;?>">
							</div>
						</div>
						<div class="control-group hidden-phone">
							<label class="control-label" for="txSeriesNo"><strong>Series No.</strong></label>
							<div class="controls" align="left">
								<input type="text" style="width:120px;" name="txSeriesNo" id="txSeriesNo" value="<?php echo $txSeriesNo;?>">
							</div>
						</div>
						<div class="control-group hidden-phone" style="padding-top:20px;">
							<label class="control-label" for="btnAdd"><strong>&nbsp;</strong></label>
							<div class="controls" align="left">
								<?php if($searchedItem){ ?><input type="submit" name="btnAdd" id="btnAdd" value="Save" class="btn btn-small btn-primary"><?php }?>
								<button type="reset" class="btn btn-small">Reset</button>
							</div>
						</div>
					</fieldset>
				</form>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxItem').html("");
		$('#msgtxQty').html("");
		$('#msgBdate').html("");
		$('#msgLoc').html("");

		if( $('#txItemNme').val()=="" ){
			$('#msgtxItem').html("Item Required!");
			res=false;
		}
		else if( $('#txQty').val()=="" ){
			$('#msgtxQty').html("Required!");
			$('#txQty').focus();
			res=false;
		}
		else if( $('#txDate').val()=="" ){
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#selLoc').val()=="" ){
			$('#msgLoc').html("Required!");
			$('#selLoc').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to save this information?'))
				res=true;
			else
				res=false;
		}
		return res;
	});

	$('#txDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2015:<?php echo date('Y')+1 ?>',
	});
});
</script>
<!-- end: JavaScript-->
<script>
function getXMLHTTP() { //fuction to return the xml http object
	var xmlhttp=false;
	try{
		xmlhttp=new XMLHttpRequest();
	}
	catch(e)  {
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

	if (req) {
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