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
$msg='';$memberUName='';$txPasswrd='';$txLname='';$txFname='';$txMname='';$txEmail='';$txMobile='';$errorUsername='';$txAddress='';$role='';$bdate='';$txbYear='';$txbMon='';$txbDay='';$status='';
if( isset($_POST['btnSave']) ){
	$arr_userDetail=array();
	$uid = ( isset($_POST['uid']) && !empty($_POST['uid']) ) ? functions::decode($_POST['uid']) : '';
	$memberUName = ( isset($_POST['txUname']) && !empty($_POST['txUname']) ) ? $_POST['txUname'] : '';
	$txPasswrd = ( isset($_POST['txPasswrd']) && !empty($_POST['txPasswrd']) ) ? $_POST['txPasswrd'] : '';
	$txLname = ( isset($_POST['txLname']) && !empty($_POST['txLname']) ) ? $_POST['txLname'] : '';
	$txFname = ( isset($_POST['txFname']) && !empty($_POST['txFname']) ) ? $_POST['txFname'] : '';
	$txMname = ( isset($_POST['txMname']) && !empty($_POST['txMname']) ) ? $_POST['txMname'] : '';
	$txEmail = ( isset($_POST['txEmail']) && !empty($_POST['txEmail']) ) ? $_POST['txEmail'] : '';
	$txMobile = ( isset($_POST['txMobile']) && !empty($_POST['txMobile']) ) ? $_POST['txMobile'] : '';
	$txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '00';
	$txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '00';
	$txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '0000';
	$txAddress = ( isset($_POST['txAddress']) && !empty($_POST['txAddress']) ) ? $_POST['txAddress'] : '';
	$role = ( isset($_POST['selRole']) && !empty($_POST['selRole']) ) ? $_POST['selRole'] : '';
	$txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;
	$status = ( isset($_POST['selStat']) && !empty($_POST['selStat']) ) ? $_POST['selStat'] : '';

	if( empty($memberUName) || empty($txLname) || empty($txFname) || empty($role) ){
		$msg='<div class="alert alert-error"><strong>Oops!</strong> Please fill up the form properly!</div>';
	}
	else if( functions::has_special_char($memberUName) ){
		$msg='<div class="alert alert-error"><strong>Warning!</strong> Username must have no any special character!</div>';
	}
	else if( $db->getValue('users','count(*)',array('username'=>$memberUName),'AND user_id != "'.$db->clean($uid).'"') ){
		$errorUsername='Username already exist, please create another username.';
	}
	else{
		$arr_userDetail = array('username'=>$memberUName,'lname'=>strtoupper($txLname),'fname'=>strtoupper($txFname),'mname'=>strtoupper($txMname),'bdate'=>$txBdate,'address'=>$txAddress,'email'=>$txEmail,'mobile'=>$txMobile,'branch'=>'Cebu','reg_date'=>date('Y-m-d'),'reg_time'=>date('H:i:s'),'status'=>$status);
		if($txPasswrd)
			$arr_userDetail = array_merge($arr_userDetail,array('password'=>functions::encode($txPasswrd)));
		if( !empty($txbMon) && !empty($txbDay) && !empty($txbYear) )
			$arr_userDetail = array_merge($arr_userDetail,array('bdate'=>$txBdate));
		$db->update('users',$arr_userDetail,array('user_id'=>$uid));
		$db->delete('role_assignment',array('user_id'=>$uid));
		$db->insert('role_assignment',array('role_id'=>$role,'user_id'=>$uid));
		$_SESSION['notif_success']='Changes Saved';
		functions::sendTo('user_info.php?uid='.functions::encode($uid));
		die();
	}
}

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
	$bdate = explode("-",$rInfo['bdate']);
	if(count($bdate)){
		$txbYear=$bdate[0];
		$txbMon=$bdate[1];
		$txbDay=$bdate[2];
	}
	$status = $rInfo['status'];
	$uid = $rInfo['user_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Personnel Add</title>
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
<?php echo $msg;?>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Add Personnel Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal form-actions" method="post">
				<input type='hidden' name='uid' id='uid' value='<?php echo functions::encode($uid);?>'>
				<table width="60%" align="center" border="0">
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Username</label>
								<div class="controls">
									<input type="text" name="txUname" id="txUname" value="<?php echo $memberUName;?>"/>
									<span class="help-inline warning" id="msgUsername" style="font-weight:bold;" name="msgUsername"><?php echo $errorUsername;?></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Password</label>
								<div class="controls">
									<input type="password" name="txPasswrd" id="txPasswrd" value="<?php echo $txPasswrd;?>" />
									<span class="help-inline warning" id="msgPasswrd" style="font-weight:bold;" name="msgPasswrd"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">First Name</label>
								<div class="controls">
									<input type="text" name="txFname" id="txFname" value="<?php echo $txFname;?>" />
									<span class="help-inline warning" id="msgFname" style="font-weight:bold;" name="msgFname"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Middle Name</label>
								<div class="controls">
									<input type="text" name="txMname" id="txMname" value="<?php echo $txMname;?>" />
									<span class="help-inline warning"></span>
								</div>
							</div>
						</td>
					</tr>  
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Last Name</label>
								<div class="controls">
									<input type="text" name="txLname" id="txLname" value="<?php echo $txLname;?>" />
									<span class="help-inline warning" style="font-weight:bold;" id="msgLname" name="msgLname"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Email</label>
								<div class="controls">
									<input type="text" name="txEmail" id="txEmail" value="<?php echo $txEmail;?>" />
									<span class="help-inline warning" style="font-weight:bold;" id="msgEmail" name="msgEmail"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Mobile</label>
								<div class="controls">
									<input type="text" name="txMobile" id="txMobile" value="<?php echo $txMobile;?>" />
									<span class="help-inline warning" style="font-weight:bold;" id="msgMobile" name="msgMobile"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Access Type</label>
								<div class="controls">
									<select name="selRole" id="selRole">
										<option>--select--</option>
										<?php
										$qRole = $db->select('role','*',array(),'WHERE page IS NOT NULL ORDER BY role_description');
										while($rRole = $db->fetch_array($qRole)):
										?>
										<option value="<?php echo $rRole['role_id']?>" <?php if($rRole['role_id']==$role)echo 'selected="selected"';?> ><?php echo $rRole['role_description'];?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" style="font-weight:bold;" id="msgPosition" name="msgPosition"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group warning">
								<label class="control-label" for="inputSuccess">Status</label>
								<div class="controls">
									<select name="selStat" id="selStat">
										<option value="active" <?php if($status=="active")echo 'selected="selected"';?> >Active</option>
										<option value="inactive" <?php if($status=="inactive")echo 'selected="selected"';?> >Inactive</option>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="controls"><input type="submit" name="btnSave" id="btnSave" value=" Save " class="btn btn-small btn-primary"></div>
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
<script>
$(document).ready(function(){
	$('#btnCreate').click(function(){
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
		else if( $('#selRole').val() == "" ){
			$('#msgPosition').html('Access Type required!');
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
<!-- end: JavaScript-->
</body>
</html>