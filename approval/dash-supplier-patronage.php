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
$yr=date('Y');
$supplierID='';
$yrSearch = " AND left(po_date,4)='".$yr."'";
$supplierQ='';
if(isset($_POST['btnSearch'])){
	$supplierID = (isset($_POST['sel_supplier']) && !empty($_POST['sel_supplier']) ) ? functions::decode($_POST['sel_supplier']) : 0;
	$yr = (isset($_POST['yr']) && !empty($_POST['yr']) ) ? functions::decode($_POST['yr']) : 0;

	if($yr=='all')
		$yrSearch = "";
	else if($yr)
		$yrSearch = " AND left(po_date,4)='".$db->clean($yr)."'";
	if($supplierID)
		$supplierQ = "AND p.supplierID='".$db->clean($supplierID)."'";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Supplier Patronage Report</title>
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
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Patronage Report</h2>
		</div>
		<div class="box-content" align="center">
			<form method="post">
				<table width="80%" border="0">
					<tr>
						<td width="13%" align="right">Supplier / Payee:&nbsp;</td>
						<td width="32%" align="left" style="padding: 12px 0px 4px 0px">
							<select name="sel_supplier" id="sel_supplier" data-rel="chosen" style="width:550px;">
								<option value="">-- All Supplier / Payee --</option>
								<?php
								$qSup = $db->query('SELECT * FROM supplier ORDER BY name');
								while($rSup = $db->fetch_array($qSup)):
								?>
								<option value="<?php echo functions::encode($rSup['supplierID'])?>" <?php if($supplierID==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo $rSup['name']?></option>
								<?php endwhile;?>
							</select>
						</td>
						<td width="9%" align="right">Year:&nbsp;</td>
						<td width="22%" align="left" style="padding: 12px 0px 4px 0px">
							<select name="yr" id="yr" style="width:150px;" data-rel="chosen">
								<option value="<?php echo functions::encode('all')?>">-- All Year --</option>
								<?php
								$qYr = $db->query('SELECT DISTINCT left(po_date,4) as dt FROM po ORDER BY po_date DESC ');
								while($rYr = $db->fetch_array($qYr)):
								?>
								<option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($yr==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
								<?php endwhile;?>
							</select>
						</td>
						<td width="10%" valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary"></td>
					</tr>
				</table><br><br><br>
				<div style="width:70%">
				<table width="50%" align="center" border="0" class="table table-hover table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<td width="50%" height="30"><strong>Supplier / Payee</strong></td>
							<td width="15%"><div align="right"><strong>Spent</strong></div></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$totalCost=0;
						$rank=0;
						$qSup = $db->query('SELECT p.supplierID as sid, sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ) as res FROM po_item poi, po p where p.po_id=poi.po_id '.$supplierQ.$yrSearch.' GROUP BY p.supplierID ORDER BY `res` DESC ');
						while($rSup = $db->fetch_array($qSup)):
							$totalCost += $rSup['res'];
							$rank++;
						?>
						<tr>
							<td height="30"><?php echo $rank.'. <strong>'.$db->getValue('supplier','name',array('supplierID'=>$rSup['sid'])).'</strong>';?></td>
							<td><div align="right"><a id="view<?php echo $rank?>" class="thickbox" style="cursor: pointer;" title="Manage Income" data-rel="tooltip" onclick="showThis(this.id,'dash-supplier-patronage-detail.php?sid=<?php echo functions::encode($rSup['sid']);?>&yr=<?php echo functions::encode($yr);?>','Patronage Detail','1')"><?php echo functions::formatMoney($rSup['res']);?></a></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td height="30"><div align="right"><strong>Total Spent</strong></div></td>
							<td><strong><?php echo functions::formatMoney($totalCost);?></strong></td>
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
<script>function selDt(PiEwgD){window.location="dash-proj-profit.php?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>