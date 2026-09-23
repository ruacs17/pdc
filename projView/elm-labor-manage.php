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
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$_SESSION['notif_id_list']=$itemID;
function disp($parentItem,$level,$currentItemID){
	global $db;
	global $itemID;
	$string='';
	$q = $db->select('elm_rate','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned)');
	while($r = $db->fetch_array($q)):
		if($itemID!=$r['id']){
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
		}
	endwhile;
}

function updateSubItem($itemID){
	global $db;
	$itemParentItemNo = $db->getValue('elm_rate','itemNo',array('id'=>$itemID));
	$q = $db->select('elm_rate','*',array('itemParent'=>$itemID),'ORDER BY id');
	$count=0;
	while($r = $db->fetch_array($q)):
		$count++;
		$newItemNo = $itemParentItemNo. '.' . $count;
		$db->update('elm_rate',array('itemNo'=>$newItemNo),array('id'=>$r['id']));
		updateSubItem($r['id']);
	endwhile;
}

$count=0;
$type=(isset($_REQUEST['tpe']) && !empty($_REQUEST['tpe']) ) ? functions::decode($_REQUEST['tpe']) : 0;
$itemID=(isset($_REQUEST['itemID']) && !empty($_REQUEST['itemID']) ) ? functions::decode($_REQUEST['itemID']) : 0;
$readOnly =  ( $db->getValue('elm_rate','count(*)',array('itemParent'=>$itemID)) ) ? "readonly" : "";
$txItemDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$itemID));
$qItemDetail = $db->select('elm_rate','*',array('id'=>$itemID));
$r = $db->fetch_array($qItemDetail);
$itemBase=($r['itemBase']) ? $r['itemBase'] : '';
$txItemDesc=($r['itemDesc']) ? $r['itemDesc'] : '';
$txItemRate = ($r['itemRate']) ? functions::formatMoney($r['itemRate']) : '';
$txUnit = ($r['itemUnit']) ? $r['itemUnit'] : '';
$itemParentTx = ($r['itemParent']) ? $r['itemParent'] : '';

$qUnit = $db->select('elm_rate','DISTINCT itemUnit',array());
$namesUnit='';
while($rUnit=$db->fetch_array($qUnit)):
	$string = preg_replace("/'/",'"',$rUnit['itemUnit']);
	$namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesUnit .= '"--"';

if( isset($_POST['btnSave']) ){
	$selParentItem = (isset($_POST['selParentItem']) && !empty($_POST['selParentItem']) ) ? $_POST['selParentItem'] : '';
	if($selParentItem!=$itemParentTx){
		$itemIDParent = $db->getValue('elm_rate','itemParent',array('id'=>$itemID));
		$rootItemNo = $db->getValue('elm_rate','itemNo',array('id'=>$selParentItem));
		$subItemNo = $db->getValue('elm_rate','count(*)',array('itemParent'=>$selParentItem)) + 1;
		$newItemNo = $rootItemNo.'.'.$subItemNo;
		$db->update('elm_rate',array('itemNo'=>$newItemNo,'itemParent'=>$selParentItem),array('id'=>$itemID));
		updateSubItem($itemID); #updating Item's new subItem
		updateSubItem($itemIDParent); #update Item's recent subItem
	}

	$itemDesc = (isset($_POST['txItemDesc']) && !empty($_POST['txItemDesc']) ) ? trim($_POST['txItemDesc']) : '';
	$itemRate = (isset($_POST['txItemRate']) && !empty($_POST['txItemRate']) ) ? functions::moneyToDouble(trim($_POST['txItemRate'])) : 0; 
	$itemUnit = (isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : '';
	$db->update('elm_rate',array('itemDesc'=>$itemDesc,'itemRate'=>$itemRate,'itemUnit'=>$itemUnit),array('id'=>$itemID));
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo(functions::pageName().'?itemID='.functions::encode($itemID));
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>ELM LABOR Manage</title>
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
	<script src="../js/inputInt.js"></script>
	<script src="../js/formatCurrency.js"></script>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Item Manage</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div>
					<strong>Parent Item:</strong>
					<select name="selParentItem" id="selParentItem" style="width:90%;">
						<option value="">None</option>
						<?php 
						$q = $db->select('elm_rate','*',array('id'=>$itemBase),'ORDER BY cast(itemNo as unsigned)');
						while($r = $db->fetch_array($q)):?>
						<option value="<?php echo $r['id']?>" <?php if($itemParentTx==$r['id'])echo 'selected="selected"';?>><?php echo $r['itemDesc']?></option>
						<?php
						disp($r['id'],1,$itemParentTx);
						endwhile;
						?>
					</select>
				</div><br/><br/><br/>
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
					<tr style="background-color:#CCC;">
						<th width="50%" scope="col"><div align="center">Item</div></th>
						<th width="12%" scope="col"><div align="center">Unit</div></th>
						<th width="10%" scope="col"><div align="center">Rate</div></th>
						<th width="8%" scope="col"><div align="center">&nbsp;</div></th>
					</tr>
					<tr>
						<td><div align="left"><textarea style="width:99%; height:10px;" class="span6 typeahead" name="txItemDesc" id="txItemDesc" required><?php echo $txItemDesc?></textarea></div></td>
						<td><div align="center"><input type="text" name="txUnit" id="txUnit" value="<?php echo $txUnit;?>" style="width: 140px;text-align:center;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesUnit;?>]'></div></td>
						<td><div align="center"><input type="text" name="txItemRate" id="txItemRate" value="<?php echo $txItemRate;?>" style="width: 140px;text-align:center;" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);"></div></td>
						<td><div align="center"><input type="submit" name="btnSave" class="btn btn-small btn-info" id="btnSave" value=" Save "></div></td>
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