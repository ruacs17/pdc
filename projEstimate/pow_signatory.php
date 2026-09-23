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
$filename='';$payroll_type='';$selMonFrom='';$selDayFrom='';$selYrFrom=date('Y');$selMonTo='';$selDayTo='';$selYrTo=date('Y');

$proj_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$qEatID = $db->select('dqc_boq_ca','*',array('proj_id'=>$proj_id,'sig_type'=>'pow'));
$rEatID = $db->fetch_array($qEatID);
$prepared_other = $rEatID['prepared_other'];
$prepared_id = $rEatID['prepared_id'];
$prepared_by = ($prepared_other) ? $rEatID['prepared_by'] : '';
$prepared_by_title = ($prepared_other) ? $rEatID['prepared_by_title'] : '';

$checked_other = $rEatID['checked_other'];
$checked_id = $rEatID['checked_id'];
$checked_by = ($checked_other) ? $rEatID['checked_by'] : '';
$checked_by_title = ($checked_other) ? $rEatID['checked_by_title'] : '';

$noted_other = $rEatID['noted_other'];
$noted_id = $rEatID['noted_id'];
$noted_by = ($noted_other) ? $rEatID['noted_by'] : '';
$noted_by_title = ($noted_other) ? $rEatID['noted_by_title'] : '';

$reviewed_other = $rEatID['reviewed_other'];
$reviewed_id = $rEatID['reviewed_id'];
$reviewed_by = ($reviewed_other) ? $rEatID['reviewed_by'] : '';
$reviewed_by_title = ($reviewed_other) ? $rEatID['reviewed_by_title'] : '';

$approved_other = $rEatID['approved_other'];
$approved_id = $rEatID['approved_id'];
$approved_by = ($approved_other) ? $rEatID['approved_by'] : '';
$approved_by_title = ($approved_other) ? $rEatID['approved_by_title'] : '';


$arrEmp=array();
$qEU = $db->select('employee','*',array(),'ORDER BY lname');
while($rEU = $db->fetch_array($qEU)):
	$arrEmp[$rEU['emp_id']] = strtoupper($rEU['lname'].', '.$rEU['fname']);
endwhile;

$qList = $db->query('SELECT DISTINCT prepared_by FROM dqc_boq_ca WHERE prepared_other = 1 ORDER BY prepared_by');
$namesPreparedBy='';
while($rList=$db->fetch_array($qList)):
	$namesPreparedBy .= '"'.$rList['prepared_by'].'",';
endwhile;
$namesPreparedBy .= '"--"';

$qList = $db->query('SELECT DISTINCT prepared_by_title FROM dqc_boq_ca WHERE prepared_other = 1 ORDER BY prepared_by_title');
$namesPreparedByTitle='';
while($rList=$db->fetch_array($qList)):
	$namesPreparedByTitle .= '"'.$rList['prepared_by_title'].'",';
endwhile;
$namesPreparedByTitle .= '"--"';


$qList = $db->query('SELECT DISTINCT checked_by FROM dqc_boq_ca WHERE checked_other = 1 ORDER BY checked_by');
$namesCheckedBy='';
while($rList=$db->fetch_array($qList)):
	$namesCheckedBy .= '"'.$rList['checked_by'].'",';
endwhile;
$namesCheckedBy .= '"--"';
$qList = $db->query('SELECT DISTINCT checked_by_title FROM dqc_boq_ca WHERE checked_other = 1 ORDER BY checked_by_title');
$namesCheckedByTitle='';
while($rList=$db->fetch_array($qList)):
	$namesCheckedByTitle .= '"'.$rList['checked_by_title'].'",';
endwhile;
$namesCheckedByTitle .= '"--"';

$qList = $db->query('SELECT DISTINCT noted_by FROM dqc_boq_ca WHERE noted_other = 1 ORDER BY noted_by');
$namesNotedBy='';
while($rList=$db->fetch_array($qList)):
	$namesNotedBy .= '"'.$rList['noted_by'].'",';
