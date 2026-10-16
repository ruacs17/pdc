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
$eat_id = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$editItem = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
$deleteItem = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eat_id));
$adjustment_name='';$adjustment_value='';$adjustment_mode='';
$att_year = $db->getValue('emp_attendance','att_year',array('eat_id'=>$eat_id));
$att_month = $db->getValue('emp_attendance','att_month',array('eat_id'=>$eat_id));
$arrPosition=array();
$pay_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pay_ready'=>1));
$fromPayroll = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? 1 : 0;
if($fromPayroll)
	$_SESSION['notif_indi_id']=$eid;
if($editItem){
	$q = $db->select('payroll_adjustment','*',array('pa_id'=>$editItem));
	while($r = $db->fetch_array($q)):
		$adjustment_name = $r['adjustment_name'];
		$adjustment_value = functions::formatMoney($r['adjustment_value'],'~');
		$adjustment_mode = $r['adjustment_mode'];
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payroll Deduction</title>
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
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->

	<style type="text/css">
		:root {
			--bg-canvas: #fcfaf8;
			--panel-bg: #ffffff;
			--border-subtle: #e7e0d8;
			--text-primary: #2c1d11;
			--text-muted: #78695c;
			
			/* Earthy Theme Palette */
			--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
			--theme-brown-primary: #7c401e;
			--theme-brown-hover: #5c2e14;
			--theme-brown-light: #f5ebe6;
			--theme-brown-accent: #b45309;
		}

		body {
			background-color: var(--bg-canvas);
			font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
			color: var(--text-primary);
			padding: 0;
			margin: 0;
			font-size: 13px;
		}

		/* Main Layout Container at 90% Width */
		.form-card {
			background: var(--panel-bg);
			border-radius: 16px;
			border: 1px solid var(--border-subtle);
			box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
			overflow: hidden;
			width: 99% !important;
			max-width: 99% !important;
			margin: 0 auto;
			box-sizing: border-box;
		}

		.form-card-header {
			background: var(--theme-brown-header);
			padding: 18px 25px;
			color: #ffffff;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.form-card-header h2 {
			margin: 0;
			font-size: 18px;
			font-weight: 700;
			color: #ffffff;
			display: flex;
			align-items: center;
			gap: 10px;
			line-height: 1;
		}

		.form-card-body {
			padding: 25px 35px;
		}

		.emp-info-badge {
			background: #faf6f2;
			border: 1px solid var(--border-subtle);
			border-radius: 8px;
			padding: 12px 18px;
			font-size: 14px;
			margin-bottom: 20px;
			display: inline-block;
		}

		/* Modern Table Styling */
		.table-wrapper {
			border-radius: 12px;
			overflow: hidden;
			border: 1px solid var(--border-subtle);
			background: #ffffff;
			margin-bottom: 20px;
			box-shadow: 0 2px 8px rgba(61, 35, 20, 0.03);
			width: 100%;
		}

		#tblist {
			width: 100%;
			margin-bottom: 0;
			border-collapse: collapse;
			table-layout: fixed;
		}

		#tblist thead th {
			background: #f4ede6 !important;
			color: var(--text-primary);
			font-size: 11px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.6px;
			padding: 12px 14px;
			border-bottom: 2px solid var(--border-subtle);
			border-top: none;
			box-sizing: border-box;
		}

		#tblist tbody td {
			padding: 11px 14px;
			vertical-align: middle;
			color: var(--text-primary);
			border-bottom: 1px solid #f2ece6;
			font-size: 13px;
			box-sizing: border-box;
		}

		#tblist tbody tr:last-child td {
			border-bottom: none;
		}

		#tblist tbody tr:hover {
			background-color: #faf5f0 !important;
		}

		.text-left { text-align: left !important; }
		.text-center { text-align: center !important; }
		.text-right { text-align: right !important; }

		.input-control {
			box-sizing: border-box !important;
			padding: 6px 12px !important;
			font-size: 13px !important;
			border-radius: 8px !important;
			border: 1px solid var(--border-subtle) !important;
			background-color: #ffffff !important;
			color: var(--text-primary) !important;
			outline: none !important;
			height: 36px !important;
			margin-bottom: 0 !important;
		}

		.input-control:focus {
			border-color: var(--theme-brown-primary) !important;
			box-shadow: 0 0 0 3px rgba(124, 64, 30, 0.12) !important;
		}

		.input-control[readonly] {
			background-color: #f1ede8 !important;
			cursor: not-allowed;
			color: var(--text-muted) !important;
		}

		.btn-save-custom {
			background: var(--theme-brown-primary);
			color: #ffffff !important;
			font-weight: 700;
			font-size: 12px;
			padding: 7px 22px;
			border-radius: 6px;
			border: none;
			cursor: pointer;
			transition: all 0.2s ease;
		}

		.btn-save-custom:hover {
			background: var(--theme-brown-hover);
		}

		/* Modern Confirmation Modal Styling */
		.custom-modal-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100vw;
			height: 100vh;
			background: rgba(15, 23, 42, 0.55);
			backdrop-filter: blur(8px);
			-webkit-backdrop-filter: blur(8px);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 99999;
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.25s ease, visibility 0.25s ease;
		}

		.custom-modal-overlay.active {
			opacity: 1;
			visibility: visible;
		}

		.custom-modal-card {
			background: #ffffff;
			width: 90%;
			max-width: 420px;
			border-radius: 20px;
			border: 1px solid #f1f5f9;
			box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
			padding: 32px 28px 28px 28px;
			text-align: center;
			transform: scale(0.9) translateY(12px);
			transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
			box-sizing: border-box;
		}

		.custom-modal-overlay.active .custom-modal-card {
			transform: scale(1) translateY(0);
		}

		.custom-modal-icon-wrapper {
			width: 56px;
			height: 56px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 20px auto;
			font-size: 22px;
			background: var(--theme-brown-light);
			color: var(--theme-brown-primary);
			box-shadow: 0 0 0 8px var(--theme-brown-light), 0 4px 12px rgba(124, 64, 30, 0.15);
		}

		.custom-modal-title {
			font-size: 19px;
			font-weight: 700;
			color: #0f172a;
			margin: 0 0 10px 0;
		}

		.custom-modal-text {
			font-size: 13.5px;
			color: #64748b;
			margin: 0 0 28px 0;
			line-height: 1.55;
		}

		.custom-modal-actions {
			display: flex;
			gap: 12px;
			justify-content: center;
		}

		.btn-modal-cancel {
			flex: 1;
			background: #ffffff;
			color: #475569;
			font-weight: 600;
			font-size: 13px;
			padding: 11px 18px;
			border-radius: 10px;
			border: 1px solid #cbd5e1;
			cursor: pointer;
		}

		.btn-modal-confirm-save {
			flex: 1;
			background: var(--theme-brown-primary);
			color: #ffffff !important;
			font-weight: 600;
			font-size: 13px;
			padding: 11px 18px;
			border-radius: 10px;
			border: none;
			cursor: pointer;
			text-decoration: none !important;
			display: inline-flex;
			align-items: center;
			justify-content: center;
		}
	</style>

