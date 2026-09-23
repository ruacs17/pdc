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

$qAO = $db->select('travel_order','*',array('to_id'=>$to_id));
$rAO = $db->fetch_array($qAO);
$requested_by=$db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rAO['requested_by']));
$requested_date=$rAO['requested_date'];
$requested_position = position($rAO['requested_by']);
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

$project_id = $rAO['proj_id'];
$to_date = $rAO['to_date'];
$purpose = $rAO['purpose'];
$travel_date = ($to_date) ? $to_date : date('Y-m-d');
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
	<title>Travel Order Print</title>
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
				print_header('Travel Order Request');
				?>
			</td>
		</tr>
		<tr>
			<td valign='bottom'>&nbsp;</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" class="table table-bordered" style="font-size: 12px">
					<tr>
						<td align="right" width="20%">Charging Project / Department:</td>
						<td align="left"><strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$project_id));?></strong></td>
					</tr>
					<tr>
						<td align="right">Purpose: </td>
						<td align="left"><strong><?php echo $purpose;?></strong></td>
					</tr>
					<tr>
						<td align="right">Date File:</td>
						<td align="left"><strong><?php echo functions::datearr($to_date);?></strong></td>
					</tr>
				</table>
				<?php
				$count=0;
				$qPersnl = $db->select('travel_personnel top, employee emp','*',array('to_id'=>$to_id),'AND top.personnel_id=emp.emp_id ORDER BY lname');
				if($db->num_rows($qPersnl))
					echo '<div align="left">Personnel(s):</div>';
				while($rPersnl = $db->fetch_array($qPersnl)):
					$rPersnlID = $rPersnl['tp_id'];
					$count++;
					$pos=position($rPersnl['emp_id']);
					$pos = ($pos) ? '&nbsp;&nbsp;<i style="font-size:12px">('.$pos.')</i>' : '';
					echo $count.'. <strong>'.strtoupper($rPersnl['lname'].', '.$rPersnl['fname']).'</strong>'.$pos.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				endwhile;?><br><br>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="500" valign="top">
				<table width="100%" border="1" align="center" style="font-size: 12px">
					<tr>
						<th width="10%" scope="col"><div align="center">TRAVEL DATE</div></th>
						<th width="17%" scope="col"><div align="center">ORIGIN</div></th>
						<th width="17%" scope="col"><div align="center">DESTINATION</div></th>
						<th colspan="3" scope="col"><div align="center">ESTIMATED TIME </div></th>
						<th colspan="3" scope="col"><div align="center">ACTUAL TIME </div></th>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td width="8%"><div align="center"><strong>Start</strong></div></td>
						<td width="8%"><div align="center"><strong>End</strong></div></td>
						<td width="7%"><div align="center"><strong>Duration</strong></div></td>
						<td width="8%"><div align="center"><strong>Start</strong></div></td>
						<td width="8%"><div align="center"><strong>End</strong></div></td>
						<td width="7%"><div align="center"><strong>Duration</strong></div></td>
					</tr>
					<?php
					$qDisp = $db->select('travel_order_detail','*',array('to_id'=>$to_id),'ORDER BY travel_date');
					while($rDisp = $db->fetch_array($qDisp)):
						$rID = $rDisp['tod_id'];
						$est_mins = ($rDisp['est_time_from'] && $rDisp['est_time_to']) ? functions::min_diff($rDisp['est_time_from'],$rDisp['travel_date'],$rDisp['est_time_to'],$rDisp['travel_date']) : 0;
					?>
					<tr>
						<td height="30px"><div align="center"><?php echo functions::datearr($rDisp['travel_date']);?></div></td>
						<td><div align="center"><?php echo $rDisp['origin'];?></div></td>
						<td><div align="center"><?php echo $rDisp['destination'];?></div></td>
						<td><div align="center"><?php echo functions::MilToTwelve($rDisp['est_time_from']);?></div></td>
						<td><div align="center"><?php echo functions::MilToTwelve($rDisp['est_time_to']);?></div></td>
						<td><div align="center"><?php echo functions::min_to_hour($est_mins);?></div></td>
						<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_from']);?></div></td>
						<td><div align="center"><?php echo functions::MilToTwelve($rDisp['act_time_to']);?></div></td>
						<td><div align="center"><?php echo functions::min_to_hour($rDisp['mins_travel']);?></div></td>
					</tr>
					<?php endwhile;?>
				</table><br><br>
				<table border="0" width="100%" style="font-size: 10px">
					<tr>
						<td align="center">Requested By</td>
						<td align="center">Reviewed By</td>
						<td align="center">Checked By</td>
						<td align="center">Verified By</td>
						<td align="center">Approved By</td>
					</tr>
					<tr>
						<td height="60px" valign="bottom" align="center"><strong><u>&nbsp;<?php echo $requested_by?>&nbsp;</u></strong></td>
						<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $reviewed_by?>&nbsp;</u></strong></td>
						<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $checked_by?>&nbsp;</u></strong></td>
						<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $verified_by?>&nbsp;</u></strong></td>
						<td valign="bottom" align="center"><strong><u>&nbsp;<?php echo $approved_by?>&nbsp;</u></strong></td>
					</tr>
					<tr>
						<td align="center" style="font-size:10px"><i><?php echo $requested_position?></i></td>
						<td align="center" style="font-size:10px"><i><?php echo $reviewed_position?></i></td>
						<td align="center" style="font-size:10px"><i><?php echo $checked_position?></i></td>
						<td align="center" style="font-size:10px"><i><?php echo $verified_position?></i></td>
						<td align="center" style="font-size:10px"><i><?php echo $approved_position?></i></td>
					</tr>
					<tr>
						<td align="center"><?php echo functions::datearr($requested_date)?></td>
						<td align="center"><?php echo functions::datearr($reviewed_date)?></td>
						<td align="center"><?php echo functions::datearr($checked_date)?></td>
						<td align="center"><?php echo functions::datearr($verified_date)?></td>
						<td align="center"><?php echo functions::datearr($approved_date)?></td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<footer>
	<div align="right">19HRD.FRM051.00-03/19</div>
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
<!-- end: JavaScript-->
</body>
</html>