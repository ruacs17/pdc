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
$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;
$searchedItem = (isset($_REQUEST['srcItm']) && !empty($_REQUEST['srcItm']) ) ? functions::decode($_REQUEST['srcItm']) : 0;
$poidEdt=0;

if( isset($_POST['btnSave']) ){
	$txpoidEdt = ( isset($_POST['txpoidEdt']) && !empty($_POST['txpoidEdt']) ) ? functions::decode($_POST['txpoidEdt']) : '0';
	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? $_POST['txQty'] : '';
	$txQtyDel = ( isset($_POST['txQtyDel']) && !empty($_POST['txQtyDel']) ) ? $_POST['txQtyDel'] : '';
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : '0';
	if( $txQty && $txItem && $txCost){
		$db->update('po_item',array('quantity'=>$txQty,'qty_delivered'=>$txQtyDel,'cost'=>$txCost,'discount'=>$txDiscount),array('po_item_id'=>$txpoidEdt));
	}
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?po_id='.functions::encode($po_id));
	die();
}

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
	$poidDel = functions::decode($_REQUEST['poidDel']);
	$db->delete('po_item',array('po_item_id'=>$poidDel));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?po_id='.functions::encode($po_id));
	die();
}

$editTrue=0;
$poidEdt='';
$item='';$quantity='';$brand='';$cost='';$unit='';$qty_delivered='';$discount='';
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
}

if( $searchedItem ){
	$itemID = $db->getValue('material_reference','mf_id',array('mf_id'=>$searchedItem)); 
	$item = $db->getValue('material_reference','item',array('mf_id'=>$searchedItem));
	$brand = $db->getValue('material_reference','brand',array('mf_id'=>$searchedItem));
	$unit = $db->getValue('material_reference','unit',array('mf_id'=>$searchedItem));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Purchase Order Received Item</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Item Details</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table width="100%" border="0" align="center" class="table table-striped">
					<tr>
						<th width="23%" scope="col"><div align="left">Item</div></th>
						<th width="9%" scope="col"><div align="left">Qty Request</div></th>
						<th width="9%" scope="col"><div align="left">Qty Delivered</div></th>
						<th width="8%" scope="col"><div align="left">Unit</div></th>
						<th width="10%" scope="col"><div align="left">Brand</div></th>
						<th width="8%" scope="col"><div align="left">Price</div></th>
						<th width="9%" scope="col"><div align="left">Discount</div></th>
						<th width="8%" scope="col"><div align="left">Amount</div></th>
					</tr>
					<?php
					$total_amount=0;$disc_amount=0;$amount=0;
					#$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
					$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,sum(qty_delivered) as qty_d,unit,brand,cost,discount FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item');
					echo $db->last_query;
					while($rPOI = $db->fetch_array($qPOI)):
						$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
						$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
					?>
					<tr>
						<td><?php echo $rPOI['items'];?></td>
						<td><?php echo round($rPOI['qty']);?></td>
						<td><?php echo round($rPOI['qty_d']);?></td>
						<td><?php echo $rPOI['unit'];?></td>
						<td><?php echo $rPOI['brand'];?></td>
						<td><?php echo functions::formatMoney($rPOI['cost']);?></td>
						<td><?php echo $rPOI['discount'];?>%</td>
						<td><?php echo functions::formatMoney($amount);?></td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
						<td>&nbsp;</td>
						<td><div align="right"><strong>Total Amount</strong></div></td>
						<td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
					</tr>
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
			$('#msgtxItem').html("Item Required!");
			res=false;
		}
		else if( $('#txQty').val()=="" ){
			$('#msgtxQty').html("Required!");
			$('#txQty').focus();
			res=false;
		}
		else if( $('#txQtyDel').val()=="" ){
			$('#msgtxQtyDel').html("Required!");
			$('#txQtyDel').focus();
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
</script>
<?php require_once('templ_down.php');?>
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