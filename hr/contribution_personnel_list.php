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
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$empid=( isset($_REQUEST['emp']) && !empty($_REQUEST['emp']) ) ? functions::decode($_REQUEST['emp']) : '';
$rowdisplay=20;
$arrVal=array();
$searchVal=$empid;
if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	//echo $chksss = ( isset($_POST['chksss']) ) ? 1 : '';
	$_SESSION['chksss'] = ( isset($_POST['chksss']) ) ? 1 : '';
	$_SESSION['chkpagibig'] = ( isset($_POST['chkpagibig']) ) ? 1 : '';
	$_SESSION['chkph'] = ( isset($_POST['chkph']) ) ? 1 : '';
	$_SESSION['chktin'] = ( isset($_POST['chktin']) ) ? 1 : '';
	functions::sendTo(functions::pageName().'?emp='.functions::encode($searchVal));
	die();
}
$qChk='';
$chksss = isset($_SESSION['chksss']) ? $_SESSION['chksss'] : 1;
if($chksss)
	$qChk = ' sss <> ""';
$chkpagibig = isset($_SESSION['chkpagibig']) ? $_SESSION['chkpagibig'] : 1;
if($chkpagibig)
	$qChk .= ($qChk) ? ' AND pagibig <> "" ' : ' pagibig <> ""';
$chkph = isset($_SESSION['chkph']) ? $_SESSION['chkph'] : 1;
if($chkph)
	$qChk .= ($qChk) ? ' AND philhealth <> ""' : ' philhealth <> ""';
$chktin = isset($_SESSION['chktin']) ? $_SESSION['chktin'] : 1;
if($chktin)
	$qChk .= ($qChk) ? ' AND tin <> ""' : ' tin <> ""';
// if($searchVal)
// 	$qChk .= ($qChk) ? ' AND emp_id='.$db->clean($searchVal) : ' emp_id='.$db->clean($searchVal);
$where = ($qChk) ? ' WHERE ' : '';
$qDisp = $db->query('SELECT * FROM employee '.$where.$qChk.' ORDER BY lname,fname,mname');
#echo $db->last_query;
if($searchVal)
	$qDisp = $db->select('employee','*',array('emp_id'=>$searchVal));
else
	$qDisp = $db->query('SELECT * FROM employee '.$where.$qChk.' ORDER BY lname,fname,mname');
#echo $db->last_query;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Contribution Personnel List</title>
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
	<style type="text/css">
	.padright{padding-right:10px;}
	.txbox{width:90px;text-align:center;font-size:12px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	.table-wrapper thead tr:nth-child(2) th { background: #DDD;position: sticky; top: 65px;}
	</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="contribution_ph.php">Philhealth</a></li>
				<li><a href="contribution_pagibig.php">HDMF</a></li>
				<li><a href="contribution_sss.php">SSS</a></li>
				<li class="active"><a href="contribution_personnel_list.php" style="opacity:.9">PERSONNEL</a></li>
			</ul>
		</div>
		<div class="box-content">
			<div align="right" style="display:none;"><a id="adc" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'contribution_pagibig_manage.php?','Add Contribution Schedule')">Add Contribution Schedule</a></div>
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>EMPLOYEE LIST</h2></div>
				<form method="post">
					<div class="control-group">
						<table border="0" width="90%">
							<tr>
								<td>
									<label class="checkbox inline"><input type="checkbox" name="chktin" id="chktin" value="1" <?php echo ($chktin==1)?'checked':'';?>>&nbsp;TIN #</label>
									<label class="checkbox inline"><input type="checkbox" name="chksss" id="chksss" value="1" <?php echo ($chksss==1)?'checked':'';?>>&nbsp;SSS</label>
									<label class="checkbox inline"><input type="checkbox" name="chkpagibig" id="chkpagibig" value="1" <?php echo ($chkpagibig==1)?'checked':'';?>>&nbsp;PAGIBIG</label>
									<label class="checkbox inline"><input type="checkbox" name="chkph" id="chkph" value="1" <?php echo ($chkph==1)?'checked':'';?>>&nbsp;PHILHEALTH</label>
								</td>
								<td style="padding-top: 7px;">
									<div class="inline">
									<select name="txSearch" id="txSearch" data-rel="chosen" style="width:480px;">
										<option value="">--All Employee--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($searchVal==$rEU['emp_id'])echo 'selected="selected"'; ?>><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
									</div>
								</td>
								<td>&nbsp;&nbsp;<input type="submit" class="btn btn-primary btn-small" name="btnSearch" id="btnSearch" value="Search"></td>
								<td>&nbsp;&nbsp; <a href="?" class="btn btn-info btn-small">&nbsp;&nbsp;Clear&nbsp;&nbsp;</a></td>
							</tr>
						</table>
					</div>
				</form>
				<div align="center">
					<div style="width:90%;">
						<div class="table-wrapper">
							<table class="table table-bordered table-hover" style="font-size: 12px;">
								<thead>
									<tr style="background-color:#CCC;">
										<th scope="col" width="10%"><div align="left">ID</div></th>
										<th scope="col"><div align="left">NAME</div></th>
										<th scope="col" style="<?php echo ($chktin==1)?'background-color:#CCC':'';?>"><div align="left">TIN</div></th>
										<th scope="col" style="<?php echo ($chksss==1)?'background-color:#CCC':'';?>"><div align="left">SSS</div></th>
										<th scope="col" style="<?php echo ($chkpagibig==1)?'background-color:#CCC':'';?>"><div align="left">PAGIBIG</div></th>
										<th scope="col" style="<?php echo ($chkph==1)?'background-color:#CCC':'';?>"><div align="left">PHILHEALTH</div></th>
										<th scope="col">&nbsp;</th>
									</tr>
								</thead>
								<tbody>
								<?php
								while($r = $db->fetch_array($qDisp)):
								$id = $r['emp_id'];
								?>
									<tr>
										<td><div align="left"><?php echo $r['emp_no']?></div></td>
										<td><div align="left"><?php echo $r['lname'].' '.$r['extname'].', '.$r['fname'].' '.$r['mname']?></div></td>
										<td style="<?php echo ($chktin==1)?'background-color:#CCB':'';?>"><div align="left"><?php echo $r['tin']?></div></td>
										<td style="<?php echo ($chksss==1)?'background-color:#CCB':'';?>"><div align="left"><?php echo $r['sss']?></div></td>
										<td style="<?php echo ($chkpagibig==1)?'background-color:#CCB':'';?>"><div align="left"><?php echo $r['pagibig']?></div></td>
										<td style="<?php echo ($chkph==1)?'background-color:#CCB':'';?>"><div align="left"><?php echo $r['philhealth']?></div></td>
										<td>
											<div align="center">
												<a id="adc<?php echo $id?>" href="#" class="thickbox btn btn-warning btn-mini" title="Manage Personnel" data-rel="tooltip" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($id)?>','Manage')"><i class="halflings-icon white pencil"></i></a>
											</div>
										</td>
									</tr>
								<?php endwhile;?>
								</tbody>
							</table>
						</div>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
function delt(){
	if(confirm('Do you want to remove this Reference?'))
		return true;
	else
		return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>