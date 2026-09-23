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
$equip_id = (isset($_REQUEST['eqp']) && !empty($_REQUEST['eqp']) ) ? functions::decode($_REQUEST['eqp']) : 0;

unset($_SESSION['arr_proj']);
if( isset($_REQUEST['txItmDel']) && !empty($_REQUEST['txItmDel']) ){
	$er_idDel = functions::decode($_REQUEST['txItmDel']);
	if( $ref_id = $db->getValue('equip_repair','ref_id',array('er_id'=>$er_idDel,'equip_id'=>$equip_id,'multi'=>'1')) ){
		//For multiple entry only.
		if( $db->getValue('equip_repair','count(*)',array('ref_id'=>$ref_id,'equip_id'=>$equip_id,'multi'=>'1'))==1 ){
			//If there's only 1 remaining entry, then it can delete the image.
			$di_id = $db->getValue('equip_repair','di_id',array('er_id'=>$er_idDel,'equip_id'=>$equip_id,'multi'=>'1'));
			$db->delete('equip_repair',array('er_id'=>$er_idDel,'equip_id'=>$equip_id));
			$oldFileName = $db->getValue('doc_img','doc_name',array('di_id'=>$di_id));
			if($oldFileName)
				unlink('../img_equip/'.$oldFileName);
			$db->delete('doc_img',array('di_id'=>$di_id));
		}
		else{//only the entry will be removed.
			$db->delete('equip_repair',array('er_id'=>$er_idDel,'equip_id'=>$equip_id));
		}
	}
	else{
		//For Single Entry.
		$di_id = $db->getValue('equip_repair','di_id',array('er_id'=>$er_idDel,'equip_id'=>$equip_id));
		$db->delete('equip_repair',array('er_id'=>$er_idDel,'equip_id'=>$equip_id));
		$oldFileName = $db->getValue('doc_img','doc_name',array('di_id'=>$di_id));
		if($oldFileName)
			unlink('../img_equip/'.$oldFileName);
		$db->delete('doc_img',array('di_id'=>$di_id));
	}
	$_SESSION['notif_warning']="Item Removed";
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
	die();
}

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['er_equip'] = (isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? $_POST['selEquip'] : '';
}
if( (isset($_REQUEST['eqp']) && !empty($_REQUEST['eqp']) ) )
	$_SESSION['er_equip']=functions::decode($_REQUEST['eqp']);
