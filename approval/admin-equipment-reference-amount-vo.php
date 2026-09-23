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
$itmid = (isset($_REQUEST['itmid']) && !empty($_REQUEST['itmid']) ) ? functions::decode($_REQUEST['itmid']) : '';

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

#$detailID = ( $r['reference_type']=='po' ) ? $db->getValue('voucher v, voucher_particular vp, voucher_po_payment vpp, po_item poi','voucher_no',array('poi.po_item_id'=>$reference_id),'AND v.voucher_id=vp.voucher_id AND vpp.vp_id=vp.vp_id AND vpp.po_id=poi.po_id') : $db->getValue('voucher v, voucher_particular vp, voucher_detail vd','voucher_no',array('vd.vd_id'=>$reference_id),'AND v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id');
$detailID = $r['reference_id'];
$vono = isset($_REQUEST['txVoNo']) ? $_REQUEST['txVoNo'] : $detailID;
$vid = $db->getValue('voucher','voucher_id',array('voucher_no'=>$vono));
$supplierID = $db->getValue('voucher','supplierID',array('voucher_id'=>$vid));

if($r['reference_type']=='po'){
	functions::sendTo('admin-equipment-reference-amount-po.php?vdidVw='.functions::encode($equip_id));
	die();
}

if($remove){
	$db->delete('equipment_price_ref',array('equip_id'=>$equip_id));
	$db->update('equipment',array('reference_type'=>NULL,'reference_id'=>NULL,'reference_value'=>NULL,'price'=>$r['old_price'],'supplierID'=>NULL,'old_price'=>NULL),array('equip_id'=>$equip_id));
	$_SESSION['notif_warning']='Reference Removed!';
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
	die();
}

if( isset($_POST['btnSave']) ){
	$vono = isset($_POST['txVoNo']) ? $_POST['txVoNo'] : '';
	$chkDetail = isset($_POST['chkSel']) ? $_POST['chkSel'] : array();
	$total_vo_amount=0; $count=0; $type_vo=0; $type_po=0;

	if( $db->getValue('voucher','count(*)',array('voucher_no'=>$vono))==0 ){
		functions::say('Invalid Voucher Number!');
	}
	else if(is_array($chkDetail) && $vono && $equip_id){
		$db->delete('equipment_price_ref',array('equip_id'=>$equip_id));
		foreach($chkDetail as $detail):
			$count++;
			$detail_id = functions::decode($detail);
			$itmxp = explode("|", $detail_id);
			$selItmID = isset($itmxp[0]) ? $itmxp[0] : 0;
			$selItmAmount = isset($itmxp[1]) ? $itmxp[1] : 0;
			$selItmSupplier = $supplierID;
			$vopo = isset($itmxp[2]) ? $itmxp[2] : 0;
			$total_vo_amount += $selItmAmount;
			if($vopo=='voucher')
				$type_vo++;
			if($vopo=='po')
				$type_po++;
			$db->insert('equipment_price_ref',array('equip_id'=>$equip_id,'ref_group_id '=>$vono,'ref_detail_id '=>$selItmID,'ref_type'=>$vopo));
		endforeach;
		if($count>0){
			$vopo_type='';
			if($type_vo && $type_po)
				$vopo_type='voucher|po';
			else if($type_vo)
				$vopo_type='voucher';
			else if($type_po)
				$vopo_type='po';
			$db->update('equipment',array('old_price'=>$r['price']),array('equip_id'=>$equip_id,'old_price'=>NULL));
			$db->update('equipment',array('reference_type'=>'voucher','price'=>$total_vo_amount,'reference_id'=>$vono,'supplierID'=>$selItmSupplier,'reference_value'=>$total_vo_amount),array('equip_id'=>$equip_id));
		}
		if($count==0){
			$db->update('equipment',array('reference_type'=>NULL,'reference_id'=>NULL,'reference_value'=>NULL,'price'=>$r['old_price'],'supplierID'=>NULL,'old_price'=>NULL),array('equip_id'=>$equip_id));
		}
		$_SESSION['notif_success']='Reference saved!';
	}
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id).'&txVoNo='.$vono);
	die();
}




