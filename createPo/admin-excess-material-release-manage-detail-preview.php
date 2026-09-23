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
$orno = (isset($_REQUEST['orno']) && !empty($_REQUEST['orno']) ) ? functions::decode($_REQUEST['orno']) : 0;
$merl_id = (isset($_REQUEST['mid']) && !empty($_REQUEST['mid']) ) ? functions::decode($_REQUEST['mid']) : 0;

$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? functions::decode($_REQUEST['im_id']) : 0;
$qIH = $db->select('inhouse_material','*',array('im_no'=>$orno));
$rIH = $db->fetch_array($qIH);
$prepared_by = $rIH['prepared_by'];
$im_id = $rIH['im_id'];

$merld_id = $db->getValue('material_excess_release_detail','merld_id',array('order_no'=>$orno));
if($merld_id){$_SESSION['notif_excs_id']=$merld_id;}

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

if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
	$poidDel = functions::decode($_REQUEST['poidDel']);
	$_SESSION['notif_warning']='Item Removed!';
	$db->delete('material_excess_release_detail',array('merld_id'=>$poidDel));
	functions::sendTo(functions::pageName().'?mid='.functions::encode($merl_id));
	die();
}
if( isset($_POST['btnSearch']) ){
	$txorno = ( isset($_POST['txorno']) && !empty($_POST['txorno']) ) ? trim($_POST['txorno']) : '';
	functions::sendTo('?mid='.functions::encode($merl_id).'&orno='.functions::encode($txorno));
	die();
}

if( isset($_POST['btnAdd']) ){
	$txorno = ( isset($_POST['txorno']) && !empty($_POST['txorno']) ) ? trim($_POST['txorno']) : '';
	if( $im_id = $db->getValue('inhouse_material','im_id',array('im_no'=>$txorno)) ){
		$im_id = $db->getValue('inhouse_material','im_id',array('im_id'=>$im_id));
		$im_no = $db->getValue('inhouse_material','im_no',array('im_id'=>$im_id));
		if( $db->getValue('material_excess_release_detail','count(*)',array('merl_id'=>$merl_id,'im_id'=>$im_id))==0 ){
			$ins = $db->insert('material_excess_release_detail',array('merl_id'=>$merl_id,'im_id'=>$im_id,'order_no'=>$im_no));
			$_SESSION['notif_excs_id']=$ins;
		}
	}
	$_SESSION['notif_success']='New Order Successfully Added!';
	functions::sendTo('admin-excess-material-release-manage-detail.php?mid='.functions::encode($merl_id));
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Excess Material Receive Item Manage</h2>
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
									Order No: <input type="text" name="txorno" id="txorno" value="<?php echo $orno; ?>">
									<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-info btn-small">
									<?php if(empty($merld_id)){ ?><input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-success btn-small"><?php } ?>
									<a href="admin-excess-material-release-manage-detail.php?mid=<?php echo functions::encode($merl_id)?>" class="btn btn-small">Back</a>
								</div>
								<table width="100%" border="0">
									<tr>
										<td align="right">Order No: <strong><?php echo $rIH['im_no'];?></strong></td>
									</tr>
									<tr>
										<td align="right">Date:<strong> <?php echo functions::datearr($rIH['im_date']);?></strong></td>
									</tr>
									<tr>
										<td>&nbsp;</td>
									</tr>
									<tr>
										<td>Charge To: <strong><?php echo ($rIH['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rIH['proj_id'])) : $db->getValue('inhouse_material','payee',array('im_id'=>$im_id));?></strong></td>
									</tr>
									<?php if($rIH['address']){?>
									<tr>
										<td>Address: <strong><?php echo $rIH['address'];?></strong></td>
									</tr>
									<?php }?>
									<tr>
										<td>&nbsp;</td>
									</tr>
								</table>
								<table width="100%" border="0" class="table table-hover table-bordered table-striped">
									<thead>
										<tr style="background-color:#CCC;">
											<th width="40%" scope="col"><div align="center">Item</div></th>
											<th width="8%" scope="col"><div align="center">Qty</div></th>
											<th width="10%" scope="col"><div align="center">Unit</div></th>
											<th width="20%" scope="col"><div align="center">Brand</div></th>
											<th width="10%" scope="col"><div align="center">Cost</div></th>
											<th width="15%" scope="col"><div align="center">Amount</div></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$total_amount=0;
										$amount=0;
										$q = $db->select('inhouse_material_item','*',array('im_id'=>$im_id),'ORDER BY item');
										while($r = $db->fetch_array($q)):
										$amount = $r['cost'] * $r['quantity'];
										$disc_amount = ($r['discount']) ? $amount * ($r['discount'] / 100) : 0;
										$amount = $amount - $disc_amount;
										$total_amount += $amount;
										?>
										<tr>
											<td><div align="left" class="padParLeft"><?php echo $r['item'];?></div></td>
											<td><div align="center"><?php echo $r['quantity'];?></div></td>
											<td><div align="center"><?php echo $r['unit'];?></div></td>
											<td><div align="center"><?php echo $r['brand'];?></div></td>
											<td><div align="right"><?php echo functions::formatMoney($r['cost']);?>&nbsp;&nbsp;</div></td>
											<td><div align="right"><?php echo functions::formatMoney($amount);?> <?php echo ($r['discount']) ? '<br><i style="font-size:11px">('.$r['discount'].'% off)</i>' : '';?>&nbsp;&nbsp;</div></td>
										</tr>
										<?php endwhile;?>
										<tr>
											<td colspan="5"><div align="right"><strong>Total</strong>&nbsp;&nbsp;</div></td>
											<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong>&nbsp;&nbsp;</div></td>
										</tr>
									</tbody>
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
	if(confirm('Do you want to remove this item?'))
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
function searchMaterial(srch) {  
	var strURL="po_view_material_view.php?srch="+srch+"&poidEdt=<?php echo functions::encode($poidEdt)?>&po_id=<?php echo functions::encode($merc_id)?>"; 
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
<?php if(isset($_SESSION['notif_newid'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_newid'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_newid'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_newid'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_newid'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_newid']);} ?>
<!-- end: JavaScript-->
</body>
</html>