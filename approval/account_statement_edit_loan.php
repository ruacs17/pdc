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
$did = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$_SESSION['notif_id_list']=$did;

$txTran_name=''; $txTran_desc=''; $txTran_amount=0;$mon='';$day='';$year='';$sType='';$txTran_AccntType='';$confirmation='';$tag_id='';$readonly='';$readOnlyProjIncome='';$project_income='';
$q = $db->select('account_statement','*',array('as_id'=>$did));
$r = $db->fetch_array($q);
$txTran_AccntType = $r['account_type'];
$txTran_name = $r['transaction'];
$txTran_desc = $r['description'];
$txTran_amount = $r['as_amount'];
$txTran_type = $r['as_type'];
$confirmation = $r['confirmned'];
$tag_id = ($r['tag_id']) ? $r['tag_id'] : '';
$project_income = ($r['project_income']) ? $r['project_income'] : '';
$vdate = $r['as_date'];
$xdate = explode('-',$vdate);
if(count($xdate)==3){
	$mon = $xdate[1];
	$day = $xdate[2];
	$year = $xdate[0];
}
if($tag_id || $project_income){
	$readonly = 'readonly';
}
if( $project_income ){
	$readOnlyProjIncome='readonly';
	functions::sendTo("account_statement_billing_edit.php?as_id=".functions::encode($did));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Account Statement Loan Adding</title>
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
	$did = ( isset($_POST['did']) && !empty($_POST['did']) ) ? functions::decode($_POST['did']) : '';
	$txTran_AccntType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	$txTran_type = ( isset($_POST['asType']) && !empty($_POST['asType']) ) ? $_POST['asType'] : '';
	$txTran_name = ( isset($_POST['txTran_name']) && !empty($_POST['txTran_name']) ) ? $_POST['txTran_name'] : '';
	$txTran_desc = ( isset($_POST['txTran_desc']) && !empty($_POST['txTran_desc']) ) ? $_POST['txTran_desc'] : '';
	$txTran_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;
	$asConf = ( isset($_POST['asConf']) && !empty($_POST['asConf']) ) ? $_POST['asConf'] : 1;

	$mon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$day = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$txBdate = (functions::valid_date($year.'-'.$mon.'-'.$day)) ? $year.'-'.$mon.'-'.$day : NULL;

	if( empty($txTran_name) ){
		$_SESSION['notif_warning']='Transaction Name Required!';
	}
	else if( empty($txTran_type) ){
		$_SESSION['notif_warning']='Transaction Type Required!';
	}
	else if( empty($txTran_AccntType) ){
		$_SESSION['notif_warning']='Account Type Required!';
	}
	else if( empty($txBdate) ){
		$_SESSION['notif_warning']='Please input valid date!';
	}
	else if( $txTran_name && $txTran_amount && $txBdate && $txTran_type && $txTran_AccntType && $did){
		$db->update('account_statement',array('as_type'=>$txTran_type,'transaction'=>$txTran_name,'description'=>$txTran_desc,'as_date'=>$txBdate,'as_amount'=>$txTran_amount,'account_type'=>$txTran_AccntType,'confirmned'=>$asConf),array('as_id'=>$did)); 
		$_SESSION['notif_success']="Statement Updated!";
		functions::sendTo(functions::pageName().'?eid='.functions::encode($did));
		die();
	}
	else{
		$_SESSION['notif_warning']="Please fill up the form properly!";
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Loan Update Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post"> 
				<input type="hidden" name="did" id="did" value="<?php echo functions::encode($did);?>">          
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30">Account Type</td>
						<td width="43%">
							<select name="txVoType" id="txVoType">
								<option value="">--Account Type--</option>
								<?php 
								$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
								while($rVtype = $db->fetch_array($qVtype)):
								?>
								<option value="<?php echo $rVtype['vt_id']?>" <?php if($txTran_AccntType==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
								<?php endwhile;?>
							</select>
							<span class="help-inline warning" name="msgVoType" id="msgVoType" style="font-weight:bold;"></span>
						</td>
					</tr>
					<tr>
						<td width="17%" height="30">Transaction Type</td>
						<td width="43%">
							<select name="asType" id="asType">
								<option value="credit" <?php if($txTran_type=='credit')echo 'selected="selected"';?>>CREDIT</option>
							</select>
							<span class="help-inline warning" name="msgasType" id="msgasType" style="font-weight:bold;"></span>
						</td>
					</tr>
					<tr>
						<td width="17%" height="30">Transaction Name</td>
						<td width="43%">
							<input type="text" name="txTran_name" id="txTran_name" class="span6" value="<?php echo $txTran_name;?>" <?php echo $readonly?>>
							<span class="help-inline warning" id="msgName" style="font-weight:bold;" name="msgName"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Description</td>
						<td>
							<input type="text" name="txTran_desc" id="txTran_desc" class="span6" value="<?php echo $txTran_desc;?>" readonly/>
							<span class="help-inline warning" id="msgDesc" style="font-weight:bold;" name="msgDesc"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Transaction Date</td>
						<td>
							<select name="bdMon" id="bdMon" style="width:80px;">
								<option value="">Month</option>
								<option value="01" <?php if($mon=='01')echo 'selected="selected"';?>>Jan</option>
								<option value="02" <?php if($mon=='02')echo 'selected="selected"';?>>Feb</option>
								<option value="03" <?php if($mon=='03')echo 'selected="selected"';?>>Mar</option>
								<option value="04" <?php if($mon=='04')echo 'selected="selected"';?>>Apr</option>
								<option value="05" <?php if($mon=='05')echo 'selected="selected"';?>>May</option>
								<option value="06" <?php if($mon=='06')echo 'selected="selected"';?>>Jun</option>
								<option value="07" <?php if($mon=='07')echo 'selected="selected"';?>>Jul</option>
								<option value="08" <?php if($mon=='08')echo 'selected="selected"';?>>Aug</option>
								<option value="09" <?php if($mon=='09')echo 'selected="selected"';?>>Sep</option>
								<option value="10" <?php if($mon=='10')echo 'selected="selected"';?>>Oct</option>
								<option value="11" <?php if($mon=='11')echo 'selected="selected"';?>>Nov</option>
								<option value="12" <?php if($mon=='12')echo 'selected="selected"';?>>Dec</option>
							</select>
							<select name="bdDay" id="bdDay" style="width:60px;">
								<option value="">Day</option>
								<?php for($i=1;$i<=31;$i++):?>
								<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($day==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
								<?php endfor;?>
							</select>
							<select name="bdYear" id="bdYear" style="width:70px;">
								<option value="">Year</option>
								<?php for($y=(date('Y')+1);$y>=2015;$y--):?>
								<option value="<?php echo $y;?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
								<?php endfor;?>
							</select>
							<span class="help-inline warning" id="msgDate" style="font-weight:bold;" name="msgDate"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Amount</td>
						<td>
							<input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo functions::formatMoney($txTran_amount);?>" onkeyup="FormatCurrency(this);" <?php echo $readOnlyProjIncome;?> />
							<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
						</td>
					</tr>
					<tr>
						<td width="17%" height="30">Confirm Cash Return</td>
						<td width="43%">
							<select name="asConf" id="asConf">
								<option value="2" <?php if($confirmation==2)echo 'selected="selected"';?>>YES</option>
								<option value="1" <?php if($confirmation==1)echo 'selected="selected"';?>>NO</option>
							</select>
							<span class="help-inline warning" id="msgasType" style="font-weight:bold;" name="msgasType"></span>
						</td>
					</tr>
				</table>
				<div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-small btn-primary"></div>
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
	var res = false;
	$('#btnSave').click(function(){
		$('#msgVoType').html("");
		$('#msgasType').html("");
		$('#msgName').html("");
		$('#msgDesc').html("");
		$('#msgDate').html("");
		$('#msgAmount').html("");

		if( $('#txVoType').val()=="" ){
			$('#txVoType').focus();
			$('#msgVoType').html("Account Type Required!");
			res=false;
		}
		else if( $('#asType').val()=="" ){
			$('#asType').focus();
			$('#msgasType').html("Transaction Type Required!");
			res=false;
		}
		else if( $('#txTran_name').val()=="" ){
			$('#txTran_name').focus();
			$('#msgName').html("Transaction Name Required!");
			res=false;
		}
		else if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
			$('#msgDate').html("Date Required!");
			res=false;
		}
		else if( $('#txTran_amount').val()=="" ){
			$('#txTran_amount').focus();
			$('#msgAmount').html("Amount Required!");
			res=false;
		}
		else{
			if(confirm('Do you want to save changes?'))
				res = true;
		}
		return res;
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
<!-- end: JavaScript-->
</body>
</html>