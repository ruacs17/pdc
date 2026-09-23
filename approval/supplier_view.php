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
		<div class="box-header" data-original-title style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
			<h2><i class="halflings-icon white user"></i><span class="break"></span>Supplier / Payee Details</h2>
		</div>
		<div class="box-content" style="background: #fdfdfd; padding: 20px;">
			
			<div style="width: 100%; box-sizing: border-box;">
				<!-- Main Profile Header Banner -->
				<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-sizing: border-box; width: 100%;">
					<div>
						<h3 style="margin: 0 0 5px 0; color: #333; font-size: 20px;"><?php echo htmlspecialchars($txtName); ?></h3>
						<div style="font-size: 13px; color: #666; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
							<span><strong>Business Type:</strong> <?php echo htmlspecialchars($txBusType ? $txBusType : 'N/A'); ?></span>
							<span><strong>Established:</strong> <?php echo htmlspecialchars($txYrEst ? $txYrEst : 'N/A'); ?></span>
							<span><strong>VAT Status:</strong> <span style="text-transform: capitalize;"><?php echo htmlspecialchars($selVATStat ? $selVATStat : 'N/A'); ?></span></span>
						</div>
					</div>
					<div style="display: flex; gap: 8px; align-items: center;">
						<?php 
							// Accreditation Badge Styling
							$badgeClass = 'label';
							if($selAccredited == 'Accredited') $badgeClass .= ' label-success';
							elseif($selAccredited == 'Pre-Accredited') $badgeClass .= ' label-warning';
							elseif($selAccredited == 'Not Accredited') $badgeClass .= ' label-important';
							echo ($selAccredited) ? '<span class="'.$badgeClass.'" style="padding: 5px 10px; font-size: 12px;">'.htmlspecialchars($selAccredited).'</span>' : '';

							// Evaluation Badge Styling & Icons
							$evalClass = 'label';
							$evalIcon = '';
							if($selEvaluated == 'Passed' || $selEvaluated == 'Pass') {
								$evalClass .= ' label-success';
								$evalIcon = '<i class="halflings-icon white ok"></i>';
							} elseif($selEvaluated == 'Evaluated') {
								$evalClass .= ' label-info';
								$evalIcon = '<i class="halflings-icon white eye-open"></i>';
							} elseif($selEvaluated == 'Failed') {
								$evalClass .= ' label-important';
								$evalIcon = '<i class="halflings-icon white remove"></i>';
							} else {
								$evalClass .= ' label-default';
								$evalIcon = '<i class="halflings-icon white minus"></i>';
							}
							echo ($selEvaluated) ? '<span class="'.$evalClass.'" style="padding: 5px 10px; font-size: 12px;" title="Evaluation Status" data-rel="tooltip">'.$evalIcon.' '.htmlspecialchars($selEvaluated).'</span>' : '';
						?>
					</div>
				</div>

				<!-- Two-Column Grid Layout for Details -->
				<div style="display: flex; gap: 20px; flex-wrap: wrap; box-sizing: border-box; width: 100%; margin: 0;">
					
					<!-- Left Column: Contact & Location Info -->
					<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; box-sizing: border-box; box-shadow: 0 1px 3px rgba(0,0,0,0.03); flex: 1; min-width: 300px;">
						<h4 style="border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; margin-top: 0; color: #444; font-size: 14px; text-transform: uppercase;"><i class="halflings-icon map-marker"></i> Contact & Location Information</h4>
						
						<div style="margin-bottom: 12px; font-size: 13px;">
							<strong style="color: #555; display: block; margin-bottom: 2px;">Address:</strong>
							<span style="color: #333;"><?php echo htmlspecialchars($txtAddress ? $txtAddress : 'No address provided'); ?></span>
						</div>

						<div style="margin-bottom: 12px; font-size: 13px;">
							<strong style="color: #555; display: block; margin-bottom: 2px;">Contact Person:</strong>
							<span style="color: #333;"><?php echo htmlspecialchars($txtConPerson ? $txtConPerson : 'N/A'); ?> <?php echo ($txtConPersonDesig) ? '<span style="color: #777;">('.htmlspecialchars($txtConPersonDesig).')</span>' : ''; ?></span>
						</div>

						<div style="margin-bottom: 12px; font-size: 13px;">
							<strong style="color: #555; display: block; margin-bottom: 2px;">Email Address:</strong>
							<span style="color: #333;"><?php echo htmlspecialchars($txEmail ? $txEmail : 'N/A'); ?></span>
						</div>

						<div style="margin-bottom: 5px; font-size: 13px;">
							<strong style="color: #555; display: block; margin-bottom: 4px;">Phone & Mobile Numbers:</strong>
							<div style="color: #333; line-height: 1.6;">
								<?php 
								$hasPhones = false;
								if($txtPhone1) { echo 'Phone 1: '.htmlspecialchars($txtPhone1).'<br>'; $hasPhones=true; }
								if($txtPhone2) { echo 'Phone 2: '.htmlspecialchars($txtPhone2).'<br>'; $hasPhones=true; }
								if($txtCell1) { echo 'Cell 1: '.htmlspecialchars($txtCell1).'<br>'; $hasPhones=true; }
								if($txtCell2) { echo 'Cell 2: '.htmlspecialchars($txtCell2).'<br>'; $hasPhones=true; }
								if(!$hasPhones) echo '<span style="color: #888;">No contact numbers listed.</span>';
								?>
							</div>
						</div>
					</div>

					<!-- Right Column: Financial & Banking Info -->
					<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; box-sizing: border-box; box-shadow: 0 1px 3px rgba(0,0,0,0.03); flex: 1; min-width: 300px;">
						<h4 style="border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; margin-top: 0; color: #444; font-size: 14px; text-transform: uppercase;"><i class="halflings-icon credit-card"></i> Financial & Banking Details</h4>
						
						<div style="margin-bottom: 12px; font-size: 13px;">
							<strong style="color: #555; display: block; margin-bottom: 2px;">Tax Identification Number (TIN):</strong>
							<span style="color: #333; font-family: monospace; font-size: 14px;"><?php echo htmlspecialchars($txTIN ? $txTIN : 'N/A'); ?></span>
						</div>

						<div style="margin-bottom: 12px; font-size: 13px;">
							<strong style="color: #555; display: block; margin-bottom: 2px;">Payment Terms:</strong>
							<span style="color: #333;"><?php echo htmlspecialchars($txPayTerm ? $txPayTerm : 'N/A'); ?></span>
						</div>

						<div style="margin-bottom: 5px; font-size: 13px;">
							<strong style="color: #555; display: block; margin-bottom: 4px;">Registered Bank Accounts:</strong>
							<div style="color: #333; line-height: 1.6;">
								<?php 
								$hasBanks = false;
								if($txBank1) { echo 'Bank 1: '.htmlspecialchars($txBank1).'<br>'; $hasBanks=true; }
								if($txBank2) { echo 'Bank 2: '.htmlspecialchars($txBank2).'<br>'; $hasBanks=true; }
								if($txBank3) { echo 'Bank 3: '.htmlspecialchars($txBank3).'<br>'; $hasBanks=true; }
								if(!$hasBanks) echo '<span style="color: #888;">No bank accounts listed.</span>';
								?>
							</div>
						</div>
					</div>

				</div>
			</div>

			<!-- Bottom Action Toolbar -->
			<div style="margin-top: 25px; text-align: center; display: flex; gap: 10px; justify-content: center;">
				<a href="supplier_edit.php?sid=<?php echo functions::encode($sid);?>" class="btn btn-warning"><i class="halflings-icon white pencil"></i> Modify Details</a>
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
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<!-- end: JavaScript-->
</body>
</html>