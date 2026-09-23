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
$itm = (isset($_REQUEST['itm']) && !empty($_REQUEST['itm']) ) ? functions::decode($_REQUEST['itm']) : 0;
$prj = (isset($_REQUEST['prj']) && !empty($_REQUEST['prj']) ) ? functions::decode($_REQUEST['prj']) : 0;

$txbMon = ( isset($_SESSION['ppeMon']) ) ? $_SESSION['ppeMon'] : date('m');
$txbYear = ( isset($_SESSION['ppeYear']) ) ? $_SESSION['ppeYear'] : date('Y');
$arr = array('item'=>$itm,'proj_id'=>$prj);
if($txbMon && $txbYear)
	$arr = array_merge($arr,array('LEFT(ppe_date,7)'=>$txbYear.'-'.$txbMon));
else if($txbMon)
	$arr = array_merge($arr,array('SUBSTRING(ppe_date,6,2)'=>$txbMon));
elseif($txbYear)
	$arr = array_merge($arr,array('LEFT(ppe_date,4)'=>$txbYear));

$q = $db->select('employee e, ppe p, ppe_items pi','p.emp_id as empid,lname, fname,sum(quantity) as issuance, unit',$arr,'AND p.ppe_id=pi.ppe_id AND e.emp_id=p.emp_id GROUP BY p.emp_id ORDER BY e.lname');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>IPPE Monitoring Per Personnel</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>IPPE Monitoring Report</h2>
		</div>
		<div class="box-content">
			<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left" style="display:none;">
				<tr>
					<td width="50%"><div align="right"><a id="whprint" class="btn btn-info" href="po_monitoring_print.php"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div><br><br></td>
				</tr>
			</table>
			<div align="left">Item: <strong><?php echo $itm;?></strong></div><br>
			<div align="left">Project / Department: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$prj));?></strong></div><br>
			<table class="table table-bordered table-hover" style="font-size:12px">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="40%">Personnel</th>
						<th width="10%">Issued</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$countItem=0;
					while($r = $db->fetch_array($q)):
					$countItem+=$r['issuance'];
					?>
					<tr>
						<td><?php echo $r['lname'].', '.$r['fname']?></td>
						<td><div><a id="detail<?php echo $count++?>" class="thickbox" style="cursor:pointer;" title="View IPPE item issuance date" data-rel="tooltip" onclick="showThis(this.id,'admin-ppe-monitoring-issuance-date.php?itm=<?php echo functions::encode($itm);?>&prj=<?php echo functions::encode($prj);?>&emp=<?php echo functions::encode($r['empid']);?>','IPPE Item Issuance Date','1')"><?php echo $r['issuance'].' <i>('.$r['unit'].')</i>'?></a></div></td>
					</tr>
					<?php
					endwhile; //rPO?>
					<tr>
						<td><div align="right"><strong>Total</strong></div></td>
						<td><strong><?php echo $countItem?></strong></td>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>