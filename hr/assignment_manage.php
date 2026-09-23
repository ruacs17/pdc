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
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<?php if($eas_id){?><li><a href="assignment_group_select.php?easid=<?php echo functions::encode($eas_id)?>">Select Name</a></li><?php }?>
				<li class="active"><a href="assignment_manage.php?easid=<?php echo functions::encode($eas_id)?>" style="opacity:.9">Manage Assignment</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 10px;"><h2>MANAGE GROUP ASSIGNMENT</h2></div>
				<div align="center">
					<table border="0">
						<tr>
							<td height="55" width="20%">Project / Department</td>
							<td>
								<div align="left">
									<select name="selProj" id="selProj" data-rel="chosen" style="width:750px;font-size:12px;" required>
										<option value="">--Select Project / Department--</option>
										<?php 
										$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
										while($rProj = $db->fetch_array($qProj)):?>
										<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td height="55">Group Name</td>
							<td><div align="left"><input type="text" name="txName" id="txName" value="<?php echo $eas_name?>" style="width:500px;" required></div></td>
						</tr>
						<tr>
							<td height="55">Worker Type </td>
							<td>
								<div align="left">
									<select name="selPayType" id="selPayType" required>
										<option value="">--Select Worker Type--</option>
										<option value="admin" <?php if($worker_type=='admin')echo 'selected="selected"';?>>Office Personnel</option>
										<option value="labor" <?php if($worker_type=='labor')echo 'selected="selected"';?>>Labor Group</option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><div align="left" style="padding-top: 20px;"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small"></div></td>
						</tr>
					</table>
				</div>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->
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