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
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$itemEdt = (isset($_REQUEST['eeEdt']) && !empty($_REQUEST['eeEdt']) ) ? functions::decode($_REQUEST['eeEdt']) : 0;
$itemDel = (isset($_REQUEST['eeDlt']) && !empty($_REQUEST['eeDlt']) ) ? functions::decode($_REQUEST['eeDlt']) : 0;
$selMon='';$selDay='';$selYr='';$txSalMonth='';$txSalWorkDays='';$txSalDay='';$txSalHour='';$txSalMin='';$selSalType='';$txSalDate='';
if($itemEdt){
	$qSal = $db->select('emp_salary','*',array('es_id'=>$itemEdt,'emp_id'=>$eid));
	if( $db->num_rows($qSal) > 0 ){
		$rSal = $db->fetch_array($qSal);
		$txSalMonth = $rSal['es_salary'];
		$txSalDay = $rSal['es_daily'];
		$txSalHour = $rSal['es_hourly'];
		$txSalMin = $rSal['es_minute'];
		$txSalWorkDays = $rSal['working_days'];
		$txSalDate = $rSal['es_date'];
		$selSalType = $rSal['es_type'];
	}
}
if($itemDel){
	$db->delete('emp_salary',array('es_id'=>$itemDel,'emp_id'=>$eid));
	$q = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC,es_id DESC');
	$r = $db->fetch_array($q);
	$essalary = $r['es_salary'] ?? 0;
	$_SESSION['notif_warning']="Salary Information Removed!";
	$db->update('employee',array('salary'=>$essalary),array('emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Salary</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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

	<style type="text/css">
		:root {
			--bg-canvas: #fcfaf8;
			--panel-bg: #ffffff;
			--border-subtle: #e7e0d8;
			--text-primary: #2c1d11;
			--text-muted: #78695c;
			
			/* Earthy Brown Palette */
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
			padding: 0px;
			margin: 0;
			font-size: 13px;
		}

		/* Expand Card Container to 75% Width */
		.form-card {
			background: var(--panel-bg);
			border-radius: 16px;
			border: 1px solid var(--border-subtle);
			box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
			overflow: hidden;
			width: 75% !important;
			max-width: 75% !important;
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
			padding: 30px 35px;
		}

		.section-title {
			font-size: 16px;
			font-weight: 700;
			color: var(--theme-brown-primary);
			margin-bottom: 18px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.section-title.centered-title {
			justify-content: center;
		}

		/* Modernized Table Styling */
		.table-wrapper {
			border-radius: 12px;
			overflow: hidden;
			border: 1px solid var(--border-subtle);
			background: #ffffff;
			margin-bottom: 30px;
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

		/* Explicit Alignment Utilities */
		.text-left { text-align: left !important; }
		.text-center { text-align: center !important; }
		.text-right { text-align: right !important; }

		.badge-type-fixed {
			background-color: var(--theme-brown-light);
			color: var(--theme-brown-primary);
			font-size: 11px;
			font-weight: 700;
			padding: 3px 8px;
			border-radius: 4px;
			border: 1px solid #e6d8ce;
			display: inline-block;
		}

		.badge-type-flexible {
			background-color: #f1f5f9;
			color: #475569;
			font-size: 11px;
			font-weight: 700;
			padding: 3px 8px;
			border-radius: 4px;
			border: 1px solid #e2e8f0;
			display: inline-block;
		}

		.action-btn-group {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 6px;
		}

		.tbl-action-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 30px;
			height: 30px;
			border-radius: 6px;
			transition: all 0.2s ease;
			text-decoration: none !important;
			cursor: pointer;
		}

		.tbl-action-btn.btn-edit {
			background-color: #f59e0b;
			color: #ffffff !important;
		}
		.tbl-action-btn.btn-edit:hover {
			background-color: #d97706;
		}

		.tbl-action-btn.btn-delete {
			background-color: #ef4444;
			color: #ffffff !important;
		}
		.tbl-action-btn.btn-delete:hover {
			background-color: #dc2626;
		}

		/* --- Compact 2-Row Horizontal Form Styling --- */
		.form-grid-container {
			background: #faf6f2;
			border: 1px solid var(--border-subtle);
			border-radius: 12px;
			padding: 20px 24px;
			width: 100%;
			box-sizing: border-box;
		}

		.horizontal-form-grid {
			display: grid;
			grid-template-columns: 1.2fr 0.9fr 0.8fr 1fr 1fr;
			gap: 12px 14px;
			align-items: start;
		}

		.col-label {
			font-size: 11px;
			font-weight: 700;
			color: var(--text-primary);
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 2px;
		}

		.col-input {
			width: 100%;
		}

		.input-control {
			box-sizing: border-box !important;
			padding: 8px 10px !important;
			font-size: 13px !important;
			line-height: 22px !important;
			border-radius: 8px !important;
			border: 1px solid var(--border-subtle) !important;
			background-color: #ffffff !important;
			color: var(--text-primary) !important;
			transition: all 0.2s ease !important;
			outline: none !important;
			height: 38px !important;
			text-align: left;
			width: 100% !important;
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

		.datepicker-flex {
			display: flex;
			align-items: center;
			gap: 6px;
			width: 100%;
		}

		.btn-actions-centered {
			grid-column: 1 / -1;
			margin-top: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
		}

		.btn-save-custom {
			background: var(--theme-brown-primary);
			color: #ffffff !important;
			font-weight: 700;
			font-size: 13px;
			padding: 8px 26px;
			border-radius: 8px;
			border: none;
			cursor: pointer;
			transition: all 0.2s ease;
			box-shadow: 0 4px 12px rgba(124, 64, 30, 0.2);
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.btn-save-custom:hover {
			background: var(--theme-brown-hover);
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(124, 64, 30, 0.3);
		}

		.btn-cancel-custom {
			background: #e5e7eb;
			color: #374151 !important;
			font-weight: 600;
			font-size: 13px;
			padding: 8px 20px;
			border-radius: 8px;
			border: 1px solid #d1d5db;
			text-decoration: none !important;
			display: inline-flex;
			align-items: center;
			transition: all 0.2s ease;
		}

		.btn-cancel-custom:hover {
			background: #d1d5db;
		}

		/* --- ENHANCED MODERN CONFIRMATION MODALS --- */
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
			box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.03);
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
			letter-spacing: -0.3px;
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
			transition: all 0.2s ease;
			box-shadow: 0 1px 2px rgba(0,0,0,0.05);
		}

		.btn-modal-cancel:hover {
			background: #f8fafc;
			border-color: #94a3b8;
			color: #1e293b;
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
			transition: all 0.2s ease;
			box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
			text-decoration: none !important;
			display: inline-flex;
			align-items: center;
			justify-content: center;
		}

		.btn-modal-confirm-danger:hover {
			background: #b91c1c;
			box-shadow: 0 6px 18px rgba(220, 38, 38, 0.45);
			transform: translateY(-1px);
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
			transition: all 0.2s ease;
			box-shadow: 0 4px 14px rgba(124, 64, 30, 0.35);
			text-decoration: none !important;
			display: inline-flex;
			align-items: center;
			justify-content: center;
		}

		.btn-modal-confirm-save:hover {
			background: var(--theme-brown-hover);
			box-shadow: 0 6px 18px rgba(124, 64, 30, 0.45);
			transform: translateY(-1px);
		}
	</style>

<?php
if( isset($_POST['btnSave']) ){

	$es_salary = ( isset($_POST['txSalMonth']) && !empty($_POST['txSalMonth']) ) ? functions::moneyToDouble($_POST['txSalMonth']) : 0;
	$working_days = ( isset($_POST['txSalWorkDays']) && !empty($_POST['txSalWorkDays']) ) ? trim($_POST['txSalWorkDays']) : 0;
	$es_daily = ( isset($_POST['txSalDay']) && !empty($_POST['txSalDay']) ) ? functions::moneyToDouble($_POST['txSalDay']) : 0;
	$es_hourly = ( isset($_POST['txSalHour']) && !empty($_POST['txSalHour']) ) ? functions::moneyToDouble($_POST['txSalHour']) : 0;
	$es_minute = ( isset($_POST['txSalMin']) && !empty($_POST['txSalMin']) ) ? functions::moneyToDouble($_POST['txSalMin']) : 0;
	$es_type = ( isset($_POST['selSalType']) && !empty($_POST['selSalType']) ) ? $_POST['selSalType'] : NULL;
    
	if($es_type=='fixed' && !empty($working_days) ){
		$es_salary = $es_daily * $working_days;
		$es_hourly = $es_daily / 8;
		$es_minute = $es_hourly / 60;
	}
	else if($es_type == 'flexible' && !empty($working_days) ){
		$es_daily = $es_salary / $working_days;
		$es_hourly = $es_daily / 8;
		$es_minute = $es_hourly / 60;
	}

	$es_date = ( isset($_POST['txStatDate']) && !empty($_POST['txStatDate']) ) ? trim($_POST['txStatDate']) : NULL;

	if($es_type && $working_days && ($es_salary || $es_daily) ){
		$arrField = array('emp_id'=>$eid,'es_date'=>$es_date,'es_salary'=>$es_salary,'es_daily'=>$es_daily,'es_hourly'=>$es_hourly,'es_minute'=>$es_minute,'working_days'=>$working_days,'es_type'=>$es_type);
		if($itemEdt){
			$_SESSION['notif_id_list']=$itemEdt;
			$db->update('emp_salary',$arrField,array('es_id'=>$itemEdt,'emp_id'=>$eid));
		}
		else{
			if( $es_id=$db->getValue('emp_salary','es_id',array('es_date'=>$es_date,'emp_id'=>$eid)) ){
				$db->update('emp_salary',$arrField,array('es_date'=>$es_date,'emp_id'=>$eid));
				$_SESSION['notif_id_list']=$es_id;
			}
			else{
				$es_id=$db->insert('emp_salary',$arrField);
				$_SESSION['notif_id_list']=$es_id;
			}
				
		}
		$q = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC,es_id DESC');
		$r = $db->fetch_array($q);
		$essalary = $r['es_salary'] ?? 0;
		$db->update('employee',array('salary'=>$essalary),array('emp_id'=>$eid));
		$_SESSION['notif_success']='Information Saved!';
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
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
	<?php if(empty($fromED)){ ?>
	<div style="background: #f4ede6; padding: 10px 20px; border-bottom: 1px solid var(--border-subtle);" align="right">
		<?php require_once('employee_options.php');?>
	</div>
	<?php } ?>

	<div class="form-card-body">
		<div class="section-title">
			<i class="halflings-icon list-alt" style="margin-top:-2px;"></i> Salary History
		</div>

		<div class="table-wrapper">
			<table id="tblist">
				<thead>
					<tr>
						<th width="14%" class="text-left">Date Started</th>
						<th width="14%" class="text-right">Monthly</th>
						<th width="12%" class="text-center">Days / Mon</th>
						<th width="13%" class="text-right">Daily</th>
						<th width="13%" class="text-right">Hourly</th>
						<th width="12%" class="text-right">Minute</th>
						<th width="12%" class="text-center">Type</th>
						<th width="10%" class="text-center">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$q = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC');
					$countRec = 0;
					while($r = $db->fetch_array($q)):
					$countRec++;
					$rID = $r['es_id'];
					?>
					<tr id="rw<?php echo $rID?>">
						<td class="text-left"><strong><?php echo functions::datearr($r['es_date'])?></strong></td>
						<td class="text-right"><?php echo functions::formatMoney($r['es_salary'])?></td>
						<td class="text-center"><?php echo $r['working_days']?> days</td>
						<td class="text-right"><?php echo functions::formatMoney($r['es_daily'],'~')?></td>
						<td class="text-right"><?php echo functions::formatMoney($r['es_hourly'],'~')?></td>
						<td class="text-right"><?php echo functions::formatMoney($r['es_minute'],'~')?></td>
						<td class="text-center">
							<?php if($r['es_type']=='fixed'){ ?>
								<span class="badge-type-fixed">Daily</span>
							<?php } elseif($r['es_type']=='flexible'){ ?>
								<span class="badge-type-flexible">Monthly</span>
							<?php } ?>
						</td>
						<td class="text-center">
							<div class="action-btn-group">
								<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeEdt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" title="Modify" class="tbl-action-btn btn-edit">
									<i class="halflings-icon white pencil" style="margin-top:0;"></i>
								</a>
								<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeDlt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" title="Remove" class="tbl-action-btn btn-delete btn-delete-trigger">
									<i class="halflings-icon white trash" style="margin-top:0;"></i>
								</a>
							</div>
						</td>
					</tr>
					<?php endwhile;
					if($countRec == 0){
						echo '<tr><td colspan="8" class="text-center" style="padding:20px; color: var(--text-muted);">--- No Salary History Found ---</td></tr>';
					}
					?>
				</tbody>
			</table>
		</div>

		<div class="section-title centered-title">
			<i class="halflings-icon edit" style="margin-top:-2px;"></i> <?php echo ($itemEdt) ? 'Modify Salary Record' : 'Add New Salary Record'; ?>
		</div>

		<form id="salaryForm" method="post" onSubmit="return ask()">
			<div class="form-grid-container">
				<div class="horizontal-form-grid">
					
					<!-- Row 1: Labels -->
					<div class="col-label"><label for="selSalType" style="font-weight:700; margin:0;">Salary Type</label></div>
					<div class="col-label"><label for="txStatDate" style="font-weight:700; margin:0;">Date Started</label></div>
					<div class="col-label"><label for="txSalWorkDays" style="font-weight:700; margin:0;">Days / Month</label></div>
					<div class="col-label"><label for="txSalMonth" style="font-weight:700; margin:0;">Monthly Rate</label></div>
					<div class="col-label"><label for="txSalDay" style="font-weight:700; margin:0;">Daily Rate</label></div>

					<!-- Row 2: Inputs -->
					<div class="col-input">
						<select name="selSalType" id="selSalType" class="input-control" required>
							<option value="">--Select--</option>
							<option value="fixed" <?php if($selSalType=='fixed'){echo 'selected="selected"';} ?>>(Labor) Daily Fixed</option>
							<option value="flexible" <?php if($selSalType=='flexible'){echo 'selected="selected"';} ?>>(Office) Monthly</option>
						</select>
					</div>

					<div class="col-input">
						<div class="datepicker-flex">
							<input name="txStatDate" type="text" class="input-control" id="txStatDate" onClick="javascript:NewCssCal(this.id)" onkeyup="document.getElementById(this.id).value=''" value="<?php echo $txSalDate; ?>" placeholder="YYYY-MM-DD" required>
							<a href="javascript:NewCssCal('txStatDate')"><img src="../js/datepick/cal.gif" width="18" height="18" border="0" alt="Pick Date"></a>
						</div>
					</div>

					<div class="col-input">
						<input type="number" name="txSalWorkDays" id="txSalWorkDays" class="input-control" value="<?php echo $txSalWorkDays?>" placeholder="e.g. 26" required>
					</div>

					<div class="col-input">
						<input type="text" name="txSalMonth" id="txSalMonth" class="input-control" value="<?php echo $txSalMonth?>" onkeyup="FormatCurrency(this);" placeholder="0.00" required>
					</div>

					<div class="col-input">
						<input type="text" name="txSalDay" id="txSalDay" class="input-control" value="<?php echo $txSalDay?>" onkeyup="FormatCurrency(this);" placeholder="0.00" required>
					</div>

					<!-- Centered Action Buttons -->
					<div class="btn-actions-centered">
						<button type="submit" name="btnSave" id="btnSave" class="btn-save-custom">
							<i class="halflings-icon white ok" style="margin-top:0;"></i> Save Information
						</button>
						<?php if($itemEdt){ ?>
							<a class="btn-cancel-custom" href="?eid=<?php echo functions::encode($eid)?>&fromED=<?php echo $fromED;?>">Cancel</a>
						<?php } ?>
					</div>

				</div>
			</div>
		</form>

	</div>
</div>

<!-- Modern Delete Confirmation Modal HTML -->
<div id="deleteConfirmModal" class="custom-modal-overlay">
	<div class="custom-modal-card">
		<div class="custom-modal-icon-wrapper danger-theme">
			<i class="halflings-icon trash" style="margin-top:2px;"></i>
		</div>
		<h3 class="custom-modal-title">Remove Salary Record?</h3>
		<p class="custom-modal-text">Are you sure you want to remove this salary record? This action cannot be undone.</p>
		<div class="custom-modal-actions">
			<button type="button" class="btn-modal-cancel" id="btnCancelDeleteModal">Cancel</button>
			<button type="button" class="btn-modal-confirm-danger" id="btnConfirmDelete">Delete</button>
		</div>
	</div>
</div>

<!-- Modern Save Confirmation Modal HTML -->
<div id="saveConfirmModal" class="custom-modal-overlay">
	<div class="custom-modal-card">
		<div class="custom-modal-icon-wrapper save-theme">
			<i class="halflings-icon ok" style="margin-top:2px;"></i>
		</div>
		<h3 class="custom-modal-title">Save Salary Details?</h3>
		<p class="custom-modal-text">Are you sure you want to save this salary information record for the employee?</p>
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

function ask(){
	if (!allowDirectSubmit) {
		$('#saveConfirmModal').addClass('active');
		return false;
	}
	return true;
}

$(document).ready(function(){
	var salType='';
	$('#txSalMonth').attr('readonly',true);
	$('#txSalDay').attr('readonly',true);

	<?php
	if($selSalType=='fixed'){echo "$('#txSalDay').attr('readonly',false);";}
	if($selSalType=='flexible'){echo "$('#txSalMonth').attr('readonly',false);";}
	?>

	$('#selSalType').change(function(){
		salType = $(this).val();
		if(salType == 'fixed'){
			$('#txSalDay').attr('readonly',false);
			$('#txSalMonth').attr('readonly',true);
			<?php if(empty($itemEdt)){?>$('#txSalMonth').val('');<?php } ?>
		}
		else if(salType=='flexible'){
			$('#txSalMonth').attr('readonly',false);
			$('#txSalDay').attr('readonly',true);
			<?php if(empty($itemEdt)){?>$('#txSalDay').val('');<?php } ?>
		}
		else{
			$('#txSalMonth').attr('readonly',true);
			$('#txSalDay').attr('readonly',true);
		}
	});

	// --- Save Modal JS Event Handlers ---
	$('#btnConfirmSave').on('click', function(e){
		e.preventDefault();
		allowDirectSubmit = true;
		$('#saveConfirmModal').removeClass('active');
		if ($('#salaryForm input[name="btnSave"]').length === 0) {
			$('#salaryForm').append('<input type="hidden" name="btnSave" value="1">');
		}
		$('#salaryForm')[0].submit();
	});

	$('#btnCancelSaveModal').on('click', function(){
		$('#saveConfirmModal').removeClass('active');
	});

	// --- Delete Modal JS Event Handlers ---
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

	$('#btnCancelDeleteModal').on('click', function(){
		$('#deleteConfirmModal').removeClass('active');
	});

	// --- Close Modals on Backdrop Click & Escape Key ---
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

<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','2px solid #7c401e');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>