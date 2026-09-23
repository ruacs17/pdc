<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');


if( isset($_POST['btnSubmit']) ){
	if(isset($_POST['selProj']))
		$_SESSION['mr_monintor_proj']=functions::decode($_POST['selProj']);
	$_SESSION['mrp_Unreturned'] = (isset($_POST['Unreturned'])) ? 1 : 0;
	$_SESSION['mrp_Returned'] = (isset($_POST['Returned'])) ? 1 : 0;
	$_SESSION['mrp_Transferred'] = (isset($_POST['Transferred'])) ? 1 : 0;
	$_SESSION['mrp_TurnedOver'] = (isset($_POST['TurnedOver'])) ? 1 : 0;
	$_SESSION['mrp_Damaged'] = (isset($_POST['Damaged'])) ? 1 : 0;
	$_SESSION['mrp_Disposed'] = (isset($_POST['Disposed'])) ? 1 : 0;
	functions::sendTo(functions::pageName());
	die();
}

$chkUnreturned = (isset($_SESSION['mrp_Unreturned'])) ? $_SESSION['mrp_Unreturned'] : 1;
$chkReturned = (isset($_SESSION['mrp_Returned'])) ? $_SESSION['mrp_Returned'] : 1;
$chkTransferred = (isset($_SESSION['mrp_Transferred'])) ? $_SESSION['mrp_Transferred'] : 1;
$chkTurnedOver = (isset($_SESSION['mrp_TurnedOver'])) ? $_SESSION['mrp_TurnedOver'] : 1;
$chkDamaged = (isset($_SESSION['mrp_Damaged'])) ? $_SESSION['mrp_Damaged'] : 1;
$chkDisposed = (isset($_SESSION['mrp_Disposed'])) ? $_SESSION['mrp_Disposed'] : 1;


$proj_id = (isset($_SESSION['mr_monintor_proj']) && !empty($_SESSION['mr_monintor_proj']) ) ? $_SESSION['mr_monintor_proj'] : 0;
$prjQ = $db->select('project','*',array('proj_id'=>$proj_id));
$rPrj = $db->fetch_array($prjQ);
$colorUnreturned="#fcb77b";

if( isset($_REQUEST['pic']) && $proj_id){
	$emp_id = (isset($_REQUEST['pic']) && !empty($_REQUEST['pic']) ) ? functions::decode($_REQUEST['pic']) : NULL;
	if( $mpi_id = $db->getValue('mr_project_incharge','mpi_id',array('proj_id'=>$proj_id)) )
		$db->update('mr_project_incharge',array('emp_id'=>$emp_id),array('mpi_id'=>$mpi_id));
	else
		$db->insert('mr_project_incharge',array('proj_id'=>$proj_id,'emp_id'=>$emp_id));

	if( $db->getValue('mr_project_incharge','count(*)',array('proj_id'=>$proj_id,'emp_id'=>$emp_id)) )
		$_SESSION['notif_success']='Project In-charge saved!';
	else
		$_SESSION['notif_warning']='Fail to set Project In-charge!';
	functions::sendTo(functions::pageName());
	die();
}


