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
$did = (isset($_REQUEST['did']) && !empty($_REQUEST['did']) ) ? functions::decode($_REQUEST['did']) : 0;

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
	<title>Supplier Add</title>
	 <!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<style>
		/* Strongly emphasized form controls to ensure they are instantly noticeable */
		.form-control-notice {
			background-color: #f8fafc !important;
			border: 2px solid #718096 !important;
			border-radius: 4px !important;
			color: #1a202c !important;
			font-weight: 500;
			box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
			padding: 6px 10px !important;
			height: auto !important;
			min-height: 30px;
		}
		.form-control-notice:focus {
			background-color: #ffffff !important;
			border-color: #3182ce !important;
			box-shadow: inset 0 1px 2px rgba(0,0,0,0.05), 0 0 6px rgba(49, 130, 206, 0.5) !important;
		}
		select.form-control-notice {
			height: 34px !important;
		}

		/* Modern Confirmation Modal Styling */
		.custom-modal-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			z-index: 9999;
			justify-content: center;
			align-items: center;
			animation: fadeIn 0.2s ease-in-out;
		}
		.custom-modal-card {
			background: #ffffff;
			width: 100%;
			max-width: 400px;
			border-radius: 8px;
			box-shadow: 0 10px 25px rgba(0,0,0,0.15);
			overflow: hidden;
			animation: scaleUp 0.2s ease-in-out;
		}
		.custom-modal-header {
			padding: 16px 20px;
			background: #f7fafc;
			border-bottom: 1px solid #e2e8f0;
			font-weight: bold;
			font-size: 15px;
			color: #2d3748;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.custom-modal-body {
			padding: 20px;
			font-size: 14px;
			color: #4a5568;
			line-height: 1.5;
		}
		.custom-modal-footer {
			padding: 12px 20px;
			background: #f7fafc;
			border-top: 1px solid #e2e8f0;
			display: flex;
			justify-content: flex-end;
			gap: 10px;
		}
		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}
		@keyframes scaleUp {
			from { transform: scale(0.95); }
			to { transform: scale(1); }
		}
	</style>
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
<?php
	$txtName = ( isset($_POST['txtName']) && !empty($_POST['txtName']) ) ? trim($_POST['txtName']) : '';
	$txtAddress = ( isset($_POST['txtAddress']) && !empty($_POST['txtAddress']) ) ? trim($_POST['txtAddress']) : '';
	$txtPhone1 = ( isset($_POST['txtPhone1']) && !empty($_POST['txtPhone1']) ) ? trim($_POST['txtPhone1']) : '';
	$txtPhone2 = ( isset($_POST['txtPhone2']) && !empty($_POST['txtPhone2']) ) ? trim($_POST['txtPhone2']) : '';
	$txtCell1 = ( isset($_POST['txtCell1']) && !empty($_POST['txtCell1']) ) ? trim($_POST['txtCell1']) : '';
	$txtCell2 = ( isset($_POST['txtCell2']) && !empty($_POST['txtCell2']) ) ? trim($_POST['txtCell2']) : '';
	$txBank1 = ( isset($_POST['txBank1']) && !empty($_POST['txBank1']) ) ? trim($_POST['txBank1']) : '';
	$txBank2 = ( isset($_POST['txBank2']) && !empty($_POST['txBank2']) ) ? trim($_POST['txBank2']) : '';
	$txBank3 = ( isset($_POST['txBank3']) && !empty($_POST['txBank3']) ) ? trim($_POST['txBank3']) : '';
	$txtConPerson = ( isset($_POST['txtConPerson']) && !empty($_POST['txtConPerson']) ) ? trim($_POST['txtConPerson']) : '';
	$tin = ( isset($_POST['txTIN']) && !empty($_POST['txTIN']) ) ? trim($_POST['txTIN']) : '';
	$email_add = ( isset($_POST['txEmail']) && !empty($_POST['txEmail']) ) ? trim($_POST['txEmail']) : '';
	$contact_designation = ( isset($_POST['txtConPersonDesig']) && !empty($_POST['txtConPersonDesig']) ) ? trim($_POST['txtConPersonDesig']) : '';
	$business_type = ( isset($_POST['txBusType']) && !empty($_POST['txBusType']) ) ? trim($_POST['txBusType']) : '';
	$year_established = ( isset($_POST['txYrEst']) && !empty($_POST['txYrEst']) ) ? trim($_POST['txYrEst']) : '';
	$payment_term = ( isset($_POST['txPayTerm']) && !empty($_POST['txPayTerm']) ) ? trim($_POST['txPayTerm']) : '';
	$selAccredited = ( isset($_POST['selAccredited']) && !empty($_POST['selAccredited']) ) ? trim($_POST['selAccredited']) : '';
	$selEvaluated = ( isset($_POST['selEvaluated']) && !empty($_POST['selEvaluated']) ) ? trim($_POST['selEvaluated']) : '';
	$selVATStat = ( isset($_POST['selVATStat']) && !empty($_POST['selVATStat']) ) ? trim($_POST['selVATStat']) : '';

