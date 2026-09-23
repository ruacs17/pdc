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

$type=(isset($_REQUEST['tpe']) && !empty($_REQUEST['tpe']) ) ? functions::decode($_REQUEST['tpe']) : 0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$itemBaseSel = ($itemID) ? $db->getValue('elm_rate','itemBase',array('id'=>$itemID)) : 0;
$_SESSION['notif_id_list']=$itemID;
if( isset($_POST['btnAdd']) ){
	$itemDesc = ( isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
	$itemUnit = 'day';
	$parentItemID = ( isset($_POST['selParentItem']) && !empty($_POST['selParentItem']) ) ? $_POST['selParentItem'] : '0';
	$parentItemNo = $db->getValue('elm_rate','itemNo',array('id'=>$parentItemID));
	if($parentItemID){
		$itemNo = $db->getValue('elm_rate','count(itemNo)',array('itemParent'=>$parentItemID));
		$itemBase = $db->getValue('elm_rate','itemBase',array('id'=>$parentItemID));
		$parentItemNo = $db->getValue('elm_rate','itemNo',array('id'=>$parentItemID));
		$finalItemNo = $parentItemNo.'.'.($db->getValue('elm_rate','COUNT(*)',array('itemParent'=>$parentItemID)) + 1);
		$db->insert('elm_rate',array('itemBase'=>$itemBase,'itemParent'=>$parentItemID,'itemNo'=>$finalItemNo,'itemDesc'=>$itemDesc,'itemUnit'=>$itemUnit));
		$_SESSION['notif_success']="New Item Added!";
	}else{
		$itemNo = $db->getValue('elm_rate','count(itemNo)',array('itemParent'=>0));
		$db->insert('elm_rate',array('itemNo'=>($itemNo + 1),'itemDesc'=>$itemDesc,'itemRate'=>$itemRate,'itemUnit'=>$itemUnit));
		$itemBase = $db->getValue('elm_rate','id',array('itemNo'=>($itemNo + 1),'itemDesc'=>$itemDesc));
		$db->update('elm_rate',array('itemBase'=>$itemBase),array('id'=>$itemBase,'itemNo'=>($itemNo + 1),'itemDesc'=>$itemDesc));
		$_SESSION['notif_success']="New Item Added!";
	}
	functions::sendTo(functions::pageName().'?itemID='.functions::encode($parentItemID));
	die();
}

function disp($parentItem,$level,$currentItemID){
	global $db;
	$string='';

	$q = $db->select('elm_rate','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
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
		disp($r['id'],$level + 1,$currentItemID);
	endwhile;
}
$namesItem='';
$namesItem .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>ELM LABOR Add</title>
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
</head>
<body onLoad="document.getElementById('txItemDesc').focus()">
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Labor Item</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
					<tr style="background-color:#CCC;">
						<th width="45%" scope="col"><div align="left">Parent Item</div></th>
						<th width="45%" scope="col"><div align="left">Description</div></th>
						<th width="10%" scope="col">&nbsp;</th>
					</tr>
					<tr>
						<td>
							<select name="selParentItem" id="selParentItem" style="width:99%;">
								<?php
								if($itemBaseSel)
								$q = $db->select('elm_rate','*',array('id'=>$itemBaseSel),'ORDER BY cast(itemNo as unsigned)');
								else
								$q = $db->select('elm_rate','*',array('itemParent'=>0),'ORDER BY cast(itemNo as unsigned)');
								while($r = $db->fetch_array($q)):?>
								<option value="<?php echo $r['id']?>" <?php if($itemID==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemNo'].' '.$r['itemDesc']?></option>
								<?php
								disp($r['id'],1,$itemID);
								endwhile;
								?>
							</select>
						</td>
						<td><textarea style="width:99%;height:10px;" class="span6 typeahead" name="txItemDesc" id="txItemDesc" required></textarea></td>
						<td><div align="center"><input type="submit" name="btnAdd" class="btn btn-small btn-info" id="btnAdd" value="Add Item"></div></td>
					</tr>
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
<!-- end: JavaScript-->
</body>
</html>