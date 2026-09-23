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
$uid = (isset($_REQUEST['uid']) && !empty($_REQUEST['uid']) ) ? functions::decode($_REQUEST['uid']) : 0;
$_SESSION['notif_id_list']=$uid;
$msg='';$memberUName='';$txPasswrd='';$txLname='';$txFname='';$txMname='';$txEmail='';$txMobile='';$errorUsername='';$txAddress='';$role='';$bdate='';$status='';
$qUser = $db->select('users','*',array('user_id'=>$uid));
if($db->num_rows($qUser)){
	$rInfo = $db->fetch_array($qUser);
	$memberUName = $rInfo['username'];
	$txLname=$rInfo['lname'];
	$txFname=$rInfo['fname'];
	$txMname=$rInfo['mname'];
	$txEmail=$rInfo['email'];
	$txMobile=$rInfo['mobile'];
	$txAddress=$rInfo['address'];
	$role=$db->getValue('role_assignment','role_id',array('user_id'=>$rInfo['user_id']));
	$bdate = functions::datearr($rInfo['bdate']);
	$status = $rInfo['status'];
	$uid = $rInfo['user_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Personnel Information</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Add Personnel Form</h2>
		</div>
		<div class="box-content">
			<table width="50%" align="center" border="0" class="table table-bordered table-hover">
				<tr>
					<td width="49%" height="30"><div align="right">Username</div></td>
					<td width="50%"><strong><?php echo $memberUName;?></strong></td>
				</tr>
				<tr>
					<td height="30" align="right"><div align="right">Name</div></td>
					<td><strong><?php echo strtoupper($txFname.' '.$txMname.' '.$txLname);?></strong></td>
				</tr>
				<tr>
					<td height="30"><div align="right">Address</div></td>
					<td><strong><?php echo $txAddress;?></strong></td>
				</tr>
				<tr>
					<td height="30"><div align="right">Email</div></td>
					<td><strong><?php echo $txEmail;?></strong></td>
				</tr>
				<tr>
					<td height="30"><div align="right">Mobile</div></td>
					<td><strong><?php echo $txMobile;?></strong></td>
				</tr>
				<tr>
					<td height="30"><div align="right">Position</div></td>
					<td>
						<strong>
						<?php
						$qRole = $db->query('SELECT * FROM role_assignment ra, role r WHERE ra.role_id=r.role_id AND user_id="'.$db->clean($uid).'"');
						while( $rRole = $db->fetch_array($qRole)):
							echo $rRole['role_description'].'<br>';
						endwhile;
						?>
						</strong>
					</td>
				</tr>
				<tr>
					<td height="30"><div align="right">Status</div></td>
					<td><strong><?php echo strtoupper($status);?></strong></td>
				</tr>
				<tr>
					<td align="center" colspan="2"><div align="center"><a id="detail" href="user_edit.php?uid=<?php echo functions::encode($uid);?>" class="btn btn-small btn-info thickbox" title="Modify this account" data-rel="tooltip">Edit</a></div></td>
				</tr>
			</table>
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
$(document).ready(function(){
$('#btnUpdate').click(function(){
	var submitStatus = false;
	resetErrorMsg();
	if( $('#txUname').val() == ""){
		$('#msgUsername').html('Username required!');
		$('#txUname').focus();
	}
	else if( $('#txUname').val().length <= 5){
		$('#msgUsername').html('Username must be atleast 6 characters!');
		$('#txUname').focus();
	}
	else if( $('#txPasswrd').val() == "" ){
		$('#msgPasswrd').html('Password required!');
		$('#txPasswrd').focus();
	}
	else if( $('#txPasswrd').val().length < 5 ){
		$('#msgPasswrd').html('Password must be atleast 6 characters!');
		$('#txPasswrd').focus();
	}
	else if( $('#txCPasswrd').val() == "" ){
		$('#msgCPasswrd').html('Confirmation Password required!');
		$('#txCPasswrd').focus();
	}
	else if( $('#txCPasswrd').val() != $('#txPasswrd').val() ){
		$('#msgCPasswrd').html('Confirmation Password does not match!');
		$('#txCPasswrd').focus();
	}
	else if( $('#txFname').val() == "" ){
		$('#msgFname').html('First Name required!');
		$('#txFname').focus();
	}
	else if( $('#txLname').val() == "" ){
		$('#msgLname').html('Last Name required!');
		$('#txLname').focus();
	}
	else if( $('#bdMon').val() == ""){
		$('#msgBday').html('Birth date required!');
		$('#bdMon').focus();
	}
	else if( $('#bdDay').val() == ""){
		$('#msgBday').html('Birth date required!');
		$('#bdDay').focus();
	}
	else if( $('#bdYear').val() == ""){
		$('#msgBday').html('Birth date required!');
		$('#bdYear').focus();
	}
	else if( $('#txEmail').val() == "" ){
		$('#msgEmail').html('Email address required!');
		$('#txEmail').focus();
	}
	else if( checkEmail($('#txEmail').val())==false ){
		$('#msgEmail').html('Invalid Email address!');
		$('#txEmail').focus();
	}
	else if( $('#txMobile').val().length != 11 ){
		$('#msgMobile').html('11 digit mobile number required!');
		$('#txMobile').focus();
	}
	else if( $('#selRole').val() == "" ){
		$('#msgPosition').html('Upline required!');
		$('#selRole').focus();
	}
	else{
		if( confirm("Are all information true?") )
			submitStatus=true;
	}
	return submitStatus;
	});
	function resetErrorMsg(){
		$('#msgUsername').html('');
		$('#msgPasswrd').html('');
		$('#msgCPasswrd').html('');
		$('#msgFname').html('');
		$('#msgLname').html('');
		$('#msgEmail').html('');
		$('#msgMobile').html('');
	}

	function checkEmail(email){
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		if( reg.test(email)==false )
			return false;
		else
			return true;
	}
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