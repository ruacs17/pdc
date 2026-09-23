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
$equip_id = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;

if( isset($_REQUEST['idDel']) && $equip_id ){
	$ec_delete = functions::decode($_REQUEST['idDel']);
	$last_ec_id = $db->getValue('equip_condition','ec_id',array('equip_id'=>$equip_id),'ORDER BY con_date DESC, ec_id DESC LIMIT 1');
	$db->delete('equip_condition',array('ec_id'=>$ec_delete,'equip_id'=>$equip_id));
	if($ec_delete == $last_ec_id){// if it delete the latest condition
		$q = $db->select('equip_condition','*',array('equip_id'=>$equip_id),'ORDER BY con_date DESC, ec_id DESC LIMIT 1');
		$r = $db->fetch_array($q);
		//get the latest condition after deletion and use it as current equipment condition
		$db->update('equipment',array('status'=>$r['con_stat'],'out_date'=>$r['con_date']),array('equip_id'=>$equip_id));
	}
	$_SESSION['notif_warning']='Status Removed!';
	functions::sendTo(functions::pageName().'?eid='.functions::encode($equip_id));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Condition</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
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
	<style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>PROPERTY STATUS</h2>
		</div>
		<div class="box-content">
			<div align="right"><a id="statPrint" class="btn btn-info btn-small" title="Print Property Status History" data-rel="tooltip" href="admin-equipment-condition-print.php?eid=<?php echo functions::encode($equip_id)?>"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div>
			<div align="center">
				<form method="post">
					<div style="width:550px">
						<table width="25%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-bordered">
							<tr>
								<th width="15%" align="center" scope="row">Status</th>
								<th width="10%" align="center">Date</th>
								<th width="5%" align="center">&nbsp;</th>
							</tr>
							<?php
							$q = $db->select('equip_condition','*',array('equip_id'=>$equip_id),'ORDER BY con_date');
							while($r=$db->fetch_array($q)):
							?>
							<tr>
								<td class="tdSpace"><?php echo $r['con_stat']?></td>
								<td class="tdSpace"><?php echo functions::datearr($r['con_date'])?></td>
								<td><div align="center"><a class="btn btn-mini btn-warning" onClick="return askDel()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?idDel=<?php echo functions::encode($r['ec_id']);?>&eid=<?php echo functions::encode($r['equip_id']);?>"><i class="halflings-icon white trash"></i></a></div></td>
							</tr>
							<?php endwhile;?>
						</table>
					</div>
				</form>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#txDateOut').datepicker({
		numberOfMonths:1,
		dateFormat:'yy/mm/dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+2 ?>',
	});
});
</script>
<script>
function askDel(){
	if(confirm('Do you want to delete this Detail?'))
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
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 5000,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>