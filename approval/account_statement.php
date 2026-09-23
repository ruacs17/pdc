<?php require_once('templ_up.php');
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

if( isset($_POST['btnSearch']) ){
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
	$selMonth = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$account_type = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	$_SESSION['as_acType']=$account_type;
	$_SESSION['as_selMonth']=$selMonth;
	$_SESSION['as_selYr']=$year;
	functions::sendTo(functions::pageName());
	die();
}

if( isset($_REQUEST['ddid']) && !empty($_REQUEST['ddid']) ){
	$as_id = ( isset($_REQUEST['ddid']) && !empty($_REQUEST['ddid']) ) ? functions::decode($_REQUEST['ddid']) : '';
	$getProjIncomeID = $db->getValue('account_statement','project_income',array('as_id'=>$as_id));
	$db->query('UPDATE project_income SET pi_date=NULL WHERE pi_id="'.$db->clean($getProjIncomeID).'"');
	$db->delete('account_statement',array('as_id'=>$as_id));
	functions::sendTo(functions::pageName());
	die();
}

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
?>
<!-- body content: start here-->
<table width="460" cellspacing="4" cellpadding="6" border='0' align="right">
	<tr>
		<td width="5"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
		<td width="35"> Unconfirmned Statement</td>
		<td width="5"><div style="background-color:#fa6f6f; width:20px;">&nbsp;</div></td>
		<td width="35"> Unclaimed Voucher</td>
	</tr>
</table><br><br>
<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
<tr>
	<td width="3%" style="visibility:hidden"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
	<td width="47%" style="visibility:hidden"> Inactive</td>
	<td width="50%">
		<div align="right">
			<a id="adc" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'account_statement_add.php?t=<?php echo functions::encode($account_type)?>&y=<?php echo functions::encode($year)?>&m=<?php echo functions::encode($selMonth)?>','Statement Add')">Add New Statement</a>
			<a id="adcd" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'account_statement_print.php?','Account Statement Print','1')"><i class="halflings-icon white print"></i></a>
		</div>
	</td>
