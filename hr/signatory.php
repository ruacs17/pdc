<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$searchVal='1';
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));

$form_type = (isset($_REQUEST['ft']) && !empty($_REQUEST['ft']) ) ? $_REQUEST['ft'] : '';
$arr_form=array();
if($form_type=='leave'){
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Noted By','variable'=>'noted_by');   
}
else if($form_type=='overtime'){
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Reviewed By','variable'=>'reviewed_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Checked By','variable'=>'checked_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Verified By','variable'=>'verified_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Approved By','variable'=>'approved_by'); 
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Noted By','variable'=>'noted_by');
}
else if($form_type=='travel'){
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Requested By','variable'=>'requested_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Reviewed By','variable'=>'reviewed_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Checked By','variable'=>'checked_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Verified By','variable'=>'verified_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Approved By','variable'=>'approved_by');
}
else if($form_type=='payroll'){
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Prepared By','variable'=>'prepared_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Checked By','variable'=>'checked_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Received By','variable'=>'received_by');
	$arr_form[]=array('sig_form'=>$form_type,'title'=>'Approved By','variable'=>'approved_by');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Signatory</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
function manageSignatory($sig_form,$sig_position,$emp_id){    
	global $db;
	if( $db->getValue('signatories','count(*)',array('sig_form'=>$sig_form,'sig_position'=>$sig_position))==0 )
		$db->insert('signatories',array('sig_form'=>$sig_form,'sig_position'=>$sig_position,'emp_id'=>$emp_id));        
	else
		$db->update('signatories',array('emp_id'=>$emp_id),array('sig_form'=>$sig_form,'sig_position'=>$sig_position));
}
if ( isset($_POST['btnSave']) ) {
	foreach($arr_form as $form):
		$selectedEmp = (isset($_POST[$form['variable']]) && !empty($_POST[$form['variable']]) ) ? $_POST[$form['variable']] : NULL;
		manageSignatory($form['sig_form'],$form['title'],$selectedEmp);
	endforeach;
	$_SESSION['notif_success']='Changes saved!';
	functions::sendTo(functions::pageName().'?ft='.$form_type);
	die();
}
?>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>SETTING DEFAULT SIGNATORY</h2></div>
				<div align="center" style="padding-bottom: 15px;">
					<select name="selType" onChange="window.location='<?php functions::pageName()?>?ft='+this.value">
						<option value="">--Select Form--</option>
						<option value="leave" <?php if($form_type=='leave'){echo 'selected="selected"';} ?>>Leave</option>
						<option value="overtime" <?php if($form_type=='overtime'){echo 'selected="selected"';} ?>>Overtime</option>
						<option value="travel" <?php if($form_type=='travel'){echo 'selected="selected"';} ?>>Travel Order</option>
						<option value="payroll" <?php if($form_type=='payroll'){echo 'selected="selected"';} ?>>Payroll</option>
					</select>
				</div>
				<input type="hidden" name="form_type" value="<?php echo $form_type ?>">
				<div align="center">
					<div style="width:700px;">
						<table class="table table-hover table-bordered" border="0" style="font-size: 12px;">
						<?php
						foreach($arr_form as $form):
							$selEmp = $db->getValue('signatories','emp_id',array('sig_form'=>$form['sig_form'],'sig_position'=>$form['title']));
						?>
							<tr>
								<td><div style="padding-top: 5px"><strong><?php echo $form['title']; ?></strong></div></td>
								<td>
									<div style="padding-top: 5px">
										<select name="<?php echo $form['variable'] ?>" id="<?php echo $form['variable'] ?>" data-rel="chosen" style="width:400px; text-align:left;">
											<option value="">--select--</option>
											<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
											while($rEU = $db->fetch_array($qEU)):
											?>
											<option value="<?php echo $rEU['emp_id']?>" <?php if($selEmp==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
											<?php endwhile;?>
										</select>
									</div>
								</td>
							</tr>
						<?php
						endforeach;
					if(count($arr_form)){
					?>
							<tr>
								<td></td>
								<td><input type="submit" name="btnSave" value="Save" class="btn btn-primary btn-small"></td>
							</tr>
					<?php }?>
						</table>
					</div>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
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