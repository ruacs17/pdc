<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$txLatestRenew='';$txDueRenew='';$txSchedRenew='';$txActualRenew='';$txLocation='';$txDriver='';$txStat='';
$itemEdt = (isset($_REQUEST['itemEdt']) && !empty($_REQUEST['itemEdt']) ) ? functions::decode($_REQUEST['itemEdt']) : 0;

$qEqp = $db->select('equipment','*',array('equip_id'=>$equip_id));
$rInfo = $db->fetch_array($qEqp);
$date_acquired = $rInfo['date_acquired'];
$acquired_year = date('Y',strtotime($date_acquired));
$reg_renew = $rInfo['reg_renew'];
$renw = explode('-', $reg_renew);
$renew_mon = isset($renw[0]) ? $renw[0] : NULL;
$renew_week = isset($renw[1]) ? $renw[1] : NULL;
#$renewal=date('F',strtotime(date('Y-'.$renew_mon.'-d')));
if($renew_week=='01')
	$renew_week='first';
else if($renew_week=='02')
	$renew_week='second';
else if($renew_week=='03')
	$renew_week='third';
else if($renew_week=='04')
	$renew_week='fourth';

if( isset($_POST['btnSave']) && $equip_id && $itemEdt ){
	
	$stat = (isset($_POST['renewStat']) && !empty($_POST['renewStat'])) ? $_POST['renewStat'] : NULL;
	$date_actual = (isset($_POST['txActualRenew']) && functions::valid_date($_POST['txActualRenew'])) ? $_POST['txActualRenew'] : NULL;
	$date_due = date("Y-m-d", strtotime($renew_week." friday ".$itemEdt."-".$renew_mon));
	$date_due_start = date('Y-m-d',strtotime('-2 months',strtotime($date_due)));	
	if($stat!='Renewed')
		$date_actual=NULL;
	if( !empty($itemEdt) ){
		if( $erig_id = $db->getValue('equip_registration','erig_id',array('equip_id'=>$equip_id,'reg_term'=>$itemEdt)) ){
			$db->update('equip_registration',array('equip_id'=>$equip_id,'date_actual'=>$date_actual,'date_due'=>$date_due,'date_due_start'=>$date_due_start,'stat'=>$stat),array('erig_id'=>$erig_id));
			#echo $db->last_query;
			#die();
			$_SESSION['notif_id_list']=$itemEdt;
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
			die();
		}
		else{
			$ins = $db->insert('equip_registration',array('equip_id'=>$equip_id,'reg_term'=>$itemEdt,'date_actual'=>$date_actual,'date_due'=>$date_due,'date_due_start'=>$date_due_start,'stat'=>$stat));
			// #echo $db->last_query;
			if($ins){
				$_SESSION['notif_id_list']=$itemEdt;
				$_SESSION['notif_success']='New Record Added!';
				functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
				die();
			}
			else{
				$_SESSION['notif_warning']='Fail to Add!';
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Registration List</title>
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
	<style>
	.tdSpace{padding: 4px 0px 4px 0px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>VEHICLE REGISTRATION - Manage</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="admin-equipment-registration-manage.php?vdidVw=<?php echo functions::encode($equip_id);?>" style="opacity:.9">Manage</a></li>
				<li><a href="admin-equipment-registration-done.php?vdidVw=<?php echo functions::encode($equip_id);?>">Renewed</a></li>
				<li><a href="admin-equipment-registration-pending.php?vdidVw=<?php echo functions::encode($equip_id);?>">For Renewal</a></li>
				<li><a href="admin-equipment-registration-monitor.php?vdidVw=<?php echo functions::encode($equip_id);?>">Monitoring</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form method="post">
				<select name="selEquip" id="selEquip" data-rel="chosen" style="width:90%;font-size:12px;height:60px;" onchange='propSel(this.value)'>
					<option value="">-- Select --</option>
					<?php
					$qEquip = $db->query('SELECT * FROM equipment WHERE reg_renew IS NOT NULL');
					while($rEquip = $db->fetch_array($qEquip)):
					?>
					<option value="<?php echo functions::encode($rEquip['equip_id'])?>" <?php if($equip_id==$rEquip['equip_id'])echo 'selected="selected"';?>><?php echo strtolower($rEquip['inventory_id']).' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
					<?php endwhile;?>
				</select>
				<div style="padding-top:20px;"></div>
				<?php if($date_acquired){ ?>
				<div class="table-wrapper">
					<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th><div align="center">Term</div></th>
								<th><div align="center">Renewal Schedule</div></th>
								<th><div align="center">Due Date of Renewal</div></th>
								<th><div align="center">Date Actual Renewed</div></th>
								<th width="20%"><div align="center">Location</div></th>
								<th width="17%"><div align="center">Assigned Driver</div></th>
								<th><div align="center">Status</div></th>
								<th><div align="center">&nbsp;</div></th>
							</tr>
						</thead>
						<tbody>
							<?php
							for($i=date('Y'); $i>=($acquired_year-1); $i--):
								$q = $db->select('equip_registration','*',array('equip_id'=>$equip_id,'reg_term'=>$i),'ORDER BY date_actual ASC');
								$r = $db->fetch_array($q);
								$txActualRenew = $r['date_actual'];
								$stat = $r['stat'];
								$date_due = date("Y-m-d", strtotime($renew_week." friday ".$i."-".$renew_mon));
								$date_renewal_sched = date('Y-m-d',strtotime('-2 months',strtotime($date_due)));
								if($itemEdt==$i){
							?>
							<tr>
								<td><div align="center"><?php echo $i.' - '.($i+1);?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_renewal_sched)?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_due);?></div></td>
								<td><div align="center"><input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txActualRenew" id="txActualRenew" value="<?php echo $txActualRenew ?>"></div></td>
								<td>
									<div align="center">
										<?php
										$loc = $db->query('SELECT * FROM `mr_item` mi, mr WHERE mi.mr_id=mr.mr_id AND equip_id="'.$db->clean($equip_id).'" AND mr.mr_date <=  "'.$date_due.'" ORDER BY mr.mr_date DESC LIMIT 1');
										$rLoc = $db->fetch_array($loc);
										$mr_emp = $rLoc['mr_emp'];
										if($db->num_rows($loc)==0){
											$location=$rInfo['location']."<br><i>(in possession)</>";
										}
										else if($rLoc['mr_status']=='Returned'){
											$location=$rInfo['location']."<br><i>(in possession)</>";
										}
										elseif($rLoc['mr_status']=='Unreturned'){
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
										}
										else{
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
										}
										echo $location;
										?>
									</div>
								</td>
								<td><div align="center" style="font-size:10px;"><?php echo $db->getValue('employee','concat(lname," ",fname)',array('emp_id'=>$mr_emp)) ?></div></td>
								<td>
									<div align="center">
										<select name="renewStat" id="renewStat">
											<option value="">--select--</option>
											<option value="Renewed" <?php if($stat=='Renewed')echo 'selected="selected"';?>>Renewed</option>
											<option value="For Renewal" <?php if($stat=='For Renewal')echo 'selected="selected"';?>>For Renewal</option>
											<option value="Extended" <?php if($stat=='Extended')echo 'selected="selected"';?>>Extended</option>
										</select>
									</div>
								</td>
								<td>
									<div align="center">
										<input type="submit" class="btn btn-mini btn-info" title="Update this Information" data-rel="tooltip" name="btnSave" id="btnSave" value="Save">
										<a class="btn btn-mini" title="Update this Information" data-rel="tooltip" href="?vdidVw=<?php echo functions::encode($equip_id);?>">Cancel</a>
									</div>
								</td>
							</tr>
							<?php
								}else{
							?>
							<tr id="rw<?php echo $i?>">
								<td><div align="center"><?php echo $i.' - '.($i+1);?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_renewal_sched)?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_due);?></div></td>
								<td><div align="center"><?php echo functions::datearr($txActualRenew);?></div></td>
								<td>
									<div align="center">
										<?php
										$mr_emp='';
										$loc = $db->query('SELECT * FROM `mr_item` mi, mr WHERE mi.mr_id=mr.mr_id AND equip_id="'.$db->clean($equip_id).'" AND mr.mr_date <=  "'.$date_due.'" ORDER BY mr.mr_date DESC LIMIT 1');
										$rLoc = $db->fetch_array($loc);
										$mr_emp = $rLoc['mr_emp'];
										if($db->num_rows($loc)==0){
											$location=$rInfo['location']."<br><i>(in possession)</>";
										}
										else if($rLoc['mr_status']=='Returned'){
											$location=$rInfo['location']."<br><i>(in possession)</>";
										}
										elseif($rLoc['mr_status']=='Unreturned'){
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
										}
										else{
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
										}
										echo $location;
										?>
									</div>
								</td>
								<td><div align="center" style="font-size:10px;"><?php echo $db->getValue('employee','concat(lname," ",fname)',array('emp_id'=>$mr_emp)) ?></div></td>
								<td>
									<div align="center">
										<?php
										if(empty($stat)){
											if(empty($txActualRenew)){
												if(date('Y-m-d') >= $date_renewal_sched)
													echo 'For Renewal';
											}
										}
										else
											echo $stat;
										?>
									</div>
								</td>
								<td><div align="center"><a id="edt<?php echo $i;?>" class="btn btn-mini btn-warning" title="Update this Information" data-rel="tooltip" href="?vdidVw=<?php echo functions::encode($equip_id);?>&itemEdt=<?php echo functions::encode($i);?>"><i class="halflings-icon white pencil"></i></a></div></td>
							</tr>
							<?php }
							endfor; ?>
						</tbody>
					</table>
				</div>
				<?php }else if($equip_id && empty($date_acquired)){
					echo '<div align="center"><strong>---Please identify date acquired---</strong></div>';
				}?>
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
<script type="text/javascript">
	$('#txActualRenew').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+1 ?>'
	});
	function propSel(PiEwgD){
		if(PiEwgD)
			window.location="<?php echo functions::pageName()?>?vdidVw="+PiEwgD
		else
			window.location="<?php echo functions::pageName()?>"
	}
</script>
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
<!-- end: JavaScript-->
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