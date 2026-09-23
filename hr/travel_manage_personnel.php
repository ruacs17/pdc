<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$to_id = (isset($_REQUEST['to']) && !empty($_REQUEST['to']) ) ? functions::decode($_REQUEST['to']) : 0;
$personnel_id='';
$project_id = $db->getValue('travel_order','proj_id',array('to_id'=>$to_id));
$origin='';$destination='';$est_time_from='';$est_time_to='';$act_time_from='';$act_time_to='';$travel_date=date('Y-m-d');

$to_date = $db->getValue('travel_order','to_date',array('to_id'=>$to_id));
$travel_date = ($to_date) ? $to_date : date('Y-m-d');

if( isset($_REQUEST['itmDlt']) && !empty($_REQUEST['itmDlt']) ){
	$itmDlt = functions::decode($_REQUEST['itmDlt']);
	$db->delete('travel_personnel',array('tp_id'=>$itmDlt));
	$_SESSION['notif_warning']='Personnel Removed!';
	functions::sendTo(functions::pageName().'?to='.functions::encode($to_id));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Travel Order Personnel Details</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
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
if( isset($_POST['btnAdd']) ){
	$personnel_id = ( isset($_POST['selPersonnel']) && !empty($_POST['selPersonnel']) ) ? trim($_POST['selPersonnel']) : '';

	if( $personnel_id && $to_id ){
		if( $db->getValue('travel_personnel','count(*)',array('to_id'=>$to_id,'personnel_id'=>$personnel_id))==0 )
			$ins = $db->insert('travel_personnel',array('to_id'=>$to_id,'personnel_id'=>$personnel_id));
			if($ins)
				$_SESSION['notif_success']='Personnel Added!';
		functions::sendTo(functions::pageName().'?to='.functions::encode($to_id));
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Travel Order Personnel Details</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a style="opacity:.9" href="travel_manage_personnel.php?to=<?php echo functions::encode($to_id);?>">Personnel</a></li>
				<li><a href="travel_manage_detail.php?to=<?php echo functions::encode($to_id);?>">Itinerary</a></li>
				<li><a href="travel_manage.php?to=<?php echo functions::encode($to_id);?>">Travel Order</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="left">
					<div>Project / Department:
						<strong>
							<?php 
							if($project_id){
								$qProj = $db->select('project','*',array('proj_id'=>$project_id));
								while($rProj = $db->fetch_array($qProj)):
									echo strtoupper($rProj['proj_name']);
									echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
								endwhile;
							}
							?>
						</strong>
					</div>
					<div>Date: <strong><?php echo functions::datearr($to_date);?></strong></div><br><br>
				</div>
				<input type="hidden" name="txitmEdt" id="txitmEdt" value="<?php echo functions::encode($itmEdt);?>">
				<table width="35%" border="0" align="center" style="font-size: 14px" class="table-hover">
					<thead>
						<tr>
							<td width="50%" style="padding: 10px;">
								<div align="left">
									<select name="selPersonnel" id="selPersonnel" data-rel="chosen" style="width:350px; text-align:left;">
										<option value="">-- Select Personnel --</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td width="8%">
								<div align="center">
									<input type="submit" class="btn btn-small btn-primary" value="   Add   " name="btnAdd">
								</div>
							</td>
						</tr>
					</thead>
					<tbody>
						<?php
						$count=0;
						$qPersnl = $db->select('travel_personnel top, employee emp','*',array('to_id'=>$to_id),'AND top.personnel_id=emp.emp_id ORDER BY lname');
						while($rPersnl = $db->fetch_array($qPersnl)):
							$rPersnlID = $rPersnl['tp_id'];
							$count++;
						?>
						<tr>
							<td style="padding: 12px;">
								<div align="left"><?php echo $count.'. <strong>'.strtoupper($rPersnl['lname'].', '.$rPersnl['fname']).'</strong>';?></div>
							</td>
							<td>
								<div align="center">
									<a class="btn btn-small btn-danger" title="Delete Personnel" data-rel="tooltip" onClick="return delt()" href="?itmDlt=<?php echo functions::encode($rPersnlID);?>&to=<?php echo functions::encode($to_id);?>"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br><br><br>
				<table width="100%" border="0" align="center" class="table table-striped table-bordered table-hover" style="font-size: 12px">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="8%" scope="col"><div align="center">TRAVEL DATE</div></th>
							<th width="18%" scope="col"><div align="center">ORIGIN</div></th>
							<th width="18%" scope="col"><div align="center">DESTINATION</div></th>
							<th colspan="3" scope="col"><div align="center">ESTIMATED TIME </div></th>
							<th colspan="3" scope="col"><div align="center">ACTUAL TIME </div></th>
						</tr>
						<tr style="background-color:#CCC;">
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td width="7%"><div align="center"><strong>Start</strong></div></td>
							<td width="7%"><div align="center"><strong>End</strong></div></td>
							<td width="7%"><div align="center"><strong>Duration</strong></div></td>
							<td width="7%"><div align="center"><strong>Start</strong></div></td>
							<td width="7%"><div align="center"><strong>End</strong></div></td>
							<td width="7%"><div align="center"><strong>Duration</strong></div></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$qDisp = $db->select('travel_order_detail','*',array('to_id'=>$to_id),'ORDER BY travel_date');
						while($rDisp = $db->fetch_array($qDisp)):
							$rID = $rDisp['tod_id'];
							$est_mins = ($rDisp['est_time_from'] && $rDisp['est_time_to']) ? functions::min_diff($rDisp['est_time_from'],$rDisp['travel_date'],$rDisp['est_time_to'],$rDisp['travel_date']) : 0;
						?>
						<tr>
							<td><div align="center"><?php echo functions::datearr($rDisp['travel_date']);?></div></td>
							<td><div align="left"><?php echo $rDisp['origin'];?></div></td>
							<td><div align="left"><?php echo $rDisp['destination'];?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['est_time_from']);?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['est_time_to']);?></div></td>
							<td><div align="center"><?php echo functions::min_to_hour($est_mins);?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_from']);?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_to']);?></div></td>
							<td><div align="center"><?php echo functions::min_to_hour($rDisp['mins_travel']);?></div></td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br>
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
function delt(){
	if(confirm('Do you want to remove this personnel?'))
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