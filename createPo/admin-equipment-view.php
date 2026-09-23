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
if( isset($_POST['btnSave']) && $equip_id){
	functions::sendTo('admin-equipment-edit.php?vdidEdt='.functions::encode($equip_id));
	die();
}
$selClass='';$selType='';$txDesc='';$txModel='';$txBrand='';$txSerialNo='';$txPlateNo='';$txEngineNo='';$txChassisNo='';$txDateAcquired='';$txQuantity=0;$txStatus='';
$txMVNo='';$txPower='';$txLocation='';$txGrossWt='';$txNetWt='';$txShipWt='';$txPrice='';$txRemark=''; $txProposedNoYr=0; $txUsageLimit=0;$txWarranty=0;
$txWarrantyNo=''; $txProposedYr=''; $txRate=0;$txUnit='';$out_date='';

$q = $db->select('equipment','*',array('equip_id'=>$equip_id));
$r = $db->fetch_array($q);
$txInventoryNo = $r['inventory_id'];
$selClass = $r['classification'];
$selCat = $r['category'];
$selType = $r['type'];
$supplierName = $db->getValue('supplier','name',array('supplierID'=>$r['supplierID']));
$txDesc = $r['equip_desc'];
$txProductivity = $r['productivity'];
$txModel = $r['model'];
$txBrand = $r['brand'];
$txSerialNo = $r['serial_no'];
$txPlateNo = $r['plate_no'];
$txEngineNo = $r['engine_no'];
$txChassisNo = $r['chassis_no'];
$txDateAcquired = functions::datearr($r['date_acquired']);
$txMVNo = $r['mvFileNo'];

$txPower = $r['power'];
$txLocation = $r['location'];
$txGrossWt = $r['gross_wt'];
$txNetWt = $r['net_wt'];
$txShipWt = $r['shipping_wt'];
$txPrice = ($r['price']) ? functions::formatMoney($r['price']) : 0;
$txRemark = $r['remarks'];
$txQuantity = $r['quantity'];
$txUnit = $r['unit'];
$txStatus = $r['status'];
$out_date = $r['out_date'];
$liter_hour = $r['liter_hr'];
$liter_km = $r['liter_km'];
$selRefType = $r['reference_type'];
$reference_type = $r['reference_type'];
$reference_value = $r['reference_value'];
$reference_display = '';
if($reference_type=='po'){
	$ref_q = $db->select('po','*',array('po_no'=>$r['reference_id']));
	$ref_r = $db->fetch_array($ref_q);
	$reference_display = ($ref_r['invoice']) ? 'Invoice: <strong>'.$ref_r['invoice'].'</strong>' : 'P.O. Number: <strong>'.$ref_r['po_no'].'</strong>';
}
else if($reference_type=='voucher'){
	$reference_display = 'Voucher Number: <strong>'.$r['reference_id'].'</strong>';
}
$reg_renew = $r['reg_renew'];
$renw = explode('-', $reg_renew);
$renew_mon = isset($renw[0]) ? $renw[0] : NULL;
$renew_week = isset($renw[1]) ? $renw[1] : NULL;
if($renew_week=='01') $renew_week='1st Week'; else if($renew_week=='02')$renew_week='2nd Week'; else if($renew_week=='03')$renew_week='3rd Week'; else if($renew_week=='04')$renew_week='4th Week';
$renewal=($renew_mon && $renew_week) ? date('F',strtotime(date('Y-'.$renew_mon))).', '.$renew_week : '';


$txProposedNoYr = $db->getValue('equipment','if( IFNULL(left(proposed_life,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(proposed_life,4),0)-IFNULL(left(date_acquired,4),0),0) as proposedLife',array('equip_id'=>$r['equip_id']));
$txProposedYr = functions::datearr($r['proposed_life']);
$remaing_useful_life_year = functions::year_diff(date('Y-m-d'),$r['proposed_life']);
$net_book_value = ( $r['price'] && $txProposedNoYr ) ? $remaing_useful_life_year * ($r['price']/$txProposedNoYr) : 0;

$txWarrantyNo = $db->getValue('equipment','if( IFNULL(left(warranty,4),0) > IFNULL(left(date_acquired,4),0), IFNULL(left(warranty,4),0)-IFNULL(left(date_acquired,4),0),0) as yrWarranty',array('equip_id'=>$r['equip_id']));
$txWarranty = functions::datearr($r['warranty']);

