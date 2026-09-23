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

$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? $db->clean(functions::decode($_REQUEST['yr'])) : 0;
$mn = (isset($_REQUEST['mn']) && !empty($_REQUEST['mn']) ) ? $db->clean(functions::decode($_REQUEST['mn'])) : 0;
$proj_id = (isset($_REQUEST['prj']) && !empty($_REQUEST['prj']) ) ? functions::decode($_REQUEST['prj']) : 0;
$qYrMn='';
if($yr && $mn)
	$qYrMn = ' AND LEFT(p.po_date,7)="'.$yr.'-'.$mn.'"';
elseif($yr)
	$qYrMn = ' AND LEFT(p.po_date,4)="'.$yr.'"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Items</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>All Purchase Order Details By Supplier/Payee</h2>
		</div>
		<div class="box-content">
			<div>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id));?></strong></div><br><br>
			<table width="50%" align="center" border="0" class="table table-bordered table-hover table-striped" style="font-size:12px;" >
				<thead>
					<tr style="background-color:#E4E1E1">
						<th width="70%"><div align="left">Supplier/Payee</div></th>
						<th width="10%"><div align="right">Amount</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$totalAmount=0;$amount=0;$count=0;
				$qShow = $db->query('SELECT vwpp.supplierID,round(sum(balance),2) as bal FROM view_po_payment vwpp, supplier s WHERE vwpp.supplierID=s.supplierID AND balance > 0 AND vwpp.proj_id="'.$db->clean($proj_id).'" GROUP BY vwpp.supplierID ORDER BY s.name');
				while($rShow = $db->fetch_array($qShow)):
				$totalAmount += $amount = $rShow['bal'];
				?>
					<tr>
						<td><div align="left"><?php echo $db->getValue('supplier','name',array('supplierID'=>$rShow['supplierID']));?></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($amount)?></strong></div></td>
					</tr>
				<?php endwhile;?>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalAmount)?></strong></div></td>
					</tr>
				</tbody>
			</table>
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