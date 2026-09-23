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

if( isset($_POST['btnAdd']) ){
	$arr = array();
	$proj_id = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : '';
	$emp_id = ( isset($_POST['selEU']) && !empty($_POST['selEU']) ) ? trim($_POST['selEU']) : '';
	$employer = ( isset($_POST['txEmployer']) && !empty($_POST['txEmployer']) ) ? trim($_POST['txEmployer']) : NULL;
	$employer_add = ( isset($_POST['txEmployerAdd']) && !empty($_POST['txEmployerAdd']) ) ? trim($_POST['txEmployerAdd']) : '';
	$ppe_date = ( isset($_POST['txPPEDate']) && !empty($_POST['txPPEDate']) ) ? trim($_POST['txPPEDate']) : NULL;
	$assigned_location = ( isset($_POST['txAssgnLoc']) && !empty($_POST['txAssgnLoc']) ) ? $_POST['txAssgnLoc'] : '';

	$prepared_by = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : NULL;
	$prepared_by_title = ( isset($_POST['txPreparedByTitle']) && !empty($_POST['txPreparedByTitle']) ) ? trim($_POST['txPreparedByTitle']) : '';
	$prepared_date = ( isset($_POST['txPreparedDate']) && !empty($_POST['txPreparedDate']) ) ? $_POST['txPreparedDate'] : NULL;

	$received_by = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
	$received_by_title = ( isset($_POST['txReceivedByTitle']) && !empty($_POST['txReceivedByTitle']) ) ? trim($_POST['txReceivedByTitle']) : '';
	$received_date = ( isset($_POST['txReceivedDate']) && !empty($_POST['txReceivedDate']) ) ? $_POST['txReceivedDate'] : NULL;


	$arr = array('emp_id'=>$emp_id,'proj_id'=>$proj_id,'assigned_location'=>$assigned_location,'prepared_by_title'=>$prepared_by_title,'received_by_title'=>$received_by_title,'employer'=>$employer,'employer_add'=>$employer_add,'ppe_date'=>$ppe_date,'prepared_by'=>$prepared_by,'prepared_date'=>$prepared_date,'received_by'=>$received_by,'received_date'=>$received_date);

	if( !empty($emp_id) && !empty($proj_id) ){
		$insertID = $db->insert('ppe',$arr);
		$ppe_no = date('Ymd').$insertID;
		$db->update('ppe',array('ppe_no'=>$ppe_no),array('ppe_id'=>$insertID));
		if($insertID){
			$_SESSION['notif_success']="PPE Issuance Successfully Created!";
			functions::sendTo('admin-ppe-item-manage.php?ppe_id='.functions::encode($insertID));
			die();
		}
		else{
			$_SESSION['notif_warning']='Please Fill up the form properly!';
		}	
	}
	else{
		$_SESSION['notif_warning']='Please Fill up the form properly!';
	}
}


$qEmployer = $db->query('SELECT DISTINCT employer FROM ppe');
$namesEmployer='';
while($rEmployer=$db->fetch_array($qEmployer)):
	$string = preg_replace("/'/",'"',$rEmployer['employer']);
	$namesEmployer .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesEmployer .= '"--"';

$qEmployerAdd = $db->query('SELECT DISTINCT employer_add FROM ppe');
$namesEmployerAdd='';
while($rEmployerAdd=$db->fetch_array($qEmployerAdd)):
	$string = preg_replace("/'/",'"',$rEmployerAdd['employer_add']);
	$namesEmployerAdd .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesEmployerAdd .= '"--"';

$qAssgnLoc = $db->query('SELECT DISTINCT assigned_location FROM ppe');
$namesAssgnLoc='';
while($rAssgnLoc=$db->fetch_array($qAssgnLoc)):
	$string = preg_replace("/'/",'"',$rAssgnLoc['assigned_location']);
	$namesAssgnLoc .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesAssgnLoc .= '"--"';


$qPreparedTitle = $db->query('SELECT DISTINCT prepared_by_title FROM ppe');
$namesPreparedTitle='';
while($rPreparedTitle=$db->fetch_array($qPreparedTitle)):
	$string = preg_replace("/'/",'"',$rPreparedTitle['prepared_by_title']);
	$namesPreparedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPreparedTitle .= '"--"';

$qReceivedTitle = $db->query('SELECT DISTINCT received_by_title FROM ppe');
$namesReceivedTitle='';
while($rReceivedTitle=$db->fetch_array($qReceivedTitle)):
	$string = preg_replace("/'/",'"',$rReceivedTitle['received_by_title']);
	$namesReceivedTitle .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesReceivedTitle .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>IPPE Create</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>IPPE Create</h2>
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
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
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
							<td class="tdSpace" colspan="2" align="left"><input type="text" name="txAssgnLoc" id="txAssgnLoc" style="width:500px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesAssgnLoc;?>]' /></td>
						</tr>
						<tr>
							<th align="right" scope="row">IPPE Date</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<a href="javascript:NewCssCal('txPPEDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
								<input name="txPPEDate" type="text" class="span6 mytextbox" id="txPPEDate" value="<?php echo date('Y-m-d')?>" style="width: 90px;" readonly>
								<span class="help-inline warning" style="font-weight:bold;" id="msgPPEDate" name="msgPPEDate"></span>
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
												<select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:340px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
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
										<td><div align="left"><strong>Prepared and Issued by</strong></div></td>
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
										<td class="tdSpace" align="center">
											<div align="left">
												<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:340px;">
													<option value="">--select--</option>
													<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
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
										<td><div align="left"><strong>Received and Inspected by</strong></div></td>
										<td><div align="center"><strong>Position</strong></div></td>
										<td><div align="center"><strong>Date</strong></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="3"><div align="center"><input type="submit" name="btnAdd" id="btnAdd" value="Create IPPE" class="btn btn-primary btn-small" onClick="return ask()"></div></td>
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
    if(confirm('Do you want to add this IPPE?'))
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