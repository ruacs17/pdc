<?php require_once('templ_up.php');?>
<?php
require_once('../class/MoneytoWords.php');
require_once('../class/voucher_advance.php');

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$_SESSION['notif_id_list']=$vid;
$vpidDel = (isset($_REQUEST['vpidDel']) && !empty($_REQUEST['vpidDel']) ) ? functions::decode($_REQUEST['vpidDel']) : 0;
$vpidpoDel = (isset($_REQUEST['vpidpoDel']) && !empty($_REQUEST['vpidpoDel']) ) ? functions::decode($_REQUEST['vpidpoDel']) : 0;
$poDel = (isset($_REQUEST['poDel']) && !empty($_REQUEST['poDel']) ) ? functions::decode($_REQUEST['poDel']) : 0;
$checkStatus = $db->getValue('voucher','count(checkedBy)',array('voucher_id'=>$vid));
$approveStatus = $db->getValue('voucher','count(approved)',array('voucher_id'=>$vid));
$claimedStatus = $db->getValue('voucher','count(claimed)',array('voucher_id'=>$vid,'claimed'=>'1'));
$qvoucher = $db->select('voucher','*',array('voucher_id'=>$vid));
$rVoucher = $db->fetch_array($qvoucher);
$withholding = $rVoucher['witholding_tax'];//$db->getValue('voucher','witholding_tax',array('voucher_id'=>$vid));
$witholding_vat = $rVoucher['witholding_vat'];
$withholding_percent = ($withholding) ? $withholding / 100 : 0;
$advance_payment = $db->getValue('voucher','advance_payment',array('voucher_id'=>$vid));
$pf = $db->getValue('voucher','prof_fee',array('voucher_id'=>$vid));
$pf_percent = ($pf) ? $pf / 100 : 0;
$hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$vid));
$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'deduction'));
$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$vid,'charge_type'=>'surcharge'));
if( isset($_REQUEST['v']) && !empty($_REQUEST['v'])){
	if( $_REQUEST['v'] == "YQ==" ){
		if($approveStatus==0)
			$db->update('voucher',array('approved'=>$user_id,'approveDate'=>date('Y-m-d'),'approveTime'=>date('H:i:s')),array('voucher_id'=>$vid));
		else
			$db->update('voucher',array('approved'=>NULL),array('voucher_id'=>$vid));
			#$db->query('UPDATE voucher SET approved=NULL WHERE voucher_id="'.$db->clean($vid).'"');
	}
	functions::sendTo(functions::pageName().'?vid='.functions::encode($vid));
	die();
}
if( isset($_REQUEST['c']) && !empty($_REQUEST['c'])){
	if( $_REQUEST['c'] == "YQ==" )  {
		if($claimedStatus==0)
			$db->update('voucher',array('claimed'=>'1'),array('voucher_id'=>$vid));
		else
			$db->update('voucher',array('claimed'=>'2'),array('voucher_id'=>$vid));
	}
	functions::sendTo(functions::pageName().'?vid='.functions::encode($vid));
	die();
}

if($approveStatus==0){
	if($vpidpoDel){
		$db->query('DELETE FROM account_statement WHERE tag_id IN (SELECT vd_id FROM voucher_detail WHERE vp_id="'.$db->clean($vpidpoDel).'")');
		$db->update('po',array('vp_id'=>NULL),array('vp_id'=>$vpidpoDel));
		$db->delete('voucher_particular',array('vp_id'=>$vpidpoDel));
		$db->delete('voucher_po_payment',array('vp_id'=>$vpidpoDel,'po_id'=>$poDel));
		$_SESSION['notif_warning']='Item Removed!';
		functions::sendTo(functions::pageName().'?vid='.functions::encode($vid));
		die();
	}
	if($vpidDel){
		$db->query('DELETE FROM account_statement WHERE tag_id IN (SELECT vd_id FROM voucher_detail WHERE vp_id="'.$db->clean($vpidDel).'")');
		$db->delete('voucher_detail',array('vp_id'=>$vpidDel));
		$db->delete('voucher_particular',array('vp_id'=>$vpidDel));
		$_SESSION['notif_warning']='Item Removed!';
		functions::sendTo(functions::pageName().'?vid='.functions::encode($vid));
		die();
	}
}

