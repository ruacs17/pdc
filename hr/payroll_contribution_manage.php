<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eat_id = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eat_id));
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txtSSSContrib='';$txtPHContrib='';$txtPagIbigContrib='';
$arrPosition=array();
if($eid){
	$q = $db->select('employee','*',array('emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txtPagIbigContrib = functions::formatMoney($r['pagibig_contribution']);
		$txtPHContrib = functions::formatMoney($r['philhealth_contribution']);
		$txtSSSContrib = functions::formatMoney($r['sss_contribution']);
	endwhile;
}

if( isset($_POST['btnSave']) && !empty($eid) ){
	$pagibig_contribution = ( isset($_POST['txtPagIbigContrib']) ) ? functions::moneyToDouble($_POST['txtPagIbigContrib']) : '';
	$philhealth_contribution = ( isset($_POST['txtPHContrib']) ) ? functions::moneyToDouble($_POST['txtPHContrib']) : '';
	$sss_contribution = ( isset($_POST['txtSSSContrib']) && !empty($_POST['txtSSSContrib']) ) ? functions::moneyToDouble($_POST['txtSSSContrib']) : 0;
	$arrUpdate = array('pagibig_contribution'=>$pagibig_contribution,'philhealth_contribution'=>$philhealth_contribution,'sss_contribution'=>$sss_contribution);
	$db->update('employee',$arrUpdate,array('emp_id'=>$eid));
	functions::say('Updated Successfully!');
	functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Update</title>
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
	<script src="../js/formatCurrency.js"></script>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" enctype="multipart/form-data">
				<div align="center" style="padding-bottom: 15px;"><h2>CONTRIBUTION EVERY PAYROLL</h2></div>
				<table width="30%" align="center" border="0" class="tablea table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td height="30" style="padding: 10px"><div align="right">SSS </div></td>
						<td><input type="text" name="txtSSSContrib" id="txtSSSContrib" style="width: 144px;" class="span6" value="<?php echo $txtSSSContrib?>" onkeyup="FormatCurrency(this);"></td>
					</tr>
					<tr>
						<td height="30" style="padding: 10px"><div align="right">Philhealth </div></td>
						<td><input type="text" name="txtPHContrib" id="txtPHContrib" style="width: 144px;" class="span6" value="<?php echo $txtPHContrib?>" onkeyup="FormatCurrency(this);"></td>
					</tr>
					<tr>
						<td width="45%" height="30" style="padding: 10px"><div align="right">Pagibig </div></td>
						<td><input type="text" name="txtPagIbigContrib" id="txtPagIbigContrib" style="width: 144px;" class="span6" value="<?php echo $txtPagIbigContrib?>" onkeyup="FormatCurrency(this);"></td>
					</tr>
				</table><br>
				<?php if($confirmed==0){?>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary">
				</div>
				<?php }?>
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
</body>
</html>