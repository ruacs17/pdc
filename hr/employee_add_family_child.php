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
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$childEdit = (isset($_REQUEST['cidEdt']) && !empty($_REQUEST['cidEdt']) ) ? functions::decode($_REQUEST['cidEdt']) : 0;
$txtlName='';$txtfName='';$txtmName='';$txtExtName='';$bdMon='';$bdDay='';$bdYear='';$ec_bdate='';
if($childEdit){
	$q = $db->select('emp_child','*',array('ec_id'=>$childEdit,'emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txtlName = $r['ec_lname'];
		$txtfName = $r['ec_fname'];
		$txtmName = $r['ec_mname'];
		$txtExtName = $r['ec_extname'];
		$ec_bdate = $r['ec_bdate'];
	endwhile;
}
else{
	$q = $db->select('employee','*',array('emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txtlName = $r['lname'];
		$txtmName = $r['sp_lname'];
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Children Manage</title>
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
	<style type="text/css">body{font-size: 12px;}</style>
<?php
if( isset($_POST['btnSave']) ){

	$lname = ( isset($_POST['txtlName']) ) ? strtoupper(trim($_POST['txtlName'])) : '';
	$fname = ( isset($_POST['txtfName']) ) ? strtoupper(trim($_POST['txtfName'])) : '';
	$mname = ( isset($_POST['txtmName']) ) ? strtoupper(trim($_POST['txtmName'])) : '';
	$extname = ( isset($_POST['txtExtName']) ) ? strtoupper(trim($_POST['txtExtName'])) : '';
	$ec_bdate = ( isset($_POST['txBDate']) && !empty($_POST['txBDate']) ) ? trim($_POST['txBDate']) : NULL;

    $arrIns = array('ec_lname'=>$lname,'ec_fname'=>$fname,'ec_mname'=>$mname,'ec_extname'=>$extname,'ec_bdate'=>$ec_bdate,'emp_id'=>$eid);
	if($childEdit){
		$db->update('emp_child',$arrIns,array('ec_id'=>$childEdit,'emp_id'=>$eid));
		$_SESSION['notif_success']='Changes Saved!';
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&cidEdt='.functions::encode($childEdit).'&fromED='.$fromED);
		die();
	}
	else{
		$ins_id = $db->insert('emp_child',$arrIns);
		if( $ins_id ){
			$_SESSION['notif_success']='Information Added!';
			functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
			die();
		}
		else
			functions::say('Please fill up the form properly!');
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Child Information</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"></td>
						<td width="43%"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Child's Name</div></td>
						<td>
							<table width="70%" border="0">
								<tr>
									<td width="25%">First Name</td>
									<td><input type="text" name="txtfName" id="txtfName" class="span6" value="<?php echo $txtfName?>" style="width:314px;" required /></td>
								</tr>
								<tr>
									<td>Middle Name</td>
									<td><input type="text" name="txtmName" id="txtmName" class="span6" value="<?php echo $txtmName?>" style="width:314px;" /></td>
								</tr>
								<tr>
									<td>Last Name</td>
									<td><input type="text" name="txtlName" id="txtlName" class="span6" value="<?php echo $txtlName?>" style="width:314px;" required /></td>
								</tr>
								<tr>
									<td>Suffix (JR, SR, III)</td>
									<td><input type="text" name="txtExtName" id="txtExtName" class="span6" value="<?php echo $txtExtName?>" style="width:314px;" /></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height="30" colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Date of Birth</div></td>
						<td>
							<a href="javascript:NewCssCal('txBDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
							<input name="txBDate" type="text" class="span6 mytextbox" id="txBDate" value="<?php echo $ec_bdate; ?>" style="width: 90px;" readonly>
						</td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
</script>
<!-- end: JavaScript-->
</body>
</html>