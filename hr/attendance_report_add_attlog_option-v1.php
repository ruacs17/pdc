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
$filename='';$uploaded_file='';$filetype='';

$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$proj_id = $rEatID['proj_id'];
$date_start = $rEatID['date_start'];
$date_end = $rEatID['date_end'];
$worker_type = $rEatID['payroll_type'];
$note = $rEatID['note'];


$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));
$frmSummary = (isset($_REQUEST['frm']) && !empty($_REQUEST['frm']) ) ? $_REQUEST['frm'] : 0;
$personnel = $db->getValue('emp_attendance_personnel','count(eat_id)',array('eat_id'=>$eatid));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Upload Option</title>
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
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>ATTENDANCE UPLOAD</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<?php if($attendance_detail){?><li><a href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>">Summary</a></li><?php }?>
				<li class="active"><a href="attendance_report_add_attlog.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Upload Att. Log</a></li>
				<li><a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>">Personnel</a></li>
				<li><a href="attendance_report_add_charge.php?eatid=<?php echo functions::encode($eatid)?>">Charge To</a></li>
			</ul>
		</div>
		<div class="box-content">
		<?php if($frmSummary){?>
		<div align="right">
			<a id="icnReupload" href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info" title="Back To Attendance Summary">Back To Attendance Summary</a>&nbsp;
		</div>
		<?php }?>
				<table border="0" width="99%">
					<tr>
						<td align="left" width="12%">Project / Department </td>
						<td valign="middle" height="25px"><div align="left" style="font-weight:bold;"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></div></td>
					</tr>
					<tr>
						<td align="left">Period Cover:</td>
						<td valign="middle" height="25px"><div align="left" style="font-weight:bold;"><?php echo functions::datearr($date_start).' - '.functions::datearr($date_end); echo '&nbsp;&nbsp;&nbsp;&nbsp;('.functions::date_diff($date_start,$date_end,$includeDay1=1).') days'; ?></div></td>
					</tr>
					<tr>
						<td height="25">Worker Type: </td>
						<td><div align="left" style="font-weight:bold;"><?php if($worker_type=="admin"){echo 'Office Personnel';}elseif($worker_type=="labor"){echo 'Labor Group';}else{echo "Undefined";} ?></div></td>
					</tr>
					<?php if($note){?>
					<tr>
						<td height="25">Note: </td>
						<td><div align="left" style="font-weight:bold;"><?php echo $note; ?></div></td>
					</tr>
					<?php }?>
				</table><br>
			<form method="post" enctype="multipart/form-data">
				<div align="center">
					<?php if($personnel){?>
					<div style="font-size:17px;"><strong>Please choose the file attendance version</strong></div>
					<table border="0" cellpadding="40">
						<tr>
							<td>Version 1</td>
							<td><a href="attendance_report_add_attlog_v1.php?eatid=<?php echo functions::encode($eatid)?>"><img src="../img/att_v1.jpg" height="700" width="700"></a></td>
						</tr>
						<tr>
							<td>Version 2</td>
							<td><a href="attendance_report_add_attlog_v2.php?eatid=<?php echo functions::encode($eatid)?>"><img src="../img/att_v2.jpg" height="700" width="700"></a></td>
						</tr>
						<tr>
							<td>Version 3</td>
							<td><a href="attendance_report_add_attlog_v3.php?eatid=<?php echo functions::encode($eatid)?>"><img src="../img/att_v3.jpg" height="200" width="500"></a></td>
						</tr>
						<tr>
							<td>Version 4</td>
							<td><a href="attendance_report_add_attlog_v4.php?eatid=<?php echo functions::encode($eatid)?>"><img src="../img/att_v4.JPG" height="200" width="600"></a></td>
						</tr>
						<tr>
							<td>Version 5</td>
							<td><a href="attendance_report_add_attlog_v5.php?eatid=<?php echo functions::encode($eatid)?>"><img src="../img/att_v5.jpg" height="200" width="600"></a></td>
						</tr>
					</table>
					<?php }
					else{
					?>
						<div>No Specified Personnel, Please choose <a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>">personnel</a>.
					<?php }?>
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
<script src="../js/customx.js"></script>
<script src="../js/wxh.js"></script>
<script type="text/javascript">
$(window).ready(function(){
	$("#spinner").fadeOut("slow");
	$("#upload").click(function(){
		if($("#fileatt").val()){
			if(confirm('Do you want to upload the attendance log?')){
				$("#spinner").fadeIn();
				return true;
			}else{
				return false;
			}
			//$("#spinner").fadeIn();
		}
	});
});
</script>
<!-- end: JavaScript-->
</body>
</html>