<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$benefit = (isset($_REQUEST['benType']) && !empty($_REQUEST['benType']) ) ? functions::decode($_REQUEST['benType']) : 0;
$benefit_type='';$benefit_name='';$page='';
if($benefit=='sss'){
	$benefit_type="sss_contribution";
	$benefit_name = "SSS";
	$page='contribution_sss_reference.php';
}
else if($benefit=='philhealth'){
	$benefit_type="philhealth_contribution";
	$benefit_name = "Philhealth";
	$page='contribution_ph_reference.php';
}
else if($benefit=='pagibig'){
	$benefit_type="pagibig_contribution";
	$benefit_name = "Pagibig";
	$page='contribution_pagibig_reference.php';
}
$salary_monthly = $db->getValue('emp_salary','es_salary',array('emp_id'=>$eid),'ORDER BY es_date DESC');
$assign_ebr_id = $db->getValue('emp_benefit_ref','ebr_id',array('emp_id'=>$eid,'benefit_type'=>$benefit),'ORDER BY date_start DESC LIMIT 1');
$contribution = ($benefit_type) ? $db->getValue('employee',$benefit_type,array('emp_id'=>$eid)) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Benefit Management</title>
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
if( isset($_POST['btnSave']) && !empty($eid) && !empty($benefit_type) ){
	$adjustment_value = ( isset($_POST['txAdjustmentVal']) ) ? functions::moneyToDouble($_POST['txAdjustmentVal']) : 0;
	$db->update('employee',array($benefit_type=>$adjustment_value),array('emp_id'=>$eid));
	functions::say('Changes Saved!');
	functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid).'&benType='.functions::encode($benefit));
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>PAYROLL DEDUCTION MANAGE</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="right"><a id="btnsss" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'<?php echo $page?>?eid=<?php echo functions::encode($eid)?>','Add Contribution Schedule')">Reference</a></div>
				<div align="left">Name: <strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong><br><br></div>
				<div align="center">
					<div style="width:700px;">

					<?php
					$assign_sal_from='';$assign_sal_to='';
					if( $assign_ebr_id ){
						$qRef = $db->select('emp_benefit_ref','*',array('ebr_id'=>$assign_ebr_id));
						$rRef = $db->fetch_array($qRef);
					?>
					<div align="left"><h2>Currently Assigned <?php echo $benefit_name;?> Bracket</h2></div>
					<table class="table table-bordered table-hover" style="font-size: 12px;">
						<thead>
							<tr style="background-color:#CCC">
								<th scope="col"><div align="left">SALARY</div></th>
								<th scope="col"><div align="left">RANGE OF COMPENSATION </div></th>
								<th scope="col"><div align="right">EE SHARE</div></th>
								<th scope="col"><div align="right">ER SHARE</div></th>
								<th scope="col"><div align="right">EC</div></th>
								<th scope="col"><div align="right">TOTAL</div></th>
								<th scope="col" width="15%"><div align="center">DATE START</div></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><div align="left"><?php echo functions::formatMoney($salary_monthly)?></div></td>
								<td><div align="left"><?php echo functions::formatMoney($rRef['sal_from']).' - '.functions::formatMoney($rRef['sal_to'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rRef['er_share'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rRef['ee_share'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rRef['ec'])?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($rRef['benefit_total'])?></div></td>
								<td><div align="center"><?php echo functions::datearr($rRef['date_start']) ?></div></td>
							</tr>
						</tbody>
					</table>
					<?php }else{?>
						<div align="left">
							<h2>No Assigned <?php echo $benefit_name;?> Bracket, Please choose from the reference list. <a id="btnsssh" class="thickbox" style="cursor:pointer;" onclick="showThis(this.id,'<?php echo $page?>?eid=<?php echo functions::encode($eid)?>','Add Contribution Schedule')">Click here</a>.</h2>
							<div>Current Monthly Salary: <strong><?php echo functions::formatMoney($salary_monthly)?></strong></div>
						</div>
					<?php }?>
					<div style="padding-top:30px;">&nbsp;</div>
					<table width="100%" align="center" border="0" class="tablea table-bordered table-striped" style="font-size: 12px;">
						<thead>
						<tr style="background-color:#CCC">
							<th height="30" style="padding: 10px"  width="45%"><div align="left">DEDUCTION NAME</div></th>
							<th width="30%"><div align="center">DEDUCTION AMOUNT PER PAYROLL</div></th>
							<th width="14%">&nbsp;</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td height="30" style="padding: 10px"><div align="left"><?php echo $benefit_name ?></div></td>
							<td style="padding: 10px"><div align="center"><input type="text" name="txAdjustmentVal" id="txAdjustmentVal" style="width: 144px;" class="span6" value="<?php echo $contribution?>" onkeyup="FormatCurrency(this);"></div></td>
							<td style="padding: 10px"><div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small"></div></td>
						</tr>
						</tbody>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}
</script>
</body>
</html>