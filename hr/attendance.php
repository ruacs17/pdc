<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$unconfirmed=( isset($_REQUEST['unc']) && !empty($_REQUEST['unc']) ) ? $_REQUEST['unc'] : '';
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
if($delID){
	if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$delID,'attendance_ready'=>0)) ){
		$uploaded_file = '../attendance/'.$db->getValue('emp_attendance','excelfile',array('eat_id'=>$delID));
		$db->delete('emp_attendance',array('eat_id'=>$delID,'attendance_ready'=>0));
		if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$delID))==0 ){
			$db->prepareQ('DELETE eaa,ead FROM emp_attendance_adjustment eaa, emp_attendance_detail ead WHERE eaa.eatd_id=ead.eatd_id AND ead.eat_id=?',array($delID));
			$db->delete('emp_attendance_detail',array('eat_id'=>$delID));
			$db->delete('emp_payroll_detail',array('eat_id'=>$delID));
			$db->delete('payroll_adjustment',array('eat_id'=>$delID));
			$db->delete('payroll_premium',array('eat_id'=>$delID));
			$_SESSION['notif_warning']='Attendance File Removed!';
		}
	}
	else{
		functions::say('Confirmed attendance cannot be removed!');
	}
	functions::sendTo(functions::pageName());
	die();
}
if($unconfirmed){
	$_SESSION['atProj'] = '';
	$_SESSION['atType'] = '';
	$_SESSION['atStat'] = '2';
	$_SESSION['atMon'] = '';
	$_SESSION['atYear'] = '';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['atProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : '';
	$_SESSION['atType'] = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? $_POST['selType'] : '';
	$_SESSION['atStat'] = ( isset($_POST['selStat']) && !empty($_POST['selStat']) ) ? $_POST['selStat'] : '';
	$_SESSION['atMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['atYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$arr = array();
$projID = ( isset($_SESSION['atProj']) && !empty($_SESSION['atProj']) ) ? $_SESSION['atProj'] : '';
$at_type = ( isset($_SESSION['atType']) && !empty($_SESSION['atType']) ) ? $_SESSION['atType'] : '';
$at_stat = ( isset($_SESSION['atStat']) && !empty($_SESSION['atStat']) ) ? $_SESSION['atStat'] : '';
$txbMon = ( isset($_SESSION['atMon']) ) ? $_SESSION['atMon'] : date('m');
$txbYear = ( isset($_SESSION['atYear']) ) ? $_SESSION['atYear'] : date('Y');

if($txbMon && $txbYear)
	$arr = array('LEFT(date_start,7)'=>$txbYear.'-'.$txbMon);
else if($txbMon)
	$arr = array('SUBSTRING(date_start,6,2)'=>$txbMon);
elseif($txbYear)
	$arr = array('LEFT(date_start,4)'=>$txbYear);
if($at_stat){
	$arr = ($at_stat==1) ? array_merge($arr,array('attendance_ready'=>'1')) : array_merge($arr,array('attendance_ready'=>'0'));
}
if($at_type)
	$arr = array_merge($arr,array('payroll_type'=>$at_type));
if($projID)
	$arr = array_merge($arr,array('proj_id'=>$projID));
?>
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }

/* --- MODERN CONFIRMATION MODAL STYLES --- */
.custom-modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100vw;
	height: 100vh;
	background: rgba(15, 23, 42, 0.45);
	backdrop-filter: blur(4px);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 99999;
	opacity: 0;
	visibility: hidden;
	transition: opacity 0.2s ease, visibility 0.2s ease;
}

.custom-modal-overlay.active {
	opacity: 1;
	visibility: visible;
}

.custom-modal-card {
	background: #ffffff;
	width: 100%;
	max-width: 400px;
	border-radius: 14px;
	border: 1px solid #e2e8f0;
	box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
	padding: 24px 24px;
	text-align: center;
	transform: scale(0.95);
	transition: transform 0.2s ease;
	box-sizing: border-box;
}

.custom-modal-overlay.active .custom-modal-card {
	transform: scale(1);
}

.custom-modal-icon-wrapper {
	width: 48px;
	height: 48px;
	background: #fee2e2;
	color: #ef4444;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 0 auto 16px auto;
	font-size: 20px;
}

.custom-modal-title {
	font-size: 18px;
	font-weight: 700;
	color: #1e293b;
	margin: 0 0 8px 0;
}

.custom-modal-text {
	font-size: 13px;
	color: #64748b;
	margin: 0 0 24px 0;
	line-height: 1.5;
}

.custom-modal-actions {
	display: flex;
	gap: 10px;
	justify-content: center;
}

.btn-modal-cancel {
	flex: 1;
	background: #f1f5f9;
	color: #475569;
	font-weight: 600;
	font-size: 13px;
	padding: 9px 16px;
	border-radius: 8px;
	border: 1px solid #cbd5e1;
	cursor: pointer;
	transition: all 0.2s ease;
}

.btn-modal-cancel:hover {
	background: #e2e8f0;
	color: #334155;
}

.btn-modal-confirm {
	flex: 1;
	background: #ef4444;
	color: #ffffff !important;
	font-weight: 600;
	font-size: 13px;
	padding: 9px 16px;
	border-radius: 8px;
	border: none;
	cursor: pointer;
	transition: all 0.2s ease;
	box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
	text-decoration: none !important;
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.btn-modal-confirm:hover {
	background: #dc2626;
	color: #ffffff !important;
}
</style>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" style="display:none;" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'attendance_upload.php?','Attendance Upload')">Upload Attendance Log</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="adcd" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'attendance_report_add_charge.php?','Create Attendance Report')">Create Attendance Report</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Attendance</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0" width="100%">
						<tr>
							<td width="18%" style="padding-top: 8px;">
								<div align="center">
									<select name="bdYear" id="bdYear" style="width:90px;font-size:12px;">
										<option value="">All Year</option>
										<?php
										$qYr = $db->select('emp_attendance','DISTINCT LEFT(date_start,4) as yr',array(),'ORDER BY date_start DESC');
										while($rYr = $db->fetch_array($qYr)):
										?>
										<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
										<?php endwhile;?>
									</select>
									<select name="bdMon" id="bdMon" style="width:95px;font-size:12px;">
										<option value="">All Month</option>
										<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
								</div>
							</td>
							<td width="52%" style="padding-top: 5px;">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:98%;font-size:12px;">
									<option value="">--All Projects / Department--</option>
									<?php 
									$qProj = $db->select('project','*',array(),'WHERE proj_id IN (SELECT DISTINCT proj_id FROM emp_attendance) ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):?>
									<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($projID==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
									<?php endwhile;?>
								</select>
							</td>
							<td width="7%" style="padding-top: 8px;">
								<select name="selType" id="selType" style="width:95px;font-size:12px;">
									<option value="">--All Type--</option>
									<option value="admin" <?php if($at_type=='admin')echo 'selected="selected"'; ?>>ADMIN</option>
									<option value="labor" <?php if($at_type=='labor')echo 'selected="selected"'; ?>>LABOR</option>
								</select>
							</td>
							<td width="7%" style="padding-top: 8px;">
								<select name="selStat" id="selStat" style="width:112px;font-size:12px;">
									<option value="">--Status--</option>
									<option value="1" <?php if($at_stat=='1')echo 'selected="selected"'; ?>>Confirmed</option>
									<option value="2" <?php if($at_stat=='2')echo 'selected="selected"'; ?>>Unconfirmed</option>
								</select>
							</td>
							<td width="7%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></div></td>
						</tr>
					</table>
				</div>
			</form>
			<div class="table-wrapper">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="15%" scope="col">Date Range</th>
							<th width="30%" scope="col">Project / Department</th>
							<th width="10%" scope="col">Description</th>
							<th width="7%" scope="col">Confirm Status</th>
							<th width="5%" scope="col"><div align="center">&nbsp;</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$count=0;
					$qDisp = $db->select('emp_attendance','*',$arr,'ORDER BY date_start DESC');
					while($rDisp = $db->fetch_array($qDisp)):
						$count++;
						$rID = $rDisp['eat_id'];
						$dateRangeDisp='';
						$date_from=$rDisp['date_start'];
						$date_to=$rDisp['date_end'];
						$eas_name = $rDisp['note'];
						$days = functions::date_diff($date_from,$date_to,$includeDay1=1);
						$dtfArr = explode('-',$date_from);
						$monthFrom = isset($dtfArr[1]) ? $dtfArr[1] :'00';
						$dayFrom =  isset($dtfArr[2]) ? $dtfArr[2] :'00';
						$yrFrom =  isset($dtfArr[0]) ? $dtfArr[0] :'0000';
						$dttArr = explode('-',$date_to);
						$monthTo = isset($dttArr[1]) ? $dttArr[1] :'00';
						$dayTo =  isset($dttArr[2]) ? $dttArr[2] :'00';
						$yrTo =  isset($dttArr[0]) ? $dttArr[0] :'0000';
						if($yrFrom==$yrTo){
							if($monthFrom==$monthTo){
								$dateMonthName = date('M', strtotime($yrFrom.'-'.$monthFrom.'-'.'01'));
								$dateRangeDisp = $dateMonthName.' '.$dayFrom.' - '.$dayTo.', '.$yrFrom;
							}
							else
								$dateRangeDisp = functions::datearr($date_from).' - '.functions::datearr($date_to);
						}
						else
							$dateRangeDisp = functions::datearr($date_from).' - '.functions::datearr($date_to);
					?>
						<tr id="rw<?php echo $rID?>">
							<td><?php echo $dateRangeDisp.' (<i>'.$days.' days</i>)';?></td>
							<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rDisp['proj_id']));?></td>
							<td><?php echo $rDisp['note'];?></td>
							<td><?php echo ($rDisp['attendance_ready']==1) ? 'Confirmed' : 'Unconfirm';?></td>
							<td><?php $page = ($rDisp['upload_done']==1) ? 'attendance_summary_view.php' : 'attendance_summary_view.php'?>
								<div align="left">
									<a id="vw<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $page?>?eatid=<?php echo functions::encode($rID);?>','Attendance Detail')"><i class="halflings-icon white zoom-in"></i></a>
									<?php if($rDisp['attendance_ready']==0){?>
									<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger btn-delete-trigger" title="Remove this Attendance" data-rel="tooltip" href="?delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
									<?php }?>
								</div>
							</td>
						</tr>
					<?php endwhile;
					if($count==0){
						echo '<tr><td colspan="5"><div align="center">---Nothing to Report---</div></td></tr>';
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->

<!-- Modern Delete Confirmation Modal HTML -->
<div id="deleteConfirmModal" class="custom-modal-overlay">
	<div class="custom-modal-card">
		<div class="custom-modal-icon-wrapper">
			<i class="halflings-icon trash"></i>
		</div>
		<h3 class="custom-modal-title">Remove Attendance?</h3>
		<p class="custom-modal-text">Are you sure you want to remove this attendance record? This action cannot be undone.</p>
		<div class="custom-modal-actions">
			<button type="button" class="btn-modal-cancel" id="btnCancelModal">Cancel</button>
			<button type="button" class="btn-modal-confirm" id="btnConfirmDelete">Delete</button>
		</div>
	</div>
</div>
<!-- body content: end here-->

<?php require_once('templ_down.php');?>

<script type="text/javascript">
$(document).ready(function(){
	var deleteTargetUrl = "";

	// Open modal on delete button click
	$(document).on('click', '.btn-delete-trigger', function(e){
		e.preventDefault();
		deleteTargetUrl = $(this).attr('href');
		$('#deleteConfirmModal').addClass('active');
	});

	// Trigger navigation on Delete Confirm button click
	$('#btnConfirmDelete').on('click', function(e){
		e.preventDefault();
		if (deleteTargetUrl !== "") {
			window.location.href = deleteTargetUrl;
		}
	});

	// Close modal on Cancel
	$('#btnCancelModal').on('click', function(){
		$('#deleteConfirmModal').removeClass('active');
	});

	// Close modal when clicking on the backdrop
	$('#deleteConfirmModal').on('click', function(e){
		if ($(e.target).is('#deleteConfirmModal')) {
			$('#deleteConfirmModal').removeClass('active');
		}
	});

	// Close modal on Escape Key
	$(document).keyup(function(e) {
		if (e.key === "Escape") {
			$('#deleteConfirmModal').removeClass('active');
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
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>