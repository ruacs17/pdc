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

$ao_id = (isset($_REQUEST['ao']) && !empty($_REQUEST['ao']) ) ? functions::decode($_REQUEST['ao']) : 0;
$_SESSION['notif_id']=$ao_id;
$project_id = $db->getValue('attendance_overtime','proj_id',array('ao_id'=>$ao_id));

$emp_id=''; $projected_hours='';$projected_output='';$actual_output='';$actual_start_date='';$actual_start_time='';$actual_end_date='';$actual_end_time='';$total_mins='';
$ao_date = $db->getValue('attendance_overtime','ao_date',array('ao_id'=>$ao_id));
$actual_start_date =$ao_date;
$actual_end_date = $ao_date;
if( isset($_REQUEST['itmDlt']) && !empty($_REQUEST['itmDlt']) ){
	$itmDlt = functions::decode($_REQUEST['itmDlt']);
	$itmDate = $db->getValue('attendance_overtime_detail','actual_start_date',array('aod_id'=>$itmDlt));
	$itmEmp = $db->getValue('attendance_overtime_detail','emp_id',array('aod_id'=>$itmDlt));

	$db->delete('attendance_overtime_detail',array('aod_id'=>$itmDlt));

	$ot_in = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$itmEmp,'actual_start_date'=>$itmDate),'ORDER BY actual_start_time LIMIT 1');
	$ot_in = ($ot_in) ? $ot_in : NULL;
	$ot_out = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$itmEmp,'actual_start_date'=>$itmDate),'ORDER BY actual_end_time DESC LIMIT 1');
	$ot_out = ($ot_out) ? $ot_out : NULL;
	$ot_min = $db->getValue('attendance_overtime_detail','sum(total_mins)',array('emp_id'=>$itmEmp,'actual_start_date'=>$itmDate));
	$ot_min = ($ot_min) ? $ot_min : NULL;
	$_SESSION['notif_warning']='Overtime Removed!';
	$db->update('emp_attendance_detail',array('ot_in'=>$ot_in,'ot_out'=>$ot_out,'ot_min'=>$ot_min),array('emp_id'=>$itmEmp,'eat_date'=>$itmDate));
	functions::sendTo(functions::pageName().'?ao='.functions::encode($ao_id));
	die();
}

$editTrue=0;
$itmEdt='';
$submitButtonName='btnAdd';
$act_time_from_hr='';$act_time_from_mn='';$act_time_to_hr='';$act_time_to_mn='';$actual_end_time='';
$est_time_from_hr='';$est_time_from_mn='';$est_time_to_hr='';$est_time_to_mn='';
if( isset($_REQUEST['itmEdt']) && !empty($_REQUEST['itmEdt']) ){
	$submitButtonName='btnSave';
	$itmEdt = functions::decode($_REQUEST['itmEdt']);
	$editTrue = $db->getValue('attendance_overtime_detail','count(*)',array('aod_id'=>$itmEdt));
	$qvedt = $db->select('attendance_overtime_detail','*',array('aod_id'=>$itmEdt));
	$rvedt = $db->fetch_array($qvedt);
	$emp_id = $rvedt['emp_id'];
	$projected_hours = $rvedt['projected_hours'];
	$projected_output = $rvedt['projected_output'];
	$actual_output = $rvedt['actual_output'];
	$actual_start_date = $rvedt['actual_start_date'];
	$actual_start_time = $rvedt['actual_start_time'];
	$actual_end_date = $rvedt['actual_end_date'];
	$actual_end_time = $rvedt['actual_end_time'];

	$act_time_from_hr = ( strlen($actual_start_time)==8 ) ? $actual_start_time[0].$actual_start_time[1] : '';
	$act_time_from_mn = ( strlen($actual_start_time)==8 ) ? $actual_start_time[3].$actual_start_time[4] : '';

	$act_time_to_hr = ( strlen($actual_end_time)==8 ) ? $actual_end_time[0].$actual_end_time[1] : '';
	$act_time_to_mn = ( strlen($actual_end_time)==8 ) ? $actual_end_time[3].$actual_end_time[4] : '';

	$ot_min = functions::min_diff($actual_start_time,$actual_start_date,$actual_end_time,$actual_end_date);
	#$db->update('emp_attendance_detail',array('ot_in'=>$actual_start_time,'ot_out'=>$actual_end_time,'ot_min'=>$ot_min),array('emp_id'=>$emp_id,'eat_date'=>$actual_start_date));
}
$qProjOut = $db->query('SELECT DISTINCT projected_output as "itm" FROM attendance_overtime_detail ORDER BY projected_output');
$namesProjOut='';
while($rProjOut=$db->fetch_array($qProjOut)):
	$string = preg_replace("/'/",'"',$rProjOut['itm']);
	$namesProjOut .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesProjOut .= '"--"';