$txUsageLimit = ($r['limit_minutes'] > 0) ? $r['limit_minutes'] / 60 : 0;
$txRate = ($r['rate']) ? functions::formatMoney($r['rate']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Detail</title>
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
			<h2><i class="halflings-icon white th"></i><span class="break"></span>PROPERTY DETAIL</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-equipment-view-docu.php?vdidVw=<?php echo functions::encode($equip_id);?>">Document</a></li>
				<li><a href="admin-equipment-view-mr.php?vdidVw=<?php echo functions::encode($equip_id);?>">MR</a></li>
				<li><a href="admin-equipment-view-fuel.php?vdidVw=<?php echo functions::encode($equip_id);?>">Fuel & Oil</a></li>
				<li><a href="admin-equipment-view-operation.php?vdidVw=<?php echo functions::encode($equip_id);?>">Operation</a></li>
				<li><a href="admin-equipment-view-usage.php?vdidVw=<?php echo functions::encode($equip_id);?>">Usage</a></li>
				<li><a href="admin-equipment-view-accessory.php?vdidVw=<?php echo functions::encode($equip_id);?>">Accessory</a></li>
				<li><a href="admin-equipment-view-repair.php?vdidVw=<?php echo functions::encode($equip_id);?>">Repair & Maintenance</a></li>
				<li class="active"><a href="#" style="opacity:.9">Detail</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="100%" border="0">
						<tr>
							<td>
								<table class="table table-hover" width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td width="7%">&nbsp;</td>
										<th width="25%" class="tdSpace" align="right" scope="row">Property Classification</th>
										<td width="2%">&nbsp;</td>
										<td width="50%" class="tdSpace"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'classification','type_desc'=>$selClass));?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Property Category</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $selCat?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Property Type</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'type','type_desc'=>$selType));?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Brand/Made</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $db->getValue('equipment_type','type_desc',array('type_name'=>'brand','type_desc'=>$txBrand));?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Property Code</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txInventoryNo;?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Description</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo nl2br($txDesc)?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Productivity</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo nl2br($txProductivity)?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Model</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txModel?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Serial Number</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txSerialNo?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Plate Number</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txPlateNo?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">MV File Number</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txMVNo?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Engine Number</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txEngineNo?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Chassis Number</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txChassisNo?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">LTO Renewal Schedule</th>
										<td>&nbsp;</td>
										<td class="tdSpace">
											<?php
											if($renewal){
											echo $renewal;
											$qEquipReg = $db->query('SELECT * FROM equipment eq, equip_registration er WHERE eq.equip_id=er.equip_id AND eq.reg_renew IS NOT NULL AND eq.status NOT IN ("Trade In","Sold","Inactive","Unserviceable") AND "'.date('Y-m-d').'" BETWEEN er.date_due_start AND er.date_validity AND eq.equip_id="'.$db->clean($equip_id).'"');
											$rReg=$db->fetch_array($qEquipReg);
											echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status: <strong>'.$rReg['stat'].'</strong>';
											?>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="regdetail" class="thickbox" onclick="showThis(this.id,'admin-equipment-registration-manage-detail.php?vdidVw=<?php echo functions::encode($equip_id);?>&rg=<?php echo functions::encode($rReg['erig_id']);?>','Reference')" style="cursor:pointer;">(Manage)</a>
											<?php } ?>
										</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Power / Capacity</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo nl2br($txPower)?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Gross Weight</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txGrossWt?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Net Weight</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txNetWt?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Shipping Weight</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txShipWt?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Average Liter/Hour</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $liter_hour?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Average Liter/Km</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $liter_km?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Acquisition Cost</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txPrice?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Net Book Value</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo functions::formatMoney($net_book_value);?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Location</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txLocation?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Date Acquired</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txDateAcquired;?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Warranty Expire</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txWarranty.' ('.$txWarrantyNo.' Year/s)';?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Useful Life</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txProposedYr.' ('.$txProposedNoYr.' Year/s)';?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Rate Per Hour</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txRate?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Maintenance Period (No. of Hours)</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txUsageLimit?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Quantity</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $txQuantity.' '.$txUnit;?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Status</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo strtoupper($txStatus); echo ($txStatus=="Unserviceable" || $txStatus=="Sold" || $txStatus=="Trade In") ? ' <i>('.functions::datearr($out_date).')</i>' : "";?>&nbsp;&nbsp;&nbsp;&nbsp;<a id="vwCondition" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-condition-manage.php?eid=<?php echo functions::encode($equip_id)?>','Property Condition History','1')" title="View Property Condition History" data-rel="tooltip"><i>(Condition History)</i></a></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Remarks</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo nl2br($txRemark);?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Supplier</th>
										<td>&nbsp;</td>
										<td class="tdSpace"><?php echo $supplierName;?></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<th class="tdSpace" align="right" scope="row">Reference</th>
										<td>&nbsp;</td>
										<td class="tdSpace">
											<?php echo $reference_display?>
											<?php $page_ref = ($selRefType=='voucher') ? 'admin-equipment-reference-amount-vo.php' : 'admin-equipment-reference-amount-po.php'; ?>
											<a id="amntRef" class="thickbox" onclick="showThis(this.id,'<?php echo $page_ref ?>?vdidVw=<?php echo functions::encode($equip_id);?>','Reference')" style="cursor:pointer;">(Manage)</a>
										</td>
									</tr>
									<tr>
										<td class="tdSpace" colspan="4" align="center"><div align="center" style="display:none;"><input type="submit" name="btnSave" id="btnSave" value="Modify" class="btn btn-primary btn-small"></div></td>
									</tr>
								</table>
							</td>
							<td valign="top" width="30%">
								<div align="left">
									<?php
									$fileName = $db->getValue('images','name',array('equip_id'=>$equip_id));
									$file='../img_equip/blank-pic.png';
									if($fileName){
										$file = file_exists('../img_equip/'.$fileName) ? '../img_equip/'.$fileName : '../img_equip/blank-pic.png';
									}
									#$file = ($fileName) ? '../img_equip/'.$fileName : '../img_equip/blank-pic.png';
									?>
									<img height="400" width="400" src="<?php echo $file?>">
								</div>
							</td>
						</tr>
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
<!-- end: JavaScript-->
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