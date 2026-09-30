<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$arrVal=array();
$searchVal='';
$qDisp = $db->select('employee','*',$arrVal,'ORDER BY lname');
if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	functions::sendTo(functions::pageName().'?emp='.functions::encode($searchVal));
	die();
}
$searchVal = ( isset($_REQUEST['emp']) ) ? functions::decode($_REQUEST['emp']) : '';
$qDisp = $db->select('employee','*',array('emp_id'=>$searchVal));
?>

<style>
	:root {
		--bg-canvas: #fcfaf8;
		--panel-bg: #ffffff;
		--border-subtle: #e7e0d8;
		--text-primary: #2c1d11;
		--text-muted: #78695c;
		
		/* Brown Theme Color Palette for Main Header Container */
		--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
		--theme-brown-primary: #7c401e;
		--theme-brown-hover: #5c2e14;
		--theme-brown-light: #f5ebe6;
	}

	/* Maximize Entire Width of Page */
	.page-full-wrapper {
		width: 100% !important;
		max-width: 100% !important;
		padding: 0 15px !important;
		box-sizing: border-box;
	}

	.card-panel {
		width: 100%;
		background: var(--panel-bg);
		border-radius: 16px;
		border: 1px solid var(--border-subtle);
		box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
		overflow: visible; /* Allows dropdown lists to render outside */
		box-sizing: border-box;
		margin-bottom: 25px;
	}

	/* Target Custom Card Header */
	.card-header-custom {
		background: var(--theme-brown-header);
		padding: 18px 25px;
		color: #ffffff;
		display: flex;
		align-items: center;
		justify-content: space-between;
		border-top-left-radius: 16px;
		border-top-right-radius: 16px;
	}

	.card-header-custom h2 {
		margin: 0;
		font-size: 18px;
		font-weight: 700;
		display: flex;
		align-items: center;
		gap: 10px;
		color: #ffffff;
	}

	.card-body-custom {
		padding: 25px;
		width: 100%;
		box-sizing: border-box;
		overflow: visible;
	}

	.emp-toolbar {
		display: flex;
		justify-content: flex-end;
		gap: 8px;
		margin-bottom: 15px;
	}
	.emp-search-bar {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px;
		margin-bottom: 20px;
		display: flex;
		justify-content: center;
		align-items: center;
		gap: 10px;
		position: relative;
		z-index: 50;
	}

	/* Full-Width Profile Container */
	.emp-profile-wrapper {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
		overflow: hidden;
		margin-top: 15px;
		width: 100%;
		transition: box-shadow 0.3s ease;
	}

	.emp-profile-wrapper:hover {
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
	}

	.emp-profile-header {
		background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
		color: #ffffff;
		padding: 24px 30px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 20px;
	}

	.emp-profile-main {
		display: flex;
		align-items: center;
		gap: 20px;
	}

	.emp-avatar-box {
		width: 90px;
		height: 90px;
		border-radius: 50%;
		background-color: #3b82f6;
		border: 4px solid rgba(255, 255, 255, 0.2);
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
		flex-shrink: 0;
		transition: transform 0.3s ease, border-color 0.3s ease;
	}

	.emp-avatar-box:hover {
		transform: scale(1.05);
		border-color: #38bdf8;
	}

	.emp-avatar-box img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.emp-avatar-initials {
		font-size: 32px;
		font-weight: 700;
		color: #ffffff;
		text-transform: uppercase;
	}

	.emp-header-info h2 {
		margin: 0;
		font-size: 22px;
		font-weight: 700;
		color: #ffffff;
		line-height: 1.2;
	}

	.emp-header-info .emp-id-tag {
		display: inline-block;
		background: rgba(255, 255, 255, 0.12);
		color: #38bdf8;
		padding: 3px 10px;
		border-radius: 12px;
		font-size: 12px;
		font-weight: 600;
		margin-top: 6px;
		border: 1px solid rgba(56, 189, 248, 0.2);
		transition: background 0.2s ease;
	}

	.emp-header-info .emp-id-tag:hover {
		background: rgba(56, 189, 248, 0.25);
	}

	.emp-profile-body {
		padding: 30px;
		display: grid;
		grid-template-columns: 2fr 1fr;
		gap: 24px;
	}

	.emp-info-section {
		display: flex;
		flex-direction: column;
		gap: 18px;
	}

	.emp-info-block {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px 20px;
		transition: all 0.25s ease-in-out;
	}

	.emp-info-block:hover {
		background: #ffffff;
		border-color: #cbd5e1;
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
	}

	.emp-label {
		font-size: 11px;
		text-transform: uppercase;
		letter-spacing: 0.6px;
		color: #64748b;
		font-weight: 700;
		display: block;
		margin-bottom: 6px;
	}

	.emp-value {
		font-size: 14px;
		color: #0f172a;
	}

	.pos-item {
		background: #ffffff;
		border-left: 4px solid #2563eb;
		border-radius: 4px;
		padding: 10px 14px;
		margin-bottom: 8px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.04);
		transition: all 0.2s ease;
	}

	.pos-item:hover {
		transform: translateX(4px);
		border-left-color: #0284c7;
		box-shadow: 0 4px 8px rgba(0,0,0,0.08);
		background: #f0f9ff;
	}

	.emp-sidebar {
		display: flex;
		flex-direction: column;
		gap: 18px;
	}

	/* Status Card UI Styles */
	.status-card {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 18px 16px;
		text-align: center;
		transition: all 0.25s ease;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 8px;
	}

	.status-card:hover {
		background: #ffffff;
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
	}

	.status-pill {
		display: inline-block;
		background: #0284c7;
		color: #ffffff;
		padding: 6px 16px;
		border-radius: 20px;
		font-weight: 700;
		font-size: 12px;
		text-transform: uppercase;
		letter-spacing: 0.4px;
		transition: transform 0.2s ease, background-color 0.2s ease;
	}

	.status-pill:hover {
		transform: scale(1.05);
		background: #0369a1;
	}

	/* Refined Status Date Badge UI */
	.status-date {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		background: #f1f5f9;
		color: #475569;
		border: 1px solid #e2e8f0;
		padding: 3px 10px;
		border-radius: 12px;
		font-size: 11px;
		font-weight: 600;
	}

	.status-date i {
		font-size: 10px;
		color: #64748b;
	}

	.tenure-box {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px;
		transition: all 0.25s ease;
	}

	.tenure-box:hover {
		background: #ffffff;
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
	}

	.tenure-grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 8px;
		text-align: center;
		margin-top: 10px;
	}

	.tenure-metric {
		background: #ffffff;
		border-radius: 8px;
		padding: 10px 4px;
		border: 1px solid #e2e8f0;
		transition: all 0.2s ease;
	}

	.tenure-metric:hover {
		transform: translateY(-3px) scale(1.03);
		border-color: #3b82f6;
		box-shadow: 0 4px 8px rgba(59, 130, 246, 0.15);
		background: #eff6ff;
	}

	.tenure-metric:hover .tenure-num {
		color: #2563eb;
	}

	.tenure-num {
		font-size: 18px;
		font-weight: 700;
		color: #1e293b;
		display: block;
		transition: color 0.2s ease;
	}

	.tenure-unit {
		font-size: 10px;
		color: #64748b;
		text-transform: uppercase;
		font-weight: 600;
	}

	.emp-actions-group {
		display: flex;
		gap: 10px;
	}

	.emp-btn {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 8px 16px;
		border-radius: 20px;
		font-size: 12px;
		font-weight: 600;
		text-decoration: none !important;
		transition: all 0.25s ease;
		border: 1px solid transparent;
	}

	.emp-btn i {
		font-size: 12px;
	}

	.emp-btn-checklist {
		background: rgba(59, 130, 246, 0.15);
		color: #60a5fa !important;
		border-color: rgba(96, 165, 250, 0.3);
	}

	.emp-btn-checklist:hover {
		background: #2563eb;
		color: #ffffff !important;
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
	}

	.emp-btn-view {
		background: rgba(16, 185, 129, 0.15);
		color: #34d399 !important;
		border-color: rgba(52, 211, 153, 0.3);
	}

	.emp-btn-view:hover {
		background: #059669;
		color: #ffffff !important;
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
	}

	.schedule-table-wrapper {
		overflow-x: auto;
		margin-top: 8px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #ffffff;
	}

	.schedule-table {
		width: 100%;
		border-collapse: collapse;
		text-align: left;
	}

	.schedule-table th {
		background: #f1f5f9;
		color: #475569;
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		padding: 10px 14px;
		border-bottom: 2px solid #e2e8f0;
	}

	.schedule-table td {
		padding: 10px 14px;
		border-bottom: 1px solid #f1f5f9;
		vertical-align: middle;
		transition: background-color 0.2s ease;
	}

	.schedule-table tr:last-child td {
		border-bottom: none;
	}

	.schedule-table tr:hover td {
		background-color: #f0f9ff;
	}

	.schedule-day-cell {
		font-weight: 700;
		font-size: 13px;
		color: #1e293b;
		display: flex;
		align-items: center;
		gap: 8px;
		white-space: nowrap;
	}

	.schedule-day-dot {
		width: 6px;
		height: 6px;
		background-color: #3b82f6;
		border-radius: 50%;
	}

	.time-chip {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-size: 11px;
		font-weight: 600;
		padding: 4px 10px;
		border-radius: 6px;
	}

	.time-chip-am {
		background: #fef3c7;
		color: #b45309;
		border: 1px solid #fde68a;
	}

	.time-chip-pm {
		background: #e0e7ff;
		color: #4338ca;
		border: 1px solid #c7d2fe;
	}

	.time-none {
		color: #cbd5e1;
		font-size: 12px;
		font-weight: 500;
	}

	.no-schedule-box {
		background: #f8fafc;
		border: 1px dashed #cbd5e1;
		border-radius: 8px;
		padding: 16px;
		text-align: center;
		color: #64748b;
		font-style: italic;
		font-size: 13px;
	}

	.no-results {
		text-align: center;
		padding: 50px 20px;
		color: #64748b;
		font-style: italic;
		background: #f8fafc;
		border: 1px dashed #cbd5e1;
		border-radius: 8px;
		margin-top: 15px;
	}