<?php
if( isset($_POST['btnSave']) && !empty($eid) && !empty($eat_id) && $editItem ){
	$adjustment_name = $db->getValue('payroll_adjustment','adjustment_name',array('pa_id'=>$editItem));
	$adjustment_value = ( isset($_POST['txAdjustmentVal']) ) ? functions::moneyToDouble($_POST['txAdjustmentVal']) : 0;

	$arrField = array('adjustment_value'=>$adjustment_value,'emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'deduction');

	if( $adjustment_name=="SSS" || $adjustment_name=="Pagibig" || $adjustment_name=="Philhealth"){
		if( $pp_id = $db->getValue('payroll_premium','pp_id',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pa_id'=>$editItem)) ){
			$premium_value = $db->getValue('payroll_premium','(ee_orig+ee_personal) as eeorg',array('pp_id'=>$pp_id));
			if($premium_value!=$adjustment_value){
				$arrField = array_merge($arrField,array('adjustment_mode'=>'manual'));
				$db->update('payroll_premium',array('ee_share'=>$adjustment_value,'modified'=>1),array('pp_id'=>$pp_id));
			}
			else{
				$arrField = array_merge($arrField,array('adjustment_mode'=>'system'));
				$db->update('payroll_premium',array('ee_share'=>$adjustment_value,'modified'=>0),array('pp_id'=>$pp_id));
			}
		}
	}
	$db->update('payroll_adjustment',$arrField,array('pa_id'=>$editItem));
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id).'&edtID='.functions::encode($editItem));
	die();
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="form-card">
	<div class="form-card-header">
		<h2><i class="halflings-icon white edit"></i> Manage Payroll Deductions</h2>
	</div>

	<div class="form-card-body">
		<div class="emp-info-badge">
			Employee Name: <strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong>
		</div>

		<form id="deductionForm" method="post" onSubmit="return askSave()">
			<?php $pp_id = $db->getValue('payroll_premium','pp_id',array('pa_id'=>$editItem)); ?>
			<div class="table-wrapper">
				<table id="tblist">
					<thead>
						<tr>
							<th width="45%" class="text-left">DEDUCTION NAME</th>
							<th width="25%" class="text-center">DEDUCTION AMOUNT</th>
							<th width="30%" class="text-center">ACTION</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="text-left">
								<input <?php if($confirmed==1 || $pay_confirm==1){echo 'disabled="disabled"';}?> type="text" name="txAdjustmentName" id="txAdjustmentName" class="input-control span6" style="width: 280px !important;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php #echo $namesAdjustment;?>]' <?php if( $adjustment_mode=="system" ){echo 'readonly';}else if( $adjustment_name=="SSS" || $adjustment_name=="Pagibig" || $adjustment_name=="Philhealth" ){echo 'readonly';}?>>
								<?php if($pp_id){
										$premium_balance=0;
										if( $adjustment_name=="SSS" ){
											$ee_share = $db->getValue('payroll_premium','sum(ee_share)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'SSS'));
											$base_pay = $db->getValue('payroll_premium','sum(base_pay)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'SSS'));
											$qS = $db->query('SELECT * FROM table_sss WHERE "'.$base_pay.'" BETWEEN sal_from AND sal_to');
											$rS = $db->fetch_array($qS);
											$premium_balance = $rS['sss_ee_share'] - $ee_share;
										}
										if( $adjustment_name=="Pagibig"){
											$premium_balance = $db->getValue('payroll_premium','(ee_personal+ee_orig)-ee_share as sasa',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'HDMF'),'ORDER BY pp_id DESC LIMIT 1');
										}
										if( $adjustment_name=="Philhealth"){
											$ee_share = $db->getValue('payroll_premium','sum(ee_share)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'PHIC'));
											$base_pay = $db->getValue('payroll_premium','sum(base_pay)',array('emp_id'=>$eid,'pp_month'=>$att_month,'pp_year'=>$att_year,'pp_type'=>'PHIC'));
											$qPH = $db->query('SELECT * FROM table_phic WHERE "'.$base_pay.'" BETWEEN sal_from AND sal_to');
											$rPH = $db->fetch_array($qPH);
											if($rPH['phic_type']=='percent'){
												$phic_ee_share = (($rPH['phic_ee_share']/100) * $base_pay);
											}
											else{
												$phic_ee_share = $rPH['phic_ee_share'];
											}
											$premium_balance = round($phic_ee_share - $ee_share,12);
										}

										if($premium_balance)
											echo '<div style="font-size: 11px; color: var(--theme-brown-accent); margin-top: 4px;"><i>Premium Balance Payable: '.$premium_balance.'</i></div>';
								?>
								<?php } ?>
							</td>
							<td class="text-center">
								<input <?php if($confirmed==1 || $pay_confirm==1){echo 'disabled="disabled"';}?> type="text" name="txAdjustmentVal" id="txAdjustmentVal" class="input-control text-center span6" style="width: 140px !important;" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);">
							</td>
							<td class="text-center">
								<button <?php if($confirmed==1 || $pay_confirm==1){echo 'disabled="disabled"';}?> type="submit" name="btnSave" id="btnSave" class="btn-save-custom"><i class="icon-save"></i> SAVE</button>
							</td>
						</tr>
						<?php if($confirmed==1 || $pay_confirm==1){ ?>
						<tr>
							<td colspan="3" class="text-center" style="padding: 15px; color: #dc2626;"><strong>Payroll is already confirmed. Cannot modify!</strong></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>
</div>

<!-- Modern Save Confirmation Modal -->
<div id="saveConfirmModal" class="custom-modal-overlay">
	<div class="custom-modal-card">
		<div class="custom-modal-icon-wrapper">
			<i class="halflings-icon ok" style="margin-top:2px;"></i>
		</div>
		<h3 class="custom-modal-title">Save Deduction Details?</h3>
		<p class="custom-modal-text">Are you sure you want to save these deduction adjustment details for the employee?</p>
		<div class="custom-modal-actions">
			<button type="button" class="btn-modal-cancel" id="btnCancelSaveModal">Cancel</button>
			<button type="button" class="btn-modal-confirm-save" id="btnConfirmSave">Yes, Save</button>
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
var allowDirectSubmit = false;

function askSave(){
	if (!allowDirectSubmit) {
		$('#saveConfirmModal').addClass('active');
		return false;
	}
	return true;
}

$(document).ready(function(){
	// --- Save Modal Handlers ---
	$('#btnConfirmSave').on('click', function(e){
		e.preventDefault();
		allowDirectSubmit = true;
		$('#saveConfirmModal').removeClass('active');
		if ($('#deductionForm input[name="btnSave"]').length === 0) {
			$('#deductionForm').append('<input type="hidden" name="btnSave" value="1">');
		}
		$('#deductionForm')[0].submit();
	});

	// Dismiss modal without extra prompt
	$('#btnCancelSaveModal').on('click', function(){
		$('#saveConfirmModal').removeClass('active');
	});

	// Close Modal on Backdrop Click or Escape Key
	$('.custom-modal-overlay').on('click', function(e){
		if ($(e.target).hasClass('custom-modal-overlay')) {
			$('.custom-modal-overlay').removeClass('active');
		}
	});

	$(document).keyup(function(e) {
		if (e.key === "Escape") {
			$('.custom-modal-overlay').removeClass('active');
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

<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
</body>
</html>