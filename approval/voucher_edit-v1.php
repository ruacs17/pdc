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

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$_SESSION['notif_id_list']=$vid;
$mon='';$day='';$year='';$cmon='';$cday='';$cyear='';$payee='';$cheque_id='';$cheque_date='';$preparedBy='';$checkedBy='';$approvedBy='';$txPayee='';$voType='';$remarks='';
$receivedBy='';$voNo=''; $witholding_tax=0;$witholding_vat=2; $prof_fee='';$advance_payment='';
$qVoucherInfo = $db->select('voucher','*',array('voucher_id'=>$vid));
if($db->num_rows($qVoucherInfo)){
	$voucherInfo = $db->fetch_array($qVoucherInfo);
	$txBdate = $voucherInfo['vdate'];
	$txPayee = $voucherInfo['supplierID'];
	$cheque_id = $voucherInfo['cheque_id'];
	$txCdate = $voucherInfo['cheque_date'];
	$checkedBy=$voucherInfo['checkedBy'];
	$approvedBy=$voucherInfo['approvedBy'];
	$preparedBy =$voucherInfo['preparedBy'];
	$receivedBy = $voucherInfo['receivedBy'];
	$voType = $voucherInfo['vt_id'];
	$voDest = $voucherInfo['dest_accnt'];
	$remarks = $voucherInfo['remarks'];
	$voNo=$voucherInfo['voucher_no'];
	$witholding_tax=$voucherInfo['witholding_tax'];
	$witholding_vat=$voucherInfo['witholding_vat'];
	$advance_payment=functions::formatMoney($voucherInfo['advance_payment']);
	$prof_fee = $voucherInfo['prof_fee'];
}
$qList = $db->select('voucher','DISTINCT receivedBy',array());
$namesReceivedBy='';
while($rList=$db->fetch_array($qList)):
	$namesReceivedBy .= '"'.$rList['receivedBy'].'",';
