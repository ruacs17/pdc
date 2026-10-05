<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/read_excel_xlsx.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$filename='';$payroll_type='';$selMonFrom='';$selDayFrom='';$selYrFrom=date('Y');$selMonTo='';$selDayTo='';$selYrTo=date('Y');$worker_type='';$eas_name='';

$frmSummary = (isset($_REQUEST['frm']) && !empty($_REQUEST['frm']) ) ? $_REQUEST['frm'] : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;
$qEatID = $db->select('emp_attendance','*',array('eat_id'=>$eatid));
$rEatID = $db->fetch_array($qEatID);
$proj_id = $rEatID['proj_id'] ?? NULL;
$eas_name = $rEatID['note'] ?? NULL;
$worker_type = $rEatID['payroll_type'] ?? NULL;

$att_year = $rEatID['att_year'] ?? NULL;
$att_month = $rEatID['att_month'] ?? NULL;
$date_start = $rEatID['date_start'] ?? NULL;
$date_end = $rEatID['date_end'] ?? NULL;
$date_start_arr = isset($rEatID['date_start']) ? explode('-',$rEatID['date_start']) : "";
$selMonFrom = ( isset($date_start_arr[1]) ) ? $date_start_arr[1] : '';
$selDayFrom = ( isset($date_start_arr[2]) ) ? $date_start_arr[2] : '';
$selYrFrom = ( isset($date_start_arr[0]) ) ? $date_start_arr[0] : '';

$date_end_arr = isset($rEatID['date_end']) ? explode('-',$rEatID['date_end']) : "";
$selMonTo = ( isset($date_end_arr[1]) ) ? $date_end_arr[1] : '';
$selDayTo = ( isset($date_end_arr[2]) ) ? $date_end_arr[2] : '';
$selYrTo = ( isset($date_end_arr[0]) ) ? $date_end_arr[0] : '';

