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
$project_id = ( isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : 0;
$selDate = ( isset($_REQUEST['dte']) && !empty($_REQUEST['dte']) ) ? functions::decode($_REQUEST['dte']) : 0;
$collectionStatus = ( isset($_REQUEST['colstat']) && !empty($_REQUEST['colstat']) ) ? $_REQUEST['colstat'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Billing Report</title>
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
	<style>.padleft{padding-right: 5px; padding-left: 5px;}</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header">
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Project Billing Report</h2>
		</div><br>
		<div align="left">Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$project_id))?></strong></div>
		<div align="left">
			Cost:
			<strong>
				<?php 
				$proj_cost = $db->getValue('project','proj_cost',array('proj_id'=>$project_id));
				echo functions::formatMoney($proj_cost);
				?>
			</strong>
		</div><br>
		<table class="table-hover table table-bordered table-striped" width="100%" border="0" style="font-size:12px;">
			<thead>
				<tr style="background-color:#CCC;">
					<td class="padleft" width="11%"><strong>Transaction</strong></td>
					<td class="padleft" width="8%"><div align="right"><strong>Proposed Bill</strong></div></td>
					<td class="padleft" width="8%"><div align="right"><strong>% Billed</strong></div></td>
					<td class="padleft" width="8%"><div align="right"><strong>VAT</strong></div></td>
					<td class="padleft" width="8%"><div align="right"><strong>EWT</strong></div></td>
					<td class="padleft" width="8%"><div align="right"><strong>Retention</strong></div></td>
					<td class="padleft" width="8%"><div align="right"><strong>Contractor's Tax</strong></div></td>
					<td class="padleft" width="8%"><div align="right"><strong>Recoupment</strong></div></td>
					<td class="padleft" width="8%"><div align="right"><strong>Net Bill</strong></div></td>
					<td class="padleft" width="8%"><div align="center"><strong><?php echo ($collectionStatus==2) ? "Submitted" : "Collected";?></strong></div></td>
				</tr>
			</thead>
			<tbody>
				<?php
				$qProj = $db->select('project','*',array('proj_id'=>$project_id));
				while( $rProj = $db->fetch_array($qProj)):
					$proj_cost = $rProj['proj_cost'];
					if($collectionStatus==2)
						$qPI = $db->select('project_income','*',array('proj_id'=>$project_id),' AND left(submit_date,7)="'.$selDate.'" AND pi_date IS NULL ORDER BY submit_date ASC');
					else
						$qPI = $db->select('project_income','*',array('proj_id'=>$project_id),' AND left(pi_date,7)="'.$selDate.'" ORDER BY pi_date ASC');
					$countBilledPercent=0;
					$totalAmountBilled=0;
					$totalNetBilled=0;
					while($rPI = $db->fetch_array($qPI)):
						$totalAmountBilled += $amountBilled = $rPI['amount'];
						$netBilled = ($amountBilled - $rPI['vat'] - $rPI['ewt'] - $rPI['retention'] - $rPI['contractor'] - $rPI['recoupment']);
						$totalNetBilled += $netBilled;
						$billedPercent = ($proj_cost && $amountBilled) ? functions::formatMoney(($amountBilled / $proj_cost) * 100) : 0;
						$countBilledPercent += $billedPercent;
				?>
				<tr>
					<td class="padleft"><?php echo $rPI['name'];?></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($amountBilled);?></div></td>
					<td class="padleft"><div align="right"><?php echo $billedPercent.'%';?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['vat']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['ewt']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['retention']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['contractor']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($rPI['recoupment']);?></div></td>
					<td class="padleft"><div align="right"><?php echo functions::formatMoney($netBilled);?></div></td>
					<td class="padleft"><div align="center"><?php echo ($collectionStatus==2) ? functions::datearr($rPI['submit_date']) : functions::datearr($rPI['pi_date']);?></div></td>
				</tr>
				<?php endwhile;?>
				<tr>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalAmountBilled);?></strong></div></td>
					<td class="padleft"><div align="right"><strong><?php echo $countBilledPercent.'%'?></strong></div></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalNetBilled);?></strong></div></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="10">&nbsp;</td>
				</tr>
				<?php endwhile;?>
			</tbody>
		</table>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?yr="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>