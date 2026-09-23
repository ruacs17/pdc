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
$iel_id = (isset($_REQUEST['iel_id']) && !empty($_REQUEST['iel_id']) ) ? functions::decode($_REQUEST['iel_id']) : 0;
$_SESSION['notif_id_list']=$iel_id;
$project_id = $db->getValue('inhouse_equip_leasing','proj_id',array('iel_id'=>$iel_id));

if( isset($_POST['btnAdd']) ){
	$selEquip = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
	$txDuration = ( isset($_POST['txDuration']) && !empty($_POST['txDuration']) ) ? $_POST['txDuration'] : 0;
	#$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$txCost = $db->getValue('equipment','rate',array('equip_id'=>$selEquip));

	if( $txDuration && $selEquip ){
		$ins_id = $db->insert('inhouse_equip_leasing_item',array('iel_id'=>$iel_id,'equip_id'=>$selEquip,'duration'=>$txDuration,'cost'=>$txCost,'discount'=>$txDiscount));
		if($ins_id){
			$_SESSION['notif_success']='New Item Successfully Added!';
			$_SESSION['notif_id2_list']=$ins_id;
		}
			
	}
	functions::sendTo(functions::pageName().'?iel_id='.functions::encode($iel_id));
	die();
}

if( isset($_POST['btnSave']) ){
	$txpoidEdt = ( isset($_POST['txpoidEdt']) && !empty($_POST['txpoidEdt']) ) ? functions::decode($_POST['txpoidEdt']) : 0;
	$txDuration = ( isset($_POST['txDuration']) && !empty($_POST['txDuration']) ) ? $_POST['txDuration'] : 0;
	$selEquip = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? functions::decode($_POST['selEquip']) : '';
	#$txCost = ( isset($_POST['txCost']) && !empty($_POST['txCost']) ) ? functions::moneyToDouble($_POST['txCost']) : 0;
	$txCost = $db->getValue('equipment','rate',array('equip_id'=>$selEquip));
	$txDiscount = ( isset($_POST['txDiscount']) && !empty($_POST['txDiscount']) ) ? $_POST['txDiscount'] : 0;
	$_SESSION['notif_id2_list']=$txpoidEdt;
	if( $txDuration && $selEquip ){
		$db->update('inhouse_equip_leasing_item',array('iel_id'=>$iel_id,'equip_id'=>$selEquip,'duration'=>$txDuration,'cost'=>$txCost,'discount'=>$txDiscount),array('ieli_id'=>$txpoidEdt));
		$_SESSION['notif_success']='Changes Saved!';
	}
	functions::sendTo(functions::pageName().'?iel_id='.functions::encode($iel_id));
	die();
}
if( isset($_REQUEST['poidDel']) && !empty($_REQUEST['poidDel']) ){
	$poidDel = functions::decode($_REQUEST['poidDel']);
	$db->delete('inhouse_equip_leasing_item',array('ieli_id'=>$poidDel));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?iel_id='.functions::encode($iel_id));
	die();
}

