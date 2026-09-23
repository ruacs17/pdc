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
$po_no='';
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
if($po_id)
	$po_no = $db->getValue('po','po_no',array('po_id'=>$po_id));
else if( isset($_POST['txPoNo']) )
	$po_no = $_POST['txPoNo'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Purchase Order History Logs</title>
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
	<style type="text/css">
		.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Activity Logs History</h2>
		</div>
		<div class="box-content">
			<?php if($po_id){ ?>
			<ul class="nav tab-menu nav-tabs">
				<li><a href="po_view_document.php?po_id=<?php echo functions::encode($po_id)?>" style="opacity:.9">Document</a></li>
				<li><a href="po_view.php?po_id=<?php echo functions::encode($po_id)?>" style="opacity:.9">P.O. Detail</a></li>
				<li class="active"><a href="po_history.php?po_id=<?php echo functions::encode($po_id)?>">Activity Log</a></li>
			</ul>
			<?php } ?>
			<form method="post">
				<?php if(empty($po_id)){ ?>
				<div class="control-group">
					<label class="control-label" for="txPoNo">P.O. Number</label>
					<div class="controls">
						<div class="input-append">
							<input name="txPoNo" id="txPoNo" value="<?php echo $po_no; ?>" type="text"><input type="submit" class="btn btn-info btn-small" name="btnSubmit" value="Search">
						</div>
					</div>
				</div>
				<?php } ?>
				<div class="table-wrapper">
					<table width="100%" class="table table-hover table-striped table-bordered" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th width="15%">Date</th>
								<th width="7%">Activity</th>
								<th>Details</th>
								<th width="17%">Account</th>
							</tr>
						</thead>
						<tbody>
					<?php
					#$po_no = $db->getValue('po','po_no',array('po_id'=>$po_id));
					if($po_no){
					$qH = $db->select('po_history ph, users u','*',array('po_no'=>$po_no),'AND ph.user_id=u.user_id ORDER BY act_date,act_time');
					while($rH = $db->fetch_array($qH)):
						$dte = strtotime($rH['act_date'].' '.$rH['act_time']);
					?>
							<tr>
								<td><?php echo date('F j, Y, h:i:s a',$dte); ?></td>
								<td><?php echo $rH['act_mode'] ?></td>
								<td><?php echo $rH['content'] ?></td>
								<td><?php echo $rH['lname'].', '.$rH['fname']; ?></td>
							</tr>
					<?php
					endwhile;
					}
					?>
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
</body>
</html>