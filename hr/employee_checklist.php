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
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdate='';$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';
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
	endwhile;
}
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
#$file = ($fileName) ? $fileName : 'blank-pic.png';
$file = ($fileName && file_exists('../img_emp/'.$fileName) ) ? $fileName : 'blank-pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Check List</title>
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
		body {
			background-color: #f8fafc;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			color: #1e293b;
			padding: 20px;
		}

		.checklist-container {
			max-width: 1100px;
			margin: 0 auto;
		}

		/* Header & Profile Card Styling */
		.page-header-title {
			text-align: center;
			margin-bottom: 24px;
		}

		.page-header-title h2 {
			font-size: 24px;
			font-weight: 700;
			color: #0f172a;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin: 0;
		}

		.profile-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
			padding: 24px;
			margin-bottom: 24px;
			display: flex;
			align-items: center;
			gap: 30px;
		}

		@media (max-width: 768px) {
			.profile-card {
				flex-direction: column;
				text-align: center;
			}
		}

		.profile-avatar-wrapper {
			flex-shrink: 0;
		}

		.profile-avatar {
			width: 140px;
			height: 140px;
			border-radius: 12px;
			object-fit: cover;
			border: 3px solid #f1f5f9;
			box-shadow: 0 4px 8px rgba(0,0,0,0.06);
		}

		.profile-info-grid {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 16px;
			width: 100%;
		}

		@media (max-width: 600px) {
			.profile-info-grid {
				grid-template-columns: repeat(1, 1fr);
			}
		}

		.info-item {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			padding: 10px 14px;
			border-radius: 8px;
		}

		.info-label {
			font-size: 10px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: #64748b;
			display: block;
			margin-bottom: 4px;
		}

		.info-value {
			font-size: 14px;
			font-weight: 600;
			color: #0f172a;
		}

		/* Enhanced Table Styling */
		.table-wrapper {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
			overflow: hidden;
		}

		.custom-table {
			width: 100%;
			border-collapse: collapse;
			margin: 0;
		}

		.custom-table thead tr {
			background-color: #f1f5f9 !important;
		}

		.custom-table th {
			padding: 14px 16px;
			font-size: 11px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: #475569;
			border-bottom: 2px solid #e2e8f0;
			position: sticky;
			top: 0;
			z-index: 10;
		}

		.custom-table td {
			padding: 12px 16px;
			font-size: 13px;
			color: #334155;
			border-bottom: 1px solid #f1f5f9;
			vertical-align: middle;
		}

		.custom-table tbody tr:last-child td {
			border-bottom: none;
		}

		.custom-table tbody tr:hover td {
			background-color: #f0f9ff !important;
		}

		/* Status Badges */
		.badge-status {
			display: inline-block;
			padding: 4px 12px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 700;
			text-transform: uppercase;
		}

		.badge-done {
			background-color: #dcfce7;
			color: #15803d;
			border: 1px solid #bbf7d0;
		}

		.badge-not-done {
			background-color: #fef2f2;
			color: #b91c1c;
			border: 1px solid #fecaca;
		}

		/* Interactive Document View Button */
		.btn-doc-view {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			color: #0284c7;
			font-weight: 600;
			font-size: 12px;
			text-decoration: none !important;
			padding: 4px 8px;
			border-radius: 4px;
			transition: background 0.2s ease;
		}

		.btn-doc-view:hover {
			background: #e0f2fe;
			color: #0369a1;
		}

		/* Action Edit Button Override */
		.btn-edit-action {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 30px;
			height: 30px;
			border-radius: 6px;
			background: #fef3c7;
			color: #d97706 !important;
			border: 1px solid #fde68a;
			transition: all 0.2s ease;
		}

		.btn-edit-action:hover {
			background: #d97706;
			color: #ffffff !important;
			border-color: #d97706;
		}
	</style>
</head>
<body>

