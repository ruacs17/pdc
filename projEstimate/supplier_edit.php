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
if( isset($_POST['btnSave']) ){
	$sid = (isset($_POST['sid']) && !empty($_POST['sid']) ) ? functions::decode($_POST['sid']) : 0;
	$txtName = ( isset($_POST['txtName']) && !empty($_POST['txtName']) ) ? $_POST['txtName'] : '';
	$txtAddress = ( isset($_POST['txtAddress']) && !empty($_POST['txtAddress']) ) ? $_POST['txtAddress'] : '';
	$txtPhone1 = ( isset($_POST['txtPhone1']) && !empty($_POST['txtPhone1']) ) ? $_POST['txtPhone1'] : '';
	$txtPhone2 = ( isset($_POST['txtPhone2']) && !empty($_POST['txtPhone2']) ) ? $_POST['txtPhone2'] : '';
	$txtCell1 = ( isset($_POST['txtCell1']) && !empty($_POST['txtCell1']) ) ? $_POST['txtCell1'] : '';
	$txtCell2 = ( isset($_POST['txtCell2']) && !empty($_POST['txtCell2']) ) ? $_POST['txtCell2'] : '';
	$txtConPerson = ( isset($_POST['txtConPerson']) && !empty($_POST['txtConPerson']) ) ? $_POST['txtConPerson'] : '';
	$tin = ( isset($_POST['txTIN']) && !empty($_POST['txTIN']) ) ? trim($_POST['txTIN']) : '';
	$email_add = ( isset($_POST['txEmail']) && !empty($_POST['txEmail']) ) ? trim($_POST['txEmail']) : '';
	$contact_designation = ( isset($_POST['txtConPersonDesig']) && !empty($_POST['txtConPersonDesig']) ) ? trim($_POST['txtConPersonDesig']) : '';
	$business_type = ( isset($_POST['txBusType']) && !empty($_POST['txBusType']) ) ? trim($_POST['txBusType']) : '';
	$year_established = ( isset($_POST['txYrEst']) && !empty($_POST['txYrEst']) ) ? trim($_POST['txYrEst']) : '';
	$payment_term = ( isset($_POST['txPayTerm']) && !empty($_POST['txPayTerm']) ) ? trim($_POST['txPayTerm']) : '';
	$selAccredited = ( isset($_POST['selAccredited']) && !empty($_POST['selAccredited']) ) ? trim($_POST['selAccredited']) : '';
	$selEvaluated = ( isset($_POST['selEvaluated']) && !empty($_POST['selEvaluated']) ) ? trim($_POST['selEvaluated']) : '';
	$selVATStat = ( isset($_POST['selVATStat']) && !empty($_POST['selVATStat']) ) ? trim($_POST['selVATStat']) : '';
	if( $txtName ){
		$db->update('supplier',array('name'=>$txtName,'address'=>$txtAddress,'contactPhoneNo'=>$txtPhone1,'contactPhoneNo2'=>$txtPhone2,'contactCellNo'=>$txtCell1,'contactCellNo2'=>$txtCell2,'contactPerson'=>$txtConPerson,'tin'=>$tin,'payment_term'=>$payment_term,'business_type'=>$business_type,'email_add'=>$email_add,'contact_designation'=>$contact_designation,'year_established'=>$year_established,'accredited'=>$selAccredited,'evaluated'=>$selEvaluated,'vat'=>$selVATStat),array('supplierID'=>$sid));
		$_SESSION['notif_success']='Changes Saved!';
	}
	else{
		functions::say('Please fill up the form properly!');
	}
	functions::sendTo('supplier_view.php?sid='.functions::encode($sid));
	die();
}
$qShow = $db->select('supplier','*',array('supplierID'=>$sid));
$rShow = $db->fetch_array($qShow);
$txtName = ( isset($rShow['name']) && !empty($rShow['name']) ) ? $rShow['name'] : '';
$txtAddress = ( isset($rShow['address']) && !empty($rShow['address']) ) ? $rShow['address'] : '';
$txtPhone1 = ( isset($rShow['contactPhoneNo']) && !empty($rShow['contactPhoneNo']) ) ? $rShow['contactPhoneNo'] : '';
$txtPhone2 = ( isset($rShow['contactPhoneNo2']) && !empty($rShow['contactPhoneNo2']) ) ? $rShow['contactPhoneNo2'] : '';
$txtCell1 = ( isset($rShow['contactCellNo']) && !empty($rShow['contactCellNo']) ) ? $rShow['contactCellNo'] : '';
$txtCell2 = ( isset($rShow['contactCellNo2']) && !empty($rShow['contactCellNo2']) ) ? $rShow['contactCellNo2'] : '';
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
$qContactDesig = $db->query('SELECT DISTINCT contact_designation FROM supplier ORDER BY contact_designation');
$namesCD='';
while($rCD=$db->fetch_array($qContactDesig)):
	$string = preg_replace("/'/",'"',$rCD['contact_designation']);
	$namesCD .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCD .= '"--"';
