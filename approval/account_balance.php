<?php require_once('templ_up.php');?>
<?php
require_once('../class/voucher_advance.php');
if( isset($_REQUEST['DswrW']) ){

	$selMonth = ( isset($_REQUEST['bdMon']) && !empty($_REQUEST['bdMon']) ) ? functions::decode($_REQUEST['bdMon']) : '';
	$account_type = ( isset($_REQUEST['txVoType']) && !empty($_REQUEST['txVoType']) ) ? functions::decode($_REQUEST['txVoType']) : '';
	$date_explode = explode("-",$selMonth);
	if( count($date_explode)==3 ){
		$year=$date_explode[0];
		$month = $date_explode[1];
	}
	$_SESSION['as_acType']=$account_type;
	$_SESSION['as_selMonth']=$month;
	$_SESSION['as_selYr']=$year;
	functions::sendTo("account_statement.php");
	die();
}

$balance_date = $db->getValue('account_balance','DISTINCT bal_date',array(),'LIMIT 1');
$balance_time = $db->getValue('account_balance','DISTINCT bal_time',array(),'LIMIT 1');

if($balance_date && $balance_time){
	$balanceDate_explode = explode("-",$balance_date);
	$balanceTime_explode = explode(":",$balance_date);
	$bal_year=date('Y');
	$bal_month = date('m');
	$bal_day = date('d');
	$bal_hr = date('h');
	$bal_min = date('i');
	$bal_ss = '00';
	if( count($balanceDate_explode)==3 ){
		$bal_year=$balanceDate_explode[0];
		$bal_month = $balanceDate_explode[1];
		$bal_day = $balanceDate_explode[2];
	}
	if( count($balanceTime_explode)==3 ){
		$bal_hr=$balanceTime_explode[0];
		$bal_min = $balanceTime_explode[1];
		$bal_ss = $balanceTime_explode[2];
	}
	$bal_mktime = mktime($bal_hr,$bal_min,$bal_ss,$bal_month,$bal_day,$bal_year);
}
if($balance_date != date('Y-m-d')){
	$date=date('Y-m-d');
	$time=date('H:i:00');
	$qAccount = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
	while($rAccount = $db->fetch_array($qAccount)):
		$bal = getBalance($rAccount['vt_id'],$date);
		if( $db->getValue('account_balance','count(*)',array('vt_id'=>$rAccount['vt_id'])) )
			$db->update('account_balance',array('bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time),array('vt_id'=>$rAccount['vt_id']));
		else
			$db->insert('account_balance',array('vt_id'=>$rAccount['vt_id'],'bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time));
	endwhile;
	functions::sendTo(functions::pageName());  
}

if( isset($_POST['btnUpdate']) ){
	$date=date('Y-m-d');
	$time=date('H:i:00');
	$qAccount = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
	while($rAccount = $db->fetch_array($qAccount)):
		$bal = getBalance($rAccount['vt_id'],$date);
		if( $db->getValue('account_balance','count(*)',array('vt_id'=>$rAccount['vt_id'])) )
			$db->update('account_balance',array('bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time),array('vt_id'=>$rAccount['vt_id']));
		else
			$db->insert('account_balance',array('vt_id'=>$rAccount['vt_id'],'bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time));
	endwhile;
	functions::sendTo(functions::pageName());
	die();
}
$bidUpdt = ( isset($_REQUEST['bidUpdt']) && !empty($_REQUEST['bidUpdt']) ) ? functions::decode($_REQUEST['bidUpdt']) : '';
if($bidUpdt){
	if( $db->getValue('account_balance','included',array('vt_id'=>$bidUpdt))==1 )
		$db->update('account_balance',array('included'=>0),array('vt_id'=>$bidUpdt));
	else
		$db->update('account_balance',array('included'=>1),array('vt_id'=>$bidUpdt));
	functions::sendTo(functions::pageName());
	die();
}

function monthDays($month=0,$year=0,$day=31){
	$list = array();
	$month = ($month) ? $month : date('m');
	$year = ($year) ? $year : date('Y');
	for($d=1; $d<=$day; $d++):
		$time = mktime(12,0,0,$month,$d,$year);
		if( date('m',$time)==$month )
			$list[]=date('Y-m-d',$time);
	endfor;
	return $list;
}

function getPayablePO($year=0){
	global $db;
	$totalCost=0;
	$monthlyCost=0;
	$cost=0;
	if($year==0)
		$totalCost = $db->getValue('view_po_payment','sum(balance)',array());
	else
		$totalCost = $db->getValue('view_po_payment','sum(balance)',array('LEFT(po_date,4)'=>$year));
	return $totalCost;
}

function getBalance($account_type,$dateNow){
	global $db;
	$balance=0;
	$break=0;
	$year=date('Y');

	$qHasAdvance = $db->query('SELECT DISTINCT voucher_id_owner FROM voucher_advance_payment vap, voucher v WHERE vap.voucher_id_owner=v.voucher_id AND v.vt_id="'.$db->clean($account_type).'"');
	$arrVoucherAdvanceID=array();
	while($rHasAdvance = $db->fetch_array($qHasAdvance)):
		$arrVoucherAdvanceID[$rHasAdvance['voucher_id_owner']]=$rHasAdvance['voucher_id_owner'];
	endwhile;


	$arrASCredit=array();
	$qCredit = $db->select('account_statement','as_date as dt, sum(as_amount) as amnt',array('left(as_date,4)'=>$year,'as_type'=>'credit','account_type'=>$account_type,'confirmned'=>2),'GROUP BY dt');
	while($rCredit = $db->fetch_array($qCredit)):
		$arrASCredit[$rCredit['dt']]=$rCredit['amnt'];
	endwhile; #while Credit

	$arrASDebit=array();
	$qDebit = $db->select('account_statement','as_date as dt, sum(as_amount) as amnt',array('left(as_date,4)'=>$year,'as_type'=>'debit','account_type'=>$account_type,'confirmned'=>2),'GROUP BY dt');
	while($rDebit = $db->fetch_array($qDebit)):
		$arrASDebit[$rDebit['dt']]=$rDebit['amnt'];
	endwhile; #end while Debit


	for($m = 1; $m <= 12; $m++):
		$days = monthDays($m,$year);

		foreach($days as $day):
			if($day <= $dateNow){
				$balance += isset($arrASCredit[$day]) ? $arrASCredit[$day] : 0;
				$balance = ($balance) ? round($balance,2) : $balance;

				$balance -= isset($arrASDebit[$day]) ? $arrASDebit[$day] : 0;
				$balance = ($balance) ? round($balance,2) : $balance;

				#voucher or supplier for debit
				$qVoucher = $db->select('voucher','*',array('cheque_date'=>$day,'vt_id'=>$account_type));
				while($rVoucher = $db->fetch_array($qVoucher)):
					$amountDebit=0;
					$vid = $rVoucher['voucher_id'];
					$claimed = $rVoucher['claimed'];
					$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'deduction'));

					if( isset($arrVoucherAdvanceID[$rVoucher['voucher_id']]) ){
						$va = $a = new voucherAdvance($rVoucher['voucher_id']);
						$amountDebit = $va->current_voucher_payable_amount;
					}
					else{
						$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVoucher['voucher_id'],'charge_type'=>'surcharge'));
						$amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
						$non_po = $db->result($amount_q);

						$amount_non_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
						$po = $db->result($amount_non_po_q);
						$voucher_amount = $non_po + $po + $otherSurcharge;

						$wtax = ($rVoucher['witholding_tax'] > 0) ? ($rVoucher['witholding_tax'] / 100) : 0;

						$withholding_amount = ($rVoucher['witholding_vat']==1) ? $wtax * (($voucher_amount) / 1.12) : $wtax * $voucher_amount;
						$amountDebit = ($wtax) ? $voucher_amount - $withholding_amount : $voucher_amount;
					}
					if($otherDeduction)
						$amountDebit -= $otherDeduction;
					if($claimed==1){
						$balance -= $amountDebit;
						$balance = ($balance) ? round($balance,2) : $balance;
					}
				endwhile;#end while Voucher or supplier for debit
			}#end if date is greater than required
			else{
				$break=1;
				break;
			}
		endforeach; #end for each day
		if($break)
			break;
	endfor; #end for each month
	$balanceDisplay = $balance;
	return $balance;
}

?>
<!-- body content: start here-->
<form method="post">
<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
	<tr>
		<td width="3%" style="visibility:hidden"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
		<td width="47%" style="visibility:hidden"> Inactive</td>
		<td width="50%"><div align="right"><input type="submit" id="btnUpdate" name="btnUpdate" value="Get Updated Balance" class="btn btn-small btn-info"></div></td>
	</tr>
</table><br><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Account Balance</h2>
		</div>
		<div align="center"><br>&nbsp;&nbsp;Balance as of <?php echo date('l F d, Y',$bal_mktime);?> - <strong><?php echo functions::MilToTwelve($balance_time);?></strong><br><br></div>
		<div class="box-content">
			<table class="table table-hover table-striped" border="0" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="45%">ACCOUNT</th>
						<th width="25%"><div align="right">BALANCE</div></th>
						<th width="20%">&nbsp;</th>
						<th width="5%">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$qAccount = $db->select('account_balance','*',array());
				while($rAccount = $db->fetch_array($qAccount)):
				?>
					<tr>
						<td><?php echo $db->getValue('voucher_type','vt_name',array('vt_id'=>$rAccount['vt_id']));?></td>
						<td><div align="right"><a href="<?php echo functions::pageName()?>?DswrW=Gsetg&txVoType=<?php echo functions::encode($rAccount['vt_id'])?>&bdMon=<?php echo functions::encode($balance_date)?>"><?php echo functions::formatMoney($rAccount['bal']);?></a></div></td>
						<td>&nbsp;</td>
						<td><input type="checkbox" name="acnt<?php echo $rAccount['vt_id']?>" id="acnt<?php echo $rAccount['vt_id']?>" value="<?php echo functions::encode($rAccount['vt_id']);?>" <?php echo ($rAccount['included']) ? 'checked="checked"' : '';?> onClick="updtChk(this.value)" /></td>
					</tr>
				<?php endwhile;
				$totalBalance = $db->getValue('account_balance','sum(bal)',array('included'=>1));
				$payable = getPayablePO(date('Y',$bal_mktime));
				?>
					<tr>
						<td colspan="4"><hr width="100%" style="color:#FF0000"></td>
					</tr>
					<tr>
						<td><div align="right"><strong>Total Balance</strong></div></td>
						<td><div align="right" id="sumBal"><strong><?php echo functions::formatMoney($totalBalance);?></strong></div></td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td><div align="right"><strong>Payable P.O.</strong></div></td>
						<td><div align="right" id="sumBal"><strong><?php echo functions::formatMoney($payable);?></strong></div></td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
						<td><div align="right"><strong>Remaining Balance</strong></div></td>
						<td><div align="right" id="sumBal"><strong><?php echo functions::formatMoney($totalBalance - $payable);?></strong></div></td>
						<td>&nbsp;</td>
						<td></td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="txSum" id="txSum" value="">
		</div>
	</div><!--/span-->
</div><!--/row-->
</form>
<!-- body content: end here-->
<script>function updtChk(PiEwgD){window.location="<?php echo functions::pageName()?>?bidUpdt="+PiEwgD}</script>
<?php require_once('templ_down.php');?>