<div class="checklist-container">
	<div class="page-header-title">
		<h2>201 File Checklist</h2>
	</div>

	<!-- Profile Information Card -->
	<div class="profile-card">
		<div class="profile-avatar-wrapper">
			<img class="profile-avatar" src="../img_emp/<?php echo $file;?>" alt="Employee Picture">
		</div>
		<div class="profile-info-grid">
			<div class="info-item">
				<span class="info-label">ID Number</span>
				<span class="info-value"><?php echo $txtEmpNo ?: '--';?></span>
			</div>
			<div class="info-item">
				<span class="info-label">Last Name</span>
				<span class="info-value"><?php echo $txtlName ?: '--';?></span>
			</div>
			<div class="info-item">
				<span class="info-label">First Name</span>
				<span class="info-value"><?php echo $txtfName ?: '--';?></span>
			</div>
			<div class="info-item">
				<span class="info-label">Middle Name</span>
				<span class="info-value"><?php echo $txtmName ?: '--';?></span>
			</div>
			<div class="info-item">
				<span class="info-label">Suffix</span>
				<span class="info-value"><?php echo $txtExtName ?: '--';?></span>
			</div>
			<div class="info-item">
				<span class="info-label">Nickname</span>
				<span class="info-value"><?php echo $txtNickName ?: '--';?></span>
			</div>
		</div>
	</div>

	<!-- Checklist Data Table -->
	<div class="table-wrapper">
		<table id="tblist" class="custom-table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?>">
			<thead>
				<tr>
					<th width="30%">Checklist Requirement</th>
					<th width="15%" style="text-align: center;">Status</th>
					<th width="20%" style="text-align: center;">File Attachment</th>
					<th width="25%" style="text-align: center;">Remarks</th>
					<th width="10%" style="text-align: center;">Manage</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$countHours=0;$countSeminar=0;$count=0;
				$q = $db->select('emp_201_reference','*',array(),'ORDER BY e2r_name');
				while($r = $db->fetch_array($q)):
					$rID = 0;
					$refID = $r['e2r_id'];
					$status = $db->getValue('emp_201','e2_status',array('emp_id'=>$eid,'e2r_id'=>$refID));
					$e201_file = $db->getValue('emp_201','e2_file',array('emp_id'=>$eid,'e2r_id'=>$refID));
					$remarks = $db->getValue('emp_201','remarks',array('emp_id'=>$eid,'e2r_id'=>$refID));
				?>
				<tr id="rw<?php echo $refID?>">
					<td><strong><?php echo $r['e2r_name'];?></strong></td>
					<td style="text-align: center;">
						<?php if($status): ?>
							<span class="badge-status badge-done">Done</span>
						<?php else: ?>
							<span class="badge-status badge-not-done">Not Done</span>
						<?php endif; ?>
					</td>
					<td style="text-align: center;">
						<?php if($db->getValue('emp_201_docs','count(*)',array('e2r_id'=>$refID,'emp_id'=>$eid))){ ?>
							<a id="vwpcs<?php echo $count++?>" style="cursor: pointer;" class="btn-doc-view thickbox" title="Document View" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage_view.php?e2r_id=<?php echo functions::encode($refID);?>&eid=<?php echo functions::encode($eid);?>','View Document','1')">
								<i class="halflings-icon file"></i> View Document
							</a>
						<?php } else { ?>
							<span style="color: #cbd5e1;">--</span>
						<?php } ?>
					</td>
					<td style="text-align: center; color: #64748b;"><?php echo $remarks ?: '<span style="color:#cbd5e1;">--</span>';?></td>
					<td style="text-align: center;">
						<a id="edit<?php echo $count++?>" class="btn-edit-action thickbox" title="Modify Employee Checklist Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist_manage.php?eid=<?php echo functions::encode($eid);?>&refID=<?php echo functions::encode($refID);?>','Employee Checklist Update')">
							<i class="halflings-icon pencil"></i>
						</a>
					</td>
				</tr>
				<?php endwhile;?>
			</tbody>
		</table>
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

<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid #10b981');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>
<!-- end: JavaScript-->
</body>
</html>