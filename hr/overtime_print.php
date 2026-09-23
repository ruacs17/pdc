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

$ao_id = (isset($_REQUEST['ao']) && !empty($_REQUEST['ao']) ) ? functions::decode($_REQUEST['ao']) : 0;

$project_id = $db->getValue('attendance_overtime','proj_id',array('ao_id'=>$ao_id));
$requested_by='';$requested_date='';$reviewed_by='';$reviewed_date='';$checked_by='';$checked_date='';$verified_by='';$verified_date='';$approved_by='';$approved_date='';$noted_by='';$noted_date='';
$emp_id=''; $projected_hours='';$projected_output='';$actual_output='';$actual_start_date='';$actual_start_time='';$actual_end_date='';$actual_end_time='';$total_mins='';
$ao_date = $db->getValue('attendance_overtime','ao_date',array('ao_id'=>$ao_id));
$actual_start_date =$ao_date;
$actual_end_date = $ao_date;
$qAO = $db->select('attendance_overtime','*',array('ao_id'=>$ao_id));
$rAO = $db->fetch_array($qAO);
$reason = $rAO['reason'];
$reviewed_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rAO['reviewed_by']));
$reviewed_date=$rAO['reviewed_date'];
$reviewed_position = position($rAO['reviewed_by']);
$checked_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rAO['checked_by']));
$checked_date=$rAO['checked_date'];
$checked_position = position($rAO['checked_by']);
$verified_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rAO['verified_by']));
$verified_date=$rAO['verified_date'];
$verified_position = position($rAO['verified_by']);
$approved_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rAO['approved_by']));
$approved_date=$rAO['approved_date'];
$approved_position = position($rAO['approved_by']);
$noted_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rAO['noted_by']));
$noted_date=$rAO['noted_date'];
$noted_position = position($rAO['noted_by']);
$requested_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rAO['requested_by']));
$requested_date=$rAO['requested_date'];
$requested_position = position($rAO['requested_by']);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Overtime Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link href="../css/printerfoot.css" rel="stylesheet">
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
	</style>
	<script>window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="90%" border="0" align="center">
	<thead>
		<tr>
			<td align="center">
				<?php 
				require_once('../class/print_header.php');
				print_header('Overtime Request Form');
				?>
			</td>
		</tr>
		<tr>
			<td valign='bottom'>&nbsp;</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0">
					<tr>
						<td align="center" width="50%">Reason / Objective: <strong><u>&nbsp;<?php echo $reason;?>&nbsp;</u></strong></td>
						<td align="center">Date: <strong><u>&nbsp;<?php echo functions::datearr($ao_date);?>&nbsp;</u></strong></td>
					</tr>
				</table>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="500" valign="top">
				<div align="center">
					<table width="100%" border="1" align="center" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="25%" scope="col" rowspan="2"><div align="left" style="padding-left:2px;">Name</div></th>
								<th width="23%" scope="col" colspan="2"><div align="center">Projected</div></th>
								<th width="42%" scope="col" colspan="3"><div align="center">Actual</div></th>
								<th width="8%" scope="col" rowspan="2"><div align="center">Total Hours</div></th>
							</tr>
							<tr>
								<th style="background-color:#CCC;" width="15%">Output</th>
								<th style="background-color:#CCC;" width="5%">Hours</th>
								<th style="background-color:#CCC;" width="12%">Output</th>
								<th style="background-color:#CCC;" width="13%">Start</th>
								<th style="background-color:#CCC;" width="13%">End</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						$qOT = $db->select('attendance_overtime_detail aod, employee emp','*',array('ao_id'=>$ao_id),'AND aod.emp_id=emp.emp_id ORDER BY lname');
						while($rOT = $db->fetch_array($qOT)):
							$start_date = '';
							$end_date = '';
							$rID = $rOT['aod_id'];
							if($rOT['actual_start_date'] != $rOT['actual_end_date']){
								$start_date = ' <i>('.$rOT['actual_start_date'].')</i>';
								$end_date = ' <i>('.$rOT['actual_end_date'].')</i>';
							}
						?>
							<tr>
								<td><div style="padding-left:2px;"><?php echo $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$rOT['emp_id']));?></div></td>
								<td><div align="center"><?php echo $rOT['projected_output']?></div></td>
								<td><div align="center"><?php echo $rOT['projected_hours']?></div></td>
								<td><div align="center"><?php echo $rOT['actual_output']?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($rOT['actual_start_time']).$start_date?></div></td>
								<td><div align="center"><?php echo functions::MilToTwelve($rOT['actual_end_time']).$end_date?></div></td>
								<td><div align="center"><?php echo functions::min_to_hour($rOT['total_mins']);?></div></td>
							</tr>
						<?php endwhile;?>
						</tbody>
					</table>
					<table border="0" width="100%">
						<tr>
							<td align="center">Requested By</td>
							<td align="center">Reviewed By</td>
							<td align="center">Checked By</td>
							<td align="center">Verified By</td>
							<td align="center">Approved By</td>
							<td align="center">Noted By</td>
						</tr>
						<tr>
							<td height="60px" valign="bottom" align="center"><strong><u>&nbsp;<?php echo $requested_by?>&nbsp;</u></strong></td>
							<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $reviewed_by?>&nbsp;</u></strong></td>
							<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $checked_by?>&nbsp;</u></strong></td>
							<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $verified_by?>&nbsp;</u></strong></td>
							<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $approved_by?>&nbsp;</u></strong></td>
							<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $noted_by?>&nbsp;</u></strong></td>
						</tr>
						<tr>
							<td align="center" style="font-size:10px"><i><?php echo $requested_position?></i></td>
							<td align="center" style="font-size:10px"><i><?php echo $reviewed_position?></i></td>
							<td align="center" style="font-size:10px"><i><?php echo $checked_position?></i></td>
							<td align="center" style="font-size:10px"><i><?php echo $verified_position?></i></td>
							<td align="center" style="font-size:10px"><i><?php echo $approved_position?></i></td>
							<td align="center" style="font-size:10px"><i><?php echo $noted_position?></i></td>
						</tr>
						<tr>
							<td align="center"><?php echo functions::datearr($requested_date)?></td>
							<td align="center"><?php echo functions::datearr($reviewed_date)?></td>
							<td align="center"><?php echo functions::datearr($checked_date)?></td>
							<td align="center"><?php echo functions::datearr($verified_date)?></td>
							<td align="center"><?php echo functions::datearr($approved_date)?></td>
							<td align="center"><?php echo functions::datearr($noted_date)?></td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<footer>
	<div align="right">18HRD.FRM011.01-03/19</div>
</footer>
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
<!-- end: JavaScript-->
</body>
</html>