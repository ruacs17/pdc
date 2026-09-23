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

$iel_id = (isset($_REQUEST['iel_id']) && !empty($_REQUEST['iel_id']) ) ? functions::decode($_REQUEST['iel_id']) : 0;
$_SESSION['notif_id_list']=$iel_id;
$proj_id='';$mon='';$day='';$year='';$discount='';$brand='';$address='';$chargedBy='';$checkedBy='';$deliveredBy='';$approvedBy='';$checked_by='';$delivered_by='';$approved_by='';$terms='';$is_paid=''; $payee='';
if( $iel_id ){
	$q = $db->select('inhouse_equip_leasing','*',array('iel_id'=>$iel_id));
	$r = $db->fetch_array($q);
	$address = $r['address'];
	$checkedBy = $r['checkedBy'];
	$chargedBy = $r['chargedBy'];
	$approvedBy = $r['approvedBy'];
	$checked_by = $r['checked_by'];
	$charged_by = $r['charged_by'];
	$delivered_by = $r['delivered_by'];
	$approved_by = $r['approved_by'];
	$proj_id = $r['proj_id'];
	$txDate = $r['iel_date'];
	$terms = $r['terms'];
	$is_paid = $r['is_paid'];
	$payee = $r['payee'];

	$xdate = explode('-',$txDate);
	if(count($xdate)==3){
		$mon = $xdate[1];
		$day = $xdate[2];
		$year = $xdate[0];
	}
}

if( isset($_POST['btnSave']) ){
	$selProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : NULL;

	$txChargedBy = ( isset($_POST['txChargedBy']) && !empty($_POST['txChargedBy']) ) ? strtoupper(trim($_POST['txChargedBy'])) : NULL;
	$txCheckedBy = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? strtoupper(trim($_POST['txCheckedBy'])) : NULL;
	$txDeliveredBy = ( isset($_POST['txDeliveredBy']) && !empty($_POST['txDeliveredBy']) ) ? strtoupper(trim($_POST['txDeliveredBy'])) : NULL;
	$txApprovedBy = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? strtoupper(trim($_POST['txApprovedBy'])) : NULL;
	$txAddress = ( isset($_POST['txAddress']) && !empty($_POST['txAddress']) ) ? trim($_POST['txAddress']) : '';
	$txTerms = ( isset($_POST['txTerms']) && !empty($_POST['txTerms']) ) ? $_POST['txTerms'] : '';  
	$txPayment = ( isset($_POST['txPayment']) && !empty($_POST['txPayment']) ) ? $_POST['txPayment'] : '';
	
	$txDate = ( isset($_POST['txChargeDate']) && !empty($_POST['txChargeDate']) ) ? $_POST['txChargeDate'] : NULL;
	$txDate = (functions::valid_date($txDate)) ? $txDate : NULL;

	$rdoCharge = ( isset($_POST['rdoCharge']) && !empty($_POST['rdoCharge']) ) ? trim($_POST['rdoCharge']) : '';
	$txPayee = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? trim($_POST['txPayee']) : NULL;

	if($rdoCharge==1)
		$txPayee=NULL;
	else
		$selProj = NULL;

	if( $txDate && $txPayment && ($selProj || $txPayee) ){
		$arr = array('iel_date'=>$txDate,'prepared_by'=>$user_id,'payee'=>$txPayee,'proj_id'=>$selProj,'payee'=>'','address'=>$txAddress,'checkedBy'=>$txCheckedBy,'chargedBy'=>$txChargedBy,'approvedBy'=>$txApprovedBy,'terms'=>$txTerms,'is_paid'=>$txPayment);
		$db->update('inhouse_equip_leasing',$arr,array('iel_id'=>$iel_id));
		$_SESSION['notif_success']='Changes saved!';
	}
	else{
		functions::say('Please fill up the form properly!');
	}
	functions::sendTo(functions::pageName().'?iel_id='.functions::encode($iel_id));
	die();
}

