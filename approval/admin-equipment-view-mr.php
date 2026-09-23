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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$txItmEdt='';

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['mr_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$_SESSION['mr_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['mr_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['mr_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$_SESSION['mr_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
	$_SESSION['mr_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
	$_SESSION['mr_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
}
$txProj = ( isset($_SESSION['mr_proj']) ) ? $_SESSION['mr_proj'] : '';
$txbYear = ( isset($_SESSION['mr_yr']) ) ? $_SESSION['mr_yr'] : date('Y');
$txbMon = ( isset($_SESSION['mr_mn']) ) ? $_SESSION['mr_mn'] : date('m');
$txbDay = ( isset($_SESSION['mr_day']) ) ? $_SESSION['mr_day'] : date('d');

$txbYearTo = ( isset($_SESSION['mr_yrTo']) ) ? $_SESSION['mr_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['mr_mnTo']) ) ? $_SESSION['mr_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['mr_dayTo']) ) ? $_SESSION['mr_dayTo'] : date('d');

$where = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (mr_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(mr_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(mr_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(mr_date,6,2)>="'.$txbMon.'" AND SUBSTRING(mr_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(mr_date,4) >= "'.$txbYear.'" AND LEFT(mr_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND mr_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	$where .=' AND proj_id="'.$db->clean($txProj).'"';
}
$qShow = $db->query('SELECT * FROM mr, mr_item mi WHERE mr.mr_id=mi.mr_id AND equip_id="'.$db->clean($equip_id).'" '.$where.' ORDER BY mr.mr_date DESC');

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM mr m, mr_item mri WHERE m.mr_id=mri.mr_id AND equip_id="'.$db->clean($equip_id).'")');
while($rProj = $db->fetch_array($qProj)):
	if($rProj['proj_id'])
	$arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;
ksort($arrChargeList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property MR</title>
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
	.tdSpace{padding: 4px 0px 4px 0px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white th"></i><span class="break"></span>PROPERTY MR</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-equipment-view-docu.php?vdidVw=<?php echo functions::encode($equip_id);?>">Document</a></li>
				<li class="active"><a href="#" style="opacity:.9">MR</a></li>
				<li><a href="admin-equipment-view-fuel.php?vdidVw=<?php echo functions::encode($equip_id);?>">Fuel & Oil</a></li>
				<li><a href="admin-equipment-view-operation.php?vdidVw=<?php echo functions::encode($equip_id);?>">Operation</a></li>
				<li><a href="admin-equipment-view-usage.php?vdidVw=<?php echo functions::encode($equip_id);?>">Usage</a></li>
				<li><a href="admin-equipment-view-accessory.php?vdidVw=<?php echo functions::encode($equip_id);?>">Accessory</a></li>
				<li><a href="admin-equipment-view-repair.php?vdidVw=<?php echo functions::encode($equip_id);?>">Repair & Maintenance</a></li>
				<li><a href="admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip_id);?>">Detail</a></li>
			</ul>
		</div>
		<div align="right"><a id="mrPrint" href="admin-equipment-view-mr-print.php?vdidVw=<?php echo functions::encode($equip_id);?>" class="btn btn-info btn-small"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div><br>
		<table width="100%" border="0">
			<tr>
				<td width="50%"><div align="left">&nbsp;Property: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
			</tr>
		</table><br>
		<form method="post">
			<table border="0">
				<tr>
					<td width="20%">
						<div align="center"> 
							<div align="center"><strong>FROM</strong></div>
							<select name="bdYear" id="bdYear" style="width:80px;">
								<option value="">All Year</option>
								<?php
								$qYr = $db->select('mr','DISTINCT LEFT(mr_date,4) as yr',array(),'ORDER BY mr_date DESC');
								while($rYr = $db->fetch_array($qYr)):
								?>
								<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
								<?php endwhile;?>
							</select>
							<select name="bdMon" id="bdMon" style="width:95px;">
								<option value="">All Month</option>
								<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
								<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
								<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
								<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
								<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
								<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
								<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
								<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
								<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
								<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
								<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
								<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
							</select>
							<select name="bdDay" id="bdDay" style="width:65px;">
								<option value="">Day</option>
								<?php for($i=1;$i<=31;$i++):?>
								<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
								<?php endfor;?>
							</select>
						</div>
						<div align="center">
							<div align="center"><strong>TO</strong></div>
							<select name="bdYearTo" id="bdYearTo" style="width:80px;">
								<option value="">All Year</option>
								<?php
								$qYr = $db->select('mr','DISTINCT LEFT(mr_date,4) as yr',array(),'ORDER BY mr_date DESC');
								while($rYr = $db->fetch_array($qYr)):
								?>
								<option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
								<?php endwhile;?>
							</select>
							<select name="bdMonTo" id="bdMonTo" style="width:95px;">
								<option value="">All Month</option>
								<option value="01" <?php if($txbMonTo=='01')echo 'selected="selected"';?>>Jan</option>
								<option value="02" <?php if($txbMonTo=='02')echo 'selected="selected"';?>>Feb</option>
								<option value="03" <?php if($txbMonTo=='03')echo 'selected="selected"';?>>Mar</option>
								<option value="04" <?php if($txbMonTo=='04')echo 'selected="selected"';?>>Apr</option>
								<option value="05" <?php if($txbMonTo=='05')echo 'selected="selected"';?>>May</option>
								<option value="06" <?php if($txbMonTo=='06')echo 'selected="selected"';?>>Jun</option>
								<option value="07" <?php if($txbMonTo=='07')echo 'selected="selected"';?>>Jul</option>
								<option value="08" <?php if($txbMonTo=='08')echo 'selected="selected"';?>>Aug</option>
								<option value="09" <?php if($txbMonTo=='09')echo 'selected="selected"';?>>Sep</option>
								<option value="10" <?php if($txbMonTo=='10')echo 'selected="selected"';?>>Oct</option>
								<option value="11" <?php if($txbMonTo=='11')echo 'selected="selected"';?>>Nov</option>
								<option value="12" <?php if($txbMonTo=='12')echo 'selected="selected"';?>>Dec</option>
							</select>
							<select name="bdDayTo" id="bdDayTo" style="width:65px;">
								<option value="">Day</option>
								<?php for($i=1;$i<=31;$i++):?>
								<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
								<?php endfor;?>
							</select>
						</div>
					</td>
					<td width="35%">
						<div align="left">
							<select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
								<option value="">All Project / Payee</option>
								<?php foreach($arrChargeList as $name => $id): ?>
								<option value="<?php echo $id?>" <?php if($txProj===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
								<?php endforeach;?>
							</select>
						</div>
					</td>
					<td width="8%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary"></div></td>
				</tr>
				<tr><td colspan="4"><hr width="100%"></td></tr>
			</table>
			<div class="table-wrapper">
				<table class="table table-hover table-bordered table-striped" width="70%" style="font-size:12px;" border="0" cellspacing="0" cellpadding="0">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="7%" scope="col"><div align="center">MR No.</div></th>
							<th width="17%" scope="col"><div align="center">User</div></th>
							<th width="23%" scope="col"><div align="center">Project</div></th>
							<th width="15%" scope="col"><div align="center">Location</div></th>
							<th width="7%" scope="col"><div align="center">Release Date</div></th>
							<th width="7%" scope="col"><div align="center">Return Date</div></th>
							<th width="10%" scope="col"><div align="center">Quantity</div></th>
							<th width="15%" scope="col"><div align="center">Remark</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						while($rShow = $db->fetch_array($qShow)):
						?>
						<tr>
							<td><div align="center"><?php echo $rShow['mr_no']?></div></td>
							<td><div align="center"><?php echo $db->getValue('employee','concat(lname,", ",fname," ",mname)',array('emp_id'=>$rShow['mr_emp']))?></div></td>
							<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rShow['proj_id']))?></td>
							<td><div align="center"><?php echo $rShow['assigned_location']?></div></td>
							<td><div align="center"><?php echo functions::datearr($rShow['released_date'])?></div></td>
							<td><div align="center"><?php echo functions::datearr($rShow['return_date'])?></div></td>
							<td><div align="center"><?php echo $rShow['qty'];?></div></td>
							<td><div align="center"><?php echo $rShow['remark'];?></div></td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table>
			</div>
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