if( isset($_POST['btnSave']) ){
	if( $txtName ){
		if( $db->getValue('supplier','count(name)',array('name'=>$txtName))==0 ){ #if there's changes on supplier's name, check if it's unique
			$insertID = $db->insert('supplier',array('supplierID'=>0,'name'=>$txtName,'address'=>$txtAddress,'contactPhoneNo'=>$txtPhone1,'contactPhoneNo2'=>$txtPhone2,'contactCellNo'=>$txtCell1,'contactCellNo2'=>$txtCell2,'contactPerson'=>$txtConPerson,'bank1'=>$txBank1,'bank2'=>$txBank2,'bank3'=>$txBank3,'tin'=>$tin,'payment_term'=>$payment_term,'business_type'=>$business_type,'email_add'=>$email_add,'contact_designation'=>$contact_designation,'year_established'=>$year_established,'accredited'=>$selAccredited,'evaluated'=>$selEvaluated,'vat'=>$selVATStat));
			if($insertID){
				$_SESSION['notif_id_list']=$insertID;
				$_SESSION['notif_success']="New Supplier Added!";
			}
			functions::sendTo('supplier_add.php');
			die();
		}
		else
		functions::say('Supplier name already existed!');
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Supplier / Payee Add Form</h2>
		</div>
		<div class="box-content" style="background: #fdfdfd; padding: 20px;">
			<form id="supplierForm" method="post" style="margin: 0;">
				
				<div style="width: 100%; box-sizing: border-box;">
					<!-- Main Profile Header Banner (Editable Name & Meta) -->
					<div style="background: #fff; border: 1px solid #cbd5e0; border-radius: 6px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-sizing: border-box; width: 100%;">
						<div style="flex: 1; min-width: 280px;">
							<label style="font-size: 11px; font-weight: bold; color: #4a5568; text-transform: uppercase; margin-bottom: 3px;">Supplier / Payee Name</label>
							<input type="text" name="txtName" id="txtName" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txtName); ?>" style="margin-bottom: 10px; font-weight: bold; font-size: 16px;" required>
							
							<div style="font-size: 13px; color: #666; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
								<span style="flex: 2; min-width: 200px;">
									<strong style="display: block; font-size: 11px; margin-bottom: 2px; color: #4a5568;">Business Type:</strong>
									<input type="text" class="span12 typeahead form-control-notice" name="txBusType" id="txBusType" value="<?php echo htmlspecialchars($business_type); ?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesBusiness;?>]'>
								</span>
								<span style="flex: 0.8; min-width: 90px;">
									<strong style="display: block; font-size: 11px; margin-bottom: 2px; color: #4a5568;">Year Established:</strong>
									<input type="text" name="txYrEst" id="txYrEst" class="span12 form-control-notice" value="<?php echo htmlspecialchars($year_established); ?>" onkeypress="return checkinput(this, event);">
								</span>
								<span style="flex: 0.8; min-width: 90px;">
									<strong style="display: block; font-size: 11px; margin-bottom: 2px; color: #4a5568;">VAT Status:</strong>
									<select name="selVATStat" id="selVATStat" class="span12 form-control-notice">
										<option value="non-vatable" <?php if($selVATStat=='non-vatable')echo 'selected="selected"';?>>Non-Vatable</option>
										<option value="vatable" <?php if($selVATStat=='vatable')echo 'selected="selected"';?>>Vatable</option>
									</select>
								</span>
							</div>
						</div>
						<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
							<div>
								<label style="font-size: 11px; font-weight: bold; color: #4a5568; text-transform: uppercase; margin-bottom: 3px;">Accreditation</label>
								<select name="selAccredited" id="selAccredited" class="span12 form-control-notice">
									<option value="">--select--</option>
									<option value="Accredited" <?php if($selAccredited=='Accredited')echo 'selected="selected"';?>>Accredited</option>
									<option value="Pre-Accredited" <?php if($selAccredited=="Pre-Accredited")echo 'selected="selected"';?>>Pre-Accredited</option>
									<option value="Not Accredited" <?php if($selAccredited=="Not Accredited")echo 'selected="selected"';?>>Not Accredited</option>
								</select>
							</div>
							<div>
								<label style="font-size: 11px; font-weight: bold; color: #4a5568; text-transform: uppercase; margin-bottom: 3px;">Evaluation</label>
								<select name="selEvaluated" id="selEvaluated" class="span12 form-control-notice">
									<option value="">--select--</option>
									<option value="Evaluated" <?php if($selEvaluated=='Evaluated')echo 'selected="selected"';?>>Evaluated</option>
									<option value="Passed" <?php if($selEvaluated=='Passed')echo 'selected="selected"';?>>Passed</option>
									<option value="Failed" <?php if($selEvaluated=='Failed')echo 'selected="selected"';?>>Failed</option>
									<option value="N/A" <?php if($selEvaluated=='N/A')echo 'selected="selected"';?>>N/A</option>
								</select>
							</div>
						</div>
					</div>

					<!-- Two-Column Grid Layout for Details Editing -->
					<div style="display: flex; gap: 20px; flex-wrap: wrap; box-sizing: border-box; width: 100%; margin: 0;">
						
						<!-- Left Column: Contact & Location Info -->
						<div style="background: #fff; border: 1px solid #cbd5e0; border-radius: 6px; padding: 20px; box-sizing: border-box; box-shadow: 0 1px 3px rgba(0,0,0,0.05); flex: 1; min-width: 300px;">
							<h4 style="border-bottom: 2px solid #edf2f7; padding-bottom: 8px; margin-top: 0; color: #2d3748; font-size: 14px; text-transform: uppercase;"><i class="halflings-icon map-marker"></i> Contact & Location Information</h4>
							
							<div style="margin-bottom: 12px; font-size: 13px;">
								<strong style="color: #4a5568; display: block; margin-bottom: 2px;">Address:</strong>
								<input type="text" name="txtAddress" id="txtAddress" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txtAddress); ?>">
							</div>

							<div style="margin-bottom: 12px; font-size: 13px;">
								<strong style="color: #4a5568; display: block; margin-bottom: 2px;">Contact Person & Designation:</strong>
								<div style="display: flex; gap: 10px;">
									<input type="text" name="txtConPerson" id="txtConPerson" class="span6 form-control-notice" value="<?php echo htmlspecialchars($txtConPerson); ?>" placeholder="Contact Person">
									<input type="text" class="span6 typeahead form-control-notice" name="txtConPersonDesig" id="txtConPersonDesig" value="<?php echo htmlspecialchars($contact_designation); ?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCD;?>]' placeholder="Designation">
								</div>
							</div>

							<div style="margin-bottom: 12px; font-size: 13px;">
								<strong style="color: #4a5568; display: block; margin-bottom: 2px;">Email Address:</strong>
								<input type="text" name="txEmail" id="txEmail" class="span12 form-control-notice" value="<?php echo htmlspecialchars($email_add); ?>">
							</div>

							<div style="margin-bottom: 5px; font-size: 13px;">
								<strong style="color: #4a5568; display: block; margin-bottom: 6px;">Phone & Mobile Numbers:</strong>
								<div style="display: flex; flex-direction: column; gap: 8px;">
									<input type="text" name="txtPhone1" id="txtPhone1" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txtPhone1); ?>" placeholder="Phone Number 1">
									<input type="text" name="txtPhone2" id="txtPhone2" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txtPhone2); ?>" placeholder="Phone Number 2">
									<input type="text" name="txtCell1" id="txtCell1" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txtCell1); ?>" placeholder="Cell Number 1">
									<input type="text" name="txtCell2" id="txtCell2" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txtCell2); ?>" placeholder="Cell Number 2">
								</div>
							</div>
						</div>

						<!-- Right Column: Financial & Banking Info -->
						<div style="background: #fff; border: 1px solid #cbd5e0; border-radius: 6px; padding: 20px; box-sizing: border-box; box-shadow: 0 1px 3px rgba(0,0,0,0.05); flex: 1; min-width: 300px;">
							<h4 style="border-bottom: 2px solid #edf2f7; padding-bottom: 8px; margin-top: 0; color: #2d3748; font-size: 14px; text-transform: uppercase;"><i class="halflings-icon credit-card"></i> Financial & Banking Details</h4>
							
							<div style="margin-bottom: 12px; font-size: 13px;">
								<strong style="color: #4a5568; display: block; margin-bottom: 2px;">Tax Identification Number (TIN):</strong>
								<input type="text" name="txTIN" id="txTIN" class="span12 form-control-notice" value="<?php echo htmlspecialchars($tin); ?>">
							</div>

							<div style="margin-bottom: 12px; font-size: 13px;">
								<strong style="color: #4a5568; display: block; margin-bottom: 2px;">Payment Terms:</strong>
								<input type="text" name="txPayTerm" id="txPayTerm" class="span12 form-control-notice" value="<?php echo htmlspecialchars($payment_term); ?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPayment;?>]'>
							</div>

							<div style="margin-bottom: 5px; font-size: 13px;">
								<strong style="color: #4a5568; display: block; margin-bottom: 6px;">Registered Bank Accounts:</strong>
								<div style="display: flex; flex-direction: column; gap: 8px;">
									<input type="text" name="txBank1" id="txBank1" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txBank1); ?>" placeholder="Bank Account 1">
									<input type="text" name="txBank2" id="txBank2" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txBank2); ?>" placeholder="Bank Account 2">
									<input type="text" name="txBank3" id="txBank3" class="span12 form-control-notice" value="<?php echo htmlspecialchars($txBank3); ?>" placeholder="Bank Account 3">
								</div>
							</div>
						</div>

					</div>
				</div>

				<!-- Save Action Toolbar -->
				<div style="margin-top: 25px; text-align: center; display: flex; gap: 10px; justify-content: center;">
					<button type="button" id="openConfirmModal" class="btn btn-primary">
						<i class="halflings-icon white ok"></i> ADD
					</button>
					<a href="supplier_add.php" class="btn btn-default">
						<i class="halflings-icon remove"></i> Cancel
					</a>
				</div>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->

