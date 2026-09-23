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
$did = (isset($_REQUEST['did']) && !empty($_REQUEST['did']) ) ? functions::decode($_REQUEST['did']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher Type Modify</title>
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
<?php
if( isset($_POST['btnSave']) ){
	$arrUpdate=array();
	$inchargeID="";
	$did = (isset($_POST['did']) && !empty($_POST['did']) ) ? functions::decode($_POST['did']) : 0;
	$txName = ( isset($_POST['txName']) && !empty($_POST['txName']) ) ? $_POST['txName'] : '';
	$txDesc = ( isset($_POST['txDesc']) && !empty($_POST['txDesc']) ) ? $_POST['txDesc'] : '';
	$old_name = $db->getValue('voucher_type','vt_name',array('vt_id'=>$did));

	if( $txName && $old_name){
		if( $db->getValue('voucher_type','vt_name',array('vt_id'=>$did))==$txName ){ #no changes on project name
			$db->update('voucher_type',array('vt_name'=>$txName,'vt_desc'=>$txDesc),array('vt_id'=>$did));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo('voucher_type_edit.php?did='.functions::encode($did));
			die();
		}
		else if( $db->getValue('voucher_type','count(vt_name)',array('vt_name'=>$txName),'and vt_id!="'.$db->clean($did).'"')==0 ){ #if there's changes on category's name, check if it's unique
			$db->update('voucher_type',array('vt_name'=>$txName,'vt_desc'=>$txDesc),array('vt_id'=>$did));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo('voucher_type_edit.php?did='.functions::encode($did));
			die();
		}
		else{
			functions::say('Voucher Type name already existed!');
		}
	}
}
$name='';$desc='';
$qDisp = $db->select('voucher_type','*',array('vt_id'=>$did));
if($db->num_rows($qDisp)){
	$rInfo = $db->fetch_array($qDisp);
	$name = $rInfo['vt_name'];
	$desc = $rInfo['vt_desc'];
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Type Update Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<input type="hidden" name="did" id="did" value="<?php echo functions::encode($did);?>">        
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30">Voucher Type Name</td>
						<td width="43%"><input type="text" name="txName" id="txName" class="span6" value="<?php echo $name;?>"></td>
					</tr>
					<tr>
						<td height="30">Description</td>
						<td><input type="text" name="txDesc" id="txDesc" class="span6" value="<?php echo $desc?>" /></td>
					</tr>
				</table>
				<div align="center"><input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-small btn-primary" onClick="if(confirm('Do you want to save this information?')){return true;}else{return false;}"></div>
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