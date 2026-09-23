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
$emp_id='';$txMon='';$txDay='';$txYear='';$reason='';$requested_by='';$date_file='';$lc_id='';$recommended_date=date('Y-m-d');$requested_date=date('Y-m-d');$reviewed_by='';$reviewed_date=date('Y-m-d');$checked_by='';$checked_date=date('Y-m-d');$recommended_by='';$recommended_date=date('Y-m-d');$approved_by='';$approved_date=date('Y-m-d');$noted_by='';$noted_date=date('Y-m-d');
$lf_id = (isset($_REQUEST['lfid']) && !empty($_REQUEST['lfid']) ) ? functions::decode($_REQUEST['lfid']) : 0;
$rq_empid = (isset($_REQUEST['rq']) && !empty($_REQUEST['rq']) ) ? functions::decode($_REQUEST['rq']) : '';
$dateFile = (isset($_REQUEST['fd']) && !empty($_REQUEST['fd']) ) ? functions::decode($_REQUEST['fd']) : '';
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
	$date_fileArr = explode("-",$date_file);
	if(count($date_fileArr)==3){
		$txMon=$date_fileArr[1];
		$txDay=$date_fileArr[2];
		$txYear=$date_fileArr[0];
	}
	$btnName = 'btnSave';
}
$emp_id = ($rq_empid) ? $rq_empid : $emp_id;
$dateFile = !empty($dateFile) ? $dateFile : $date_file;
function leave_avail_term($emp_id,$dateFile){
	global $db;
	$term='';
	$allow_with_pay='';
	$arrResult = array();
	$not_applicable=0;
	$date_leave_start='';
	$date_leave_end='';


	$date_refer='';$date_regular='';
	$emp_work_stat = $db->getValue('employee','work_status',array('emp_id'=>$emp_id));
	if( $emp_work_stat=='Regular' ){
		$date_regular = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Regular'),'ORDER BY ews_date DESC LIMIT 1');
	}
	else if( $emp_work_stat=='Contractual' ){
		$date_refer = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Contractual'),'ORDER BY ews_date DESC LIMIT 1');
	}
	else if( $emp_work_stat=='Probationary' ){
		$date_refer = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id,'ews_stat'=>'Probationary'),'ORDER BY ews_date DESC LIMIT 1');
	}
	else
		$not_applicable=1;

	if($not_applicable==0){
		$allow_with_pay=0;
		if($date_regular){
			$years_regular = functions::year_diff($date_regular,date('Y-m-d'));
			if($years_regular)
				$allow_with_pay=1;
		}
		$date_reference = ($date_regular) ? $date_regular : $date_refer;
		$month_date_hired_reference = substr($date_reference, 4, 6);//-12-15

		//$dateFile_month_date =  substr($dateFile, 5, 5);
		/*$date_file_year = substr($dateFile, 0, 4);
		if( functions::year_diff(($date_file_year-1).$month_date_hired_reference,$dateFile)==0 ){//less than a year
			$term = ($date_file_year-1).'-'.$date_file_year;
			$date_leave_start = ($date_file_year-1).$month_date_hired_reference;
			$date_leave_end = ($date_file_year).$month_date_hired_reference;
		}
		else{//If is year or greater
			$term = $date_file_year.'-'.($date_file_year+1);
			$date_leave_start = ($date_file_year).$month_date_hired_reference;
			$date_leave_end = ($date_file_year+1).$month_date_hired_reference;
		}
		$arr_dle = explode('-', $date_leave_end);
		$yy = isset($arr_dle[0]) ? $arr_dle[0] : 0;
		$mm = isset($arr_dle[1]) ? $arr_dle[1] : 0;
		$dd = isset($arr_dle[2]) ? $arr_dle[2] : 0;
		$date_leave_end = date('Y-m-d',mktime(0,0,0,$mm,$dd - 1,$yy));*/
	}
	return array('allow_with_pay'=>$allow_with_pay);
	//return array('term'=>$term,'allow_with_pay'=>$allow_with_pay,'term_start'=>$date_leave_start,'term_end'=>$date_leave_end);
}

