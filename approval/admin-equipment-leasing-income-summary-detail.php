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
$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : '';
$mn = (isset($_REQUEST['mn']) && !empty($_REQUEST['mn']) ) ? functions::decode($_REQUEST['mn']) : '';
$mode = (isset($_REQUEST['mode']) && !empty($_REQUEST['mode']) ) ? $_REQUEST['mode'] : '';
$paidunpaid = (isset($_REQUEST['paidunpaid']) && !empty($_REQUEST['paidunpaid']) ) ? $_REQUEST['paidunpaid'] : '';
$mnyr = $yr.'-'.$mn;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Leasing Income Details</title>
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
	<style type="text/css">body{font-size:12px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Equipment Leasing Income Detail</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table class="table table-hover">
					<?php
					$overall_amount=0;
					$arr=array('left(iel_date,7)'=>$mnyr);
					if($paidunpaid=='paid')
						$arr = array_merge($arr,array('is_paid'=>1));
					if($paidunpaid=='unpaid')
						$arr = array_merge($arr,array('is_paid'=>2));
					if($mode=='inside')
						$q = $db->select('inhouse_equip_leasing iel','*',$arr,'AND proj_id IS NOT NULL ORDER BY iel_date');
					if($mode=='outside')
						$q = $db->select('inhouse_equip_leasing iel','*',$arr,'AND payee <> "" ORDER BY iel_date');
					#echo $db->last_query;
					while($r = $db->fetch_array($q)):
						$iel_id = $r['iel_id'];
					?>
					<tr>
						<th style="background-color:#CCC;"><div><?php echo ($mode=='inside') ? $db->getValue('project','proj_name',array('proj_id'=>$r['proj_id'])) : $r['payee'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<i><?php echo functions::datearr($r['iel_date']) ?></i>)</div></th>
					</tr>
					<tr>
						<td>
							<table width="100%" border="0" align="center">
								<thead>
									<tr>
										<th width="50%" scope="col"><div align="left">Item</div></th>
										<th width="7%" scope="col"><div align="center">Duration (Hour)</div></th>
										<th width="8%" scope="col"><div align="right">Price</div></th>
										<th width="9%" scope="col"><div align="center">Discount</div></th>
										<th width="8%" scope="col"><div align="right">Amount</div></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$total_amount=0;$disc_amount=0;$amount=0;
									$qPOI = $db->select('inhouse_equip_leasing_item','*',array('iel_id'=>$iel_id));
									while($rPOI = $db->fetch_array($qPOI)):
										$amount = $rPOI['cost'] * $rPOI['duration'];
										$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
										$amount = $amount - $disc_amount;
										$total_amount += $amount;
										$overall_amount+=$amount;
										$equipmentName = '';
										$qEqp = $db->select('equipment','*',array('equip_id'=>$rPOI['equip_id']));
										while($rEqp = $db->fetch_array($qEqp)):
											$equipmentName = ucwords(strtolower($rEqp['equip_desc']." ".$rEqp['plate_no']." ".$rEqp['serial_no']));
										endwhile;

									?>
									<tr>
										<td><?php echo $equipmentName;?></td>
										<td><div align="center"><?php echo round($rPOI['duration'],2);?></div></td>
										<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
										<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
										<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
									</tr>
									<?php endwhile;?>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td colspan="2"><div align="right">Total Amount&nbsp;&nbsp;<strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
									</tr>
								</tbody>
							</table><p>&nbsp;</p>
						</td>
					</tr>
					<?php endwhile; ?>
					<tr>
						<td><div align="right">Overall Amount &nbsp;&nbsp; <strong><?php echo functions::formatMoney($overall_amount) ?></strong></div></td>
					</tr>
				</table>

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
<!-- end: JavaScript-->
</body>
</html>