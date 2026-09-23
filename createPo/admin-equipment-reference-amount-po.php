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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;

$remove = isset($_REQUEST['rm']) ? $_REQUEST['rm'] : "";

$q = $db->select('equipment','*',array('equip_id'=>$equip_id));
$r = $db->fetch_array($q);
$txInventoryNo = $r['inventory_id'];
$selClass = $r['classification'];
$selCat = $r['category'];
$selType = $r['type'];
$supplierName = $db->getValue('supplier','name',array('supplierID'=>$r['supplierID']));
$txDesc = $r['equip_desc'];
$reference_id = $r['reference_id'];
if($r['reference_type']=='voucher')
	functions::sendTo('admin-equipment-reference-amount-vo.php?vdidVw='.functions::encode($equip_id));
$old_price = $r['price'];
if($remove){
	$db->delete('equipment_price_ref',array('equip_id'=>$equip_id));
	$db->update('equipment',array('reference_type'=>NULL,'reference_id'=>NULL,'reference_value'=>NULL,'price'=>$r['old_price'],'supplierID'=>NULL,'old_price'=>NULL),array('equip_id'=>$equip_id));
	$_SESSION['notif_error']="Reference Removed!";
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
	die();
}

$pono = isset($_REQUEST['txPoNo']) ? $_REQUEST['txPoNo'] : $db->getValue('equipment_price_ref','ref_group_id',array('equip_id'=>$equip_id,'ref_type'=>'po'));

$po_id = ($pono) ? $db->getValue('po','po_id',array('po_no'=>$pono)) : '';
$poi_id = isset($_REQUEST['poiid']) ? functions::decode($_REQUEST['poiid']) : "";

$supplierID = $db->getValue('po','supplierID',array('po_no'=>$pono));

if( isset($_POST['btnSave']) ){
	$pono = isset($_POST['txPoNo']) ? $_POST['txPoNo'] : '';
	$chkPOI = isset($_POST['chkSel']) ? $_POST['chkSel'] : array();
	$total_po_amount=0;
	$count=0;
	if( $db->getValue('po','count(*)',array('po_no'=>$pono))==0 ){
		functions::say('Invalid P.O. Number');
	}
	else if(is_array($chkPOI) && $pono && $equip_id){
		$db->delete('equipment_price_ref',array('equip_id'=>$equip_id));
		foreach($chkPOI as $poi):
			$count++;
			$poi_id = functions::decode($poi);
			$itmxp = explode("|", $poi_id);
			$selItmID = isset($itmxp[0]) ? $itmxp[0] : 0;
			$selItmAmount = isset($itmxp[1]) ? $itmxp[1] : 0;
			$selItmSupplier = isset($itmxp[2]) ? $itmxp[2] : 0;
			$total_po_amount += $selItmAmount;
			$db->insert('equipment_price_ref',array('equip_id'=>$equip_id,'ref_group_id '=>$pono,'ref_detail_id '=>$selItmID,'ref_type'=>'po'));
		endforeach;
		if($count>0){
			$db->update('equipment',array('old_price'=>$r['price']),array('equip_id'=>$equip_id,'old_price'=>NULL));
			$db->update('equipment',array('reference_type'=>'po','price'=>$total_po_amount,'reference_id'=>$pono,'supplierID'=>$selItmSupplier,'reference_value'=>$total_po_amount),array('equip_id'=>$equip_id));
		}
		if($count==0){
			$db->update('equipment',array('reference_type'=>NULL,'reference_id'=>NULL,'reference_value'=>NULL,'price'=>$r['old_price'],'supplierID'=>NULL,'old_price'=>NULL),array('equip_id'=>$equip_id));
		}
		$_SESSION['notif_success']='Reference saved!';
	}
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id).'&txPoNo='.$pono);
	die();
}
$arrPriceRef=array();
$qPR = $db->select('equipment_price_ref','*',array('equip_id'=>$equip_id,'ref_group_id'=>$pono));
while($rPR = $db->fetch_array($qPR)):
	$arrPriceRef[$rPR['ref_detail_id']]=$rPR['ref_detail_id'];
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Reference P.O. Detail</title>
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
	<style>.tdSpace{padding: 4px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ACQUISITION REFERENCES (P.O.)</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-equipment-reference-amount-vo.php?vdidVw=<?php echo functions::encode($equip_id);?>">Voucher</a></li>
				<li class="active"><a href="admin-equipment-reference-amount-po.php?vdidVw=<?php echo functions::encode($equip_id);?>" style="opacity:.9">P.O.</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<div align="left"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'type','type_desc'=>$selType)).' - '.$txDesc?></div><br>
					<div align="left">P.O. No: <input type="text" name="txPoNo" id="txPoNo" value="<?php echo $pono; ?>" <?php if(count($arrPriceRef)){echo 'readonly';} ?>>&nbsp;<input type="submit" name="btnSearch" id="btnSearch" class="btn btn-primary btn-mini" value="Search"><?php if($pono && count($arrPriceRef)){ ?>&nbsp;<a class="btn btn-mini" href="?vdidVw=<?php echo functions::encode($equip_id)?>&rm=t" onClick="if(confirm('Do you want to remove this reference?')){return true;}else{return false;}">Remove</a><?php } ?></div><br>
					<div align="left">Invoice: <strong><?php echo $db->getValue('po','invoice',array('po_no'=>$pono)); ?></strong></div><br>
					<div align="left">Supplier: <strong><?php echo $db->getValue('supplier','name',array('supplierID'=>$supplierID)) ?></strong></div><br>
					<table width="100%" border="0" align="center" class="table table-bordered">
						<tr>
							<th width="40%" scope="col"><div align="left">Item</div></th>
							<th width="10%" scope="col"><div align="right">Price</div></th>
							<th width="10%" scope="col"><div align="right">Discount</div></th>
							<th width="10%" scope="col"><div align="right">Amount</div></th>
							<th width="5%" scope="col"><div align="center">Select</div></th>
						</tr>
						<?php
						$total_amount=0;$amount=0;$count=0;
						$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
						while($rPOI = $db->fetch_array($qPOI)):
							$count++;
							$amount = $rPOI['cost'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;
							#$readonly = $db->getValue('equipment_price_ref','count(*)',array('ref_detail_id'=>$rPOI['po_item_id']),'AND equip_id != "'.$db->clean($equip_id).'"');
							#$readonly = ($readonly) ? 'readonly checked disabled' : '';
						?>
						<tr>
							<td><?php echo $rPOI['item'];?></td>
							<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
							<td><div align="right"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
							<td><div align="center"><input <?php #echo $readonly ?> type="checkbox" name="chkSel[]" id="chkSel" value="<?php echo functions::encode($rPOI['po_item_id'].'|'.$amount.'|'.$supplierID); ?>" <?php if( isset($arrPriceRef[$rPOI['po_item_id']]) ){echo 'checked';} ?> ></div></td>
						</tr>
						<?php
						endwhile;
						if($count){
						?>
						<tr>
							<td height="25"></td>
							<td></td>
							<td></td>
							<td></td>
							<td><div align="center"><input type="submit" name="btnSave" id="btnSave" class="btn btn-info btn-small" value="Save"></div></td>
						</tr>
						<?php }else if($pono && ($count==0) ){ ?>
						<tr><td colspan="5"><div align="center"><strong>-- No Result Found! --</strong></div></td></tr>
						<?php } ?>
					</table>
				</form>
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
function saveit(po_item_id){
	if(po_item_id)
		window.location="?vdidVw=<?php echo functions::encode($equip_id)?>&txPoNo=<?php echo $pono?>&poiid="+po_item_id
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