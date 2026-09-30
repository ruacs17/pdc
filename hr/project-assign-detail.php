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
#$fn = (isset($_REQUEST['fn']) && !empty($_REQUEST['fn']) ) ? functions::decode($_REQUEST['fn']).'.xlsx' : 0;
$date_started = ( isset($_SESSION['pas_dateStart']) ) ? $_SESSION['pas_dateStart'] : NULL;
$date_ended = ( isset($_SESSION['pas_dateEnd']) ) ? $_SESSION['pas_dateEnd'] : NULL;
$projid = (isset($_REQUEST['projid']) && !empty($_REQUEST['projid']) ) ? functions::decode($_REQUEST['projid']) : 0;
if($projid)
	$_SESSION['notif_id']=$projid;
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$projid));

function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
		endwhile;
		return $position;
}
function work_status($emp_id){
	global $db;
	$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'ORDER BY ews_date DESC');
	$rStat = $db->fetch_array($qStat);
	$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
	$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
	$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <strong><i>Project Based</i></strong>' : '';
	return $wrkStat. $projBased . $wrkDate;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Group Select</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
			
			/* Brown Theme Color Palette */
			--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
			--theme-brown-primary: #7c401e;
			--theme-brown-hover: #5c2e14;
			--theme-brown-light: #f5ebe6;
			--theme-brown-accent: #b45309;
			--theme-brown-active-row: #f3e5dc;
		}

		body {
			background-color: var(--bg-canvas);
			font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
			color: var(--text-primary);
			margin: 0;
			padding: 0;
		}

		/* Full Width Container Setup */
		.page-full-wrapper {
			width: 100% !important;
			max-width: 100% !important;
			/*padding: 15px 20px 30px 20px;*/
			box-sizing: border-box;
		}

		/* Card Container */
		.card-panel {
			width: 100%;
			background: var(--panel-bg);
			border-radius: 16px;
			border: 1px solid var(--border-subtle);
			box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
			overflow: hidden;
			box-sizing: border-box;
		}

		/* Header Styling */
		.card-header-custom {
			background: var(--theme-brown-header);
			padding: 18px 25px;
			color: #ffffff;
			display: flex;
			align-items: center;
			justify-content: space-between;
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

		.project-banner {
			background: #f7f2ed;
			border-left: 4px solid var(--theme-brown-primary);
			padding: 12px 20px;
			border-radius: 6px;
			margin-bottom: 20px;
			font-size: 14px;
			color: var(--text-muted);
		}

		.project-banner strong {
			color: var(--text-primary);
			font-size: 16px;
		}

		.card-body-custom {
			padding: 25px;
			width: 100%;
			box-sizing: border-box;
		}

		.section-title {
			text-align: center;
			margin-bottom: 25px;
		}

		.section-title h2 {
			font-size: 18px;
			font-weight: 800;
			letter-spacing: 0.5px;
			color: var(--theme-brown-primary);
			margin: 0;
			text-transform: uppercase;
		}

		/* Add Member Form Controls Box */
		.form-add-box {
			background: #f7f2ed;
			padding: 20px;
			border-radius: 12px;
			border: 1px solid var(--border-subtle);
			margin-bottom: 30px;
		}

		.form-grid-flex {
			display: flex;
			flex-wrap: wrap;
			align-items: flex-end;
			gap: 15px;
			justify-content: space-between;
		}

		.field-group {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.field-group label {
			font-size: 11px;
			font-weight: 700;
			text-transform: uppercase;
			color: var(--text-muted);
			margin: 0;
		}

		.date-input-container {
			display: flex;
			align-items: center;
			gap: 8px;
			background: #ffffff;
			border: 1px solid var(--border-subtle);
			border-radius: 8px;
			padding: 4px 10px;
		}

		.date-input-container input {
			border: none !important;
			box-shadow: none !important;
			margin: 0 !important;
			padding: 4px 0 !important;
			height: auto !important;
			font-size: 13px !important;
			background: transparent !important;
		}

		.date-input-container img {
			cursor: pointer;
			opacity: 0.75;
			transition: opacity 0.2s;
		}

		.date-input-container img:hover {
			opacity: 1;
		}

		.btn-modern-add {
			background: var(--theme-brown-primary);
			color: #ffffff !important;
			font-weight: 700;
			padding: 10px 24px;
			border-radius: 8px;
			border: none;
			font-size: 13px;
			cursor: pointer;
			transition: all 0.2s ease;
			box-shadow: 0 4px 12px rgba(124, 64, 30, 0.2);
			height: 42px;
		}

		.btn-modern-add:hover {
			background: var(--theme-brown-hover);
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(92, 46, 20, 0.3);
		}

		/* Data Table Styling */
		.table-wrapper {
			width: 100%;
			border-radius: 12px;
			overflow: hidden;
			border: 1px solid var(--border-subtle);
			box-sizing: border-box;
		}

		.custom-data-table {
			width: 100% !important;
			border-collapse: separate;
			border-spacing: 0;
			font-size: 13px;
			margin: 0;
		}

		.custom-data-table thead tr {
			background: #f4ebe2;
		}

		.custom-data-table thead th {
			color: var(--text-muted);
			font-weight: 700;
			text-transform: uppercase;
			font-size: 11px;
			letter-spacing: 0.5px;
			padding: 14px 16px;
			border-bottom: 1px solid var(--border-subtle);
			position: sticky;
			top: 0;
		}

		.custom-data-table tbody tr {
			transition: background 0.15s ease;
			cursor: pointer;
		}

		.custom-data-table tbody tr:hover {
			background-color: #f9f4ef !important;
		}

		.custom-data-table tbody td {
			padding: 14px 16px;
			border-bottom: 1px solid #f2e9e1;
			vertical-align: middle;
		}

		.custom-data-table tbody tr:last-child td {
			border-bottom: none;
		}

		.tdpadleft {
			padding-left: 1px !important;
		}

		/* Row Selection Active State */
		.rActive {
			background-color: var(--theme-brown-active-row) !important;
		}

		.rInActive {
			background-color: #ffffff !important;
		}

		.btn-danger-remove {
			background: #dc2626;
			color: #ffffff;
			font-weight: 600;
			padding: 8px 20px;
			border-radius: 8px;
			border: none;
			font-size: 12px;
			cursor: pointer;
			transition: background 0.2s ease;
		}

		.btn-danger-remove:hover {
			background: #b91c1c;
		}

		.status-edit-btn {
			color: var(--theme-brown-primary);
			padding: 4px;
			border-radius: 4px;
			transition: background 0.2s;
			display: inline-block;
			margin-left: 6px;
		}

		.status-edit-btn:hover {
			background: var(--theme-brown-light);
		}
	</style>

	<?php
	$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
	if( $projid && $delID ){
		$db->delete('emp_site_assign',array('esa_id'=>$delID,'proj_id'=>$projid));
		$_SESSION['notif_warning']='Employee Removed!';
		functions::sendTo(functions::pageName().'?projid='.functions::encode($projid));
		die();
	}
	if( isset($_POST['btnRemoved']) && $projid ){
		$ArrRemoved = isset($_POST['chkDel']) ? $_POST['chkDel'] : '';
		if(is_array($ArrRemoved)){
			foreach($ArrRemoved as $remID):
				$db->delete('emp_site_assign',array('esa_id'=>$remID,'proj_id'=>$projid));
			endforeach;
			$_SESSION['notif_warning']='Personnel Removed!';
		}
		functions::sendTo(functions::pageName().'?projid='.functions::encode($projid));
		die();
	}
	if( isset($_POST['btnAdd']) ){
		$_SESSION['pas_dateStart'] = $txStartDate = ( isset($_POST['txStartDate']) && !empty($_POST['txStartDate']) ) ? trim($_POST['txStartDate']) : NULL;
		$_SESSION['pas_dateEnd'] = $txEndDate = ( isset($_POST['txEndDate']) && !empty($_POST['txEndDate']) ) ? trim($_POST['txEndDate']) : NULL;
		$emp_id = ( isset($_POST['selEmp']) && !empty($_POST['selEmp']) ) ? trim($_POST['selEmp']) : '';

		$current=0;
		if($txStartDate && $txEndDate){
			if( $txStartDate <= date('Y-m-d') && $txEndDate >= date('Y-m-d') )
				$current=1;
		}
		else if(functions::valid_date($txStartDate) && $txStartDate <= date('Y-m-d')){
				$current=1;
		}
		if( $projid && $emp_id ){
			$arrField = array('emp_id'=>$emp_id,'proj_id'=>$projid,'is_current'=>$current);
			if( $db->getValue('emp_site_assign','count(*)',$arrField)==0 ){
				$db->insert('emp_site_assign',array_merge($arrField,array('date_started'=>$txStartDate,'date_ended'=>$txEndDate)));
				$_SESSION['notif_success']='Employee Added!';
				functions::sendTo(functions::pageName().'?projid='.functions::encode($projid));
				die();
			}
			else{
				functions::say('Employee already assigned!');
				functions::sendTo(functions::pageName().'?projid='.functions::encode($projid));
				die();
			}
		}
		else{
			functions::say('Please fill up the form properly!');
			functions::sendTo(functions::pageName().'?projid='.functions::encode($projid));
			die();
		}
	}
	?>
</head>
<body>
<!-- body content: start here-->
<div class="page-full-wrapper">
	<div class="card-panel">
		<div class="card-header-custom">
			<h2><i class="halflings-icon white edit"></i> Project Group Selector</h2>
		</div>
		<div class="card-body-custom">
			<div class="project-banner">
				Project: <strong><?php echo $proj_name;?></strong>
			</div>

			<form class="form-horizontal" id="showform" name="showform" method="post">
				<div class="section-title">
					<h2>Project Assignment Member</h2>
				</div>

				<div class="form-add-box">
					<div class="form-grid-flex">
						<div class="field-group" style="flex: 1; min-width: 280px;">
							<label for="selEmp">Select Employee</label>
							<select name="selEmp" id="selEmp" data-rel="chosen" style="width:480px;">
								<option value="">--Select Employee--</option>
								<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
								while($rEU = $db->fetch_array($qEU)):
								?>
								<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
								<?php endwhile;?>
							</select>
						</div>

						<div class="field-group">
							<label for="txStartDate">Date Start</label>
							<div class="date-input-container">
								<a href="javascript:NewCssCal('txStartDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Pick Date"></a>
								<input name="txStartDate" type="text" id="txStartDate" value="<?php echo $date_started; ?>" style="width: 85px;" readonly>
							</div>
						</div>

						<div class="field-group">
							<label for="txEndDate">Date End <span style="font-weight:normal; text-transform:none; font-style:italic; color:var(--text-muted);">(Optional)</span></label>
							<div class="date-input-container">
								<a href="javascript:NewCssCal('txEndDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Pick Date"></a>
								<input name="txEndDate" type="text" id="txEndDate" value="<?php echo $date_ended; ?>" style="width: 85px;" readonly>
							</div>
						</div>

						<div class="field-group">
							<input type="submit" name="btnAdd" id="btnAdd" value="ADD MEMBER" class="btn-modern-add">
						</div>
					</div>
				</div>

				<div class="table-wrapper">
					<table id="tblist" class="custom-data-table table-hover">
						<thead>
							<tr>
								<th width="4%" style="text-align: center;">#</th>
								<th width="8%" class="tdpadleft" align="left">ID Number</th>
								<th width="20%" class="tdpadleft" align="left">Name</th>
								<th width="20%" class="tdpadleft" align="left">Position</th>
								<th width="15%" class="tdpadleft" align="left">Duration</th>
								<th width="20%" class="tdpadleft" align="left">Status</th>
								<th width="8%" style="text-align: center;"><input type="checkbox" name="checkAll" id="checkAll" value="all"></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$qDisp = $db->select('emp_site_assign esa, employee e','*',array('proj_id'=>$projid),'AND esa.emp_id=e.emp_id ORDER BY lname');
							$count=1;
							while($rDisp = $db->fetch_array($qDisp)):
								$rID = $rDisp['esa_id'];
							?>
							<tr>
								<td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?php echo $count++;?></td>
								<td class="tdpadleft" style="font-weight: 600;"><?php echo $rDisp['emp_no']?></td>
								<td class="tdpadleft" style="font-weight: 600; color: var(--theme-brown-primary);"><?php echo $rDisp['lname'].', '.$rDisp['fname']?></td>
								<td class="tdpadleft"><?php echo position($rDisp['emp_id'])?></td>
								<td class="tdpadleft" style="font-weight: 600;"><?php echo !empty($rDisp['date_started']) ? functions::datearr($rDisp['date_started']) : '---';?> - <?php echo !empty($rDisp['date_ended']) ? functions::datearr($rDisp['date_ended']) : '---';?></td>
								<td class="tdpadleft">
									<?php echo work_status($rDisp['emp_id'])?>
									<a id="adcworkstat<?php echo $rID;?>" href="#" class="thickbox status-edit-btn" title="Manage Status" onclick="showThis(this.id,'employee_add_work_stat.php?eid=<?php echo functions::encode($rDisp['emp_id'])?>&fromED=t','Work Status')">
										<i class="halflings-icon pencil" style="margin-top:0;"></i>
									</a>
								</td>
								<td style="text-align: center;">
									<input type="checkbox" class="chkDel" name="chkDel[<?php echo $rID; ?>]" id="chkDel[<?php echo $rID; ?>]" value="<?php echo $rID; ?>">
								</td>
							</tr>
							<?php endwhile;?>
							<tr style="background: #f7f2ed;">
								<td colspan="6">&nbsp;</td>
								<td style="text-align: center; padding: 10px 0;">
									<input type="submit" class="btn-danger-remove" name="btnRemoved" id="btnRemoved" value="Remove">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</form>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>

<script>
function ask(){
	if(confirm('Do you want to remove this personnel?'))
		return true;
	else
		return false;
}
function delt(){
if(confirm('Do you want to remove this item?'))
	return true;
else
	return false;
}
$(window).ready(function(){

	$('#btnRemoved').click(function(){
		return ask();
	});
	$(".chkDel").each(function() {
		if(this.checked==true){
			$(this).parent().parent().parent().attr('class','rActive');
		}
	});
	if ($('.chkDel:checked').length == $('.chkDel').length && $('.chkDel').length > 0){
		$('#checkAll').prop('checked',true);
	}
	else {
		$('#checkAll').prop('checked',false);
	}
	$("#checkAll").change(function() {
		if (this.checked) {
			$(".chkDel").each(function() {
				this.checked=true;
				$(this).parent().parent().parent().attr('class','rActive');
			});
		} else {
			$(".chkDel").each(function() {
				this.checked=false;
				$(this).parent().parent().parent().attr('class','');
			});
		}
	});
	$('#tblist tbody tr').click(function(event) {
		if (event.target.type !== 'checkbox' && !$(event.target).is('a') && !$(event.target).is('i')) {
			$(':checkbox', this).trigger('click');
		}
	});
	$(".chkDel").click(function () {
		$(this).closest('tr').toggleClass('rActive');
		if ($('.chkDel:checked').length == $('.chkDel').length){
			$('#checkAll').prop('checked',true);
		}
		else {
			$('#checkAll').prop('checked',false);
		}
	});
});
function delt(){if(confirm('Do you want to remove this Personnel?'))return true; else return false;}
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
<!-- end: JavaScript-->
</body>
</html>