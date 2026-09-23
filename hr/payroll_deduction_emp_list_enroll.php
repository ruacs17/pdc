<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	return $position;
}

$searchVal='';
$qDisp = $db->select('employee','*',array(),'ORDER BY lname');
#$qDisp = $db->query('SELECT * FROM employee WHERE emp_id IN (SELECT DISTINCT emp_id FROM payroll_deduction_reference) ORDER BY lname');
if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	$qDisp = $db->query('SELECT * FROM employee WHERE emp_no LIKE "%'.$db->clean($searchVal).'%" OR lname LIKE "%'.$db->clean($searchVal).'%" OR fname LIKE "%'.$db->clean($searchVal).'%" ORDER BY lname');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Payroll Deduction</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Employee List</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center">
					<table border="0">
						<tr>
							<td><input type="text" name="txSearch" id="txSearch" value="<?php echo $searchVal?>"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-primary btn-small" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-small" name="btnAll" id="btnAll" value="Clear"></td>
						</tr>
					</table>
				</div><br><br>
				<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC">
							<th width="10%" scope="col">ID No.</th>
							<th width="35%" scope="col">NAME</th>
							<th width="35%" scope="col">POSITION</th>
							<th width="10%" scope="col">STATUS</th>
							<th width="7%" scope="col"><div align="center">DETAILS</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					if($searchVal){
						$countResult=0;
						while($rDisp = $db->fetch_array($qDisp)):
							$countResult++;
					?>
						<tr>
							<td><?php echo $rDisp['emp_no']?></td>
							<td><?php echo $rDisp['lname'].' '.$rDisp['extname'].', '.$rDisp['fname'].' '.$rDisp['mname']?></td>
							<td><?php echo position($rDisp['emp_id'])?></td>
							<td><?php echo $rDisp['work_status'];?></td>
							<td><div align="center"><a id="edit<?php echo $rDisp['emp_id']?>" class="btn btn-mini btn-info thickbox" title="Employee Deduction Manage" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_reference_detail.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee Deduction Manage','1')"><i class="halflings-icon white zoom-in"></i></a></div></td>
						</tr>
					<?php
						endwhile;
						if($countResult==0){
					?>
						<tr>
							<td colspan="5"><div align="center">---No Match Found!---</div></td>
						</tr>
						<?php }
					}?>
					</tbody>
				</table>
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
</body>
</html>