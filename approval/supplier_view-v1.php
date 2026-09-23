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

$sid = (isset($_REQUEST['sid']) && !empty($_REQUEST['sid']) ) ? functions::decode($_REQUEST['sid']) : 0;
$_SESSION['notif_id_list']=$sid;
$qShow = $db->select('supplier','*',array('supplierID'=>$sid));
$rShow = $db->fetch_array($qShow);
$txtName = ( isset($rShow['name']) && !empty($rShow['name']) ) ? $rShow['name'] : '';
$txtAddress = ( isset($rShow['address']) && !empty($rShow['address']) ) ? $rShow['address'] : '';
$txtPhone1 = ( isset($rShow['contactPhoneNo']) && !empty($rShow['contactPhoneNo']) ) ? $rShow['contactPhoneNo'] : '';
$txtPhone2 = ( isset($rShow['contactPhoneNo2']) && !empty($rShow['contactPhoneNo2']) ) ? $rShow['contactPhoneNo2'] : '';
$txtCell1 = ( isset($rShow['contactCellNo']) && !empty($rShow['contactCellNo']) ) ? $rShow['contactCellNo'] : '';
$txtCell2 = ( isset($rShow['contactCellNo2']) && !empty($rShow['contactCellNo2']) ) ? $rShow['contactCellNo2'] : '';
$txBank1 = ( isset($rShow['bank1']) && !empty($rShow['bank1']) ) ? $rShow['bank1'] : '';
$txBank2 = ( isset($rShow['bank2']) && !empty($rShow['bank2']) ) ? $rShow['bank2'] : '';
$txBank3 = ( isset($rShow['bank3']) && !empty($rShow['bank3']) ) ? $rShow['bank3'] : '';
$txtConPerson = ( isset($rShow['contactPerson']) && !empty($rShow['contactPerson']) ) ? $rShow['contactPerson'] : '';
$txTIN = ( isset($rShow['tin']) && !empty($rShow['tin']) ) ? $rShow['tin'] : '';
$txEmail = ( isset($rShow['email_add']) && !empty($rShow['email_add']) ) ? $rShow['email_add'] : '';
$txtConPersonDesig = ( isset($rShow['contact_designation']) && !empty($rShow['contact_designation']) ) ? $rShow['contact_designation'] : '';
$txBusType = ( isset($rShow['business_type']) && !empty($rShow['business_type']) ) ? $rShow['business_type'] : '';
$txYrEst = ( isset($rShow['year_established']) && !empty($rShow['year_established']) ) ? $rShow['year_established'] : '';
$txPayTerm = ( isset($rShow['payment_term']) && !empty($rShow['payment_term']) ) ? $rShow['payment_term'] : '';
$selAccredited = ( isset($rShow['accredited']) && !empty($rShow['accredited']) ) ? $rShow['accredited'] : '';
$selEvaluated = ( isset($rShow['evaluated']) && !empty($rShow['evaluated']) ) ? $rShow['evaluated'] : '';
$selVATStat = $rShow['vat'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Supplier View</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Supplier / Payyee Details</h2>
			<div class="box-icon">
				<a id="edit" style="cursor:pointer;" title="Modify this Supplier" data-rel="tooltip" href="supplier_edit.php?sid=<?php echo functions::encode($sid);?>"><h2>Edit</h2><i class="halflings-icon white pencil"></i></a>
			</div>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post"> 
				<input type="hidden" name="sid" id="sid" class="span6" value="<?php echo functions::encode($sid);?>">         
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"><div align="right">Supplier / Payee Name</div></td>
						<td width="43%"><input type="text" name="txtName" id="txtName" class="span6" value="<?php echo $txtName;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Address</td>
						<td><input type="text" name="txtAddress" id="txtAddress" class="span6" value="<?php echo $txtAddress;?>" readonly/></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Phone Number 1</div></td>
						<td><input type="text" name="txtPhone1" id="txtPhone1" class="span6" value="<?php echo $txtPhone1;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Phone Number 2</div></td>
						<td><input type="text" name="txtPhone2" id="txtPhone2" class="span6" value="<?php echo $txtPhone2;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Cell Number 1</div></td>
						<td><input type="text" name="txtCell1" id="txtCell1" class="span6" value="<?php echo $txtCell1;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Cell Number 2</div></td>
						<td><input type="text" name="txtCell2" id="txtCell2" class="span6" value="<?php echo $txtCell2;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Bank 1</div></td>
						<td><input type="text" name="txBank1" id="txBank1" class="span6" value="<?php echo $txBank1?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Bank 2</div></td>
						<td><input type="text" name="txBank2" id="txBank2" class="span6" value="<?php echo $txBank2?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Bank 3</div></td>
						<td><input type="text" name="txBank3" id="txBank3" class="span6" value="<?php echo $txBank3?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Tax Identification Number (TIN)</div></td>
						<td><input type="text" name="txTIN" id="txTIN" class="span6" value="<?php echo $txTIN;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">E-mail Address</div></td>
						<td><input type="text" name="txEmail" id="txEmail" class="span6" value="<?php echo $txEmail;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Person</div></td>
						<td><input type="text" name="txtConPerson" id="txtConPerson" class="span6" value="<?php echo $txtConPerson;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Person's Designation</div></td>
						<td><input type="text" class="span6 typeahead" name="txtConPersonDesig" id="txtConPersonDesig" value="<?php echo $txtConPersonDesig;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Type of Business</div></td>
						<td><input type="text" class="span6 typeahead" name="txBusType" id="txBusType" value="<?php echo $txBusType;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Year Established</div></td>
						<td><input type="text" name="txYrEst" id="txYrEst" class="span6" value="<?php echo $txYrEst;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Payment Term</div></td>
						<td><input type="text" name="txPayTerm" id="txPayTerm" class="span6" value="<?php echo $txPayTerm;?>" readonly></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Accreditation Status</div></td>
						<td>
							<select name="selAccredited" id="selAccredited" disabled="disabled">
								<option value="">--select--</option>
								<option value="Accredited" <?php if($selAccredited=='Accredited')echo 'selected="selected"';?>>Accredited</option>
								<option value="Pre-Accredited" <?php if($selAccredited=="Pre-Accredited")echo 'selected="selected"';?>>Pre-Accredited</option>
								<option value="Not Accredited" <?php if($selAccredited=="Not Accredited")echo 'selected="selected"';?>>Not Accredited</option>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Evaluation Status</div></td>
						<td>
							<select name="selEvaluated" id="selEvaluated" disabled="disabled">
								<option value="">--select--</option>
								<option value="Evaluated" <?php if($selEvaluated=='Evaluated')echo 'selected="selected"';?>>Evaluated</option>
								<option value="Passed" <?php if($selEvaluated=='Passed')echo 'selected="selected"';?>>Passed</option>
								<option value="Failed" <?php if($selEvaluated=='Failed')echo 'selected="selected"';?>>Failed</option>
								<option value="N/A" <?php if($selEvaluated=='N/A')echo 'selected="selected"';?>>N/A</option>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">VAT Status</div></td>
						<td>
							<select name="selVATStat" id="selVATStat" disabled="disabled">
								<option value="">--select--</option>
								<option value="vatable" <?php if($selVATStat=='vatable')echo 'selected="selected"';?>>Vatable</option>
								<option value="non-vatable" <?php if($selVATStat=='non-vatable')echo 'selected="selected"';?>>Non-Vatable</option>
							</select>
						</td>
					</tr>
				</table>
				<div align="center"><a href="supplier_edit.php?sid=<?php echo functions::encode($sid);?>" class="btn btn-primary btn-small">Edit</a></div>
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
<!-- end: JavaScript-->
</body>
</html>