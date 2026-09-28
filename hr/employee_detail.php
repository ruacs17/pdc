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
$db->utf8();
$add = (isset($_REQUEST['add']) && !empty($_REQUEST['add']) ) ? $_REQUEST['add'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdate='';$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';$txReligion='';$txHeight='';$txWeight='';$txBloodType='';$txTIN='';$txpagibig='';$txphilhealth='';$txtSSS='';$txtGSIS='';$selCurProvince='';$selCurCityMun='';$selCurBrngy='';$curStreet='';$curr_zipcode='';$curTelNo='';$selProvincePerm='';$selCityMunPerm='';$selBrngyPerm='';$permStreet='';$permZipCode='';$permTelNo='';$txEmail='';$txCellphone='';

$txtspfName='';$txtspmName='';$txtsplName='';$txtspExtName='';$txtExtName='';$txtspOccupation='';$txspbusname='';$txtspbusadd='';$txspTelNo='';$txtfrfName='';$txtfrmName='';$txtfrlName='';$txtfrExtName='';$txtmrfName='';$txtmrmName='';$txtmrlName='';
$current_address=''; $permanent_address='';
$ctc_no='';$ctc_issue_place='';$ctc_issue_date='';$date_accomplished='';$work_status='';$dp_id='';
$monAmIn='';$monAmOut='';$monPmIn='';$monPmOut='';$tueAmIn='';$tueAmOut='';$tuePmIn='';$tuePmOut='';$wedAmIn='';$wedAmOut='';$wedPmIn='';$wedPmOut='';$thuAmIn='';$thuAmOut='';$thuPmIn='';$thuPmOut='';$friAmIn='';$friAmOut='';$friPmIn='';$friPmOut='';$satAmIn='';$satAmOut='';$satPmIn='';$satPmOut='';
$txtPagIbigContrib='';$txtPHContrib=''; $txtSSSContrib='';$es_type='';$txSalMonth='';$txSalWorkDays='';$txSalDay='';$txSalHour='';$txSalMin='';$selSalMon='';$selSalDay='';$selSalYr='';$txSalDate=''; $has_attendance=0;
if($eid){
	$q = $db->select('employee','*',array('emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$cert_id = $r['cert_id'];
		$txtEmpNo = $r['emp_no'];
		$txtfName = $r['fname'];
		$txtmName = $r['mname'];
		$txtlName = $r['lname'];
		$txtExtName = $r['extname'];
		$txtNickName = $r['nickname'];
		$bdate = $r['bdate'];
		$txtBirthPlace = $r['bplace'];
		$selGender = $r['gender'];
		$selCivilStat = $r['civil_status'];
		$txCitizenship = $r['citizenship'];
		$txReligion = $r['religion'];
		$txHeight = $r['height'];
		$txWeight = $r['weight'];
		$txBloodType = $r['bloodtype'];
		$txTIN = $r['tin'];
		$txpagibig = $r['pagibig'];
		$txphilhealth = $r['philhealth'];
		$txtSSS = $r['sss'];
		$txtGSIS = $r['gsis'];

		$txtPagIbigContrib = ($r['pagibig_contribution']) ? functions::formatMoney($r['pagibig_contribution']) : '';
		$pagibig_autodeduct = $r['pagibig_autodeduct'];
		$txtPHContrib = functions::formatMoney($r['philhealth_contribution']);
		$philhealth_autodeduct = $r['philhealth_autodeduct'];
		$txtSSSContrib = functions::formatMoney($r['sss_contribution']);
		$sss_autodeduct = $r['sss_autodeduct'];
		$gsis_autodeduct = $r['gsis_autodeduct'];

		$curStreet = $r['curr_street'];
		$curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['curr_province']));
		$curProvince = ($curProvince) ? ', '.$curProvince : '';
		$curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['curr_cityMun']));
		$curCity = ($curCity) ? ', '.$curCity : '';
		$curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['curr_add']));
		$curr_zipcode = ($r['curr_zipcode']) ? ', '.$r['curr_zipcode'] : '';
		$current_address = $curStreet.' '.strtoupper($curBrngy).$curCity.$curProvince.$curr_zipcode;
		$curTelNo = $r['curr_tel'];

		$permStreet = $r['perm_street'];
		$permProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['perm_province']));
		$permCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['perm_cityMun']));
		$permBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['perm_add']));
		$permanent_address = $permStreet.' '.strtoupper($permBrngy).', '.$permCity.', '.$permProvince.', '.$r['perm_zipcode'];
		$permTelNo = $r['perm_tel'];

		$txEmail = $r['email'];
		$txCellphone = $r['cell_no'];

		$txtspfName = $r['sp_fname'];
		$txtspmName = $r['sp_mname'];
		$txtsplName = $r['sp_lname'];
		$txtspExtName = $r['sp_extname'];
		$txtExtName = $r['extname'];
		$txtspOccupation = $r['sp_occupation'];
		$txspbusname = $r['sp_bus_name'];
		$txtspbusadd = $r['sp_bus_address'];
		$txspTelNo = $r['sp_tel_no'];
		$txtfrfName = $r['fr_fname'];
		$txtfrmName = $r['fr_mname'];
		$txtfrlName = $r['fr_lname'];
		$txtfrExtName = $r['fr_extname'];
		$txtmrfName = $r['mr_fname'];
		$txtmrmName = $r['mr_mname'];
		$txtmrlName = $r['mr_lname'];
		$ctc_no = $r['ctc_no'];
		$ctc_issue_place = $r['ctc_issue_place'];
		$ctc_issue_date = $r['ctc_issue_date'];
		$date_accomplished = $r['date_accomplished'];
		$work_status = $r['work_status'];
		$has_attendance = $r['has_attendance'];
	endwhile;
	if($has_attendance){
		$qTimeIn = $db->select('emp_timein','*',array('emp_id'=>$eid));
		while($rTI = $db->fetch_array($qTimeIn)):
			if($rTI['eti_day']=='Monday'){
				$monAmIn = $rTI['am_in'];
				$monAmOut = $rTI['am_out'];
				$monPmIn = $rTI['pm_in'];
				$monPmOut = $rTI['pm_out'];
			}
			if($rTI['eti_day']=='Tuesday'){
				$tueAmIn = $rTI['am_in'];
				$tueAmOut = $rTI['am_out'];
				$tuePmIn = $rTI['pm_in'];
				$tuePmOut = $rTI['pm_out'];   
			}
			if($rTI['eti_day']=='Wednesday'){
				$wedAmIn = $rTI['am_in'];
				$wedAmOut = $rTI['am_out'];
				$wedPmIn = $rTI['pm_in'];
				$wedPmOut = $rTI['pm_out'];
			}
			if($rTI['eti_day']=='Thursday'){
				$thuAmIn = $rTI['am_in'];
				$thuAmOut = $rTI['am_out'];
				$thuPmIn = $rTI['pm_in'];
				$thuPmOut = $rTI['pm_out']; 
			}
			if($rTI['eti_day']=='Friday'){
				$friAmIn = $rTI['am_in'];
				$friAmOut = $rTI['am_out'];
				$friPmIn = $rTI['pm_in'];
				$friPmOut = $rTI['pm_out'];
			}
			if($rTI['eti_day']=='Saturday'){
				$satAmIn = $rTI['am_in'];
				$satAmOut = $rTI['am_out'];
				$satPmIn = $rTI['pm_in'];
				$satPmOut = $rTI['pm_out']; 
			}
		endwhile;
	}
	$qSal = $db->select('emp_salary','*',array('emp_id'=>$eid),'ORDER BY es_date DESC, es_id DESC');
	if( $db->num_rows($qSal) > 0 ){
		$rSal = $db->fetch_array($qSal);
		$txSalMonth = functions::formatMoney($rSal['es_salary']);
		$txSalDay = functions::formatMoney($rSal['es_daily']);
		$txSalHour = functions::formatMoney($rSal['es_hourly']);
		$txSalMin = functions::formatMoney($rSal['es_minute']);
		$txSalWorkDays = $rSal['working_days'];
		$txSalDate = $rSal['es_date'];
		$es_type = $rSal['es_type'];
		$salDateExp = explode('-',$txSalDate);
		if( count($salDateExp)==3 ){
			$selSalMon=$salDateExp[1];
			$selSalDay=$salDateExp[2];
			$selSalYr=$salDateExp[0];
		}
	}
}
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
$file = ($fileName && file_exists('../img_emp/'.$fileName)) ? $fileName : 'blank-pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Detail</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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

	<style>
		body {
			background-color: #f4f6f9;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			color: #333333;
			padding: 20px 10px;
		}

		.pds-container {
			max-width: 99%;
			margin: 0 auto;
		}

		.pds-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 25px;
			border-bottom: 2px solid #e0e0e0;
			padding-bottom: 15px;
		}

		.pds-title {
			font-size: 24px;
			font-weight: 700;
			color: #1a252f;
			margin: 0;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		/* Modern Card Styling */
		.pds-card {
			background: #ffffff;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
			margin-bottom: 30px;
			border: 1px solid #e9ecef;
			overflow: hidden;
		}

		.pds-card-header {
			background-color: #1a252f;
			color: #ffffff;
			padding: 12px 20px;
			font-size: 15px;
			font-weight: 600;
			display: flex;
			justify-content: space-between;
			align-items: center;
			letter-spacing: 0.5px;
		}

		.pds-card-header a {
			color: #3498db;
			transition: color 0.2s;
		}

		.pds-card-header a:hover {
			color: #2980b9;
			text-decoration: none;
		}

		.pds-card-body {
			padding: 20px;
		}

		/* Grid Layout for Personal Info */
		.profile-layout {
			display: grid;
			grid-template-columns: 260px 1fr;
			gap: 25px;
		}

		.profile-avatar-wrapper {
			text-align: center;
		}

		.profile-avatar {
			width: 220px;
			height: 220px;
			object-fit: cover;
			border-radius: 8px;
			border: 3px solid #e0e0e0;
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		}

		/* Enhanced Data Grid */
		.data-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
			gap: 15px;
		}

		.data-item {
			background: #f8f9fa;
			padding: 10px 14px;
			border-radius: 6px;
			border-left: 3px solid #3498db;
		}

		.data-item.full-width {
			grid-column: 1 / -1;
		}

		.data-label {
			font-size: 11px;
			text-transform: uppercase;
			color: #7f8c8d;
			font-weight: 700;
			margin-bottom: 4px;
		}

		.data-value {
			font-size: 14px;
			color: #2c3e50;
			font-weight: 500;
			word-break: break-word;
		}

		/* Modern Tables */
		.custom-table {
			width: 100%;
			margin-bottom: 0;
			border-collapse: collapse;
		}

		.custom-table th {
			background-color: #f1f3f5;
			color: #495057;
			font-size: 12px;
			font-weight: 700;
			text-transform: uppercase;
			padding: 10px 12px;
			border-bottom: 2px solid #dee2e6;
		}

		.custom-table td {
			padding: 10px 12px;
			vertical-align: middle;
			border-top: 1px solid #e9ecef;
			font-size: 13px;
		}

		.custom-table tbody tr:hover {
			background-color: #f8f9fa;
		}

		/* Badges & Status */
		.badge-status {
			display: inline-block;
			padding: 4px 8px;
			font-size: 11px;
			font-weight: 600;
			border-radius: 4px;
			background: #e9ecef;
			color: #495057;
		}

		.badge-active {
			background-color: #e6f4ea;
			color: #137333;
		}

		/* Utility styling */
		.text-center { text-align: center; }
		.text-right { text-align: right; }
		.no-data { color: #95a5a6; font-style: italic; text-align: center; padding: 15px; }

		@media (max-width: 768px) {
			.profile-layout {
				grid-template-columns: 1fr;
			}
			.profile-avatar-wrapper {
				margin-bottom: 20px;
			}
		}
	</style>
</head>
<body>

<div class="pds-container">
	
	<!-- Top Controls -->
	<div class="pds-header">
		<h1 class="pds-title">Personal Data Sheet</h1>
		<?php if($add){?>
		<a href="employee_add.php" class="btn btn-primary"><i class="icon-plus icon-white"></i> Add New Employee</a>
		<?php } ?>
	</div>

	<!-- Section 1: Personal Information -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon user icon-white"></i> I. PERSONAL INFORMATION</span>
			<a id="lnkEmpEdt" href="#" class="thickbox" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Employee Detail')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body">
			<div class="profile-layout">
				<div class="profile-avatar-wrapper">
					<img class="profile-avatar" src="../img_emp/<?php echo $file;?>" alt="Employee Photo">
				</div>
				<div class="data-grid">
					<div class="data-item">
						<div class="data-label">Employee Number</div>
						<div class="data-value"><?php echo $txtEmpNo ? $txtEmpNo : '---';?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Full Name</div>
						<div class="data-value"><?php echo trim($txtlName.', '.$txtfName.' '.$txtmName.' '.$txtExtName);?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Nickname</div>
						<div class="data-value"><?php echo $txtNickName ? $txtNickName : '---';?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Date of Birth</div>
						<div class="data-value">
							<?php echo functions::datearr($bdate); ?>
							<?php if($bdate){ ?> <span class="badge-status">(Age: <?php echo functions::year_diff($bdate,date('Y-m-d'));?>)</span><?php }?>
						</div>
					</div>
					<div class="data-item">
						<div class="data-label">Place of Birth</div>
						<div class="data-value"><?php echo $txtBirthPlace ? $txtBirthPlace : '---';?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Gender</div>
						<div class="data-value"><?php echo $selGender ? $selGender : '---';?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Civil Status</div>
						<div class="data-value"><?php echo $selCivilStat ? $selCivilStat : '---';?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Citizenship</div>
						<div class="data-value"><?php echo $txCitizenship ? $txCitizenship : '---';?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Height & Weight</div>
						<div class="data-value"><?php echo $txHeight ? $txHeight.' m' : '---';?> / <?php echo $txWeight ? $txWeight.' kg' : '---';?></div>
					</div>
					<div class="data-item">
						<div class="data-label">Blood Type</div>
						<div class="data-value"><?php echo $txBloodType ? $txBloodType : '---';?></div>
					</div>
				</div>
			</div>

			<hr style="margin: 20px 0; border-top: 1px solid #e9ecef;">

			<div class="data-grid">
				<div class="data-item">
					<div class="data-label">TIN</div>
					<div class="data-value"><?php echo $txTIN ? $txTIN : '---';?></div>
				</div>
				<div class="data-item">
					<div class="data-label">PAG-IBIG ID</div>
					<div class="data-value">
						<?php echo $txpagibig ? $txpagibig : '---';?>
						<?php if($txtPagIbigContrib){ ?> <br><small><strong>Addl: <?php echo $txtPagIbigContrib;?></strong></small><?php } ?>
						<br><small class="text-muted">Auto-deduct: <?php echo $pagibig_autodeduct ? '<span class="badge-status badge-active">Enabled</span>' : '<span class="badge-status">Disabled</span>';?></small>
					</div>
				</div>
				<div class="data-item">
					<div class="data-label">PHILHEALTH NO.</div>
					<div class="data-value">
						<?php echo $txphilhealth ? $txphilhealth : '---';?>
						<br><small class="text-muted">Auto-deduct: <?php echo $philhealth_autodeduct ? '<span class="badge-status badge-active">Enabled</span>' : '<span class="badge-status">Disabled</span>';?></small>
					</div>
				</div>
				<div class="data-item">
					<div class="data-label">SSS NO.</div>
					<div class="data-value">
						<?php echo $txtSSS ? $txtSSS : '---';?>
						<br><small class="text-muted">Auto-deduct: <?php echo $sss_autodeduct ? '<span class="badge-status badge-active">Enabled</span>' : '<span class="badge-status">Disabled</span>';?></small>
					</div>
				</div>
				<div class="data-item">
					<div class="data-label">GSIS NO.</div>
					<div class="data-value">
						<?php echo $txtGSIS ? $txtGSIS : '---';?>
						<br><small class="text-muted">Auto-deduct: <?php echo $gsis_autodeduct ? '<span class="badge-status badge-active">Enabled</span>' : '<span class="badge-status">Disabled</span>';?></small>
					</div>
				</div>
				<div class="data-item full-width">
					<div class="data-label">Present Address & Tel</div>
					<div class="data-value"><?php echo $current_address ? $current_address : '---';?> <?php echo $curTelNo ? ' (Tel: '.$curTelNo.')' : '';?></div>
				</div>
				<div class="data-item full-width">
					<div class="data-label">Home Address & Tel</div>
					<div class="data-value"><?php echo $permanent_address ? $permanent_address : '---';?> <?php echo $permTelNo ? ' (Tel: '.$permTelNo.')' : '';?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Email Address</div>
					<div class="data-value"><?php echo $txEmail ? $txEmail : '---';?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Cellphone Number</div>
					<div class="data-value"><?php echo $txCellphone ? $txCellphone : '---';?></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Section 2: Family Background -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon home icon-white"></i> II. FAMILY BACKGROUND</span>
			<a id="lnkfamback" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_family.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Family Background')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body">
			<div class="data-grid" style="margin-bottom: 20px;">
				<div class="data-item">
					<div class="data-label">Father's Name</div>
					<div class="data-value"><?php echo trim($txtfrfName.' '.$txtfrmName.' '.$txtfrlName.' '.$txtfrExtName);?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Mother's Name</div>
					<div class="data-value"><?php echo trim($txtmrfName.' '.$txtmrmName.' '.$txtmrlName);?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Spouse's Name</div>
					<div class="data-value"><?php echo trim($txtspfName.' '.$txtspmName.' '.$txtsplName.' '.$txtspExtName);?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Spouse Occupation</div>
					<div class="data-value"><?php echo $txtspOccupation ? $txtspOccupation : '---';?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Business Name & Tel</div>
					<div class="data-value"><?php echo $txspbusname ? $txspbusname : '---';?> <?php echo $txspTelNo ? ' ('.$txspTelNo.')' : '';?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Business Address</div>
					<div class="data-value"><?php echo $txtspbusadd ? $txtspbusadd : '---';?></div>
				</div>
			</div>

			<h5 style="margin-bottom: 10px; font-weight:700;">Children</h5>
			<table class="custom-table">
				<thead>
					<tr>
						<th width="50">#</th>
						<th>Name</th>
						<th width="30%">Date of Birth</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countChild=0;
				$qChild = $db->select('emp_child','*',array('emp_id'=>$eid));
				while($rChild = $db->fetch_array($qChild)):
					$countChild++;
				?>
					<tr>
						<td><?php echo $countChild;?></td>
						<td><strong><?php echo $rChild['ec_fname'].' '.$rChild['ec_mname'].' '.$rChild['ec_lname']; echo ($rChild['ec_extname']) ? ', '.$rChild['ec_extname'] : '';?></strong></td>
						<td><?php echo functions::datearr($rChild['ec_bdate']);?> <span class="badge-status">(Age: <?php echo functions::year_diff($rChild['ec_bdate'],date('Y-m-d'));?>)</span></td>
					</tr>
				<?php endwhile;
				if($countChild==0){
					echo '<tr><td colspan="3" class="no-data">--- No Children Recorded ---</td></tr>';
				}
				?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 3: Educational Background -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon book icon-white"></i> III. EDUCATIONAL BACKGROUND</span>
			<a id="lnkfedback" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_educ.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Educational Background')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th>Level</th>
						<th>Name of School</th>
						<th>Degree / Course</th>
						<th class="text-center">Year Graduated</th>
						<th>Units Earned</th>
						<th class="text-center">Inclusive Dates</th>
						<th>Honors Received</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countAllLevel=0;
				$arrLevel = array('ELEMENTARY','SECONDARY','VOCATIONAL','COLLEGE','GRADUATE');
				foreach($arrLevel as $level):
					$qCol = $db->select('emp_education','*',array('emp_id'=>$eid,'ee_level'=>$level));
					while($rCol = $db->fetch_array($qCol)):
						$countAllLevel++;
				?>
					<tr>
						<td><span class="badge-status"><?php echo $rCol['ee_level']?></span></td>
						<td><strong><?php echo ucwords(strtolower($rCol['ee_school']))?></strong></td>
						<td><?php echo ucwords(strtolower($rCol['ee_course']))?></td>
						<td class="text-center"><?php echo $rCol['ee_year_grad']?></td>
						<td><?php echo $rCol['ee_highest_level']?></td>
						<td class="text-center"><?php echo $rCol['ee_date_from'].' - '.$rCol['ee_date_to'];?></td>
						<td><?php echo ucwords(strtolower($rCol['ee_honors']))?></td>
					</tr>
				<?php
					endwhile;
				endforeach;
				if($countAllLevel==0){
					echo '<tr><td colspan="7" class="no-data">--- No Educational Record ---</td></tr>';
				}
				?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 4: PRC Licenses -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon certificate icon-white"></i> IV. PRC ISSUED LICENSE(S)</span>
			<a id="lnkprc" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_prc.php?eid=<?php echo functions::encode($eid)?>&fromED=t','PRC ISSUED LICENSE(S)')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th>Board / Bar Exam</th>
						<th class="text-center">Rating</th>
						<th class="text-center">Exam Date</th>
						<th>Exam Place</th>
						<th class="text-center">License No.</th>
						<th class="text-center">Release Date</th>
					</tr>
				</thead>
				<tbody>
				<?php
				 $countLicenses=0;
				 $q = $db->select('emp_prc_license','*',array('emp_id'=>$eid),'ORDER BY el_exam_date');
				 while($r = $db->fetch_array($q)):
				 	$countLicenses++;
				 ?>
					<tr>
						<td><strong><?php echo $r['el_title']?></strong></td>
						<td class="text-center"><?php echo $r['el_rating']?></td>
						<td class="text-center"><?php echo functions::datearr($r['el_exam_date'])?></td>
						<td><?php echo $r['el_exam_place']?></td>
						<td class="text-center"><?php echo $r['el_no']?></td>
						<td class="text-center"><?php echo functions::datearr($r['el_date_release'])?></td>
					</tr>
				<?php endwhile;
				if($countLicenses==0){
					echo '<tr><td colspan="6" class="no-data">--- No PRC Record ---</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 5: Work Experience -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon briefcase icon-white"></i> V. WORK EXPERIENCES</span>
			<a id="lnkworkexp" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_work_exp.php?eid=<?php echo functions::encode($eid)?>&fromED=t','WORK EXPERIENCES')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th class="text-center">Inclusive Dates</th>
						<th>Position</th>
						<th>Department / Office / Company</th>
						<th class="text-center">Years in Service</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countYears=0;
				$countWrkExp=0;
				$q = $db->select('emp_work_exp','*',array('emp_id'=>$eid),'ORDER BY ewe_date_from DESC');
				while($r = $db->fetch_array($q)):
					$countWrkExp++;
					$countYears +=functions::year_diff($r['ewe_date_from'],$r['ewe_date_to']);
				?>
					<tr>
						<td class="text-center"><?php echo functions::datearr($r['ewe_date_from']).' - '.functions::datearr($r['ewe_date_to']);?></td>
						<td><strong><?php echo $r['ewe_position']?></strong></td>
						<td><?php echo $r['ewe_company']?></td>
						<td class="text-center"><?php echo functions::year_diff($r['ewe_date_from'],$r['ewe_date_to']);?></td>
					</tr>
				<?php endwhile;
				if($countWrkExp==0){
					echo '<tr><td colspan="4" class="no-data">--- No Work Experience Record ---</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 6: Relevant Trainings -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon list-alt icon-white"></i> VI. RELEVANT TRAININGS</span>
			<a id="lnkreltraining" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_training.php?eid=<?php echo functions::encode($eid)?>&fromED=t','RELEVANT TRAININGS')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th>Seminar / Workshop Title</th>
						<th class="text-center">Dates</th>
						<th class="text-center">Hours</th>
						<th>Conducted / Sponsored By</th>
						<th class="text-center">Certificate</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countHours=0;$countTraining=0;
				$q = $db->select('emp_training','*',array('emp_id'=>$eid,'et_type'=>'training'),'ORDER BY et_date_from DESC');
				while($r = $db->fetch_array($q)):
					$countTraining++;
					if($r['et_no_hour'])
						$countHours+=is_numeric($r['et_no_hour']) ? $r['et_no_hour'] : 0;
				?>
					<tr>
						<td><strong><?php echo $r['et_title']?></strong></td>
						<td class="text-center"><?php echo functions::datearr($r['et_date_from']).' - '.functions::datearr($r['et_date_to']);?></td>
						<td class="text-center"><?php echo $r['et_no_hour'];?></td>
						<td><?php echo $r['et_sponsored_by']?></td>
						<td class="text-center">
							<?php
							$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$r['cert_id']));
							$file = ($fileName) ? $fileName : '';
							if($file){?>
								<a id="vw<?php echo $r['et_id']?>" style="cursor: pointer;" class="thickbox" onclick="showThis(this.id,'employee_cert_view.php?crtID=<?php echo functions::encode($r['cert_id'])?>','View Certificate','1')">
									<img height="50" width="50" style="border-radius:4px; border:1px solid #ccc;" src="../img_emp/<?php echo $file;?>">
								</a>
							<?php }else{ echo '<span class="text-muted">None</span>'; } ?>
						</td>
					</tr>
				<?php endwhile;
				if($countTraining==0){
					echo '<tr><td colspan="5" class="no-data">--- No Training Record ---</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 7: Special Licenses -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon file icon-white"></i> VII. SPECIAL LICENSES</span>
			<a id="lnkaddlicense" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_license.php?eid=<?php echo functions::encode($eid)?>&fromED=t','SPECIAL LICENSES')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th>Title</th>
						<th>License No.</th>
						<th class="text-center">Date Issued</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countSpeLi=0;
				$q = $db->select('emp_special_license','*',array('emp_id'=>$eid),'ORDER BY esl_date DESC,esl_title');
				while($r = $db->fetch_array($q)):
					$countSpeLi++;
				?>
					<tr>
						<td><strong><?php echo $r['esl_title']?></strong></td>
						<td><?php echo $r['esl_no']?></td>
						<td class="text-center"><?php echo functions::datearr($r['esl_date'])?></td>
					</tr>
				<?php endwhile;
				if($countSpeLi==0){
					echo '<tr><td colspan="3" class="no-data">--- No Special Licenses Record ---</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 8: Special Awards -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon star icon-white"></i> VIII. SPECIAL AWARDS AND RECOGNITIONS</span>
			<a id="lnkspecaward" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_spec_award.php?eid=<?php echo functions::encode($eid)?>&fromED=t','SPECIAL AWARD AND RECOGNITION')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th>Title / Recognition</th>
						<th class="text-center">Date Received</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countAward=0;
				$q = $db->select('emp_special_award','*',array('emp_id'=>$eid),'ORDER BY esa_date');
				while($r = $db->fetch_array($q)):
					$countAward++;
				?>
					<tr>
						<td><strong><?php echo ucwords(strtolower($r['esa_title']))?></strong></td>
						<td class="text-center"><?php echo functions::datearr($r['esa_date'])?></td>
					</tr>
				<?php endwhile;
				if($countAward==0){
					echo '<tr><td colspan="2" class="no-data">--- No Awards Record ---</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 9: Seminars -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon tasks icon-white"></i> IX. TRAININGS / SEMINARS</span>
			<a id="lnktrainingseminar" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_seminar.php?eid=<?php echo functions::encode($eid)?>&fromED=t','TRAININGS / SEMINAR')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th>Seminar / Course</th>
						<th class="text-center">Dates</th>
						<th class="text-center">Hours</th>
						<th>Conducted / Sponsored By</th>
						<th class="text-center">Certificate</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countHours=0;$countSeminar=0;
				$q = $db->select('emp_training','*',array('emp_id'=>$eid,'et_type'=>'seminar'),'ORDER BY et_date_from DESC');
				while($r = $db->fetch_array($q)):
					$countHours+=is_numeric($r['et_no_hour']) ? $r['et_no_hour'] : 0;
					$countSeminar++;
				?>
					<tr>
						<td><strong><?php echo $r['et_title']?></strong></td>
						<td class="text-center"><?php echo functions::datearr($r['et_date_from']).' - '.functions::datearr($r['et_date_to']);?></td>
						<td class="text-center"><?php echo $r['et_no_hour'];?></td>
						<td><?php echo $r['et_sponsored_by']?></td>
						<td class="text-center">
							<?php
							$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$r['cert_id']));
							$file = ($fileName) ? $fileName : '';
							if($file){ ?>
								<a id="vw<?php echo $r['et_id']?>" style="cursor: pointer;" class="thickbox" onclick="showThis(this.id,'employee_cert_view.php?crtID=<?php echo functions::encode($r['cert_id'])?>','View Certificate','1')">
									<img height="50" width="50" style="border-radius:4px; border:1px solid #ccc;" src="../img_emp/<?php echo $file;?>">
								</a>
							<?php }else{ echo '<span class="text-muted">None</span>'; } ?>
						</td>
					</tr>
				<?php endwhile;
				if($countSeminar==0){
					echo '<tr><td colspan="5" class="no-data">--- No Seminar Record ---</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 10: References -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon font icon-white"></i> REFERENCES</span>
			<a id="lnkreferences" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_references.php?eid=<?php echo functions::encode($eid)?>&fromED=t','REFERENCES')">
				<i class="halflings-icon pencil icon-white"></i> Edit
			</a>
		</div>
		<div class="pds-card-body" style="padding:0;">
			<table class="custom-table">
				<thead>
					<tr>
						<th>Name</th>
						<th>Address</th>
						<th>Contact No.</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$countReferences=0;
				$q = $db->select('emp_references','*',array('emp_id'=>$eid),'ORDER BY er_name');
				while($r = $db->fetch_array($q)):
					$countReferences++;
				?>
					<tr>
						<td><strong><?php echo $r['er_name']?></strong></td>
						<td><?php echo $r['er_address']?></td>
						<td><?php echo $r['er_tel_no']?></td>
					</tr>
				<?php endwhile;
				if($countReferences==0){
					echo '<tr><td colspan="3" class="no-data">--- No References Record ---</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Section 11: Work Settings & Duty Schedule -->
	<div class="pds-card">
		<div class="pds-card-header">
			<span><i class="halflings-icon cog icon-white"></i> WORK DETAILS & ATTENDANCE</span>
			<a id="adcCTC" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_ctc.php?eid=<?php echo functions::encode($eid)?>&fromED=t','CTC Details')">
				<i class="halflings-icon pencil icon-white"></i> CTC Details
			</a>
		</div>
		<div class="pds-card-body">
			
			<div class="data-grid" style="margin-bottom:20px;">
				<div class="data-item">
					<div class="data-label">CTC Number</div>
					<div class="data-value"><?php echo $ctc_no ? $ctc_no : '---';?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Issued At</div>
					<div class="data-value"><?php echo $ctc_issue_place ? $ctc_issue_place : '---';?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Issued On</div>
					<div class="data-value"><?php echo functions::datearr($ctc_issue_date);?></div>
				</div>
				<div class="data-item">
					<div class="data-label">Date Accomplished</div>
					<div class="data-value"><?php echo functions::datearr($date_accomplished);?></div>
				</div>
			</div>

			<hr style="margin:20px 0; border-top:1px solid #e9ecef;">

			<div class="data-item full-width" style="margin-bottom:20px;">
				<div class="data-label">Department / Position</div>
				<div class="data-value" style="margin-top:5px;">
					<?php
					$qDep = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$eid),'AND ep.dp_id=dp.dp_id ORDER BY dp.pos_name');
					$countDep = $db->num_rows($qDep);
					if($countDep>1){
					?>
						<table class="custom-table" style="margin-top:5px;">
							<thead>
								<tr>
									<th>Department</th>
									<th>Position</th>
								</tr>
							</thead>
							<tbody>
							<?php while($rDep = $db->fetch_array($qDep)): ?>
								<tr>
									<td><?php echo $db->getValue('department','dep_name',array('dep_id'=>$rDep['dep_id']))?></td>
									<td><strong><?php echo $rDep['pos_name']?></strong></td>
								</tr>
							<?php endwhile;?>
							</tbody>
						</table>
					<?php } else if($countDep==1){
						$rDep = $db->fetch_array($qDep);
					?>
						<strong><?php echo $db->getValue('department','dep_name',array('dep_id'=>$rDep['dep_id']));?></strong> — <?php echo $rDep['pos_name']?>
					<?php } else { echo '---'; }?>
				</div>
			</div>

			<div class="data-item full-width" style="margin-bottom:20px;">
				<div class="data-label" style="display:flex; justify-content:space-between; align-items:center;">
					<span>Project Assignments</span>
					<a id="adcProjAssgnmnt" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_assign.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Employee Detail')"><i class="halflings-icon pencil"></i> Manage</a>
				</div>
				<div class="data-value" style="margin-top:5px;">
					<?php
					$qSite = $db->select('emp_site_assign esa, project proj','*',array('emp_id'=>$eid),'AND esa.proj_id=proj.proj_id ORDER BY proj.date_start,proj.proj_name');
					$countSite = $db->num_rows($qSite);
					if($countSite){
					?>
					<table class="custom-table">
						<thead>
							<tr>
								<th>Site / Project</th>
								<th class="text-center">Duration</th>
								<th class="text-center">Status</th>
							</tr>
						</thead>
						<tbody>
						<?php
						while($rSite = $db->fetch_array($qSite)):
							$current='';
							if($rSite['date_started'] && $rSite['date_ended']){
								if($rSite['date_started']<=date('Y-m-d') && $rSite['date_ended']>=date('Y-m-d'))
									$current='current';
							} else if($rSite['date_started']){
								if( $rSite['date_started']<=date('Y-m-d') )
									$current='current';
							} else if($rSite['date_ended']){
								if( $rSite['date_ended']>=date('Y-m-d') )
									$current='current';
							}
						?>
							<tr>
								<td><strong><?php echo strtoupper($rSite['proj_name'])?></strong></td>
								<td class="text-center"><?php echo functions::datearr($rSite['date_started']).' - '.functions::datearr($rSite['date_ended']); ?></td>
								<td class="text-center"><?php echo ($current) ? '<span class="badge-status badge-active">Current Assignment</span>' : '';?></td>
							</tr>
						<?php endwhile;?>
						</tbody>
					</table>
					<?php } else { echo '<div class="no-data">No Assignment</div>'; } ?>
				</div>
			</div>

			<div class="data-item full-width" style="margin-bottom:20px;">
				<div class="data-label" style="display:flex; justify-content:space-between; align-items:center;">
					<span>Work Status</span>
					<a id="adcworkstat" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_work_stat.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Work Status')"><i class="halflings-icon pencil"></i> Manage</a>
				</div>
				<div class="data-value" style="margin-top:5px;">
					<?php
					$qStat = $db->select('emp_work_status','*',array('emp_id'=>$eid,'ews_stat'=>$work_status),'ORDER BY ews_date DESC');
					$rStat = $db->fetch_array($qStat);
					$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
					$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <span class="badge-status">(Project Based)</span>' : '';
					$wrkDate = (isset($rStat['ews_date'])) ? functions::datearr($rStat['ews_date']) : '-----';
					?>
					<strong><?php echo $wrkStat;?></strong> <?php echo $projBased;?> <span class="text-muted">(Effective: <?php echo $wrkDate?>)</span>
				</div>
			</div>

			<div class="data-item full-width" style="margin-bottom:20px;">
				<div class="data-label" style="display:flex; justify-content:space-between; align-items:center;">
					<span>Duty Schedule</span>
					<a id="adcDuty" href="#" class="thickbox" onclick="showThis(this.id,'employee_add_duty.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Manage Duty Hours')"><i class="halflings-icon pencil"></i> Manage</a>
				</div>
				<div class="data-value" style="margin-top:5px;">
					<?php if($has_attendance){?>
					<table class="custom-table text-center">
						<thead>
							<tr>
								<th>Day</th>
								<th class="text-center" colspan="2">Morning</th>
								<th class="text-center" colspan="2">Afternoon</th>
							</tr>
							<tr>
								<th></th>
								<th class="text-center">In</th>
								<th class="text-center">Out</th>
								<th class="text-center">In</th>
								<th class="text-center">Out</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><strong>Monday</strong></td>
								<td><?php echo functions::MilToTwelve($monAmIn);?></td>
								<td><?php echo functions::MilToTwelve($monAmOut);?></td>
								<td><?php echo functions::MilToTwelve($monPmIn);?></td>
								<td><?php echo functions::MilToTwelve($monPmOut);?></td>
							</tr>
							<tr>
								<td><strong>Tuesday</strong></td>
								<td><?php echo functions::MilToTwelve($tueAmIn);?></td>
								<td><?php echo functions::MilToTwelve($tueAmOut);?></td>
								<td><?php echo functions::MilToTwelve($tuePmIn);?></td>
								<td><?php echo functions::MilToTwelve($tuePmOut);?></td>
							</tr>
							<tr>
								<td><strong>Wednesday</strong></td>
								<td><?php echo functions::MilToTwelve($wedAmIn);?></td>
								<td><?php echo functions::MilToTwelve($wedAmOut);?></td>
								<td><?php echo functions::MilToTwelve($wedPmIn);?></td>
								<td><?php echo functions::MilToTwelve($wedPmOut);?></td>
							</tr>
							<tr>
								<td><strong>Thursday</strong></td>
								<td><?php echo functions::MilToTwelve($thuAmIn);?></td>
								<td><?php echo functions::MilToTwelve($thuAmOut);?></td>
								<td><?php echo functions::MilToTwelve($thuPmIn);?></td>
								<td><?php echo functions::MilToTwelve($thuPmOut);?></td>
							</tr>
							<tr>
								<td><strong>Friday</strong></td>
								<td><?php echo functions::MilToTwelve($friAmIn);?></td>
								<td><?php echo functions::MilToTwelve($friAmOut);?></td>
								<td><?php echo functions::MilToTwelve($friPmIn);?></td>
								<td><?php echo functions::MilToTwelve($friPmOut);?></td>
							</tr>
							<tr>
								<td><strong>Saturday</strong></td>
								<td><?php echo functions::MilToTwelve($satAmIn);?></td>
								<td><?php echo functions::MilToTwelve($satAmOut);?></td>
								<td><?php echo functions::MilToTwelve($satPmIn);?></td>
								<td><?php echo functions::MilToTwelve($satPmOut);?></td>
							</tr>
						</tbody>
					</table>
					<?php } else { ?>
						<div class="no-data">Duty Hours Not Applicable</div>
					<?php }?>
				</div>
			</div>

			<div class="data-grid">
				<div class="data-item">
					<div class="data-label">Salary Details</div>
					<div class="data-value">
						<?php if($es_type=='fixed'){ ?>
							<strong>Daily Fixed Wage:</strong> <?php echo $txSalDay;?><br>
							<small class="text-muted">Started: <?php echo functions::datearr($txSalDate);?></small>
						<?php }elseif($es_type=='flexible'){ ?>
							<strong>Monthly:</strong> <?php echo $txSalMonth;?><br>
							<small class="text-muted">Started: <?php echo functions::datearr($txSalDate);?></small>
						<?php }else{ echo 'Salary Type Not Specified.'; } ?>
						<div style="margin-top:10px;">
							<a id="adcSal" href="#" class="btn btn-info btn-mini thickbox" onclick="showThis(this.id,'employee_manage_salary.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Salary Details')">Manage Salary</a>
						</div>
					</div>
				</div>

				<div class="data-item">
					<div class="data-label">Salary Adjustment</div>
					<div class="data-value" style="margin-top:10px;">
						<a id="btnAdjustment" href="#" class="btn btn-info btn-mini thickbox" onclick="showThis(this.id,'payroll_deduction_reference_detail.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Salary Adjustment Details','1')">Manage Adjustments</a>
					</div>
				</div>

				<div class="data-item">
					<div class="data-label">Leave Record</div>
					<div class="data-value" style="margin-top:10px;">
						<a id="btnleaveRec" href="#" class="btn btn-info btn-mini thickbox" onclick="showThis(this.id,'employee_leave.php?eid=<?php echo functions::encode($eid)?>&fromED=t','Leave Detail','1')">View Leave Record</a>
					</div>
				</div>
			</div>

		</div>
	</div>

</div>

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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
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