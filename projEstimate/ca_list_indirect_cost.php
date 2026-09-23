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
$proj_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$mobile_default=0;
$mobile_value=0;

$ocm_default=0;
$ocm_value=0;

$contractor_default=0;
$contractor_value=0;

if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id)) ){
	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id,'mobilization'=>NULL)) ){
		$mobile_default=1;
	}
	else{
		$mobile_value=$db->getValue('dqc_boq_ca','mobilization',array('proj_id'=>$proj_id));
	}

	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id,'overhead'=>NULL)) ){
		$ocm_default=1;
	}
	else{
		$ocm_value=$db->getValue('dqc_boq_ca','overhead',array('proj_id'=>$proj_id));
	}

	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id,'contractor'=>NULL)) ){
		$contractor_default=1;
	}
	else{
		$contractor_value=$db->getValue('dqc_boq_ca','contractor',array('proj_id'=>$proj_id));
	}
	$vat=$db->getValue('dqc_boq_ca','vat',array('proj_id'=>$proj_id));
}
else{
	$mobile_default=1; $ocm_default=1;$contractor_default=1;$vat = 5;
}

if( isset($_POST['btnSave']) ){
	$optMobile = isset($_POST['optMobil']) ? $_POST['optMobil'] : 0;
	$mobilization = isset($_POST['txMobil']) ? $_POST['txMobil'] : 0;
	$optOCM = isset($_POST['optOCM']) ? $_POST['optOCM'] : 0;
	$overhead = isset($_POST['txOCM']) ? $_POST['txOCM'] : 0;
	$optCP = isset($_POST['optCP']) ? $_POST['optCP'] : 0;
	$contractor = isset($_POST['txCP']) ? $_POST['txCP'] : 0;
	$vat = isset($_POST['txVAT']) ? $_POST['txVAT'] : 0;
	$arrField = array();
	$mobileValue = ($optMobile==2) ? $mobilization : NULL;
	$overheadValue = ($optOCM==2) ? $overhead : NULL;
	$contractorValue = ($optCP==2) ? $contractor : NULL;
	$arrField = array('mobilization'=>$mobileValue,'overhead'=>$overheadValue,'contractor'=>$contractorValue,'vat'=>$vat,'proj_id'=>$proj_id,'sig_type'=>'ca');
	if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id)) ){
		$db->update('dqc_boq_ca',$arrField,array('proj_id'=>$proj_id));
	}
	else{
		$db->insert('dqc_boq_ca',$arrField);
	}
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?pid='.functions::encode($proj_id));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Cost Analysis Indirect Cost</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
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
	<script>
	function delt(){
		if(confirm('Do you want to remove this Item?'))
			return true;
		else
			return false; 
	}
	</script>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>POW</h2>
		</div>

		<div class="box-content">
			<form method="post">
				<div align="center">
					<div style="width:50%">
						<table id="tblist" class="table table-striped table-hover table-bordered">
							<tr id="pl1">
								<th width="50%"><div style="padding-top:20px;">Mobilization</div></th>
								<td>
									<div class="control-group">
										<label class="control-label"></label>
										<div class="controls">
											<label class="radio">
												<input type="radio" name="optMobil" id="optMobil1" value="1" <?php echo ($mobile_default) ? 'checked="checked"' : '' ?>>
												Default Value
											</label>
											<div style="clear:both; padding:10px;"></div>
											<label class="radio">
												<input type="radio" name="optMobil" id="optMobil2" value="2" <?php echo ($mobile_default==0) ? 'checked="checked"' : '' ?>>
												<input type="number" name="txMobil" id="txMobil" min="0" max="100" step="0.01" value="<?php echo ($mobile_default==0) ? $mobile_value : '';?>" onclick="document.getElementById('optMobil2').checked=true;" style="width:50px;"> <strong>%</strong>
											</label>
										</div>
									</div>
								</td>
							</tr>
							<tr id="pl2">
								<th><div style="padding-top:20px;">Overhead, Contengencies & Miscellaneous</div></th>
								<td>
									<div class="control-group">
										<label class="control-label"></label>
										<div class="controls">
											<label class="radio">
												<input type="radio" name="optOCM" id="optOCM1" value="1" <?php echo ($ocm_default) ? 'checked="checked"' : '' ?>>
												Default Value
											</label>
											<div style="clear:both; padding:10px;"></div>
											<label class="radio">
												<input type="radio" name="optOCM" id="optOCM2" value="2" <?php echo ($ocm_default==0) ? 'checked="checked"' : '' ?>>
												<input type="number" name="txOCM" id="txOCM" min="0" max="100" step="0.01" value="<?php echo ($ocm_default==0) ? $ocm_value : '' ?>" onclick="document.getElementById('optOCM2').checked=true;" style="width:50px;"> <strong>%</strong>
											</label>
										</div>
									</div>
								</td>
							</tr>
							<tr id="pl3">
								<th><div style="padding-top:20px;">Contractor's Profit</div></th>
								<td>
									<div class="control-group">
										<label class="control-label"></label>
										<div class="controls">
											<label class="radio">
												<input type="radio" name="optCP" id="optCP1" value="1" <?php echo ($contractor_default) ? 'checked="checked"' : '' ?>>
												Default Value
											</label>
											<div style="clear:both; padding:10px;"></div>
											<label class="radio">
												<input type="radio" name="optCP" id="optCP2" value="2" <?php echo ($contractor_default==0) ? 'checked="checked"' : '' ?>>
												<input type="number" name="txCP" id="txCP" min="0" max="100" step="0.01" value="<?php echo ($contractor_default==0) ?$contractor_value : '' ?>" onclick="document.getElementById('optCP2').checked=true;" style="width:50px;"> <strong>%</strong>
											</label>
										</div>
									</div>
								</td>
							</tr>
							<tr id="pl4">
								<th><div style="padding-top:20px;">VAT</div></th>
								<td><div style="padding-top:20px;"><input type="number" name="txVAT" id="txVAT" min="0" max="100" step="0.01" value="<?php echo $vat ?>" style="width:50px;"> <strong>%</strong></div></td>
							</tr>
							<tr>
								<th></th>
								<td><input type="submit" class="btn btn-primary btn-small" name="btnSave" id="btnSave" value="Save" onClick="askcon()"></td>
							</tr>
						</table>						
					</div>
				</div>

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
<script>
function askcon(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false; 
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