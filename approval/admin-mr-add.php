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
$today = date('Y-m-d');
$advance = strtotime('+1 day',strtotime(date('Y-m-d')));
$mr_date = date('Y-m-d',$advance);
if( isset($_POST['btnAdd']) ){

	$arr = array();
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : NULL;
	$mr_emp = ( isset($_POST['selEU']) && !empty($_POST['selEU']) ) ? trim($_POST['selEU']) : NULL;
	$employer = ( isset($_POST['txEmployer']) && !empty($_POST['txEmployer']) ) ? trim($_POST['txEmployer']) : NULL;
	$employer_add = ( isset($_POST['txEmployerAdd']) && !empty($_POST['txEmployerAdd']) ) ? trim($_POST['txEmployerAdd']) : NULL;
	$mr_date = ( isset($_POST['txMRDate']) && !empty($_POST['txMRDate']) ) ? trim($_POST['txMRDate']) : NULL;
	$return_date = ( isset($_POST['txDateRetrn']) && !empty($_POST['txDateRetrn']) ) ? trim($_POST['txDateRetrn']) : NULL;

	$assigned_location = ( isset($_POST['txAssgnLoc']) && !empty($_POST['txAssgnLoc']) ) ? $_POST['txAssgnLoc'] : NULL;

	$released_by = ( isset($_POST['txReleasedBy']) && !empty($_POST['txReleasedBy']) ) ? $_POST['txReleasedBy'] : NULL;
	$released_by_title = ( isset($_POST['txReleasedByTitle']) && !empty($_POST['txReleasedByTitle']) ) ? trim($_POST['txReleasedByTitle']) : NULL;
	$released_date = ( isset($_POST['txReleasedDate']) && !empty($_POST['txReleasedDate']) ) ? $_POST['txReleasedDate'] : NULL;

	$prepared_by = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : NULL;
	$prepared_by_title = ( isset($_POST['txPreparedByTitle']) && !empty($_POST['txPreparedByTitle']) ) ? trim($_POST['txPreparedByTitle']) : NULL;
	$prepared_date = ( isset($_POST['txPreparedDate']) && !empty($_POST['txPreparedDate']) ) ? $_POST['txPreparedDate'] : NULL;

	$checked_by = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? $_POST['txCheckedBy'] : NULL;
	$checked_by_title = ( isset($_POST['txCheckedByTitle']) && !empty($_POST['txCheckedByTitle']) ) ? trim($_POST['txCheckedByTitle']) : NULL;
	$checked_date = ( isset($_POST['txCheckedDate']) && !empty($_POST['txCheckedDate']) ) ? $_POST['txCheckedDate'] : NULL;

	$recommended_by = ( isset($_POST['txRecommendedBy']) && !empty($_POST['txRecommendedBy']) ) ? $_POST['txRecommendedBy'] : NULL;
	$recommended_by_title = ( isset($_POST['txRecommendedByTitle']) && !empty($_POST['txRecommendedByTitle']) ) ? trim($_POST['txRecommendedByTitle']) : NULL;
	$recommended_date = ( isset($_POST['txRecommendedDate']) && !empty($_POST['txRecommendedDate']) ) ? $_POST['txRecommendedDate'] : NULL;

	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : NULL;
	$approved_by_title = ( isset($_POST['txApprovedByTitle']) && !empty($_POST['txApprovedByTitle']) ) ? trim($_POST['txApprovedByTitle']) : NULL;
	$approved_date = ( isset($_POST['txApprovedDate']) && !empty($_POST['txApprovedDate']) ) ? $_POST['txApprovedDate'] : NULL;

	$released_by = ( isset($_POST['txReleasedBy']) && !empty($_POST['txReleasedBy']) ) ? $_POST['txReleasedBy'] : NULL;
	$released_by_title = ( isset($_POST['txReleasedByTitle']) && !empty($_POST['txReleasedByTitle']) ) ? trim($_POST['txReleasedByTitle']) : NULL;
	$released_date = ( isset($_POST['txReleasedDate']) && !empty($_POST['txReleasedDate']) ) ? $_POST['txReleasedDate'] : NULL;

	$received_by = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
	$received_date = ( isset($_POST['txReceivedDate']) && !empty($_POST['txReceivedDate']) ) ? $_POST['txReceivedDate'] : NULL;
	$received_by_title = ( isset($_POST['txReceivedByTitle']) && !empty($_POST['txReceivedByTitle']) ) ? $_POST['txReceivedByTitle'] : NULL;

	$arr = array('mr_emp'=>$mr_emp,'proj_id'=>$proj_id,'received_by_title'=>$received_by_title,'prepared_by_title'=>$prepared_by_title,'checked_by_title'=>$checked_by_title,'recommended_by_title'=>$recommended_by_title,'approved_by_title'=>$approved_by_title,'released_by_title'=>$released_by_title,'employer'=>$employer,'employer_add'=>$employer_add,'mr_date'=>$mr_date,'mreturn_date'=>$return_date,'assigned_location'=>$assigned_location,'received_id'=>$received_by,'received_date'=>$received_date,'prepared_id'=>$prepared_by,'prepared_date'=>$prepared_date,'checked_id'=>$checked_by,'checked_date'=>$checked_date,'recommended_id'=>$recommended_by,'recommended_date'=>$recommended_date,'approved_id'=>$approved_by,'approved_date'=>$approved_date,'released_id'=>$released_by,'released_date'=>$released_date);

	if( !empty($mr_emp) && !empty($proj_id) ){
		$insertID = $db->insert('mr',$arr);
		if($insertID){
			$mr_no = date('Ymd').$insertID;
			$db->update('mr',array('mr_no'=>$mr_no),array('mr_id'=>$insertID));
			$_SESSION['notif_success']='New MR Successfully Created!';
			functions::sendTo('admin-mr-item-manage.php?mr_id='.functions::encode($insertID));die();		
		}
		else{
			functions::say('MR Failed to Create. Please try again.');
		}
	}
}