endwhile;
$namesNotedBy .= '"--"';
$qList = $db->query('SELECT DISTINCT noted_by_title FROM dqc_boq_ca WHERE noted_other = 1 ORDER BY noted_by_title');
$namesNotedByTitle='';
while($rList=$db->fetch_array($qList)):
	$namesNotedByTitle .= '"'.$rList['noted_by_title'].'",';
endwhile;
$namesNotedByTitle .= '"--"';

$qList = $db->query('SELECT DISTINCT reviewed_by FROM dqc_boq_ca WHERE reviewed_other = 1 ORDER BY reviewed_by');
$namesReviewedBy='';
while($rList=$db->fetch_array($qList)):
	$namesReviewedBy .= '"'.$rList['reviewed_by'].'",';
endwhile;
$namesReviewedBy .= '"--"';
$qList = $db->query('SELECT DISTINCT reviewed_by_title FROM dqc_boq_ca WHERE reviewed_other = 1 ORDER BY reviewed_by_title');
$namesReviewedByTitle='';
while($rList=$db->fetch_array($qList)):
	$namesReviewedByTitle .= '"'.$rList['reviewed_by_title'].'",';
endwhile;
$namesReviewedByTitle .= '"--"';

$qList = $db->query('SELECT DISTINCT approved_by FROM dqc_boq_ca WHERE approved_other = 1 ORDER BY approved_by');
$namesApprovedBy='';
while($rList=$db->fetch_array($qList)):
	$namesApprovedBy .= '"'.$rList['approved_by'].'",';
endwhile;
$namesApprovedBy .= '"--"';
$qList = $db->query('SELECT DISTINCT approved_by_title FROM dqc_boq_ca WHERE approved_other = 1 ORDER BY approved_by_title');
$namesApprovedByTitle='';
while($rList=$db->fetch_array($qList)):
	$namesApprovedByTitle .= '"'.$rList['approved_by_title'].'",';
