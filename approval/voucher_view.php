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
$withholding = $rVoucher['witholding_tax'];
$withholding_vat = $rVoucher['witholding_vat'];
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
	<div class="box span12" style="margin-left:0;">
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
		
		<div class="box-content" style="overflow: hidden;">
			
			<!-- Voucher Meta Info Card Style -->
			<div class="row-fluid" style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #e5e5e5; box-sizing: border-box;">
				<div class="span6" style="margin-left: 0;">
					<p style="margin: 0 0 8px 0; font-size: 14px;">Payee: <strong style="font-size: 15px; color: #333;"><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rVoucher['supplierID'])));?></strong></p>
					<p style="margin: 0; font-size: 13px; color: #666;">Cheque #: <strong><?php echo !empty($rVoucher['cheque_id']) ? $rVoucher['cheque_id'] : 'N/A';?></strong></p>
				</div>
				<div class="span6" style="text-align: right;">
					<p style="margin: 0 0 8px 0; font-size: 14px;">Voucher No: <strong style="color: #004a99;"><?php echo $rVoucher['voucher_no'];?></strong></p>
					<p style="margin: 0; font-size: 13px; color: #666;">Date: <strong><?php echo functions::datearr($rVoucher['vdate']);?></strong></p>
					<p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">Cheque Date: <strong><?php echo ($rVoucher['cheque_date']) ? functions::datearr($rVoucher['cheque_date']) : 'N/A';?></strong></p>
				</div>
			</div>

			<!-- Unified Financial Sheet Table -->
			<table id="tblist" class="table table-bordered table-striped table-hover" style="width: 100%; margin-bottom: 15px;">
				<thead>
					<tr style="background-color: #f5f5f5;">
						<th width="65%">PARTICULARS</th>
						<th width="25%"><div align="right">AMOUNT</div></th>
						<th width="10%"><div align="center">ACTIONS</div></th>
					</tr>
				</thead>
				<tbody>
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
						<div align="center">
							<?php if($approveStatus==0){?>
								<?php if($rvp['vtype'] == "po"){?>
								<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_po_view.php?vid=<?php echo functions::encode($vid);?>&po_id=<?php echo functions::encode($po_id);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','P.O. Details')"><i class="halflings-icon white zoom-in"></i></a>
								<a class="btn btn-mini btn-danger" title="Remove" data-rel="tooltip" onClick="return delt();" href="voucher_view.php?vid=<?php echo functions::encode($vid);?>&poDel=<?php echo functions::encode($po_id);?>&vpidpoDel=<?php echo functions::encode($rvp['vp_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }else if($rvp['vtype'] == "cash"){?>
								<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_part_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details')"><i class="halflings-icon white zoom-in"></i></a>
								<a class="btn btn-mini btn-danger" title="Remove" data-rel="tooltip" onClick="return delt();" href="voucher_view.php?vid=<?php echo functions::encode($vid);?>&vpidDel=<?php echo functions::encode($rvp['vp_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }else if($rvp['vtype'] == "transfer"){?>
								<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details')"><i class="halflings-icon white zoom-in"></i></a>
								<a class="btn btn-mini btn-danger" title="Remove" data-rel="tooltip" onClick="return delt();" href="voucher_view.php?vid=<?php echo functions::encode($vid);?>&vpidDel=<?php echo functions::encode($rvp['vp_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							<?php } else {?>
								<?php if($rvp['vtype'] == "po"){?>
								<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="P.O. Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_po_view.php?vid=<?php echo functions::encode($vid);?>&po_id=<?php echo functions::encode($po_id);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','P.O. Details','1')"><i class="halflings-icon white zoom-in"></i></a>
								<?php }else if($rvp['vtype'] == "cash"){?>
								<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_part_view.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details','1')"><i class="halflings-icon white zoom-in"></i></a>
								<?php }else if($rvp['vtype'] == "transfer"){?>
								<a id="detail<?php echo $rvp['vp_id']?>" class="btn btn-mini btn-info thickbox" title="Particular Details" data-rel="tooltip" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($rvp['vp_id']);?>','Particular Details','1')"><i class="halflings-icon white zoom-in"></i></a>
								<?php }?>
							<?php }?>
						</div>
					</td>
				</tr>
				<?php endwhile;?>

				<!-- Surcharges -->
				<?php
				$qSC = $db->select('voucher_deduction','*',array('voucher_id'=>$vid,'charge_type'=>'surcharge'));
				$countSurCharge=$db->num_rows($qSC);
				if($countSurCharge){
				?>
				<tr class="info">
					<td colspan="3"><strong>Surcharge Items:</strong></td>
				</tr>
				<?php
				while($rSC = $db->fetch_array($qSC)):
					$total_amount += $rSC['deduction_value'];
				?>
				<tr>
					<td style="padding-left: 30px;"><?php echo $rSC['deduction_name'];?></td>
					<td><div align="right"><?php echo functions::formatMoney($rSC['deduction_value']);?></div></td>
					<td></td>
				</tr>
				<?php endwhile;
				}
				?>

				<!-- Calculations & Totals Integrated Row Section -->
				<?php
				$taxable_advance_payment=0;
				$taxable_amount = $total_amount;
				if($withholding_vat==1)
					$current_w_tax = ($total_amount && $withholding) ? ($total_amount / 1.12) * ($withholding / 100) : 0;
				else
					$current_w_tax = ($total_amount && $withholding) ? ($total_amount) * ($withholding / 100) : 0;
				$current_w_tax = round($current_w_tax,2);
				$payable_amount = $total_amount - $current_w_tax;
				if($hasAdvance){
					$va = $a = new voucherAdvance($rVoucher['voucher_id']);
					$arrVID_list = $va->arrayVoucher;
					$taxable_amount = $va->taxable_amount;
					$payable_amount = $va->current_voucher_payable_amount;
					$current_w_tax = $va->current_voucher_withholding_tax_amount;
				}
				$current_w_tax = round($current_w_tax,2);
				if($otherDeduction)
					$payable_amount -= $otherDeduction;
				?>
				
				<tr style="background-color: #fafbfc; border-top: 2px solid #ddd;">
					<td><div align="right"><strong>Total Amount:</strong></div></td>
					<td><div align="right"><strong><?php echo $pa = functions::formatMoney($total_amount);?></strong></div></td>
					<td></td>
				</tr>
				<?php if($otherDeduction){?>
				<tr style="background-color: #fafbfc;">
					<td><div align="right">Other Deductions:</div></td>
					<td><div align="right"><a id="deductionss" class="thickbox" style="text-decoration:none;cursor:pointer;" onclick="showThis(this.id,'voucher_manage_deduction.php?vid=<?php echo functions::encode($vid);?>','Voucher Deductions')"><?php echo functions::formatMoney($otherDeduction);?></a></div></td>
					<td></td>
				</tr>
				<?php }?>
				<?php if($hasAdvance){?>
				<tr style="background-color: #fafbfc;">
					<td><div align="right">Advance Payment <em>(Taxable)</em>:</div></td>
					<td><div align="right"><?php echo functions::formatMoney(abs($va->voucher_amount_without_withholding_tax));?></div></td>
					<td></td>
				</tr>
				<tr style="background-color: #fafbfc;">
					<td><div align="right">Total Taxable Amount:</div></td>
					<td><div align="right"><?php echo functions::formatMoney($taxable_amount);?></div></td>
					<td></td>
				</tr>
				<?php }?>
				<?php if($current_w_tax || $hasAdvance){?>
				<tr style="background-color: #fafbfc;">
					<td><div align="right">Less: Withholding Tax <em>(<?php echo $withholding;?>%)</em>:</div></td>
					<td><div align="right"><strong><?php echo functions::formatMoney($current_w_tax);?></strong></div></td>
					<td></td>
				</tr>
				<?php }?>
				<?php if($hasAdvance){?>
				<tr style="background-color: #fafbfc;">
					<td><div align="right">Advance Payment's Withholding Tax:</div></td>
					<td><div align="right"><?php echo functions::formatMoney($va->total_advance_withholding_tax_amount);?></div></td>
					<td></td>
				</tr>
				<tr style="background-color: #fafbfc;">
					<td><div align="right">Overall Payment:</div></td>
					<td><div align="right"><?php echo functions::formatMoney($va->overall_payment);?></div></td>
					<td></td>
				</tr>
				<?php }?>
				<?php if($current_w_tax || $hasAdvance || $otherDeduction ){?>
				<tr style="background-color: #f1f5f9; font-size: 14px;">
					<td><div align="right"><strong>Payable Amount:</strong></div></td>
					<td><div align="right"><strong style="color: #004a99;"><?php echo $pa = functions::formatMoney($payable_amount);?></strong></div></td>
					<td></td>
				</tr>
				<?php }?>
				<?php
				$pf_amount = ($taxable_amount) * $pf_percent;
				if($pf_amount){?>
				<tr style="background-color: #fafbfc;">
					<td><div align="right">Professional Fee <em>(<?php echo $pf;?>%)</em>:</div></td>
					<td><div align="right"><strong><?php echo functions::formatMoney($pf_amount);?></strong></div></td>
					<td></td>
				</tr>
				<?php }?>

				</tbody>
			</table>

			<!-- Add Particular Control Buttons -->
			<?php if($approveStatus==0){?>
			<div style="margin-bottom: 20px;">
				<?php if($voucher_type==''){?>
				<a id="addParticular" class="btn btn-small btn-success thickbox" onclick="showThis(this.id,'voucher_part_add.php?vid=<?php echo functions::encode($vid);?>','Adding Particular Details')"><i class="halflings-icon white plus"></i> Add Particular</a>
				<a id="addPOpayment" class="btn btn-small btn-success thickbox" onclick="showThis(this.id,'voucher_part_po_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Select P.O. to Pay')"><i class="halflings-icon white plus"></i> Add PO Payment</a>
				<a id="fundTransfer" class="btn btn-small btn-success thickbox" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Fund transfer')"><i class="halflings-icon white plus"></i> Fund Transfer</a>
				<?php } elseif($voucher_type=='cash'){?>
				<a id="addParticular" class="btn btn-small btn-success thickbox" onclick="showThis(this.id,'voucher_part_add.php?vid=<?php echo functions::encode($vid);?>','Adding Particular Details')"><i class="halflings-icon white plus"></i> Add Particular</a>
				<?php } elseif($voucher_type=='po'){?>
				<a id="addPOpayment" class="btn btn-small btn-success thickbox" onclick="showThis(this.id,'voucher_part_po_add.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Select P.O. to Pay')"><i class="halflings-icon white plus"></i> Add PO Payment</a>
				<?php } elseif($voucher_type=='transfer'){?>
				<a id="fundTransfer" class="btn btn-small btn-success thickbox" onclick="showThis(this.id,'voucher_fund_transfer.php?vid=<?php echo functions::encode($vid);?>&vpid=<?php echo functions::encode($vp_id);?>','Fund transfer')"><i class="halflings-icon white plus"></i> Fund Transfer</a>
				<?php }?>
			</div>
			<?php }?>

			<!-- Amount in Words -->
			<?php $mtw = functions::moneyToDouble($pa); ?>
			<div class="alert alert-info" style="margin-top: 10px; box-sizing: border-box;">
				Amount in Words: <strong><?php echo ucwords(strtolower(functions::number_to_words($mtw)))?> Pesos Only</strong>
			</div>

			<hr style="margin: 20px 0;">

			<!-- Signatures Layout -->
			<div class="row-fluid" style="margin-top: 20px;">
				<div class="span3" style="margin-left: 0; border-right: 1px solid #eee; padding-right: 15px; box-sizing: border-box;">
					<p class="muted" style="margin-bottom: 25px;">Prepared By</p>
					<p><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rVoucher['preparedBy'])));?></strong></p>
				</div>
				<div class="span3" style="border-right: 1px solid #eee; padding-right: 15px; box-sizing: border-box;">
					<p class="muted" style="margin-bottom: 25px;">Checked By</p>
					<p><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rVoucher['checkedBy'])));?></strong></p>
				</div>
				<div class="span3" style="border-right: 1px solid #eee; padding-right: 15px; box-sizing: border-box;">
					<p class="muted" style="margin-bottom: 25px;">Approved By</p>
					<p><strong><?php echo strtoupper($db->getValue('users','CONCAT(fname," ",left(mname,1),". ",lname)',array('user_id'=>$rVoucher['approvedBy'])));?></strong></p>
				</div>
				<div class="span3" style="box-sizing: border-box;">
					<p class="muted" style="margin-bottom: 25px;">Received By</p>
					<p><strong><?php echo strtoupper($rVoucher['receivedBy']);?></strong></p>
				</div>
			</div>

			<div class="row-fluid" style="margin-top: 20px;">
				<div class="span12" style="margin-left: 0;">
					<p class="muted">Remarks:</p>
					<div style="background: #fcfcfc; padding: 10px; border: 1px solid #eaeaea; border-radius: 3px;">
						<strong><?php echo !empty($rVoucher['remarks']) ? $rVoucher['remarks'] : 'No remarks provided.';?></strong>
					</div>
				</div>
			</div>

			<!-- Status Checkboxes Bar -->
			<div class="row-fluid" style="margin-top: 20px; margin-left: 0; background: #f5f5f5; padding: 15px; border-radius: 4px; box-sizing: border-box;">
				<div class="span12" style="text-align: right; margin-left: 0;">
					<label class="checkbox inline" style="font-weight: bold; font-size: 14px; cursor: pointer;">
						<input type="checkbox" name="chkClaim" id="chkClaim" value="<?php echo functions::encode('a');?>" onClick="statClaim(this.value)" <?php if($claimedStatus)echo 'checked';?> <?php if($approveStatus)echo 'disabled="disabled"';?>> Claimed
					</label>
					<?php if($username==="nancy"){?>
					<label class="checkbox inline" style="font-weight: bold; font-size: 14px; margin-left: 20px; cursor: pointer;">
						<input type="checkbox" name="chkApprv" id="chkApprv" value="<?php echo functions::encode('a');?>" onClick="stat(this.value)" <?php if($approveStatus)echo 'checked';?>> Approve
					</label>
					<?php }?>
				</div>
			</div>

		</div>
	</div>
</div>

<script>
function delt(){
	return confirm('Do you want to remove this item?');
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