$approveValue='Approve';
$approveColor = 'btn-warning';
if($approveStatus){
	$approveValue='Disapprove';
	$approveColor = 'btn-success';
	$checkColor = '';
}
?>
<!-- body content: start here-->
<ul class="breadcrumb">
	<li>
		<i class="icon-home"></i>
		<a href="voucher_list.php">Voucher List</a>
		<i class="icon-angle-right"></i>
	</li>
	<li><a href="#">Detail</a></li>
</ul>
<div class="row-fluid">
	<div class="box span10">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Voucher Detail</h2>
			<div class="box-icon">
				<?php if($approveStatus==0){?>
				<a id="advance" class="thickbox" style="text-decoration:none;cursor:pointer;" onclick="showThis(this.id,'voucher_add_advance_payment.php?vid=<?php echo functions::encode($vid);?>','Voucher Advance Payment')"><span style="color:white">Advance Payment&nbsp;&nbsp;</span><i class="halflings-icon white pencil"></i></a><span class="break"></span>
				<a id="deductions" class="thickbox" style="text-decoration:none;cursor:pointer;" onclick="showThis(this.id,'voucher_manage_deduction.php?vid=<?php echo functions::encode($vid);?>','Voucher Deductions/Charges')"><span style="color:white">Other Deductions / Charges&nbsp;&nbsp;</span><i class="halflings-icon white pencil"></i></a><span class="break"></span>
				<a id="edit<?php echo $vid?>" class="thickbox" style="text-decoration:none;cursor:pointer;" title="Modify this voucher" data-rel="tooltip" onclick="showThis(this.id,'voucher_edit.php?vid=<?php echo functions::encode($vid);?>','Voucher Details')"><span style="color:white">Edit&nbsp;&nbsp;</span><i class="halflings-icon white pencil"></i></a>
				<span class="break"></span>
				<?php }?>
				<a id="print<?php echo $vid?>" class="thickbox" style="text-decoration:none;cursor:pointer;" title="Print this voucher" data-rel="tooltip" onclick="showThis(this.id,'voucher_print.php?vid=<?php echo functions::encode($vid);?>','Voucher Print','1')"><span style="color:white">Print&nbsp;</span> <i class="halflings-icon white print"></i></a>
			</div>
		</div>
		<div class="box-content">
			<div align="right">Voucher No:<strong> <?php echo $rVoucher['voucher_no'];?></strong></div>
			<div align="right">Date:<strong> <?php echo functions::datearr($rVoucher['vdate']);?></strong></div>
			<div>Payee: <strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])));?></strong></div><br><br><br><br>
			<table id="tblist" class="table">
				<tr>
					<td width="60%"><strong>PARTICULARS</strong></td>
					<td width="25%"><div align="right"><strong>AMOUNT</strong></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php
				$total_amount = 0; $amount = 0; $po_id=0; $vp_id=0; $voucher_type='';
				$qvp = $db->select('voucher_particular','*',array('voucher_id'=>$vid));
				while($rvp = $db->fetch_array($qvp)):
					$vp_id=$rvp['vp_id'];
					$voucher_type = $rvp['vtype'];
					if($rvp['vtype'] == "po"){
						$po_id = $db->getValue('voucher_po_payment','po_id',array('vp_id'=>$rvp['vp_id']));
						$amount = $db->getValue('voucher_po_payment','amount',array('vp_id'=>$rvp['vp_id']));
					}
					else
						$amount = $db->getValue('voucher_detail','SUM(amount_issue)',array('vp_id'=>$rvp['vp_id']));
					$total_amount += $amount;
				?>
				<tr id="rw<?php echo $rvp['vp_id']?>">
					<td><?php echo $rvp['vp_title'];?></td>
					<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
					<td>
						<?php if($approveStatus==0){?>
						<div align="right">
							<?php if($rvp['vtype'] == "po"){?>
							<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_po_view.php?vid=<?php echo functions::encode($vid);?>&po_id=<?php echo functions::encode($po_id);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','P.O. Details')"><i class="halflings-icon white zoom-in"></i></a>
							<a class="btn btn-mini btn-danger" title="Remove this PO particular" data-rel="tooltip" onClick="return delt();" href="voucher_view.php?vid=<?php echo functions::encode($vid);?>&poDel=<?php echo functions::encode($po_id);?>&vpidpoDel=<?php echo functions::encode($rvp['vp_id']);?>"><i class="halflings-icon white trash"></i></a>
							<?php }else if($rvp['vtype'] == "cash"){?>
							<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_part_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details')"><i class="halflings-icon white zoom-in"></i></a>
							<a class="btn btn-mini btn-danger" title="Remove this particular" data-rel="tooltip" onClick="return delt();" href="voucher_view.php?vid=<?php echo functions::encode($vid);?>&vpidDel=<?php echo functions::encode($rvp['vp_id']);?>"><i class="halflings-icon white trash"></i></a>
							<?php }else if($rvp['vtype'] == "transfer"){?>
							<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details')"><i class="halflings-icon white zoom-in"></i></a>
							<a class="btn btn-mini btn-danger" title="Remove this particular" data-rel="tooltip" onClick="return delt();" href="voucher_view.php?vid=<?php echo functions::encode($vid);?>&vpidDel=<?php echo functions::encode($rvp['vp_id']);?>"><i class="halflings-icon white trash"></i></a>
							<?php }?>
						</div>
						<?php }#end checkStatus
						else{?>
						<div align="right">
							<?php if($rvp['vtype'] == "po"){?>
							<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_po_view.php?vid=<?php echo functions::encode($vid);?>&po_id=<?php echo functions::encode($po_id);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','P.O. Details','1')"><i class="halflings-icon white zoom-in"></i></a>
							<?php }else if($rvp['vtype'] == "cash"){?>
							<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_part_view.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details','1')"><i class="halflings-icon white zoom-in"></i></a>
							<?php }else if($rvp['vtype'] == "transfer"){?>
							<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details','1')"><i class="halflings-icon white zoom-in"></i></a>
							<?php }?>
						</div>
						<?php }?>
					</td>
				</tr>
				<?php

				endwhile;?>
				<?php
				$qSC = $db->select('voucher_deduction','*',array('voucher_id'=>$vid,'charge_type'=>'surcharge'));
				$countSurCharge=$db->num_rows($qSC);
				if($countSurCharge){
				?>
				<tr>
					<td><div align="left"><i>Surcharge:</i></div></td>
					<td>&nbsp;</td>
					<td></td>
				</tr>
				<?php
				while($rSC = $db->fetch_array($qSC)):
					$total_amount += $rSC['deduction_value'];
				?>
				<tr>
					<td><div align="left" style="padding-left:50px;"><?php echo $rSC['deduction_name'];?></div></td>
					<td><div align="right"><?php echo functions::formatMoney($rSC['deduction_value']);?></div></td>
					<td></td>
				</tr>
				<?php endwhile;
				}
				?>
				<tr>
					<td>
						<?php if($approveStatus==0){?>
						<div align="left">
							<?php if($voucher_type==''){?>
							<a id="addParticular" title="Add particular" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'voucher_part_add.php?vid=<?php echo functions::encode($vid);?>','Adding Particular Details')"><i class="halflings-icon white plus"></i>Add Particular</a>
							<a id="addPOpayment" title="Add PO Payment" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'voucher_part_po_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Select P.O. to Pay')"><i class="halflings-icon white plus"></i>Add PO Payment</a>
							<a id="fundTransfer" title="Fund Transfer" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Fund transfer')"><i class="halflings-icon white plus"></i>Fund Transfer</a>
						</div>
						<?php }#if voucher_type is empty
							elseif($voucher_type=='cash'){
						?>
						<a id="addParticular" title="Add particular" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'voucher_part_add.php?vid=<?php echo functions::encode($vid);?>','Adding Particular Details')"><i class="halflings-icon white plus"></i>Add Particular</a>
						<?php
							}
							elseif($voucher_type=='po'){
						?>
						<a id="addPOpayment" title="Add PO Payment" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'voucher_part_po_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Select P.O. to Pay')"><i class="halflings-icon white plus"></i>Add PO Payment</a>
						<?php
							}
							elseif($voucher_type=='transfer'){
						?>
						<a id="fundTransfer" title="Fund Transfer" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Fund transfer')"><i class="halflings-icon white plus"></i>Fund Transfer</a>
						<?php
							}
						}#end checkStatus?>
					</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<?php
				$taxable_advance_payment=0;
				$taxable_amount = $total_amount;
				if($witholding_vat==1)
					$current_w_tax = ($total_amount && $withholding) ? ($total_amount / 1.12) * ($withholding / 100) : 0;
				else
					$current_w_tax = ($total_amount && $withholding) ? ($total_amount) * ($withholding / 100) : 0;

				$payable_amount = $total_amount - $current_w_tax;
				if($hasAdvance){
					$va = $a = new voucherAdvance($rVoucher['voucher_id']);
					$arrVID_list = $va->arrayVoucher;
					$taxable_amount = $va->taxable_amount;
					$payable_amount = $va->current_voucher_payable_amount;
					$current_w_tax = $va->current_voucher_withholding_tax_amount;
				}
				if($otherDeduction)
					$payable_amount -= $otherDeduction;
				?>
				<tr>
					<td><div align="right">Total Amount</div></td>
					<td><div align="right"><strong><?php echo $pa = functions::formatMoney($total_amount);?></strong></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php if($otherDeduction){?>
				<tr>
					<td><div align="right">Other Deductions</div></td>
					<td><div align="right"><a id="deductionss" class="thickbox" style="text-decoration:none;cursor:pointer;" onclick="showThis(this.id,'voucher_manage_deduction.php?vid=<?php echo functions::encode($vid);?>','Voucher Deductions')"><?php echo functions::formatMoney($otherDeduction);?></a></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php }?>
				<?php if($hasAdvance){?>
				<tr>
					<td><div align="right">Advance Payment <i>(Taxable)</i></div></td>
					<td><div align="right"><?php echo functions::formatMoney(abs($va->voucher_amount_without_withholding_tax));?></div></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><div align="right">Total Taxable Amount</div></td>
					<td><div align="right"><?php echo functions::formatMoney($taxable_amount);?></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php }?>
				<?php if($current_w_tax || $hasAdvance){?>
				<tr>
					<td><div align="right">Less: <?php echo '<strong>(<i>'.$withholding.'%)</strong></i>&nbsp;&nbsp;&nbsp;&nbsp;';?>Withholding Tax</div></td>
					<td><div align="right"><strong><?php echo functions::formatMoney($current_w_tax);?></strong></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php }?>
				<?php if($hasAdvance){?>
				<tr>
					<td><div align="right">Advance Payment's Withholding Tax</div></td>
					<td><div align="right"><?php echo functions::formatMoney($va->total_advance_withholding_tax_amount);?></div></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><div align="right">Total Advance Payment</div></td>
					<td><div align="right"><?php echo functions::formatMoney($va->total_advance_voucher_amount);?></div></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><div align="right">Overall Payment</div></td>
					<td><div align="right"><?php echo functions::formatMoney($va->overall_payment);?></div></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><div align="right">Overall Withholding Tax</div></td>
					<td><div align="right"><?php echo functions::formatMoney($va->overall_withholding_tax_amount);?></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php }?>
				<?php if($current_w_tax || $hasAdvance || $otherDeduction ){?>
				<tr>
					<td><div align="right">Payable Amount</div></td>
					<td><div align="right"><strong><?php echo $pa = functions::formatMoney($payable_amount);?></strong></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php }?>
				<?php
				$pf_amount = ($taxable_amount) * $pf_percent;
				?>
				<?php if($pf_amount){?>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td><div align="right"><?php echo '<strong>(<i>'.$pf.'%)</strong></i>&nbsp;&nbsp;&nbsp;&nbsp;';?>Professional Fee</div></td>
					<td><div align="right"><strong><?php echo functions::formatMoney($pf_amount);?></strong></div></td>
					<td>&nbsp;</td>
				</tr>
				<?php }?>
				<tr>
					<td> </td>
					<td></td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<?php $mtw = functions::moneyToDouble($pa); ?>
			<div style="display:none;">Amount: <strong><?php #$ta = new MoneytoWords($payable_amount); #echo $ta->words;?> Only</strong></div>
			<div>Amount: <strong><?php echo ucwords(strtolower(functions::number_to_words($mtw)))?> Pesos Only</strong></div><br><br>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td colspan="2">&nbsp;</td>
					<td colspan="2"><div>CHEQUE #: <strong><?php echo $rVoucher['cheque_id']?></strong></div></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
					<td colspan="2"><div>CHEQUE DATE: <strong><?php echo ($rVoucher['cheque_date']) ? functions::datearr($rVoucher['cheque_date']) : '';?></strong></div></td>
				</tr>
				<tr>
					<td colspan="2">Prepared By</td>
					<td width="7%">&nbsp;</td>
					<td width="32%">&nbsp;</td>
				</tr>
				<tr>
					<td width="6%" height="46">&nbsp;</td>
					<td width="55%"><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rVoucher['preparedBy'])));?></strong></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">Checked By</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td height="48">&nbsp;</td>
					<td><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rVoucher['checkedBy'])));?></strong></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">Approved By</td>
					<td colspan="2">Received By</td>
				</tr>
				<tr>
					<td height="54">&nbsp;</td>
					<td><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rVoucher['approvedBy'])));?></strong></td>
					<td>&nbsp;</td>
					<td><strong><?php echo strtoupper($rVoucher['receivedBy']);?></strong></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td height="63">&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td height="44">Remarks:</td>
					<td>&nbsp;&nbsp;&nbsp;<strong><?php echo $rVoucher['remarks'];?></strong></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<div align="right">
				<div class="controls">
					<label class="inline">
						<input type="checkbox" name="chkClaim" id="chkClaim" value="<?php echo functions::encode('a');?>" onClick="statClaim(this.value)" <?php if($claimedStatus)echo 'checked';?> <?php if($approveStatus)echo 'disabled="disabled"';?>> Claimed &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<?php if($username==="nancy"){?>
						<input type="checkbox" name="chkApprv" id="chkApprv" value="<?php echo functions::encode('a');?>" onClick="stat(this.value)" <?php if($approveStatus)echo 'checked';?>> Approve
						<?php }?>
					</label>
				</div>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
	if(confirm('Do you want to remove this item?'))
		return true;
	else
		return false; 
}
function stat(v){
	window.location="<?php echo functions::pageName()?>?vid=<?php echo functions::encode($vid);?>&v="+v;
}
function statClaim(v){
	window.location="<?php echo functions::pageName()?>?vid=<?php echo functions::encode($vid);?>&c="+v;
}
</script>
<?php require_once('templ_down.php');?>
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
<?php if(isset($_SESSION['notif_id2_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id2_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id2_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id2_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id2_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id2_list']);} ?>