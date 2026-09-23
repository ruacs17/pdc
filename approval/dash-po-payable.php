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
$arr = array();
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');

if( isset($_POST['btnSearch']) ){
	$_SESSION['po_payable_yr'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['po_payable_mon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
	functions::sendTo(functions::pageName());
	die();
}
$txbYear = ( isset($_SESSION['po_payable_yr']) ) ? $_SESSION['po_payable_yr'] : date('Y');
$txbMon = ( isset($_SESSION['po_payable_mon']) ) ? $_SESSION['po_payable_mon'] : date('m');

if($txbMon && $txbYear){
	$arr = array('LEFT(po_date,7)'=>$txbYear.'-'.$txbMon);
	$qPODate = $db->query('SELECT DISTINCT LEFT(po_date,7) as dte FROM view_po_payment WHERE LEFT(po_date,7)="'.$db->clean($txbYear.'-'.$txbMon).'" AND balance > 0 ORDER BY po_date');
}
elseif($txbYear){
	$arr = array('LEFT(po_date,4)'=>$txbYear);
	$qPODate = $db->query('SELECT DISTINCT LEFT(po_date,7) as dte FROM view_po_payment WHERE LEFT(po_date,4)="'.$db->clean($txbYear).'" AND balance > 0 ORDER BY po_date');
}
else{
	$qPODate = $db->query('SELECT DISTINCT LEFT(po_date,7) as dte FROM view_po_payment AND balance > 0 ORDER BY po_date');
}
#echo $db->last_query;
$count=0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payable P.O.</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Payable P.O.</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="dash-po-payable-all-by-supplier.php">By All Supplier</a></li>
				<li><a href="dash-po-payable-all-by-project.php">By All Project</a></li>
				<li><a href="dash-po-payable-project-view.php">View By Project</a></li>
				<li class="active"><a href="#">View By Supplier</a></li>
			</ul>
		</div>
		<div class="box-content" align="center">
			<form method="post">
				<table width="25%" border="0">
					<tr>
						<td align="left" style="padding: 12px 0px 4px 0px">
							<select name="bdYear" id="bdYear" style="width:90px;">
								<option value="">Select Year</option>
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
						</td>
						<td valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value=" View " class="btn btn-small btn-primary"></td>
					</tr>
				</table><br><br><br>
				<div style="width:70%;">
					<table width="50%" align="center" border="1" class="table table-bordered table-hover table-striped" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<td width="15%"><strong>Month</strong></td>
								<td width="40%" height="30"><strong>Supplier / Payee</strong></td>
								<td width="15%"><div align="center"><strong>Cost</strong></div></td>
								<td width="5%">&nbsp;</td>
							</tr>
						</thead>
						<tbody>
							<?php
							$curMonth='';$totalCost=0;$monthlyCost=0;$cost=0;$rank=0;
							while($rPODate = $db->fetch_array($qPODate)):
								$rank++;
								$time = mktime(0,0,0,substr($rPODate['dte'],5,2),1,$txbYear);
								$monthName = date('F',$time);
								$txbMon = date('m',$time);
							?>
							<tr>
								<td height="30"><strong><?php echo $monthName.' '.$txbYear;?></strong></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<?php
							$qPO = $db->query('SELECT vwpp.supplierID,round(sum(balance),2) as bal FROM view_po_payment vwpp, supplier s WHERE vwpp.supplierID=s.supplierID AND LEFT(po_date,7)="'.$rPODate['dte'].'" AND vwpp.balance > 0  GROUP BY supplierID ORDER BY s.name');
							while($rPO = $db->fetch_array($qPO)):
								$costPO = $rPO['bal'];
								$totalCost += $cost = $costPO;
								$monthlyCost += $cost;
								$count++;
							?>  
							<tr>
								<td>&nbsp;</td>
								<td height="30"><?php echo '<strong>'.$db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID'])).'</strong>';?></td>
								<td><div align="right" style="padding-right:50px;"><a id="costdetail<?php echo $count?>" class="label label-info thickbox" title="View P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'dash-po-payable-detail-supplier.php?yr=<?php echo functions::encode($txbYear)?>&mn=<?php echo functions::encode($txbMon)?>&sup=<?php echo functions::encode($rPO['supplierID'])?>','P.O. Details','1')"><?php echo functions::formatMoney($cost);?></a></div></td>
								<td><div align="center"><a id="whprint<?php echo $count?>" class="btn btn-small btn-info thickbox" onclick="showThis(this.id,'dash-po-payable-print.php?yr=<?php echo functions::encode($txbYear)?>&mn=<?php echo functions::encode($txbMon)?>&sup=<?php echo functions::encode($rPO['supplierID'])?>','Print P.O.','1')"><i class="halflings-icon white print"></i></a></div></td>
							</tr>
							<?php endwhile;#endwhile $rPO?>
							<tr>
								<td>&nbsp;</td>
								<td><div align='right'><strong>Monthly Cost of <?php echo $monthName.' '.$txbYear;?></strong></div></td>
								<td><div align="right" style="padding-right:50px;"><strong><?php echo functions::formatMoney($monthlyCost);?></strong></div></td>
								<td>&nbsp;</td>
							</tr>  
							<?php 
							$monthlyCost=0;
							endwhile;#endwhile PODate?>
							<tr>
								<td height="30"></td>
								<td><div align="right"><strong>Total Cost</strong></div></td>
								<td><div align="right" style="padding-right:50px;"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
								<td>&nbsp;</td>
							</tr>
						</tbody>
					</table>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>