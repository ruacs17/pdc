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
$pID = ( isset($_REQUEST['pID']) && !empty($_REQUEST['pID']) ) ? functions::decode($_REQUEST['pID']) : '';
$editTrue=0;
$selMon=0; $selYear='';$selDay='';
$txDate = date('Y-m-d');

$pDel = ( isset($_REQUEST['pDel']) && !empty($_REQUEST['pDel']) ) ? functions::decode($_REQUEST['pDel']) : 0;
if($pDel){
	if( $db->getValue('account_statement','count(*)',array('project_income'=>$pDel))==0 ){
		$db->delete('project_income',array('pi_id'=>$pDel));
		$_SESSION['notif_warning']='Transaction Removed!';
	}
	functions::sendTo(functions::pageName().'?pID='.functions::encode($pID));
	die();
}
$editID = '';
$pEdt = ( isset($_REQUEST['pEdt']) && !empty($_REQUEST['pEdt']) ) ? functions::decode($_REQUEST['pEdt']) : '';
$txTran_name='';$txTran_amount='';$txTran_vat='';$txTran_ewt='';$txTran_retention='';$txTran_contax='';$txTran_recoupment=''; $txBillPercent=0;
if( isset($_REQUEST['pEdt']) && !empty($_REQUEST['pEdt']) ){
	$editID = functions::decode($_REQUEST['pEdt']);
	$editTrue = $db->getValue('project_income','count(*)',array('pi_id'=>$editID));
	$qvedt = $db->select('project_income','*',array('pi_id'=>$editID));
	$rvedt = $db->fetch_array($qvedt);
	$proj_cost = $db->getValue('project','proj_cost',array('proj_id'=>$rvedt['proj_id']));
	$txTran_name = $rvedt['name'];
	$txBillPercent = ($rvedt['amount'] / $proj_cost) * 100;;
	$txTran_amount = functions::formatMoney($rvedt['amount']);
	$txTran_vat = functions::formatMoney($rvedt['vat']);
	$txTran_ewt = functions::formatMoney($rvedt['ewt']);
	$txTran_retention = functions::formatMoney($rvedt['retention']);
	$txTran_contax = functions::formatMoney($rvedt['contractor']);
	$txTran_recoupment = functions::formatMoney($rvedt['recoupment']);
	$txDateEdt = explode("-",$rvedt['submit_date']);
	$txDate = (count($txDateEdt)==3) ? $rvedt['submit_date'] : date('Y-m-d');
}
if( isset($_POST['btnAdd']) ){
	$txTran_name = ( isset($_POST['txTran_name']) && !empty($_POST['txTran_name']) ) ? $_POST['txTran_name'] : '';
	$txTran_proj = $pID;
	$txTran_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;

	$txTran_vat = ( isset($_POST['txTran_vat']) && !empty($_POST['txTran_vat']) ) ? functions::moneyToDouble($_POST['txTran_vat']) : 0;
	$txTran_ewt = ( isset($_POST['txTran_ewt']) && !empty($_POST['txTran_ewt']) ) ? functions::moneyToDouble($_POST['txTran_ewt']) : 0;
	$txTran_retention = ( isset($_POST['txTran_retention']) && !empty($_POST['txTran_retention']) ) ? functions::moneyToDouble($_POST['txTran_retention']) : 0;
	$txTran_contax = ( isset($_POST['txTran_contax']) && !empty($_POST['txTran_contax']) ) ? functions::moneyToDouble($_POST['txTran_contax']) : 0;
	$txTran_recoupment = ( isset($_POST['txTran_recoupment']) && !empty($_POST['txTran_recoupment']) ) ? functions::moneyToDouble($_POST['txTran_recoupment']) : 0;

	$txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$arrDate = explode("-",$txDate);
	$submit_date = (count($arrDate)==3) ? $txDate : date('Y-m-d');

	if( $txTran_name && $txTran_amount && $submit_date && $txTran_proj){
		$ins = $db->insert('project_income',array('proj_id'=>$txTran_proj,'name'=>$txTran_name,'submit_date'=>$submit_date,'amount'=>$txTran_amount,'vat'=>$txTran_vat,'ewt'=>$txTran_ewt,'retention'=>$txTran_retention,'contractor'=>$txTran_contax,'recoupment'=>$txTran_recoupment));
		$_SESSION['notif_success']='New Transaction Added!';
		$_SESSION['notif_id3_list']=$ins;
		functions::sendTo(functions::pageName().'?pID='.functions::encode($pID));
		die();
	}
}