$editTrue=0;
$poidEdt='';
$equip_id=''; $duration='';$cost='';$unit='';$discount='';$brand='';
if( isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ){
	$poidEdt = functions::decode($_REQUEST['poidEdt']);
	$editTrue = $db->getValue('inhouse_equip_leasing_item','count(*)',array('ieli_id'=>$poidEdt));
	$qvedt = $db->select('inhouse_equip_leasing_item','*',array('ieli_id'=>$poidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$duration = $rvedt['duration'];
	$cost = $rvedt['cost'];
	$discount = $rvedt['discount'];
	$equip_id = $rvedt['equip_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Leasing Item Manage</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Equipment Leasing Item Manage</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="left"><br>
					<div>Project: 
						<strong>
							<?php 
							if($project_id){
							$qProj = $db->select('project','*',array('proj_id'=>$project_id));
							while($rProj = $db->fetch_array($qProj)):
							echo strtoupper($rProj['proj_name']);
							echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
							endwhile;
							}
							else
							echo $db->getValue('inhouse_equip_leasing','payee',array('iel_id'=>$iel_id));
							?>
						</strong>
					</div>
					<div>Date: <strong><?php echo functions::datearr($db->getValue('inhouse_equip_leasing','iel_date',array('iel_id'=>$iel_id)));?></strong></div><br><br>
				</div>
				<input type="hidden" name="txpoidEdt" id="txpoidEdt" value="<?php echo functions::encode($poidEdt);?>">
				<table width="100%" border="0" align="center" class="table table-striped">
					<tr>
						<th width="30%" scope="col"><div align="center">Equipment</div></th>
						<th width="10%" scope="col"><div align="center">Duration (Hour)</div></th>
						<th width="8%" scope="col"><div align="center">Discount(%)</div></th>
						<th width="10%" scope="col">&nbsp;</th>
					</tr>
					<tr bgcolor="#f7ebeb">
						<td>
							<div align="left">
								<select name="selEquip" id="selEquip" data-rel="chosen" style="width:550px;font-size:12px;height:60px;">
									<option value="">-- Select Equipment --</option>
									<?php $qEquip = $db->select('equipment','*',array(),'ORDER BY type');
									while($rEquip = $db->fetch_array($qEquip)):
									?>
									<option value="<?php echo functions::encode($rEquip['equip_id'])?>" <?php if($equip_id==$rEquip['equip_id'])echo 'selected="selected"';?>><?php echo strtolower($rEquip['inventory_id']).' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
									<?php endwhile;?>
								</select><br>
								<span class="help-inline warning" id="msgtxEquip" style="font-weight:bold;" name="msgtxEquip"></span>
							</div>
						</td>
						<td>
							<div align="center"><input type="text" style="width:80px;text-align:center;" name="txDuration" id="txDuration" value="<?php echo $duration?>" onkeypress="return checkinput(this, event);"></div>
							<span class="help-inline warning" id="msgtxDuration" style="font-weight:bold;" name="msgtxDuration"></span>
						</td>
						<td><div align="center"><input type="text" style="width:80px;text-align:center;" name="txDiscount" id="txDiscount" value="<?php echo $discount?>" onkeypress="return checkinput(this, event);"></div></td>
						<td>
							<div align="center">
								<?php 
								if($editTrue)
								echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-small">';
								else
								echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary btn-small">';
								?>
							</div>
						</td>
					</tr>
				</table><br><br><br>
				<table width="100%" border="0" align="center" id="tblist" class="table <?php if(!isset($_SESSION['notif_id2_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
					<tr style="background-color:#CCC;">
						<th width="45%" scope="col"><div align="left">Equipment</div></th>
						<th width="7%" scope="col"><div align="center">Duration (Hour)</div></th>
						<th width="7%" scope="col"><div align="right">Rate</div></th>
						<th width="7%" scope="col"><div align="center">Discount</div></th>
						<th width="8%" scope="col"><div align="right">Amount</div></th>
						<th width="5%" scope="col">&nbsp;</th>
					</tr>
					<?php
					$total_amount=0;$disc_amount=0;$amount=0;
					$qPOI = $db->select('inhouse_equip_leasing_item','*',array('iel_id'=>$iel_id));
					while($rPOI = $db->fetch_array($qPOI)):
						$amount = $rPOI['cost'] * $rPOI['duration'];
						$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
						$amount = $amount - $disc_amount;
						$total_amount += $amount;
						$equipmentName = '';
						$qEqp = $db->select('equipment','*',array('equip_id'=>$rPOI['equip_id']));
						while($rEqp = $db->fetch_array($qEqp)):
							$equipmentName = ucwords(strtolower($rEqp['inventory_id']." ".$rEqp['equip_desc']." ".$rEqp['plate_no']." ".$rEqp['serial_no']));
						endwhile;
					?>
					<tr id="rw<?php echo $rPOI['ieli_id'];?>">
						<td><?php echo $equipmentName;?></td>
						<td><div align="center"><?php echo round($rPOI['duration'],2);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
						<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
						<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						<td>
							<div align="center">
								<a id="edit<?php echo $rPOI['ieli_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?iel_id=<?php echo functions::encode($iel_id);?>&poidEdt=<?php echo functions::encode($rPOI['ieli_id']);?>"><i class="halflings-icon white pencil"></i></a>
								<a id="del<?php echo $rPOI['ieli_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?iel_id=<?php echo functions::encode($iel_id);?>&poidDel=<?php echo functions::encode($rPOI['ieli_id']);?>"><i class="halflings-icon white trash"></i></a>
							</div>
						</td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><div align="right"><strong>Total Amount</strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
						<td></td>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxEquip').html("");
		$('#msgtxDuration').html("");

		if( $('#selEquip').val()=="" ){
			$('#msgtxEquip').html("Specify Equipment!");
			$('#selEquip').focus();
			res=false;
		}
		else if( $('#txDuration').val()=="" ){
			$('#msgtxDuration').html("Required!");
			$('#txDuration').focus();
			res=false;
		}
		else
			res=true;
		return res;
	});
});
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?proj_id="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
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
<?php if(isset($_SESSION['notif_id2_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id2_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id2_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id2_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id2_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id2_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>