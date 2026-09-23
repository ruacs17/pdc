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
$qEatID = $db->select('equip_registration_signatory','*',array());
$rEatID = $db->fetch_array($qEatID);
$prepared_id = $rEatID['prepared_id'];
$prepared_by = $rEatID['prepared_by'];
$prepared_by_title = $rEatID['prepared_by_title'];


$noted_id = $rEatID['noted_id'];
$noted_by = $rEatID['noted_by'];
$noted_by_title = $rEatID['noted_by_title'];


$approved_id = $rEatID['approved_id'];
$approved_by = $rEatID['approved_by'];
$approved_by_title = $rEatID['approved_by_title'];


$arrEmp=array();
$qEU = $db->select('employee','*',array(),'ORDER BY lname');
while($rEU = $db->fetch_array($qEU)):
	$arrEmp[$rEU['emp_id']] = strtoupper($rEU['lname'].', '.$rEU['fname']);
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>BOQ Signatory Setting</title>
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
	$prepared_id = ( isset($_POST['selPreparedBy']) && !empty($_POST['selPreparedBy']) ) ? trim($_POST['selPreparedBy']) : NULL;
	$qEU = $db->select('employee','*',array('emp_id'=>$prepared_id));
	$rEU = $db->fetch_array($qEU);
	$prepared_by = strtoupper($rEU['lname'].', '.$rEU['fname']);
	$prepared_by_title = position($prepared_id);


	$noted_id = ( isset($_POST['selNotedBy']) && !empty($_POST['selNotedBy']) ) ? trim($_POST['selNotedBy']) : NULL;
	$qEU = $db->select('employee','*',array('emp_id'=>$noted_id));
	$rEU = $db->fetch_array($qEU);
	$noted_by = strtoupper($rEU['lname'].', '.$rEU['fname']);
	$noted_by_title = position($noted_id);

	$approved_id = ( isset($_POST['selApprovedBy']) && !empty($_POST['selApprovedBy']) ) ? trim($_POST['selApprovedBy']) : NULL;
	$qEU = $db->select('employee','*',array('emp_id'=>$approved_id));
	$rEU = $db->fetch_array($qEU);
	$approved_by = strtoupper($rEU['lname'].', '.$rEU['fname']);
	$approved_by_title = position($approved_id);

	$arrField = array(
		'prepared_id'=>$prepared_id,'prepared_by'=>$prepared_by,'prepared_by_title'=>$prepared_by_title,
		'noted_id'=>$noted_id,'noted_by'=>$noted_by,'noted_by_title'=>$noted_by_title,
		'approved_id'=>$approved_id,'approved_by'=>$approved_by,'approved_by_title'=>$approved_by_title);
	if( $db->getValue('equip_registration_signatory','count(*)',array()) ){
		$db->update('equip_registration_signatory',$arrField,array());
		$_SESSION['notif_success']='Changes Saved!';
		functions::sendTo(functions::pageName().'?pid='.functions::encode($proj_id));
		die();
	}
	else{
		$db->insert('equip_registration_signatory',$arrField);
		$_SESSION['notif_success']='Changes Saved!';
		functions::sendTo(functions::pageName().'?pid='.functions::encode($proj_id));
		die();
	}
}
?>
<style type="text/css">
fieldset {
    margin: 8px;
    border: 3px solid silver;
    padding: 8px;    
    border-radius: 4px;
}
legend {
    padding: 2px;    
}
</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask();">
				<div align="center" style="padding-bottom: 15px;"><h2>MOTOR VEHICLE RENEWAL OF REGISTRATION SCHEDULE AND MONITORING PLAN SIGNATORY</h2></div>
				<div align="center">
					<table border="0" width="70%">
						<tr>
							<td valign="middle" width="20%" height="50px"><div align="left"><strong>Prepared And Scheduled By</strong></div></td>
							<td>
								<select name="selPreparedBy" id="selPreparedBy" data-rel="chosen" style="width:550px;">
									<option value="">--select--</option>
									<?php foreach($arrEmp as $empid => $empname):?>
									<option value="<?php echo $empid?>" <?php if($prepared_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
									<?php endforeach;?>
								</select>
							</td>
						</tr>
						<tr>
							<td valign="middle" height="50px"><div align="left"><strong>Noted and Reviewed By</strong></div></td>
							<td>
								<select name="selNotedBy" id="selNotedBy" data-rel="chosen" style="width:550px;">
									<option value="">--select--</option>
									<?php foreach($arrEmp as $empid => $empname):?>
									<option value="<?php echo $empid?>" <?php if($noted_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
									<?php endforeach;?>
								</select>
							</td>
						</tr>
						<tr>
							<td valign="middle" height="50px"><div align="left"><strong>Approved By</strong></div></td>
							<td>
								<select name="selApprovedBy" id="selApprovedBy" data-rel="chosen" style="width:550px;">
									<option value="">--select--</option>
									<?php foreach($arrEmp as $empid => $empname):?>
									<option value="<?php echo $empid?>" <?php if($approved_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
									<?php endforeach;?>
								</select>
							</td>
						</tr>
					</table>
					<table border="0">
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>
								<div align="left" style="padding-top: 40px;">
									<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
								</div>
							</td>
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
<script>
$(document).ready(function(){
});
</script>
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this changes?'))
		return true;
	else
		return false;
}
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