</style>

<!-- Top Actions Bar -->
<div class="emp-toolbar">
	<a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'employee_add.php?','Employee Detail')"><i class="icon-plus icon-white"></i> Add New Employee</a>
	<a id="adcd" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'employee_list.php?','Employee Detail','1')"><i class="icon-list icon-white"></i> Employee List</a>
	<a href="employee_export_menu.php?from=<?php echo functions::encode($_SERVER['REQUEST_URI']) ?>" class="btn btn-info btn-small"><i class="icon-download-alt icon-white"></i> Download List</a>
</div>

<div class="page-full-wrapper">
	<div class="card-panel">
		<!-- Updated Custom Card Header -->
		<div class="card-header-custom">
			<h2><i class="halflings-icon white list-alt"></i> Employee Record</h2>
		</div>
		<div class="card-body-custom">
			<!-- Search Header -->
			<form method="post">
				<div class="emp-search-bar">
					<label style="margin:0; font-weight:600; color:#475569;">Select Employee:</label>
					<select name="txSearch" id="txSearch" data-rel="chosen" style="width:420px; margin-bottom:0;">
						<option value="">-- Search by Name or ID --</option>
						<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
						while($rEU = $db->fetch_array($qEU)):
						?>
						<option value="<?php echo $rEU['emp_id']?>" <?php if($searchVal==$rEU['emp_id'])echo 'selected="selected"'; ?>><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
						<?php endwhile;?>
					</select>
					<input style="display:none;" type="text" name="txSearch_" id="txSearch_" value="<?php echo $searchVal?>">
					<button type="submit" class="btn btn-primary btn-small" name="btnSearch" id="btnSearch"><i class="icon-search icon-white"></i> Search</button>
					<a href="?" class="btn btn-default btn-small">Clear</a>
				</div>
			</form>

			<?php if($searchVal){ 
				$countResult=0;
				while($rDisp = $db->fetch_array($qDisp)):
					$countResult++;
					$emp_id = $rDisp['emp_id'];
					$cert_id = $rDisp['cert_id'];
					$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
					$has_attendance = $rDisp['has_attendance'];
					
					$curStreet = $rDisp['curr_street'];
					$curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$rDisp['curr_province']));
					$curProvince = ($curProvince) ? ', '.$curProvince : '';
					$curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$rDisp['curr_cityMun']));
					$curCity = ($curCity) ? ', '.$curCity : '';
					$curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$rDisp['curr_add']));
					$current_address = $curStreet.' '.strtoupper($curBrngy).$curCity.$curProvince;

					// Work Status calculation
					$qStat = $db->select('emp_work_status','*',array('emp_id'=>$rDisp['emp_id']),'ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
					$rStat = $db->fetch_array($qStat);
					$ewsDateFormatted = (isset($rStat['ews_date']) && $rStat['ews_date'] && $rStat['ews_date'] != '0000-00-00') ? functions::datearr($rStat['ews_date']) : '';

					// Tenure calculation
					$datehired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$rDisp['emp_id']),'ORDER BY ews_date, ews_id DESC LIMIT 1');
					$datetime1 = new DateTime($datehired);
					$datetime2 = new DateTime(date('Y-m-d'));
					$interval = $datetime1->diff($datetime2);
					$intrval_year = $interval->format('%y');
					$intrval_month = $interval->format('%m');
					$intrval_day = $interval->format('%d');

					$monAmIn=$monAmOut=$monPmIn=$monPmOut=$tueAmIn=$tueAmOut=$tuePmIn=$tuePmOut=$wedAmIn=$wedAmOut=$wedPmIn=$wedPmOut=$thuAmIn=$thuAmOut=$thuPmIn=$thuPmOut=$friAmIn=$friAmOut=$friPmIn=$friPmOut=$satAmIn=$satAmOut=$satPmIn=$satPmOut='';
					if($has_attendance){
						$qTimeIn = $db->select('emp_timein','*',array('emp_id'=>$emp_id));
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

					$weekDays = array(
						'Monday' => array('amIn' => $monAmIn, 'amOut' => $monAmOut, 'pmIn' => $monPmIn, 'pmOut' => $monPmOut),
						'Tuesday' => array('amIn' => $tueAmIn, 'amOut' => $tueAmOut, 'pmIn' => $tuePmIn, 'pmOut' => $tuePmOut),
						'Wednesday' => array('amIn' => $wedAmIn, 'amOut' => $wedAmOut, 'pmIn' => $wedPmIn, 'pmOut' => $wedPmOut),
						'Thursday' => array('amIn' => $thuAmIn, 'amOut' => $thuAmOut, 'pmIn' => $thuPmIn, 'pmOut' => $thuPmOut),
						'Friday' => array('amIn' => $friAmIn, 'amOut' => $friAmOut, 'pmIn' => $friPmIn, 'pmOut' => $friPmOut),
						'Saturday' => array('amIn' => $satAmIn, 'amOut' => $satAmOut, 'pmIn' => $satPmIn, 'pmOut' => $satPmOut)
					);

					$initials = strtoupper(substr($rDisp['fname'], 0, 1) . substr($rDisp['lname'], 0, 1));
					$profilePic = ($fileName && file_exists('../img_emp/'.$fileName)) ? $fileName : 'blank-pic.png';
			?>
			
			<div class="emp-profile-wrapper">
				<div class="emp-profile-header">
					<div class="emp-profile-main">
						<div class="emp-avatar-box">
							<?php if( file_exists('../img_emp/'.$profilePic)): ?>
								<img src="<?php echo '../img_emp/'.$profilePic; ?>" alt="Employee Photo">
							<?php else: ?>
								<span class="emp-avatar-initials"><?php echo $initials; ?></span>
							<?php endif; ?>
						</div>
						<div class="emp-header-info">
							<h2><?php echo $rDisp['lname'].' '.$rDisp['extname'].', '.$rDisp['fname'].' '.$rDisp['mname']?></h2>
							<span class="emp-id-tag">ID NO: <?php echo $rDisp['emp_no']?></span>
						</div>
					</div>

					<div class="emp-actions-group">
						<a id="cl<?php echo $rDisp['emp_id']?>" style="cursor: pointer;" class="emp-btn emp-btn-checklist thickbox" title="File 201 Checklist" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee 201 Detail','1')"><i class="halflings-icon white list"></i> 201 File</a>
						<a id="vw<?php echo $rDisp['emp_id']?>" style="cursor: pointer;" class="emp-btn emp-btn-view thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_detail.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee Detail','1')"><i class="halflings-icon white zoom-in"></i> View Detail</a>
					</div>
				</div>

				<div class="emp-profile-body">
					<div class="emp-info-section">
						<div class="emp-info-block">
							<span class="emp-label">Position & Department</span>
							<div class="emp-value">
								<?php
								$qDep = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$rDisp['emp_id']),'AND ep.dp_id=dp.dp_id ORDER BY dp.pos_name');
								$countDep = $db->num_rows($qDep);
								if($countDep > 0){
									while($rDep = $db->fetch_array($qDep)):
										$depName = $db->getValue('department','dep_name',array('dep_id'=>$rDep['dep_id']));
								?>
									<div class="pos-item">
										<strong style="font-size:15px; color:#0f172a;"><?php echo $rDep['pos_name']?></strong>
										<?php if($depName): ?>
											<div style="font-size:12px; color:#64748b; margin-top:2px;"><?php echo $depName; ?></div>
										<?php endif; ?>
									</div>
								<?php 
									endwhile;
								} else {
									echo '<span style="color:#94a3b8; font-style:italic;">No position assigned</span>';
								}
								?>
							</div>
						</div>

						<div class="emp-info-block">
							<span class="emp-label">Current Address</span>
							<div class="emp-value">
								<?php echo ($current_address) ? $current_address : '<em>No address specified</em>'; ?>
							</div>
						</div>

						<div class="emp-info-block">
							<span class="emp-label">Current Project/Site Assignment</span>
							<div class="emp-value">
								<?php
								$qSite = $db->select('emp_site_assign esa, project proj','*',array('emp_id'=>$emp_id),'AND esa.proj_id=proj.proj_id ORDER BY proj.date_start DESC,proj.proj_name LIMIT 1');
								$rSite = $db->fetch_array($qSite);
								echo isset($rSite['proj_name']) ? strtoupper($rSite['proj_name']) : '<em>No assignment specified</em>';
								?>
							</div>
						</div>

						<div class="emp-info-block">
							<span class="emp-label">Duty Schedule</span>
							<div class="emp-value">
								<?php if($has_attendance){ ?>
									<div class="schedule-table-wrapper">
										<table class="schedule-table">
											<thead>
												<tr>
													<th>Day of Week</th>
													<th>Morning Shift (AM)</th>
													<th>Afternoon Shift (PM)</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach($weekDays as $dayName => $times): 
													$amFormattedIn = !empty($times['amIn']) ? functions::MilToTwelve($times['amIn']) : '';
													$amFormattedOut = !empty($times['amOut']) ? functions::MilToTwelve($times['amOut']) : '';
													$pmFormattedIn = !empty($times['pmIn']) ? functions::MilToTwelve($times['pmIn']) : '';
													$pmFormattedOut = !empty($times['pmOut']) ? functions::MilToTwelve($times['pmOut']) : '';
													
													$hasAm = !empty($amFormattedIn) || !empty($amFormattedOut);
													$hasPm = !empty($pmFormattedIn) || !empty($pmFormattedOut);
												?>
													<tr>
														<td>
															<div class="schedule-day-cell">
																<span class="schedule-day-dot"></span>
																<?php echo $dayName; ?>
															</div>
														</td>
														<td>
															<?php if($hasAm): ?>
																<span class="time-chip time-chip-am">
																	<i class="icon-time"></i> <?php echo $amFormattedIn; ?> - <?php echo $amFormattedOut; ?>
																</span>
															<?php else: ?>
																<span class="time-none">--</span>
															<?php endif; ?>
														</td>
														<td>
															<?php if($hasPm): ?>
																<span class="time-chip time-chip-pm">
																	<i class="icon-time"></i> <?php echo $pmFormattedIn; ?> - <?php echo $pmFormattedOut; ?>
																</span>
															<?php else: ?>
																<span class="time-none">--</span>
															<?php endif; ?>
														</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php } else { ?>
									<div class="no-schedule-box">
										<i class="icon-info-sign"></i> Duty Hours Not Applicable for this employee.
									</div>
								<?php } ?>
							</div>
						</div>
					</div>

					<div class="emp-sidebar">
						<!-- Enhanced Status Card with Date Badge -->
						<div class="status-card">
							<span class="emp-label" style="text-align:center; margin-bottom: 2px;">Work Status</span>
							<div class="status-pill"><?php echo $rStat['ews_stat'] ?: 'UNKNOWN'; ?></div>
							
							<?php if($ewsDateFormatted): ?>
								<div class="status-date" title="Status Date">
									<i class="icon-calendar"></i> <?php echo $ewsDateFormatted; ?>
								</div>
							<?php endif; ?>

							<?php if($rStat['project_based']): ?>
								<div style="font-size:11px; color:#64748b; margin-top:2px; font-style:italic;">(Project Based)</div>
							<?php endif; ?>
						</div>

						<div class="tenure-box">
							<span class="emp-label">Date Hired</span>
							<div style="font-weight:600; font-size:13px; color:#334155; margin-bottom:12px;">
								<?php echo ($datehired && $datehired != '0000-00-00') ? functions::datearr($datehired) : '----'; ?>
							</div>

							<span class="emp-label">Length of Service</span>
							<div class="tenure-grid">
								<div class="tenure-metric">
									<span class="tenure-num"><?php echo $intrval_year; ?></span>
									<span class="tenure-unit">Years</span>
								</div>
								<div class="tenure-metric">
									<span class="tenure-num"><?php echo $intrval_month; ?></span>
									<span class="tenure-unit">Months</span>
								</div>
								<div class="tenure-metric">
									<span class="tenure-num"><?php echo $intrval_day; ?></span>
									<span class="tenure-unit">Days</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php 
				endwhile;
				if($countResult == 0){
			?>
				<div class="no-results">
					--- No Match Found! ---
				</div>
			<?php 
				}
			} 
			?>
		</div>
	</div>
</div>
<!-- body content: end here-->
<?php require_once('templ_down.php');?>