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

$edit = (isset($_REQUEST['edt'])) ? 1 : 0;
$po_item_id = (isset($_REQUEST['po_item_id']) && !empty($_REQUEST['po_item_id']) ) ? functions::decode($_REQUEST['po_item_id']) : 0;
$_SESSION['notif_id_monitoring']=$po_item_id;
if( isset($_POST['btnSave']) && $po_item_id ){
	$arr = array();
	$remarks_monitoring = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? $_POST['txRemarks'] : '';
	$db->update('po_item',array('remarks_monitoring'=>$remarks_monitoring),array('po_item_id'=>$po_item_id));
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?po_item_id='.functions::encode($po_item_id));
	die();
}
$qPO = $db->select('po p, po_item poi','*',array('po_item_id'=>$po_item_id),'AND p.po_id=poi.po_id ORDER BY po_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Monitoring</title>
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
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Purchase Order Monitoring Report</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">		
				<table class="table table-bordered table-hover table-striped" style="font-size:12px;">
					<?php
					while($rPO = $db->fetch_array($qPO)):
						$qPOI = $db->select('po_item','*',array('po_item_id'=>$rPO['po_item_id']));
						while($rPOI = $db->fetch_array($qPOI)):
					?>
						<tr>
							<th style="background-color:#CCC;" width="10%">P.O. Date</th>
							<td><div align="left"><?php echo functions::datearr($rPO['po_date']);?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;"><div>P.O. #</div></th>
							<td><div align="left"><?php echo $rPO['po_no'];?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;"><div>R.R. #</div></th>
							<td><div align="left"><?php echo $rPO['receive_no'];?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;"><div>Issuance #</div></th>
							<td>
								<div align="left">
									<?php
									$qpo = $db->select('po_issuance poi, po_issuance_details posd, po p','*',array('p.po_id'=>$rPO['po_id']),'AND poi.pos_id=posd.pos_id AND p.po_id=posd.po_id');
									while($rpo = $db->fetch_array($qpo)):
										echo '<div>'.$rpo['series_no'].'</div>';
									endwhile;
									?>
								</div>
							</td>
						</tr>
						<tr>
							<th style="background-color:#CCC;">External Provider</th>
							<td><div align="left"><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;">Item</th>
							<td><div align="left"><?php echo $rPOI['item']?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;"><div align="left">Quantity</div></th>
							<td><div align="left"><?php echo round($rPOI['qty_delivered'],2)?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;">Unit</th>
							<td><div align="left"><?php echo $rPOI['unit']?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;">Brand</th>
							<td><div align="left"><?php echo $rPOI['brand']?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;"><div align="left">Cost</div></th>
							<td><div align="left"><?php echo functions::formatMoney($rPOI['cost'])?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;"><div align="left">Amount</div></th>
							<td><div align="left"><?php echo functions::formatMoney($rPOI['qty_delivered'] * $rPOI['cost'])?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;"><div align="left">Payment Term</div></th>
							<td><div align="left"><?php echo $rPO['payment_term']?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;">Delivery Date</th>
							<td><div align="left"><?php echo functions::datearr($rPO['delivery_date']);?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;">Status</th>
							<td><div align="left"><?php echo ($rPO['received']==1) ? 'Received' : 'Not Received';?></div></td>
						</tr>
						<tr>
							<th style="background-color:#CCC;">Remarks</th>
							<td>
								<div align="left">
									<?php if($edit){ ?>
									<textarea id="txRemarks" name="txRemarks" rows="4" style="width:500px;"><?php echo $rPO['remarks_monitoring'];?></textarea>
									<?php }else{echo nl2br($rPO['remarks_monitoring']); }?>&nbsp;&nbsp;&nbsp;
									<?php if(empty($edit)){ ?><a href="?po_item_id=<?php echo functions::encode($po_item_id);?>&edt=true" class="btn btn-warning btn-small"><i class="halflings-icon white pencil"></i></a>&nbsp;&nbsp;<?php } ?>
									<?php if($edit){ ?><input type="submit" class="btn btn-primary btn-small" name="btnSave" id="btnSave" value="Save">&nbsp;&nbsp;&nbsp;<a href="?po_item_id=<?php echo functions::encode($po_item_id);?>" class="btn btn-small">Cancel</a><?php } ?>
								</div>
								</td>
						</tr>
						<?php endwhile; // rPOI
					endwhile; //rPO
					?>
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

<!-- end: JavaScript-->
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
</body>
</html>