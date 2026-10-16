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
$confirmed = $db->getValue('emp_attendance','confirmed',array('eat_id'=>$eat_id));
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$editItem = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
$deleteItem = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
$paID = (isset($_REQUEST['dID']) && !empty($_REQUEST['dID']) ) ? functions::decode($_REQUEST['dID']) : 0;
$fromPayroll = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? 1 : 0;
if($fromPayroll)
	$_SESSION['notif_indi_id']=$eid;
$adjustment_name='';$adjustment_value='';
$arrPosition=array();
$pay_confirm = $db->getValue('emp_attendance_personnel','count(*)',array('emp_id'=>$eid,'eat_id'=>$eat_id,'pay_ready'=>1));
if($editItem){
	$q = $db->select('payroll_adjustment','*',array('pa_id'=>$editItem));
	while($r = $db->fetch_array($q)):
		$adjustment_name = $r['adjustment_name'];
		$adjustment_value = functions::formatMoney($r['adjustment_value'],'~');
	endwhile;
}
if($deleteItem){
	$db->delete('payroll_adjustment',array('pa_id'=>$deleteItem));
	$_SESSION['notif_warning']='Adjustment Removed!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
	die();
}
if($paID){
	if( $db->getValue('payroll_adjustment','include',array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id))==1 ){
		$db->update('payroll_adjustment',array('include'=>0),array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
		$_SESSION['notif_warning']='Adjustment Excluded!';
	}
	else{
		$db->update('payroll_adjustment',array('include'=>1),array('pa_id'=>$paID,'emp_id'=>$eid,'eat_id'=>$eat_id));
		$_SESSION['notif_success']='Adjustment Included!';
	}
	$_SESSION['notif_id']=$paID;
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
	die();
}

