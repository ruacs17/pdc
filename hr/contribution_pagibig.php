<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$delid = (isset($_REQUEST['delid']) && !empty($_REQUEST['delid']) ) ? functions::decode($_REQUEST['delid']) : '';
if($delid){
	$db->delete('table_hdmf',array('thdmf_id'=>$delid));
	$_SESSION['notif_warning']='References Removed!';
	functions::sendTo(functions::pageName());
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>HDMF Contribution</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="contribution_ph.php">Philhealth</a></li>
				<li class="active"><a href="contribution_pagibig.php" style="opacity:.9">HDMF</a></li>
				<li><a href="contribution_sss.php">SSS</a></li>
				<li><a href="contribution_personnel_list.php">PERSONNEL</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="right"><a id="adc" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'contribution_pagibig_manage.php?','Add Contribution Schedule')">Add Contribution Schedule</a></div>
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>HDMF Contribution</h2></div>
				<div align="center">
					<div style="width:800px;">
						<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
							<thead>
								<tr style="background-color:#CCC;">
									<th scope="col"><div align="left">RANGE OF COMPENSATION </div></th>
									<th scope="col"><div align="right">EE SHARE</div></th>
									<th scope="col"><div align="right">ER SHARE</div></th>
									<th scope="col"><div align="right">TOTAL</div></th>
									<th scope="col">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$qDisp = $db->select('table_hdmf','*',array(),'ORDER BY sal_from');
							while($rDisp = $db->fetch_array($qDisp)):
							$id = $rDisp['thdmf_id'];
							?>
								<tr>
									<td><div align="left"><?php echo functions::formatMoney($rDisp['sal_from']).' - '.functions::formatMoney($rDisp['sal_to'])?></div></td>
									<td><div align="right"><?php echo ($rDisp['hdmf_type']=='percent') ? $rDisp['hdmf_ee_share'].'%' : functions::formatMoney($rDisp['hdmf_ee_share']);?></div></td>
									<td><div align="right"><?php echo ($rDisp['hdmf_type']=='percent') ? $rDisp['hdmf_er_share'].'%' : functions::formatMoney($rDisp['hdmf_er_share'])?></div></td>
									<td><div align="right"><?php echo ($rDisp['hdmf_type']=='percent') ? $rDisp['hdmf_total'].'%' : functions::formatMoney($rDisp['hdmf_total'])?></div></td>
									<td>
										<div align="center">
											<a id="adc<?php echo $id?>" href="#" class="thickbox btn btn-warning btn-mini" title="Manage Contribution Reference" data-rel="tooltip" onclick="showThis(this.id,'contribution_pagibig_manage.php?hmdf=<?php echo functions::encode($id)?>','Manage Contribution Schedule')"><i class="halflings-icon white pencil"></i></a>
											<a id="del<?php echo $id?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Travel Order" data-rel="tooltip" href="?delid=<?php echo functions::encode($id)?>"><i class="halflings-icon white trash"></i></a>
										</div>
									</td>
								</tr>
							<?php endwhile;?>
							</tbody>
						</table>
					</div>
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
<script>
function delt(){
	if(confirm('Do you want to remove this Reference?'))
		return true;
	else
		return false; 
}
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>