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
$itemEdt = (isset($_REQUEST['itemEdt']) && !empty($_REQUEST['itemEdt']) ) ? functions::decode($_REQUEST['itemEdt']) : 0;
$merl_id = (isset($_REQUEST['mid']) && !empty($_REQUEST['mid']) ) ? functions::decode($_REQUEST['mid']) : 0;
$q = $db->select('material_excess_release','*',array('merl_id'=>$merl_id));
$r = $db->fetch_array($q);
$proj_id = $r['proj_id'];
$encoded_id = $r['encoded_id'];
$encoded_name = $r['encoded_name'];
$encoded_title = $r['encoded_title'];
$checked_id = $r['checked_id'];
$checked_name = $r['checked_name'];
$checked_title = $r['checked_title'];
$verified_id = $r['verified_id'];
$verified_name = $r['verified_name'];
$verified_title = $r['verified_title'];
$accounted_id = $r['accounted_id'];
$accounted_name = $r['accounted_name'];
$accounted_title = $r['accounted_title'];
$merl_date = $r['merl_date'];
$remarks = $r['remarks'];
$txorno='';
$_SESSION['notif_id_merc_list']=$merl_id;

if( isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ){
	$itemDel = functions::decode($_REQUEST['itemDel']);
	$_SESSION['notif_warning']='Item Removed!';
	$db->delete('material_excess_release_detail',array('merld_id'=>$itemDel));
	functions::sendTo(functions::pageName().'?mid='.functions::encode($merl_id));
	die();
}
if( isset($_POST['btnSearch']) ){
	$txorno = ( isset($_POST['txorno']) && !empty($_POST['txorno']) ) ? trim($_POST['txorno']) : '';
	functions::sendTo('admin-excess-material-release-manage-detail-preview.php?mid='.functions::encode($merl_id).'&orno='.functions::encode($txorno));
	die();
}
if( isset($_POST['btnSave']) ){
	if($itemEdt){
		$date_released = ( isset($_POST['txDateReleased']) && !empty($_POST['txDateReleased']) ) ? trim($_POST['txDateReleased']) : '';
		$date_received = ( isset($_POST['txDateReceived']) && !empty($_POST['txDateReceived']) ) ? trim($_POST['txDateReceived']) : '';
		$db->update('material_excess_release_detail',array('date_released'=>$date_released,'date_received'=>$date_received),array('merld_id'=>$itemEdt));		
		$_SESSION['notif_success']='Changes Saved!';
		$_SESSION['notif_excs_id']=$itemEdt;
	}
	functions::sendTo('?mid='.functions::encode($merl_id));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Excess Material Release Item Manage</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Excess Material Release Item Manage</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="left"><br>
					<div>Project:
						<strong>
						<?php 
						$qProj = $db->select('project','*',array('proj_id'=>$proj_id));
						while($rProj = $db->fetch_array($qProj)):
							echo strtoupper($rProj['proj_name']);
							echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
						endwhile;
						?>
						</strong>
					</div>
					<div>Date: <strong><?php echo functions::datearr($db->getValue('material_excess_release','merl_date',array('merl_id'=>$merl_id)));?></strong></div><br><br>
				</div>
					<table width="100%" border="0" style="font-size:12px;">
						<tr>
							<td>
								<div align="right">Series No. <strong><?php echo $r['series_no']; ?></strong></div>
								<div align="left">
									Order No: <input type="text" name="txorno" id="txorno" value="<?php echo $txorno; ?>">
									<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-info btn-small">
								</div>
								<table id="tblist" width="100%" border="0" class="table <?php if(!isset($_SESSION['notif_excs_id'])){echo 'table-bordered';} ?> table-hover table-striped">
									<thead>
										<tr style="background-color:#CCC;">
											<th><div align="center"><strong>Date Released</strong></div></th>
											<th><div align="center"><strong>Date Received</strong></div></th>
											<th><div align="center"><strong>WI Stock Order No.</strong></div></th>
											<th><div align="center"><strong>Amount</strong></div></th>
											<th width="10%"><div align="center"><strong>&nbsp;</strong></div></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$total_amount = 0;
										$ql = $db->select('material_excess_release_detail','*',array('merl_id'=>$merl_id));
										while($rl = $db->fetch_array($ql)):
											$id = $rl['merld_id'];
											$amount = $db->getValue('inhouse_material_item','sum( (quantity * cost) )',array('im_id'=>$rl['im_id']));
											$total_amount += $amount;
											if($itemEdt==$id){
										?>
										<tr id="rw<?php echo $id;?>">
											<td><div align="center"><input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateReleased" id="txDateReleased" value="<?php echo $rl['date_released'] ?>"></div></td>
											<td><div align="center"><input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDateReceived" id="txDateReceived" value="<?php echo $rl['date_received'] ?>"></div></td>
											<td><div align="center"><?php echo $rl['order_no']; ?></div></td>
											<td><div align="right"><?php echo functions::formatMoney($amount); ?></div></td>
											<td>
												<div align="center">
													<input type="submit" class="btn btn-mini btn-info" name="btnSave" id="btnSave" value="Save">
													<a id="edit" class="btn btn-mini" title="Cancel" data-rel="tooltip" href="?mid=<?php echo functions::encode($merl_id)?>">Cancel</a>
												</div>
											</td>
										</tr>
										<?php }else{ ?>
										<tr id="rw<?php echo $id;?>">
											<td><div align="center"><?php echo functions::datearr($rl['date_released']) ?></div></td>
											<td><div align="center"><?php echo functions::datearr($rl['date_received']) ?></div></td>
											<td><div align="center"><?php echo $rl['order_no']; ?></div></td>
											<td><div align="right"><?php echo functions::formatMoney($amount); ?></div></td>
											<td>
												<div align="center">
													<a class="btn btn-mini btn-info" title="View Order" data-rel="tooltip" href="admin-excess-material-release-manage-detail-preview.php?orno=<?php echo functions::encode($rl['order_no']);?>&mid=<?php echo functions::encode($merl_id)?>"><i class="halflings-icon white search"></i></a>
													<a class="btn btn-mini btn-warning" title="Modify Order" data-rel="tooltip" href="?itemEdt=<?php echo functions::encode($id);?>&mid=<?php echo functions::encode($merl_id)?>"><i class="halflings-icon white pencil"></i></a>
													<a class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Order" data-rel="tooltip" href="?itemDel=<?php echo functions::encode($id);?>&mid=<?php echo functions::encode($merl_id)?>"><i class="halflings-icon white trash"></i></a>
												</div>
											</td>
										</tr>
										<?php } ?>
										<?php endwhile;?>
										<tr>
											<td colspan="4"><div align="right">Total Amount: <strong><?php echo functions::formatMoney($total_amount) ?></strong></div></td>
											<td>&nbsp;</td>
										</tr>
									</tbody>
								</table>
								<table width="100%" border="1" style="display:none;">
									<tr>
										<td align="center">Encoded by:</td>
										<td align="center">Checked and Charged by:</td>
										<td align="center">Verified by:</td>
										<td align="center">Accounted by:</td>
									</tr>
									<tr>
										<td height="80" align="center" valign="bottom">
											<div><strong><?php echo $encoded_name ?></strong></div>
											<div><i><?php echo $encoded_title ?></i></div>
										</td>
										<td align="center" valign="bottom">
											<div><strong><?php echo $checked_name ?></strong></div>
											<div><i><?php echo $checked_title ?></i></div>
										</td>
										<td align="center" valign="bottom">
											<div><strong><?php echo $verified_name ?></strong></div>
											<div><i><?php echo $verified_title ?></i></div>
										</td>
										<td align="center" valign="bottom">
											<div><strong><?php echo $accounted_name ?></strong></div>
											<div><i><?php echo $accounted_title ?></i></div>
										</td>
									</tr>
									<tr>
										<td align="center" valign="bottom">Date: _________</td>
										<td align="center" valign="bottom">Date: _________</td>
										<td align="center" valign="bottom">Date: _________</td>
										<td align="center" valign="bottom">Date: _________</td>
									</tr>
								</table>
							</td>
						</tr>
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
	if(confirm('Do you want to remove this order?'))
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
	$('#txDateReleased,#txDateReceived').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+1 ?>'
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
<?php if(isset($_SESSION['notif_excs_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_excs_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_excs_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_excs_id'] ?>").animate({borderColor:"#87EAC1"}, 3000);
	$("#rw<?php echo $_SESSION['notif_excs_id'] ?>").animate({borderColor:""}, 3000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 3000);
});
</script>
<?php unset($_SESSION['notif_excs_id']);} ?>
<!-- end: JavaScript-->
</body>
</html>