$proj_incharge = $db->getValue('mr_project_incharge','emp_id',array('proj_id'=>$proj_id));
/*
$proj_incharge = $db->getValue('mr_project_incharge mrpi, employee emp','concat(fname,' ',lname)',array('proj_id'=>$proj_id),'AND mrpi.emp_id=emp.emp_id');
if( !empty($proj_incharge) )
	$proj_incharge = $db->getValue('project_incharge pi, users usr','concat(fname,' ',lname)',array('proj_id'=>$proj_id),'AND pi.user_id=usr.user_id');
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>MR Assigned Per Project</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
	<script src="../js/inputInt.js"></script>
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div id="spinner"></div>
<form method="post">
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>MR Assigned Per Project</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-mr-monitor-due-by-date.php">Due By Date</a></li>
				<li><a href="admin-mr-monitor-due.php">Due By Personnel</a></li>
				<li class="active"><a href="admin-mr-monitor-assign.php" style="opacity:.9">MR Per Project</a></li>
			</ul>
		</div>

		<div class="box-content">
			<div align="left">
				<div align="right">
					<a id="mrPrint" href="admin-mr-monitor-assign-print.php?projid=<?php echo functions::encode($proj_id);?>" class="btn btn-info" title="Print Details"><i class="halflings-icon white print"></i></a>&nbsp;
				</div>
			</div><br><br>
			<div align="center">
				<form method="post">
					<table border="0">
						<tr>
							<td style="padding-right:30px;">
								<label class="checkbox inline"><input type="checkbox" name="Unreturned" value="1" <?php if($chkUnreturned){echo 'checked';} ?>> Unreturned</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Returned" value="1" <?php if($chkReturned){echo 'checked';} ?>> Returned</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Transferred" value="1" <?php if($chkTransferred){echo 'checked';} ?>> Transferred</label><br>
								<label class="checkbox inline"><input type="checkbox" name="TurnedOver" value="1" <?php if($chkTurnedOver){echo 'checked';} ?>> Turned Over</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Damaged" value="1" <?php if($chkDamaged){echo 'checked';} ?>> Damaged</label><br>
								<label class="checkbox inline"><input type="checkbox" name="Disposed" value="1" <?php if($chkDisposed){echo 'checked';} ?>> Disposed</label><br>
							</td>
							<td>
								<select name="selProj" id="selProj" data-rel="chosen" style="width:700px;">
									<option value="">--select--</option>
									<?php
									$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM mr)');
									while($rProj = $db->fetch_array($qProj)):?>
									<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
									<?php endwhile;?>
								</select>
							</td>
							<td style="padding-left:30px;"><input type="submit" class="btn btn-primary btn-small" name="btnSubmit" value="Submit"></td>
						</tr>
					</table>
				</form>
			</div>
			<?php if($rPrj['proj_name']){ ?>
			<div style="padding-top:40px;">Name of Project: <u><strong><?php echo $rPrj['proj_name'];?></strong></u></div>
			<div>Location: <u><strong><?php echo ($rPrj['proj_location']) ? $rPrj['proj_location'] : '----';?></strong></u></div>
			<?php } ?>
			<br><br>
			<div class="table-wrapper">
				<table width="100%" border="0" align="center" class="table table-bordered table-hover" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC">
							<th width="30%">List of MR'd Units</th>
							<th><div align="center">Qty</div></th>
							<th><div align="center">MR Date</div></th>
							<th><div align="center">MR Ref. No.</div></th>
							<th><div align="center">Prop Code</div></th>
							<th width="10%"><div align="center">Serial/Plate No.</div></th>
							<th><div align="center">Accountable Person</div></th>
							<th><div align="center">Status</div></th>
							<th width="15%"><div align="center">Remarks</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$countRec=0;$totalAcq=0;
					$qMRd = $db->select('mr m, mr_item mi, equipment eq, employee emp','*',array('proj_id'=>$proj_id),'AND m.mr_emp=emp.emp_id AND m.mr_id=mi.mr_id AND eq.equip_id=mi.equip_id ORDER BY mr_date');
					while($rm = $db->fetch_array($qMRd)):
						
						$stat = ($rm['mr_status']) ? $rm['mr_status'] : 'Unreturned';
						$return_date = (functions::valid_date($rm['mreturn_date'])) ? $rm['mreturn_date'] : '';
						$return_date_item = (functions::valid_date($rm['return_date'])) ? $rm['return_date'] : '';
						$date_now = date('Y-m-d');
						$mr_date = $rm['mr_date'];
						$late_days = 0;

						$display=0;
						if($stat=='Unreturned')
							$display = ($chkUnreturned) ? 1 : 0;
						else if($stat=='Returned')
							$display = ($chkReturned) ? 1 : 0;
						else if($stat=='Transferred')
							$display = ($chkTransferred) ? 1 : 0;
						else if($stat=='Turned Over')
							$display = ($chkTurnedOver) ? 1 : 0;
						else if($stat=='Damaged')
							$display = ($chkDamaged) ? 1 : 0;
						else if($stat=='Disposed')
							$display = ($chkDisposed) ? 1 : 0;

						if($display){
							$countRec++;
					?>
						<tr>
							<td><?php echo $rm['name'];?></td>
							<td><div align="center"><?php echo $rm['qty']; ?></div></td>
							<td><div align="center"><?php echo functions::datearr($rm['mr_date']); ?></div></td>
							<td><div align="center"><?php echo $rm['mr_no']; ?></div></td>
							<td><div align="center"><?php echo $rm['inventory_id']; ?></div></td>
							<td><div align="center"><?php echo $rm['serial_no']; echo ($rm['serial_no'] && $rm['plate_no']) ? ' | '.$rm['plate_no'] : $rm['plate_no']; ?></div></td>
							<td><div align="center"><?php echo $rm['lname'].', '.$rm['fname']; ?></div></td>
							<td><div align="center"><?php echo $stat; echo (functions::valid_date($rm['return_date'])) ? '<br><i>('.functions::datearr($rm['return_date']).')</i>' : ''; ?></div></td>
							<td><div align="center"><?php echo $rm['remark']; ?></div></td>
						</tr>
					<?php
						}
					endwhile;
					if($countRec==0){
					?>
					<?php
						echo '<tr><td colspan="8"><div align="center">---Nothing to Report---</div></td></tr>';
					}?>
					</tbody>
				</table>
			</div>
			<div align="center" style="padding-top:50px;">
				<table width="30%" border="0" align="center">
					<tr>
						<td>Project In-charge: </td>
					</tr>
					<tr>
						<td class="tdSpace" align="center">
							<div align="left" style="padding-top:10px;">
								<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" onChange="window.location='?pic='+this.value" style="width:300px;">
									<option value="">--select--</option>
									<?php $qEU = $db->query('SELECT * FROM employee ORDER BY lname,fname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo functions::encode($rEU['emp_id'])?>" <?php if($proj_incharge==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
</form>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
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