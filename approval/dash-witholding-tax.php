<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/voucher_advance.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$arr = array();
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');

$txbYear = (isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$txbMon = (isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : 0;
$txbYear = isset($_SESSION['wtYr']) ? $_SESSION['wtYr'] : $txbYear;
$txbMon = isset($_SESSION['wtMn']) ? $_SESSION['wtMn'] : $txbMon;
if(isset($_POST['btnSearch'])){
	$_SESSION['wtYr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : date('Y');
	$_SESSION['wtMn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
	functions::sendTo(functions::pageName());
	die();
}
if($txbMon && $txbYear)
	$arr = array('LEFT(cheque_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
	$arr = array('LEFT(cheque_date,4)'=>$txbYear);
$qDate = $db->select('voucher','DISTINCT LEFT(cheque_date,7) as dte',$arr,'ORDER BY cheque_date');
$count=0;

if($txbMon && $txbYear)
	$qHasAdvance = $db->query('SELECT DISTINCT voucher_id_owner FROM voucher_advance_payment vap, voucher v WHERE vap.voucher_id_owner=v.voucher_id AND LEFT(vdate,7)="'.$db->clean($txbYear.'-'.$txbMon).'"');
elseif($txbYear)
	$qHasAdvance = $db->query('SELECT DISTINCT voucher_id_owner FROM voucher_advance_payment vap, voucher v WHERE vap.voucher_id_owner=v.voucher_id AND LEFT(vdate,4)="'.$db->clean($txbYear).'"');
else
	$qHasAdvance = $db->query('SELECT DISTINCT voucher_id_owner FROM voucher_advance_payment vap, voucher v WHERE vap.voucher_id_owner=v.voucher_id');

$arrVoucherAdvanceID=array();
while($rHasAdvance = $db->fetch_array($qHasAdvance)):
	$arrVoucherAdvanceID[$rHasAdvance['voucher_id_owner']]=$rHasAdvance['voucher_id_owner'];
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Withholding Tax</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Withholding Tax</h2>
		</div>
		<div class="box-content" align="center">
			<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
				<tr>
					<td width="50%"><div align="right"><a id="whprint" class="btn btn-info" href="dash-witholding-tax-print.php?y=<?php echo functions::encode($txbYear)?>&m=<?php echo functions::encode($txbMon)?>"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div></td>
				</tr>
			</table>
			<form method="post">
				<table width="26%" border="0">
					<tr>
						<td align="left" style="padding: 12px 0px 4px 0px">
							<select name="bdYear" id="bdYear" style="width:90px;">
								<option value="">Select Year</option>
								<?php
								$qYr = $db->select('voucher','DISTINCT LEFT(vdate,4) as yr',array(),'WHERE vdate IS NOT NULL ORDER BY vdate DESC');
								while($rYr = $db->fetch_array($qYr)):
								?>
								<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
								<?php endwhile;?>
							</select>
							<select name="bdMon" id="bdMon" style="width:95px;">
								<option value="">All Month</option>
								<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
								<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
								<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
								<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
								<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
								<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
								<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
								<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
								<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
								<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
								<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
								<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
							</select>
						</td>
						<td valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary"></td>
					</tr>
				</table><br><br><br>
				<div style="width:70%;">
				<table width="50%" align="center" border="0" class="table table-bordered table-hover table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<td width="15%"><strong>Month</strong></td>
							<td width="70%" height="30"><strong>Supplier / Payee</strong></td>
							<td width="15%"><div align="right"><strong>Amount</strong></div></td>
						</tr>
					</thead>
					<tbody>
						<?php
						$curMonth='';$totalCost=0;$monthlyCost=0;$cost=0;$rank=0;$monthlyWitholding=0;$totalWitholding=0;
						while($rDate = $db->fetch_array($qDate)):
							$monthlyWitholding=0;
							$rank++;
							$time = mktime(0,0,0,substr($rDate['dte'],5,2),1,$txbYear);
							$monthName = date('F',$time);
							$txbMon = date('m',$time);
						?>
						<tr>
							<td height="30"><strong><?php echo $monthName.' '.$txbYear;?></strong></td>
							<td></td>
							<td></td>
						</tr>
						<?php
						$qVoucher = $db->select('voucher v, supplier s','s.supplierID,sum(witholding_tax) as wt',array('LEFT(vdate,7)'=>$rDate['dte']),'AND v.supplierID=s.supplierID GROUP BY v.supplierID HAVING wt > 0 ORDER BY s.name');
						$total=0;
						$witholding_tax=0;
						$advance_payment=0;
						$voucher_advance_payment=0;
						while($rVoucher = $db->fetch_array($qVoucher)):
							$supplierWitholdingTax=0;
							$qTaxCompute = $db->select('voucher','*',array('LEFT(vdate,7)'=>$rDate['dte'],'supplierID'=>$rVoucher['supplierID']));
							while($rTC = $db->fetch_array($qTaxCompute)):
								//Getting surchages
								$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rTC['voucher_id'],'charge_type'=>'surcharge'));

								$amount_q = $db->query('SELECT SUM(vd.amount_issue)  FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rTC['voucher_id']).'"');
								$non_po = $db->result($amount_q);

								$amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rTC['voucher_id']).'"');
								$po = $db->result($amount_po_q);
								$amount = $non_po + $po + $otherSurcharge;

								$wtax = ($rTC['witholding_tax'] > 0) ? ($rTC['witholding_tax'] / 100) : 0;
								$wtax_vat = $rTC['witholding_vat'];

								$witholding = ($wtax_vat==1) ? $wtax * ($amount / 1.12) : $wtax * $amount;

								if( isset($arrVoucherAdvanceID[$rTC['voucher_id']]) ){
									$va = new voucherAdvance($rTC['voucher_id']);
									$witholding = $va->current_voucher_withholding_tax_amount;
								}
								$supplierWitholdingTax += $witholding;
							endwhile;

							$witholding_tax = $supplierWitholdingTax;
							$monthlyWitholding += $witholding_tax;
							$totalWitholding += $witholding_tax;
							$count++;
							$tin = $db->getValue('supplier','tin',array('supplierID'=>$rVoucher['supplierID']));
						?>  
						<tr>
							<td>&nbsp;</td>
							<td height="30"><?php echo '<strong>'.$db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])).'</strong>'; echo ($tin) ? ' : <i>'.$tin.'</i>' : '';?></td>
							<td><div align='right'><a id="costdetail<?php echo $count?>" class="label label-info thickbox" title="View Withholding Tax Details" data-rel="tooltip" onclick="showThis(this.id,'dash-witholding-tax-detail.php?yr=<?php echo functions::encode($txbYear)?>&mn=<?php echo functions::encode($txbMon)?>&sup=<?php echo functions::encode($rVoucher['supplierID'])?>','Witholding Tax Details','1')"><?php echo functions::formatMoney($witholding_tax);?></a></div></td>
						</tr>
						<?php endwhile;#endwhile $rVoucher?>
						<tr>
							<td>&nbsp;</td>
							<td height="30"><div align='right'><strong>Monthly Withholding TAX of <?php echo $monthName.' '.$txbYear;?></strong></div></td>
							<td><div align='right'><strong><?php echo functions::formatMoney($monthlyWitholding);?></strong></div></td>
						</tr>  
						<?php 
						$monthlyCost=0;
						endwhile;#endwhile PODate?>
						<tr>
							<td height="30"></td>
							<td><div align="right"><strong>Yearly Withholding TAX</strong></div></td>
							<td><strong><?php echo functions::formatMoney($totalWitholding);?></strong></td>
						</tr>
					</tbody>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>