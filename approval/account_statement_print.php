<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));

require_once('../class/voucher_advance.php');
function monthDays($month=0,$year=0){
	$list = array();
	$month = ($month) ? $month : date('m');
	$year = ($year) ? $year : date('Y');
	for($d=1; $d<=31; $d++):
		$time = mktime(12,0,0,$month,$d,$year);
		if( date('m',$time)==$month )
			$list[]=date('Y-m-d',$time);
	endfor;
	return $list;
}

$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$ac_type = ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) ) ? functions::decode($_REQUEST['t']) : '';

$account_type = ( isset($_SESSION['as_acType']) && !empty($_SESSION['as_acType']) ) ? $_SESSION['as_acType'] : $ac_type;
$selMonth = ( isset($_SESSION['as_selMonth']) && !empty($_SESSION['as_selMonth']) ) ? $_SESSION['as_selMonth'] : $mn;
$year = ( isset($_SESSION['as_selYr']) && !empty($_SESSION['as_selYr']) ) ? $_SESSION['as_selYr'] : $yr;
if($year)
	$qHasAdvance = $db->query('SELECT DISTINCT voucher_id_owner FROM voucher_advance_payment vap, voucher v WHERE vap.voucher_id_owner=v.voucher_id AND v.vt_id="'.$db->clean($account_type).'" AND LEFT(cheque_date,4)="'.$db->clean($year).'"');
else
	$qHasAdvance = $db->query('SELECT DISTINCT voucher_id_owner FROM voucher_advance_payment vap, voucher v WHERE vap.voucher_id_owner=v.voucher_id AND v.vt_id="'.$db->clean($account_type).'"');

$arrVoucherAdvanceID=array();
while($rHasAdvance = $db->fetch_array($qHasAdvance)):
	$arrVoucherAdvanceID[$rHasAdvance['voucher_id_owner']]=$rHasAdvance['voucher_id_owner'];
