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
$eas_id = (isset($_REQUEST['easid']) && !empty($_REQUEST['easid']) ) ? functions::decode($_REQUEST['easid']) : 0;
if($eas_id)
	$_SESSION['notif_id']=$eas_id;
$proj_id = $db->getValue('emp_assignment','proj_id',array('eas_id'=>$eas_id));
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));
$group_name = $db->getValue('emp_assignment','eas_name',array('eas_id'=>$eas_id));

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
	$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <span class="badge-project">Project Based</span>' : '';
	return $wrkStat. $projBased .'<br>'. $wrkDate;
}
if( isset($_POST['btnRemoved']) && $eas_id ){
	$ArrRemoved = isset($_POST['chkDel']) ? $_POST['chkDel'] : '';
	if(is_array($ArrRemoved)){
		foreach($ArrRemoved as $remID):
			$db->delete('emp_assign_detail',array('ead_id'=>$remID,'eas_id'=>$eas_id));
		endforeach;
		$_SESSION['notif_warning']='Personnel Removed!';
	}
	functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
	die();
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
			
			/* Brown Theme Color Palette */
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
			padding: 20px;
			margin: 0;
		}

		.form-card {
			background: var(--panel-bg);
			border-radius: 16px;
			border: 1px solid var(--border-subtle);
			box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
			/*overflow: hidden;*/
			max-width: 950px;
			margin: 0 auto;
		}

		.form-card-header {
			background: var(--theme-brown-header);
			padding: 20px 30px;
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

		/* Custom Tab Navigation */
		.nav-tabs-custom {
			display: flex;
			background: #f4ede6;
			border-bottom: 1px solid var(--border-subtle);
			padding: 0 20px;
			margin: 0;
			list-style: none;
		}

		.nav-tabs-custom li a {
			display: block;
			padding: 12px 20px;
			font-size: 13px;
			font-weight: 600;
			color: var(--text-muted);
			text-decoration: none;
			border-bottom: 3px solid transparent;
			transition: all 0.2s ease;
		}

		.nav-tabs-custom li.active a,
		.nav-tabs-custom li a:hover {
			color: var(--theme-brown-primary);
			border-bottom-color: var(--theme-brown-primary);
			background: #ffffff;
		}

		.form-card-body {
			padding: 30px 35px;
		}

		/* Meta Information Banner */
		.meta-info-bar {
			background: #faf6f2;
			border: 1px solid var(--border-subtle);
			border-radius: 8px;
			padding: 12px 20px;
			margin-bottom: 25px;
			display: flex;
			gap: 30px;
			font-size: 14px;
		}

		.meta-info-item {
			color: var(--text-muted);
		}

		.meta-info-item strong {
			color: var(--text-primary);
		}

		.section-title {
			text-align: center;
			font-size: 18px;
			font-weight: 700;
			color: var(--theme-brown-primary);
			margin-bottom: 20px;
			letter-spacing: 0.5px;
		}

		/* Form Row & Sizing Overrides */
		.add-member-bar {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 12px;
			margin-bottom: 30px;
		}

		.input-control {
			box-sizing: border-box !important;
			padding: 8px 14px !important;
			font-size: 14px !important;
			line-height: 24px !important;
			border-radius: 8px !important;
			border: 1px solid var(--border-subtle) !important;
			background-color: #faf8f5 !important;
			color: var(--text-primary) !important;
			transition: all 0.2s ease !important;
			outline: none !important;
			height: 42px !important;
		}

		select.input-control {
			width: 480px !important;
		}

		/* Fixed Pixel Width for Chosen Plugin Dropdown */
		.chzn-container {
			width: 480px !important;
		}

		.chzn-container-single .chzn-single {
			height: 42px !important;
			line-height: 40px !important;
			border-radius: 8px !important;
			border: 1px solid var(--border-subtle) !important;
			background: #faf8f5 !important;
			box-shadow: none !important;
			color: var(--text-primary) !important;
			padding-left: 14px !important;
		}

		.chzn-container-active .chzn-single {
			border-color: var(--theme-brown-primary) !important;
			box-shadow: 0 0 0 3px rgba(124, 64, 30, 0.12) !important;
		}

		.btn-add-custom {
			background: var(--theme-brown-primary);
			color: #ffffff !important;
			font-weight: 700;
			font-size: 13px;
			padding: 0 24px;
			height: 42px;
			border-radius: 8px;
			border: none;
			cursor: pointer;
			transition: all 0.2s ease;
			box-shadow: 0 4px 12px rgba(124, 64, 30, 0.2);
			display: inline-flex;
			align-items: center;
			gap: 6px;
			line-height: 42px;
		}

		.btn-add-custom:hover {
			background: var(--theme-brown-hover);
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(124, 64, 30, 0.3);
		}

		/* Table Enhancements */
		.table-wrapper {
			border-radius: 10px;
			border: 1px solid var(--border-subtle);
			/*overflow: hidden;*/
		}

		.tablea {
			width: 100%;
			margin-bottom: 0;
			border-collapse: separate;
			border-spacing: 0;
		}

		.tablea thead th {
			background-color: #f3ebe3 !important;
			color: var(--text-primary);
			font-weight: 700;
			font-size: 12px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			padding: 12px 14px;
			border-bottom: 1px solid var(--border-subtle);
		}

		.tablea tbody td {
			padding: 10px 14px;
			vertical-align: middle;
			border-bottom: 1px solid #f0e9e2;
			font-size: 13px;
		}

		.tablea tbody tr:last-child td {
			border-bottom: none;
		}

		.tablea tbody tr:hover {
			background-color: #faf5f0;
			cursor: pointer;
		}

		.rActive {
			background-color: #f2e4d8 !important;
		}

		.badge-project {
			background-color: #f5ebe6;
			color: var(--theme-brown-primary);
			font-size: 11px;
			font-weight: 700;
			padding: 2px 8px;
			border-radius: 4px;
			border: 1px solid var(--border-subtle);
			margin-left: 4px;
			display: inline-block;
		}

		.btn-remove-custom {
			background-color: #dc2626;
			color: #ffffff !important;
			font-weight: 700;
			font-size: 12px;
			padding: 6px 16px;
			border-radius: 6px;
			border: none;
			cursor: pointer;
			transition: all 0.2s ease;
		}

		.btn-remove-custom:hover {
			background-color: #b91c1c;
		}

		.tdpadleft {
			padding-left: 10px;
		}
	</style>
<?php
$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
if( $eas_id && $delID ){
	$db->delete('emp_assign_detail',array('ead_id'=>$delID));
	$_SESSION['notif_warning']='Employee Removed!';
	functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
	die();
}
if( isset($_POST['btnAdd']) ){
	$emp_id = ( isset($_POST['selEmp']) && !empty($_POST['selEmp']) ) ? trim($_POST['selEmp']) : '';
	if( $eas_id && $emp_id ){
		$arrField = array('emp_id'=>$emp_id,'eas_id'=>$eas_id);
		if( $db->getValue('emp_assign_detail','count(*)',$arrField)==0 ){
			$db->insert('emp_assign_detail',$arrField);
			$_SESSION['notif_success']='Employee Added!';
			functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
			die();
		}
		else{
			functions::say('Employee already assigned!');
			functions::sendTo(functions::pageName().'?easid='.functions::encode($eas_id));
			die();
		}
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="form-card">
	<div class="form-card-header">
		<h2><i class="halflings-icon white edit"></i> Employee Group Assignment</h2>
	</div>

	<ul class="nav-tabs-custom">
		<li><a href="assignment_manage.php?easid=<?php echo functions::encode($eas_id)?>">Manage Assignment</a></li>
		<li class="active"><a href="assignment_group_select.php?easid=<?php echo functions::encode($eas_id)?>">Select Name</a></li>
	</ul>

	<div class="form-card-body">
		<div class="meta-info-bar">
			<div class="meta-info-item">Project / Department: <strong><?php echo $proj_name;?></strong></div>
			<?php if($group_name){?>
				<div class="meta-info-item">Group Name: <strong><?php echo $group_name;?></strong></div>
			<?php }?>
		</div>

		<form id="showform" name="showform" method="post">
			<div class="section-title">GROUP ASSIGNMENT MEMBER</div>

			<div class="add-member-bar">
				<select name="selEmp" id="selEmp" data-rel="chosen" class="input-control">
					<option value="">--Select Employee--</option>
					<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
					while($rEU = $db->fetch_array($qEU)):
					?>
					<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
					<?php endwhile;?>
				</select>
				<button type="submit" name="btnAdd" id="btnAdd" class="btn-add-custom">
					<i class="halflings-icon white plus"></i> Add Member
				</button>
			</div>

			<div class="table-wrapper">
				<table id="tblist" class="tablea">
					<thead>
						<tr>
							<th width="5%" style="text-align:center;">#</th>
							<th width="15%" class="tdpadleft">ID Number</th>
							<th class="tdpadleft">Name</th>
							<th class="tdpadleft">Position</th>
							<th width="25%" class="tdpadleft">Status</th>
							<th width="8%" style="text-align:center;"><input type="checkbox" name="checkAll" id="checkAll" value="all"></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$qDisp = $db->select('emp_assign_detail ead, employee e','*',array('eas_id'=>$eas_id),'AND ead.emp_id=e.emp_id ORDER BY lname');
						$count=1;
						while($rDisp = $db->fetch_array($qDisp)):
							$rID = $rDisp['ead_id'];
						?>
						<tr>
							<td style="text-align:center;"><?php echo $count++;?></td>
							<td class="tdpadleft"><?php echo $rDisp['emp_no']?></td>
							<td class="tdpadleft"><strong><?php echo $rDisp['lname'].', '.$rDisp['fname']?></strong></td>
							<td class="tdpadleft"><?php echo position($rDisp['emp_id'])?></td>
							<td class="tdpadleft"><?php echo work_status($rDisp['emp_id'])?>&nbsp;&nbsp;<a id="adcworkstat<?php echo $rID;?>" href="#" class="thickbox" title="Manage Status" onclick="showThis(this.id,'employee_add_work_stat.php?eid=<?php echo functions::encode($rDisp['emp_id'])?>&fromED=t','Work Status')"><i class="halflings-icon pencil"></i></a></td>
							<td style="text-align:center;">
								<input type="checkbox" class="chkDel" name="chkDel[<?php echo $rID; ?>]" id="chkDel[<?php echo $rID; ?>]" value="<?php echo $rID; ?>">
								<a id="del<?php echo $rID;?>" style="display:none;" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Employee" data-rel="tooltip" href="?easid=<?php echo functions::encode($eas_id);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
							</td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td colspan="5">&nbsp;</td>
							<td style="text-align:center; padding: 12px 0;">
								<button type="submit" class="btn-remove-custom" name="btnRemoved" id="btnRemoved">Remove</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
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
	$('#tblist tr').click(function(event) {
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