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
$filename='';$payroll_type='';$selMonFrom='';$selDayFrom='';$selYrFrom=date('Y');$selMonTo='';$selDayTo='';$selYrTo=date('Y');

$proj_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$qEatID = $db->select('dqc_boq','*',array('proj_id'=>$proj_id));
$rEatID = $db->fetch_array($qEatID);
$prepared_by = $rEatID['prepared_by'];
$reviewed_by = $rEatID['reviewed_by'];
$approved_by = $rEatID['approved_by'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Signatory Setting</title>
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
	$prepared_by = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? trim($_POST['txPreparedBy']) : NULL;
	$reviewed_by = ( isset($_POST['txReviewedBy']) && !empty($_POST['txReviewedBy']) ) ? trim($_POST['txReviewedBy']) : NULL;
	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? trim($_POST['txApprovedBy']) : NULL;
	$prepared_by_title = position($prepared_by);
	$reviewed_by_title = position($reviewed_by);
	$approved_by_title = position($approved_by);
	$arrField = array('prepared_by'=>$prepared_by,'prepared_by_title'=>$prepared_by_title,'reviewed_by'=>$reviewed_by,'reviewed_by_title'=>$reviewed_by_title,'approved_by'=>$approved_by,'approved_by_title'=>$approved_by_title);
	if( $proj_id ){
		if( $db->getValue('dqc_boq','count(*)',array('proj_id'=>$proj_id)) ){
			$db->update('dqc_boq',$arrField,array('proj_id'=>$proj_id));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?pid='.functions::encode($proj_id));
			die();
		}
		else{
			$db->insert('dqc_boq',array('prepared_by'=>$prepared_by,'prepared_by_title'=>$prepared_by_title,'reviewed_by'=>$reviewed_by,'reviewed_by_title'=>$reviewed_by_title,'approved_by'=>$approved_by,'approved_by_title'=>$approved_by_title,'proj_id'=>$proj_id));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?pid='.functions::encode($proj_id));
			die();
		}
	}
	else
		$_SESSION['notif_warning']='Please fill up the form properly!';
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
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>BOQ SIGNATORY</h2></div>
				<div align="center">
					<table border="0">
						<tr height="55px">
							<td width="30%"><div align="right"><strong>Prepared By</strong></div></td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td>
								<div align="left">
									<select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($prepared_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr height="55px">
							<td><div align="right"><strong>Reviewed By</strong></div></td>
							<td>&nbsp;</td>
							<td>
								<div align="left">
									<select name="txReviewedBy" id="txReviewedBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($reviewed_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr height="55px">
							<td><div align="right"><strong>Approved By</strong></div></td>
							<td>&nbsp;</td>
							<td>
								<div align="left">
									<select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:300px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($approved_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>
								<div align="left" style="padding-top: 40px;">
									<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small" onClick="return ask();">
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