endwhile;
$account_name = $db->getValue('voucher_type','vt_name',array('vt_id'=>$account_type));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Account Statement Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/printerfoot.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:50px;}
	.padAmLeft{padding-left:60px;}
	</style>
	<script>window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="90%" border="0" align="center">

	<tr>
		<td>
			<?php 
			require_once('../class/print_header.php');
			print_header('ACCOUNT STATEMENT OF '.$account_name);
			?>
		</td>
	</tr>
	<tr>
		<td valign='bottom'>
		</td>
	</tr>
	<tr>
		<td height="500" valign="top" align="center">
			<table class="tablea table-bordereda" border="1" cellpadding="3" style="font-size:10px;">
				<thead>
					<tr>
						<th width="10%">Date</th>
						<th width="37%">Transactions</th>
						<th width="9%">Check Number</th>
						<th width="15%">DEBIT</th>
						<th width="13%">CREDIT</th>
						<th width="12%">BALANCE</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$balance=0;$totalDebit=0;$totalCredit=0;$monthlyDebit=0;$monthlyCredit=0;
				for($m = 1; $m <= 12; $m++):
					$monthlyCredit=0;$monthlyDebit=0;
					$time = mktime(0,0,0,$m,1,$year);
					$monthName = date('F',$time);
					$days = monthDays($m,$year);

					if($selMonth==$m || $selMonth == ''){
						echo '
						<tr>
							<td colspan="8" height="25px"><div align="left"><strong>Month Of '.$monthName.'</strong></div></td>
						</tr>
						';
					}
					foreach($days as $day):
						$qCredit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'credit','account_type'=>$account_type));
						while($rCredit = $db->fetch_array($qCredit)):
							$credID = $rCredit['as_id'];
							if($rCredit['confirmned']==2){
								$balance += $rCredit['as_amount'];
								$balance = ($balance) ? round($balance,2) : $balance;
								$totalCredit += $rCredit['as_amount'];
								$monthlyCredit += $rCredit['as_amount'];
							}
							$project_income = ($rCredit['project_income']) ? $rCredit['project_income'] : 0;
							$project_id = $db->getValue('project_income','proj_id',array('pi_id'=>$project_income));
							$project_name = ($project_id) ? ' <i>('.$db->getValue('project','proj_name',array('proj_id'=>$project_id)).')</i>' : '';
							if($selMonth==$m || $selMonth == ''){
								$balanceDisplay = $balance;
								echo '
								<tr>
									<td height="10px">'.functions::datearr($day).'</td>
									<td>'.$rCredit['transaction'].$project_name.'</td>
									<td></td>
									<td></td>
									<td align="right">'.functions::formatMoney($rCredit['as_amount']).'</td>
									<td align="right">'.functions::formatMoney($balanceDisplay).'</td>
								</tr>
								';
							}
						endwhile; #while Credit

						$qDebit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'debit','account_type'=>$account_type));
						while($rDebit = $db->fetch_array($qDebit)):
							$debID = $rDebit['as_id'];
							if($rDebit['confirmned']==2){
								$totalDebit += $rDebit['as_amount'];
								$balance -= $rDebit['as_amount'];
								$balance = ($balance) ? round($balance,2) : $balance;
								$monthlyDebit += $rDebit['as_amount'];
							}
							if($selMonth==$m || $selMonth == ''){
								$balanceDisplay = $balance;
								echo '
								<tr>
									<td height="10px">'.functions::datearr($day).'</td>
									<td>'.$rDebit['transaction'].'</td>
									<td></td>
									<td align="right">'.functions::formatMoney($rDebit['as_amount']).'</td>
									<td></td>
									<td align="right">'.functions::formatMoney($balanceDisplay).'</td>
								</tr>
								';
							}
						endwhile; #end while Debit
						#voucher or supplier for debit
						$qVoucher = $db->select('voucher','*',array('cheque_date'=>$day,'vt_id'=>$account_type));
						while($rVoucher = $db->fetch_array($qVoucher)):
							$vid = $rVoucher['voucher_id'];
							$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'deduction'));
							$claimed = $rVoucher['claimed'];
							$amountDebit=0;
							$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVoucher['voucher_id'],'charge_type'=>'surcharge'));
							$amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
							$non_po = $db->result($amount_q);

							$amount_non_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
							$po = $db->result($amount_non_po_q);
							$voucher_amount = $non_po + $po + $otherSurcharge;

							$wtax = ($rVoucher['witholding_tax'] > 0) ? ($rVoucher['witholding_tax'] / 100) : 0;

							$withholding_amount = ($rVoucher['witholding_vat']==1) ? $wtax * (($voucher_amount) / 1.12) : $wtax * $voucher_amount;
							$amountDebit = ($wtax) ? $voucher_amount - $withholding_amount : $voucher_amount;

							$withAdvance=0;
							if( isset($arrVoucherAdvanceID[$rVoucher['voucher_id']]) ){
								$withAdvance=1;
								$va = $a = new voucherAdvance($rVoucher['voucher_id']);
								$amountDebit = $va->current_voucher_payable_amount;
								$withholding_amount = $va->current_voucher_withholding_tax_amount;
							}
							if($otherDeduction)
								$amountDebit -= $otherDeduction;

							if($claimed==1){
								$totalDebit += $amountDebit;
								$balance -= $amountDebit;
								$balance = ($balance) ? round($balance,2) : $balance;
								$monthlyDebit += $amountDebit;
							}
							if($selMonth==$m || $selMonth == ''){
								$balanceDisplay = $balance;
								echo '
								<tr>
									<td>'.functions::datearr($day).'</td>
									<td>'.$db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])).'</td>
									<td align="center">'.$rVoucher['cheque_id'].'</td>
									<td align="right">'.functions::formatMoney($amountDebit);
									echo ($wtax) ? '<br><i font-size="9px">withholding: ('.functions::formatMoney($withholding_amount).')</i>' : '';
									echo '
									</td>
									<td></td>
									<td align="right">'.functions::formatMoney($balanceDisplay).'</td>
								</tr>
								';
							}
						endwhile;#end while Voucher or supplier for debit
					endforeach; #end for each day
					if($selMonth==$m || $selMonth == ''){
						$balanceDisplay = $balance;
						echo '
						<tr>
							<td>&nbsp;</td>
							<td colspan="2"><div align="right"><strong> End of '.$monthName.' '.$year.'</strong></div></td>
							<td align="right"><strong>'.functions::formatMoney($monthlyDebit).'</strong></td>
							<td align="right"><strong>'.functions::formatMoney($monthlyCredit).'</strong></td>
							<td><strong></strong></td>
						</tr>
						';
						echo '
						<tr>
							<td>&nbsp;</td>
							<td colspan="2"><div align="right"><strong>Cumulative report until '.$monthName.' '.$year.'</strong></div></td>
							<td align="right"><strong>'.functions::formatMoney($totalDebit).'</strong></td>
							<td align="right"><strong>'.functions::formatMoney($totalCredit).'</strong></td>
							<td align="right"><strong>'.functions::formatMoney($balanceDisplay).'</strong></td>
						</tr>
						';
					}
				endfor; #end for each month
				$balanceDisplay = $balance;
				?>
				<tr>
					<td colspan="3"><div align="right"><strong>End of Year <?php echo $year?></strong></div></td>
					<td align="right"><strong><?php echo functions::formatMoney($totalDebit);?></strong></td>
					<td align="right"><strong><?php echo functions::formatMoney($totalCredit);?></strong></td>
					<td align="right"><strong><?php echo functions::formatMoney($balanceDisplay);?></strong></td>
				</tr>
			</tbody>
		</table>
	</td>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>

<!-- end: JavaScript-->
</body>
</html>