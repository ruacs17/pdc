<?php require_once('templ_up.php');?>
<?php
require_once('../class/voucher_advance.php');
$txPayee = '';
$txbMon = '';
$txbYear = '';
$txVtype = '';
unset($_SESSION['fr_Vo']);
$vidDel = (isset($_REQUEST['vidDel']) && !empty($_REQUEST['vidDel']) ) ? functions::decode($_REQUEST['vidDel']) : 0;
if($vidDel){
	$amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($vidDel).'"');
	$non_po = $db->result($amount_q);

	$amount_non_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($vidDel).'"');
	$po = $db->result($amount_non_po_q);
	$amount = $non_po + $po;
	$approveStatus = $db->getValue('voucher','count(approved)',array('voucher_id'=>$vidDel));

	if($approveStatus==0 && $amount==0){
		$db->delete('voucher',array('voucher_id'=>$vidDel));
		$_SESSION['notif_warning']='Voucher Removed!';
	}
	functions::sendTo(functions::pageName());
	die();
}

if( isset($_POST['btnSearch']) ){
	$_SESSION['vDateType'] = ( isset($_POST['selDateType']) && !empty($_POST['selDateType']) ) ? $_POST['selDateType'] : '';
	$_SESSION['vPayee'] = ( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ) ? $_POST['txPayee'] : '';
	$_SESSION['vMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['vYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['vType'] = ( isset($_POST['txVtype']) && !empty($_POST['txVtype']) ) ? $_POST['txVtype'] : '';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnViewAll']) ){
	unset($_SESSION['vPayee']);
	functions::sendTo(functions::pageName());
	die();
}
$arr = array();
$selDateType = ( isset($_SESSION['vDateType']) && !empty($_SESSION['vDateType']) ) ? $_SESSION['vDateType'] : 'cheque';
$txPayee = ( isset($_SESSION['vPayee']) && !empty($_SESSION['vPayee']) ) ? $_SESSION['vPayee'] : '';
$txbMon = ( isset($_SESSION['vMon']) ) ? $_SESSION['vMon'] : date('m');
$txbYear = ( isset($_SESSION['vYear']) ) ? $_SESSION['vYear'] : date('Y');
$txVtype = ( isset($_SESSION['vType']) && !empty($_SESSION['vType']) ) ? $_SESSION['vType'] : '';

$dateType = ($selDateType=='voucher') ? 'vdate' : 'cheque_date';

if($txbMon && $txbYear)
	$arr = array('LEFT('.$dateType.',7)'=>$txbYear.'-'.$txbMon);
else if($txbMon)
	$arr = array('SUBSTRING('.$dateType.',6,2)'=>$txbMon);
elseif($txbYear)
	$arr = array('LEFT('.$dateType.',4)'=>$txbYear);

if($txPayee)
	$arr = array_merge($arr,array('supplierID'=>$txPayee));
if($txVtype)
	$arr = array_merge($arr,array('vt_id'=>$txVtype));

$sort = (isset($_REQUEST['sort']) && !empty($_REQUEST['sort']) ) ? functions::decode($_REQUEST['sort']) : 0;
$orderBy = ($selDateType=='voucher') ? "ORDER BY vdate DESC" : "ORDER BY cheque_date DESC,cheque_id";

$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
if($sort){
	if($sort=='voucherID')
		$orderBy = ($ascDes==="DESC") ? "ORDER BY voucher_no DESC" : "ORDER BY voucher_no";
	elseif($sort=='chequeID')
		$orderBy = ($ascDes==="DESC") ?  "ORDER BY cheque_id DESC" : "ORDER BY cheque_id";
	elseif($sort=='chequeDate')
		$orderBy = ($ascDes==="DESC") ?  "ORDER BY cheque_date DESC" : "ORDER BY cheque_date";
	elseif($sort=='voucherDate')
		$orderBy = ($ascDes==="DESC") ?  "ORDER BY vdate DESC" : "ORDER BY vdate";
	if($ascDes==="DESC")
		$ascDes="ASC";
	else if($ascDes==="ASC")
		$ascDes="DESC";
} 

$qVoucher = $db->select('voucher','*',$arr,$orderBy);
$txSearch = '';
if( isset($_POST['btnSearch2']) ){
	$txSearch = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	$qVoucher = $db->query("SELECT * FROM voucher WHERE voucher_no LIKE '%".$db->clean($txSearch)."%' OR cheque_id LIKE '%".$db->clean($txSearch)."%'");
}
$_SESSION['voucher_list_print_query'] = $db->last_query;
?>
<!-- body content: start here-->
<table width="180" cellspacing="4" cellpadding="6" border='0' align="right">
	<tr>
		<td width="5"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
		<td width="15"> Unclaimed Voucher</td>
	</tr>
</table><br><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Voucher List</h2>
			<div class="box-icon">
				<a id="print" class="thickbox" style="text-decoration:none;cursor:pointer;" title="Print this voucher" data-rel="tooltip" onclick="showThis(this.id,'voucher_list_print.php?','Voucher List Print','1')"><span style="color:white">Print&nbsp;</span> <i class="halflings-icon white print"></i></a>
			</div>
		</div>
		<div class="box-content">
			<form method="post">
				<table border="0" width="98%" align="center">
					<tr>
						<td colspan="5">Voucher No or Cheque #: <input type="text" name="txSearch" value="<?php echo $txSearch;?>">&nbsp;<input type="submit" name="btnSearch2" id="btnSearch2" value="Search" class="btn btn-small btn-primary">&nbsp;<input type="submit" name="btnViewAll" id="btnViewAll" value="View All" class="btn btn-small btn-primary">&nbsp;<a id="vlwv" class="btn btn-small btn-primary thickbox" onclick="showThis(this.id,'voucher_list_weekly.php?','Voucher List Weekly View','1')">View Weekly</a>&nbsp;<a id="vlwvr" class="btn btn-primary btn-small thickbox" onclick="showThis(this.id,'fuel-paid-report.php?','Paid Fuel Report','1')">Paid Fuel Report</a></td>
					</tr>
					<tr>
						<td colspan="5"><hr width="100%"></td>
					</tr>
					<tr>
						<td width="10%">
							<div style="font-size:10px"><strong>Date From:</strong></div>
							<div align="center">
								<select name="selDateType" id="selDateType" style="width:90px;">
									<option value="cheque" <?php if($selDateType=='cheque')echo 'selected="selected"';?>>Cheque</option>
									<option value="voucher" <?php if($selDateType=='voucher')echo 'selected="selected"';?>>Voucher</option>
								</select>
							</div>
						</td>
						<td width="20%">&nbsp;
							<div align="center">
								<select name="bdYear" id="bdYear" style="width:90px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('voucher','DISTINCT LEFT(vdate,4) as yr',array(),'ORDER BY vdate DESC');
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
							</div>
						</td>
						<td width="10%">&nbsp;
							<div align="left">
								<select name="txPayee" id="txPayee" data-rel="chosen" style="width:450px;">
									<option value="">All Supplier / Payee</option>
									<?php
									$qSup = $db->query("SELECT * FROM supplier WHERE supplierID IN (SELECT DISTINCT supplierID FROM voucher) ORDER BY name");
									while($rSup = $db->fetch_array($qSup)):
									?>
									<option value="<?php echo $rSup['supplierID']?>" <?php if($txPayee==$rSup['supplierID'])echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
									<?php endwhile;?>
								</select>
							</div>
						</td>
						<td width="20%">&nbsp;
							<div align="left">
								<select name="txVtype" id="txVtype" data-rel="chosen" style="width:160px;">
									<option value="">All Voucher Type</option>
									<?php
									$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
									while($rVtype = $db->fetch_array($qVtype)):
									?>
									<option value="<?php echo $rVtype['vt_id']?>" <?php if($txVtype==$rVtype['vt_id'])echo 'selected="selected"';?>><?php echo $rVtype['vt_name']?></option>
									<?php endwhile;?>
								</select>
							</div>
						</td>
						<td width="7%">&nbsp;<div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></div></td>
					</tr>
					<tr>
						<td colspan="5"><hr width="100%"></td>
					</tr>
				</table>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="5%"><a href="?<?php echo 'sort='.functions::encode('voucherID').'&ascDes='.$ascDes?>">Voucher #</a></th>
						<th width="9%"><a href="?<?php echo 'sort='.functions::encode('voucherDate').'&ascDes='.$ascDes?>">Voucher Date</a></th>
						<th width="5%"><a href="?<?php echo 'sort='.functions::encode('chequeID').'&ascDes='.$ascDes?>">Cheque #</a></th>
						<th width="9%"><a href="?<?php echo 'sort='.functions::encode('chequeDate').'&ascDes='.$ascDes?>">Cheque Date</a></th>
						<th width="23%">Supplier / Payee</th>
						<th width="9%"><div align="right">Amount</div></th>
						<th width="3%"><div align="center">Approved</div></th>
						<th width="9%"><div align="center">Options</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$total=0;
				while($rVouch = $db->fetch_array($qVoucher)):
					//Getting surchages
					$otherSurcharge  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'surcharge'));

					$amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVouch['voucher_id']).'"');
					$non_po = $db->result($amount_q);

					$amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rVouch['voucher_id']).'"');
					$po = $db->result($amount_po_q);
					$amount = $non_po + $po + $otherSurcharge;

					$approveStatus = $db->getValue('voucher','count(approved)',array('voucher_id'=>$rVouch['voucher_id']));
					$style='style="visibility:hidden;"';
					if($approveStatus==0)
						$style='';
					$claimedStatus = $db->getValue('voucher','count(claimed)',array('voucher_id'=>$rVouch['voucher_id'],'claimed'=>'2'));
					$bgColor='';

					$sel_w_tax = $db->getValue('voucher','witholding_tax',array('voucher_id'=>$rVouch['voucher_id']));
					$sel_w_tax_vat = $db->getValue('voucher','witholding_vat',array('voucher_id'=>$rVouch['voucher_id']));
					$hasAdvance = $db->getValue('voucher_advance_payment','count(*)',array('voucher_id_owner'=>$rVouch['voucher_id']));
					//if voucher has advance payment, it also computed the withholding tax.
					if($hasAdvance){
						$va = new voucherAdvance($rVouch['voucher_id']);
						$amount = $va->current_voucher_payable_amount;
					}
					else{//Deduction of the withholding tax.
						if($sel_w_tax_vat==1)
							$current_w_tax = ($amount && $sel_w_tax) ? ($amount / 1.12) * ($sel_w_tax / 100) : 0;
						else
						$current_w_tax = ($amount && $sel_w_tax) ? ($amount) * ($sel_w_tax / 100) : 0;
					$amount -= $current_w_tax;
					}
					$otherDeduction  = $db->getValue('voucher_deduction','sum(deduction_value)',array('voucher_id'=>$rVouch['voucher_id'],'charge_type'=>'deduction'));
					if($otherDeduction)
						$amount -= $otherDeduction;

					$total += $amount;

					if($claimedStatus)
						$bgColor = 'bgcolor="#f5ae00"';
				?>
					<tr id="rw<?php echo $rVouch['voucher_id']?>" <?php echo $bgColor;?>>
						<td><?php echo $rVouch['voucher_no'];?></td>
						<td><?php echo functions::datearr($rVouch['vdate']);?></td>
						<td><?php echo $rVouch['cheque_id'];?></td>
						<td><?php echo functions::datearr($rVouch['cheque_date']);?></td>
						<td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rVouch['supplierID']));?></td>
						<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						<td><div align="center"><?php echo ($rVouch['approved']) ? '<i class="halflings-icon ok"></i>' : '----';?></div></td>
						<td>
							<div align="left" style="padding-left:2px;">
								<a id="detail<?php echo $rVouch['voucher_id']?>" class="btn btn-mini btn-info" title="Voucher Details" data-rel="tooltip" href="voucher_view.php?vid=<?php echo functions::encode($rVouch['voucher_id'])?>"><i class="halflings-icon white zoom-in"></i></a>
								<a id="edit<?php echo $rVouch['voucher_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this voucher" data-rel="tooltip" onclick="showThis(this.id,'voucher_edit.php?vid=<?php echo functions::encode($rVouch['voucher_id']);?>','Voucher Details')" <?php echo $style;?>><i class="halflings-icon white pencil"></i></a>
								<?php if($approveStatus==0 && $amount==0){?>
								<a id="del<?php echo $rVouch['voucher_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Voucher" data-rel="tooltip" href="voucher_list.php?vidDel=<?php echo functions::encode($rVouch['voucher_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
					</tr>
				<?php endwhile;?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><div align="right">Total Amount</div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($total);?></strong></div></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
	if(confirm('Do you want to remove this Voucher?'))
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
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>