$leave_avail = leave_avail_term($emp_id,$dateFile);
#print_r($leave_avail);
$allow_with_pay = isset($leave_avail['allow_with_pay']) ? $leave_avail['allow_with_pay'] : 0;
#Default Signatories

if(empty($lf_id)){
	$sig_form = 'leave';
	$recommended_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Recommending Approval'));
	$reviewed_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Reviewed By'));
	$approved_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Approved By'));
	$noted_by = $db->getValue('signatories','emp_id',array('sig_form'=>$sig_form,'sig_position'=>'Noted By'));
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
if( isset($_POST['btnAdd']) ){
	$arrField = array();
	$lc_id = ( isset($_POST['txLeaveType']) && !empty($_POST['txLeaveType']) ) ? $_POST['txLeaveType'] : NULL;
	$date_file = ( isset($_POST['txDF']) && !empty($_POST['txDF']) ) ? functions::decode($_POST['txDF']) : NULL;
	$reason = ( isset($_POST['txReason']) && !empty($_POST['txReason']) ) ? trim($_POST['txReason']) : '';
	$emp_id = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : NULL;
	$recommended_by = ( isset($_POST['txRecommendedBy']) && !empty($_POST['txRecommendedBy']) ) ? $_POST['txRecommendedBy'] : NULL;
	$recommended_date = ( isset($_POST['txRecommendedDate']) && !empty($_POST['txRecommendedDate']) ) ? $_POST['txRecommendedDate'] : NULL;    
	$reviewed_by = ( isset($_POST['txReviewedBy']) && !empty($_POST['txReviewedBy']) ) ? $_POST['txReviewedBy'] : NULL;
	$reviewed_date = ( isset($_POST['txReviewedDate']) && !empty($_POST['txReviewedDate']) ) ? $_POST['txReviewedDate'] : NULL;
	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : NULL;
	$approved_date = ( isset($_POST['txApprovedDate']) && !empty($_POST['txApprovedDate']) ) ? $_POST['txApprovedDate'] : NULL;
	$noted_by = ( isset($_POST['txNotedBy']) && !empty($_POST['txNotedBy']) ) ? $_POST['txNotedBy'] : NULL;
	$noted_date = ( isset($_POST['txNotedDate']) && !empty($_POST['txNotedDate']) ) ? $_POST['txNotedDate'] : NULL;
	
	/*$lv_detail = leave_avail_term($emp_id,$dateFile);
	$lf_term = isset($lv_detail['term']) ? $lv_detail['term'] : '';
	$lf_term_start = isset($lv_detail['term_start']) ? $lv_detail['term_start'] : '';
	$lf_term_end = isset($lv_detail['term_end']) ? $lv_detail['term_end'] : '';*/
	
	#$arrField = array('lc_id'=>$lc_id,'reason'=>$reason,'date_file'=>$date_file,'emp_id'=>$emp_id,'approved_by'=>$approved_by,'approved_date'=>$approved_date,'noted_by'=>$noted_by,'noted_date'=>$noted_date,'lf_term'=>$lf_term,'lf_term_start'=>$lf_term_start,'lf_term_end'=>$lf_term_end);
	$arrField = array('lc_id'=>$lc_id,'reason'=>$reason,'date_file'=>$date_file,'emp_id'=>$emp_id,'approved_by'=>$approved_by,'approved_date'=>$approved_date,'noted_by'=>$noted_by,'noted_date'=>$noted_date);

	if( $lc_id && $emp_id && $date_file){
		$q = $db->insertPrint('leave_file',$arrField);
		$qInsert = $db->query($q);
		$insertID = $db->insert_id();
		if($insertID){
			functions::sendTo('leave_file_manage_detail.php?lf='.functions::encode($insertID));
			die();
		}
		else
			functions::say('Please fill up the form properly!');
	}
	else
		functions::say('Please fill up the form properly!');
}
if( isset($_POST['btnSave']) ){
	$arrField = array();
	$lc_id = ( isset($_POST['txLeaveType']) && !empty($_POST['txLeaveType']) ) ? $_POST['txLeaveType'] : NULL;
	$date_file = ( isset($_POST['txDF']) && !empty($_POST['txDF']) ) ? functions::decode($_POST['txDF']) : NULL;
	$reason = ( isset($_POST['txReason']) && !empty($_POST['txReason']) ) ? trim($_POST['txReason']) : '';
	$emp_id = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : NULL;
	$recommended_by = ( isset($_POST['txRecommendedBy']) && !empty($_POST['txRecommendedBy']) ) ? $_POST['txRecommendedBy'] : NULL;
	$recommended_date = ( isset($_POST['txRecommendedDate']) && !empty($_POST['txRecommendedDate']) ) ? $_POST['txRecommendedDate'] : NULL;    
	$reviewed_by = ( isset($_POST['txReviewedBy']) && !empty($_POST['txReviewedBy']) ) ? $_POST['txReviewedBy'] : NULL;
	$reviewed_date = ( isset($_POST['txReviewedDate']) && !empty($_POST['txReviewedDate']) ) ? $_POST['txReviewedDate'] : NULL;
	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : NULL;
	$approved_date = ( isset($_POST['txApprovedDate']) && !empty($_POST['txApprovedDate']) ) ? $_POST['txApprovedDate'] : NULL;
	$noted_by = ( isset($_POST['txNotedBy']) && !empty($_POST['txNotedBy']) ) ? $_POST['txNotedBy'] : NULL;
	$noted_date = ( isset($_POST['txNotedDate']) && !empty($_POST['txNotedDate']) ) ? $_POST['txNotedDate'] : NULL;

	$arrField = array('reason'=>$reason,'date_file'=>$date_file,'approved_by'=>$approved_by,'approved_date'=>$approved_date,'noted_by'=>$noted_by,'noted_date'=>$noted_date);

	if( $date_file && $lf_id ){
		$db->update('leave_file',$arrField,array('lf_id'=>$lf_id));
		$_SESSION['notif_success']='Changes saved!';
		functions::sendTo(functions::pageName().'?lfid='.functions::encode($lf_id));
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Leave Details</h2>
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
											<strong><?php echo $db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$emp_id)); ?></strong>
											<div style="padding-bottom:5px;"><input type="hidden" name="txRequestedBy" id="txRequestedBy" value="<?php echo $rq_empid ?>"></div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Date File</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><div style="padding-bottom:5px;"><?php echo functions::datearr($dateFile); ?></div><input type="hidden" name="txDF" id="txDF" value="<?php echo functions::encode($dateFile)?>" /></td>
						</tr>
						<tr>
							<th align="right" scope="row">Leave Type</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<table>
									<tr>
										<td align="left">
											<select name="txLeaveType" id="txLeaveType" style="width:500px; text-align:left;" <?php if($lf_id)echo 'disabled';?> required>
												<option value="">--select--</option>
												<?php
												$qEU = $db->select('leave_config','*',array(),'ORDER BY leave_name');
												while($rEU = $db->fetch_array($qEU)):
													$displaySel=0;
													if($allow_with_pay==1)
														$displaySel=1;
													else{
														if($rEU['with_pay']==0)
															$displaySel=1;
													}
													if($displaySel==1){
												?>
												<option value="<?php echo $rEU['lc_id']?>" <?php if($lc_id==$rEU['lc_id'])echo 'selected="selected"';?>><?php echo $rEU['leave_name']; echo ($rEU['with_pay']==1) ? ' (With Pay)' : ' (Without Pay)';?></option>
												<?php   }
												endwhile;?>
											</select>
											&nbsp;&nbsp;&nbsp;
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Reason</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txReason" id="txReason" style="width:70%;" value="<?php echo $reason?>" /></td>
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
							<td class="tdSpace" colspan="2" align="left"><input type="submit" name="<?php echo $btnName?>" id="<?php echo $btnName?>" value="Save" class="btn btn-primary btn-small"></td>
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