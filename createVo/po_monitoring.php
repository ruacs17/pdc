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

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array();

$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $txSearch='';

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['poPayee'] = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
	$_SESSION['poMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['poYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo(functions::pageName());
	die();
}
if($po_id)
	$arr = array('po_id'=>$po_id);   
else{
	$txPayee = ( isset($_SESSION['poPayee']) && !empty($_SESSION['poPayee']) ) ? $_SESSION['poPayee'] : '';
	$txbMon = ( isset($_SESSION['poMon']) ) ? $_SESSION['poMon'] : date('m');
	$txbYear = ( isset($_SESSION['poYear']) ) ? $_SESSION['poYear'] : date('Y');
	if($txPayee)
		$arr = array_merge($arr,array('supplierID'=>$txPayee));

	if($txbMon && $txbYear)
		$arr = array_merge($arr,array('LEFT(po_date,7)'=>$txbYear.'-'.$txbMon));
	else if($txbMon)
		$arr = array_merge($arr,array('SUBSTRING(po_date,6,2)'=>$txbMon));
	elseif($txbYear)
		$arr = array_merge($arr,array('LEFT(po_date,4)'=>$txbYear));  
}

$qPO = $db->select('po','*',$arr,'ORDER BY po_date DESC');
$num_record = $db->getValue('po','count(*)',$arr);

if( isset($_POST['btnSearch2']) ){
	$txSearch = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	$qPO = $db->query("SELECT * FROM po WHERE po_no LIKE '%".$db->clean($txSearch)."%' OR invoice LIKE '%".$db->clean($txSearch)."%' LIMIT 0, 100");
	$num_record = $db->num_rows($qPO);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Monitoring</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Monitoring Report</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
					<tr>
						<td width="50%"><div align="right"><a id="whprint" class="btn btn-info btn-small" href="po_monitoring_print.php"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div><br><br></td>
					</tr>
				</table>
				<table border="0">
					<tr>
						<td width="22%">
							<div align="right" style="padding-right: 20px;">
								<select name="bdYear" id="bdYear" style="width:90px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array(),'ORDER BY po_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMon" id="bdMon" style="width:95px;">
									<option value="">All Month</option>
									<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
							</div>
						</td>
						<td width="15%">
							<div align="left">
								<select name="txPayee" id="txPayee" data-rel="chosen" style="width:550px;">
									<option value="">All Payee</option>
									<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
									while($rSup = $db->fetch_array($qSup)):
									?>
									<option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
									<?php endwhile;?>
								</select>
							</div>
						</td>
						<td width="8%">
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
					<tr><td colspan="3"><hr width="100%"></td></tr>
				</table>
			</form>
			<table class="table table-bordered table-hover table-striped" style="font-size:12px">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="6%">P.O. Date</th>
						<th width="5%"><div>P.O. #</div></th>
						<th width="12%">External Provider</th>
						<th width="15%">Item</th>
						<th width="5%"><div align="center">Qty</div></th>
						<th width="5%">Unit</th>
						<th width="7%">Brand</th>
						<th width="5%"><div align="center">Cost</div></th>
						<th width="5%"><div align="center">Amount</div></th>
						<th width="7%"><div align="center">Payment Term</div></th>
						<th width="7%">Delivery Date</th>
						<th width="7%">Status</th>
					</tr>
				</thead>
				<tbody>
				<?php
				while($rPO = $db->fetch_array($qPO)):
					$qPOI = $db->select('po_item','*',array('po_id'=>$rPO['po_id']));
					while($rPOI = $db->fetch_array($qPOI)):
				?>
					<tr>
						<td><?php echo functions::datearr($rPO['po_date']);?></td>
						<td><div><?php echo $rPO['po_no'];?></div></td>
						<td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></td>
						<td><?php echo $rPOI['item']?></td>
						<td><?php echo round($rPOI['qty_delivered'],2)?></td>
						<td><?php echo $rPOI['unit']?></td>
						<td><?php echo $rPOI['brand']?></td>
						<td><div align="right"><?php echo functions::formatMoney($rPOI['cost'])?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPOI['qty_delivered'] * $rPOI['cost'])?></div></td>
						<td><div align="center"><?php echo $rPO['payment_term']?></div></td>
						<td><?php echo functions::datearr($rPO['delivery_date']);?></td>
						<td><?php echo ($rPO['received']==1) ? 'Received' : 'Not Received';?></td>
					</tr>
					<?php endwhile; // rPOI
				endwhile; //rPO?>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>