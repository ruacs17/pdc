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
$itemID = (isset($_REQUEST['itmID']) && !empty($_REQUEST['itmID']) ) ? functions::decode($_REQUEST['itmID']) : 0;
$pdf_detail='';$pdf_total_amount='';$pdf_payroll_deduction='';
$arrPosition=array();
if($editItem){
	$q = $db->select('payroll_deduction_reference','*',array('pdf_id'=>$editItem));
	while($r = $db->fetch_array($q)):
		$pdf_detail = $r['pdf_detail'];
		$pdf_total_amount = functions::formatMoney($r['total_amount']);
		$pdf_payroll_deduction = functions::formatMoney($r['payroll_deduction']);
	endwhile;
}
$qItemDeduction = $db->query('SELECT DISTINCT pdf_detail FROM payroll_deduction_reference ORDER BY pdf_detail');
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
	functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid).'&edtID='.functions::encode($editItem));
}
if( isset($_POST['btnSave']) && !empty($eid) ){
	$pdf_detail = ( isset($_POST['txDeduction']) ) ? trim($_POST['txDeduction']) : '';
	$pdf_total_amount = ( isset($_POST['txTotalAmount']) ) ? functions::moneyToDouble($_POST['txTotalAmount']) : 0;
	$pdf_payroll_deduction = ( isset($_POST['txParollDeduct']) ) ? functions::moneyToDouble($_POST['txParollDeduct']) : 0;
	$arrField = array('pdf_detail'=>$pdf_detail,'total_amount'=>$pdf_total_amount,'payroll_deduction'=>$pdf_payroll_deduction,'emp_id'=>$eid);
	if($pdf_detail){
		if($editItem){
			$db->update('payroll_deduction_reference',$arrField,array('pdf_id'=>$editItem));
			functions::say('Changes Saved!');
			functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid).'&edtID='.functions::encode($editItem));
		}
		else{
			$db->insert('payroll_deduction_reference',$arrField);
			functions::say('Saved Successfully!');
			functions::sendTo($_SERVER['PHP_SELF'].'?eid='.functions::encode($eid));
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>PAYROLL DEDUCTION HISTORY</h2></div>
				<div align="left">Name: <strong><?php echo $db->getValue('employee','concat(lname, ", ", fname)',array('emp_id'=>$eid));?></strong><br><br></div>
				<div align="center">
					<table width="60%" align="center" border="0" class="tablea table-bordered table-hover table-striped">
						<tr>
							<th><div align="center" >Payroll Number</div></th>
							<th style="padding: 20px" ><div align="center">Payroll Date</div></th>
							<th><div align="center" >Payment</div></th>
						</tr>
						<?php
						$count=0;$total_payment=0;
						$q = $db->select('payroll_adjustment pa, emp_attendance ea','*',array('pdf_id'=>$itemID),'AND pa.eat_id=ea.eat_id');
						while($r = $db->fetch_array($q)):
							$count++;
							$total_payment+=$r['adjustment_value'];
						?>
						<tr>
							<td><div align="center"><?php echo $r['payroll_no']?></div></td>
							<td style="padding: 10px"><div align="center"><?php echo functions::datearr($r['date_start']).' - '.functions::datearr($r['date_end'])?></div></td>
							<td><div align="center"><?php echo functions::formatMoney($r['adjustment_value'])?></div></td>
						</tr>
						<?php endwhile;?>
						<?php
						if($count){?>
							<tr>
								<td colspan="2" style="padding: 10px"><div align="right"><strong>Total Payment </strong></div></td>
								<td style="padding: 10px"><div align="center"><strong><?php echo functions::formatMoney($total_payment); ?></strong></div></td>
							</tr>
						<?php }?>
						<?php
						if($count==0){?>
							<tr>
								<td colspan="3" style="padding: 10px"><div align="center">--Nothing to Report--</div></td>
							</tr>
						<?php }?>
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
function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}
</script>
</body>
</html>