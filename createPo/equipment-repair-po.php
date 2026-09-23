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
$er_id = (isset($_REQUEST['er_id']) && !empty($_REQUEST['er_id']) ) ? functions::decode($_REQUEST['er_id']) : 0;
$saved_po_id = $db->getValue('equip_repair','po_id',array('er_id'=>$er_id));
$search_po = $db->getValue('po','po_no',array('po_id'=>$saved_po_id));
$_SESSION['notif_id_list2']=$er_id;
if( isset($_POST['btnPo']) && $equip_id ){
	$search_po = isset($_POST['txPo']) ? $_POST['txPo'] : '';
}

if( isset($_POST['btnAdd']) && $equip_id && $er_id ){
	$search_po = isset($_POST['txPo']) ? $_POST['txPo'] : '';


	$qPODetails = $db->select('po, po_item','*',array('po.po_no'=>$search_po),'AND po.po_id=po_item.po_id');
	if( $db->num_rows($qPODetails) ){
		$spare='';
		$po_amount=0;
		$er_date='';
		while($rPODetails = $db->fetch_array($qPODetails)):
			$po_id = $rPODetails['po_id'];
			$po_no = $rPODetails['po_no'];
			$proj_id = $rPODetails['proj_id'];
			$er_date = $rPODetails['po_date'];

			$amount=0;
			$er_date=$rPODetails['po_date'];
			$amount = $rPODetails['cost'] * $rPODetails['qty_delivered'];
			$disc_amount = ($rPODetails['discount']) ? $amount * ($rPODetails['discount'] / 100) : 0;
			$amount = $amount - $disc_amount;
			$po_amount += $amount;
			$spare .= '<div>'.$rPODetails['item'].' ( '.round($rPODetails['qty_delivered'],2).' '.$rPODetails['unit'].' x '.functions::formatMoney($rPODetails['cost']).')</div>';
		endwhile;
		$db->update('equip_repair',array('po_id'=>$po_id,'proj_id'=>$proj_id,'er_date'=>$er_date,'description'=>$spare,'quotation_part'=>$po_amount,'invoice'=>'P.O. No:'.$po_no),array('er_id'=>$er_id,'equip_id'=>$equip_id));
		$db->update('po',array('po_type'=>'parts'),array('po_id'=>$po_id));
		if($saved_po_id!=$po_id)
			$db->update('po',array('po_type'=>'material'),array('po_id'=>$saved_po_id));
	}
	functions::sendTo('?vdidVw='.functions::encode($equip_id).'&er_id='.functions::encode($er_id));
	die();
}
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
	<style>
	.tdSpace{padding: 10px 0px 4px 10px;}
	</style>
</head>
<body>
<!-- body content: start here-->
<form method="post">
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>PROPERTY REPAIR/MAINTENANCE HISTORY</h2>
			</div>
			<div class="box-content">
				<div align="center">
					<input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
					<table width="90%" border="0" align="center" style="background-color:#f9f6f6">
						<tr>
							<th bgcolor="#f7ebeb" scope="col" height="60px;"><div align="center">Property</div></th>
							<td class="tdSpace"><div align="left"><strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb"><div align="center">PO No:</div></th>
							<td width="80%" valign="middle" class="tdSpace">
								<div align="left">
									<input type="text" name="txPo" value="<?php echo $search_po; ?>">&nbsp;&nbsp;<input type="submit" name="btnPo" id="btnPo" value="Search" class="btn btn-primary btn-small">
								</div>
							</td>
						</tr>
						<tr>
							<th bgcolor="#f7ebeb" scope="col"><div align="center">Detail</div></th>
							<td class="tdSpace">
								<?php if($search_po){
									$qPO = $db->select('po','*',array('po_no'=>$search_po));
									$rPO = $db->fetch_array($qPO);
										$proj_id = $rPO['proj_id'];
										$supplierID = $rPO['supplierID'];
										$po_id = $rPO['po_id'];
								?>
								<div align="left">
									<div>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id));?></strong></div>
									<div>
										Payee/Supplier:
										<strong>
											<?php 
											$supplierID = $db->getValue('po','supplierID',array('po_id'=>$po_id));
											echo $db->getValue('supplier','name',array('supplierID'=>$supplierID));
											?>
										</strong>
									</div>
									<div>Date: <strong><?php echo functions::datearr($db->getValue('po','po_date',array('po_id'=>$po_id)));?></strong></div>
									<div>Invoice: <strong><?php echo $db->getValue('po','invoice',array('po_id'=>$po_id));?></strong></div><br><br>
									<table width="100%" border="0" align="center" class="table table-hover table-bordered table-striped" style="font-size:12px;">
										<thead style="background-color:#CCC;">
										<tr>
											<th width="30%" scope="col"><div align="left">Item</div></th>
											<th width="7%" scope="col"><div align="center">Qty Request</div></th>
											<th width="7%" scope="col"><div align="center">Qty Delivered</div></th>
											<th width="5%" scope="col"><div align="left">Unit</div></th>
											<th width="20%" scope="col"><div align="left">Brand</div></th>
											<th width="7%" scope="col"><div align="right">Price</div></th>
											<th width="5%" scope="col"><div align="center">Discount</div></th>
											<th width="7%" scope="col"><div align="right">Amount</div></th>
										</tr>
										</thead>
										<tbody>
											<?php
											$total_amount=0;$amount=0;
											$qPOI = $db->select('po_item','*',array('po_id'=>$po_id),'ORDER BY item');
											while($rPOI = $db->fetch_array($qPOI)):
												$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
												$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
												$amount = $amount - $disc_amount;
												$total_amount += $amount;
												$bgColor='';
												if($rPOI['quantity'] != $rPOI['qty_delivered'])
													$bgColor = 'bgcolor="#f5ae00"';
											?>
											<tr <?php echo $bgColor;?>>
												<td><?php echo $rPOI['item'];?></td>
												<td><div align="center"><?php echo round($rPOI['quantity'],2);?></div></td>
												<td><div align="center"><?php echo round($rPOI['qty_delivered'],2);?></div></td>
												<td><?php echo $rPOI['unit'];?></td>
												<td><?php echo $rPOI['brand'];?></td>
												<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
												<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
												<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
											</tr>
											<?php endwhile;?>
											<tr>
												<td colspan="7"><div align="right"><strong>Total Amount</strong></div></td>
												<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
											</tr>
										</tbody>
									</table>
									<p>&nbsp;</p>
								</div>
								<?php if($po_id){ ?><div align="center"><input type="submit" name="btnAdd" id="btnAdd" value="Select" class="btn btn-primary btn-small"></div><?php }?>
								<?php } ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div><!--/span-->
	</div><!--/row-->
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
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
}

$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxDesc').html("");
		$('#msgselType').html("");

		if( $('#selType').val()=="" ){
			$('#msgselType').html("Repair Type Required!");
			$('#selType').focus();
			res=false;
		}
		else if( $('#txDesc').val()=="" ){
			$('#msgtxDesc').html("Specify Description!");
			$('#txDesc').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to submit this information?'))
				res=true;
		}
		return res;
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
<!-- end: JavaScript-->
</body>
</html>