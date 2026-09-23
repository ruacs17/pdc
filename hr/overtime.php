<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
if($delID){
	$tod = $db->getValue('attendance_overtime_detail','count(*)',array('ao_id'=>$delID));
	if($tod==0){
		$_SESSION['notif_warning']='Overtime File Removed!';
		$db->delete('attendance_overtime',array('ao_id'=>$delID));
	}
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['otProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : '';
	$_SESSION['otMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['otYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$arr = array();
$projID = ( isset($_SESSION['otProj']) && !empty($_SESSION['otProj']) ) ? $_SESSION['otProj'] : '';
$txbMon = ( isset($_SESSION['otMon']) ) ? $_SESSION['otMon'] : date('m');
$txbYear = ( isset($_SESSION['otYear']) ) ? $_SESSION['otYear'] : date('Y');

if($txbMon && $txbYear)
	$arr = array('LEFT(ao_date,7)'=>$txbYear.'-'.$txbMon);
else if($txbMon)
	$arr = array('SUBSTRING(ao_date,6,2)'=>$txbMon);
elseif($txbYear)
	$arr = array('LEFT(ao_date,4)'=>$txbYear);

if($projID)
	$arr = array_merge($arr,array('proj_id'=>$projID));
?>
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'overtime_manage.php?','Overtime Form')">Add Overtime Form</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Overtime</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0" width="90%">
						<tr>
							<td width="20%" style="padding-top: 8px;">
								<div align="center">
									<select name="bdYear" id="bdYear" style="width:90px;">
										<option value="">All Year</option>
										<?php
										$qYr = $db->select('attendance_overtime','DISTINCT LEFT(ao_date,4) as yr',array(),'ORDER BY ao_date DESC');
										while($rYr = $db->fetch_array($qYr)):
										?>
										<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
										<?php endwhile;?>
									</select>
									<select name="bdMon" id="bdMon" style="width:95px;">
										<option value="">All Month</option>
										<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
								</div>
							</td>
							<td width="50%" style="padding-top: 7px;">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:98%;font-size:12px;">
									<option value="">--All Projects / Department--</option>
									<?php 
									$qProj = $db->select('project','*',array(),'WHERE proj_id IN (SELECT DISTINCT proj_id FROM attendance_overtime) ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):?>
									<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($projID==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
									<?php endwhile;?>
								</select>
							</td>
							<td width="7%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary"></div></td>
						</tr>
					</table>
				</div>
			</form>
			<div class="table-wrapper">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="50%" scope="col">Project / Department</th>
							<th width="30%" scope="col">Reason</th>
							<th width="10%" scope="col"><div align="center">Overtime Date</div></th>
							<th width="10%" scope="col"><div align="center">Options</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$qDisp = $db->select('attendance_overtime','*',$arr,'ORDER BY ao_date DESC');
					while($rDisp = $db->fetch_array($qDisp)):
						$rID = $rDisp['ao_id'];
						$ot_detail = $db->getValue('attendance_overtime_detail','count(*)',array('ao_id'=>$rID));
					?>
						<tr id="rw<?php echo $rID?>">
							<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rDisp['proj_id']));?></td>
							<td><?php echo $rDisp['reason']?></td>
							<td><div align="center"><?php echo functions::datearr($rDisp['ao_date'])?></div></td>
							<td>
								<div align="center">
									<a id="vw<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="Overtime Detail" data-rel="tooltip" onclick="showThis(this.id,'overtime_manage_detail.php?ao=<?php echo functions::encode($rID);?>','Overtime Detail')"><i class="halflings-icon white zoom-in"></i></a>
									<a id="vwEdt<?php echo $rID?>" class="btn btn-mini btn-warning thickbox" title="Overtime Detail" data-rel="tooltip" onclick="showThis(this.id,'overtime_manage.php?ao=<?php echo functions::encode($rID);?>','Overtime Detail')"><i class="halflings-icon white pencil"></i></a>
									<a id="print<?php echo $rID?>" class="btn btn-mini btn-success thickbox" title="Print Overtime Detail" data-rel="tooltip" onclick="showThis(this.id,'overtime_print.php?ao=<?php echo functions::encode($rID);?>','Overtime Detail','1')"><i class="halflings-icon white print"></i></a>
									<?php if($ot_detail==0){?>
									<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Attendance" data-rel="tooltip" href="?delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
									<?php }?>
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
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?pid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
function delt(){if(confirm('Do you want to remove this overtime?'))return true; else return false;}
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