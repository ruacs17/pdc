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
#$fn = (isset($_REQUEST['fn']) && !empty($_REQUEST['fn']) ) ? functions::decode($_REQUEST['fn']).'.xlsx' : 0;
$imprt = (isset($_REQUEST['imprt']) && !empty($_REQUEST['imprt']) ) ? $_REQUEST['imprt'] : 0;
$eas_id = (isset($_REQUEST['easid']) && !empty($_REQUEST['easid']) ) ? functions::decode($_REQUEST['easid']) : 0;
$proj_id = (isset($_REQUEST['projid']) && !empty($_REQUEST['projid']) ) ? functions::decode($_REQUEST['projid']) : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));
$group_name = $db->getValue('emp_assignment','eas_name',array('eas_id'=>$eas_id));

if($imprt==="t" && $eas_id && $proj_id && $eatid){
	$qDisp = $db->select('emp_assign_detail ead, employee e','*',array('eas_id'=>$eas_id),'AND ead.emp_id=e.emp_id ORDER BY lname');
	while($rDisp = $db->fetch_array($qDisp)):
		$emp_id = $rDisp['emp_id'];
		if( $eatid && $emp_id ){
			$arrField = array('emp_id'=>$emp_id,'eat_id'=>$eatid);
			if( $db->getValue('emp_attendance_personnel','count(*)',$arrField)==0 ){
				$db->insert('emp_attendance_personnel',$arrField);
			}
		}
	endwhile;
	$_SESSION['notif_success']='Importing Personnel Done!';
	functions::sendTo('attendance_report_add_personnel.php?eatid='.functions::encode($eatid));
	die();
}
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
function work_status($emp_id){
	global $db;
	$qStat = $db->select('emp_work_status','*',array('emp_id'=>$emp_id),'ORDER BY ews_date DESC');
	$rStat = $db->fetch_array($qStat);
	$wrkStat = (isset($rStat['ews_stat'])) ? $rStat['ews_stat'] : '-----';
	$wrkDate = (isset($rStat['ews_date'])) ? ' ('.functions::datearr($rStat['ews_date']).')' : '';
	$projBased = (isset($rStat['project_based']) && $rStat['project_based']==1) ? ' <i>Project Based</i>' : '';
	return $wrkStat. $projBased . $wrkDate;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Group Select</title>
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
	.tdpadleft{padding-left: 5px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<div align="left">Project / Department: <strong><?php echo $proj_name;?></strong></div><br><br>
			<form class="form-horizontal" id="showform" name="showform" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>GROUP MEMBER</h2></div>
				<div align="center">
					<table border="0">
						<tr>
							<td>
								<div align="left" style="padding-top: 2px;">
									<select name="selGroup" id="selGroup" data-rel="chosen" style="width:480px;" onChange="itmSel(this.value)">
										<option value="">--Select Group--</option>
										<?php $qEU = $db->select('emp_assignment','*',array('proj_id'=>$proj_id),'ORDER BY worker_type');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo functions::encode($rEU['eas_id'])?>" <?php if($eas_id==$rEU['eas_id']){echo 'selected="selected"';} ?>><?php echo '('.$rEU['worker_type'].') '.$rEU['eas_name'];?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td><div align="left" style="padding-left: 12px;"><a id="bck" class="btn btn-small btn-info" title="Back To Personnel Detail" data-rel="tooltip" href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>">Back</a></div></td>
						</tr>
					</table><br><br>
				</div>
				<div align="center">
					<div class="table-wrapper">
						<table width="90%" align="center" class="tablea table-hover table-striped" border="1" style="font-size:12px;">
							<thead>
								<tr style="background-color:#CCC;">
									<th>&nbsp;</th>
									<th width="12%" height="30" class="tdpadleft"><div align="left">ID Number</div></th>
									<th class="tdpadleft"><div align="left">Name</div></th>
									<th class="tdpadleft"><div align="left">Position</div></th>
									<th class="tdpadleft" width="22%"><div align="left">Status</div></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$count=1;
								$qDisp = $db->select('emp_assign_detail ead, employee e','*',array('eas_id'=>$eas_id),'AND ead.emp_id=e.emp_id ORDER BY lname,fname');
								while($rDisp = $db->fetch_array($qDisp)):
								$rID = $rDisp['ead_id'];
								?>
								<tr>
									<td><div align="center"><?php echo $count++;?></div></td>
									<td height="30" class="tdpadleft"><?php echo $rDisp['emp_no']?></td>
									<td class="tdpadleft"><?php echo $rDisp['lname'].', '.$rDisp['fname']?></td>
									<td class="tdpadleft"><?php echo position($rDisp['emp_id'])?></td>
									<td class="tdpadleft"><?php echo work_status($rDisp['emp_id'])?></td>
								</tr>
								<?php endwhile;?>
							</tbody>
						</table>
					</div>
				<?php if($proj_id && $eas_id && $eatid){ ?>
				<br><a id="imprt" class="btn btn-small btn-success" title="Import" data-rel="tooltip" onClick="return imprtAsk()" href="?projid=<?php echo functions::encode($proj_id);?>&eatid=<?php echo functions::encode($eatid)?>&easid=<?php echo functions::encode($eas_id)?>&imprt=t">Import Member</a>
				<?php } ?>
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
<script>
function itmSel(PiEwgD){window.location="<?php echo functions::pageName()?>?projid=<?php echo functions::encode($proj_id)?>&eatid=<?php echo functions::encode($eatid)?>&easid="+PiEwgD}
function imprtAsk(){if(confirm("Do you want to import this members?"))return true; else return false;}
</script>
<!-- end: JavaScript-->
</body>
</html>