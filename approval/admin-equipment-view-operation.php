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

function monthDays($month=0,$year=0){
	$list = array();
	$month = ($month) ? $month : date('m');
	$year = ($year) ? $year : date('Y');
	for($d=1; $d<=31; $d++):
		$time = mktime(12,0,0,$month,$d,$year);
		if( date('m',$time)==$month )
			$list[]=date('Y-m-d',$time);
	endfor;
	return $list;
}

$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$txItmDel = ( isset($_REQUEST['txItmDel']) && !empty($_REQUEST['txItmDel']) ) ? functions::decode($_REQUEST['txItmDel']) : '';
if($txItmDel){
    $db->delete('equip_operation',array('eop_id'=>$txItmDel));
    $_SESSION['notif_warning']='Operation Removed!';
    functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
    die();
}

$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : date('m');

if( isset($_POST['btnSearch']) ){
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : date('Y');
	$selMonth = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['eop_selMonth']=$selMonth;
	$_SESSION['eop_selYr']=$year;
	functions::sendTo(functions::pageName().'?vdidVw='.functions::encode($equip_id));
	die();
}
$selMonth = ( isset($_SESSION['eop_selMonth']) ) ? $_SESSION['eop_selMonth'] : $mn;
$year = ( isset($_SESSION['eop_selYr']) && !empty($_SESSION['act_selYr']) ) ? $_SESSION['eop_selYr'] : $yr;
$arrdte = array('equip_id'=>$equip_id);
if($selMonth && $year)
	$arrdte = array_merge(array('LEFT(eop_date_start,7)'=>$year.'-'.$selMonth),$arrdte);
elseif($year)
	$arrdte = array_merge(array('LEFT(eop_date_start,4)'=>$year),$arrdte);  

$arrOP = array();

$qOP = $db->select('equip_operation','*',array('equip_id'=>$equip_id),'ORDER BY eop_date_start');
while($rOP = $db->fetch_array($qOP)):
	$makecolumn=1;
	$date_start = $rOP['eop_date_start'];
	$date_end = $rOP['eop_date_end'];
	$dates = functions::date_diff($date_start,$date_end);
	if($dates){
		for($i=0;$i<=$dates;$i++):
			$succeeding_date = functions::AddDay($date_start,$i);
			$dayDate = date('Y-m-d', strtotime($succeeding_date));
			$arrOP[$dayDate][$rOP['eop_id']]=array('id'=>$rOP['eop_id'],'eop_date_start'=>$rOP['eop_date_start'],'eop_date_end'=>$rOP['eop_date_end'],'eop_details'=>$rOP['eop_details'],'ph'=>$rOP['ph'],'dt'=>$rOP['dt'],'sba'=>$rOP['sba'],'hr_reading'=>$rOP['hr_reading'],'remarks'=>$rOP['remarks'],'rowspan'=>($dates+1),'makecolumn'=>$makecolumn);
			$makecolumn=0;
		endfor;
	}
	else{
		$arrOP[$date_start][$rOP['eop_id']]=array('id'=>$rOP['eop_id'],'eop_date_start'=>$rOP['eop_date_start'],'eop_date_end'=>$rOP['eop_date_end'],'eop_details'=>$rOP['eop_details'],'ph'=>$rOP['ph'],'dt'=>$rOP['dt'],'sba'=>$rOP['sba'],'hr_reading'=>$rOP['hr_reading'],'remarks'=>$rOP['remarks'],'rowspan'=>0,'makecolumn'=>1);
	}
