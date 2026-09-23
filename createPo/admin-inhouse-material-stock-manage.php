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
$count=0;$editTrue=0;$total_quantity = 0;$imsl_id=0;$quantity=0;
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? $db->clean(functions::decode($_REQUEST['mf_id'])) : 0;
$_SESSION['notif_id_list']=$mf_id;
$location = (isset($_REQUEST['loc']) && !empty($_REQUEST['loc']) ) ? $db->clean(functions::decode($_REQUEST['loc'])) : '';
$itemEdit = (isset($_REQUEST['itemEdt']) && !empty($_REQUEST['itemEdt']) ) ? $db->clean(functions::decode($_REQUEST['itemEdt'])) : 0;
$itemDel=(isset($_REQUEST['itemDlt']) && !empty($_REQUEST['itemDlt']) ) ? $db->clean(functions::decode($_REQUEST['itemDlt'])) : 0;
$itemDte=(isset($_REQUEST['itemDte']) && !empty($_REQUEST['itemDte']) ) ? $db->clean(functions::decode($_REQUEST['itemDte'])) : '';
$qM = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$rM = $db->fetch_array($qM);
$category = $rM['category'];
$classification = $rM['classification'];
$type = $rM['mtype'];
$item = $rM['item'];
$unit = $rM['unit'];
$brand = $rM['brand'];
$mdescription = $rM['mdescription'];