$arrPriceRef=array();
$qPR = $db->select('equipment_price_ref','*',array('equip_id'=>$equip_id,'ref_group_id'=>$vono));
while($rPR = $db->fetch_array($qPR)):
	$arrPriceRef[$rPR['ref_detail_id']]=$rPR['ref_detail_id'];
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Reference Voucher Detail</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ACQUISITION REFERENCES (Voucher)</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="admin-equipment-reference-amount-vo.php?vdidVw=<?php echo functions::encode($equip_id);?>" style="opacity:.9">Voucher</a></li>
				<li><a href="admin-equipment-reference-amount-po.php?vdidVw=<?php echo functions::encode($equip_id);?>">P.O.</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<div align="left"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'type','type_desc'=>$selType)).' - '.$txDesc?></div><br>
					<div align="left">Voucher No: <input type="text" name="txVoNo" id="txVoNo" value="<?php echo $vono; ?>" <?php if(count($arrPriceRef)){echo 'readonly';} ?>>&nbsp;<input type="submit" name="btnSearch" id="btnSearch" class="btn btn-primary btn-mini" value="Search"><?php if($vono && count($arrPriceRef)){ ?>&nbsp;<a class="btn btn-mini" href="?vdidVw=<?php echo functions::encode($equip_id)?>&rm=t" onClick="if(confirm('Do you want to remove this reference?')){return true;}else{return false;}">Remove</a><?php } ?></div><br>
					<table class="table" border="0">
						<?php
						$total_amount = 0; $amount = 0; $po_id=0; $vp_id=0; $voucher_type=''; $countContent=0;
						$qvp = $db->select('voucher_particular','*',array('voucher_id'=>$vid));
						while($rvp = $db->fetch_array($qvp)):
							$arrItem=array();
							$po_id=0;
							$vp_id=$rvp['vp_id'];
							$voucher_type = $rvp['vtype'];
							if($rvp['vtype'] == "po"){
								$po_id = $db->getValue('voucher_po_payment','po_id',array('vp_id'=>$rvp['vp_id']));
								$amount = $db->getValue('voucher_po_payment','amount',array('vp_id'=>$rvp['vp_id']));
							}
							else
								$amount = $db->getValue('voucher_detail','SUM(amount_issue)',array('vp_id'=>$rvp['vp_id']));

							if($rvp['vtype'] == "po"){
								$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
								while($rPOI = $db->fetch_array($qPOI)):
									$amount = $rPOI['cost'];
									$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
									$amount = $amount - $disc_amount;
									$arrItem[] = array('item_id'=>$rPOI['po_item_id'],'item'=>$rPOI['item'],'amount'=>$amount,'vtype'=>'po');
								endwhile;
							}
							else if($rvp['vtype'] == "cash"){
								$qvDetails = $db->select('voucher_detail','*',array('vp_id'=>$vp_id),'ORDER BY vd_date');
								while($rvDetails = $db->fetch_array($qvDetails)):
									$arrItem[] = array('item_id'=>$rvDetails['vd_id'],'item'=>$rvDetails['item'],'amount'=>$rvDetails['amount'],'vtype'=>'voucher');
								endwhile;
							}
						?>
						<tr>
							<td>
								<strong><?php echo $rvp['vp_title'];?></strong>
								<table class="table" width="50%">
									<?php foreach($arrItem as $itm): $countContent++; ?>
									<tr>
										<td width="70%"><?php echo $itm['item'] ?></td>
										<td width="20%"><?php echo functions::formatMoney($itm['amount']) ?></td>
										<td><div align="center"><input type="checkbox" name="chkSel[]" id="chkSel" value="<?php echo functions::encode($itm['item_id'].'|'.$itm['amount'].'|'.$itm['vtype'].'|'.$supplierID);?>" <?php if( isset($arrPriceRef[$itm['item_id']]) ){echo 'checked';} ?> ></div></td>
									</tr>
									<?php endforeach; ?>
								</table>
							</td>
						</tr>
						<?php
						endwhile;?>
						<?php if($countContent){ ?>
						<tr>
							<td><div align="right" style="padding-right:43px;"><input type="submit" name="btnSave" id="btnSave" class="btn btn-info btn-small" value="Save"></div></td>
						</tr>
						<?php }else if($vono && ($countContent==0) ){ ?>
						<tr>
							<td><div align="center"><strong>-- No Result Found! --</strong></div></td>
						</tr>
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
	window.location="?vdidVw=<?php echo functions::encode($equip_id)?>&txVoNo=<?php echo $vono?>&itmid="+po_item_id
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