endwhile;
$namesReceivedBy .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher Edit</title>
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
	$insertID=0;
	$arrInsert = array();
	$_SESSION['notif_warning']='Changes Fail!';
	$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
	$txCheque = ( isset($_POST['txCheque']) && !empty($_POST['txCheque']) ) ? $_POST['txCheque'] : '';
	$txPrepare = ( isset($_POST['txPrepare']) && !empty($_POST['txPrepare']) ) ? $_POST['txPrepare'] : '';
	$txChecker = ( isset($_POST['txChecker']) && !empty($_POST['txChecker']) ) ? $_POST['txChecker'] : '';
	$txApprover = ( isset($_POST['txApprover']) && !empty($_POST['txApprover']) ) ? $_POST['txApprover'] : '';
	$txVoType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	$txRemark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? $_POST['txRemark'] : '';
	$txWitholding = ( isset($_POST['txWitholding']) && !empty($_POST['txWitholding']) ) ? $_POST['txWitholding'] : 0;
	$witholding_vat = ( isset($_POST['selWVat']) && !empty($_POST['selWVat']) ) ? $_POST['selWVat'] : 1;
	$txPF = ( isset($_POST['txPF']) && !empty($_POST['txPF']) ) ? $_POST['txPF'] : 0;

	$txVoNo = ( isset($_POST['txVoNo']) && !empty($_POST['txVoNo']) ) ? $_POST['txVoNo'] : '';

	$txBdate = ( isset($_POST['txVoDate']) && functions::valid_date($_POST['txVoDate']) ) ? $_POST['txVoDate'] : NULL;
	$txCdate = ( isset($_POST['txChqDate']) && functions::valid_date($_POST['txChqDate']) ) ? $_POST['txChqDate'] : NULL;
	$receiver = (isset($_POST['txReceiver'])) ? $_POST['txReceiver'] : '';

	$arrInsert = array('vdate'=>$txBdate,'voucher_no'=>$txVoNo,'supplierID'=>$txPayee,'witholding_vat'=>$witholding_vat,'witholding_tax'=>$txWitholding,'prof_fee'=>$txPF,'vt_id'=>$txVoType,'remarks'=>$txRemark,'cheque_id'=>$txCheque,'cheque_date'=>$txCdate,'receivedBy'=>$receiver);


	if( $db->getValue('users','count(user_id)',array('user_id'=>$txPrepare)) ){
		$arrInsert = array_merge($arrInsert,array('preparedBy'=>$txPrepare));
	}
	if( $db->getValue('users','count(user_id)',array('user_id'=>$txChecker)) ){
		$arrInsert = array_merge($arrInsert,array('checkedBy'=>$txChecker));
	}
	if( $db->getValue('users','count(user_id)',array('user_id'=>$txApprover)) ){
		$arrInsert = array_merge($arrInsert,array('approvedBy'=>$txApprover));
	}

	if( $txPayee && $txBdate ){
		$allowed=1;
		$oldtxVono = $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));

		if($txVoNo){
			if($txVoNo != $oldtxVono){
				if( $db->getValue('voucher','count(*)',array('voucher_no'=>$txVoNo)) )
					$allowed=0;
			}
		}
		if($allowed){
			$db->update('voucher',$arrInsert,array('voucher_id'=>$vid));
			$_SESSION['notif_success']="Changes Saved!";
			unset($_SESSION['notif_warning']);
			functions::sendTo('voucher_edit.php?vid='.functions::encode($vid));
			die();
		}
		else
			functions::say("Voucher Number already exist!");
	}
	else
		functions::say("Please fill up the form properly!");
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Update Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="60%" align="center" border="0" style="background-color:#E4E1E1">
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Voucher Type</label>
								<div class="controls">
									<select name="txVoType" id="txVoType" data-rel="chosen" style="width:300px;">
										<option value="">--select--</option>
										<?php
										$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
										while($rVtype = $db->fetch_array($qVtype)):
										?>
										<option value="<?php echo $rVtype['vt_id']?>" <?php if($voType==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgVoType" style="font-weight:bold;" name="msgVoType"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Voucher No:</label>
								<div class="controls">
									<input type="text" name="txVoNo" id="txVoNo" style="width:300px;" value="<?php echo $voNo;?>" />
									<span class="help-inline warning" id="msgVono" style="font-weight:bold;" name="msgVono"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Voucher Date</label>
								<div class="controls">
									<input type="text" style="width: 90px;" name="txVoDate" id="txVoDate" placeholder="YYYY-MM-DD" value="<?php echo $txBdate ?>">
									<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Payee</label>
								<div class="controls">
									<select name="txPayee" id="txPayee" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
										while($rSup = $db->fetch_array($qSup)):
										?>
										<option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Cheque #</label>
								<div class="controls">
									<input type="text" name="txCheque" id="txCheque" value="<?php echo $cheque_id?>" />
									<span class="help-inline warning" id="msgCheque" style="font-weight:bold;" name="msgCheque"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Cheque Date</label>
								<div class="controls">
									<input type="text" style="width: 90px;" name="txChqDate" id="txChqDate" placeholder="YYYY-MM-DD" value="<?php echo $txCdate ?>">
									<span class="help-inline warning" id="msgChequeDate" style="font-weight:bold;" name="msgChequeDate"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Prepared By</label>
								<div class="controls">
									<select name="txPrepare" id="txPrepare" data-rel="chosen" style="width:300px;">
										<option>--select--</option>
										<?php
										$qPrep = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rPrep = $db->fetch_array($qPrep)):
										?>
										<option value="<?php echo $rPrep['user_id']?>" <?php if($preparedBy==$rPrep['user_id']) echo 'selected="selected"';?> ><?php echo $rPrep['lname'].', '.$rPrep['fname']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Checker</label>
								<div class="controls">
									<select name="txChecker" id="txChecker" data-rel="chosen" style="width:300px;">
										<option>--select--</option>
										<?php
										$qCheck = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rCheck = $db->fetch_array($qCheck)):
										?>
										<option value="<?php echo $rCheck['user_id']?>" <?php if($checkedBy==$rCheck['user_id']) echo 'selected="selected"';?> ><?php echo $rCheck['lname'].', '.$rCheck['fname']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Approval</label>
								<div class="controls">
									<select name="txApprover" id="txApprover" data-rel="chosen" style="width:300px;">
										<option>--select--</option>
										<?php
										$qApprove = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rApprove = $db->fetch_array($qApprove)):
										?>
										<option value="<?php echo $rApprove['user_id']?>" <?php if($approvedBy==$rApprove['user_id']) echo 'selected="selected"';?> ><?php echo $rApprove['lname'].', '.$rApprove['fname']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Receiver</label>
								<div class="controls">
									<input type="text" class="span6 typeahead" name="txReceiver" id="txReceiver" style="width:300px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReceivedBy;?>]' value="<?php echo $receivedBy;?>">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Witholding Tax (%)</label>
								<div class="controls">
									<input type="text" style="width:80px;" name="txWitholding" id="txWitholding" value="<?php echo $witholding_tax?>" placeholder="0" onkeypress="return checkinput(this, event);">
									<select name="selWVat" id="selWVat" style="width:100px;">
										<option value="2" <?php if($witholding_vat==2)echo 'selected="selected"';?>>Non VAT</option>
										<option value="1" <?php if($witholding_vat==1)echo 'selected="selected"';?>>With VAT</option>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Professional Fee (%)</label>
								<div class="controls">
									<input type="text" style="width:80px;" name="txPF" id="txPF" value="<?php echo $prof_fee?>" placeholder="0" onkeypress="return checkinput(this, event);">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Remarks</label>
								<div class="controls">
									<input type="text" class="span6" name="txRemark" id="txRemark" value="<?php echo $remarks?>">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="controls">
								<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-small btn-primary">
								<button class="btn btn-small">Cancel</button>
							</div>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
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
<script src="../js/inputInt.js"></script>
<script src="../js/formatCurrency.js"></script>
<script>
$(document).ready(function(){
	var res = false;
	$('#btnSave').click(function(){

		$('#msgVoType').html("");
		$('#msgBdate').html("");
		$('#msgPayee').html("");
		$('#msgProject').html("");
		$('#msgPrepared').html("");
		$('#msgChecker').html("");
		$('#msgApprove').html("");
		$('#msgVono').html("");

		if( $('#txVoType').val()=="" ){
			$('#txVoType').focus();
			$('#msgVoType').html("Type Required!");
			res=false;
		}
		else if( $('#txVoNo').val()=="" ){
			$('#txVoNo').focus();
			$('#msgVono').html("Voucher Number Required!");
			res=false;
		}
		else if( $('#txVoDate').val()=="" ){
			$('#txVoDate').focus();
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#txPayee').val()=="" ){
			$('#txPayee').focus();
			$('#msgPayee').html("Supplier Required!");
			res=false;
		}
		else if( $('#txPrepare').val()=="" ){
			$('#msgPrepared').html("Required!");
			$('#txPrepare').focus();
			res=false;
		}
		else if( $('#txChecker').val()=="" ){
			$('#msgChecker').html("Required!");
			$('#txChecker').focus();
			res=false;
		}
		else if( $('#txApprover').val()=="" ){
			$('#msgApprove').html("Approval Required!");
			$('#txApprover').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to save this information?'))
				res=true;
			else
				res=false;
		}
		return res;
	});
	$('#txVoDate, #txChqDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+10 ?>'
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