$selEquip = ( isset($_SESSION['er_equip']) ) ? $_SESSION['er_equip'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Repair Detail</title>
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
	.tdSpace{padding: 4px 0px 4px 0px;}.amnt{padding-right:10px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white th"></i><span class="break"></span>PROPERTY REPAIR & MAINTENANCE HISTORY</h2>
		</div>
		<form method="post">
			<div style="padding-top:30px;">&nbsp;</div>
			<table border="0">
				<tr>
					<td width="10%"></td>
					<td width="25%">
						<div align="left">
							<select name="selEquip" id="selEquip" data-rel="chosen" style="width:1050px;" onChange="window.location='?eqp='+this.value">
								<option value="">All Project / Payee</option>
								<?php
								$qEquip = $db->query('SELECT eqr.equip_id,eqp.name,inventory_id,equip_desc,plate_no,serial_no FROM equipment eqp, equip_repair eqr WHERE eqp.equip_id=eqr.equip_id GROUP BY eqp.name ORDER BY eqp.name');
								while($rEquip = $db->fetch_array($qEquip)):
								?>
								<option value="<?php echo functions::encode($rEquip['equip_id'])?>" <?php if($selEquip===$rEquip['equip_id'])echo 'selected="selected"';?>><?php echo strtolower($rEquip['inventory_id']).' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
								<?php endwhile;?>
							</select>
						</div>
					</td>
				</tr>
			</table>
			<?php
			#if($selEquip)
				$qEqp = $db->select('equipment eqp, equip_repair eqr','eqr.equip_id,eqp.name',array('eqp.equip_id'=>$selEquip),'AND eqp.equip_id=eqr.equip_id GROUP BY eqp.name ORDER BY eqp.name');
			#else
				#$qEqp = $db->query('SELECT eqr.equip_id,eqp.name FROM equipment eqp, equip_repair eqr WHERE eqp.equip_id=eqr.equip_id GROUP BY eqp.name ORDER BY eqp.name');
			while($rEqp = $db->fetch_array($qEqp)):
				$equip_id=$rEqp['equip_id'];
			?>
			<div style="padding: 20px 0px 20px 0px;"><strong><?php echo $rEqp['name']; ?></strong></div>
			<div class="table-wrapper">
				<table id="tblist" class="table table-hover table-bordered table-striped" width="70%" style="font-size:12px;" border="0" cellspacing="0" cellpadding="0">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="7%" scope="col"><div align="center">Date</div></th>
							<th width="5%" scope="col"><div align="center">Repair<br>Type</div></th>
							<th width="17%" scope="col"><div align="center">Damage</div></th>
							<th width="17%" scope="col"><div align="center">Spare Parts / Service</div></th>
							<th width="7%" scope="col"><div align="center">Quotation</div></th>
							<th width="7%" scope="col"><div align="center">Reference</div></th>
							<th width="12%" scope="col"><div align="center">Charge To</div></th>
							<th width="5%" scope="col"><div align="center">Performed By</div></th>
							<th width="5%" scope="col"><div align="center">Remarks</div></th>
							<th width="6%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total_labor=0;$total_parts=0;$overall_quotation=0;
						$qShow = $db->select('equip_repair','*',array('equip_id'=>$equip_id));
						while($rShow = $db->fetch_array($qShow)):
							$discount = ($rShow['discount']) ? $rShow['discount'] / 100 : 0;
							$less = ($rShow['quotation_labor'] + $rShow['quotation_part']) * $discount;
							$totalQuotation = ($rShow['quotation_labor'] + $rShow['quotation_part']) - $less;
							$total_labor += ($rShow['quotation_labor']) ? $rShow['quotation_labor'] : 0;
							$total_parts += ($rShow['quotation_part']) ? $rShow['quotation_part'] : 0;
							$overall_quotation += $totalQuotation;
						?>
						<tr id="rw<?php echo $rShow['er_id'];?>">
							<td><div align="center"><?php echo functions::datearr($rShow['er_date'])?></div></td>
							<td><?php echo $rShow['repair_type']?></td>
							<td><?php echo nl2br($rShow['damage'])?></td>
							<td><?php echo nl2br($rShow['description'])?></td>
							<td><div align="right"><?php echo functions::formatMoney($totalQuotation)?></div></td>
							<td>
								<div align="center">
									<?php if( empty($rShow['po_id']) && !empty($rShow['invoice']) ){?>
										<div><?php echo $rShow['invoice']; ?></div>
										<a id="editpo<?php echo $rShow['er_id'];?>" title="Select P.O." data-rel="tooltip" href="#" class="btn btn-mini btn-danger thickbox" onclick="showThis(this.id,'equipment-repair-po.php?vdidVw=<?php echo functions::encode($equip_id);?>&er_id=<?php echo functions::encode($rShow['er_id']);?>','Select P.O.')"><i class="halflings-icon white pencil"></i></a>
									<?php }else if( !empty($rShow['po_id']) && !empty($rShow['invoice']) ){ ?>
										<a id="editpo<?php echo $rShow['er_id'];?>" title="Select P.O." data-rel="tooltip" href="#" class="thickbox" onclick="showThis(this.id,'equipment-repair-po.php?vdidVw=<?php echo functions::encode($equip_id);?>&er_id=<?php echo functions::encode($rShow['er_id']);?>','Select P.O.')"><?php echo ($rShow['invoice']) ? $rShow['invoice'] : $rShow['po_id']; ?></a>
									<?php }else{?>
										<a id="editpo<?php echo $rShow['er_id'];?>" title="Select P.O." data-rel="tooltip" href="#" class="btn btn-mini btn-warning thickbox" onclick="showThis(this.id,'equipment-repair-po.php?vdidVw=<?php echo functions::encode($equip_id);?>&er_id=<?php echo functions::encode($rShow['er_id']);?>','Select P.O.')"><i class="halflings-icon white plus"></i></a>
									<?php } ?>
								</div>
							</td>
							<td><div align="center"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rShow['proj_id']))?></div></td>
							<td><?php echo $rShow['incharge']?></td>
							<td><?php echo $rShow['remarks']?></td>
							<td>
								<div align="center">
									<?php if($rShow['multi']){?>
									<a id="edit<?php echo $rShow['er_id'];?>" title="Update this History" data-rel="tooltip" href="#" class="btn btn-mini btn-warning btn-setting thickbox" onclick="showThis(this.id,'admin-equipment-view-repair-edit-multiple.php?vdidVw=<?php echo functions::encode($equip_id);?>&txItmEdt=<?php echo functions::encode($rShow['ref_id']);?>','Update Property Repair')"><i class="halflings-icon white pencil"></i></a>
									<?php }else{?>
									<a id="edit<?php echo $rShow['er_id'];?>" title="Update this History" data-rel="tooltip" href="#" class="btn btn-mini btn-warning btn-setting thickbox" onclick="showThis(this.id,'admin-equipment-view-repair-add.php?vdidVw=<?php echo functions::encode($equip_id);?>&txItmEdt=<?php echo functions::encode($rShow['er_id']);?>','Update Property Repair')"><i class="halflings-icon white pencil"></i></a>
									<?php }?>
									<a id="del<?php echo $rShow['er_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?vdidVw=<?php echo functions::encode($equip_id);?>&txItmDel=<?php echo functions::encode($rShow['er_id']);?>"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div align="right"><strong><?php echo functions::formatMoney($overall_quotation);?></strong></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php endwhile; ?>
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
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false; 
}

$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgtxDesc').html("");
		$('#msgselType').html("");

		if( $('#txDesc').val()=="" ){
			$('#msgtxDesc').html("Specify Description!");
			$('#txDesc').focus();
			res=false;
		}
		else if( $('#selType').val()=="" ){
			$('#msgselType').html("Repair Type Required!");
			$('#selType').focus();
			res=false;
		}
		else
			res=true;
		return res;
    });
});
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