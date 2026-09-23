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
$temp_search=(isset($_SESSION['temp_search']) && !empty($_SESSION['temp_search']) ) ? $_SESSION['temp_search'] : '';
$temp_itemBase = ($temp_search) ? $db->getValue('elm_rate','itemBase',array('itemParent'=>0,'itemDesc'=>'Equipment')) : 0;
$ca_id=(isset($_REQUEST['ca_id']) && !empty($_REQUEST['ca_id']) ) ? functions::decode($_REQUEST['ca_id']) : 0;
$cag_id=(isset($_REQUEST['cag_id']) && !empty($_REQUEST['cag_id']) ) ? functions::decode($_REQUEST['cag_id']) : 0;
$_SESSION['ca_list_notif']=$cag_id;
$delcad_id=(isset($_REQUEST['delcad_id']) && !empty($_REQUEST['delcad_id']) ) ? functions::decode($_REQUEST['delcad_id']) : 0;
if($delcad_id){
	$_SESSION['notif_warning']='Item Removed!';
	$db->delete('cag_detail',array('cagd_id'=>$delcad_id));
	functions::sendTo(functions::pageName().'?cag_id='.functions::encode($cag_id));
	die();
}

if($ca_id && $cag_id==0){
	$insertID = $db->insert('ca_group',array('ca_id'=>$ca_id,'title'=>'Description Here','type'=>'equipment'));
	functions::sendTo(functions::pageName().'?cag_id='.functions::encode($insertID));
	die();
}
if( isset($_POST['txTitle']) ){
	$title = trim($_POST['txTitle']);
	$db->update('ca_group',array('title'=>$title),array('cag_id'=>$cag_id));
	$_SESSION['notif_success']='Description Detail Saved!';
	functions::sendTo(functions::pageName().'?cag_id='.functions::encode($cag_id));
	die();
}
if( isset($_POST['btnClear']) ){
	unset($_SESSION['temp_search']);
	functions::sendTo(functions::pageName().'?cag_id='.functions::encode($cag_id));
	die();
}

function itemSubTotal($itemParent,&$sum){
	global $db;
	$q = $db->select('elm','*',array('itemParent'=>$itemParent));
	while($r = $db->fetch_array($q)):
		$sum += $r['total'];
		itemSubTotal($r['id'],$sum);
	endwhile;
}
function itemTotal($itemParent){
	$sum=0;
	itemSubTotal($itemParent,$sum);
	return $sum;
}
function itemUnitName($itemParent){
	global $db;
	$unit='';
	$q = $db->select('elm_rate','*',array('itemParent'=>$itemParent));
	while($r = $db->fetch_array($q)):
		if( $r['itemUnit'] ){
			$unit = $r['itemUnit'];
			break;
		}
		else
			$unit = itemUnitName($r['id']);
	endwhile;
	return $unit;
}

