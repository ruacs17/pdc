<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
require_once('../class/voucher_advance.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$arr = array();
$txbYear=date('Y');
$supplierID='';
$txbMon=date('m');

$txbYear = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : '';
$txbMon = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
if($txbMon && $txbYear)
	$arr = array('LEFT(vdate,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
	$arr = array('LEFT(vdate,4)'=>$txbYear);

$qDate = $db->select('voucher','DISTINCT LEFT(vdate,7) as dte',$arr,'ORDER BY vdate');
$count=0;
$monthName='';  
if($txbMon && $txbYear){
	$time = mktime(0,0,0,$txbMon,1,$txbYear);
	$monthName = date('F',$time) .' '.$txbYear;
}
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
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Witholding Tax Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">.padParLeft{padding-left:10px;}.padAmLeft{padding-left:60px;}</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table border="0" align="center">
	<thead>
		<tr>
			<td>
				<?php 
				require_once('../class/print_header.php');
				print_header($monthName.' Tax Withheld');
				?><br>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<table width="98%" align="center" border="0" class="table table-bordered" style="font-size:12px;">
					<tr>
						<td width="10%"><strong>Month</strong></td>
						<td width="45%" height="30"><strong>Supplier / Payee</strong></td>
						<td width="15%"><div align="right" style="padding-right:30px;"><strong>Amount</strong></div></td>
					</tr>
					<?php
					$curMonth='';
					$totalCost=0;
					$monthlyCost=0;
					$cost=0;
					$rank=0;
					$monthlyWitholding=0;
					$totalWitholding=0;
					while($rDate = $db->fetch_array($qDate)):
						$monthlyWitholding=0;
						$rank++;
						$time = mktime(0,0,0,substr($rDate['dte'],5,2),1,$txbYear);
						$monthName = date('M',$time);
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
						$tin = $db->getValue('supplier','tin',array('supplierID'=>$rVoucher['supplierID']));
						$count++;
					?>
					<tr>
						<td>&nbsp;</td>
						<td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])); echo ($tin) ? ' : <i>'.$tin.'</i>' : '';?></td>
						<td><div align="right" style="padding-right:30px;"><?php echo functions::formatMoney($witholding_tax);?></div></td>
					</tr>
					<?php endwhile;#endwhile $rVoucher?>
					<tr>
						<td>&nbsp;</td>
						<td height="30"><div align='right'><strong>Monthly Withholding TAX of <?php echo date('F',$time).' '.$txbYear;?></strong></div></td>
						<td><div align="right" style="padding-right:30px;"><strong><?php echo functions::formatMoney($monthlyWitholding);?></strong></div></td>
					</tr>  
					<?php 
					$monthlyCost=0;
					endwhile;#endwhile PODate?>
					<tr>
						<td height="30"></td>
						<td><div align="right"><strong>Yearly Withholding TAX</strong></div></td>
						<td><div align="right" style="padding-right:30px;"><strong><?php echo functions::formatMoney($totalWitholding);?></strong></div></td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="dash-witholding-tax.php?y=<?php echo functions::encode($txbYear)?>&m=<?php echo functions::encode($txbMon)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>