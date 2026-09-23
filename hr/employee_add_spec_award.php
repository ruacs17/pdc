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
$txTitle='';$selMonFrom='';$selDayFrom='';$selYrFrom='';$esa_date='';
if($itemEdt){
	$q = $db->select('emp_special_award','*',array('esa_id'=>$itemEdt,'emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$txTitle = $r['esa_title'];
		$esa_date = $r['esa_date'];
	endwhile;
}
if($itemDel){
	$_SESSION['notif_warning']='Information Removed!';
	$db->delete('emp_special_award',array('esa_id'=>$itemDel,'emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
if( isset($_POST['btnSave']) ){

	$esa_title = ( isset($_POST['txTitle']) ) ? strtoupper(trim($_POST['txTitle'])) : '';
	$esa_date = ( isset($_POST['txReceivedDate']) && !empty($_POST['txReceivedDate']) ) ? trim($_POST['txReceivedDate']) : NULL;

	$arrInsert = array('emp_id'=>$eid,'esa_title'=>$esa_title,'esa_date'=>$esa_date);
	if($itemEdt){
		$_SESSION['notif_id_list']=$itemEdt;
		$_SESSION['notif_success']='Changes Saved!';
		$db->update('emp_special_award',$arrInsert,array('esa_id'=>$itemEdt,'emp_id'=>$eid));
	}else{
		$ins = $db->insert('emp_special_award',$arrInsert);
		if($ins){
			$_SESSION['notif_id_list']=$ins;
			$_SESSION['notif_success']='New Information Added!';
		}
	}
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Special Award and Recognation Received</title>
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
				<div align="center" style="padding-bottom: 15px;"><h2>SPECIAL AWARD AND RECOGNITION RECEIVED</h2></div>
					<table id="tblist" width="80%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="background-color:#E4E1E1; font-size: 12px;">
					<thead>
						<tr>
							<td width="75%"><strong>TITLE OF AWARDS AND RECOGNITIONS</strong></td>
							<td width="10%"><strong>DATE RECEIVED</strong></td>
							<td width="10%"><div align="center">&nbsp;</div></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$q = $db->select('emp_special_award','*',array('emp_id'=>$eid),'ORDER BY esa_date');
						while($r = $db->fetch_array($q)):
							$rID=$r['esa_id'];
						?>
						<tr id="rw<?php echo $rID?>">
							<td><?php echo $r['esa_title']?></td>
							<td><?php echo functions::datearr($r['esa_date'])?></td>
							<td>
								<div align="center">
								<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeEdt=<?php echo functions::encode($r['esa_id'])?>&fromED=<?php echo $fromED;?>" title="Modify" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
								<a href="?eid=<?php echo functions::encode($r['emp_id'])?>&eeDlt=<?php echo functions::encode($r['esa_id'])?>&fromED=<?php echo $fromED;?>" onClick="return askDel()" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br><br>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
					<tr>
						<td width="17%" height="30"><div align="right">TITLE OF AWARD AND RECOGNITION</div></td>
						<td width="43%"><input type="text" name="txTitle" id="txTitle" class="span6" value="<?php echo $txTitle;?>" required /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">DATE RECEIVED</div></td>
						<td>
							<a href="javascript:NewCssCal('txReceivedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
							<input name="txReceivedDate" type="text" class="span6 mytextbox" id="txReceivedDate" value="<?php echo $esa_date; ?>" style="width: 90px;" readonly>
						</td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
					<?php if ($itemEdt): ?><a class="btn btn-small" href="?eid=<?php echo functions::encode($eid)?>&fromED=<?php echo $fromED;?>">Cancel</a><?php endif ?>
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
function askDel(){
	if(confirm('Do you want to remove this award?'))
		return true;
	else
		return false;
}
function ask(){
	if(confirm('Do you want to save this information?'))
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