endwhile;
$namesApprovedByTitle .= '"--"';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Program of Works Signatory Setting</title>
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
if( isset($_POST['btnSave']) ){
	function position($emp_id){
		global $db;
		$countPos=0;$position='';
		$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
		while($rPos = $db->fetch_array($qPos)):
			if($countPos)
				$position .= ' /<br>';
			$position .= $rPos['pos_name'];
			$countPos++;
		endwhile;
		return $position;
	}
	$prepared_id = NULL;
	$rdoPrepared = ( isset($_POST['rdoPrepared']) && !empty($_POST['rdoPrepared']) ) ? trim($_POST['rdoPrepared']) : NULL;
	if($rdoPrepared==1){
		$prepared_id = ( isset($_POST['selPreparedBy']) && !empty($_POST['selPreparedBy']) ) ? trim($_POST['selPreparedBy']) : NULL;
		$qEU = $db->select('employee','*',array('emp_id'=>$prepared_id));
		$rEU = $db->fetch_array($qEU);
		$prepared_by = strtoupper($rEU['fname'].' '.$rEU['lname']);
		$prepared_by_title = position($prepared_id);
		$prepared_other=0;
	}
	else if($rdoPrepared==2){
		$prepared_by = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? trim($_POST['txPreparedBy']) : NULL;
		$prepared_by_title = ( isset($_POST['txPreparedByTitle']) && !empty($_POST['txPreparedByTitle']) ) ? trim($_POST['txPreparedByTitle']) : NULL;
		$prepared_other=1;
	}
	$checked_id=NULL;
	$rdoChecked = ( isset($_POST['rdoChecked']) && !empty($_POST['rdoChecked']) ) ? trim($_POST['rdoChecked']) : NULL;
	if($rdoChecked==1){
		$checked_id = ( isset($_POST['selCheckedBy']) && !empty($_POST['selCheckedBy']) ) ? trim($_POST['selCheckedBy']) : NULL;
		$qEU = $db->select('employee','*',array('emp_id'=>$checked_id));
		$rEU = $db->fetch_array($qEU);
		$checked_by = strtoupper($rEU['fname'].' '.$rEU['lname']);
		$checked_by_title = position($checked_id);
		$checked_other=0;
	}
	else if($rdoChecked==2){
		$checked_by = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? trim($_POST['txCheckedBy']) : NULL;
		$checked_by_title = ( isset($_POST['txCheckedByTitle']) && !empty($_POST['txCheckedByTitle']) ) ? trim($_POST['txCheckedByTitle']) : NULL;
		$checked_other=1;
	}

	$noted_id = NULL;
	$rdoNoted = ( isset($_POST['rdoNoted']) && !empty($_POST['rdoNoted']) ) ? trim($_POST['rdoNoted']) : NULL;
	if($rdoNoted==1){
		$noted_id = ( isset($_POST['selNotedBy']) && !empty($_POST['selNotedBy']) ) ? trim($_POST['selNotedBy']) : NULL;
		$qEU = $db->select('employee','*',array('emp_id'=>$noted_id));
		$rEU = $db->fetch_array($qEU);
		$noted_by = strtoupper($rEU['fname'].' '.$rEU['lname']);
		$noted_by_title = position($noted_id);
		$noted_other=0;
	}
	else if($rdoNoted==2){
		$noted_by = ( isset($_POST['txNotedBy']) && !empty($_POST['txNotedBy']) ) ? trim($_POST['txNotedBy']) : NULL;
		$noted_by_title = ( isset($_POST['txNotedByTitle']) && !empty($_POST['txNotedByTitle']) ) ? trim($_POST['txNotedByTitle']) : NULL;
		$noted_other=1;
	}

	$reviewed_id = NULL;
	$rdoReviewed = ( isset($_POST['rdoReviewed']) && !empty($_POST['rdoReviewed']) ) ? trim($_POST['rdoReviewed']) : NULL;
	if($rdoReviewed==1){
		$reviewed_id = ( isset($_POST['selReviewedBy']) && !empty($_POST['selReviewedBy']) ) ? trim($_POST['selReviewedBy']) : NULL;
		$qEU = $db->select('employee','*',array('emp_id'=>$reviewed_id));
		$rEU = $db->fetch_array($qEU);
		$reviewed_by = strtoupper($rEU['fname'].' '.$rEU['lname']);
		$reviewed_by_title = position($reviewed_id);
		$reviewed_other=0;
	}
	else if($rdoReviewed==2){
		$reviewed_by = ( isset($_POST['txReviewedBy']) && !empty($_POST['txReviewedBy']) ) ? trim($_POST['txReviewedBy']) : NULL;
		$reviewed_by_title = ( isset($_POST['txReviewedByTitle']) && !empty($_POST['txReviewedByTitle']) ) ? trim($_POST['txReviewedByTitle']) : NULL;
		$reviewed_other=1;
	}

	$approved_id = NULL;
	$rdoApproved = ( isset($_POST['rdoApproved']) && !empty($_POST['rdoApproved']) ) ? trim($_POST['rdoApproved']) : NULL;
	if($rdoApproved==1){
		$approved_id = ( isset($_POST['selApprovedBy']) && !empty($_POST['selApprovedBy']) ) ? trim($_POST['selApprovedBy']) : NULL;
		$qEU = $db->select('employee','*',array('emp_id'=>$approved_id));
		$rEU = $db->fetch_array($qEU);
		$approved_by = strtoupper($rEU['fname'].' '.$rEU['lname']);
		$approved_by_title = position($approved_id);
		$approved_other=0;
	}
	else if($rdoApproved==2){
		$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? trim($_POST['txApprovedBy']) : NULL;
		$approved_by_title = ( isset($_POST['txApprovedByTitle']) && !empty($_POST['txApprovedByTitle']) ) ? trim($_POST['txApprovedByTitle']) : NULL;
		$approved_other=1;
	}

	$arrField = array(
		'proj_id'=>$proj_id,
		'prepared_id'=>$prepared_id,'prepared_by'=>$prepared_by,'prepared_by_title'=>$prepared_by_title,'prepared_other'=>$prepared_other,
		'checked_id'=>$checked_id,'checked_by'=>$checked_by,'checked_by_title'=>$checked_by_title,'checked_other'=>$checked_other,
		'noted_id'=>$noted_id,'noted_by'=>$noted_by,'noted_by_title'=>$noted_by_title,'noted_other'=>$noted_other,
		'reviewed_id'=>$reviewed_id,'reviewed_by'=>$reviewed_by,'reviewed_by_title'=>$reviewed_by_title,'reviewed_other'=>$reviewed_other,
		'approved_id'=>$approved_id,'approved_by'=>$approved_by,'approved_by_title'=>$approved_by_title,'approved_other'=>$approved_other,
		'sig_type'=>'pow');
	if( $proj_id ){
		if( $db->getValue('dqc_boq_ca','count(*)',array('proj_id'=>$proj_id)) ){
			$db->update('dqc_boq_ca',$arrField,array('proj_id'=>$proj_id));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?pid='.functions::encode($proj_id));
			die();
		}
		else{
			$db->insert('dqc_boq_ca',$arrField);
			#echo $db->last_query;
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo(functions::pageName().'?pid='.functions::encode($proj_id));
			die();
		}
	}
	else
		$_SESSION['notif_warning']='Please fill up the form properly!';
}
?>
<style type="text/css">
fieldset {
    margin: 8px;
    border: 3px solid silver;
    padding: 8px;    
    border-radius: 4px;
}
legend {
    padding: 2px;    
}
</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask();">
				<div align="center" style="padding-bottom: 15px;"><h2>Program of Works SIGNATORY</h2></div>
				<div align="center">
					<table border="0" width="70%">
						<tr>
							<td valign="middle" height="200px;" width="20%"><div align="left"><strong>Prepared By</strong></div></td>
							<td>
								<table border="0" width="99%">
									<tr>
										<td style="padding: 0px 5px 15px 4px"><label class="radio"><input type="radio" name="rdoPrepared" id="rdoPrepared1" value="1" onclick="document.getElementById('txPreparedBy').disabled=true;document.getElementById('txPreparedByTitle').disabled=true;" <?php echo ($prepared_other==0) ? 'checked="checked"' : '';?>></label></td>
										<td style="padding: 10px 5px 15px 4px">
											<select name="selPreparedBy" id="selPreparedBy" data-rel="chosen" style="width:550px;" value="1" onchange="document.getElementById('rdoPrepared1').checked=true;document.getElementById('txPreparedBy').disabled=true;document.getElementById('txPreparedByTitle').disabled=true;">
												<option value="">--select--</option>
												<?php foreach($arrEmp as $empid => $empname):?>
												<option value="<?php echo $empid?>" <?php if($prepared_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 0px 5px 10px 4px" valign="top"><label class="radio"><input type="radio"name="rdoPrepared" id="rdoPrepared2" value="2" onclick="document.getElementById('txPreparedBy').disabled=false;document.getElementById('txPreparedByTitle').disabled=false;" <?php echo ($prepared_other==1) ? 'checked="checked"' : '';?>> Others</label></td>
										<td style="padding: 0px 5px 4px 4px">
											<input type="text" class="span6 typeahead" name="txPreparedBy" id="txPreparedBy" placeholder="Complete Name" style="width:550px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPreparedBy;?>]' value="<?php echo $prepared_by;?>"><div style="padding-top:5px;" required></div>
											<input type="text" class="span6 typeahead" placeholder="Designation" name="txPreparedByTitle" id="txPreparedByTitle" style="width:450px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPreparedByTitle;?>]' value="<?php echo $prepared_by_title;?>" required>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td valign="middle" height="200px;"><div align="left"><strong>Checked By</strong></div></td>
							<td>
								<table border="0" width="99%">
									<tr>
										<td style="padding: 0px 5px 15px 4px"><label class="radio"><input type="radio" name="rdoChecked" id="rdoChecked1" value="1" onclick="document.getElementById('txCheckedBy').disabled=true;document.getElementById('txCheckedByTitle').disabled=true;" <?php echo ($checked_other==0) ? 'checked="checked"' : '';?>></label></td>
										<td style="padding: 10px 5px 15px 4px">
											<select name="selCheckedBy" id="selCheckedBy" data-rel="chosen" style="width:550px;" value="1" onchange="document.getElementById('rdoChecked1').checked=true;document.getElementById('txCheckedBy').disabled=true;document.getElementById('txCheckedByTitle').disabled=true;">
												<option value="">--select--</option>
												<?php foreach($arrEmp as $empid => $empname):?>
												<option value="<?php echo $empid?>" <?php if($checked_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 0px 5px 10px 4px" valign="top"><label class="radio"><input type="radio"name="rdoChecked" id="rdoChecked2" value="2" onclick="document.getElementById('txCheckedBy').disabled=false;document.getElementById('txCheckedByTitle').disabled=false;" <?php echo ($checked_other==1) ? 'checked="checked"' : '';?>> Others</label></td>
										<td style="padding: 0px 5px 4px 4px"><input type="text" class="span6 typeahead" name="txCheckedBy" id="txCheckedBy" placeholder="Complete Name" style="width:550px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCheckedBy;?>]' value="<?php echo $checked_by;?>" required><div style="padding-top:5px;"></div>
											<input type="text" class="span6 typeahead" placeholder="Designation" name="txCheckedByTitle" id="txCheckedByTitle" style="width:450px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCheckedByTitle;?>]' value="<?php echo $checked_by_title;?>" required>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td valign="middle" height="200px;"><div align="left"><strong>Noted By</strong></div></td>
							<td>
								<table border="0" width="99%">
									<tr>
										<td style="padding: 0px 5px 15px 4px"><label class="radio"><input type="radio" name="rdoNoted" id="rdoNoted1" value="1" onclick="document.getElementById('txNotedBy').disabled=true;document.getElementById('txNotedByTitle').disabled=true;" <?php echo ($noted_other==0) ? 'checked="checked"' : '';?>></label></td>
										<td style="padding: 10px 5px 15px 4px">
											<select name="selNotedBy" id="selNotedBy" data-rel="chosen" style="width:550px;" value="1" onchange="document.getElementById('rdoNoted1').checked=true;document.getElementById('txNotedBy').disabled=true;document.getElementById('txNotedByTitle').disabled=true;">
												<option value="">--select--</option>
												<?php foreach($arrEmp as $empid => $empname):?>
												<option value="<?php echo $empid?>" <?php if($noted_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 0px 5px 10px 4px" valign="top"><label class="radio"><input type="radio"name="rdoNoted" id="rdoNoted2" value="2" onclick="document.getElementById('txNotedBy').disabled=false;document.getElementById('txNotedByTitle').disabled=false;" <?php echo ($noted_other==1) ? 'checked="checked"' : '';?>> Others</label></td>
										<td style="padding: 0px 5px 4px 4px"><input type="text" class="span6 typeahead" name="txNotedBy" id="txNotedBy" placeholder="Complete Name" style="width:550px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesNotedBy;?>]' value="<?php echo $noted_by;?>" required><div style="padding-top:5px;"></div>
											<input type="text" class="span6 typeahead" placeholder="Designation" name="txNotedByTitle" id="txNotedByTitle" style="width:450px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesNotedByTitle;?>]' value="<?php echo $noted_by_title;?>" required>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td valign="middle" height="200px;"><div align="left"><strong>Reviewed By</strong></div></td>
							<td>
								<table border="0" width="99%">
									<tr>
										<td style="padding: 0px 5px 15px 4px"><label class="radio"><input type="radio" name="rdoReviewed" id="rdoReviewed1" value="1" onclick="document.getElementById('txReviewedBy').disabled=true;document.getElementById('txReviewedByTitle').disabled=true;" <?php echo ($reviewed_other==0) ? 'checked="checked"' : '';?>></label></td>
										<td style="padding: 10px 5px 15px 4px">
											<select name="selReviewedBy" id="selReviewedBy" data-rel="chosen" style="width:550px;" value="1" onchange="document.getElementById('rdoReviewed1').checked=true;document.getElementById('txReviewedBy').disabled=true;document.getElementById('txReviewedByTitle').disabled=true;">
												<option value="">--select--</option>
												<?php foreach($arrEmp as $empid => $empname):?>
												<option value="<?php echo $empid?>" <?php if($reviewed_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 0px 5px 10px 4px" valign="top"><label class="radio"><input type="radio"name="rdoReviewed" id="rdoReviewed2" value="2" onclick="document.getElementById('txReviewedBy').disabled=false;document.getElementById('txReviewedByTitle').disabled=false;" <?php echo ($reviewed_other==1) ? 'checked="checked"' : '';?>> Others</label></td>
										<td style="padding: 0px 5px 4px 4px"><input type="text" class="span6 typeahead" name="txReviewedBy" id="txReviewedBy" placeholder="Complete Name" style="width:550px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReviewedBy;?>]' value="<?php echo $reviewed_by;?>" required><div style="padding-top:5px;"></div>
											<input type="text" class="span6 typeahead" placeholder="Designation" name="txReviewedByTitle" id="txReviewedByTitle" style="width:450px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReviewedByTitle;?>]' value="<?php echo $reviewed_by_title;?>" required>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td valign="middle" height="200px;"><div align="left"><strong>Approved By</strong></div></td>
							<td>
								<table border="0" width="99%">
									<tr>
										<td style="padding: 0px 5px 15px 4px"><label class="radio"><input type="radio" name="rdoApproved" id="rdoApproved1" value="1" onclick="document.getElementById('txApprovedBy').disabled=true;document.getElementById('txApprovedByTitle').disabled=true;" <?php echo ($approved_other==0) ? 'checked="checked"' : '';?>></label></td>
										<td style="padding: 10px 5px 15px 4px">
											<select name="selApprovedBy" id="selApprovedBy" data-rel="chosen" style="width:550px;" value="1" onchange="document.getElementById('rdoApproved1').checked=true;document.getElementById('txApprovedBy').disabled=true;document.getElementById('txApprovedByTitle').disabled=true;">
												<option value="">--select--</option>
												<?php foreach($arrEmp as $empid => $empname):?>
												<option value="<?php echo $empid?>" <?php if($approved_id==$empid)echo 'selected="selected"';?>><?php echo $empname;?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 0px 5px 10px 4px" valign="top"><label class="radio"><input type="radio"name="rdoApproved" id="rdoApproved2" value="2" onclick="document.getElementById('txApprovedBy').disabled=false;document.getElementById('txApprovedByTitle').disabled=false;" <?php echo ($approved_other==1) ? 'checked="checked"' : '';?>> Others</label></td>
										<td style="padding: 0px 5px 4px 4px"><input type="text" class="span6 typeahead" name="txApprovedBy" id="txApprovedBy" placeholder="Complete Name" style="width:550px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesApprovedBy;?>]' value="<?php echo $approved_by;?>" required><div style="padding-top:5px;"></div>
											<input type="text" class="span6 typeahead" placeholder="Designation" name="txApprovedByTitle" id="txApprovedByTitle" style="width:450px;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesApprovedByTitle;?>]' value="<?php echo $approved_by_title;?>" required>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>

					<table border="0">
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>
								<div align="left" style="padding-top: 40px;">
									<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
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
<script>
$(document).ready(function(){
	var res = false;
	<?php if( empty($prepared_other) ){?>
		$('#txPreparedBy,#txPreparedByTitle').prop('disabled',true);
	<?php } ?>
	<?php if( empty($checked_other) ){?>
		$('#txCheckedBy,#txCheckedByTitle').prop('disabled',true);
	<?php } ?>
	<?php if( empty($noted_other) ){?>
		$('#txNotedBy,#txNotedByTitle').prop('disabled',true);
	<?php } ?>
	<?php if( empty($reviewed_other) ){?>
		$('#txReviewedBy,#txReviewedByTitle').prop('disabled',true);
	<?php } ?>
	<?php if( empty($approved_other) ){?>
		$('#txApprovedBy,#txApprovedByTitle').prop('disabled',true);
	<?php } ?>

});
</script>
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this changes?'))
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