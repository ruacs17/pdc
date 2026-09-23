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
function monthDisp($date=0){
	$exp = explode('-',$date);
	$monthName='';
	if(count($exp)==2){
		$mn = $exp[1];
		$yr = $exp[0];
		$mkDate = mktime(0,0,0,$mn,1,$yr);
		$monthName = date("F Y",$mkDate);
	}
	return $monthName;
}
$date_request = ( isset($_REQUEST['dte']) && !empty($_REQUEST['dte']) ) ? functions::decode($_REQUEST['dte']) : '';
$proj_start = ( isset($_REQUEST['dstart']) && !empty($_REQUEST['dstart']) ) ? functions::decode($_REQUEST['dstart']) : '';
$collectionStatus = ( isset($_REQUEST['colstat']) && !empty($_REQUEST['colstat']) ) ? $_REQUEST['colstat'] : '';
$count=0;
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
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span><?php echo ($collectionStatus==1) ? "Collected Details" : "Collectible Details";?></h2>
		</div>
		<div class="box-content" align="center">
			<form method="post"><br>
				<div align="left">Month: <strong><?php echo monthDisp($date_request)?></strong></div><br><br>
				<div style="width:80%;">
					<table width="50%" align="center" border="0" class="table table-bordered table-hover table-striped" style="font-size:12px;">
						<thead>
							<tr style="background-color:#e2dcdc">
								<td width="70%" height="30"><strong>Project</strong></td>
								<td width="10%"><div align="center"><strong>Cost</strong></div></td>
							</tr>
						</thead>
						<tbody>
							<?php $totalCost=0;$monthlyCost=0;$cost=0;?>
							<?php
							$count=0;
							if($collectionStatus==1)
								$qProj = $db->query('SELECT DISTINCT pi.proj_id FROM project_income pi, project p WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$date_request.'" AND left(date_start,4)="'.$proj_start.'" ORDER BY p.proj_name');
							else
								$qProj = $db->query('SELECT DISTINCT pi.proj_id FROM project_income pi, project p WHERE p.proj_id=pi.proj_id AND left(submit_date,7)="'.$date_request.'" AND pi_date IS NULL AND left(date_start,4)="'.$proj_start.'" ORDER BY p.proj_name');
							while($rProj = $db->fetch_array($qProj)):
								$count++;
								if($collectionStatus==1){
									$qNet = $db->query('SELECT sum(amount) as net FROM project_income WHERE proj_id="'.$rProj['proj_id'].'" AND left(pi_date,7)="'.$date_request.'"');
									$colStatus = "Collected";
								}
								else{
									$colStatus = "Collectible";
									$qNet = $db->query('SELECT sum(amount) as net FROM project_income WHERE proj_id="'.$rProj['proj_id'].'" AND left(submit_date,7)="'.$date_request.'" AND pi_date IS NULL');
								}
								$cost=$db->result($qNet);
								$totalCost += $cost;
							?>  
							<tr>
								<td height="30"><?php echo '<strong>'.$db->getValue('project','proj_name',array('proj_id'=>$rProj['proj_id'])).'</strong>';?></td>
								<td><div align="right"><a id="costdetail<?php echo $count++?>" class="thickbox" title="Amount Details" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-billing-yearly-detail-proj.php?prjID=<?php echo functions::encode($rProj['proj_id'])?>&dte=<?php echo functions::encode($date_request)?>&colstat=<?php echo $collectionStatus;?>','<?php echo $colStatus;?> Details','1')"><?php echo functions::formatMoney($cost);?></a></div></td>
							</tr>
							<?php
							endwhile;?>
							<tr>
								<td height="30"><div align="right"><strong>Total Cost</strong></div></td>
								<td><div align="right"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
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
<!-- end: JavaScript-->
</body>
</html>