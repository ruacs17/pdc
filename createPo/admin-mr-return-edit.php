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
$mr_id = (isset($_REQUEST['mr_id']) && !empty($_REQUEST['mr_id']) ) ? functions::decode($_REQUEST['mr_id']) : 0;
  
if( isset($_POST['btnCancel']) ){
	functions::sendTo('admin-mr-return-item-manage.php?mr_id='.functions::encode($mr_id));die();
}
if( isset($_POST['btnSave']) ){
	$arr = array();

	$ret_received_by = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
	$ret_received_by_title = ( isset($_POST['txReceivedByTitle']) && !empty($_POST['txReceivedByTitle']) ) ? trim($_POST['txReceivedByTitle']) : NULL;
	$ret_received_date = ( isset($_POST['txReceivedDate']) && !empty($_POST['txReceivedDate']) ) ? $_POST['txReceivedDate'] : NULL;

	$ret_checked_by = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? $_POST['txCheckedBy'] : NULL;
	$ret_checked_by_title = ( isset($_POST['txCheckedByTitle']) && !empty($_POST['txCheckedByTitle']) ) ? trim($_POST['txCheckedByTitle']) : NULL;
	$ret_checked_date = ( isset($_POST['txCheckedDate']) && !empty($_POST['txCheckedDate']) ) ? $_POST['txCheckedDate'] : NULL;

	$ret_noted_by = ( isset($_POST['txNotedBy']) && !empty($_POST['txNotedBy']) ) ? $_POST['txNotedBy'] : NULL;
	$ret_noted_by_title = ( isset($_POST['txNotedByTitle']) && !empty($_POST['txNotedByTitle']) ) ? trim($_POST['txNotedByTitle']) : NULL;
	$ret_noted_date = ( isset($_POST['txNotedDate']) && !empty($_POST['txNotedDate']) ) ? $_POST['txNotedDate'] : NULL; 

	$mr_remarks = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : NULL;

	$arr = array(
		'ret_received_id'=>$ret_received_by,'ret_received_by_title'=>$ret_received_by_title,'ret_received_date'=>$ret_received_date,
		'ret_checked_id'=>$ret_checked_by,'ret_checked_by_title'=>$ret_checked_by_title,'ret_checked_date'=>$ret_checked_date,
		'ret_noted_id'=>$ret_noted_by,'ret_noted_by_title'=>$ret_noted_by_title,'ret_noted_date'=>$ret_noted_date,
		'mr_remarks'=>$mr_remarks);

	if( !empty($mr_id) ){
		$db->update('mr',$arr,array('mr_id'=>$mr_id));
		$_SESSION['notif_success']='MR Changes Saved!';
		functions::sendTo('admin-mr-return-item-manage.php?mr_id='.functions::encode($mr_id));
		die();
	}
}

$ret_turnover_by=''; $ret_turnover_by_title=''; $ret_turnover_date='';$ret_received_by=''; $ret_received_by_title=''; $ret_received_date='';$ret_checked_by=''; $ret_checked_by_title=''; $ret_checked_date=''; $ret_noted_by=''; $ret_noted_by_title=''; $ret_noted_date=''; $ret_approved_by=''; $ret_approved_by_title=''; $ret_approved_date=''; $remarks='';

$qShow = $db->select('mr','*',array('mr_id'=>$mr_id));
$rShow = $db->fetch_array($qShow);

$ret_received_by = $rShow['ret_received_id'];
$ret_received_by_title = $rShow['ret_received_by_title'];
$ret_received_date = $rShow['ret_received_date'];

$ret_checked_by = $rShow['ret_checked_id'];
$ret_checked_by_title = $rShow['ret_checked_by_title'];
$ret_checked_date = $rShow['ret_checked_date'];

$ret_noted_by = $rShow['ret_noted_id'];
$ret_noted_by_title = $rShow['ret_noted_by_title'];
$ret_noted_date = $rShow['ret_noted_date'];
$remarks = $rShow['mr_remarks'];

