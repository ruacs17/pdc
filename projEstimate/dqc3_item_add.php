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
$count=0;
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$itemBaseSel = ($itemID) ? $db->getValue('dqc','itemBase',array('proj_id'=>$p_id,'id'=>$itemID)) : 0;
$_SESSION['notif_id_list']=$itemID;

function disp($proj_id,$parentItem,$level,$currentItemID){
	global $db;
	$string='';

	$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$proj_id,'itemParent'=>$parentItem)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
	$q = $db->select('dqc','*',array('proj_id'=>$proj_id,'itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
	while($r = $db->fetch_array($q)):
		$s = '';
		for($i=1; $i<=$level; $i++):
			$s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		endfor;
		$s .= $r['itemNo'].'&nbsp;&nbsp;'.$r['itemDesc'];
		$selected = ($currentItemID==$r['id']) ? 'selected="selected"' : "";
		$string ='
		<option value="'.$r['id'].'" '.$selected.'>'.$s.'</option>
		';
		echo $string;
		disp($proj_id,$r['id'],$level + 1,$currentItemID);
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Item Add</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link href="../css/select2.min.css" rel="stylesheet">
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
<?php
if( isset($_POST['btnAdd']) ){
	$itemDesc = ( isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
	$itemID = $parentItemID = ( isset($_POST['selParentItem']) && !empty($_POST['selParentItem']) ) ? $_POST['selParentItem'] : '0';
	$parentItemNo = $db->getValue('dqc','itemNo',array('proj_id'=>$p_id,'id'=>$parentItemID));
	if($itemDesc){
		if($parentItemID){
			$itemNo = $db->getValue('dqc','count(itemNo)',array('proj_id'=>$p_id,'itemParent'=>$parentItemID));
			$itemBase = $db->getValue('dqc','itemBase',array('proj_id'=>$p_id,'id'=>$parentItemID));
			$parentItemNo = $db->getValue('dqc','itemNo',array('proj_id'=>$p_id,'id'=>$parentItemID));
			$finalItemNo = $parentItemNo.'.'.($db->getValue('dqc','COUNT(*)',array('proj_id'=>$p_id,'itemParent'=>$parentItemID)) + 1);
			$db->insert('dqc',array('proj_id'=>$p_id,'dqc_type'=>'3','itemBase'=>$itemBase,'itemParent'=>$parentItemID,'itemNo'=>$finalItemNo,'itemDesc'=>$itemDesc));
			$_SESSION['notif_success']='New Item Added!';
		}else{
			$itemNo = $db->getValue('dqc','count(itemNo)',array('proj_id'=>$p_id,'itemParent'=>0),'ORDER BY id ASC');
			$db->insert('dqc',array('proj_id'=>$p_id,'dqc_type'=>'3','itemNo'=>($itemNo + 1),'itemDesc'=>$itemDesc));
			$itemBase = $db->getValue('dqc','id',array('proj_id'=>$p_id,'itemNo'=>($itemNo + 1),'itemDesc'=>$itemDesc));
			$db->update('dqc',array('itemBase'=>$itemBase),array('proj_id'=>$p_id,'id'=>$itemBase,'itemNo'=>($itemNo + 1),'itemDesc'=>$itemDesc));
			$_SESSION['notif_success']='New Item Added!';
		}
		functions::sendTo(functions::pageName().'?pid='.functions::encode($p_id).'&itemID='.functions::encode($parentItemID));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body onLoad="document.getElementById('txItemDesc').focus()">
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="45%" scope="col"><div align="left">Parent Item</div></th>
							<th width="45%" scope="col"><div align="left">Description</div></th>
							<th width="8%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<select name="selParentItem" id="selParentItem" style="width:100%;">
									<option value="">None</option>
									<?php
									$orderBy = ( $db->getValue('dqc','count(*)',array('proj_id'=>$p_id,'dqc_type'=>'3','itemParent'=>0)) > 9) ? 'ORDER BY itemNo + 0 ASC' : 'ORDER BY itemNo';
									if($itemBaseSel)
									$q = $db->select('dqc','*',array('proj_id'=>$p_id,'id'=>$itemBaseSel,'dqc_type'=>'3'),'ORDER BY cast(itemNo as unsigned)');
									else
									$q = $db->select('dqc','*',array('proj_id'=>$p_id,'dqc_type'=>'3','itemParent'=>0),'ORDER BY cast(itemNo as unsigned)');
									while($r = $db->fetch_array($q)):?>
									<option value="<?php echo $r['id']?>" <?php if($itemID==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].' '.$r['itemDesc']?></option>
									<?php
									disp($p_id,$r['id'],1,$itemID);
									endwhile;
									?>
								</select>
							</td>
							<td><input type="text" name="txItemDesc" id="txItemDesc" value="" style="width:97%;" required></td>
							<td><div align="center"><input type="submit" name="btnAdd" class="btn btn-small btn-info" id="btnAdd" value="Add Item"></div></td>
						</tr>
					</tbody>
				</table><p>&nbsp;</p>
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
<script src="../js/select2.js"></script>
<script type="text/javascript">$("#selParentItem").select2();</script>
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