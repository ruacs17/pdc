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
$count=0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$_SESSION['notif_id_list2']=$itemID;
$cag_id=(isset($_REQUEST['cag_id']) && !empty($_REQUEST['cag_id']) ) ? functions::decode($_REQUEST['cag_id']) : 0;
$cagd_id=(isset($_REQUEST['cagd_id']) && !empty($_REQUEST['cagd_id']) ) ? functions::decode($_REQUEST['cagd_id']) : 0;
$_SESSION['notif_id_list']=$cagd_id;
$itemUnit="";
$itemQuantity="";
$itemCost="";
if($cagd_id)
	$q_detail = $db->select('cag_detail','*',array('cagd_id'=>$cagd_id));
else
	$q_detail = $db->select('cag_detail','*',array('cag_id'=>$cag_id,'element_id'=>$itemID));
while($r_detail = $db->fetch_array($q_detail)):
	$itemUnit=$r_detail['unit'];
	$itemQuantity=$r_detail['quantity']; 
	$itemID=$r_detail['element_id'];  
endwhile;

$itemDesc = "";
$rate = "";
$q = $db->select('elm_rate','*',array('id'=>$itemID));
while($r = $db->fetch_array($q)):
	$itemDesc = $r['itemDesc'];
	$rate = $r['itemRate'];
	$itemUnit=$r['itemUnit'];
endwhile;
$capability = $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$itemDesc));
if( $itemQuantity && $rate )
	$itemCost = $itemQuantity * $rate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Cost Analysis Material add</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
	<style>
	a{
		opacity: 0.4;
		filter: alpha(opacity=20); /* For IE8 and earlier */
	}
	a:hover{
		opacity: 1.0;
		filter: alpha(opacity=100); /* For IE8 and earlier */
	}
	</style>
	<?php
	if( isset($_POST['btnSave']) ){
		$itemUnit = (isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
		$itemQuantity = (isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : '';
		$itemCost = (isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? trim($_POST['txCost']) : '';
		if( $itemQuantity){
			if( $db->getValue('cag_detail','count(*)',array('cag_id'=>$cag_id,'element_id'=>$itemID)) )
				$db->update('cag_detail',array('unit'=>$itemUnit,'quantity'=>$itemQuantity),array('cag_id'=>$cag_id,'element_id'=>$itemID));
			else
				$cagd_id=$db->insert('cag_detail',array('cag_id'=>$cag_id,'element_id'=>$itemID,'unit'=>$itemUnit,'quantity'=>$itemQuantity));
			$_SESSION['notif_success']="Item Saved!";
			$_SESSION['notif_id_list']=$cagd_id;
			unset($_SESSION['notif_id_list2']);
		}
		functions::sendTo('ca_material_detail.php?cag_id='.functions::encode($cag_id));
		die();
	}
	?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<form method="post">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>Cost Analysis Material</h2>
			</div>
			<div class="box-content">
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class=" table table-bordered" style="font-size:12px;">
					<tr bgcolor="#CCCCCC">
						<th width="33%" scope="col">Material</th>
						<th width="9%" scope="col"><div align="center">Capabilites</div></th>
						<th width="9%" scope="col"><div align="center">Unit</div></th>
						<th width="9%" scope="col"><div align="center">Quantity</div></th>
						<th width="9%" scope="col"><div align="center">Unit Cost</div></th>
						<th width="9%" scope="col"><div align="center">Cost</div></th>
					</tr>
					<tr>
						<td><?php echo $itemDesc?></td>
						<td><div align="center"><?php echo $capability;?></td>
						<td><div align="center"><input type="text" style="width:80px;text-align:center;" name="txUnit" id="txUnit" value="<?php echo $itemUnit?>" onkeypress="return checkinput(this, event);" readonly></div></td>
						<td><div align="center"><input type="text" style="width:80px;text-align:center;" name="txQuantity" id="txQuantity" value="<?php echo $itemQuantity?>" onkeypress="return checkinput(this, event);" onKeyUp="qtyCompute()" required></div></td>
						<td><div align="center"><?php echo functions::formatMoney($rate)?></div></td>
						<td><div align="center"><input type="text" style="width:80px;text-align:center;" name="txCost" id="txCost" value="<?php echo $itemCost;?>" onkeypress="return checkinput(this, event);" required></div></td>
					</tr>
				</table><p>&nbsp;</p>
				<div align="center">
					<input type="submit" class="btn btn-small btn-info" name="btnSave" value="Save">
					<a href="ca_material_detail.php?cag_id=<?php echo functions::encode($cag_id)?>" class="btn btn-small">Cancel</a>
				</div>
			</div>
		</div><!--/span-->
	</form>
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
function qtyCompute(){
	var q = document.getElementById('txQuantity').value;
	var r = <?php echo $rate;?>;
	if( (q>0) && (r>0) ){
		document.getElementById('txCost').value = parseFloat(q*r).toFixed(2);
	}
	else{
		document.getElementById('txCost').value = "";
	}
}
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<!-- end: JavaScript-->
</body>
</html>