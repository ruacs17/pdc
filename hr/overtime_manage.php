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
$ao_date='';$proj_id='';$txMon='';$txDay='';$txYear='';$reason='';$requested_by='';$requested_date=date('Y-m-d');$reviewed_by='';$reviewed_date=date('Y-m-d');$checked_by='';$checked_date=date('Y-m-d');$verified_by='';$verified_date=date('Y-m-d');$approved_by='';$approved_date=date('Y-m-d');$noted_by='';$noted_date=date('Y-m-d');
$ao_id = (isset($_REQUEST['ao']) && !empty($_REQUEST['ao']) ) ? functions::decode($_REQUEST['ao']) : "";
if($ao_id)
	$_SESSION['notif_id']=$ao_id;
$btnName = 'btnAdd';
if($ao_id){
	$qEdt = $db->select('attendance_overtime','*',array('ao_id'=>$ao_id));
	$rEdt = $db->fetch_array($qEdt);
	$proj_id = $rEdt['proj_id'];
	$reason = $rEdt['reason'];
	$requested_by = $rEdt['requested_by'];
	$requested_date = $rEdt['requested_date'];
	$reviewed_by = $rEdt['reviewed_by'];
	$reviewed_date = $rEdt['reviewed_date'];
	$checked_by = $rEdt['checked_by'];
	$checked_date = $rEdt['checked_date'];
	$verified_by = $rEdt['verified_by'];
	$verified_date = $rEdt['verified_date'];
	$approved_by = $rEdt['approved_by'];
	$approved_date = $rEdt['approved_date'];
	$noted_by = $rEdt['noted_by'];
	$noted_date = $rEdt['noted_date'];
	$ao_date = $rEdt['ao_date'];
	$btnName = 'btnSave';
}
if(empty($ao_id)){
	$sig_form = 'overtime';
	$reviewed_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Reviewed By'));
	$checked_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Checked By'));
	$verified_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Verified By'));
	$approved_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Approved By'));
	$noted_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Noted By'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Attendance Overtime</title>
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
if( isset($_POST['btnAdd']) ){
	$arrField = array();
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : NULL;

	$reason = ( isset($_POST['txReason']) && !empty($_POST['txReason']) ) ? trim($_POST['txReason']) : '';
	$requested_by = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : NULL;
	$requested_date = ( isset($_POST['txRequestedDate']) && !empty($_POST['txRequestedDate']) ) ? $_POST['txRequestedDate'] : NULL;
	$reviewed_by = ( isset($_POST['txReviewedBy']) && !empty($_POST['txReviewedBy']) ) ? $_POST['txReviewedBy'] : NULL;
	$reviewed_date = ( isset($_POST['txReviewedDate']) && !empty($_POST['txReviewedDate']) ) ? $_POST['txReviewedDate'] : NULL;
	$checked_by = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? $_POST['txCheckedBy'] : NULL;
	$checked_date = ( isset($_POST['txCheckedDate']) && !empty($_POST['txCheckedDate']) ) ? $_POST['txCheckedDate'] : NULL;
	$verified_by = ( isset($_POST['txVerifiedBy']) && !empty($_POST['txVerifiedBy']) ) ? $_POST['txVerifiedBy'] : NULL;
	$verified_date = ( isset($_POST['txVerifiedDate']) && !empty($_POST['txVerifiedDate']) ) ? $_POST['txVerifiedDate'] : NULL;
	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : NULL;
	$approved_date = ( isset($_POST['txApprovedDate']) && !empty($_POST['txApprovedDate']) ) ? $_POST['txApprovedDate'] : NULL;
	$noted_by = ( isset($_POST['txNotedBy']) && !empty($_POST['txNotedBy']) ) ? $_POST['txNotedBy'] : NULL;
	$noted_date = ( isset($_POST['txNotedDate']) && !empty($_POST['txNotedDate']) ) ? $_POST['txNotedDate'] : NULL;
	$ao_date = ( isset($_POST['txOTDate']) && !empty($_POST['txOTDate']) ) ? trim($_POST['txOTDate']) : NULL;

	$arrField = array('proj_id'=>$proj_id,'reason'=>$reason,'ao_date'=>$ao_date,'requested_by'=>$requested_by,'requested_date'=>$requested_date,'reviewed_by'=>$reviewed_by,'reviewed_date'=>$reviewed_date,'reviewed_date'=>$reviewed_date,'checked_by'=>$checked_by,'checked_date'=>$checked_date,'verified_by'=>$verified_by,'verified_date'=>$verified_date,'approved_by'=>$approved_by,'approved_date'=>$approved_date,'noted_by'=>$noted_by,'noted_date'=>$noted_date);

	if( $proj_id && $reason && $ao_date){
		$q = $db->insertPrint('attendance_overtime',$arrField);
		$qInsert = $db->query($q);
		$insertID = $db->insert_id();
		if($insertID){
			$_SESSION['notif_success']='Overtime Detail Created!';
			functions::sendTo('overtime_manage_detail.php?ao='.functions::encode($insertID));
			die();
		}
	}
	else
		functions::say('Please fill up the form properly!');
}
if( isset($_POST['btnSave']) ){
	$arrField = array();
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : NULL;
	$reason = ( isset($_POST['txReason']) && !empty($_POST['txReason']) ) ? trim($_POST['txReason']) : '';
	$requested_by = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : NULL;
	$requested_date = ( isset($_POST['txRequestedDate']) && !empty($_POST['txRequestedDate']) ) ? $_POST['txRequestedDate'] : NULL;
	$reviewed_by = ( isset($_POST['txReviewedBy']) && !empty($_POST['txReviewedBy']) ) ? $_POST['txReviewedBy'] : NULL;
	$reviewed_date = ( isset($_POST['txReviewedDate']) && !empty($_POST['txReviewedDate']) ) ? $_POST['txReviewedDate'] : NULL;
	$checked_by = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? $_POST['txCheckedBy'] : NULL;
	$checked_date = ( isset($_POST['txCheckedDate']) && !empty($_POST['txCheckedDate']) ) ? $_POST['txCheckedDate'] : NULL;
	$verified_by = ( isset($_POST['txVerifiedBy']) && !empty($_POST['txVerifiedBy']) ) ? $_POST['txVerifiedBy'] : NULL;
	$verified_date = ( isset($_POST['txVerifiedDate']) && !empty($_POST['txVerifiedDate']) ) ? $_POST['txVerifiedDate'] : NULL;
	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : NULL;
	$approved_date = ( isset($_POST['txApprovedDate']) && !empty($_POST['txApprovedDate']) ) ? $_POST['txApprovedDate'] : NULL;
	$noted_by = ( isset($_POST['txNotedBy']) && !empty($_POST['txNotedBy']) ) ? $_POST['txNotedBy'] : NULL;
	$noted_date = ( isset($_POST['txNotedDate']) && !empty($_POST['txNotedDate']) ) ? $_POST['txNotedDate'] : NULL;
    
	$ao_date = ( isset($_POST['txOTDate']) && !empty($_POST['txOTDate']) ) ? trim($_POST['txOTDate']) : NULL;
	$arrField = array('proj_id'=>$proj_id,'reason'=>$reason,'ao_date'=>$ao_date,'requested_by'=>$requested_by,'requested_date'=>$requested_date,'reviewed_by'=>$reviewed_by,'reviewed_date'=>$reviewed_date,'reviewed_date'=>$reviewed_date,'checked_by'=>$checked_by,'checked_date'=>$checked_date,'verified_by'=>$verified_by,'verified_date'=>$verified_date,'approved_by'=>$approved_by,'approved_date'=>$approved_date,'noted_by'=>$noted_by,'noted_date'=>$noted_date);

	if( $proj_id && $reason && $ao_id && $ao_date ){
		$db->update('attendance_overtime',$arrField,array('ao_id'=>$ao_id));
		$_SESSION['notif_success']='Changes saved!';
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
			<div align="center">
				<form method="post" onSubmit="return ask()">
					<table width="70%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="35%" align="right" scope="row">&nbsp;</th>
							<td width="2%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
							<td width="13%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Project / Department</th>
							<td>&nbsp;</td>
							<td style="padding: 25px 0px 4px 0px;">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:700px;" onchange="document.getElementById('rdoCharge1').checked=true;document.getElementById('txPayee').disabled=true;" required>
									<option value="">--select--</option>
									<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):
									?>
									<option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo ($rProj['proj_name']);?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Reason / Objectives</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txReason" id="txReason" style="width:70%;" value="<?php echo $reason?>" /></td>
						</tr>
						<tr>
							<th align="right" scope="row">Overtime Date</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<input name="txOTDate" type="text" class="span6 mytextbox" id="txOTDate" value="<?php echo $ao_date; ?>" style="width: 90px;" required>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Requested By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txRequestedBy" id="txRequestedBy" data-rel="chosen" style="width:300px; text-align:left;" required>
												<option value="">--select--</option>
												<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
												while($rEU = $db->fetch_array($qEU)):
												?>
												<option value="<?php echo $rEU['emp_id']?>" <?php if($requested_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
												<?php endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
										<td align="left">
											<a href="javascript:NewCssCal('txRequestedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
											<input name="txRequestedDate" type="text" class="span6 mytextbox" id="txRequestedDate" value="<?php echo $requested_date;?>" style="width: 90px;" readonly>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Reviewed By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txReviewedBy" id="txReviewedBy" data-rel="chosen" style="width:300px; text-align:left;">
												<option value="">--select--</option>
												<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
												while($rEU = $db->fetch_array($qEU)):
												?>
												<option value="<?php echo $rEU['emp_id']?>" <?php if($reviewed_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
												<?php endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
										<td align="left">
											<a href="javascript:NewCssCal('txReviewedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
											<input name="txReviewedDate" type="text" class="span6 mytextbox" id="txReviewedDate" value="<?php echo $reviewed_date;?>" style="width: 90px;" readonly>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Checked By</th>
								<td>&nbsp;</td>
								<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txCheckedBy" id="txCheckedBy" data-rel="chosen" style="width:300px; text-align:left;">
												<option value="">--select--</option>
												<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
												while($rEU = $db->fetch_array($qEU)):
												?>
												<option value="<?php echo $rEU['emp_id']?>" <?php if($checked_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
												<?php endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
										<td align="left">
											<a href="javascript:NewCssCal('txCheckedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
											<input name="txCheckedDate" type="text" class="span6 mytextbox" id="txCheckedDate" value="<?php echo $checked_date;?>" style="width: 90px;" readonly>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Verified By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txVerifiedBy" id="txVerifiedBy" data-rel="chosen" style="width:300px; text-align:left;">
												<option value="">--select--</option>
												<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
												while($rEU = $db->fetch_array($qEU)):
												?>
												<option value="<?php echo $rEU['emp_id']?>" <?php if($verified_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
												<?php endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
										<td align="left">
											<a href="javascript:NewCssCal('txVerifiedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
											<input name="txVerifiedDate" type="text" class="span6 mytextbox" id="txVerifiedDate" value="<?php echo $verified_date;?>" style="width: 90px;" readonly>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Approved By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:300px; text-align:left;">
												<option value="">--select--</option>
												<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
												while($rEU = $db->fetch_array($qEU)):
												?>
												<option value="<?php echo $rEU['emp_id']?>" <?php if($approved_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
												<?php endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
										<td align="left">
											<a href="javascript:NewCssCal('txApprovedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
											<input name="txApprovedDate" type="text" class="span6 mytextbox" id="txApprovedDate" value="<?php echo $approved_date;?>" style="width: 90px;" readonly>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Noted By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txNotedBy" id="txNotedBy" data-rel="chosen" style="width:300px; text-align:left;">
												<option value="">--select--</option>
												<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
												while($rEU = $db->fetch_array($qEU)):
												?>
												<option value="<?php echo $rEU['emp_id']?>" <?php if($noted_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
												<?php endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
										<td align="left">
											<a href="javascript:NewCssCal('txNotedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
											<input name="txNotedDate" type="text" class="span6 mytextbox" id="txNotedDate" value="<?php echo $noted_date;?>" style="width: 90px;" readonly>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td class="tdSpace" colspan="2" align="left"><input type="submit" name="<?php echo $btnName?>" id="<?php echo $btnName?>" value="Save Overtime Form" class="btn btn-primary btn-small"></td>
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
<script>
$(document).ready(function(){
	$('#txOTDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2015:<?php echo date('Y')+1 ?>'
	});
});
</script>
<script>
function ask(){
	if(confirm('Do you want to submit this information?'))
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
<!-- end: JavaScript-->
</body>
</html>