$qEmployer = $db->query('SELECT DISTINCT employer FROM mr');
$namesEmployer='';
while($rEmployer=$db->fetch_array($qEmployer)):
	$string = preg_replace("/'/",'"',$rEmployer['employer']);
	$namesEmployer .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesEmployer .= '"--"';

$qEmployerAdd = $db->query('SELECT DISTINCT employer_add FROM mr');
$namesEmployerAdd='';
while($rEmployerAdd=$db->fetch_array($qEmployerAdd)):
	$string = preg_replace("/'/",'"',$rEmployerAdd['employer_add']);
	$namesEmployerAdd .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesEmployerAdd .= '"--"';

$qAssgnLoc = $db->query('SELECT DISTINCT assigned_location FROM mr');
$namesAssgnLoc='';
while($rAssgnLoc=$db->fetch_array($qAssgnLoc)):
	$string = preg_replace("/'/",'"',$rAssgnLoc['assigned_location']);
	$namesAssgnLoc .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesAssgnLoc .= '"--"';

$qReceivedTitle = $db->query('SELECT DISTINCT received_by_title FROM mr');
$namesReceivedTitle='';
while($rReceivedTitle=$db->fetch_array($qReceivedTitle)):
	$string = preg_replace("/'/",'"',$rReceivedTitle['received_by_title']);
	$namesReceivedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesReceivedTitle .= '"--"';

$qPreparedTitle = $db->query('SELECT DISTINCT prepared_by_title FROM mr');
$namesPreparedTitle='';
while($rPreparedTitle=$db->fetch_array($qPreparedTitle)):
	$string = preg_replace("/'/",'"',$rPreparedTitle['prepared_by_title']);
	$namesPreparedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPreparedTitle .= '"--"';

$qCheckedTitle = $db->query('SELECT DISTINCT checked_by_title FROM mr');
$namesCheckedTitle='';
while($rCheckedTitle=$db->fetch_array($qCheckedTitle)):
	$string = preg_replace("/'/",'"',$rCheckedTitle['checked_by_title']);
	$namesCheckedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCheckedTitle .= '"--"';

$qRecommendedTitle = $db->query('SELECT DISTINCT recommended_by_title FROM mr');
$namesRecommendedTitle='';
while($rRecommendedTitle=$db->fetch_array($qRecommendedTitle)):
	$string = preg_replace("/'/",'"',$rRecommendedTitle['recommended_by_title']);
	$namesRecommendedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesRecommendedTitle .= '"--"';


$qApprovedTitle = $db->query('SELECT DISTINCT approved_by_title FROM mr');
$namesApprovedTitle='';
while($rApprovedTitle=$db->fetch_array($qApprovedTitle)):
	$string = preg_replace("/'/",'"',$rApprovedTitle['approved_by_title']);
	$namesApprovedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesApprovedTitle .= '"--"';