<!-- Modern Confirmation Modal Box -->
<div id="confirmModal" class="custom-modal-overlay">
	<div class="custom-modal-card">
		<div class="custom-modal-header">
			<i class="halflings-icon question-sign" style="color: #3182ce;"></i> Confirm Action
		</div>
		<div class="custom-modal-body">
			Do you want to save this information?
		</div>
		<div class="custom-modal-footer">
			<button type="button" id="cancelModalBtn" class="btn btn-default" style="padding: 4px 12px;">Cancel</button>
			<button type="button" id="confirmSaveBtn" class="btn btn-primary" style="padding: 4px 12px;">Yes, Save</button>
		</div>
	</div>
</div>

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
	$(document).ready(function() {
		// Show modal when ADD is clicked
		$('#openConfirmModal').on('click', function(e) {
			e.preventDefault();
			var form = document.getElementById('supplierForm');
			if (form.checkValidity()) {
				$('#confirmModal').css('display', 'flex');
			} else {
				form.reportValidity();
			}
		});

		// Close modal on Cancel
		$('#cancelModalBtn').on('click', function() {
			$('#confirmModal').hide();
		});

		// Submit form when confirmed
		$('#confirmSaveBtn').on('click', function() {
			$('<input>').attr({
				type: 'hidden',
				name: 'btnSave',
				value: '1'
			}).appendTo('#supplierForm');
			
			$('#supplierForm').submit();
		});

		// Close modal if clicking outside the card
		$('#confirmModal').on('click', function(e) {
			if (e.target === this) {
				$(this).hide();
			}
		});
	});
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<!-- end: JavaScript-->
</body>
</html>