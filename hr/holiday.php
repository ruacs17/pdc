<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;$selYr=date('Y');

$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
if($delID){
	$db->delete('holiday',array('hol_id'=>$delID));
	/*echo $db->getValuePrint('emp_attendance','count(*)',array('eat_id'=>$delID,'attendance_ready'=>0));
	if( $db->getValue('emp_attendance','count(*)',array('eat_id'=>$delID,'attendance_ready'=>0)) ){
	$db->update('emp_attendance',array('eatd_id'=>NULL),array('eat_id'=>$delID,'attendance_ready'=>0));
	$db->delete('emp_attendance',array('eat_id'=>$delID,'attendance_ready'=>0));
	}
	else{
	functions::say('Confirmed attendance cannot be removed!');
	}*/
	$_SESSION['notif_warning']='Holiday Removed!';
	functions::sendTo(functions::pageName());
	die();
}
$arr = array();
$selYr = ( isset($_REQUEST['selYr']) && !empty($_REQUEST['selYr']) ) ? $_REQUEST['selYr'] : date('Y');
if($selYr)
	$arr = array('hol_year'=>$selYr);
?>
<style type="text/css">
.table-wrapper th{
	background: #DDD;
	position: sticky;
	top: 0;
}
</style>
<!-- body content: start here-->
<div align="right"><a id="adcd" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'holiday_manage.php?','Create Attendance Report')">Add Holiday</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Holiday</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0" width="40%">
						<tr>
							<td width="7%"><div align="center">Select Year</div></td>
							<td width="20%" style="padding-top: 8px;">
								<div align="left">
									<select name="selYr" id="selYr" data-rel="chosen" style="width:90px;" onChange="yrSel(this.value)">
										<option value="all">All Year</option>
										<?php for($y=date('Y');$y>=2018;$y--):?>
										<option value="<?php echo $y;?>" <?php if($selYr==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
										<?php endfor;?>
									</select>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</form>
			<div class="table-wrapper">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="15%" scope="col">Date</th>
							<th width="20%" scope="col">Holiday Name</th>
							<th width="15%" scope="col">Type</th>
							<th width="5%" scope="col">Additional Pay</th>
							<th width="5%" scope="col"><div align="center">&nbsp;</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$qDisp = $db->select('holiday','*',$arr,'OR hol_year="all" ORDER BY hol_month,hol_day');
					while($rDisp = $db->fetch_array($qDisp)):
						$rID = $rDisp['hol_id'];
						$monthName = date('F', strtotime(date('Y-').$rDisp['hol_month'].'-01'));
						$yearName = ($rDisp['hol_year']=='all') ? ' (Yearly) ' : ', '.$rDisp['hol_year'];
					?>
						<tr id="rw<?php echo $rID?>">
							<td><?php echo $monthName.' '.$rDisp['hol_day'].$yearName;?></td>
							<td><?php echo $rDisp['hol_name'];?></td>
							<td><?php echo $rDisp['hol_type'];?></td>
							<td><div align="center"><?php echo $rDisp['wage_percent'];?>%</div></td>
							<td>
								<div align="center">
									<a id="vw<?php echo $rID?>" class="btn btn-mini btn-warning thickbox" title="Attendance Detail" data-rel="tooltip" onclick="showThis(this.id,'holiday_manage.php?holid=<?php echo functions::encode($rID);?>','Attendance Detail')"><i class="halflings-icon white pencil"></i></a>
									<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Attendance" data-rel="tooltip" href="?delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
					<?php endwhile;?>
					</tbody>
				</table>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function yrSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?selYr="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
function delt(){if(confirm('Do you want to remove this holiday?'))return true; else return false;}
</script>
<?php require_once('templ_down.php');?>
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
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>
