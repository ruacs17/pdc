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
unset($_SESSION['arr_proj']);
if( isset($_REQUEST['txItmDel']) && !empty($_REQUEST['txItmDel']) ){
	$ed_idDel = functions::decode($_REQUEST['txItmDel']);
	$qFiles = $db->select('equip_docs','*',array('ed_id'=>$ed_idDel));
	while($rF = $db->fetch_array($qFiles)):
		if($filename=$rF['eds_name']){
			if( file_exists('../img_equip/'.$filename) ){
				unlink('../img_equip/'.$filename);
			}
		}
		$db->delete('equip_docs',array('eds_id'=>$rF['eds_id']));
	endwhile;
	$db->delete('equip_doc',array('ed_id'=>$ed_idDel,'equip_id'=>$equip_id));
	$_SESSION['notif_warning']='Document Removed!';
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
	die();
}

$mon='';$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['er_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$_SESSION['er_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['er_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['er_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$_SESSION['er_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
	$_SESSION['er_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
	$_SESSION['er_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
}

$txProj = ( isset($_SESSION['er_proj']) ) ? $_SESSION['er_proj'] : '';
$txbYear = ( isset($_SESSION['er_yr']) ) ? $_SESSION['er_yr'] : date('Y');
$txbMon = ( isset($_SESSION['er_mn']) ) ? $_SESSION['er_mn'] : date('m');
$txbDay = ( isset($_SESSION['er_day']) ) ? $_SESSION['er_day'] : date('d');

$txbYearTo = ( isset($_SESSION['er_yrTo']) ) ? $_SESSION['er_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['er_mnTo']) ) ? $_SESSION['er_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['er_dayTo']) ) ? $_SESSION['er_dayTo'] : date('d');

$where = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (er_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(er_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(er_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(er_date,6,2)>="'.$txbMon.'" AND SUBSTRING(er_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(er_date,4) >= "'.$txbYear.'" AND LEFT(er_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND er_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	$where .=' AND proj_id="'.$db->clean($txProj).'"';
}

$qShow = $db->select('equip_doc','*',array('equip_id'=>$equip_id),' ORDER BY ed_name DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Document</title>
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
			<h2><i class="halflings-icon white th"></i><span class="break"></span>DOCUMENT</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="#" style="opacity:.9">Document</a></li>
				<li><a href="admin-equipment-view-mr.php?vdidVw=<?php echo functions::encode($equip_id);?>">MR</a></li>
				<li><a href="admin-equipment-view-fuel.php?vdidVw=<?php echo functions::encode($equip_id);?>">Fuel & Oil</a></li>
				<li><a href="admin-equipment-view-operation.php?vdidVw=<?php echo functions::encode($equip_id);?>">Operation</a></li>
				<li><a href="admin-equipment-view-usage.php?vdidVw=<?php echo functions::encode($equip_id);?>">Usage</a></li>
				<li><a href="admin-equipment-view-accessory.php?vdidVw=<?php echo functions::encode($equip_id);?>">Accessory</a></li>
				<li><a href="admin-equipment-view-repair.php?vdidVw=<?php echo functions::encode($equip_id);?>">Repair & Maintenance</a></li>
				<li><a href="admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip_id);?>">Detail</a></li>
			</ul>
		</div>
		<div align="right" style="display:none;"><a id="mrPrint" href="admin-equipment-view-repair-print.php?vdidVw=<?php echo functions::encode($equip_id);?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div><br>
		<table width="100%" border="0">
			<tr>
				<td width="50%"><div align="left">&nbsp;Property: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
				<td><div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'admin-equipment-view-docu-manage.php?vdidVw=<?php echo functions::encode($equip_id);?>','Add Property Document')">Add Document</a>&nbsp;&nbsp;&nbsp;</div></td>
			</tr>
		</table><br>
		<form method="post">
			<div class="table-wrapper">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list2'])){echo 'table-bordered';} ?> table-hover table-striped" width="70%" style="font-size:12px;" border="0" cellspacing="0" cellpadding="0">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="30%" scope="col"><div align="center">Document Name</div></th>
							<th width="20%" scope="col"><div align="center">Remarks</div></th>
							<th width="20%" scope="col"><div align="center">Document Uploaded</div></th>
							<th width="10%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php
						while($rShow = $db->fetch_array($qShow)):
							$rID = $rShow['ed_id'];
						?>
						<tr id="rw<?php echo $rID;?>">
							<td><?php echo htmlspecialchars($rShow['ed_name'])?></td>
							<td><?php echo htmlspecialchars($rShow['ed_remarks'])?></td>
							<td>
								<?php
									$imgs = $db->getValue('equip_docs','count(*)',array('ed_id'=>$rID));
									$file = 'blank-pic.png';
								?>
								<div align="center">
									<a id="vw<?php echo $rID;?>" style="cursor: pointer;" class="thickbox btn btn-small btn-info" onclick="showThis(this.id,'admin-equipment-view-document.php?diid=<?php echo functions::encode($rShow['ed_id'])?>','View Document','1')"><?php echo $imgs; ?></a>
								</div>
							</td>
							<td>
								<div align="center">
									<a id="edit<?php echo $rID;?>" title="Update this History" data-rel="tooltip" href="#" class="btn btn-mini btn-warning thickbox" onclick="showThis(this.id,'admin-equipment-view-docu-manage.php?vdidVw=<?php echo functions::encode($equip_id);?>&txItmEdt=<?php echo functions::encode($rID);?>','Update Document')"><i class="halflings-icon white pencil"></i></a>
									<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?vdidVw=<?php echo functions::encode($equip_id);?>&txItmDel=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
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
<script>
function delt(){
	if(confirm('Do you want to remove this document?'))
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
<?php if(isset($_SESSION['notif_id_list2'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list2'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list2'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list2'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list2'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list2']);} ?>
<!-- end: JavaScript-->
</body>
</html>