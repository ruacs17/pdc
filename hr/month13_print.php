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
$month13Year = ( isset($_SESSION['month13Year']) ) ? $_SESSION['month13Year'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>13 Month Print</title>
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
	.padRight{padding-right:10px;}
	.padLeft{padding-left:5px;}
	</style>
	<script>window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
	<thead>
		<tr>
			<td align="center">
				<?php 
				require_once('../class/print_header.php');
				print_header('13 Month Payroll for '.$month13Year);
				?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="500" valign="top">
				<div align="center">
					<table width="100%" border="1" style="font-size: 12px;">
						<thead>
							<tr>
								<th width="5%" scope="col" class="padLeft"><div align="left">No.</div></th>
								<th width="25%" scope="col" class="padLeft"><div align="left">Name</div></th>
								<th width="10%" scope="col" class="padRight"><div align="right">Total Gross</div></th>
								<th width="10%" scope="col" class="padRight"><div align="right">Total Absent</div></th>
								<th width="10%" scope="col" class="padRight"><div align="right">Total Net</div></th>
								<th width="10%" scope="col" class="padRight"><div align="right">13 Month</div></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$countEmp=0;
						$qDisp = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','emp.emp_id, lname, fname, sum(gross) as totalGross, sum(absent) as totalAbsent, sum(gross-absent) as totalNet,(sum(gross-absent)/12) as month13',array('att_year'=>$month13Year),'AND ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id GROUP BY emp.emp_id ORDER BY lname,fname');
						#echo $db->last_query;
						$total13month=0;
						while($rDisp = $db->fetch_array($qDisp)):
							$countEmp++;
							$emp_id = $rDisp['emp_id'];
							$total13month += $rDisp['month13'];
						?>
						<tr>
							<td class="padLeft"><?php echo $countEmp?>.</td>
							<td class="padLeft"><?php echo $rDisp['lname'].', '.$rDisp['fname']; ?></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($rDisp['totalGross']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($rDisp['totalAbsent']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($rDisp['totalNet']);?></div></td>
							<td class="padRight"><div align="right"><?php echo functions::formatMoney($rDisp['month13']);?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td colspan="6" class="padRight"><div align="right"><strong>Total: <?php echo functions::formatMoney($total13month);?></strong></div></td>
						</tr>
						</tbody>
					</table>
					<div align="right" style="font-size:10px">18HRD.FRM011.01-03/19</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
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