$qActOut = $db->query('SELECT DISTINCT actual_output as "itm" FROM attendance_overtime_detail ORDER BY actual_output');
$namesActOut='';
while($rActOut=$db->fetch_array($qActOut)):
	$string = preg_replace("/'/",'"',$rActOut['itm']);
	$namesActOut .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesActOut .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Overtime Details</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
	<link rel="stylesheet" href="../css/timepicker.css">
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
<?php
if( isset($_POST['btnAdd']) ){ 
	$emp_id = ( isset($_POST['txEmp']) && !empty($_POST['txEmp']) ) ? trim($_POST['txEmp']) : '';
	$projected_hours = ( isset($_POST['txProjectdHour']) && !empty($_POST['txProjectdHour']) ) ? $_POST['txProjectdHour'] : '';
	$projected_output = ( isset($_POST['txProjectedOutput']) && !empty($_POST['txProjectedOutput']) ) ? trim($_POST['txProjectedOutput']) : '';

	$actual_output = ( isset($_POST['txActualOutput']) && !empty($_POST['txActualOutput']) ) ? trim($_POST['txActualOutput']) : '';
	$actual_start_date = ( isset($_POST['txActualStartDate']) && !empty($_POST['txActualStartDate']) ) ? $_POST['txActualStartDate'] : NULL;
	$actual_end_date = ( isset($_POST['txActualEndDate']) && !empty($_POST['txActualEndDate']) ) ? $_POST['txActualEndDate'] : NULL;

	$actual_start_time = (isset($_POST['timeIn']) && !empty($_POST['timeIn']) ) ? trim($_POST['timeIn']) : NULL;
	$actual_end_time = (isset($_POST['timeOut']) && !empty($_POST['timeOut']) ) ? trim($_POST['timeOut']) : NULL;

	$total_mins = ($actual_start_date && $actual_start_time && $actual_end_date && $actual_end_time ) ? functions::min_diff($actual_start_time,$actual_start_date,$actual_end_time,$actual_end_date) : NULL;
	if( $emp_id && $actual_start_time && $actual_end_time && ($total_mins>0) ){
		if( $db->getValue('emp_attendance ea, emp_attendance_detail ead','count(*)',array('ead.emp_id'=>$emp_id,'ead.eat_date'=>$actual_start_date,'attendance_ready'=>1),'AND ea.eat_id=ead.eat_id')==0 ){
			$ins = $db->insert('attendance_overtime_detail',array('ao_id'=>$ao_id,'emp_id'=>$emp_id,'projected_hours'=>$projected_hours,'projected_output'=>$projected_output,'actual_output'=>$actual_output,'actual_start_date'=>$actual_start_date,'actual_start_time'=>$actual_start_time,'actual_end_date'=>$actual_end_date,'actual_end_time'=>$actual_end_time,'total_mins'=>$total_mins));
			if($ins){
				$ot_in = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$emp_id,'actual_start_date'=>$actual_start_date),'ORDER BY actual_start_time LIMIT 1');
				$ot_in = ($ot_in) ? $ot_in : NULL;
				$ot_out = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$emp_id,'actual_start_date'=>$actual_start_date),'ORDER BY actual_end_time DESC LIMIT 1');
				$ot_out = ($ot_out) ? $ot_out : NULL;
				$ot_min = $db->getValue('attendance_overtime_detail','sum(total_mins)',array('emp_id'=>$emp_id,'actual_start_date'=>$actual_start_date));
				$ot_min = ($ot_min) ? $ot_min : NULL;
				$_SESSION['notif_success']='Overtime added!';
				$_SESSION['notif_id_list']=$ins;
				$db->update('emp_attendance_detail',array('ot_in'=>$ot_in,'ot_out'=>$ot_out,'ot_min'=>$ot_min),array('emp_id'=>$emp_id,'eat_date'=>$actual_start_date));
			}
			else{
				functions::say('Cannot add overtime data. Please fill up the form properly!');
			}
		}
		else{
			functions::say('Cannot insert overtime for confirmed attendance!');
		}
		functions::sendTo(functions::pageName().'?ao='.functions::encode($ao_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}

if( isset($_POST['btnSave']) ){
	$txitmEdt = ( isset($_POST['txitmEdt']) && !empty($_POST['txitmEdt']) ) ? functions::decode($_POST['txitmEdt']) : '0';
	$emp_id = ( isset($_POST['txEmp']) && !empty($_POST['txEmp']) ) ? trim($_POST['txEmp']) : '';
	$projected_hours = ( isset($_POST['txProjectdHour']) && !empty($_POST['txProjectdHour']) ) ? $_POST['txProjectdHour'] : '';
	$projected_output = ( isset($_POST['txProjectedOutput']) && !empty($_POST['txProjectedOutput']) ) ? trim($_POST['txProjectedOutput']) : '';

	$actual_output = ( isset($_POST['txActualOutput']) && !empty($_POST['txActualOutput']) ) ? trim($_POST['txActualOutput']) : '';
	$actual_start_date = ( isset($_POST['txActualStartDate']) && !empty($_POST['txActualStartDate']) ) ? $_POST['txActualStartDate'] : NULL;
	$actual_end_date = ( isset($_POST['txActualEndDate']) && !empty($_POST['txActualEndDate']) ) ? $_POST['txActualEndDate'] : NULL;

	$actual_start_time = (isset($_POST['timeIn']) && !empty($_POST['timeIn']) ) ? trim($_POST['timeIn']) : NULL;
	$actual_end_time = (isset($_POST['timeOut']) && !empty($_POST['timeOut']) ) ? trim($_POST['timeOut']) : NULL;

	$total_mins = ($actual_start_date && $actual_start_time && $actual_end_date && $actual_end_time ) ? functions::min_diff($actual_start_time,$actual_start_date,$actual_end_time,$actual_end_date) : NULL;

	if( $emp_id && $txitmEdt && $actual_start_time && $actual_end_time && ($total_mins>0) ){

		if( $db->getValue('emp_attendance ea, emp_attendance_detail ead','count(*)',array('ead.emp_id'=>$emp_id,'ead.eat_date'=>$actual_start_date,'attendance_ready'=>1),'AND ea.eat_id=ead.eat_id')==0 ){
			$db->update('attendance_overtime_detail',array('ao_id'=>$ao_id,'emp_id'=>$emp_id,'projected_hours'=>$projected_hours,'projected_output'=>$projected_output,'actual_output'=>$actual_output,'actual_start_date'=>$actual_start_date,'actual_start_time'=>$actual_start_time,'actual_end_date'=>$actual_end_date,'actual_end_time'=>$actual_end_time,'total_mins'=>$total_mins),array('aod_id'=>$txitmEdt));
			$ot_in = $db->getValue('attendance_overtime_detail','actual_start_time',array('emp_id'=>$emp_id,'actual_start_date'=>$actual_start_date),'ORDER BY actual_start_time LIMIT 1');
			$ot_in = ($ot_in) ? $ot_in : NULL;
			$ot_out = $db->getValue('attendance_overtime_detail','actual_end_time',array('emp_id'=>$emp_id,'actual_start_date'=>$actual_start_date),'ORDER BY actual_end_time DESC LIMIT 1');
			$ot_out = ($ot_out) ? $ot_out : NULL;
			$ot_min = $db->getValue('attendance_overtime_detail','sum(total_mins)',array('emp_id'=>$emp_id,'actual_start_date'=>$actual_start_date));
			$ot_min = ($ot_min) ? $ot_min : NULL;
			$_SESSION['notif_success']='Changes saved!';
			$_SESSION['notif_id_list']=$txitmEdt;
			$db->update('emp_attendance_detail',array('ot_in'=>$ot_in,'ot_out'=>$ot_out,'ot_min'=>$ot_min),array('emp_id'=>$emp_id,'eat_date'=>$actual_start_date));
		}
		else{
			functions::say('Cannot update overtime for confirmed attendance!');
		}
		functions::sendTo(functions::pageName().'?ao='.functions::encode($ao_id));
		die();
	}
	else
		functions::say('Please fill up the form properly!');
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Overtime Details</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="left"><br>
					<div>Project / Department:
						<strong>
							<?php 
							if($project_id){
								$qProj = $db->select('project','*',array('proj_id'=>$project_id));
								while($rProj = $db->fetch_array($qProj)):
									echo strtoupper($rProj['proj_name']);
									echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
								endwhile;
							}
							?>
						</strong>
					</div>
					<div>Date: <strong><?php echo functions::datearr($ao_date);?></strong></div><br>
					<div align="left">Reason: <strong><?php echo $db->getValue('attendance_overtime','reason',array('ao_id'=>$ao_id)); ?></strong></div><br>
					<div align="left">Attachment: <a id="vwpc" class="thickbox" title="Overtime Detail" data-rel="tooltip" style="cursor:pointer;" onclick="showThis(this.id,'overtime_manage_img.php?ao=<?php echo functions::encode($ao_id);?>','Overtime Detail')">View</a></div>
					<br><br>
				</div>
				<input type="hidden" name="txitmEdt" id="txitmEdt" value="<?php echo functions::encode($itmEdt);?>">
				<table width="100%" border="0" align="center" class="table table-striped">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="26%" scope="col"><div align="left">Name</div></th>
							<th width="21%" scope="col" colspan="2"><div align="center">Projected</div></th>
							<th width="41%" scope="col" colspan="3"><div align="center">Actual</div></th>
							<th width="8%" scope="col">&nbsp;</th>
						</tr>
						<tr style="background-color:#CCC;">
							<th width="26%">&nbsp;</th>
							<th width="10%"><div align="center">Output</div></th>
							<th width="10%"><div align="center">Hours</div></th>
							<th width="10%"><div align="center">Output</div></th>
							<th width="13%"><div align="center">Start</div></th>
							<th width="13%"><div align="center">End</div></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<div align="left">
									<select name="txEmp" id="txEmp" data-rel="chosen" style="width:350px;" required>
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($rEU['emp_id']==$emp_id)echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td align="center"><div align="left"><input type="text" name="txProjectedOutput" id="txProjectedOutput" style="width:95%;" value="<?php echo $projected_output?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesProjOut;?>]'/></div></td>
							<td>
								<div>
									<select name="txProjectdHour" id="txProjectdHour" data-rel="chosen" style="width:110px;">
										<option value="">--select--</option>
										<?php for($i=1; $i<=50; $i++):?>
										<option value="<?php echo $i?>" <?php if($i==$projected_hours)echo 'selected="selected"';?>><?php echo $i?></option>
										<?php endfor;?>
									</select>
								</div>
							</td>
							<td><div align="center"><input type="text" name="txActualOutput" id="txActualOutput" style="width:120px;" value="<?php echo $actual_output?>" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesActOut;?>]' /></div></td>
							<td>
								<div align="left">
									<input name="txActualStartDate" type="text" class="span6 mytextbox" id="txActualStartDate" value="<?php echo $actual_start_date;?>" style="width: 90px;" required>
								</div>
								<div align="left"><input id="timeIn" type="text" name="timeIn"></div>
							</td>
							<td>
								<div align="left">
									<input name="txActualEndDate" type="text" class="span6 mytextbox" id="txActualEndDate" value="<?php echo $actual_end_date;?>" style="width: 90px;" required>
								</div>
								<div align="left"><input id="timeOut" type="text" name="timeOut"></div>
							</td>
							<td>
								<div align="center">
									<input type="submit" class="btn btn-mini btn-primary" value="Save" name="<?php echo $submitButtonName;?>">
									<?php if($itmEdt){?>
									<a href="?ao=<?php echo functions::encode($ao_id)?>" class="btn btn-mini btn-warning">Cancel</a>
									<?php }?>
								</div>
							</td>
						</tr>
					</tbody>
				</table><br><br>
				<table id="tblist" width="100%" border="0" align="center" class="table table-striped <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="26%" scope="col" rowspan="2"><div align="left">Name</div></th>
							<th width="22%" scope="col" colspan="2"><div align="center">Projected</div></th>
							<th width="37%" scope="col" colspan="3"><div align="center">Actual</div></th>
							<th width="8%" scope="col" rowspan="2"><div align="left">Total Hours</div></th>
							<th width="8%" scope="col" rowspan="2">&nbsp;</th>
						</tr>
						<tr style="background-color:#CCC;">
							<th width="15%">Output</th>
							<th width="5%"><div align="center">Hours</div></th>
							<th width="15%">Output</th>
							<th width="10%"><div align="center">Start</div></th>
							<th width="10%"><div align="center">End</div></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$qOT = $db->select('attendance_overtime_detail aod, employee emp','*',array('ao_id'=>$ao_id),'AND aod.emp_id=emp.emp_id ORDER BY lname');
						while($rOT = $db->fetch_array($qOT)):
							$start_date = '';
							$end_date = '';
							$emp_id = $rOT['emp_id'];
							$rID = $rOT['aod_id'];
							if($rOT['actual_start_date'] != $rOT['actual_end_date']){
								$start_date = '<br><i>('.functions::datearr($rOT['actual_start_date']).')</i>';
								$end_date = ' <br><i>('.functions::datearr($rOT['actual_end_date']).')</i>';
							}
							else if( $rOT['actual_start_date'] != $ao_date ){
								$start_date = '<br><i>('.functions::datearr($rOT['actual_start_date']).')</i>';
								$end_date = ' <br><i>('.functions::datearr($rOT['actual_end_date']).')</i>';
							}
						?>
						<tr id="rw<?php echo $rID?>">
							<td><?php echo $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$rOT['emp_id']));?></td>
							<td><?php echo $rOT['projected_output']?></td>
							<td><div align="center"><?php echo $rOT['projected_hours']?></div></td>
							<td><?php echo $rOT['actual_output']?></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rOT['actual_start_time']).$start_date?></div></td>
							<td><div align="center"><?php echo functions::MilToTwelve($rOT['actual_end_time']).$end_date?></div></td>
							<td><div align="center"><?php echo functions::min_to_hour($rOT['total_mins']);?></div></td>
							<td>
								<div align="center">
								<?php
								if( $db->getValue('emp_attendance ea, emp_attendance_detail ead','count(*)',array('ead.emp_id'=>$emp_id,'ead.eat_date'=>$rOT['actual_start_date'],'attendance_ready'=>1),'AND ea.eat_id=ead.eat_id')==0 ){
								#if( $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_date'=>$rOT['actual_start_date']))==0 ){
								?>
									<a class="btn btn-mini btn-warning" title="Edit Detail" data-rel="tooltip" href="?itmEdt=<?php echo functions::encode($rID);?>&ao=<?php echo functions::encode($ao_id);?>"><i class="halflings-icon white pencil"></i></a>
									<a class="btn btn-mini btn-danger" title="Delete Detail" data-rel="tooltip" onClick="return delt()" href="?itmDlt=<?php echo functions::encode($rID);?>&ao=<?php echo functions::encode($ao_id);?>"><i class="halflings-icon white trash"></i></a>
								<?php }
								else{
									echo 'Locked';
								} ?>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table><br><br>
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
<script src="../js/timepicker.js"></script>
<script type="text/javascript">
$('#timeOut').timepicker({
	time:'<?php echo ($actual_end_time) ? $actual_end_time : ''; ?>'
});
$('#timeIn').timepicker({
	time:'<?php echo ($actual_start_time) ? $actual_start_time : ''; ?>'
});

