<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$filename='';$uploaded_file='';$filetype='';

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$proj_id = $rEatID['proj_id'] ?? NULL;
$date_start = $rEatID['date_start'] ?? NULL;
$date_end = $rEatID['date_end'] ?? NULL;
$worker_type = $rEatID['payroll_type'] ?? NULL;
$note = $rEatID['note'] ?? NULL;

$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));
echo $db->last_query;
$frmSummary = (isset($_REQUEST['frm']) && !empty($_REQUEST['frm']) ) ? $_REQUEST['frm'] : 0;
$personnel = $db->getValue('emp_attendance_personnel','count(eat_id)',array('eat_id'=>$eatid));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Upload Option</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">

	<!-- Earthy Brown Theme & Vertical Layout Styling -->
	<style type="text/css">
		/* Card Header */
		.card-header-custom {
			background-color: #4A3B32;
			color: #F7F5F0;
			padding: 14px 20px;
			border-top-left-radius: 6px;
			border-top-right-radius: 6px;
			margin-bottom: 0;
			border-bottom: 3px solid #8C6D58;
		}
		.card-header-custom h2 {
			color: #F7F5F0 !important;
			margin: 0;
			font-size: 16px;
			font-weight: 600;
			letter-spacing: 0.5px;
			line-height: 1.2;
		}

		/* Main Grid Layout: Content stays Left, Wizard pushed to Far Right */
		.page-layout-grid {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			padding: 20px 0;
			gap: 30px;
		}
		
		/* Expanded Main Content Area */
		.main-content-area {
			flex: 1;
			max-width: 80%;
			min-width: 0;
		}

		/* Sidebar anchored to Far Right */
		.sidebar-wizard-area {
			width: 220px;
			flex-shrink: 0;
			position: sticky;
			top: 20px;
			margin-left: auto;
		}

		/* Vertical Process Wizard Navigation */
		.process-wizard-vertical {
			display: flex;
			flex-direction: column;
			margin: 0;
			padding: 20px 15px;
			list-style: none;
			background: #FAF8F5;
			border: 1px solid #E6E1DA;
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(74, 59, 50, 0.05);
		}
		.wizard-title {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.8px;
			font-weight: 800;
			color: #8C6D58;
			margin-bottom: 15px;
			padding-bottom: 8px;
			border-bottom: 2px solid #E0DCD5;
			text-align: center;
		}
		.process-step {
			position: relative;
			padding-bottom: 25px;
		}
		.process-step:last-child {
			padding-bottom: 0;
		}

		/* Continuous 100% Vertical Connector Line */
		.process-step:not(:last-child)::after {
			content: '';
			position: absolute;
			left: 20px;
			top: 20px;
			bottom: -25px;
			width: 3px;
			background-color: #E6E1DA;
			z-index: 1;
			transform: translateX(-50%);
			transition: background-color 0.3s ease;
		}
		.process-step.active:not(:last-child)::after,
		.process-step.complete:not(:last-child)::after {
			background-color: #8C6D58;
		}

		.process-step a {
			display: flex;
			align-items: center;
			gap: 12px;
			text-decoration: none;
			position: relative;
			z-index: 2;
			transition: all 0.25s ease-in-out;
		}
		
		/* Step Icon Styling */
		.step-icon {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			background-color: #FFFFFF;
			color: #A3968A;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 600;
			font-size: 15px;
			border: 2px solid #E6E1DA;
			box-shadow: 0 2px 4px rgba(0,0,0,0.03);
			transition: all 0.25s ease-in-out;
			flex-shrink: 0;
		}

		.step-label {
			font-size: 13px;
			font-weight: 600;
			color: #8C7E72;
			transition: all 0.25s ease-in-out;
			line-height: 1.2;
		}

		/* Active Step Styling */
		.process-step.active .step-icon {
			background-color: #4A3B32;
			color: #FFFFFF;
			font-size: 16px;
			border: 3px solid #8C6D58;
			transform: scale(1.12);
			box-shadow: 0 0 0 4px rgba(140, 109, 88, 0.25), 0 4px 10px rgba(74, 59, 50, 0.3);
			animation: pulse-ring 2.5s infinite;
		}
		@keyframes pulse-ring {
			0% {
				box-shadow: 0 0 0 0px rgba(140, 109, 88, 0.4), 0 4px 10px rgba(74, 59, 50, 0.3);
			}
			70% {
				box-shadow: 0 0 0 7px rgba(140, 109, 88, 0), 0 4px 10px rgba(74, 59, 50, 0.3);
			}
			100% {
				box-shadow: 0 0 0 0px rgba(140, 109, 88, 0), 0 4px 10px rgba(74, 59, 50, 0.3);
			}
		}

		.process-step.active .step-label {
			color: #4A3B32;
			font-weight: 800;
			font-size: 13px;
		}

		/* Completed State */
		.process-step.complete .step-icon {
			background-color: #FAF8F5;
			color: #8C6D58;
			border-color: #8C6D58;
		}
		.process-step.complete .step-label {
			color: #8C6D58;
		}

		/* Hover Effects */
		.process-step:not(.active):not(.disabled):hover .step-icon {
			background-color: #4A3B32;
			color: #FFFFFF;
			border-color: #4A3B32;
			transform: scale(1.08);
			box-shadow: 0 4px 10px rgba(74, 59, 50, 0.2);
		}
		.process-step:not(.active):not(.disabled):hover .step-label {
			color: #4A3B32;
			font-weight: 700;
		}

		.process-step.disabled a {
			cursor: not-allowed;
			opacity: 0.45;
		}

		/* Form Container */
		.form-card-container {
			background: #FAF8F5;
			border: 1px solid #E6E1DA;
			border-radius: 6px;
			padding: 25px 30px;
			box-shadow: 0 2px 6px rgba(74, 59, 50, 0.05);
			box-sizing: border-box;
		}

		/* Unified Summary Card Container */
		.unified-summary-card {
			background: #FFFFFF;
			border: 1px solid #E2DDD5;
			border-left: 4px solid #4A3B32;
			border-radius: 6px;
			padding: 20px;
			margin-bottom: 25px;
			box-shadow: 0 2px 5px rgba(74, 59, 50, 0.04);
		}

		/* Top Row: Full Width for Long Project Title */
		.summary-row-top {
			padding-bottom: 15px;
			margin-bottom: 15px;
			border-bottom: 1px dashed #E0DCD5;
		}

		/* Bottom Row: 3 Columns for Period, Worker Type, and Note */
		.summary-row-bottom {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 20px;
		}

		.summary-item {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.summary-item-bordered {
			padding-right: 15px;
			border-right: 1px dashed #E0DCD5;
		}

		.summary-label {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			font-weight: 700;
			color: #8C6D58;
		}
		
		.summary-value {
			font-size: 13px;
			font-weight: 600;
			color: #332B25;
			line-height: 1.4;
		}

		.summary-value-hero {
			font-size: 16px;
			font-weight: 700;
			color: #2C221E;
			word-break: break-word;
		}

		/* Badges & Tags */
		.badge-day-count {
			background-color: #EFECE6;
			color: #4A3B32;
			font-weight: 600;
			font-size: 11px;
			padding: 2px 8px;
			border-radius: 10px;
			border: 1px solid #D8D2C7;
			margin-left: 4px;
			display: inline-block;
			vertical-align: middle;
		}
		.badge-worker-type {
			display: inline-block;
			padding: 3px 10px;
			border-radius: 12px;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.3px;
		}
		.badge-type-admin {
			background-color: #E3EBF5;
			color: #2B4C7E;
			border: 1px solid #C4D5EB;
		}
		.badge-type-labor {
			background-color: #EAF3EB;
			color: #2D6A35;
			border: 1px solid #C5E2C8;
		}
		.badge-type-default {
			background-color: #EFECE6;
			color: #594C42;
			border: 1px solid #D8D2C7;
		}

		/* Version Selection Cards Grid */
		.version-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 20px;
			margin-top: 20px;
		}
		.version-card {
			background: #FFFFFF;
			border: 1px solid #E2DDD5;
			border-radius: 8px;
			padding: 16px;
			text-decoration: none !important;
			color: inherit;
			display: flex;
			flex-direction: column;
			align-items: center;
			transition: all 0.25s ease-in-out;
			box-shadow: 0 2px 6px rgba(0,0,0,0.04);
			overflow: hidden;
		}
		.version-card:hover {
			border-color: #8C6D58;
			transform: translateY(-4px);
			box-shadow: 0 8px 20px rgba(74, 59, 50, 0.15);
		}
		.version-card-header {
			font-size: 15px;
			font-weight: 700;
			color: #4A3B32;
			margin-bottom: 12px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		.version-card-img-wrapper {
			width: 100%;
			height: 320px;
			background-color: #FAF8F5;
			border-radius: 6px;
			overflow: hidden;
			display: flex;
			align-items: center;
			justify-content: center;
			border: 1px solid #EFECE6;
			padding: 8px;
			box-sizing: border-box;
		}
		.version-card-img-wrapper img {
			width: 100%;
			height: 100%;
			object-fit: contain;
			transition: transform 0.3s ease;
		}
		.version-card:hover .version-card-img-wrapper img {
			transform: scale(1.02);
		}

		.btn-earthy-info {
			background-color: #4A3B32 !important;
			color: #FFFFFF !important;
			border: 1px solid #362B24 !important;
			background-image: none !important;
			text-shadow: none !important;
		}
		.btn-earthy-info:hover {
			background-color: #362B24 !important;
			color: #FFFFFF !important;
		}

		/* Responsive Adjustments */
		@media (max-width: 992px) {
			.page-layout-grid {
				flex-direction: column-reverse;
				gap: 25px;
			}
			.main-content-area {
				max-width: 100%;
			}
			.sidebar-wizard-area {
				width: 100%;
				position: static;
				margin-left: 0;
			}
			.process-wizard-vertical {
				flex-direction: row;
				justify-content: space-between;
			}
			.process-step {
				padding-bottom: 0;
				flex: 1;
			}
			.process-step:not(:last-child)::after {
				display: none;
			}
			.summary-row-bottom {
				grid-template-columns: 1fr;
			}
			.summary-item-bordered {
				border-right: none;
				padding-right: 0;
				padding-bottom: 10px;
				border-bottom: 1px dashed #E0DCD5;
			}
			.summary-item-bordered:last-child {
				border-bottom: none;
				padding-bottom: 0;
			}
			.version-grid { 
				grid-template-columns: 1fr; 
			}
			.version-card-img-wrapper {
				height: 280px;
			}
		}
	</style>

	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<link rel="shortcut icon" href="../img/favicon.png">
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="card-header-custom">
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ATTENDANCE UPLOAD MANAGEMENT</h2>
		</div>
		<div class="box-content">
			<!-- Grid Layout Container -->
			<div class="page-layout-grid">
				
				<!-- Main Left Content Area -->
				<div class="main-content-area">
					<div class="form-card-container">
						<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #E0DCD5; padding-bottom: 10px; margin-bottom: 20px;">
							<h3 style="margin: 0; border: none; padding: 0;">Step 3: Select Attendance Log Format</h3>
							<?php if($frmSummary){?>
								<a id="icnReupload" href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-earthy-info btn-small" title="Back To Attendance Summary">
									<i class="icon-arrow-left icon-white"></i> Back To Summary
								</a>
							<?php }?>
						</div>

						<!-- Unified Metadata Card -->
						<div class="unified-summary-card">
							<!-- Full Row: Project / Department (Optimized for long text) -->
							<div class="summary-row-top">
								<div class="summary-item">
									<span class="summary-label">Project / Department</span>
									<span class="summary-value summary-value-hero"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></span>
								</div>
							</div>

							<!-- 3-Column Bottom Row: Period Cover, Worker Type, Note -->
							<div class="summary-row-bottom">
								
								<!-- Period Cover -->
								<div class="summary-item summary-item-bordered">
									<span class="summary-label">Period Cover</span>
									<span class="summary-value">
										<?php echo functions::datearr($date_start).' - '.functions::datearr($date_end); ?>
										<span class="badge-day-count"><?php echo functions::date_diff($date_start,$date_end,$includeDay1=1); ?> days</span>
									</span>
								</div>

								<!-- Worker Type -->
								<div class="summary-item summary-item-bordered">
									<span class="summary-label">Worker Type</span>
									<span class="summary-value">
										<?php 
										if($worker_type=="admin"){
											echo '<span class="badge-worker-type badge-type-admin">Office Personnel</span>';
										} elseif($worker_type=="labor"){
											echo '<span class="badge-worker-type badge-type-labor">Labor Group</span>';
										} else {
											echo '<span class="badge-worker-type badge-type-default">Undefined</span>';
										}
										?>
									</span>
								</div>

								<!-- Note / Remarks -->
								<div class="summary-item">
									<span class="summary-label">Note / Remarks</span>
									<span class="summary-value" style="font-style: italic; font-weight: normal; color: #55483F; word-break: break-word;">
										<?php echo ($note) ? $note : '<span style="color: #A3968A;">N/A</span>'; ?>
									</span>
								</div>

							</div>
						</div>

						<!-- File Version Selector Grid -->
						<div>
							<?php if($personnel){?>
								<div class="version-grid">
									<!-- Version 1 -->
									<a href="attendance_report_add_attlog_v1.php?eatid=<?php echo functions::encode($eatid)?>" class="version-card">
										<div class="version-card-header">Version 1</div>
										<div class="version-card-img-wrapper">
											<img src="../img/att_v1.jpg" alt="Attendance Log Version 1">
										</div>
									</a>

									<!-- Version 2 -->
									<a href="attendance_report_add_attlog_v2.php?eatid=<?php echo functions::encode($eatid)?>" class="version-card">
										<div class="version-card-header">Version 2</div>
										<div class="version-card-img-wrapper">
											<img src="../img/att_v2.jpg" alt="Attendance Log Version 2">
										</div>
									</a>

									<!-- Version 3 -->
									<a href="attendance_report_add_attlog_v3.php?eatid=<?php echo functions::encode($eatid)?>" class="version-card">
										<div class="version-card-header">Version 3</div>
										<div class="version-card-img-wrapper">
											<img src="../img/att_v3.jpg" alt="Attendance Log Version 3">
										</div>
									</a>

									<!-- Version 4 -->
									<a href="attendance_report_add_attlog_v4.php?eatid=<?php echo functions::encode($eatid)?>" class="version-card">
										<div class="version-card-header">Version 4</div>
										<div class="version-card-img-wrapper">
											<img src="../img/att_v4.JPG" alt="Attendance Log Version 4">
										</div>
									</a>

									<!-- Version 5 -->
									<a href="attendance_report_add_attlog_v5.php?eatid=<?php echo functions::encode($eatid)?>" class="version-card">
										<div class="version-card-header">Version 5</div>
										<div class="version-card-img-wrapper">
											<img src="../img/att_v5.jpg" alt="Attendance Log Version 5">
										</div>
									</a>
								</div>
							<?php } else { ?>
								<div class="alert alert-warning" style="text-align: center; margin-top: 15px; border-radius: 4px;">
									<strong>No Personnel Assigned!</strong> Please select or assign <a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>">personnel</a> first before uploading attendance logs.
								</div>
							<?php }?>
						</div>
					</div>
				</div>

				<!-- Right Vertical Process Wizard Navigation (Pinned to Very Right Edge) -->
				<div class="sidebar-wizard-area">
					<ul class="process-wizard-vertical">
						<div class="wizard-title">Process Steps</div>
						<li class="process-step complete">
							<a href="attendance_report_add_charge.php?eatid=<?php echo functions::encode($eatid); ?>">
								<span class="step-icon">1</span>
								<span class="step-label">Manage Details</span>
							</a>
						</li>
						<li class="process-step complete">
							<a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid); ?>">
								<span class="step-icon">2</span>
								<span class="step-label">Manage Personnel</span>
							</a>
						</li>
						<li class="process-step active">
							<a href="javascript:void(0)">
								<span class="step-icon">3</span>
								<span class="step-label">Upload Att. Log</span>
							</a>
						</li>
						<li class="process-step <?php echo ($attendance_detail) ? 'complete' : 'disabled'; ?>">
							<a href="<?php echo ($attendance_detail) ? 'attendance_summary_view.php?eatid='.functions::encode($eatid) : '#'; ?>">
								<span class="step-icon">4</span>
								<span class="step-label">Summary</span>
							</a>
						</li>
					</ul>
				</div>

			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->

<!-- JavaScript dependencies -->
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
<script src="../js/customx.js"></script>
<script src="../js/wxh.js"></script>
<script type="text/javascript">
$(window).ready(function(){
	$("#spinner").fadeOut("slow");
	$("#upload").click(function(){
		if($("#fileatt").val()){
			if(confirm('Do you want to upload the attendance log?')){
				$("#spinner").fadeIn();
				return true;
			}else{
				return false;
			}
		}
	});
});
</script>
</body>
</html>