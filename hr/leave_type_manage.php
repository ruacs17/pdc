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
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txDepName='';$txDepDesc='';
$leave_name='';$date_from_mon='';$date_from_day='';$date_to_mon='';$date_to_day='';$with_pay='';$allowed_days='';
if($eid){
	$q = $db->select('leave_config','*',array('lc_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$leave_name = $r['leave_name'];
		$date_from_mon = $r['date_from_mon'];
		$date_from_day = $r['date_from_day'];
		$date_to_mon = $r['date_to_mon'];
		$date_to_day = $r['date_to_day'];
		$with_pay = $r['with_pay'];
		$allowed_days = $r['allowed_days'];
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Leave Type Add</title>
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
<?php
if( isset($_POST['btnSave']) ){
	$leave_name = ( isset($_POST['txLeaveName']) ) ? strtoupper(trim($_POST['txLeaveName'])) : '';
	$allowed_days = ( isset($_POST['txAllowedDays']) && !empty($_POST['txAllowedDays']) ) ? trim($_POST['txAllowedDays']) : 0;
	$with_pay = ( isset($_POST['selLeavePay']) ) ? $_POST['selLeavePay'] : 0;
	$date_from_mon = ( isset($_POST['selMonFrom']) && !empty($_POST['selMonFrom']) ) ? $_POST['selMonFrom'] : '';
	$date_from_day = ( isset($_POST['selDayFrom']) && !empty($_POST['selDayFrom']) ) ? $_POST['selDayFrom'] : '';
	$date_to_mon = ( isset($_POST['selMonTo']) && !empty($_POST['selMonTo']) ) ? $_POST['selMonTo'] : '';
	$date_to_day = ( isset($_POST['selDayTo']) && !empty($_POST['selDayTo']) ) ? $_POST['selDayTo'] : '';

	if( $leave_name){
		$arrField = array('leave_name'=>$leave_name,'allowed_days'=>$allowed_days,'with_pay'=>$with_pay);
		if($eid){
			$db->update('leave_config',$arrField,array('lc_id'=>$eid));
			functions::say('Information Saved!');
			functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid));
		}
		else{
			if( $db->getValue('leave_config','count(*)',array('leave_name'=>$leave_name,'with_pay'=>$with_pay))==0 ){
				$db->insert('leave_config',$arrField);
				functions::say('Information Saved!');
				functions::sendTo($_SERVER['PHP_SELF']);
			}
			else{
				functions::say('Leave Type already exist!');
			}
		}
	}
	else{
		functions::say('Please fill up the form properly!');
	}
}
?>
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
				<div align="center" style="padding-bottom: 15px;"><h2>LEAVE TYPE INFORMATION</h2></div>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"><div align="right">Leave Type Name</div></td>
						<td width="43%"><input type="text" name="txLeaveName" id="txLeaveName" class="span6" value="<?php echo $leave_name?>" required></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Number of Days Allowed</div></td>
						<td><input type="text" name="txAllowedDays" id="txAllowedDays" class="span6" value="<?php echo $allowed_days?>" style="width:150px;"  onkeypress="return checkinput(this, event);"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Payment Status</div></td>
						<td>
							<select name="selLeavePay" id="selLeavePay" required>
								<option value="1" <?php if($with_pay==1)echo 'selected="selected"';?>>With Pay</option>
								<option value="0" <?php if($with_pay==0)echo 'selected="selected"';?>>Without Pay</option>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><div align="left"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small"></div></td>
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
<!-- end: JavaScript-->
</body>
</html>