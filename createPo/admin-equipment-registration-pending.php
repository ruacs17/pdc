<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
#$color_expiring='#fcb77b';
#$color_expired='#D66C5B';
$color_expiring='';
$color_expired='#fcb77b';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Property Registration List - Pending</title>
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
			<h2><i class="halflings-icon white th"></i><span class="break"></span>VEHICLE REGISTRATION - Pending</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="admin-equipment-registration-manage.php?vdidVw=<?php echo functions::encode($equip_id);?>">Manage</a></li>
				<li><a href="admin-equipment-registration-done.php?vdidVw=<?php echo functions::encode($equip_id);?>">Renewed</a></li>
				<li class="active"><a href="admin-equipment-registration-pending.php?vdidVw=<?php echo functions::encode($equip_id);?>" style="opacity:.9">For Renewal</a></li>
				<li><a href="admin-equipment-registration-monitor.php?vdidVw=<?php echo functions::encode($equip_id);?>">Monitoring</a></li>
			</ul>
			<table width="180" cellspacing="0" cellpadding="0" border="0" align="left">
				<tr>
					<td width="10"><div style="background-color:<?php echo $color_expired?>; width:20px;">&nbsp;</div></td>
					<td width="90">&nbsp;Expired</td>
				</tr>
			</table>
			<div style="padding-top:10px">&nbsp;</div>
			<?php $date_now = date('Y-m-25'); #echo functions::datearr($date_now); ?>
			<select name="selEquip" id="selEquip" data-rel="chosen" style="width:1000px;font-size:12px;height:60px;" onchange='propSel(this.value)'>
				<option value="">-- All --</option>
				<?php
				$countSelected=0;
				$qEquip = $db->query('SELECT eq.* FROM equipment eq, equip_registration er WHERE eq.equip_id=er.equip_id AND eq.reg_renew IS NOT NULL AND eq.status NOT IN ("Trade In","Sold","Inactive","Unserviceable") AND "'.$date_now.'" BETWEEN er.date_due AND er.date_validity AND er.stat="For Renewal" ORDER BY equip_desc');
				while($rEquip = $db->fetch_array($qEquip)):
				?>
				<option value="<?php echo functions::encode($rEquip['equip_id'])?>" <?php if($equip_id==$rEquip['equip_id']){echo 'selected="selected"'; $countSelected++;}?>><?php echo $rEquip['inventory_id'].' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
				<?php endwhile;?>
			</select>
			<?php if(empty($countSelected) ) $equip_id='';$eqd=$equip_id; ?>
			<div style="padding-top:20px;"></div>
			<div align="center">
				<div><h2>-- For Renewal --</h2></div>
				<div class="table-wrapper">
					<table id="tblist" class="table <?php if(!isset($_SESSION['notifid_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
						<thead>
							<tr style="background-color:#CCC;">
								<th><div align="center">Make</div></th>
								<th width="30%"><div align="center">Property</div></th>
								<th><div align="center">Plate No</div></th>
								<th><div align="center">Renewal Schedule</div></th>
								<th><div align="center">Renewal Due Date</div></th>
								<th><div align="center">Location</div></th>
								<th><div align="center">Assigned Driver</div></th>
								<th><div align="center">&nbsp;</div></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$countRec=0;
							if($equip_id)
								$qEquip = $db->query('SELECT * FROM equipment eq, equip_registration er WHERE eq.equip_id=er.equip_id AND eq.reg_renew IS NOT NULL AND eq.status NOT IN ("Trade In","Sold","Inactive","Unserviceable") AND "'.$date_now.'" BETWEEN er.date_due AND er.date_validity AND er.stat="For Renewal" AND eq.equip_id="'.$db->clean($equip_id).'"');
							else
								$qEquip = $db->query('SELECT * FROM equipment eq, equip_registration er WHERE eq.equip_id=er.equip_id AND eq.reg_renew IS NOT NULL AND eq.status NOT IN ("Trade In","Sold","Inactive","Unserviceable") AND "'.$date_now.'" BETWEEN er.date_due AND er.date_validity AND er.stat="For Renewal" ORDER BY er.date_due');
							#echo $db->last_query;
							while($r = $db->fetch_array($qEquip)):
								$countRec++;
								$equip_id=$r['equip_id'];
								$erig_id = $r['erig_id'];
								$reg_renew = $r['reg_renew'];
								$date_acquired = $r['date_acquired'];
								$acquired_year = date('Y',strtotime($date_acquired));
								$stat = $r['stat'];
								$date_due=$r['date_due'];
								$date_due_start = $r['date_due_start'];
								$date_actual = $r['date_actual'];
								$reg_term = $r['reg_term'];
								$bgCol = $color_expiring;
								if($date_due < $date_now)
									$bgCol = $color_expired;
							?>
							<tr id="rw<?php echo $equip_id?>" style="background-color:<?php echo $bgCol;?>">
								<td><div align="left"><?php echo $r['type'] ?></div></td>
								<td><div align="left"><?php echo $r['name'] ?></div></td>
								<td><div align="left"><?php echo $r['plate_no'] ?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_due_start);?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_due)?></div></td>
								<td>
									<div align="left">
										<?php
										$mr_emp='';
										$loc = $db->query('SELECT * FROM `mr_item` mi, mr WHERE mi.mr_id=mr.mr_id AND equip_id="'.$db->clean($equip_id).'" AND mr.mr_date <= "'.$date_due.'" ORDER BY mr.mr_date DESC LIMIT 1');
										$rLoc = $db->fetch_array($loc);
										$mr_emp = $rLoc['mr_emp'];
										if($db->num_rows($loc)==0){
											$location=$r['location']."<br><i>(in possession)</>";
										}
										else if($rLoc['mr_status']=='Returned'){
											$location=$r['location']."<br><i>(in possession)</>";
										}
										elseif($rLoc['mr_status']=='Unreturned'){
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
											$mr_emp = $rLoc['mr_emp'];
										}
										else{
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
											$mr_emp = $rLoc['mr_emp'];
										}
										echo $location;
										?>
									</div>
								</td>
								<td><div align="left" style="font-size:10px;"><?php echo $db->getValue('employee','concat(lname," ",fname)',array('emp_id'=>$mr_emp)) ?></div></td>
								<td><div align="center"><a id="dtl<?php echo $equip_id?>" class="btn btn-mini btn-warning thickbox" onclick="showThis(this.id,'admin-equipment-registration-manage-detail.php?vdidVw=<?php echo functions::encode($equip_id);?>&rg=<?php echo functions::encode($erig_id)?>','Renewal Detail',)" style="cursor:pointer;"><i class="halflings-icon white pencil"></i></a></div></td>
							</tr>
							<?php
							endwhile;
							if($countRec==0){
								echo '<tr><td colspan="8"><div align="center">--Nothing to Report--</div></td></tr>';
							}
							if(empty($eqd)){
								echo '
							<tr>
								<td colspan="7"><div align="right"><strong>Number of Records:</strong></div></td>
								<td><div align="center"><strong>'.$countRec.'</strong></div></td>
							</tr>
							';
							}
							?>
						</tbody>
					</table>
				</div>
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
<script type="text/javascript">
	function propSel(PiEwgD){
		if(PiEwgD)
			window.location="<?php echo functions::pageName()?>?vdidVw="+PiEwgD
		else
			window.location="<?php echo functions::pageName()?>"
	}
</script>
<?php if(isset($_SESSION['notifid_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notifid_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notifid_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notifid_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notifid_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notifid_list']);} ?>
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