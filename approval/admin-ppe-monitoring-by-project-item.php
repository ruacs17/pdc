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
$proj_id = (isset($_REQUEST['prj']) && !empty($_REQUEST['prj']) ) ? functions::decode($_REQUEST['prj']) : '';

$arr = array('p.proj_id'=>$proj_id);
$andwhere = (count($arr)) ? 'AND ' : 'WHERE ';
$q = $db->select('project prj, ppe p, ppe_items pi','item, sum(quantity) as itemCount',$arr,'AND p.proj_id=prj.proj_id AND p.ppe_id=pi.ppe_id GROUP BY item ORDER BY item');
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>IPPE Monitoring By Project</title>
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
			<br><div>Project: <strong><?php echo $proj_name; ?></strong></div><br><br>
			<table class="table table-bordered table-hover" style="font-size:12px">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="40%">Item</th>
						<th width="10%">Quantity Issued</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$count=0;$countItem=0;
				while($r = $db->fetch_array($q)):
				$countItem+=$r['itemCount'];
				?>
					<tr>
						<td><div align="left"><a id="detailItm<?php echo $count++?>" class="thickbox" style="cursor:pointer;" title="View IPPE Details" data-rel="tooltip" onclick="showThis(this.id,'admin-ppe-monitoring-by-project-item-personnel.php?prj=<?php echo functions::encode($proj_id);?>&itm=<?php echo functions::encode($r['item']); ?>','IPPE Item Monitoring','1')"><?php echo $r['item']?></a></div></td>
						<td><div align="left"><a id="detail<?php echo $count++?>" class="thickbox" style="cursor:pointer;" title="View IPPE Details" data-rel="tooltip" onclick="showThis(this.id,'admin-ppe-monitoring-by-project-item-personnel.php?prj=<?php echo functions::encode($proj_id);?>&itm=<?php echo functions::encode($r['item']); ?>','IPPE Item Monitoring','1')"><?php echo $r['itemCount']?></a></div></td>
					</tr>
				<?php endwhile; //rPO?>
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
<script type="text/javascript">
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?pid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
</script>
<!-- end: JavaScript-->
</body>
</html>