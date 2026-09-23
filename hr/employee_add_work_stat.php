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
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$itemEdt = (isset($_REQUEST['eeEdt']) && !empty($_REQUEST['eeEdt']) ) ? functions::decode($_REQUEST['eeEdt']) : 0;
$itemDel = (isset($_REQUEST['eeDlt']) && !empty($_REQUEST['eeDlt']) ) ? functions::decode($_REQUEST['eeDlt']) : 0;
$selWorkStat='';$selMon='';$selDay='';$selYr='';$project_based='';$ews_date='';
if($itemEdt){
	$q = $db->select('emp_work_status','*',array('ews_id'=>$itemEdt,'emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$selWorkStat = $r['ews_stat'];
		$ews_date = $r['ews_date'];
		$project_based=$r['project_based'];
	endwhile;
}
if($itemDel){
	$_SESSION['notif_warning']='Status Removed!';
	$db->delete('emp_work_status',array('ews_id'=>$itemDel,'emp_id'=>$eid));
	$q = $db->select('emp_work_status','*',array('emp_id'=>$eid),'ORDER BY ews_date DESC,ews_id DESC LIMIT 1');
	$r = $db->fetch_array($q);
	$ewsstat = $r['ews_stat'];
	$ewsdate = $r['ews_date'];
	$db->update('employee',array('work_status'=>$ewsstat),array('emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Work Status</title>
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
	<style type="text/css">body{font-size: 12px;}</style>
<?php
if( isset($_POST['btnSave']) ){

	$ews_stat = ( isset($_POST['selWorkStat']) ) ? $_POST['selWorkStat'] : '';
	$selWorkStat = $ews_stat;

	$project_based = ( isset($_POST['chkProjBased']) ) ? $_POST['chkProjBased'] : NULL;

	$ews_date = ( isset($_POST['txStatDate']) && !empty($_POST['txStatDate']) ) ? trim($_POST['txStatDate']) : NULL;
	if($ews_date && $ews_date){
		$arrField = array('emp_id'=>$eid,'ews_stat'=>$ews_stat,'ews_date'=>$ews_date,'project_based'=>$project_based);
		if($itemEdt){
			$_SESSION['notif_id_list']=$itemEdt;
			$_SESSION['notif_success']='Changes Saved!';
			$db->update('emp_work_status',$arrField,array('ews_id'=>$itemEdt,'emp_id'=>$eid));
		}
		else{
			// if( $ews_id=$db->getValue('emp_work_status','ews_id',array('ews_stat'=>$ews_stat,'emp_id'=>$eid)) ){
			// 	$_SESSION['notif_id_list']=$ews_id;
			// 	$_SESSION['notif_success']='Changes Saved!';
			// 	$db->update('emp_work_status',array('ews_date'=>$ews_date,'project_based'=>$project_based),array('ews_stat'=>$ews_stat,'emp_id'=>$eid));
			// }
			// else{
				$ins = $db->insert('emp_work_status',$arrField);
				if($ins){
					$_SESSION['notif_id_list']=$ins;
					$_SESSION['notif_success']='New Status Added!';
				}
			//}
		}
		$q = $db->select('emp_work_status','*',array('emp_id'=>$eid),'ORDER BY ews_date DESC,ews_id DESC');
		$r = $db->fetch_array($q);
		$ewsstat = $r['ews_stat'];
		$ewsdate = $r['ews_date'];
		$db->update('employee',array('work_status'=>$ewsstat),array('emp_id'=>$eid));

		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
		die();
	}
	else
		functions::say('Please fill up the form properly!');
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
		<?php if(empty($fromED)){ ?>
		<div class="box-content">
			<div align="right" class="nav tab-menu nav-tabs" style="padding-top: 3px;"><?php require_once('employee_options.php');?></div>
		</div>
		<?php } ?>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>WORK STATUS</h2></div>
				<div align="center">
					<div style="width:50%;">
						<table id="tblist" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size: 12px;">
							<thead>
								<tr style="background-color:#E4E1E1; font-size: 12px;">
									<td width="35%"><strong>STATUS</strong></td>
									<td width="25%"><strong>DATE</strong></td>
									<td width="10%"><div align="center">Option</div></td>
								</tr>
							</thead>
							<tbody>
								<?php
								$q = $db->select('emp_work_status','*',array('emp_id'=>$eid),'ORDER BY ews_date DESC');
								while($r = $db->fetch_array($q)):
								$rID = $r['ews_id'];
								?>
								<tr id="rw<?php echo $rID?>">
									<td><?php echo $r['ews_stat']; echo ($r['project_based']==1) ? '&nbsp;(Project Based)' : '';?></td>
									<td><?php echo functions::datearr($r['ews_date'])?></td>
									<td>
										<div align="center">
											<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeEdt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" title="Modify" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
											<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeDlt=<?php echo functions::encode($rID)?>&fromED=<?php echo $fromED;?>" onClick="return askDel()" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
										</div>
									</td>
								</tr>
								<?php endwhile;?>
							</tbody>
						</table><br><br><br>
					</div>
				</div>
				<div align="center">
					<div style="width:50%;">
						<table align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
							<tr>
								<td width="17%" height="30"><div align="right">STATUS</div></td>
								<td width="43%">
									<select name="selWorkStat" id="selWorkStat" style="width: 150px;" required>
										<option value="">--Select--</option>
										<option value="OJT" <?php if($selWorkStat=='OJT')echo 'selected="selected"';?>>OJT</option>
										<option value="Part Time" <?php if($selWorkStat=='Part Time')echo 'selected="selected"';?>>Part Time</option>
										<option value="Contractual" <?php if($selWorkStat=='Contractual')echo 'selected="selected"';?>>Contractual</option>
										<option value="Probationary" <?php if($selWorkStat=='Probationary')echo 'selected="selected"';?>>Probationary</option>
										<option value="Regular" <?php if($selWorkStat=='Regular')echo 'selected="selected"';?>>Regular</option>
										<option value="Retired" <?php if($selWorkStat=='Retired')echo 'selected="selected"';?>>Retired</option>
										<option value="Resigned" <?php if($selWorkStat=='Resigned')echo 'selected="selected"';?>>Resigned</option>
										<option value="Separated" <?php if($selWorkStat=='Separated')echo 'selected="selected"';?>>Separated</option>
										<option value="AWOL" <?php if($selWorkStat=='AWOL')echo 'selected="selected"';?>>AWOL</option>
										<option value="End of Contract" <?php if($selWorkStat=='End of Contract')echo 'selected="selected"';?>>End of Contract</option>
										<option value="Blocklisted" <?php if($selWorkStat=='Blocklisted')echo 'selected="selected"';?>>Blocklisted</option>
									</select>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<div align="left">
										<label><input type="checkbox" name="chkProjBased" value="1" <?php if($project_based==1)echo 'checked="checked"'; ?>>&nbsp;Project Based</label>
									</div>
								</td>
							</tr>
							<tr>
								<td height="30"><div align="right">DATE</div></td>
								<td>
									<a href="javascript:NewCssCal('txStatDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
									<input name="txStatDate" type="text" class="span6 mytextbox" id="txStatDate" value="<?php echo $ews_date; ?>" style="width: 90px;" readonly>
								</td>
							</tr>
							<tr>
								<td><div align="right">&nbsp;</div></td>
								<td style="padding-top:20px;padding-bottom:20px;">
									<div align="left">
										<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
										<?php if ($itemEdt): ?><a class="btn btn-small" href="?eid=<?php echo functions::encode($eid)?>&fromED=<?php echo $fromED;?>">Cancel</a><?php endif ?>
									</div>
								</td>
							</tr>
						</table>
					</div>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
function askDel(){
	if(confirm('Do you want to remove this information?'))
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

<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>