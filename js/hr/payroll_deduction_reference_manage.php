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
$editItem = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
if($editItem)
	$_SESSION['notif_indi']=$editItem;
$pdf_detail='';$pdf_total_amount='';$pdf_payroll_deduction='';$pdf_active_status='';$pdf_date_start='';
$arrPosition=array();
if($editItem){
	$q = $db->select('payroll_deduction_reference','*',array('pdf_id'=>$editItem));
	while($r = $db->fetch_array($q)):
		$pdf_detail = $r['pdf_detail'];
		$pdf_total_amount = functions::formatMoney($r['total_amount']);
		$pdf_payroll_deduction = functions::formatMoney($r['payroll_deduction']);
		$pdf_active_status = $r['active'];
		$pdf_date_start = $r['adjustment_start'];
		#$arrDate = explode("-",$r['adjustment_start']);
		#$pdf_date_start = ( count($arrDate)==3 ) ? $arrDate[1].'/'.$arrDate[2].'/'.$arrDate[0] : '';
	endwhile;
}
$qItemDeduction = $db->query('SELECT DISTINCT pdf_detail FROM payroll_deduction_reference WHERE adjustment_type="deduction" ORDER BY pdf_detail');
$namesDeduction='';
while($rItemDeduction=$db->fetch_array($qItemDeduction)):
	$string = preg_replace("/'/",'"',$rItemDeduction['pdf_detail']);
	$namesDeduction .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesDeduction .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Payroll Deduction</title>
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
if( isset($_POST['btnCancel']) && !empty($eid) ){
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&edtID='.functions::encode($editItem));
	die();
}
if( isset($_POST['btnSave']) && !empty($eid) ){
	$pdf_detail = ( isset($_POST['txDeduction']) ) ? trim($_POST['txDeduction']) : '';
	$pdf_total_amount = ( isset($_POST['txTotalAmount']) && !empty($_POST['txTotalAmount']) ) ? functions::moneyToDouble($_POST['txTotalAmount']) : 0;
	$pdf_payroll_deduction = ( isset($_POST['txParollDeduct']) ) ? functions::moneyToDouble($_POST['txParollDeduct']) : 0;
	$pdf_active_status = ( isset($_POST['selActive']) && !empty($_POST['selActive']) ) ? $_POST['selActive'] : 0;

	$dateStart = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$arrField = array('pdf_detail'=>$pdf_detail,'total_amount'=>$pdf_total_amount,'payroll_deduction'=>$pdf_payroll_deduction,'emp_id'=>$eid,'adjustment_type'=>'deduction','adjustment_start'=>$dateStart,'active'=>$pdf_active_status);
	if($pdf_detail && $dateStart){
		if($editItem){
			$db->update('payroll_deduction_reference',$arrField,array('pdf_id'=>$editItem));
			$_SESSION['notif_success']='Changes Saved!';
			$_SESSION['notif_indi']=$editItem;
			functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&edtID='.functions::encode($editItem));
			die();
		}
		else{
			$ins = $db->insert('payroll_deduction_reference',$arrField);
			if($ins){
				$_SESSION['notif_indi']=$ins;
				$_SESSION['notif_success']='Saved Successfully!';
				functions::sendTo(functions::pageName().'?eid='.functions::encode($eid));
				die();
			}
			else
				functions::say('Please fill up the form properly!');
		}
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>PAYROLL DEDUCTION INFORMATION</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" onSubmit="return ask();">
				<div align="left">Name: <strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong><br><br></div>
				<div align="center">
					<table width="60%" align="center" border="0" class="tablea table-bordered table-hover table-striped">
						<tr>
							<th width="20%" style="padding: 10px"><div align="right">Deduction Name</div></th>
							<td style="padding: 10px"><div align="left"><input type="text" name="txDeduction" id="txDeduction" class="span6" style="width: 350px;" value="<?php echo $pdf_detail;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesDeduction;?>]' required></div></td>
						</tr>
						<tr>
							<th width="20%" style="padding: 10px"><div align="right">Total Amount</div></th>
							<td><div align="left" style="padding: 10px"><input type="text" name="txTotalAmount" id="txTotalAmount" style="width: 144px;" class="span6" value="<?php echo $pdf_total_amount?>" onkeyup="FormatCurrency(this);">&nbsp;&nbsp;&nbsp;<i>(leave blank for infinite total amount)</i></div></td>
						</tr>
						<tr>
							<th width="20%" style="padding: 10px"><div align="right">Deduction Per Payroll</div></th>
							<td><div align="left" style="padding: 10px"><input type="text" name="txParollDeduct" id="txParollDeduct" style="width: 144px;" class="span6" value="<?php echo $pdf_payroll_deduction?>" onkeyup="FormatCurrency(this);" required></div></td>
						</tr>
						<tr>
							<th width="20%" style="padding: 10px"><div align="right">Deduction Start</div></th>
							<td><div align="left" style="padding: 10px"><input type="text" class="input-medium" style="width: 80px;" name="txDate" id="txDate" value="<?php echo $pdf_date_start;?>" required></div></td>
						</tr>
						<tr>
							<th width="20%" style="padding: 10px"><div align="right">Status</div></th>
							<td>
								<div align="left" style="padding: 10px">
									<select name="selActive" id="selActive" style="width:120px;">
										<option value="Active" <?php if($pdf_active_status=="Active"){echo 'selected="selected"';} ?>>Active</option>
										<option value="In-active" <?php if($pdf_active_status=="In-active"){echo 'selected="selected"';} ?>>In-Active</option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<th width="20%"></th>
							<th width="20%" style="padding: 10px">
								<div align="left">
									<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-mini">
									<input type="submit" name="btnCancel" id="btnCancel" value="CANCEL" class="btn btn-mini">
								</div>
							</th>
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
	$('#txDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'1990:<?php echo date('Y')+1 ?>',
	});
});
function ask(){if(confirm('Do you want to save this details?'))return true; else return false;}
function delt(){if(confirm('Do you want to remove this item?'))return true; else return false;}
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
</body>
</html>