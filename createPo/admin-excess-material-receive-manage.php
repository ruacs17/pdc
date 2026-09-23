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
$selProj = '';$txCheckedBy = '';$txAccountedBy = '';$txReceivedBy = '';$txRemarks = '';$txDate = '';
$merc_id = (isset($_REQUEST['mercid']) && !empty($_REQUEST['mercid']) ) ? functions::decode($_REQUEST['mercid']) : 0;
$_SESSION['notif_id_merc_list']=$merc_id;
$q = $db->select('material_excess_receive','*',array('merc_id'=>$merc_id));
$r = $db->fetch_array($q);
$selProj = $r['proj_id'];
$txCheckedBy = $r['checked_id'];
$txAccountedBy = $r['accounted_id'];
$txReceivedBy = $r['received_id'];
$txDate = $r['merc_date'];
$txRemarks = $r['remarks'];
if( isset($_POST['btnAdd']) ){

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


	$arr = array();
	$selProj = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : NULL;

	$txCheckedBy = ( isset($_POST['txCheckedBy']) && !empty($_POST['txCheckedBy']) ) ? $_POST['txCheckedBy'] : NULL;
	$qEU = $db->select('employee','*',array('emp_id'=>$txCheckedBy));
	$rEU = $db->fetch_array($qEU);
	$checked_name = strtoupper($rEU['fname'].' '.$rEU['lname']);
	$checked_title = position($txCheckedBy);

	$txAccountedBy = ( isset($_POST['txAccountedBy']) && !empty($_POST['txAccountedBy']) ) ? $_POST['txAccountedBy'] : NULL;
	$qEU = $db->select('employee','*',array('emp_id'=>$txAccountedBy));
	$rEU = $db->fetch_array($qEU);
	$accounted_name = strtoupper($rEU['fname'].' '.$rEU['lname']);
	$accounted_title = position($txAccountedBy);

	$txReceivedBy = ( isset($_POST['txReceivedBy']) && !empty($_POST['txReceivedBy']) ) ? $_POST['txReceivedBy'] : NULL;
	$qEU = $db->select('employee','*',array('emp_id'=>$txReceivedBy));
	$rEU = $db->fetch_array($qEU);
	$received_name = strtoupper($rEU['fname'].' '.$rEU['lname']);
	$received_title = position($txReceivedBy);

	$txRemarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
	$txDate = ( isset($_POST['txDate']) && functions::valid_date($_POST['txDate']) ) ? $_POST['txDate'] : NULL;

	$arr = array('proj_id'=>$selProj,'merc_date'=>$txDate,'checked_id'=>$txCheckedBy,'checked_name'=>$checked_name,'checked_title'=>$checked_title,'received_id'=>$txReceivedBy,'received_name'=>$received_name,'received_title'=>$received_title,'accounted_id'=>$txAccountedBy,'accounted_name'=>$accounted_name,'accounted_title'=>$accounted_title,'remarks'=>$txRemarks);
	$_SESSION['notif_warning']='Fail to Add!';
	if( $txDate && $selProj ){
		if($merc_id){//For editing
			$db->update('material_excess_receive',$arr,array('merc_id'=>$merc_id));
			$_SESSION['notif_success']='Changes Saved!';
			unset($_SESSION['notif_warning']);
			functions::sendTo('?mercid='.functions::encode($merc_id));
			die();
		}
		else{
			$insertID = $db->insert('material_excess_receive',$arr);
			if($insertID){
				$series_no = $db->getValue('material_excess_receive','concat(substring(merc_date,1,4),substring(merc_date,6,2),merc_id) as dte',array('merc_id'=>$insertID));
				$db->update('material_excess_receive',array('series_no'=>$series_no),array('merc_id'=>$insertID));
				$_SESSION['notif_success']='New Record Successfully Added!';
				unset($_SESSION['notif_warning']);
				functions::sendTo('admin-excess-material-receive-manage-detail.php?mid='.functions::encode($insertID));
				die();
			}			
		}

	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Excess Material Receive Manage</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Excess Material Receive Manage</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post">
					<table width="70%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th width="20%" align="right" scope="row">&nbsp;</th>
							<td width="2%">&nbsp;</td>
							<td width="70%">&nbsp;</td>
						</tr>
						<tr>
							<th align="right" scope="row">Project</th>
							<td>&nbsp;</td>
							<td style="padding: 25px 0px 4px 0px;">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:800px;" onchange="document.getElementById('rdoCharge1').checked=true;document.getElementById('txPayee').disabled=true;">
									<option value="">--select--</option>
									<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):
									?>
									<option value="<?php echo $rProj['proj_id']?>" <?php if($selProj==$rProj['proj_id']){echo 'selected="selected"';} ?>><?php echo ($rProj['proj_name']);?></option>
									<?php endwhile;?>
								</select>
								<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Purchase Date</th>
							<td>&nbsp;</td>
							<td class="tdSpace">
								<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txDate" id="txDate" value="<?php echo $txDate ?>" required>
								<span class="help-inline warning" style="font-weight:bold;" id="msgTxdate" name="msgTxdate"></span>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Received/Encoded By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txReceivedBy" id="txReceivedBy" data-rel="chosen" style="width:400px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($txReceivedBy==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Checked and Verified By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txCheckedBy" id="txCheckedBy" data-rel="chosen" style="width:400px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($txCheckedBy==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th align="right" scope="row">Accounted By</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left">
								<div align="left">
									<select name="txAccountedBy" id="txAccountedBy" data-rel="chosen" style="width:400px; text-align:left;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($txAccountedBy==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<th align="right" scope="row">Remarks</th>
							<td>&nbsp;</td>
							<td class="tdSpace" colspan="2" align="left"><textarea id="txRemarks" name="txRemarks" rows="2" style="width:300px;"><?php echo $txRemarks; ?></textarea></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td class="tdSpace" colspan="2" align="left"><input type="submit" name="btnAdd" id="btnAdd" value="Save" class="btn btn-primary btn-small"></td>
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
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#txPayee').prop('disabled',true);
	$('#btnAdd').click(function(){
		$('#msgProject').html("");
		$('#msgTxdate').html("");
		if( $('#selProj').val()=="" ){
			$('#msgProject').html("Project Required!");
			$('#selProj').focus();
			res=false;
		}
		else if( $('#txDate').val()=="" ){
			$('#msgTxdate').html("Date Required!");
			$('#txDate').focus();
			res=false;
		}
		else{
			if(ask())
				res=true;
		}
		return res;
	});
	$('#txDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+1 ?>'
	});
});
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