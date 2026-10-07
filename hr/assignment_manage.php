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
$filename='';$payroll_type='';
$eas_id = (isset($_REQUEST['easid']) && !empty($_REQUEST['easid']) ) ? functions::decode($_REQUEST['easid']) : 0;
if($eas_id)
	$_SESSION['notif_id']=$eas_id;
$proj_id = $db->getValue('emp_assignment','proj_id',array('eas_id'=>$eas_id));
$eas_name = $db->getValue('emp_assignment','eas_name',array('eas_id'=>$eas_id));
$worker_type = $db->getValue('emp_assignment','worker_type',array('eas_id'=>$eas_id));
if(empty($proj_id))
	$proj_id = (isset($_REQUEST['projid']) && !empty($_REQUEST['projid']) ) ? functions::decode($_REQUEST['projid']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Manage Group Assignment</title>
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
		overflow: hidden;
		max-width: 850px;
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
		padding: 35px 40px;
	}

	.form-group-custom {
		margin-bottom: 22px;
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.form-group-custom label {
		font-size: 13px;
		font-weight: 700;
		color: var(--text-primary);
		text-transform: uppercase;
		letter-spacing: 0.5px;
		margin: 0;
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

	/* Fixed Pixel Widths */
	input.input-control[type="text"] {
		width: 500px !important;
	}

	select.input-control {
		width: 700px !important;
	}

	.input-control:focus {
		border-color: var(--theme-brown-primary) !important;
		background-color: #ffffff !important;
		box-shadow: 0 0 0 3px rgba(124, 64, 30, 0.12) !important;
	}

	/* Fixed Pixel Width for Chosen Plugin Dropdown */
	.chzn-container {
		width: 700px !important;
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

	.btn-save-custom {
		background: var(--theme-brown-primary);
		color: #ffffff !important;
		font-weight: 700;
		font-size: 14px;
		padding: 10px 28px;
		border-radius: 8px;
		border: none;
		cursor: pointer;
		transition: all 0.2s ease;
		box-shadow: 0 4px 12px rgba(124, 64, 30, 0.2);
		display: inline-flex;
		align-items: center;
		gap: 8px;
	}

	.btn-save-custom:hover {
		background: var(--theme-brown-hover);
		transform: translateY(-1px);
		box-shadow: 0 6px 16px rgba(124, 64, 30, 0.3);
	}

	.form-actions-custom {
		margin-top: 30px;
		padding-top: 20px;
		border-top: 1px solid var(--border-subtle);
		display: flex;
		justify-content: flex-end;
	}
</style>
<?php
if( isset($_POST['btnSave']) ){
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : 0;
	$worker_type = ( isset($_POST['selPayType']) && !empty($_POST['selPayType']) ) ? $_POST['selPayType'] : '';
	$eas_name = ( isset($_POST['txName']) && !empty($_POST['txName']) ) ? trim($_POST['txName']) : '';
	if($proj_id && $worker_type && $eas_name){
		$arrField = array('proj_id'=>$proj_id,'eas_name'=>$eas_name,'worker_type'=>$worker_type);
		if($eas_id){
			if( $db->getValue('emp_assignment','count(*)',$arrField,'AND eas_id != '.$eas_id)==0 ){
				$db->update('emp_assignment',$arrField,array('eas_id'=>$eas_id));
				$db->update('emp_attendance',array('proj_id'=>$proj_id,'payroll_type'=>$worker_type),array('eas_id'=>$eas_id));
				$_SESSION['notif_success']='Assignment Changes Saved';
				functions::sendTo('assignment_group_select.php?easid='.functions::encode($eas_id));
				die();
			}
			else{
				functions::say('Assignment already exist!');
			}
		}
		else{
			if( $db->getValue('emp_assignment','count(*)',array('proj_id'=>$proj_id,'eas_name'=>$eas_name))==0 ){
				$qIns = $db->insertPrint('emp_assignment',$arrField);
				$db->query($qIns);
				$eas_id = $db->insert_id();
				$_SESSION['notif_success']='New Assignment Created';
				functions::sendTo('assignment_group_select.php?easid='.functions::encode($eas_id));
			}
			else{
				functions::say('Assignment already exist!');
			}
		}
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="form-card">
	<div class="form-card-header">
		<h2><i class="halflings-icon white edit"></i> Manage Group Assignment</h2>
	</div>

	<ul class="nav-tabs-custom">
		<li class="active"><a href="assignment_manage.php?easid=<?php echo functions::encode($eas_id)?>">Manage Assignment</a></li>
		<?php if($eas_id){?>
			<li><a href="assignment_group_select.php?easid=<?php echo functions::encode($eas_id)?>">Select Name</a></li>
		<?php }?>
	</ul>

	<div class="form-card-body">
		<form method="post" onSubmit="return ask()">
			<div class="form-group-custom">
				<label for="selProj">Project / Department</label>
				<select name="selProj" id="selProj" data-rel="chosen" class="input-control" required>
					<option value="">--Select Project / Department--</option>
					<?php 
					$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
					while($rProj = $db->fetch_array($qProj)):?>
					<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
					<?php endwhile;?>
				</select>
			</div>

			<div class="form-group-custom">
				<label for="txName">Group Name</label>
				<input type="text" name="txName" id="txName" value="<?php echo htmlspecialchars($eas_name)?>" class="input-control" placeholder="Enter Group Name" required>
			</div>

			<div class="form-group-custom">
				<label for="selPayType">Worker Type</label>
				<select name="selPayType" id="selPayType" class="input-control" required>
					<option value="">--Select Worker Type--</option>
					<option value="admin" <?php if($worker_type=='admin')echo 'selected="selected"';?>>Office Personnel</option>
					<option value="labor" <?php if($worker_type=='labor')echo 'selected="selected"';?>>Labor Group</option>
				</select>
			</div>

			<div class="form-actions-custom">
				<button type="submit" name="btnSave" id="btnSave" class="btn-save-custom">
					<i class="halflings-icon white ok"></i> Save Assignment
				</button>
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this information?')){return true;}else{return false;}
}
</script>
<!-- end: JavaScript-->
</body>
</html>