$qReleasedTitle = $db->query('SELECT DISTINCT released_by_title FROM mr');
$namesReleasedTitle='';
while($rReleasedTitle=$db->fetch_array($qReleasedTitle)):
	$string = preg_replace("/'/",'"',$rReleasedTitle['released_by_title']);
	$namesReleasedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesReleasedTitle .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>MR Create</title>
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
	<style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>MR Create</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="75%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="25%" align="right" scope="row">&nbsp;</th>
							<td width="3%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Equipment User</th>
							<td>&nbsp;</td>
							<td style="padding: 25px 0px 4px 0px;">
								<select name="selEU" id="selEU" data-rel="chosen" style="width:600px;">
									<option value="">--select--</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="msgEU" style="font-weight:bold;" name="msgEU"></span>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Employer</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txEmployer" id="txEmployer" style="width:500px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesEmployer;?>]' /></td>
						</tr>
						<tr>
							<th align="right" scope="row">Employer Address</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txEmployerAdd" id="txEmployerAdd" style="width:500px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesEmployerAdd;?>]' /></td>
						</tr>
						<tr>
							<th align="right" scope="row">Project</th>
							<td>&nbsp;</td>
							<td style="padding: 25px 0px 4px 0px;">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:600px;">
									<option value="">--select--</option>
									<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):
									?>
									<option value="<?php echo $rProj['proj_id']?>"><?php echo ($rProj['proj_name']);?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Assignment Location</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txAssgnLoc" id="txAssgnLoc" style="width:500px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAssgnLoc;?>]' required/></td>
						</tr>
						<tr>
							<th align="right" scope="row">MR Date</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<a href="javascript:NewCssCal('txMRDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
								<input name="txMRDate" type="text" class="span6 mytextbox" id="txMRDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
								<span class="help-inline warning" style="font-weight:bold;" id="msgTxdate" name="msgTxdate"></span>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Date Return</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<a href="javascript:NewCssCal('txDateRetrn')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
								<input name="txDateRetrn" type="text" class="span6 mytextbox" id="txDateRetrn" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
								<span class="help-inline warning" style="font-weight:bold;" id="msgTxdateRetrn" name="msgTxdateRetrn"></span>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center" height="30">&nbsp;</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td class="tdSpace" align="center">
											<div align="left">
												<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):
													?>
													<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td><div align="center"><input type="text" name="txReceivedByTitle" id="txReceivedByTitle" style="width:285px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReceivedTitle;?>]' /></div></td>
										<td>
											<div align="center">
												<a href="javascript:NewCssCal('txReceivedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txReceivedDate" type="text" class="span6 mytextbox" id="txReceivedDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong><?php echo ($mr_date<=$today) ? 'Received By' : 'Checked and Received By'; ?></strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table><br>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td class="tdSpace" align="center">
											<div align="left">
												<select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):
													?>
													<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td><div align="center"><input type="text" name="txPreparedByTitle" id="txPreparedByTitle" style="width:285px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPreparedTitle;?>]' /></div></td>
										<td>
											<div align="center">
												<a href="javascript:NewCssCal('txPreparedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txPreparedDate" type="text" class="span6 mytextbox" id="txPreparedDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong><?php echo ($mr_date<=$today) ? 'Prepared By' : 'Prepared and Checked By'; ?></strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table><br>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td class="tdSpace" align="center">
											<div align="left">
												<select name="txCheckedBy" id="txCheckedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):
													?>
													<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td><div align="center"><input type="text" name="txCheckedByTitle" id="txCheckedByTitle" style="width:285px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCheckedTitle;?>]' /></div></td>
										<td>
											<div align="center">
												<a href="javascript:NewCssCal('txCheckedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txCheckedDate" type="text" class="span6 mytextbox" id="txCheckedDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong><?php echo ($mr_date<=$today) ? 'Checked By' : 'Delivered By'; ?></strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table><br>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td class="tdSpace" align="center">
											<div align="left">
												<select name="txRecommendedBy" id="txRecommendedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):
													?>
													<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td><div align="center"><input type="text" name="txRecommendedByTitle" id="txRecommendedByTitle" style="width:285px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesRecommendedTitle;?>]' /></div></td>
										<td width="16%">
											<div align="center">
												<a href="javascript:NewCssCal('txRecommendedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txRecommendedDate" type="text" class="span6 mytextbox" id="txRecommendedDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong>Recommended By</strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table><br>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td class="tdSpace" align="center">
											<div align="left">
												<select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:300px; text-align:left;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):
													?>
													<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td><div align="center"><input type="text" name="txApprovedByTitle" id="txApprovedByTitle" style="width:285px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesApprovedTitle;?>]' /></div></td>
										<td>
											<div align="center">
												<a href="javascript:NewCssCal('txApprovedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txApprovedDate" type="text" class="span6 mytextbox" id="txApprovedDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong>Approved By</strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table><br>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td class="tdSpace" align="center">
											<div align="left">
												<select name="txReleasedBy" id="txReleasedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):
													?>
													<option value="<?php echo $rEU['emp_id']?>"><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td><div align="center"><input type="text" name="txReleasedByTitle" id="txReleasedByTitle" style="width:285px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReleasedTitle;?>]' /></div></td>
										<td>
											<div align="center">
												<a href="javascript:NewCssCal('txReleasedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txReleasedDate" type="text" class="span6 mytextbox" id="txReleasedDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong>Released By</strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table><br>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3"><div align="center"><input type="submit" name="btnAdd" id="btnAdd" value="Create MR" class="btn btn-small btn-primary"></div></td>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function ask(){
	if(confirm('Do you want to add this MR?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#btnAdd').click(function(){
		$('#msgProject').html("");
		$('#msgEU').html("");

		if( $('#selEU').val()=="" ){
			$('#msgEU').html("Equipment User Required!");
			res=false;
		}
		else if( $('#selProj').val()=="" ){
			$('#msgProject').html("Project Required!");
			res=false;
		}
		else{
			if(ask())
				res=true;
		}
		return res;
	});
});
</script>
<!-- end: JavaScript-->
</body>
</html>