$qPayee = $db->query('SELECT DISTINCT payee FROM inhouse_equip_leasing');
$namesPayee='';
while($rPayee=$db->fetch_array($qPayee)):
	$string = preg_replace("/'/",'"',$rPayee['payee']);
	$namesPayee .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPayee .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Leasing Charge Edit</title>
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
	<script src="../js/inputInt.js"></script>
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
	<style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Equipment Leasing Edit</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="70%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="35%" align="right" scope="row">&nbsp;</th>
							<td width="2%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
							<td width="13%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Charge To</th>
							<td>&nbsp;</td>
							<td style="padding: 25px 0px 4px 0px;">
								<table border="0" width="99%">
									<tr>
										<td style="padding: 0px 5px 4px 4px"><input type="radio" name="rdoCharge" id="rdoCharge1" value="1" onclick="document.getElementById('txPayee').disabled=true;"></td>
										<td style="padding: 10px 5px 4px 4px">
											<select name="selProj" id="selProj" data-rel="chosen" style="width:700px;">
												<option value="">--select--</option>
												<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
												while($rProj = $db->fetch_array($qProj)):
												?>
												<option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ($rProj['proj_name']);?></option>
												<?php endwhile;?>
											</select>
										</td>
									<tr>
										<td style="padding: 0px 5px 10px 4px"><input type="radio"name="rdoCharge" id="rdoCharge2" value="2" onclick="document.getElementById('txPayee').disabled=false;document.getElementById('selProj').value='';"></td>
										<td style="padding: 10px 5px 4px 4px"><input type="text" name="txPayee" id="txPayee" style="width:680px;" value="<?php echo $payee?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPayee;?>]'/></td>
									</tr>
								</table>
								<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Address</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txAddress" id="txAddress" style="width:300px;" value="<?php echo $address?>" /></td>
						</tr>
						<tr>
							<th align="right" scope="row">Charging Date</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<input type="text" style="width: 80px;" name="txChargeDate" id="txChargeDate" value="<?php echo $txDate ?>" required>
								<span class="help-inline warning" style="font-weight:bold;" id="msgTxdate" name="msgTxdate"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Checked By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<select name="txCheckedBy" id="txCheckedBy" data-rel="chosen" style="width:300px;">
									<option value="">--select--</option>
									<?php
									$qEU = $db->select('equip_user','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['eu_id']?>" <?php if($checkedBy==$rEU['eu_id']){echo 'selected="selected"';}?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Charged By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<select name="txChargedBy" id="txChargedBy" data-rel="chosen" style="width:300px;">
									<option value="">--select--</option>
									<?php
									$qEU = $db->select('equip_user','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['eu_id']?>" <?php if($chargedBy==$rEU['eu_id']){echo 'selected="selected"';}?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Approved By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:300px;">
									<option value="">--select--</option>
									<?php
									$qEU = $db->select('equip_user','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['eu_id']?>" <?php if($approvedBy==$rEU['eu_id']){echo 'selected="selected"';}?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
						<tr>
							<th>Terms and Conditions</th>
							<td>&nbsp;</td>
							<td><br><textarea class="cleditor" id="txTerms" name="txTerms" rows="2"><?php echo $terms;?></textarea></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<th align="right" scope="row">Payment Status</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<select name="txPayment" id="txPayment">
									<option value="">--select--</option>
									<option value="2" <?php if($is_paid=='2')echo 'selected="selected"';?>>Paid</option>
									<option value="1" <?php if($is_paid=='1')echo 'selected="selected"';?>>Unpaid</option>
								</select>
								<span class="help-inline warning" id="msgTxPayment" style="font-weight:bold;" name="msgTxPayment"></span>
							</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td class="tdSpace" colspan="2" align="left"><input type="submit" name="btnSave" id="btnSave" value="Update Leasing" class="btn btn-primary btn-small"></td>
						</tr>
					</table>
				</form>
			</div>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
$(document).ready(function(){
	var res = false;
	<?php echo ($payee) ? "$('#rdoCharge2').attr('checked','checked');" : "$('#txPayee').prop('disabled',true);";?>
	<?php echo ($proj_id) ? "$('#rdoCharge1').attr('checked','checked');" : "";?>
	$('#btnSave').click(function(){
		$('#msgProject').html("");
		$('#msgTxdate').html("");
		$('#msgTxPayment').html("");

		if( $('#rdoCharge1').is(':checked')==false && $('#rdoCharge2').is(':checked')==false ){
			$('#msgProject').html("Project/Payee Required!");
			res=false;
		}
		else if( $('#rdoCharge1').is(':checked') && $('#selProj').val()=="" ){
			$('#msgProject').html("Project Required!");
			$('#selProj').focus();
			res=false;
		}
		else if( $('#rdoCharge2').is(':checked') && $('#txPayee').val()=="" ){
			$('#msgProject').html("Payee Required!");
			$('#txPayee').focus();
			res=false;
		}
		else if( $('#txYear').val()=="" || $('#txMon').val()=="" || $('#txdDay').val()=="" ){
			$('#msgTxdate').html("Date Required!");
			res=false;
		}
		else if( $('#txPayment').val()=="" ){
			$('#msgTxPayment').html("Payment Status Required!");
			res=false;
		}
		else{
			if(confirm('Do you want to submit this information?'))
				res=true;
		}
		return res;
	});
	$('#txChargeDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+1 ?>',
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