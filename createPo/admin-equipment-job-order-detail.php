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
$mon = (isset($_REQUEST['mon']) && !empty($_REQUEST['mon']) ) ? functions::decode($_REQUEST['mon']) : '';
$stat = (isset($_REQUEST['stat']) && !empty($_REQUEST['stat']) ) ? functions::decode($_REQUEST['stat']) : '';
$arr = array();

if($mon)
	$arr = array('LEFT(date_report,7)'=>$mon);
if($stat)
	$arr = array_merge($arr,array('jo.jo_status'=>$stat));	

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}
if(count($arr))
	$qList = $db->select('equip_job_order jo, equipment eqp','*,jo.remarks as rmks',$arr,'AND jo.equip_id=eqp.equip_id ORDER BY date_report DESC');
else
	$qList = $db->query('SELECT *,jo.remarks as rmks FROM equip_job_order jo, equipment eqp WHERE jo.equip_id=eqp.equip_id ORDER BY date_report DESC');
#echo $db->last_query;
$num_record = $db->getValue('equip_job_order jo','count(*)',$arr);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Job Order</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	<style>
	.tdSpace{padding: 12px 0px 4px 0px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Equipment Job Order List</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div style="padding-top:10">&nbsp;</div>
				<div class="table-wrapper">
					<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:11px;">
						<thead>
							<tr style="background-color:#CCC;">
							<th width="5%"><div align="center">Job Order No.</div></th>
							<th width="7%"><div align="center">Date Report</div></th>
							<th width="10%"><div align="center">Equipment Description</div></th>
							<th width="5%"><div align="center">Property Code No.</div></th>
							<th width="5%"><div align="center">Plate No./Serial No.</div></th>
							<th width="5%"><div align="center">Problem and Repair  Description</div></th>
							<th width="5%"><div align="center">Assigned Personnel / Mechanic</div></th>
							<th width="5%"><div align="center">Priority Schedule and Target</div></th>
							<th width="5%"><div align="center">Date Accomplished</div></th>
							<th width="5%"><div align="center">Status</div></th>
							<th width="6%"><div align="center">Q.C. Date</div></th>
							<th width="6%"><div align="center">Date Post and Release</div></th>
							<th width="5%"><div align="center">REMARKS</div></th>
							</tr>
						</thead>
						<tbody>
						<?php $countRes=0; while($rList = $db->fetch_array($qList)): $rID = $rList['jo_id']; $countRes++;?>
							<tr id="rw<?php echo $rID?>">
								<td><div align="left"><?php echo $rList['jo_no'];?></div></td>
								<td><div align="center"><?php echo functions::datearr($rList['date_report']);?></div></td>
								<td><div align="left"><?php echo $rList['name'];?></div></td>
								<td><div align="left"><?php echo $rList['inventory_id'];?></div></td>
								<td><div align="left"><?php echo $rList['plate_no']; echo ($rList['serial_no'] && $rList['plate_no']) ? ' / '.$rList['serial_no'] : $rList['serial_no']; ?></div></td>
								<td><div align="left"><?php echo $rList['jo_desc'];?></div></td>
								<td>
									<div align="left" style="font-size:10px;">
										<?php
											$qM = $db->select('equip_job_order_mechanic mec, employee emp','*',array('jo_id'=>$rID),'AND mec.emp_id=emp.emp_id');
											while($rM = $db->fetch_array($qM)):
												echo '<div>'.$rM['lname'].', '.$rM['fname'].'</div>';
											endwhile;
										?>
									</div>
								</td>
								<td><div align="center"><?php echo functions::datearr($rList['date_schedule']);?></div></td>
								<td><div align="center"><?php echo functions::datearr($rList['date_accomplished']);?></div></td>
								<td><div align="center"><?php echo $rList['jo_status'];?></div></td>
								<td><div align="center"><?php echo functions::datearr($rList['date_qc']);?></div></td>
								<td><div align="center"><?php echo functions::datearr($rList['date_release']);?></div></td>
								<td><div align="left"><?php echo $rList['rmks'];?></div></td>
							</tr>
						<?php endwhile;
						if($countRes==0){?>
							<tr>
								<td colspan="12"><div align="center">--No Record found!--</div></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					<div align="right">Number of Record: <?php echo $num_record; ?></div>
				</div>
			</form>
		</div>
	</div><!--/span-->
</div>
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
<script>
function delt(){
	if(confirm('Do you want to remove this Job Order?'))
		return true;
	else
		return false; 
}
</script>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>
<!-- end: JavaScript-->
</body>
</html>