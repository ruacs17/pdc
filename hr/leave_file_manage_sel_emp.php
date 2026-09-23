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
$date_file='';$emp_id='';$txMon='';$txDay='';$txYear='';$reason='';$requested_by='';$lc_id='';$recommended_date=date('Y-m-d');$requested_date=date('Y-m-d');$reviewed_by='';$reviewed_date=date('Y-m-d');$checked_by='';$checked_date=date('Y-m-d');$verified_by='';$verified_date=date('Y-m-d');$approved_by='';$approved_date=date('Y-m-d');$noted_by='';$noted_date=date('Y-m-d');
$lf_id = (isset($_REQUEST['lfid']) && !empty($_REQUEST['lfid']) ) ? functions::decode($_REQUEST['lfid']) : 0;

$btnName = 'btnAdd';
if($lf_id){
	$qEdt = $db->select('leave_file','*',array('lf_id'=>$lf_id));
	$rEdt = $db->fetch_array($qEdt);
	$lc_id = $rEdt['lc_id'];
	$reason = $rEdt['reason'];
	$emp_id = $rEdt['emp_id'];
	$requested_by = $rEdt['emp_id'];
	$reviewed_by = $rEdt['reviewed_by'];
	$reviewed_date = $rEdt['reviewed_date'];
	$recommended_by = $rEdt['recommended_by'];
	$recommended_date = $rEdt['recommended_date'];
	$approved_by = $rEdt['approved_by'];
	$approved_date = $rEdt['approved_date'];
	$noted_by = $rEdt['noted_by'];
	$noted_date = $rEdt['noted_date'];
	$date_file = $rEdt['date_file'];
	$btnName = 'btnSave';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Leave</title>
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
	<style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
	<!-- end: Favicon -->
<?php
if( isset($_POST['btnSelect']) ){
	$emp_id = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : NULL;
	$date_file = ( isset($_POST['txFileDate']) && !empty($_POST['txFileDate']) ) ? trim($_POST['txFileDate']) : NULL;

	if($emp_id && $date_file){
		$emp_work_status = $db->getValue('employee','work_status',array('emp_id'=>$emp_id));
		if( $emp_work_status=='Regular' ){
			$date_regular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Regular'),'ORDER BY ews_date DESC LIMIT 1');
		}
		else if( $emp_work_status=='Contractual' ){
			$date_refer = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Contractual'),'ORDER BY ews_date DESC LIMIT 1');
		}
		else if( $emp_work_status=='Probationary' ){
			$date_refer = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Probationary'),'ORDER BY ews_date DESC LIMIT 1');
		}
		if( empty($emp_work_status)){
			functions::say('Please Set the Date Employee Hired!');
		}
		else{
			functions::sendTo('leave_file_manage.php?rq='.functions::encode($emp_id).'&fd='.functions::encode($date_file));
			die();
		}
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Leave Request</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="75%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="30%" align="right" scope="row">&nbsp;</th>
							<td width="2%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
							<td width="13%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Requested By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txRequestedBy" id="txRequestedBy" data-rel="chosen" style="width:380px; text-align:left;" required>
												<option value="">--select--</option>
												<?php $qEU = $db->query('SELECT * FROM employee WHERE (work_status="Regular" || work_status="Probationary" || work_status="Contractual") ORDER BY lname,fname');
												while($rEU = $db->fetch_array($qEU)):
												?>
												<option value="<?php echo $rEU['emp_id']?>" <?php if($requested_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
												<?php endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Date File</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<input name="txFileDate" type="text" class="span6 mytextbox" id="txFileDate" value="<?php echo $date_file; ?>" style="width: 90px;" required>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td class="tdSpace" colspan="2" align="left"><input type="submit" name="btnSelect" id="btnSelect" value="Proceed" class="btn btn-primary btn-small"></td>
						</tr>
					</table>
				</form>
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
<script src="../js/jquery.chosen.min.js"></script>
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
<script src="../js/custom.js"></script>
<script>
$(document).ready(function(){
	$('#txFileDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		showMonthAfterYear: true,
		yearRange:'2019:<?php echo date('Y')+1 ?>',
		beforeShowDay: function(date){
			var calendayDays = date.getDay();
			if(calendayDays>0){
				return [true,'',''];
			}
			else
				return [false,"",""];

		}
	});
});
</script>
<!-- end: JavaScript-->
</body>
</html>