$qBusType = $db->query('SELECT DISTINCT business_type FROM supplier ORDER BY business_type');
$namesBusiness='';
while($rBT=$db->fetch_array($qBusType)):
	$string = preg_replace("/'/",'"',$rBT['business_type']);
	$namesBusiness .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesBusiness .= '"--"';

$qPayment = $db->query('SELECT DISTINCT payment_term FROM supplier ORDER BY payment_term');
$namesPayment='';
while($rPT=$db->fetch_array($qPayment)):
	$string = preg_replace("/'/",'"',$rPT['payment_term']);
	$namesPayment .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPayment .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Supplier Modify</title>
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
	<script src="../js/inputInt.js"></script>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Supplier / Payee Update Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<input type="hidden" name="sid" id="sid" class="span6" value="<?php echo functions::encode($sid);?>">         
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"><div align="right">Supplier / Payee Name</div></td>
						<td width="43%"><input type="text" name="txtName" id="txtName" class="span6" value="<?php echo $txtName;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Address</td>
						<td><input type="text" name="txtAddress" id="txtAddress" class="span6" value="<?php echo $txtAddress;?>" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Phone Number 1</div></td>
						<td><input type="text" name="txtPhone1" id="txtPhone1" class="span6" value="<?php echo $txtPhone1;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Phone Number 2</div></td>
						<td><input type="text" name="txtPhone2" id="txtPhone2" class="span6" value="<?php echo $txtPhone2;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Cell Number 1</div></td>
						<td><input type="text" name="txtCell1" id="txtCell1" class="span6" value="<?php echo $txtCell1;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Cell Number 2</div></td>
						<td><input type="text" name="txtCell2" id="txtCell2" class="span6" value="<?php echo $txtCell2;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Tax Identification Number (TIN)</div></td>
						<td><input type="text" name="txTIN" id="txTIN" class="span6" value="<?php echo $txTIN;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">E-mail Address</div></td>
						<td><input type="text" name="txEmail" id="txEmail" class="span6" value="<?php echo $txEmail;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Person</div></td>
						<td><input type="text" name="txtConPerson" id="txtConPerson" class="span6" value="<?php echo $txtConPerson;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Contact Person's Designation</div></td>
						<td><input type="text" class="span6 typeahead" name="txtConPersonDesig" id="txtConPersonDesig" value="<?php echo $txtConPersonDesig;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCD;?>]'></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Type of Business</div></td>
						<td><input type="text" class="span6 typeahead" name="txBusType" id="txBusType" value="<?php echo $txBusType;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesBusiness;?>]'></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Year Established</div></td>
						<td><input type="text" name="txYrEst" id="txYrEst" class="span6" value="<?php echo $txYrEst;?>" onkeypress="return checkinput(this, event);"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Payment Term</div></td>
						<td><input type="text" name="txPayTerm" id="txPayTerm" class="span6" value="<?php echo $txPayTerm;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPayment;?>]'></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Accreditation Status</div></td>
						<td>
							<select name="selAccredited" id="selAccredited">
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
							<select name="selEvaluated" id="selEvaluated">
								<option value="">--select--</option>
								<option value="Evaluated" <?php if($selEvaluated=='Evaluated')echo 'selected="selected"';?>>Evaluated</option>
								<option value="Passed" <?php if($selEvaluated=='Passed')echo 'selected="selected"';?>>Passed</option>
								<option value="Failed" <?php if($selEvaluated=='Failed')echo 'selected="selected"';?>>Failed</option>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">VAT Status</div></td>
						<td>
							<select name="selVATStat" id="selVATStat">
								<option value="non-vatable" <?php if($selVATStat=='non-vatable')echo 'selected="selected"';?>>Non-Vatable</option>
								<option value="vatable" <?php if($selVATStat=='vatable')echo 'selected="selected"';?>>Vatable</option>
							</select>
						</td>
					</tr>
				</table>
				<div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " onClick="if(confirm('Do you want to save this information?')){return true;}else{return false;}" class="btn btn-primary btn-small"></div>
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