function disp($parentItem,$level){
	global $db;
	global $cag_id;
	global $temp_search;
	global $temp_itemBase;
	$string='';
	if($temp_search)
		$q = $db->select('elm_rate','*',array('itemBase'=>$temp_itemBase),'AND itemDesc LIKE "%'.$temp_search.'%" AND itemRate > 0 ORDER BY cast(itemNo as unsigned),id');
	else
		$q = $db->select('elm_rate','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned),id');
	#$q = $db->select('elm_rate','*',array('itemParent'=>$parentItem),'ORDER BY cast(itemNo as unsigned),id');
	while($r = $db->fetch_array($q)):
		$itemNo = ($r['itemNo']) ? $r['itemNo'] : "";
		$itemDesc = ($r['itemDesc']) ? $r['itemDesc'] : "";
		$itemRate = ($r['itemRate']) ? functions::formatMoney($r['itemRate']) : '';
		$itemUnit = ($r['itemUnit']) ? $r['itemUnit'] : "";
		if( $db->getValue('elm_rate','count(*)',array('itemParent'=>$r['id'])) ){
			$itemDesc = ($r['itemDesc']) ? '<strong>'.$r['itemDesc'].'</strong>' : "";
		}

		$pad=0;
		for($i=1; $i<=$level; $i++):
			$pad+=40;
		endfor;
		$btn='';
		if( $itemRate ){
			$addSubItem = "showThis(this.id,'ca_equipment_detail_add.php?itemID=".functions::encode($r['id'])."&cag_id=".functions::encode($cag_id)."','Adding DQC Item')";
			$btn = '<a id="AddSubItem'.$r['id'].'" style="cursor:pointer" title="Add this Item" data-rel="tooltip" onclick="'.$addSubItem.'"><i class="halflings-icon plus-sign"></i></a>';
		}

		$string ='
		<tr id="rww'.$r['id'].'">
			<td>
				<div style="padding-left:'.$pad.'px;">
					<div style="display:flex;">
						<div style="flex: 0;border:0px solid;">'.$itemNo.'</div>
						<div style="flex: 2;border:0px solid;padding-left:15px;">'.$itemDesc.'</div>
					</div>
				</div>
			</td>
			<td><div align="center">'.$itemUnit.'</div></td>
			<td><div align="right">'.$itemRate.'</div></td>
			<td><div align="center">'.$btn.'</div></td>
		</tr>
		';
		echo $string;
		if(empty($temp_search))
			disp($r['id'],$level + 1);
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Cost Equipment Analysis</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
	<style>
	a{
		opacity: 0.4;
		filter: alpha(opacity=20); /* For IE8 and earlier */
	}
	a:hover {
		opacity: 1.0;
		filter: alpha(opacity=100); /* For IE8 and earlier */
	}
	</style>
	<script>
	function delt(){
		if(confirm('Do you want to remove this Item?'))
			return true;
		else
			return false; 
	}
	</script>
</head>
<body>
<div id="spinner"></div>
<!-- body content: start here-->
<div class="row-fluid">
	<form method="post">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>Cost Analysis Equipment</h2>
			</div>
			<div class="box-content"><br>
				<form method="post">
					<div class="controls">Description: 
						<div class="input-append">
							<input type="text" name="txTitle" id="txTitle" style="width:500px;" value="<?php echo $db->getValue('ca_group','title',array('cag_id'=>$cag_id));?>"><input type="submit" class="btn btn-small btn-info" name="btnSaveDesc" id="btnSaveDesc" value="Save">
						</div>
					</div>
				</form>
				<table id="tblist" width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
					<thead>
						<tr bgcolor="#CCCCCC">
							<th width="33%" scope="col">Equipment</th>
							<th width="9%" scope="col"><div align="left">Capabilites</div></th>
							<th width="9%" scope="col"><div align="right">No. of Units</div></th>
							<th width="9%" scope="col"><div align="right">No. of Days</div></th>
							<th width="9%" scope="col"><div align="right">Daily Rate</div></th>
							<th width="9%" scope="col"><div align="right">Cost</div></th>
							<th width="7%" scope="col"><div align="center">Options</div></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$q_detail = $db->select('cag_detail','*',array('cag_id'=>$cag_id));
						while($r_detail = $db->fetch_array($q_detail)):
						$rate = $db->getValue('elm_rate','itemRate',array('id'=>$r_detail['element_id']));
						$elementDesc = $db->getValue('elm_rate','itemDesc',array('id'=>$r_detail['element_id']));
						?>
						<tr id="rw<?php echo $r_detail['cagd_id']?>">
							<td><?php echo $elementDesc?></td>
							<td><?php echo $db->getValue('el_capability','concat(slow_manhour," ",itemUnit)',array('itemDesc'=>$elementDesc))?></td>
							<td><div align="right"><?php echo functions::formatMoney($r_detail['unitMen'])?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($r_detail['day'])?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rate)?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rate * $r_detail['unitMen'] * $r_detail['day'])?></div></td>
							<td>
								<div align="center">
									<a id="ManageItem<?php echo $r_detail['cagd_id']?>" style="cursor:pointer" title="Manage Item" data-rel="tooltip" onclick="showThis(this.id,'ca_equipment_detail_add.php?cagd_id=<?php echo functions::encode($r_detail['cagd_id']);?>&cag_id=<?php echo functions::encode($r_detail['cag_id']);?>','Adding Item')"><i class="halflings-icon pencil"></i></a>
									<a id="deleteItem<?php echo $r_detail['cagd_id']?>" style="cursor:pointer" title="Delete Item" data-rel="tooltip" onClick="return delt()" href="<?php echo functions::pageName()?>?cag_id=<?php echo functions::encode($cag_id);?>&delcad_id=<?php echo functions::encode($r_detail['cagd_id']);?>"><i class="halflings-icon minus-sign"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br><br>
				<form method="post">
					<div class="controls">Search: 
						<div class="input-append">
							<input type="text" name="txSearch" id="txSearch" value="<?php echo $temp_search;?>" onkeyup="sendData(this.value)"><input type="submit" class="btn btn-small" name="btnClear" id="btnClear" value="Clear">
						</div>
					</div>
				</form>
				<div id="displayit">
					<table id="tblist2" width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['notif_id_list2'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
						<thead>
							<tr bgcolor="#CCCCCC">
								<th width="65%" scope="col"><div align="left">Item</div></th>
								<th width="7%" scope="col"><div align="center">Unit</div></th>
								<th width="7%" scope="col"><div align="right">Rate</div></th>
								<th width="5%" scope="col">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$qList = $db->select('elm_rate','*',array('itemParent'=>0,'itemDesc'=>'Equipment'),'ORDER BY cast(itemNo as unsigned)');
							while($rList = $db->fetch_array($qList)):
							?>
							<tr>
								<td width="25%" ><strong><?php echo $rList['itemDesc']?></strong></td>
								<td><div align="center"><?php echo ($rList['itemUnit']) ? $rList['itemUnit'] : ''?></div></td>
								<td><div align="right"><?php echo ($rList['itemRate']) ? $rList['itemRate'] : ''?></div></td>
								<td>&nbsp;</td>
							</tr>
							<?php disp($rList['id'],1);?>
							<?php endwhile; ?>
							<tr>
								<td colspan="4">&nbsp;</td>
							</tr>
						</tbody>
					</table><p>&nbsp;</p>
				</div>
			</div>
		</div><!--/span-->
	</form>
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
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>


<?php if(isset($_SESSION['notif_id_list2'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rww<?php echo $_SESSION['notif_id_list2'] ?>').centerView();
	$('#rww<?php echo $_SESSION['notif_id_list2'] ?>').css('border','3px solid green');
	$("#rww<?php echo $_SESSION['notif_id_list2'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rww<?php echo $_SESSION['notif_id_list2'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist2').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list2']);} ?>



<script language="javascript">
var xmlhttp
var msg
function sendData(itm){
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null){
		alert ("Your browser does not support AJAX!");
		return;
	}
	var pg="ca_equipment_detail-res.php?";
	url=pg+"itm="+itm+"&cag_id=<?php echo functions::encode($cag_id);?>"
	xmlhttp.onreadystatechange=stateChanged;
	xmlhttp.open("GET",url,true);
	xmlhttp.send(null);
}

function stateChanged(){
	if (xmlhttp.readyState==4){
		document.getElementById("displayit").innerHTML=xmlhttp.responseText;
	}
}
function GetXmlHttpObject(){
	if (window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	}
	if (window.ActiveXObject){
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	return null;
}
</script>
<!-- end: JavaScript-->
</body>
</html>