$qReceivedTitle = $db->query('SELECT DISTINCT ret_received_by_title as colmn FROM mr');
$namesReceivedTitle='';
while($rReceivedTitle=$db->fetch_array($qReceivedTitle)):
	$string = preg_replace("/'/",'"',$rReceivedTitle['colmn']);
	$namesReceivedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesReceivedTitle .= '"--"';

$qCheckedTitle = $db->query('SELECT DISTINCT ret_checked_by_title as colmn FROM mr');
$namesCheckedTitle='';
while($rCheckedTitle=$db->fetch_array($qCheckedTitle)):
	$string = preg_replace("/'/",'"',$rCheckedTitle['colmn']);
	$namesCheckedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCheckedTitle .= '"--"';

$qNotedTitle = $db->query('SELECT DISTINCT ret_noted_by_title as colmn FROM mr');
$namesNotedTitle='';
while($rNotedTitle=$db->fetch_array($qNotedTitle)):
	$string = preg_replace("/'/",'"',$rNotedTitle['colmn']);
	$namesNotedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesNotedTitle .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>MR RETURN Update</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>MR Return Update</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="75%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="35%" align="right" scope="row">&nbsp;</th>
							<td width="3%">&nbsp;</td>
							<td width="50%">&nbsp;</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td width="40%" class="tdSpace" align="center">
											<div align="left">
												<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):?>
													<option value="<?php echo $rEU['emp_id']?>" <?php if($ret_received_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td width="40%"><div align="center"><input type="text" name="txReceivedByTitle" id="txReceivedByTitle" style="width:285px;" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReceivedTitle;?>]' value="<?php echo $ret_received_by_title?>" /></div></td>
										<td width="20%">
											<div align="center">
												<a href="javascript:NewCssCal('txReceivedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txReceivedDate" type="text" class="span6 mytextbox" id="txReceivedDate" value="<?php echo $ret_received_date?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong>Received and Checked By</strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td width="40%" class="tdSpace" align="center">
											<div align="left">
												<select name="txCheckedBy" id="txCheckedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):?>
													<option value="<?php echo $rEU['emp_id']?>"  <?php if($ret_checked_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td width="40%"><div align="center"><input type="text" name="txCheckedByTitle" id="txCheckedByTitle" style="width:285px;" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCheckedTitle;?>]' value="<?php echo $ret_checked_by_title?>" /></div></td>
										<td width="20%">
											<div align="center">
												<a href="javascript:NewCssCal('txCheckedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txCheckedDate" type="text" class="span6 mytextbox" id="txCheckedDate" value="<?php echo $ret_checked_date?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong>Inspected By</strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3" align="center">
								<table width="95%" border="0">
									<tr>
										<td width="40%" class="tdSpace" align="center">
											<div align="left">
												<select name="txNotedBy" id="txNotedBy" data-rel="chosen" style="width:300px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
													while($rEU = $db->fetch_array($qEU)):?>
													<option value="<?php echo $rEU['emp_id']?>"  <?php if($ret_noted_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td width="40%"><div align="center"><input type="text" name="txNotedByTitle" id="txNotedByTitle" style="width:285px;" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesNotedTitle;?>]' value="<?php echo $ret_noted_by_title?>" /></div></td>
										<td width="20%">
											<div align="center">
												<a href="javascript:NewCssCal('txNotedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
												<input name="txNotedDate" type="text" class="span6 mytextbox" id="txNotedDate" value="<?php echo $ret_noted_date?>" style="width: 90px;" readonly>
											</div>
										</td>
									</tr>
									<tr>
										<td><div align="left"><strong>Noted By</strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td height="50">&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<th align="right" scope="row">Remarks</th>
							<td></td>
							<td><textarea name="txRemark" id="txRemark" cols="7"><?php echo $remarks;?></textarea></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td class="tdSpace" colspan="2" align="left">
								<input type="submit" name="btnSave" id="btnSave" value="Update MR Return" class="btn btn-primary btn-small">
								<input type="submit" name="btnCancel" id="btnCancel" value="Cancel" class="btn btn-small">
							</td>
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
	$('#btnSave').click(function(){
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
			res=true;
		}
		return res;
	});
});
</script>
<!-- end: JavaScript-->
</body>
</html>