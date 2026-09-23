<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$unconfirmed=( isset($_REQUEST['unc']) && !empty($_REQUEST['unc']) ) ? $_REQUEST['unc'] : '';
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arr = array();
if( isset($_POST['btnSearch']) ){
	$_SESSION['atProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : '';
	$_SESSION['atType'] = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? $_POST['selType'] : '';
	$_SESSION['ptStat'] = ( isset($_POST['selStat']) && !empty($_POST['selStat']) ) ? $_POST['selStat'] : '';
	$_SESSION['atMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['atYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo(functions::pageName());
	die();
}
if($unconfirmed){
	$_SESSION['atProj'] = '';
	$_SESSION['atType'] = '';
	$_SESSION['ptStat'] = '2';
	$_SESSION['atMon'] = '';
	$_SESSION['atYear'] = '';
	functions::sendTo(functions::pageName());
	die();
}
$projID = ( isset($_SESSION['atProj']) && !empty($_SESSION['atProj']) ) ? $_SESSION['atProj'] : '';
$at_type = ( isset($_SESSION['atType']) && !empty($_SESSION['atType']) ) ? $_SESSION['atType'] : '';
$at_stat = ( isset($_SESSION['ptStat']) && !empty($_SESSION['ptStat']) ) ? $_SESSION['ptStat'] : '';
$txbMon = ( isset($_SESSION['atMon']) ) ? $_SESSION['atMon'] : date('m');
$txbYear = ( isset($_SESSION['atYear']) ) ? $_SESSION['atYear'] : date('Y');

if($txbMon && $txbYear)
	$arr = array('LEFT(date_start,7)'=>$txbYear.'-'.$txbMon);
else if($txbMon)
	$arr = array('SUBSTRING(date_start,6,2)'=>$txbMon);
elseif($txbYear)
	$arr = array('LEFT(date_start,4)'=>$txbYear);
if($at_stat){
	$arr = ($at_stat==1) ? array_merge($arr,array('confirmed'=>'1')) : array_merge($arr,array('confirmed'=>'0'));
}
if($at_type)
	$arr = array_merge($arr,array('payroll_type'=>$at_type));
if($projID)
$arr = array_merge($arr,array('proj_id'=>$projID));
$arr = array_merge($arr,array('attendance_ready'=>1));
?>
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
<!-- body content: start here-->
<div align="right" style="display:none;"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'attendance_upload.php?','Attendance Upload')">Upload Attendance Log</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="adcd" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'attendance_report_add.php?','Create Attendance Report')">Create Attendance Report</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Payroll</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0" width="90%">
						<tr>
							<td width="20%" style="padding-top: 8px;">
								<div align="center">
									<select name="bdYear" id="bdYear" style="width:90px;font-size:12px;">
										<option value="">All Year</option>
										<?php
										$qYr = $db->select('emp_attendance','DISTINCT LEFT(date_start,4) as yr',array(),'ORDER BY date_start DESC');
										while($rYr = $db->fetch_array($qYr)):
										?>
										<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
										<?php endwhile;?>
									</select>
									<select name="bdMon" id="bdMon" style="width:95px;font-size:12px;">
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
									$qProj = $db->select('project','*',array(),'WHERE proj_id IN (SELECT DISTINCT proj_id FROM emp_attendance) ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):?>
									<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($projID==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
									<?php endwhile;?>
								</select>
							</td>
							<td width="8%" style="padding-top: 8px;">
								<select name="selType" id="selType" style="width:95px;font-size:12px;">
									<option value="">--All Type--</option>
									<option value="admin" <?php if($at_type=='admin')echo 'selected="selected"'; ?>>ADMIN</option>
									<option value="labor" <?php if($at_type=='labor')echo 'selected="selected"'; ?>>LABOR</option>
								</select>
							</td>
							<td width="8%" style="padding-top: 8px;">
								<select name="selStat" id="selStat" style="width:112px;font-size:12px;">
									<option value="">--Status--</option>
									<option value="1" <?php if($at_stat=='1')echo 'selected="selected"'; ?>>Confirmed</option>
									<option value="2" <?php if($at_stat=='2')echo 'selected="selected"'; ?>>Unconfirmed</option>
								</select>
							</td>
							<td width="7%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></div></td>
						</tr>
					</table>
				</div>
			</form>
			<div class="table-wrapper">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="12%" scope="col">Date Range</th>
							<th width="25%" scope="col">Project / Department</th>
							<th width="5%" scope="col">Payroll No.</th>
							<th width="10%" scope="col">Description</th>
							<th width="5%" scope="col">Status</th>
							<th width="5%" scope="col"><div align="center">&nbsp;</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$count=0;
					$qDisp = $db->select('emp_attendance','*',$arr,'ORDER BY date_start DESC,proj_id,payroll_no');
					while($rDisp = $db->fetch_array($qDisp)):
						$count++;
						$rID = $rDisp['eat_id'];
						$dateRangeDisp='';
						$date_from=$rDisp['date_start'];
						$date_to=$rDisp['date_end'];
						$days = functions::date_diff($date_from,$date_to,$includeDay1=1);
						$dtfArr = explode('-',$date_from);
						$monthFrom = isset($dtfArr[1]) ? $dtfArr[1] :'00';
						$dayFrom =  isset($dtfArr[2]) ? $dtfArr[2] :'00';
						$yrFrom =  isset($dtfArr[0]) ? $dtfArr[0] :'0000';
						$dttArr = explode('-',$date_to);
						$monthTo = isset($dttArr[1]) ? $dttArr[1] :'00';
						$dayTo =  isset($dttArr[2]) ? $dttArr[2] :'00';
						$yrTo =  isset($dttArr[0]) ? $dttArr[0] :'0000';
						if($yrFrom==$yrTo){
							if($monthFrom==$monthTo){
								$dateMonthName = date('M', strtotime($yrFrom.'-'.$monthFrom.'-'.'01'));
							$dateRangeDisp = $dateMonthName.' '.$dayFrom.' - '.$dayTo.', '.$yrFrom;
						}
						else
							$dateRangeDisp = functions::datearr($date_from).' - '.functions::datearr($date_to);
						}
						else
						$dateRangeDisp = functions::datearr($date_from).' - '.functions::datearr($date_to);

					?>
					<tr id="rw<?php echo $rID?>">
						<td><?php echo $dateRangeDisp.' (<i>'.$days.' days</i>)';?></td>
						<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rDisp['proj_id'])); ?></td>
						<td><?php echo $rDisp['payroll_no'];?></td>
						<td><?php echo $rDisp['note'];?></td>
						<td><?php echo ($rDisp['confirmed']==1) ? 'Confirmed' : 'Unconfirm';?></td>
						<td>
							<div align="center">
								<a id="vw<?php echo $rID?>" class="btn btn-mini <?php echo ($rDisp['confirmed']==1) ? 'btn-success' : 'btn-info';?> thickbox" title="Payroll Detail" data-rel="tooltip" onclick="showThis(this.id,'payroll_view.php?eatid=<?php echo functions::encode($rID);?>','Payroll Detail')"><i class="halflings-icon white zoom-in"></i></a>
							</div>
						</td>
					</tr>
					<?php endwhile;
					if($count==0){
						echo '<tr><td colspan="6"><div align="center">---Nothing to Report---</div></td></tr>';
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}
</script>
<?php require_once('templ_down.php');?>
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