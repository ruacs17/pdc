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
$pos_idEdt = (isset($_REQUEST['id']) && !empty($_REQUEST['id']) ) ? functions::decode($_REQUEST['id']) : '';
$prepared_by='';$txIssuedDate=date('Y-m-d');$reviewed_by='';$received_by='';$approved_by='';
if($pos_idEdt){
	$qedt = $db->select('po_issuance','*',array('pos_id'=>$pos_idEdt));
	$redt = $db->fetch_array($qedt);
	$prepared_by = $redt['prepared_by'];
	$txIssuedDate = $redt['issue_date'];
	$reviewed_by = $redt['reviewed_by'];
	$received_by = $redt['received_by'];	
	$approved_by = $redt['approved_by'];
}

if( isset($_POST['btnSave']) ){
	$arr = array();
	$prepared_by = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : NULL;
	$txIssuedDate = ( isset($_POST['txIssuedDate']) && !empty($_POST['txIssuedDate']) ) ? $_POST['txIssuedDate'] : NULL;
	$reviewed_by = ( isset($_POST['txReviewedBy']) && !empty($_POST['txReviewedBy']) ) ? $_POST['txReviewedBy'] : NULL;
	$received_by = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
	$approved_by = ( isset($_POST['txApprovedBy']) && !empty($_POST['txApprovedBy']) ) ? $_POST['txApprovedBy'] : NULL;

	$arr = array('issue_date'=>$txIssuedDate,'prepared_by'=>$prepared_by,'reviewed_by'=>$reviewed_by,'received_by'=>$received_by,'approved_by'=>$approved_by);

	if( !empty($txIssuedDate) && !empty($prepared_by) ){
		if($pos_idEdt){
			$db->update('po_issuance',$arr,array('pos_id'=>$pos_idEdt));
			$_SESSION['notif_add']='Issuance Successfully Updated!';
			functions::sendTo('po-issuance-manage-detail.php?id='.functions::encode($pos_idEdt));
			die();
		}
		else{
			$insertID = $db->insert('po_issuance',$arr);
			$series_no = date('Ymd').$insertID;
			$db->update('po_issuance',array('series_no'=>$series_no),array('pos_id'=>$insertID));
			$_SESSION['notif_add']='Issuance Successfully Created!';
			if($insertID)
				functions::sendTo('po-issuance-manage-detail.php?id='.functions::encode($insertID));
			die();
		}

	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Issuance Manage</title>
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
	<style>.tdSpace{padding: 12px 0px 4px 10px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Issuance Form</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="70%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="25%" align="right" scope="row">&nbsp;</th>
							<td width="50%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Issuance Date</th>
							<td class="tdSpace">
								<a href="javascript:NewCssCal('txIssuedDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
								<input name="txIssuedDate" type="text" class="span6 mytextbox" id="txIssuedDate" value="<?php echo $txIssuedDate?>" style="width: 90px;" readonly>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Prepared and Issued by</th>
							<td class="tdSpace">
								<select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:440px;" required>
									<option value="">--select--</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>" <?php if($prepared_by==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Reviewed By</th>
							<td class="tdSpace">
								<select name="txReviewedBy" id="txReviewedBy" data-rel="chosen" style="width:440px;">
									<option value="">--select--</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>" <?php if($reviewed_by==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Approved By</th>
							<td class="tdSpace">
								<select name="txApprovedBy" id="txApprovedBy" data-rel="chosen" style="width:440px;">
									<option value="">--select--</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>" <?php if($approved_by==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Checked and Received by</th>
							<td class="tdSpace">
								<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:440px;">
									<option value="">--select--</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>" <?php if($received_by==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="2" align="center" height="30">&nbsp;</td>
						</tr>
						<tr>
							<td class="tdSpace" colspan="2">
								<div align="center">
									<input type="submit" name="btnSave" id="btnSave" onClick="return ask()" value="<?php echo ($pos_idEdt) ? 'Update' : 'Create' ?> ISSUANCE" class="btn btn-primary btn-small">
									<?php if($pos_idEdt){ ?>
									<a href="po-issuance-manage-detail.php?id=<?php echo functions::encode($pos_idEdt)?>" class="btn btn-small">Back</a>
									<?php } ?>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function ask(){
    if(confirm('Do you want to save this ISSUANCE?'))
        return true;
    else
        return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>