$attendance_detail = $db->getValue('emp_attendance_detail','count(eat_id)',array('eat_id'=>$eatid));
if($eatid==0){
	$selYrFrom=date('Y');
	$selYrTo=date('Y');
	$att_year=date('Y');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Upload</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
	if( isset($_POST['btnCreate']) ){
		$eat_id='';
		$proj_id = ( isset($_POST['selProjAssignment']) && !empty($_POST['selProjAssignment']) ) ? functions::decode($_POST['selProjAssignment']) : 0;
		$worker_type = ( isset($_POST['selPayType']) && !empty($_POST['selPayType']) ) ? $_POST['selPayType'] : '';
		$eas_name = ( isset($_POST['txNote']) && !empty($_POST['txNote']) ) ? trim($_POST['txNote']) : '';

		$date_start = ( isset($_POST['txDateFrom']) && functions::valid_date(trim($_POST['txDateFrom'])) ) ? trim($_POST['txDateFrom']) : '';
		$date_end = ( isset($_POST['txDateTo']) && functions::valid_date(trim($_POST['txDateTo'])) ) ? trim($_POST['txDateTo']) : '';
		if( empty($date_start) || empty($date_end)){
			$date_start="";$date_end="";
		}
		if($date_start && $date_end && $proj_id && $worker_type){

			//Getting the month and year of the payroll
			$pdates = functions::date_diff($date_start,$date_end);
			$arrDt=array();
			$arrYr=array();
			if($pdates){
				for($i=0;$i<=$pdates;$i++):
					$succeeding_date = functions::AddDay($date_start,$i);
					$monthName = date('m', strtotime($succeeding_date));
					$yrName = date('Y', strtotime($succeeding_date));

					if(!isset($arrYr[$yrName]))
						$arrYr[$yrName]=0;
					$arrYr[$yrName]++;

					if(!isset($arrDt[$monthName]))
						$arrDt[$monthName]=0;
					$arrDt[$monthName]++;
				endfor;
			}
			asort($arrDt);
			foreach($arrDt as $armnth => $cntMnth)
				$att_month = $armnth;

			asort($arrYr);
			foreach($arrYr as $aryr => $cntYr)
				$att_year = $aryr;
			//End of getting month and year of payroll

			$dates = functions::date_diff($date_start,$date_end,$includeDay1=1);
			if($dates >=1 ){
				if($eatid){//For update
					$db->update('emp_attendance',array('date_added'=>date('Y-m-d'),'date_start'=>$date_start,'date_end'=>$date_end,'att_year'=>$att_year,'att_month'=>$att_month,'proj_id'=>$proj_id,'payroll_type'=>$worker_type,'note'=>$eas_name),array('eat_id'=>$eatid));
					$_SESSION['notif_success']='Changes Saved!';
					functions::sendTo(functions::pageName().'?eatid='.functions::encode($eatid));
					die();
				}
				else{//For Insert
					#if( $db->getValue('emp_attendance','count(*)',array('proj_id'=>$proj_id,'payroll_type'=>$worker_type),'AND (date_start BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" OR date_end BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'")')==0 ){
						$is_project = $db->getValue('project','project',array('proj_id'=>$proj_id));
						if($is_project == 0){
							$last_payroll_no = $db->getValue('emp_attendance','payroll_count',array('proj_id'=>$proj_id,'att_year'=>$att_year),'ORDER BY payroll_count DESC LIMIT 1');
							if($last_payroll_no > 0){
								if( $db->getValue('emp_attendance','count(*)',array('proj_id'=>$proj_id,'att_year'=>$att_year,'att_month'=>$att_month,),'AND (date_start BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" OR date_end BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'")')==0 ){
									$last_payroll_no = floor($last_payroll_no + 1);
								}
								else
									$last_payroll_no += .1;
							}
							else
								$last_payroll_no = 1;//First Payroll of the project/department
							$payroll_no = $selYrFrom.'-'.$last_payroll_no;
						}
						else{//Project based payroll
							$last_payroll_no = $db->getValue('emp_attendance','payroll_count',array('proj_id'=>$proj_id),'ORDER BY payroll_count DESC LIMIT 1');
							if($last_payroll_no > 0){
								//For payroll with different date, just add the last payroll number with whole number.
								if( $db->getValue('emp_attendance','count(*)',array('proj_id'=>$proj_id),'AND (date_start BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'" OR date_end BETWEEN "'.$db->clean($date_start).'" AND "'.$db->clean($date_end).'")')==0 ){
									$last_payroll_no = floor($last_payroll_no + 1);
								}
								else //For payroll with same date, just add the last payroll number with point 1
									$last_payroll_no += .1;
							}
							else
								$last_payroll_no = 1;//First Payroll of the project/department
							$payroll_no = $last_payroll_no;
						}
						/*echo floor(11.9);
						echo $db->last_query;
						echo '<br>';
						echo $last_payroll_no;
						die();*/
						$sig_form = 'payroll';
						$prepared_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Prepared By'));
						$checked_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Checked By'));
						$received_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Received By'));
						$approved_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Approved By'));

						$prepared_by = ($prepared_by) ? $prepared_by : NULL;
						$checked_by = ($checked_by) ? $checked_by : NULL;
						$received_by = ($received_by) ? $received_by : NULL;
						$approved_by = ($approved_by) ? $approved_by : NULL;

						$eat_id = $db->insert('emp_attendance',array('date_added'=>date('Y-m-d'),'date_start'=>$date_start,'date_end'=>$date_end,'att_year'=>$att_year,'att_month'=>$att_month,'proj_id'=>$proj_id,'payroll_type'=>$worker_type,'payroll_no'=>$payroll_no,'payroll_count'=>$last_payroll_no,'note'=>$eas_name,'prepared_by'=>$prepared_by,'checked_by'=>$checked_by,'received_by'=>$received_by,'approved_by'=>$approved_by));
						if($eat_id){
							$_SESSION['notif_success']='Attendance Report Successfully Created!';
							functions::sendTo('attendance_report_add_personnel.php?eatid='.functions::encode($eat_id).'&frm='.$frmSummary);
							die();
						}
						else{
							$_SESSION['notif_warning']='Failed to create attendance record!';
						}
					#}
					#else{
					#    functions::say('Date already Exist! Please choose different dates.');
					#}
				}
			}
			else{
				$_SESSION['notif_warning']='Please fill up the form properly!';
			}
		}
		else{
			$_SESSION['notif_warning']='Please fill up the form properly.!';
		}
	}
	?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<?php if($attendance_detail){?><li><a href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>">Summary</a></li><?php }?>
				<?php if($eatid){?><li><a href="attendance_report_add_attlog_option.php?eatid=<?php echo functions::encode($eatid)?>">Upload Att. Log</a></li>
				<li><a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>">Personnel</a></li><?php }?>
				<li class="active"><a href="attendance_report_add_charge.php?eatid=<?php echo functions::encode($eatid)?>" style="opacity:.9">Charge To</a></li>
			</ul>
		</div>
		<div class="box-content">
		<?php if($frmSummary){?>
		<div align="right">
			<a id="icnReupload" href="attendance_summary_view.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info" title="Back To Attendance Summary">Back To Attendance Summary</a>&nbsp;
		</div>
		<?php } ?>
			<form class="form-horizontal" method="post" id="frmAtt">
				<div align="center" style="padding-bottom: 15px;"><h2>ATTENDANCE REPORT</h2></div>
				<div align="center">
					<table border="0" width="99%">
						<tr>
							<td width="25%"></td>
							<td align="left" width="12%">Project / Department </td>
							<td valign="middle" height="60px">
								<div align="left">
									<select name="selProjAssignment" id="selProjAssignment" data-rel="chosen" style="width:750px;font-size:12px;" required>
										<option value="">-- Select Project / Department --</option>
										<?php 
										$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
										while($rProj = $db->fetch_array($qProj)):
										$projName = $rProj['proj_name'];
										$projID = $rProj['proj_id'];
										?>
										<option value="<?php echo functions::encode($projID)?>" <?php if($proj_id==$projID)echo 'selected="selected"';?>><?php echo strtoupper($projName);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td></td>
							<td align="left">Date Start:</td>
							<td valign="middle" height="50px">
								<div align="left">
									<input type="text" style="width: 80px;" name="txDateFrom" id="txDateFrom" value="<?php echo $date_start ?>" required>
								</div>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>Date End: </td>
							<td valign="middle" height="50px">
								<input type="text" style="width: 80px;" name="txDateTo" id="txDateTo" value="<?php echo $date_end ?>" required>
							</td>
						</tr>
						<tr>
							<td></td>
							<td height="50">Worker Type </td>
							<td>
								<div align="left">
									<select name="selPayType" id="selPayType" required>
										<option value="">--Select Worker Type--</option>
										<option value="admin" <?php if($worker_type=='admin')echo 'selected="selected"';?>>Office Personnel</option>
										<option value="labor" <?php if($worker_type=='labor')echo 'selected="selected"';?>>Labor Group</option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td></td>
							<td height="50">Note </td>
							<td><div align="left"><input type="text" name="txNote" id="txNote" value="<?php echo $eas_name?>"></div></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td>
								<div align="left" style="padding-top: 40px;">
									<input type="submit" name="btnCreate" id="btnCreate" value=" SAVE " class="btn btn-primary btn-small">&nbsp;&nbsp;&nbsp;
									<?php if($eatid){?><a href="attendance_report_add_personnel.php?eatid=<?php echo functions::encode($eatid)?>" class="btn btn-info btn-small">&nbsp;Manage Personnel&nbsp;&nbsp;</a><?php }?>
								</div>
							</td>
						</tr>
					</table>
				</div>
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
$(document).ready(function(){
	$('#frmAtt').submit(function(){
		if(confirm('Do you want to save this information?'))
			return true;
		else
			return false;
	});
	$('#txDateFrom').datepicker({
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
			var dt=new Date($('#txDateFrom').val());
			dt.setDate(dt.getDate())
			$('#txDateTo').datepicker('option','minDate',dt);
			$('#txDateTo').datepicker('option','defaultDate',$('#txDateFrom').val());
			var dt=new Date($('#txDateTo').val());
			dt.setDate(dt.getDate())
			$('#txDateFrom').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txDateTo').datepicker('option','minDate',dt);
			$('#txDateTo').datepicker('option','defaultDate',$('#txDateFrom').val());
		}
	});
	$('#txDateTo').datepicker({
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
			 var dt=new Date($('#txDateFrom').val());
			dt.setDate(dt.getDate())
			$('#txDateTo').datepicker('option','minDate',dt);
			$('#txDateTo').datepicker('option','defaultDate',$('#txDateFrom').val());
			var dt=new Date($('#txDateTo').val());
			dt.setDate(dt.getDate())
			$('#txDateFrom').datepicker('option','maxDate',dt);
		},
		onSelect:function(selectdate){
			var dt=new Date(selectdate);
			dt.setDate(dt.getDate())
			$('#txDateFrom').datepicker('option','maxDate',dt);
		}
	});
});
function grpSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?eatid=<?php echo functions::encode($eatid)?>&easid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
</script>
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
</body>
</html>