$qItemAdjustment = $db->query('SELECT DISTINCT adjustment_name FROM payroll_adjustment WHERE adjustment_type="addon" ORDER BY adjustment_name');
$namesAdjustment='';
while($rItemAdjustment=$db->fetch_array($qItemAdjustment)):
	$string = preg_replace("/'/",'"',$rItemAdjustment['adjustment_name']);
	$namesAdjustment .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesAdjustment .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payroll Add-on</title>
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

		/* Main Card set to 90% Width */
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

		.btn-save-custom {
			background: var(--theme-brown-primary);
			color: #ffffff !important;
			font-weight: 700;
			font-size: 12px;
			padding: 7px 18px;
			border-radius: 6px;
			border: none;
			cursor: pointer;
			transition: all 0.2s ease;
		}

		.btn-save-custom:hover {
			background: var(--theme-brown-hover);
		}

		.btn-cancel-custom {
			background: #e5e7eb;
			color: #374151 !important;
			font-weight: 600;
			font-size: 12px;
			padding: 7px 16px;
			border-radius: 6px;
			border: 1px solid #d1d5db;
			cursor: pointer;
			text-decoration: none !important;
			display: inline-flex;
			align-items: center;
			justify-content: center;
		}

		.btn-cancel-custom:hover {
			background: #d1d5db;
		}

		/* Modern Confirmation Modals */
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
		}

		.custom-modal-icon-wrapper.danger-theme {
			background: #fef2f2;
			color: #dc2626;
			box-shadow: 0 0 0 8px #fef2f2, 0 4px 12px rgba(220, 38, 38, 0.15);
		}

		.custom-modal-icon-wrapper.save-theme {
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

		.btn-modal-confirm-danger {
			flex: 1;
			background: #dc2626;
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
if( isset($_POST['btnCancel']) && !empty($eid) && !empty($eat_id) ){
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
}
if( isset($_POST['btnSave']) && !empty($eid) && !empty($eat_id) ){
	$adjustment_name = ( isset($_POST['txAdjustmentName']) ) ? trim($_POST['txAdjustmentName']) : '';
	$adjustment_value = ( isset($_POST['txAdjustmentVal']) ) ? functions::moneyToDouble($_POST['txAdjustmentVal']) : 0;
	$arrField = array('adjustment_name'=>$adjustment_name,'adjustment_value'=>$adjustment_value,'emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'addon');
	if($adjustment_name){
		if($editItem){
			$db->update('payroll_adjustment',$arrField,array('pa_id'=>$editItem));
			$_SESSION['notif_id']=$editItem;
			$_SESSION['notif_success']='Changes saved!';
		}
		else{
			$ins = $db->insert('payroll_adjustment',array_merge($arrField,array('adjustment_mode'=>'manual')));
			$_SESSION['notif_success']='Adjustment Added!';
			$_SESSION['notif_id']=$ins;
		}
			
		$_SESSION['notif_success']='Saved Successfully!';
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="form-card">
	<div class="form-card-header">
		<h2><i class="halflings-icon white edit"></i> Manage Payroll Add-ons</h2>
	</div>

	<div class="form-card-body">
		<div class="emp-info-badge">
			Employee Name: <strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong>
		</div>

		<form id="addonForm" method="post" onSubmit="return askSave()">
			<div class="table-wrapper">
				<table id="tblist">
					<thead>
						<tr>
							<th width="8%" class="text-center">INCLUDE</th>
							<th width="45%" class="text-left">ADD-ON NAME</th>
							<th width="22%" class="text-center">AMOUNT</th>
							<th width="25%" class="text-center">ACTIONS</th>
						</tr>
					</thead>
					<tbody>
						<?php if( ($confirmed==0 && $pay_confirm==0) && empty($editItem)){?>
						<tr>
							<td></td>
							<td class="text-left">
								<input type="text" name="txAdjustmentName" id="txAdjustmentName" class="input-control span6" style="width: 280px !important;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAdjustment;?>]' required>
							</td>
							<td class="text-center">
								<input type="text" name="txAdjustmentVal" id="txAdjustmentVal" class="input-control text-center span6" style="width: 140px !important;" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);" required>
							</td>
							<td class="text-center">
								<button type="submit" name="btnSave" id="btnSave" class="btn-save-custom">SAVE</button>
								<a href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>" class="btn-cancel-custom">CANCEL</a>
							</td>
						</tr>
						<?php }?>

						<?php
						$total_adjustment=0;
						$q = $db->select('payroll_adjustment','*',array('emp_id'=>$eid,'eat_id'=>$eat_id,'adjustment_type'=>'addon'),'ORDER BY adjustment_name');
						while($r = $db->fetch_array($q)):
							$rID = $r['pa_id'];
							if($r['include'])
								$total_adjustment += $r['adjustment_value'];
							if($editItem==$rID){
						?>
						<?php if($confirmed==0 && $pay_confirm==0){?>
						<tr id="rw<?php echo $rID;?>">
							<td></td>
							<td class="text-left">
								<input type="text" name="txAdjustmentName" id="txAdjustmentName" class="input-control span6" style="width: 280px !important;" autofocus value="<?php echo $adjustment_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAdjustment;?>]' required>
							</td>
							<td class="text-center">
								<input type="text" name="txAdjustmentVal" id="txAdjustmentVal" class="input-control text-center span6" style="width: 140px !important;" value="<?php echo $adjustment_value?>" onkeyup="FormatCurrency(this);" required>
							</td>
							<td class="text-center">
								<button type="submit" name="btnSave" id="btnSave" class="btn-save-custom"><i class="icon-save"></i> Save</button>
								<a href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>" class="btn-cancel-custom"><i class="icon-ban-circle"></i> Cancel</a>
							</td>
						</tr>
						<?php }?>
						<?php }else{ ?>
						<tr id="rw<?php echo $rID;?>" <?php if(empty($r['include'])){echo 'style="background-color:#f1ede8"';}?>>
							<td class="text-center">
								<input type="checkbox" name="chck<?php echo $rID?>" id="chck<?php echo $rID?>" value="<?php echo functions::encode($rID)?>" <?php if($r['include']==1){echo 'checked="checked"';}?> onClick="chkInclude(this.value)" <?php if($confirmed)echo 'disabled="disabled"';?>>
							</td>
							<td class="text-left"><strong><?php echo $r['adjustment_name']?></strong></td>
							<td class="text-center"><?php echo functions::formatMoney($r['adjustment_value'])?></td>
							<td class="text-center">
								<?php if($confirmed==0 && $r['adjustment_mode']=='manual'){?>
								<a id="edt<?php echo $rID;?>" class="btn btn-mini btn-warning" title="Modify" href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>&edtID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white pencil"></i> Edit</a>
								<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger btn-delete-trigger" title="Remove this Item" href="?eid=<?php echo functions::encode($eid);?>&eatid=<?php echo functions::encode($eat_id);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i> Delete</a>
								<?php }?>
							</td>
						</tr>
						<?php } ?>
						<?php endwhile;?>
						<tr>
							<td colspan="2" class="text-right"><strong>Total Add-ons:</strong></td>
							<td class="text-center"><strong><?php echo functions::formatMoney($total_adjustment)?></strong></td>
							<td>&nbsp;</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
	</div>
</div>

<!-- Modern Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="custom-modal-overlay">
	<div class="custom-modal-card">
		<div class="custom-modal-icon-wrapper danger-theme">
			<i class="halflings-icon trash" style="margin-top:2px;"></i>
		</div>
		<h3 class="custom-modal-title">Remove Add-on Item?</h3>
		<p class="custom-modal-text">Are you sure you want to remove this add-on item from the employee's payroll record?</p>
		<div class="custom-modal-actions">
			<button type="button" class="btn-modal-cancel" id="btnCancelDeleteModal">Cancel</button>
			<button type="button" class="btn-modal-confirm-danger" id="btnConfirmDelete">Delete Item</button>
		</div>
	</div>
</div>

<!-- Modern Save Confirmation Modal -->
<div id="saveConfirmModal" class="custom-modal-overlay">
	<div class="custom-modal-card">
		<div class="custom-modal-icon-wrapper save-theme">
			<i class="halflings-icon ok" style="margin-top:2px;"></i>
		</div>
		<h3 class="custom-modal-title">Save Add-on Details?</h3>
		<p class="custom-modal-text">Are you sure you want to save these add-on adjustment details?</p>
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

function chkInclude(dID){
	window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eat_id)?>&eid=<?php echo functions::encode($eid)?>&dID="+dID;
}

$(document).ready(function(){
	// --- Save Modal Handlers ---
	$('#btnConfirmSave').on('click', function(e){
		e.preventDefault();
		allowDirectSubmit = true;
		$('#saveConfirmModal').removeClass('active');
		if ($('#addonForm input[name="btnSave"]').length === 0) {
			$('#addonForm').append('<input type="hidden" name="btnSave" value="1">');
		}
		$('#addonForm')[0].submit();
	});

	// Direct dismissal on modal Cancel click (no extra prompt)
	$('#btnCancelSaveModal').on('click', function(){
		$('#saveConfirmModal').removeClass('active');
	});

	// --- Delete Modal Handlers ---
	var deleteTargetUrl = "";

	$(document).on('click', '.btn-delete-trigger', function(e){
		e.preventDefault();
		deleteTargetUrl = $(this).attr('href');
		$('#deleteConfirmModal').addClass('active');
	});

	$('#btnConfirmDelete').on('click', function(e){
		e.preventDefault();
		if (deleteTargetUrl !== "") {
			window.location.href = deleteTargetUrl;
		}
	});

	// Direct dismissal on modal Cancel click (no extra prompt)
	$('#btnCancelDeleteModal').on('click', function(){
		$('#deleteConfirmModal').removeClass('active');
	});

	// --- Close Modals directly on Backdrop Click or Escape Key ---
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

<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','2px solid #7c401e');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>
</body>
</html>