if($itemDel && $itemDte){
	$db->delete('inhouse_material_storage',array('ims_date'=>$itemDte,'item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$location));
	$_SESSION['notif_warning']='Inventory Removed!';
	functions::sendTo(functions::pageName().'?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location));
	die();
}
if( isset($_POST['btnAdd']) ){
	$qDetl = $db->select('inhouse_material_storage','raw_no,stand_no,divider_no,sequence_no,series_no',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$location),'GROUP BY raw_no,stand_no,divider_no,sequence_no,series_no');
	$rDtl = $db->fetch_array($qDetl);
	$raw_no = $rDtl['raw_no'];
	$stand_no = $rDtl['stand_no'];
	$divider_no = $rDtl['divider_no'];
	$sequence_no = $rDtl['sequence_no'];
	$series_no = $rDtl['series_no'];

	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? functions::moneyToDouble($_POST['txQty']) : 0;
	$ims_date = ( isset($_POST['txDate']) && functions::valid_date($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$location = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	if( $item && $ims_date && $location ){
		$ins = $db->insert('inhouse_material_storage',array('item'=>$item,'unit'=>$unit,'brand'=>$brand,'price'=>$txCost,'ims_date'=>$ims_date,'quantity'=>$txQty,'location'=>$location,'raw_no'=>$raw_no,'stand_no'=>$stand_no,'divider_no'=>$divider_no,'sequence_no'=>$sequence_no,'series_no'=>$series_no));
		$_SESSION['notif_success']='Added to Inventory!';
		$_SESSION['notif_id_list2']=$ims_date;
		functions::sendTo(functions::pageName().'?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location));
		die();
	}
}

if( isset($_POST['btnSave']) ){

	$txQty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? functions::moneyToDouble($_POST['txQty']) : 0;
	$txBdate = ( isset($_POST['txDate']) && functions::valid_date($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$location = ( isset($_POST['selLoc']) && !empty($_POST['selLoc']) ) ? functions::decode($_POST['selLoc']) : '';
	$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	$stockDate = $db->getValue('inhouse_material_storage','ims_date',array('ims_id'=>$itemEdit));
	$existingQuantity = $db->getValue('inhouse_material_storage','quantity',array('ims_date'=>$txBdate));

	if( $txQty && $txBdate && $itemEdit && $location ){
		if($stockDate != $txBdate){
			$txQty += $existingQuantity;
			$db->delete('inhouse_material_storage',array('ims_date'=>$txBdate));
		}
		$db->update('inhouse_material_storage',array('ims_date'=>$txBdate,'quantity'=>$txQty,'location'=>$location,'price'=>$txCost),array('ims_id'=>$itemEdit));
		$_SESSION['notif_success']='Inventory Updated!';
		$_SESSION['notif_id_list2']=$txBdate;
		functions::sendTo(functions::pageName().'?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location));
		die();
	}
}

$quantity=''; $mon=date('m');$day=date('d');$year=date('Y');$vdate=date('Y-m-d');$cost=0;
if($itemEdit){
	$editTrue = $db->getValue('inhouse_material_storage','count(*)',array('ims_id'=>$itemEdit));
	$quantity = $db->getValue('inhouse_material_storage','quantity',array('ims_id'=>$itemEdit));
	$location = $db->getValue('inhouse_material_storage','location',array('ims_id'=>$itemEdit));
	$cost = $db->getValue('inhouse_material_storage','price',array('ims_id'=>$itemEdit));
	$imsl_id = $db->getValue('inhouse_material_storage_location','imsl_id',array('location'=>$location));
	$vdate = $db->getValue('inhouse_material_storage','ims_date',array('ims_id'=>$itemEdit));
	$xdate = explode('-',$vdate);
	if(count($xdate)==3){
		$mon = $xdate[1];
		$day = $xdate[2];
		$year = $xdate[0];
	}
}
if($quantity){
	$quantity = (functions::isfloat($quantity)) ? functions::formatMoney($quantity) : number_format($quantity);
}
$arrDate=array();
$arr_date = array();
$qAddDate = $db->select('inhouse_material_storage','DISTINCT ims_date',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$location),'ORDER BY ims_date');
while($rAddDate = $db->fetch_array($qAddDate)):
	$arrDate[$rAddDate['ims_date']]='acquire';
endwhile;

$qDelDate = $db->select('inhouse_material im,inhouse_material_item imi','DISTINCT im_date',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand']),'AND im.im_id=imi.im_id ORDER BY im_date');
while($rDelDate = $db->fetch_array($qDelDate)):
	$arrDate[$rDelDate['im_date']]='sold';
endwhile;
ksort($arrDate);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Inventory Item</title>
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
	<style type="text/css">body{font-size:11px;}</style>
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
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Inventory Item</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table class="table">
					<thead>
						<tr style="background-color:#ece1e1;">
							<th><div align="left">Item</div></th>
							<th><div align="left">Unit</div></th>
							<th><div align="left">Brand</div></th>
							<th><div align="left">Description</div></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><div align="left"><?php echo $item ?></div></td>
							<td><div align="left"><?php echo $unit ?></div></td>
							<td><div align="left"><?php echo $brand ?></div></td>
							<td><div align="left"><?php echo $mdescription ?></div></td>
						</tr>
					</tbody>
				</table>
				<table class="table table-bordered">
					<thead>
						<tr style="background-color:#ece1e1;">
							<th><div align="center">Category</div></th>
							<th><div align="center">Raw No.</div></th>
							<th><div align="center">Classification</div></th>
							<th><div align="center">Stand No.</div></th>
							<th><div align="center">Divider No.</div></th>
							<th><div align="center">Type</div></th>
							<th><div align="center">Sequence No.</div></th>
							<th><div align="center">Series No.</div></th>
							<th><div align="center"></div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$countPlace=0;$countDtl=0;
					$qDetl = $db->select('inhouse_material_storage','raw_no,stand_no,divider_no,sequence_no,series_no',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'location'=>$location),'GROUP BY raw_no,stand_no,divider_no,sequence_no,series_no');
					$countPlace = $db->num_rows($qDetl);
					while($rDl = $db->fetch_array($qDetl)):
						$countDtl++;
						$lnk = '?itm='.functions::encode($rM['item']).'&brnd='.functions::encode($rM['brand']).'&unt='.functions::encode($rM['unit']).'&cat='.functions::encode($category);
						$lnk .= '&raw='.functions::encode($rDl['raw_no']).'&class='.functions::encode($classification).'&stand='.functions::encode($rDl['stand_no']).'&divider='.functions::encode($rDl['divider_no']);
						$lnk .= '&type='.functions::encode($type).'&sequence='.functions::encode($rDl['sequence_no']).'&series='.functions::encode($rDl['series_no']).'&location='.functions::encode($location);
					?>
						<tr>
							<td><div align="center"><?php echo $category ?></div></td>
							<td><div align="center"><?php echo $rDl['raw_no']; ?></div></td>
							<td><div align="center"><?php echo $classification; ?></div></td>
							<td><div align="center"><?php echo $rDl['stand_no']; ?></div></td>
							<td><div align="center"><?php echo $rDl['divider_no']; ?></div></td>
							<td><div align="center"><?php echo $type; ?></div></td>
							<td><div align="center"><?php echo $rDl['sequence_no']; ?></div></td>
							<td><div align="center"><?php echo $rDl['series_no']; ?></div></td>
							<td>
								<div align="center">
									<a id="mngPlce<?php echo $countDtl?>" class="btn btn-mini btn-warning thickbox" style="cursor:pointer;" title="Manage this Item" data-rel="tooltip" onclick="showThis(this.id,'admin-inhouse-material-stock-place.php<?php echo $lnk; ?>','Details')"><i class="halflings-icon white pencil"></i></a>
									<?php if($countPlace>1){ ?>
									<a id="AddItm<?php echo $countDtl?>" class="btn btn-mini btn-info thickbox" style="cursor:pointer;" title="Add Item" data-rel="tooltip" onclick="showThis(this.id,'admin-inhouse-material-stock-add2.php<?php echo $lnk; ?>','Add Inventory Details')"><i class="halflings-icon white plus-sign"></i></a>
									<?php } ?>
								</div>
							</td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
				<?php if($countPlace==1){ ?>
				<div align="center">
					<div style="width:80%;padding-top:30px;">
						<table align="center" class="table table-bordered" border="0">
							<tr bgcolor="#ece1e1">
								<th width="15%"><div align="center">Quantity</div></th>
								<th width="15%"><div align="center">Price</div></th>
								<th width="10%"><div align="center">Date</div></th>
								<th width="20%" scope="col"><div align="center">Storage Location</div></th>
								<th width="10%">&nbsp;</th>
							</tr>
							<tr bgcolor="#f8f8f8">
								<td>
									<div align="center">
										<input type="text" style="width:80px;text-align:center;" name="txQty" id="txQty" value="<?php echo $quantity;?>" onkeypress="return checkinput(this, event);" onkeyup="FormatCurrency(this);" required>&nbsp;&nbsp;&nbsp;<i>(<?php echo $unit ?>)</i>
										<div class="warning" id="msgtxQty" style="font-weight:bold;" name="msgtxQty"></div>
									</div>
									
								</td>
								<td><div align="center"><input type="text" style="width:80px;text-align:center;" name="txCost" id="txCost" value="<?php echo ($cost) ? functions::formatMoney($cost) : '';?>" onkeyup="FormatCurrency(this);" required></div></td>
								<td>
									<div align="center">
										<input type="text" style="width: 80px;text-align:center;" name="txDate" id="txDate" value="<?php echo $vdate; ?>" required>
										<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
									</div>
								</td>
								<td>
									<div align="center"><input type="hidden" name="selLoc" id="selLoc" value="<?php echo functions::encode($location)?>"><?php echo $location?></div>
									<span class="help-inline warning" id="msgLoc" style="font-weight:bold;" name="msgLoc"></span>
								</td>
								<td>
									<div align="center">
									<?php 
									if($editTrue){
									echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary">&nbsp;';
									echo '<a href="?mf_id='.functions::encode($mf_id).'&loc='.functions::encode($location).'" class="btn btn-mini">Cancel</a>';
									}
									else
									echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-mini">';
									?>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<?php } ?>
			</form>
			<div align="center">
				<div style="width:80%">
					<table width="95%" border="0" align="center" id="abcd" name="abcd" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['notif_id_list2'])){echo 'table-bordered';} ?> table-hover">
						<thead>
							<tr style="background-color:#ece1e1;">
								<th width="15%" scope="col"><div align="center">Inventory Date</div></th>
								<th width="15%" scope="col"><div align="center">Acquired <i>(<?php echo $rM['unit'];?>)</i></div></th>
								<th width="15%" scope="col"><div align="center">Sold</div></th>
								<th width="15%" scope="col"><div align="center">On-hand</div></th>
								<th width="10%" scope="col">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$count=0;$onhand=0;$onhand_disp=0;
						foreach($arrDate as $date => $event):
							$sold=0;$acquire=0;$quantityPerDate=0;$qty=0;$count++;$ims_id = $count++;
						?>
							<tr id="rw<?php echo $date?>">
								<td><div align="center"><?php echo functions::datearr($date);?></div></td>
								<?php
								$ims_id = $db->getValue('inhouse_material_storage','ims_id',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'ims_date'=>$date,'location'=>$location));
								$bgColor='';
								$acquire_disp='';$sold_disp='';$onhand_disp='';
								$acquire = $db->getValue('inhouse_material_storage','sum(quantity)',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'ims_date'=>$date,'location'=>$location));
								$onhand += $acquire;
								$sold = $db->getValue('inhouse_material im, inhouse_material_item imi','sum(quantity)',array('item'=>$rM['item'],'unit'=>$rM['unit'],'brand'=>$rM['brand'],'im_date'=>$date,'location'=>$location),'AND im.im_id=imi.im_id');
								$onhand -= $sold;
								if($acquire)
									$acquire_disp = (functions::isfloat($acquire)) ? functions::formatMoney($acquire) : number_format($acquire);
								if($sold)
									$sold_disp = (functions::isfloat($sold)) ? functions::formatMoney($sold) : number_format($sold);
								if($onhand)
									$onhand_disp = (functions::isfloat($onhand)) ? functions::formatMoney($onhand) : number_format($onhand);
								?>
								<td><div align="center"><?php echo $acquire_disp;?></div></td>
								<td><div align="center"><?php echo $sold_disp;?></div></td>
								<td><div align="center"><?php echo $onhand_disp?></div></td>
								<td>
									<div align="center">
										<a id="update<?php echo $count?>" class="btn btn-mini btn-info thickbox" style="cursor:pointer;" title="Manage this Item" data-rel="tooltip" onclick="showThis(this.id,'admin-inhouse-material-stock-manage-detail.php?mf_id=<?php echo functions::encode($mf_id);?>&itemDte=<?php echo functions::encode($date)?>&loc=<?php echo functions::encode($location);?>','Inventory History')"><i class="halflings-icon white search"></i></a>
										<a id="dlt<?php echo $count?>" class="btn btn-mini btn-danger" title="Remove this inventory" data-rel="tooltip" onClick="return delt()" href="<?php echo functions::pageName()?>?mf_id=<?php echo functions::encode($mf_id);?>&itemDlt=<?php echo functions::encode($ims_id);?>&itemDte=<?php echo functions::encode($date)?>&loc=<?php echo functions::encode($location);?>"><i class="halflings-icon white trash"></i></a>
									</div>
								</td>
							</tr>
						<?php endforeach;?>
							<tr>
								<td></td>
								<td></td>
								<td><div align="right"><strong>Total Onhand</strong></div></td>
								<td><div align="center"><strong><?php echo $onhand_disp?></strong></div></td>
								<td></td>
							</tr>
						</tbody>
					</table>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
function delt(){
	if(confirm('Do you want to remove this Inventory?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxQty').html("");
		$('#msgBdate').html("");
		$('#msgLoc').html("");

		if( $('#txQty').val()=="" ){
			$('#msgtxQty').html("Required!");
			$('#txQty').focus();
			res=false;
		}
		else if( $('#bdMon').val()=="" || $('#bdDay').val()=="" || $('#bdYear').val()==""){
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#selLoc').val()=="" ){
			$('#msgLoc').html("Required!");
			$('#selLoc').focus();
			res=false;
		}
		else
			res=true;
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
<?php if(isset($_SESSION['notif_id_list2'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list2'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list2'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list2'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list2']);} ?>
<!-- end: JavaScript-->
</body>
</html>