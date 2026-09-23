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
	<title>Online Payment Voucher Create Form</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
$insertID=0;
$arrInsert = array();
$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : NULL;
$txCheque = ( isset($_POST['txCheque']) && !empty($_POST['txCheque']) ) ? $_POST['txCheque'] : '';
$txPrepare = ( isset($_POST['txPrepare']) && !empty($_POST['txPrepare']) ) ? $_POST['txPrepare'] : NULL;
$txChecker = ( isset($_POST['txChecker']) && !empty($_POST['txChecker']) ) ? $_POST['txChecker'] : NULL;
$txApprover = ( isset($_POST['txApprover']) && !empty($_POST['txApprover']) ) ? $_POST['txApprover'] : NULL;
$txVoType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : NULL;
$txRemark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? $_POST['txRemark'] : '';
$selProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
$txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : '';
$txBdate = ( isset($_POST['txVoDate']) && functions::valid_date($_POST['txVoDate']) ) ? $_POST['txVoDate'] : NULL;

if( isset($_POST['btnCreate']) ){
	$arrInsert = array('vt_id'=>$txVoType,'remarks'=>$txRemark,'cheque_id'=>$txCheque,);
	if($txPrepare){
		if( $db->getValue('users','count(user_id)',array('user_id'=>$txPrepare)) ){
			$arrInsert = array_merge($arrInsert,array('preparedBy'=>$txPrepare));
		}
	}
	if($txChecker){
		if( $db->getValue('users','count(user_id)',array('user_id'=>$txChecker)) ){
			$arrInsert = array_merge($arrInsert,array('checkedBy'=>$txChecker));
		}
	}
	if($txApprover){
		if( $db->getValue('users','count(user_id)',array('user_id'=>$txApprover)) ){
			$arrInsert = array_merge($arrInsert,array('approvedBy'=>$txApprover));
		}
	}

	if($txPayee && $txBdate && $txPrepare){
		$vid=0; $vp_id=0;
		$vid = $db->insert('voucher',array_merge(array('supplierID'=>$txPayee,'vdate'=>$txBdate,'cheque_date'=>$txBdate,'pay_type'=>'2'),$arrInsert));
		$VoNoID=10300;

		$available=false;
		do{
			$VoNoID++;
			if( $db->getValue('voucher','voucher_id',array(),'WHERE voucher_no LIKE "%'.$VoNoID.'" LIMIT 1') )
				$available=false;
			else
				$available=true;
		}while ( $available===false );

		$txbYear = date('Y',strtotime($txBdate));
		$txbMon = date('m',strtotime($txBdate));

		$voucher_no = $txbYear.$txbMon.$VoNoID;
		$db->update('voucher',array('voucher_no'=>$voucher_no),array('voucher_id'=>$vid));

		$particular_name = $db->getValue('item_deduction','name',array('item_id'=>$txItemCat));
		if( $vp_id = $db->getValue('voucher_particular','vp_id',array('voucher_id'=>$vid,'vp_title'=>$particular_name,'vtype'=>'cash')) ){
			;
		}
		elseif($particular_name){
			$vp_id = $db->insert('voucher_particular',array('vp_title'=>$particular_name,'voucher_id'=>$vid,'vtype'=>'cash')); 
		}

		if($vid && $vp_id){
			$_SESSION['notif_success']="New Voucher for Online Created!";
			functions::sendTo('voucher_add_online_detail.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id).'&prj='.functions::encode($selProj).'&itmCat='.functions::encode($txItemCat));
			die();
		}
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
    <div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Online Payment Voucher Create Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="60%" align="center" border="0" style="background-color:#E4E1E1">
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Charge Account</label>
								<div class="controls">
									<select name="txVoType" id="txVoType" data-rel="chosen" style="width:300px;">
										<option value="">--select--</option>
										<?php 
										$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
										while($rVtype = $db->fetch_array($qVtype)):
										?>
										<option value="<?php echo $rVtype['vt_id']?>" ><?php echo $rVtype['vt_name']?></option>
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
								<label class="control-label" for="inputSuccess">Transaction Date</label>
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
								<label class="control-label" for="inputSuccess">Charge to</label>
								<div class="controls">
									<select name="selProj" id="selProj" style="width:400px;">
										<option value="">--select--</option>
										<?php 
										$qProj = $db->select('project','*',array('project'=>0),'ORDER BY proj_name');
										while($rProj = $db->fetch_array($qProj)):
										?>
										<option value="<?php echo $rProj['proj_id']?>" ><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgtxProj" style="font-weight:bold;" name="msgtxProj"></span>
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
										<option value="<?php echo $rSup['supplierID']?>"><?php echo ($rSup['name']);?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgPayee" style="font-weight:bold;" name="msgPayee"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Charge Category</label>
								<div class="controls">
									<select name="txItemCat" id="txItemCat" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php 
										$qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
										while($rCat = $db->fetch_array($qCat)):
										?>
										<option value="<?php echo $rCat['item_id']?>" ><?php echo $rCat['name']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgtxCat" style="font-weight:bold;" name="msgtxCat"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Cheque #</label>
								<div class="controls">
									<input type="text" name="txCheque" id="txCheque" style="width:300px;" value="" />
									<span class="help-inline warning" id="msgCheque" style="font-weight:bold;" name="msgCheque"></span>
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
										<option value="">--select--</option>
										<?php
										$qPrep = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rPrep = $db->fetch_array($qPrep)):
										?>
										<option value="<?php echo $rPrep['user_id']?>" ><?php echo $rPrep['lname'].', '.$rPrep['fname']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgPrepared" style="font-weight:bold;" name="msgPrepared"></span>
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
										<option value="">--select--</option>
										<?php
										$qCheck = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rCheck = $db->fetch_array($qCheck)):
										?>
										<option value="<?php echo $rCheck['user_id']?>" ><?php echo $rCheck['lname'].', '.$rCheck['fname']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgChecker" style="font-weight:bold;" name="msgChecker"></span>
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
										<option value="">--select--</option>
										<?php
										$qApprove = $db->query('SELECT * FROM users u, role_assignment ra WHERE u.user_id=ra.user_id AND u.status="active" ORDER BY u.lname');
										while($rApprove = $db->fetch_array($qApprove)):
										?>
										<option value="<?php echo $rApprove['user_id']?>" ><?php echo $rApprove['lname'].', '.$rApprove['fname']?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgApprove" style="font-weight:bold;" name="msgAprove"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Remarks</label>
								<div class="controls">
									<input type="text" class="span6" name="txRemark" id="txRemark" value="">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="controls">
								<input type="submit" name="btnCreate" id="btnCreate" value="Create" class="btn btn-small btn-primary">
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
<script>
$(document).ready(function(){
	var res = false;
	$('#btnCreate').click(function(){
		$('#msgVoType').html("");
		$('#msgBdate').html("");
		$('#msgPayee').html("");
		$('#msgPrepared').html("");
		$('#msgtxCat').html("");
		$('#msgtxProj').html("");

		if( $('#txVoType').val()=="" ){
			$('#txVoType').focus();
			$('#msgVoType').html("Account Required!");
			res=false;
		}
		else if( $('#txVoDate').val()==""){
			$('#txVoDate').focus();
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#selProj').val()=="" ){
			$('#selProj').focus();
			$('#msgtxProj').html("Charge To Required!");
			res=false;
		}
		else if( $('#txPayee').val()=="" ){
			$('#txPayee').focus();
			$('#msgPayee').html("Payee Required!");
			res=false;
		}
		else if( $('#txItemCat').val()=="" ){
			$('#txItemCat').focus();
			$('#msgtxCat').html("Category Required!");
			res=false;
		}
		else if( $('#txPrepare').val()=="" ){
			$('#msgPrepared').html("Required!");
			$('#txPrepare').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to submit this information?')){
				$("#spinner").show();
				res=true;
			}
			else
				res=false;
		}
		return res;
	});
	$('#txVoDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+1 ?>'
	});
});
</script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>