$(document).ready(function(){
	$('#txActualStartDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2019:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			var dt=new Date($('#txActualStartDate').val());
			dt.setDate(dt.getDate())
			$('#txActualEndDate').datepicker('option','minDate',dt);
			$('#txActualEndDate').datepicker('option','defaultDate',$('#txActualStartDate').val());
			var dt=new Date($('#txActualEndDate').val());
			dt.setDate(dt.getDate())
			$('#txActualStartDate').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txActualEndDate').datepicker('option','minDate',dt);
			$('#txActualEndDate').datepicker('option','defaultDate',$('#txActualStartDate').val());
		}
	});
	$('#txActualEndDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2019:<?php echo date('Y')+1 ?>',
		beforeShow:function(selectdate){
			 var dt=new Date($('#txActualStartDate').val());
			dt.setDate(dt.getDate())
			$('#txActualEndDate').datepicker('option','minDate',dt);
			$('#txActualEndDate').datepicker('option','defaultDate',$('#txActualStartDate').val());
			var dt=new Date($('#txActualEndDate').val());
			dt.setDate(dt.getDate())
			$('#txActualStartDate').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txActualStartDate').datepicker('option','maxDate',dt);
		}
	});
});

function delt(){
	if(confirm('Do you want to remove this overtime?'))
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
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>