</tr>
</table><br><br>
<div class="row-fluid">
	<form method="post">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Account Statement</h2>
			</div>
			<div align="center"><br>&nbsp;&nbsp;
				<select name="bdYear" id="bdYear">
					<option value="">--Select Year--</option>
					<?php for($y=(date('Y')+7);$y>=2012;$y--):?>
					<option value="<?php echo functions::encode($y);?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
					<?php endfor;?>
				</select>
				<select name="bdMon" id="bdMon" style="width:95px;">
					<option value="">All Month</option>
					<option value="01" <?php if($selMonth=='01')echo 'selected="selected"';?>>Jan</option>
					<option value="02" <?php if($selMonth=='02')echo 'selected="selected"';?>>Feb</option>
					<option value="03" <?php if($selMonth=='03')echo 'selected="selected"';?>>Mar</option>
					<option value="04" <?php if($selMonth=='04')echo 'selected="selected"';?>>Apr</option>
					<option value="05" <?php if($selMonth=='05')echo 'selected="selected"';?>>May</option>
					<option value="06" <?php if($selMonth=='06')echo 'selected="selected"';?>>Jun</option>
					<option value="07" <?php if($selMonth=='07')echo 'selected="selected"';?>>Jul</option>
					<option value="08" <?php if($selMonth=='08')echo 'selected="selected"';?>>Aug</option>
					<option value="09" <?php if($selMonth=='09')echo 'selected="selected"';?>>Sep</option>
					<option value="10" <?php if($selMonth=='10')echo 'selected="selected"';?>>Oct</option>
					<option value="11" <?php if($selMonth=='11')echo 'selected="selected"';?>>Nov</option>
					<option value="12" <?php if($selMonth=='12')echo 'selected="selected"';?>>Dec</option>
				</select>
				<select name="txVoType" id="txVoType">
					<option value="">--Account Type--</option>
					<?php 
					$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
					while($rVtype = $db->fetch_array($qVtype)):
					?>
					<option value="<?php echo $rVtype['vt_id']?>" <?php if($account_type==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
					<?php endwhile;?>
				</select>
				<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small">
			</div>
			<div class="box-content">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th style="background-color:#CCC;position: -webkit-sticky;position: sticky;top: 0;" width="10%">Date</th>
							<th style="background-color:#CCC;position: -webkit-sticky;position: sticky;top: 0;" width="38%">Transactions</th>
							<th style="background-color:#CCC;position: -webkit-sticky;position: sticky;top: 0;" width="12%">Check Number</th>
							<th style="background-color:#CCC;position: -webkit-sticky;position: sticky;top: 0;" width="10%"><div align="center">DEBIT</div></th>
							<th style="background-color:#CCC;position: -webkit-sticky;position: sticky;top: 0;" width="10%"><div align="center">CREDIT</div></th>
							<th style="background-color:#CCC;position: -webkit-sticky;position: sticky;top: 0;" width="12%"><div align="center">BALANCE</div></th>
							<th style="background-color:#CCC;position: -webkit-sticky;position: sticky;top: 0;" width="9%"><div align="center">Options</div></th>
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
								<td colspan="7"><div align="left"><strong>Month Of '.$monthName.'</strong></div></td>
							</tr>
							';
						}
						foreach($days as $day):
							$qCredit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'credit','account_type'=>$account_type));
							while($rCredit = $db->fetch_array($qCredit)):
								$credID = $rCredit['as_id'];
								$bgColor = 'bgcolor="#f5ae00"';
								if($rCredit['confirmned']==2){
									$balance += $rCredit['as_amount'];
									$balance = ($balance) ? round($balance,2) : $balance;
									$totalCredit += $rCredit['as_amount'];
									$monthlyCredit += $rCredit['as_amount'];
									$bgColor="";
								}
								$visibility = "";
								if($rCredit['tag_id']){
									$visibility = "style='visibility:hidden'";
								}
								$project_income = ($rCredit['project_income']) ? $rCredit['project_income'] : 0;
								$project_id = $db->getValue('project_income','proj_id',array('pi_id'=>$project_income));
								$project_name = ($project_id) ? ' <i>('.$db->getValue('project','proj_name',array('proj_id'=>$project_id)).')</i>' : '';
								if($selMonth==$m || $selMonth == ''){
									$lnk='account_statement_edit.php';
									if($rCredit['transaction_type']=="loan proceeds")
										$lnk = 'account_statement_edit_loan.php';
									elseif($rCredit['transaction_type'] == 'project income')
										$lnk = 'account_statement_billing_edit.php';
									elseif($rCredit['transaction_type'] == 'project income')
										$lnk = 'account_statement_billing_edit.php';
									elseif($rCredit['transaction_type'] == 'stock share')
										$lnk = 'account_statement_edit_stockholder.php';
									$balanceDisplay = $balance;
									$credit_amount_display = functions::formatMoney($rCredit['as_amount']);
									if($rCredit['transaction_type']=="loan proceeds"){
										$credit_amount_display = '<a id="Vloan'.$credID.'" class="thickbox" style="cursor:pointer;" title="Statement Details" data-rel="tooltip" onclick="showThis(this.id,\'account_statement_loan_list.php?readonly=readonly&asid='.functions::encode($credID).'\',\'Statement Details\',\'1\')">'.functions::formatMoney($rCredit['as_amount']).'</a>';
									}
									echo '
									<tr id="rw'.$credID.'" '.$bgColor.'>
										<td>'.functions::datearr($day).'</td>
										<td>'.$rCredit['transaction'].$project_name.'</td>
										<td></td>
										<td></td>
										<td><div align="right">'.$credit_amount_display.'</div></td>
										<td><div align="right">'.functions::formatMoney($balanceDisplay).'</div></td>
										<td>
											<div align="center">
												<a id="edit'.$credID.'" class="btn btn-mini btn-warning thickbox" title="Modify this Statement" data-rel="tooltip" onclick="showThis(this.id,\''.$lnk.'?eid='.functions::encode($credID).'&as_id='.functions::encode($credID).'\',\'Statement Details\')"><i class="halflings-icon white pencil"></i></a>
												<a class="btn btn-mini btn-danger" title="Remove this Statement" '.$visibility.' data-rel="tooltip" onClick="return delt();" href="'.functions::pageName().'?ddid='.functions::encode($credID).'&m='.functions::encode($selMonth).'&y='.functions::encode($year).'&t='.functions::encode($account_type).'"><i class="halflings-icon white trash"></i></a>
											</div>
										</td>
									</tr>
									';
								}
							endwhile; #while Credit

							$qDebit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'debit','account_type'=>$account_type));
							while($rDebit = $db->fetch_array($qDebit)):
								$debID = $rDebit['as_id'];
								$bgColor = 'bgcolor="#f5ae00"';
								if($rDebit['confirmned']==2){
									$totalDebit += $rDebit['as_amount'];
									$balance -= $rDebit['as_amount'];
									$balance = ($balance) ? round($balance,2) : $balance;
									$monthlyDebit += $rDebit['as_amount'];
									$bgColor="";
								}
								$visibility = "";
								if($rDebit['tag_id']){
									$visibility = "style='visibility:hidden'";
								}
								if($selMonth==$m || $selMonth == ''){
									$balanceDisplay = $balance;
									echo '
									<tr id="rw'.$debID.'" '.$bgColor.'>
										<td>'.functions::datearr($day).'</td>
										<td>'.$rDebit['transaction'].'</td>
										<td></td>
										<td><div align="right">'.functions::formatMoney($rDebit['as_amount']).'</div></td>
										<td></td>
										<td><div align="right">'.functions::formatMoney($balanceDisplay).'</div></td>
										<td>
											<div align="center">
												<a id="edit'.$debID.'" class="btn btn-mini btn-warning thickbox" title="Modify this Statement" data-rel="tooltip" onclick="showThis(this.id,\'account_statement_edit.php?eid='.functions::encode($debID).'\',\'Statement Details\')"><i class="halflings-icon white pencil"></i></a>
												<a class="btn btn-mini btn-danger" title="Remove this Statement" '.$visibility.' data-rel="tooltip" onClick="return delt();" href="'.functions::pageName().'?ddid='.functions::encode($debID).'&m='.functions::encode($selMonth).'&y='.functions::encode($year).'&t='.functions::encode($account_type).'"><i class="halflings-icon white trash"></i></a>
											</div>
										</td>
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
								$bgColor = '';

								$withAdvance=0;
								if( isset($arrVoucherAdvanceID[$rVoucher['voucher_id']]) ){
									$withAdvance=1;
									$va = $a = new voucherAdvance($rVoucher['voucher_id']);
									$amountDebit = $va->current_voucher_payable_amount;
									$withholding_amount = $va->current_voucher_withholding_tax_amount;
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
									$totalDebit += $amountDebit;
									$balance -= $amountDebit;
									$balance = ($balance) ? round($balance,2) : $balance;
									$monthlyDebit += $amountDebit;
								}
								else
									$bgColor = 'bgcolor="#fa6f6f"';

								if($selMonth==$m || $selMonth == ''){
									$balanceDisplay = $balance;
									echo '
									<tr '.$bgColor.'>
										<td>'.functions::datearr($day).'</td>
										<td><a id="show'.$vid.'" class="thickbox" style="cursor:pointer" data-rel="tooltip" onclick="showThis(this.id,\'account_statement_detail.php?wad='.$withAdvance.'&vid='.functions::encode($vid).'\',\'Statement Details\',\'1\')">'.$db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])).'</a></td>
										<td>'.$rVoucher['cheque_id'].'</td>
										<td><div align="right">'.functions::formatMoney($amountDebit);
											echo ($wtax) ? ' <i>withholding: ('.functions::formatMoney($withholding_amount).')</i>' : '';
											echo '</div>
										</td>
										<td></td>
										<td><div align="right">'.functions::formatMoney($balanceDisplay).'</div></td>
										<td></td>
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
								<td><div align="right"><strong>'.functions::formatMoney($monthlyDebit).'</strong></div></td>
								<td><div align="right"><strong>'.functions::formatMoney($monthlyCredit).'</strong></div></td>
								<td><strong></strong></td>
								<td></td>
							</tr>
							';
							echo '
							<tr>
								<td>&nbsp;</td>
								<td colspan="2"><div align="right"><strong>Cumulative report until '.$monthName.' '.$year.'</strong></div></td>
								<td><div align="right"><strong>'.functions::formatMoney($totalDebit).'</strong></div></td>
								<td><div align="right"><strong>'.functions::formatMoney($totalCredit).'</strong></div></td>
								<td><div align="right"><strong>'.functions::formatMoney($balanceDisplay).'</strong></div></td>
								<td></td>
							</tr>
							';
						}
					endfor; #end for each month
					$balanceDisplay = $balance;
					?>
						<tr>
							<td colspan="7"><hr width="100%" style="color:#FF0000"></td>
						</tr>
						<tr>
							<td colspan="3"><div align="right"><strong>End of Year <?php echo $year?></strong></div></td>
							<td><strong><?php echo functions::formatMoney($totalDebit);?></strong></td>
							<td><strong><?php echo functions::formatMoney($totalCredit);?></strong></td>
							<td><strong><?php echo functions::formatMoney($balanceDisplay);?></strong></td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div><!--/span-->
	</form>
</div><!--/row-->
<!-- body content: end here-->
<?php
#this is to carry over the ending balance the selected year to the next year of it.
if($year > 2016 && $account_type){
	/*$remainingBalance = $balance;
	$transaction = 'REMAINING BALANCE of '. $year;
	$nextYear = ($year + 1) .'-01-01';

	if( $db->getValue('account_statement','count(*)',array('transaction'=>$transaction,'as_type'=>'credit','as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2))==0 ){
		$db->insert('account_statement',array('as_type'=>'credit','transaction'=>$transaction,'description'=>'','as_date'=>$nextYear,'as_amount'=>$remainingBalance,'account_type'=>$account_type,'confirmned'=>2,'transaction_type'=>'balance'));
	}
	else{
		$nextRemBal = $db->getValue('account_statement','as_amount',array('transaction'=>$transaction,'as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2));
		if($remainingBalance != $nextRemBal)
			$db->update('account_statement',array('as_amount'=>$remainingBalance),array('transaction'=>$transaction,'as_type'=>'credit','as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2,'transaction_type'=>'balance'));
	}*/
}
?>
<script>
function delt(){
	if(confirm('Do you want to remove this item?'))
		return true;
	else
		return false; 
}
</script>
<?php require_once('templ_down.php');?>

<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>