if( isset($_POST['btnSave']) ){
	$txedt_id = ( isset($_POST['txedt_id']) && !empty($_POST['txedt_id']) ) ? functions::decode($_POST['txedt_id']) : '';
	$txedt_pid = ( isset($_POST['txedt_pid']) && !empty($_POST['txedt_pid']) ) ? functions::decode($_POST['txedt_pid']) : '';
	$txTran_name = ( isset($_POST['txTran_name']) && !empty($_POST['txTran_name']) ) ? $_POST['txTran_name'] : '';
	$txTran_proj = ( $db->getValue('project','count(*)',array('proj_id'=>$txedt_pid)) ) ? $txedt_pid : 0;
	$txTran_pi_id= ( $db->getValue('project_income','count(*)',array('pi_id'=>$txedt_id)) ) ? $txedt_id : 0;
	$txTran_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;

	$txTran_vat = ( isset($_POST['txTran_vat']) && !empty($_POST['txTran_vat']) ) ? functions::moneyToDouble($_POST['txTran_vat']) : 0;
	$txTran_ewt = ( isset($_POST['txTran_ewt']) && !empty($_POST['txTran_ewt']) ) ? functions::moneyToDouble($_POST['txTran_ewt']) : 0;
	$txTran_retention = ( isset($_POST['txTran_retention']) && !empty($_POST['txTran_retention']) ) ? functions::moneyToDouble($_POST['txTran_retention']) : 0;
	$txTran_contax = ( isset($_POST['txTran_contax']) && !empty($_POST['txTran_contax']) ) ? functions::moneyToDouble($_POST['txTran_contax']) : 0;
	$txTran_recoupment = ( isset($_POST['txTran_recoupment']) && !empty($_POST['txTran_recoupment']) ) ? functions::moneyToDouble($_POST['txTran_recoupment']) : 0;

	$txDate = ( isset($_POST['txDate']) && !empty($_POST['txDate']) ) ? $_POST['txDate'] : '';
	$arrDate = explode("-",$txDate);
	$submit_date = (count($arrDate)==3) ? $txDate : date('Y-m-d');

	if( $txTran_name && $txTran_amount && $submit_date && $txTran_proj && $txTran_pi_id){
		$db->update('project_income',array('proj_id'=>$txTran_proj,'name'=>$txTran_name,'submit_date'=>$submit_date,'amount'=>$txTran_amount,'vat'=>$txTran_vat,'ewt'=>$txTran_ewt,'retention'=>$txTran_retention,'contractor'=>$txTran_contax,'recoupment'=>$txTran_recoupment),array('pi_id'=>$txTran_pi_id));
		$getNet = $db->getValue('project_income','amount - (vat + ewt + retention + contractor + recoupment) as net',array('pi_id'=>$txTran_pi_id)); #temporary
		$db->update('account_statement',array('transaction'=>$txTran_name,'as_amount'=>$getNet),array('project_income'=>$txTran_pi_id)); #temporary
		$_SESSION['notif_success']='Transaction Updated!';
		$_SESSION['notif_id3_list']=$txTran_pi_id;
		functions::sendTo(functions::pageName().'?pID='.functions::encode($pID));
		die();
	}
}
$proj_cost = $db->getValue('project','proj_cost',array('proj_id'=>$pID));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Billing</title>
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
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Billing Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post"><br>
				<div>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$pID));?></strong></div>
				<div>Cost: <strong><?php echo functions::formatMoney($proj_cost);?></strong></div><br><br>
				<input type="hidden" name="txedt_id" id="txedt_id" value="<?php echo functions::encode($editID);?>">
				<input type="hidden" name="txedt_pid" id="txedt_pid" value="<?php echo functions::encode($pID);?>">
				<table width="100%" border="0" align="center" class="table table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="12%" scope="col"><div align="left">Transaction</div></th>
							<th width="10%" scope="col"><div align="left">Billed</div></th>
							<th width="10%" scope="col"><div align="center">VAT</div></th>
							<th width="10%" scope="col"><div align="center">EWT</div></th>
							<th width="10%" scope="col"><div align="center">Retention</div></th>
							<th width="10%" scope="col"><div align="center">Contractor's Tax</div></th>
							<th width="10%" scope="col"><div align="center">Recoupment</div></th>
							<th width="10%" scope="col"><div align="center">Submitted</div></th>
							<th width="10%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<tr style="background-color:#f7ebeb">
							<td>
								<div align="left">
									<select name="txTran_name" id="txTran_name" style="width:218px;" data-rel="chosen">
										<option value="">--select--</option>
										<option value="1st Billing Payment" <?php if($txTran_name=="1st Billing Payment")echo 'selected="selected"';?> >1st Billing Payment</option>
										<option value="2nd Billing Payment" <?php if($txTran_name=="2nd Billing Payment")echo 'selected="selected"';?> >2nd Billing Payment</option>
										<option value="3rd Billing Payment" <?php if($txTran_name=="3rd Billing Payment")echo 'selected="selected"';?> >3rd Billing Payment</option>
										<option value="4th Billing Payment" <?php if($txTran_name=="4th Billing Payment")echo 'selected="selected"';?> >4th Billing Payment</option>
										<option value="5th Billing Payment" <?php if($txTran_name=="5th Billing Payment")echo 'selected="selected"';?> >5th Billing Payment</option>
										<option value="6th Billing Payment" <?php if($txTran_name=="6th Billing Payment")echo 'selected="selected"';?> >6th Billing Payment</option>
										<option value="7th Billing Payment" <?php if($txTran_name=="7th Billing Payment")echo 'selected="selected"';?> >7th Billing Payment</option>
										<option value="Advance Payment" <?php if($txTran_name=="Advance Payment")echo 'selected="selected"';?> >Advance Payment</option>
										<option value="Balance Payment" <?php if($txTran_name=="Balance Payment")echo 'selected="selected"';?> >Balance Payment</option>
										<option value="Buy-out" <?php if($txTran_name=="Buy-out")echo 'selected="selected"';?> >Buy-out</option>
										<option value="Final Payment" <?php if($txTran_name=="Final Payment")echo 'selected="selected"';?> >Final Payment</option>
										<option value="Partial Billing" <?php if($txTran_name=="Partial Billing")echo 'selected="selected"';?> >Partial Billing</option>
										<option value="Release of Bidders Bond" <?php if($txTran_name=="Release of Bidders Bond")echo 'selected="selected"';?> >Release of Bidders Bond</option>
										<option value="Retention Payment" <?php if($txTran_name=="Retention Payment")echo 'selected="selected"';?> >Retention Payment</option>
										<option value="Variation Order No. 1 Payment" <?php if($txTran_name=="Variation Order No. 1 Payment")echo 'selected="selected"';?> >Variation Order No. 1 Payment</option>
										<option value="Variation Order No. 2 Payment" <?php if($txTran_name=="Variation Order No. 2 Payment")echo 'selected="selected"';?> >Variation Order No. 2 Payment</option>
										<option value="Variation Order No. 3 Payment" <?php if($txTran_name=="Variation Order No. 3 Payment")echo 'selected="selected"';?> >Variation Order No. 3 Payment</option>
										<option value="Variation Order No. 4 Payment" <?php if($txTran_name=="Variation Order No. 4 Payment")echo 'selected="selected"';?> >Variation Order No. 4 Payment</option>
										<option value="Variation Order No. 5 Payment" <?php if($txTran_name=="Variation Order No. 5 Payment")echo 'selected="selected"';?> >Variation Order No. 5 Payment</option>
									</select>
								</div>
								<span class="help-inline warning" id="msgName" style="font-weight:bold;" name="msgName"></span>
							</td>
							<td>
								<div align="left">
									<input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo $txTran_amount?>" onkeyup="FormatCurrency(this);" style="width:120px;text-align:center;font-size:13px;" /><br>
									<input type="text" name="txTran_amountPercent" id="txTran_amountPercent" class="span6" value="<?php echo $txBillPercent;?>" onkeypress="return checkinput(this, event);" style="width:120px;text-align:center;font-size:13px;" />%
								</div>
								<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
							</td>
							<td><div align="center"><input type="text" name="txTran_vat" id="txTran_vat" class="span6" value="<?php echo $txTran_vat?>" onkeyup="FormatCurrency(this);" style="width:100px;text-align:center;font-size:13px;" /></div></td>
							<td><div align="center"><input type="text" name="txTran_ewt" id="txTran_ewt" class="span6" value="<?php echo $txTran_ewt?>" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" style="width:100px;text-align:center;font-size:13px;" /></div></td>
							<td><div align="center"><input type="text" name="txTran_retention" id="txTran_retention" class="span6" value="<?php echo $txTran_retention?>" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" style="width:100px;text-align:center;font-size:13px;" /></div></td>
							<td><div align="center"><input type="text" name="txTran_contax" id="txTran_contax" class="span6" value="<?php echo $txTran_contax?>" onkeyup="FormatCurrency(this);" onkeypress="return checkinput(this, event);" style="width:100px;text-align:center;font-size:13px;" /></div></td>
							<td><div align="center"><input type="text" name="txTran_recoupment" id="txTran_recoupment" class="span6" value="<?php echo $txTran_recoupment?>" style="width:100px;text-align:center;font-size:13px;" /></div></td>
							<td>
								<div align="center">
									<a href="javascript:NewCssCal('txDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
									<input name="txDate" type="text" class="span6 mytextbox" id="txDate" value="<?php echo $txDate?>" style="width: 90px;">
								</div>
							</td>
							<td>
								<div align="center">
									<?php #if( $db->getValue('account_statement','count(*)',array('project_income'=>$pEdt))==0 ){?>
									<?php 
									if($editTrue){
										echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-mini btn-primary">';
									?>
										<a id="editCanl" title="Cancel Update"  class="btn btn-mini" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>">Cancel</a>
									<?php
									}else
									echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-small btn-primary">';
									?>
									<?php #} ?>
								</div>
							</td>
						</tr>
					</tbody>
				</table><br>
				<table id="tblist" width="100%" border="0" align="center" class="table table-striped <?php if(!isset($_SESSION['notif_id3_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="15%" scope="col"><div align="left">Transaction</div></th>
							<th width="15%" scope="col"><div align="right">Billed</div></th>
							<th width="10%" scope="col"><div align="right">VAT</div></th>
							<th width="10%" scope="col"><div align="right">EWT</div></th>
							<th width="10%" scope="col"><div align="right">Retention</div></th>
							<th width="10%" scope="col"><div align="right">Contractor's Tax</div></th>
							<th width="10%" scope="col"><div align="right">Recoupment</div></th>
							<th width="10%" scope="col"><div align="center">Submitted</div></th>
							<th width="68%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$totalBill=0;$totalBillPercent=0;$billPercent=0;$disc_amount=0;$amount=0;
						$qPI = $db->select('project_income','*',array('proj_id'=>$pID),' AND lower(name) != "advance payment" AND  lower(name) != "retention payment" ORDER BY submit_date,pi_date');
						while($rPI = $db->fetch_array($qPI)):
						$totalBill += $rPI['amount'];
						$billPercent = ($proj_cost && $rPI['amount']) ? (($rPI['amount'] / $proj_cost) * 100) : 0;
						$totalBillPercent += $billPercent;
						?>
						<tr id="rw<?php echo $rPI['pi_id'];?>">
						<td><?php echo $rPI['name'];?></td>
						<td><div align="right"><?php echo ($billPercent) ? '<i>('.functions::formatMoney($billPercent).'%)</i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '';?><?php echo functions::formatMoney($rPI['amount']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['vat']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['ewt']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['retention']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['contractor']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['recoupment']);?></div></td>
						<td><div align="center"><?php echo functions::datearr($rPI['submit_date']);?></div></td>
						<td>
							<div align="center">
								<?php
								$paidQ = $db->select('account_statement','*',array('project_income'=>$rPI['pi_id']));
								if( $db->num_rows($paidQ) ){
									$pdr = $db->fetch_array($paidQ);
									$paid_date = functions::datearr($pdr['as_date']);
									$accntype = $db->getValue('voucher_type','vt_name',array('vt_id'=>$pdr['account_type']));
								?>
								<a id="edit<?php echo $rPI['pi_id'];?>" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>&pEdt=<?php echo functions::encode($rPI['pi_id']);?>">Paid</a><br>(<?php echo $paid_date;?>)<br><?php echo $accntype ?>
								<?php
								}else{
								?>
								<a id="edit<?php echo $rPI['pi_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>&pEdt=<?php echo functions::encode($rPI['pi_id']);?>"><i class="halflings-icon white pencil"></i></a>
								<a id="del<?php echo $rPI['pi_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>&pDel=<?php echo functions::encode($rPI['pi_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
						</tr>
						<?php endwhile;?>
						<?php
						#$totalBill=0;$totalBillPercent=0;$billPercent=0;$disc_amount=0;$amount=0;
						$qPI = $db->select('project_income','*',array('proj_id'=>$pID),' AND (lower(name) = "advance payment" OR lower(name) = "retention payment") ORDER BY submit_date,pi_date');
						while($rPI = $db->fetch_array($qPI)):
						?>
						<tr>
						<td><?php echo $rPI['name'];?></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['amount']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['vat']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['ewt']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['retention']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['contractor']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rPI['recoupment']);?></div></td>
						<td><div align="center"><?php echo functions::datearr($rPI['submit_date']);?></div></td>
						<td>
							<div align="center">
								<?php
								$paidQ = $db->select('account_statement','*',array('project_income'=>$rPI['pi_id']));
								if( $db->num_rows($paidQ) ){
									$pdr = $db->fetch_array($paidQ);
									$paid_date = functions::datearr($pdr['as_date']);
									$accntype = $db->getValue('voucher_type','vt_name',array('vt_id'=>$pdr['account_type']));
								?>
								<a id="edit<?php echo $rPI['pi_id'];?>" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>&pEdt=<?php echo functions::encode($rPI['pi_id']);?>">Paid</a><br>(<?php echo $paid_date;?>)<br><?php echo $accntype ?>
								<?php
								}else{
								?>
								<a id="edit<?php echo $rPI['pi_id'];?>" class="btn btn-mini btn-warning" title="Update this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>&pEdt=<?php echo functions::encode($rPI['pi_id']);?>"><i class="halflings-icon white pencil"></i></a>
								<a id="del<?php echo $rPI['pi_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>&pDel=<?php echo functions::encode($rPI['pi_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td><div align="right"><strong>Total Amount</strong></div></td>
							<td><div align="right"><strong><?php echo '<i>('.functions::formatMoney($totalBillPercent).'%)</i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'?><?php echo functions::formatMoney($totalBill)?></strong></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</tbody>
				</table>
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
function delt(){
	if(confirm('Do you want to remove this transaction?'))
		return true;
	else
		return false; 
}
$(document).ready(function(){
	var res = false;
	$('#btnAdd,#btnSave').click(function(){
		$('#msgName').html("");
		$('#msgAmount').html("");
		if( $('#txTran_name').val()=="" ){
			$('#txTran_name').focus();
			$('#msgName').html("Transaction Name Required!");
			res=false;
		}
		else if( $('#txTran_amount').val()=="" ){
			$('#txTran_amount').focus();
			$('#msgAmount').html("Amount Required!");
			res=false;
		}
		else
			res=true;
		return res;
	});

	$('#txTran_amount').keyup(function(){
		var ProjCost = "<?php echo $proj_cost;?>"
		var percent = 0;
		var txAmount = $('#txTran_amount').val();
		var vsqft_float = txAmount.replace(",", "");
		var amount = Number( vsqft_float.replace(/[^0-9\.]+/g,""));
		if(amount && ProjCost){
			percent = parseFloat(amount / ProjCost) * 100;
			$('#txTran_amountPercent').val(percent);
			FormatCurrency(document.getElementById('txTran_amount'));
		}
		else
			$('#txTran_amountPercent').val(0);
		computeCharge();
	});
	$('#txTran_amountPercent').keyup(function(){
		var ProjCost = "<?php echo $proj_cost;?>"
		var percent=0;
		var billed=0;
		if( $('#txTran_amountPercent').val() > 0 ){
			percent = $('#txTran_amountPercent').val() / 100;
			billed = ProjCost * percent;
		}
		$('#txTran_amount').val(billed);
		FormatCurrency(document.getElementById('txTran_amount'));
		computeCharge();
	});
	function computeCharge(){
		var ProjCost = "<?php echo $proj_cost;?>"
		var percent = 0;
		var advance_payment=0;
		var project_cost=0;
		var recoupment=0;
		var txAmount = $('#txTran_amount').val();
		var vsqft_float = txAmount.replace(",", "");
		var amount = Number( vsqft_float.replace(/[^0-9\.]+/g,""));

		var ap = "<?php echo $db->getValue('project_income','amount',array('proj_id'=>$pID,'lower(name)'=>'advance payment'))?>";
		var proj_cost = "<?php echo $db->getValue('project','proj_cost',array('proj_id'=>$pID))?>";
		if(proj_cost != "")
			project_cost = proj_cost;
		if(ap != "")
			advance_payment = ap;

		var percent=0;
		if(project_cost && amount){
			percent = (amount / project_cost);
			recoupment = advance_payment * percent;
			recoupment = parseFloat(recoupment).toFixed(2);
		}

		if($('#txTran_amount').val() == "")
			amount = 0;

		var vat = (amount / 1.12) * .05;
		var vat_ret = parseFloat(vat).toFixed(2);
		var ewt = (amount / 1.12) * .02;
		var ewt_ret = parseFloat(ewt).toFixed(2);

		var ret = amount * .1;
		var retention = parseFloat(ret).toFixed(2);

		$('#txTran_vat').val(vat_ret);
		FormatCurrency(document.getElementById('txTran_vat'));

		$('#txTran_ewt').val(ewt_ret);
		FormatCurrency(document.getElementById('txTran_ewt'));

		$('#txTran_retention').val(retention);
		FormatCurrency(document.getElementById('txTran_retention'));

		$('#txTran_recoupment').val(recoupment);
		FormatCurrency(document.getElementById('txTran_recoupment'));
	}
});
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
<?php if(isset($_SESSION['notif_id3_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id3_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id3_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id3_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id3_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id3_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>