endwhile;
$txUsageLimit = $db->getValue('equipment','limit_minutes',array('equip_id'=>$equip_id));
$usageHr = ($txUsageLimit > 0) ? $txUsageLimit / 60 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Operation Detail</title>
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
	<style>.tdSpace{padding: 4px 0px 4px 0px;}</style>
	<style>
		/* Style the header */
		.header {
			background: #CCC;
		}
		/* The sticky class is added to the header with JS when it reaches its scroll position */
		.sticky {
			position: fixed;
			top: 0;
			width: 97%
		}
		.hideit{
			display:none;
		}
			.brdrNone{
		border:none;
		}
		.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<form method="post">
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white th"></i><span class="break"></span>OPERATION HISTORY</h2>
			</div>
			<div class="box-content">
				<ul class="nav tab-menu nav-tabs">
					<li><a href="admin-equipment-view-docu.php?vdidVw=<?php echo functions::encode($equip_id);?>">Document</a></li>
					<li><a href="admin-equipment-view-mr.php?vdidVw=<?php echo functions::encode($equip_id);?>">MR</a></li>
					<li><a href="admin-equipment-view-fuel.php?vdidVw=<?php echo functions::encode($equip_id);?>">Fuel & Oil</a></li>
					<li class="active"><a href="admin-equipment-view-operation.php?vdidVw=<?php echo functions::encode($equip_id);?>" style="opacity:.9">Operation</a></li>
					<li><a href="admin-equipment-view-usage.php?vdidVw=<?php echo functions::encode($equip_id);?>">Usage</a></li>
					<li><a href="admin-equipment-view-accessory.php?vdidVw=<?php echo functions::encode($equip_id);?>">Accessory</a></li>
					<li><a href="admin-equipment-view-repair.php?vdidVw=<?php echo functions::encode($equip_id);?>">Repair & Maintenance</a></li>
					<li><a href="admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip_id);?>">Detail</a></li>
				</ul>
			</div>
			<div align="right">
				<a id="adc" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'admin-equipment-view-operation-manage.php?vdidVw=<?php echo functions::encode($equip_id);?>','Add Operation Detail')">Add Operation</a>&nbsp;&nbsp;&nbsp;
				<a style="display:none;" id="mrPrint" href="admin-equipment-view-usage-print.php?vdidVw=<?php echo functions::encode($equip_id);?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;
			</div>
			<table width="100%" border="0">
				<tr>
					<td width="50%"><div align="left">&nbsp;Property: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div></td>
				</tr>
			</table>
			<div align="center"><br>
				<table align="center" width="40%" border="0">
					<tr>
						<td width="20%">
							<div align="center">
								<select name="bdYear" id="bdYear" style="width:135px;">
									<option value="">--Select Year--</option>
									<?php for($y=(date('Y') + 1);$y>=2018;$y--):?>
									<option value="<?php echo $y;?>" <?php if($y==$year)echo 'selected="selected"';?>><?php echo $y;?></option>
									<?php endfor;?>
								</select>
								<select name="bdMon" id="bdMon" style="width:95px;">
									<option value="">All Month</option>
									<option value="01" <?php if($selMonth=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($selMonth=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($selMonth=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($selMonth=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($selMonth=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($selMonth=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($selMonth=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($selMonth=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($selMonth=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($selMonth=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($selMonth=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($selMonth=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
							</div>
						</td>
						<td width="8%">
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
				</table>
			</div><br>
			<div class="table-wrapper">
				<table class="tablea table-bordereds table-hover" border="1">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="8%" height="30px">Date</th>
							<th width="15%"><div align="center">Details</div></th>
							<th width="5%"><div align="center">Program Hours</div></th>
							<th width="5%"><div align="center">DT</div></th>
							<th width="5%"><div align="center">SBA</div></th>
							<th width="4%"><div align="center">A</div></th>
							<th width="5%"><div align="center">HR Reading</div></th>
							<th width="20%"><div align="center">Remarks/Status</div></th>
							<th width="5%"><div align="center">&nbsp;</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$dayCount=0;$rowspan='';$arr_A_year=array();$arr_A_month=array();$ph_monthly=0;$ph_yearly=0;$dt_monthly=0;$dt_yearly=0;$sba_monthly=0;$sba_yearly=0;
					for($m = 1; $m <= 12; $m++):
						$time = mktime(0,0,0,$m,1,$year);
						$monthName = date('F',$time);
						$days = monthDays($m,$year);
						$arr_A_month=array();$ph_monthly=0;$dt_monthly=0;$sba_monthly=0;

						if($selMonth==$m || $selMonth == ''){
							echo '
						<tr>
								<td colspan="9" height="30"><div align="left"><strong>Month Of '.$monthName.'</strong></div></td>
						</tr>
							';
						}
						foreach($days as $day):
							$dayCount++;
							$rws=0;$makecolumn=0;$eop_id='';$a = 0;$arr_A_month[]=0;$details='';$ph=0;$sba=0;$hr_reading=0;$remarks='';$dt=0;
							if(isset($arrOP[$day])){
								foreach($arrOP[$day] as $rpo):
									$eop_id = $rpo['id'];$details = $rpo['eop_details']; $ph = $rpo['ph']; $dt = $rpo['dt'];$sba = $rpo['sba'];$hr_reading = $rpo['hr_reading'];$remarks = $rpo['remarks'];
									$rowspan=($rpo['rowspan']>1) ? 'rowspan="'.$rpo['rowspan'].'"' : '';
									#$rws=$rpo['rowspan'];
									$a = round(($ph-$dt)/$ph,2) * 100;
									$makecolumn=$rpo['makecolumn'];
								endforeach;//foreach($arrOP[$day] as $rpo)
							}//if(isset($arrOP[$day])){
							$arr_A_year[]=$a; $ph_yearly+=$ph; $dt_yearly+=$dt; $sba_yearly+=$sba;
							if($selMonth==$m || $selMonth == ''){
								$arr_A_month[]=$a;
								$ph_monthly+=$ph; $dt_monthly+=$dt; $sba_monthly+=$sba;
							?>
						<tr id="rw<?php echo $day?>">
							<td height="30"><div align="left">&nbsp;&nbsp;<?php echo date('M d, Y', strtotime($day)).' <i>- '.date('D', strtotime($day)).'</i>';?></div></td>
							<td><div align="center"><?php echo $details?></div></td>
							<td><div align="center"><?php echo ($ph) ? $ph : '';?></div></td>
							<td><div align="center"><?php echo ($dt) ? $dt : '';?></div></td>
							<td><div align="center"><?php echo ($sba) ? $sba : '';?></div></td>
							<td><div align="center"><?php echo ($a) ? $a.'%' : '';?></div></td>
							<td><div align="center"><?php echo ($hr_reading) ? $hr_reading : '';?></div></td>
							<td><div align="center" style="padding:4px;font-size:12px;"><?php echo $remarks?></div></td>
							<td>
								<div align="center">
									<?php if($eop_id){ ?>
									<a id="edit<?php echo $dayCount?>" class="btn btn-mini btn-warning thickbox" title="Update this item" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-view-operation-manage.php?txItmEdt=<?php echo functions::encode($eop_id);?>&vdidVw=<?php echo functions::encode($equip_id);?>','Operation Update')"><i class="halflings-icon white pencil"></i></a>
									<a id="del<?php echo $dayCount?>" class="btn btn-mini btn-danger" onClick="return askDel()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?txItmDel=<?php echo functions::encode($eop_id);?>&vdidVw=<?php echo functions::encode($equip_id);?>"><i class="halflings-icon white trash"></i></a>
									<?php }else{ ?>
									<a id="add<?php echo functions::encode($day)?>" class="btn btn-mini btn-info thickbox" title="Add Operation" data-rel="tooltip" onclick="showThis(this.id,'admin-equipment-view-operation-manage.php?vdidVw=<?php echo functions::encode($equip_id);?>&dte=<?php echo functions::encode($day);?>','Operation Add')"><i class="halflings-icon white plus-sign"></i></a>
									<?php } ?>
								</div>
							</td>
						</tr>
							<?php
							}//if($selMonth==$m || $selMonth == ''){
						endforeach; #end for each day

						if($selMonth==$m || $selMonth == ''){
						?>
						<tr>
							<td colspan="2" height="30"><div align="left">&nbsp;&nbsp;<strong>Month of <?php echo $monthName.' '.$year;?>&nbsp;&nbsp;&nbsp;</strong></div></td>
							<td><div align="center"><strong><?php echo $ph_monthly;?></strong></div></td>
							<td><div align="center"><strong><?php echo $dt_monthly;?></strong></div></td>
							<td><div align="center"><strong><?php echo $sba_monthly;?></strong></div></td>
							<td><div align="center"><strong><?php echo round(functions::average($arr_A_month,$includeZero=TRUE),2).'%';?></strong></div></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td colspan="2" height="30"><div align="left">&nbsp;&nbsp;<strong>Cumulative report until  <?php echo $monthName.' '.$year;?>&nbsp;&nbsp;&nbsp;</strong></div></td>
							<td><div align="center"><strong><?php echo $ph_yearly;?></strong></div></td>
							<td><div align="center"><strong><?php echo $dt_yearly;?></strong></div></td>
							<td><div align="center"><strong><?php echo $sba_yearly;?></strong></div></td>
							<td><div align="center"><strong><?php echo round(functions::average($arr_A_year,$includeZero=TRUE),2).'%';?></strong></div></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td colspan="9"><hr width="100%" style="color:#FF0000"></td>
						</tr>
						<?php
						}
						endfor; #end for each month
						?>
						<tr>
							<td colspan="2" height="30"><div align="left">&nbsp;&nbsp;<strong>End of Year <?php echo $year?>&nbsp;&nbsp;&nbsp;</strong></div></td>
							<td><div align="center"><strong><?php echo $ph_yearly;?></strong></div></td>
							<td><div align="center"><strong><?php echo $dt_yearly;?></strong></div></td>
							<td><div align="center"><strong><?php echo $sba_yearly;?></strong></div></td>
							<td><div align="center"><strong><?php echo round(functions::average($arr_A_year,$includeZero=TRUE),2).'%';?></strong></div></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div><!--/span-->
	</div><!--/row-->
</form>
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
function askDel(){
	if(confirm('Do you want to delete this operation?'))
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
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>