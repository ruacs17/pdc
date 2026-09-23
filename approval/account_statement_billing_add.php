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
$pyID = ( isset($_REQUEST['pyID']) && !empty($_REQUEST['pyID']) ) ? functions::decode($_REQUEST['pyID']) : '';
$account_type = ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) ) ? functions::decode($_REQUEST['t']) : '';
$selYear = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : '';
$selMon = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$proj_cost=0;
if($pyID){
	if( $db->getValue('account_statement','count(*)',array('project_income'=>$pyID)) ){
		functions::sendTo(functions::pageName().'?pID='.functions::encode($pID).'&t='.functions::encode($account_type).'&y='.functions::encode($selYear).'&m='.functions::encode($selMon));
		die();		
	}

}
$is_paid = (isset($_REQUEST['ispd']) && !empty($_REQUEST['ispd']) ) ? functions::decode($_REQUEST['ispd']) : 0;

$proj_cost = $db->getValue('project','proj_cost',array('proj_id'=>$pID));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Account Statement Billing Adding</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
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
if( isset($_POST['btnAdd']) ){
	$pi_id = ( isset($_POST['txpay_id']) && !empty($_POST['txpay_id']) ) ? functions::decode($_POST['txpay_id']) : '';
	$projid = ( isset($_POST['txproj_id']) && !empty($_POST['txproj_id']) ) ? functions::decode($_POST['txproj_id']) : '';
	$bill_id = ( isset($_POST['txTran_ID']) && !empty($_POST['txTran_ID']) ) ? trim($_POST['txTran_ID']) : '';
	$or_no = ( isset($_POST['txOR']) && !empty($_POST['txOR']) ) ? trim($_POST['txOR']) : '';

	$qSPI = $db->select('project_income','*',array('pi_id'=>$pi_id));
	$rSPI = $db->fetch_array($qSPI);
	$txBdate='';
	$txTran_AccntType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	$txTran_type = 'credit';
	$txTran_name = $rSPI['name'];
	$txTran_proj = $projid;
	$txTran_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;

	$txTran_vat = $rSPI['vat'];
	$txTran_ewt = $rSPI['ewt'];
	$txTran_retention = $rSPI['retention'];
	$txTran_contax = $rSPI['contractor'];
	$txTran_recoupment = $rSPI['recoupment'];

	$txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : NULL;
	$txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : NULL;
	$txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : NULL;
	$txBdate = ( functions::valid_date( $txbYear.'-'.$txbMon.'-'.$txbDay) ) ? $txbYear.'-'.$txbMon.'-'.$txbDay : NULL;

	if( $txTran_name && $txTran_amount && $txBdate && $txTran_type && $txTran_AccntType && $txTran_proj){
		$getNet = $db->getValue('project_income','amount - (vat + ewt + retention + contractor + recoupment) as net',array('pi_id'=>$pi_id));
		$projName = $db->getValue('project','proj_name',array('proj_id'=>$txTran_proj));
		$insid = $db->insert('account_statement',array('as_type'=>'credit','transaction'=>$txTran_name,'description'=>$projName,'as_date'=>$txBdate,'as_amount'=>$getNet,'account_type'=>$txTran_AccntType,'confirmned'=>2,'project_income'=>$pi_id,'transaction_type'=>'project income'));
		if($insid){
			$_SESSION['notif_id_list']=$insid;
			$db->update('project_income',array('pi_date'=>$txBdate,'bill_id'=>$bill_id,'or_no'=>$or_no),array('pi_id'=>$pi_id));
			$_SESSION['notif_success']='Billing Successfully Received!';
			functions::sendTo(functions::pageName().'?pID='.functions::encode($pID).'&t='.functions::encode($txTran_AccntType).'&y='.functions::encode($txbYear).'&m='.functions::encode($txbMon).'&ispd='.functions::encode($pi_id));
			die();
		}
		else{
			$_SESSION['notif_warning']='Transaction Failed! Please try again.';
			functions::sendTo(functions::pageName().'?pID='.functions::encode($pID).'&t='.functions::encode($txTran_AccntType).'&y='.functions::encode($txbYear).'&m='.functions::encode($txbMon));
			die();
		}
	}
	else{
		$_SESSION['notif_warning']='Transaction Failed! Please try again.';
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Billing Form</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="account_statement_billing_add.php?t=<?php echo functions::encode($account_type)?>&y=<?php echo functions::encode($selYear)?>&m=<?php echo functions::encode($selMon)?>" style="opacity:.9">Billing Transaction</a></li>
				<li><a href="account_statement_add_loan.php?t=<?php echo functions::encode($account_type)?>&y=<?php echo functions::encode($selYear)?>&m=<?php echo functions::encode($selMon)?>">Loan Transaction</a></li>
				<li><a href="account_statement_add_stockholder.php?t=<?php echo functions::encode($account_type)?>&y=<?php echo functions::encode($selYear)?>&m=<?php echo functions::encode($selMon)?>">Stock Share</a></li>
				<li><a href="account_statement_add.php?t=<?php echo functions::encode($account_type)?>&y=<?php echo functions::encode($selYear)?>&m=<?php echo functions::encode($selMon)?>">Regular Transaction</a></li>
			</ul>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table>
					<tr>
						<td>Select Project: </td>
						<td>
							<select name="txTran_proj" id="txTran_proj" data-rel="chosen" style="width:850px;font-size:12px;height:50px;" onChange="projSel(this.value)">
								<option value="">-- Select Project --</option>
								<?php $qProj = $db->select('project','*',array('project'=>'1'),'ORDER BY proj_name');
								while($rProj = $db->fetch_array($qProj)):
								?>
								<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($pID==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Project Cost: </td>
						<td><div><strong><?php echo functions::formatMoney($proj_cost);?></strong></div></td>
					</tr>
				</table>
				<div><br><br>
				<?php if($pID){?>
				<table width="100%" border="0" align="center" class="table table-hover table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="10%" scope="col"><div align="center">O.R.</div></th>
							<th width="17%" scope="col"><div align="left">Transaction</div></th>
							<th width="12%" scope="col"><div align="center">Billed</div></th>
							<th width="9%" scope="col"><div align="right">VAT</div></th>
							<th width="9%" scope="col"><div align="right">EWT</div></th>
							<th width="9%" scope="col"><div align="right">Retention</div></th>
							<th width="9%" scope="col"><div align="right">Contractor's Tax</div></th>
							<th width="9%" scope="col"><div align="right">Recoupment</div></th>
							<th width="7%" scope="col"><div align="right">Date Paid</div></th>
							<th width="7%" scope="col">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$totalBill=0;$totalBillPercent=0;$billPercent=0;$disc_amount=0;$amount=0;$selectedNetAmount = 0;
						$qPI = $db->select('project_income','*',array('proj_id'=>$pID),'ORDER BY submit_date,pi_date');
						while($rPI = $db->fetch_array($qPI)):
							$totalBill += $rPI['amount'];
							$billPercent = ($proj_cost && $rPI['amount']) ? (($rPI['amount'] / $proj_cost) * 100) : 0;
							$totalBillPercent += $billPercent;
							$bgColor='';
							if($rPI['pi_id']==$is_paid)
								$bgColor = 'bgcolor="#3efaba"';
							if($rPI['pi_id']==$pyID){
								$bgColor = 'bgcolor="#f5ae00"';
							}
							$account = $db->getValue('account_statement ast, voucher_type vt','vt_name',array('project_income'=>$rPI['pi_id']),'AND vt.vt_id=ast.account_type');
						?>
						<tr <?php echo $bgColor;?>>
							<td><div align="center"><?php echo $rPI['or_no'];?></div></td>
							<td><?php echo $rPI['name']; echo ($account) ? ' <i>('.$account.')</i>' : '';?></td>
							<td><div align="right"><?php echo ($billPercent) ? '<i>('.functions::formatMoney($billPercent).'%)</i>&nbsp;&nbsp;' : '';?><?php echo functions::formatMoney($rPI['amount']);?></td>
							<td><div align="right"><?php echo functions::formatMoney($rPI['vat']);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rPI['ewt']);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rPI['retention']);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rPI['contractor']);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rPI['recoupment']);?></div></td>
							<td><div align="right"><?php echo functions::datearr($rPI['pi_date']);?></div></td>
							<td>
								<div align="center">
								<?php
								if( $db->getValue('account_statement','count(*)',array('project_income'=>$rPI['pi_id'])) ){
									echo '<span class="label label-success">Paid</span>';
								}
								elseif($rPI['pi_id']==$pyID){
									echo 'Paying...';
									$selectedNetAmount = $rPI['amount'] - ($rPI['vat'] + $rPI['ewt'] + $rPI['retention'] + $rPI['contractor'] + $rPI['recoupment']);
								}
								else{
								?>
									<a id="pay<?php echo $rPI['pi_id'];?>" class="btn btn-mini btn-info" title="Pay this billing" data-rel="tooltip" href="<?php echo functions::pageName()?>?pID=<?php echo functions::encode($pID);?>&pyID=<?php echo functions::encode($rPI['pi_id']).'&t='.functions::encode($account_type).'&y='.functions::encode($selYear).'&m='.functions::encode($selMon);?>"><i class="halflings-icon white share-alt"></i></a>
								<?php }?>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div align="right"><?php echo ' <i>('.functions::formatMoney($totalBillPercent).'%)</i>&nbsp;&nbsp;&nbsp;<strong>'.functions::formatMoney($totalBill).'</strong>'?></div></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div align="right"><strong>Total Amount</strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($totalBill)?></strong></div></td>
							<td>&nbsp;</td>
						</tr>
					</tbody>
				</table>
				<?php }#if($pID)?>
				<?php if($pyID){?>
				<input type="hidden" name="txpay_id" id="txpay_id" value="<?php echo functions::encode($pyID);?>">
				<input type="hidden" name="txproj_id" id="txproj_id" value="<?php echo functions::encode($pID);?>">
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30">Account Type</td>
						<td width="43%">
							<select name="txVoType" id="txVoType">
								<option value="">--Account Type--</option>
								<?php 
								$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
								while($rVtype = $db->fetch_array($qVtype)):
								?>
								<option value="<?php echo $rVtype['vt_id']?>" <?php if($account_type==$rVtype['vt_id'])echo 'selected="selected"';?>><?php echo $rVtype['vt_name']?></option>
								<?php endwhile;?>
							</select>
							<span class="help-inline warning" id="msgVoType" style="font-weight:bold;" name="msgVoType"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Billing ID</td>
						<td>
							<input type="text" name="txTran_ID" id="txTran_ID" class="span6" value="" style="width:218px"/>
						</td>
					</tr>
					<tr>
						<td height="30">O.R. No:</td>
						<td>
							<input type="text" name="txOR" id="txOR" class="span6" value="" style="width:218px"/>
						</td>
					</tr>
					<tr>
						<td height="30">Payment Date</td>
						<td>
							<select name="bdMon" id="bdMon" style="width:80px;">
								<option value="">Month</option>
								<option value="01" <?php if($selMon=='01')echo 'selected="selected"';?>>Jan</option>
								<option value="02" <?php if($selMon=='02')echo 'selected="selected"';?>>Feb</option>
								<option value="03" <?php if($selMon=='03')echo 'selected="selected"';?>>Mar</option>
								<option value="04" <?php if($selMon=='04')echo 'selected="selected"';?>>Apr</option>
								<option value="05" <?php if($selMon=='05')echo 'selected="selected"';?>>May</option>
								<option value="06" <?php if($selMon=='06')echo 'selected="selected"';?>>Jun</option>
								<option value="07" <?php if($selMon=='07')echo 'selected="selected"';?>>Jul</option>
								<option value="08" <?php if($selMon=='08')echo 'selected="selected"';?>>Aug</option>
								<option value="09" <?php if($selMon=='09')echo 'selected="selected"';?>>Sep</option>
								<option value="10" <?php if($selMon=='10')echo 'selected="selected"';?>>Oct</option>
								<option value="11" <?php if($selMon=='11')echo 'selected="selected"';?>>Nov</option>
								<option value="12" <?php if($selMon=='12')echo 'selected="selected"';?>>Dec</option>
							</select>
							<select name="bdDay" id="bdDay" style="width:65px;">
								<option value="">Day</option>
								<?php for($i=1;$i<=31;$i++):?>
								<option value="<?php echo ($i < 10) ? '0'.$i : $i;?>"><?php echo $i;?></option>
								<?php endfor;?>
							</select>
							<select name="bdYear" id="bdYear" style="width:70px;">
								<option value="">Year</option>
								<?php for($y=(date('Y')+2);$y>=2012;$y--):?>
								<option value="<?php echo $y;?>" <?php if($selYear==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
								<?php endfor;?>
							</select>
							<span class="help-inline warning" id="msgDate" style="font-weight:bold;" name="msgDate"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Billing Amount</td>
						<td>
							<input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo functions::formatMoney($db->getValue('project_income','amount',array('pi_id'=>$pyID)));?>" onkeyup="FormatCurrency(this);" style="width:218px" readonly />
							<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Net Amount</td>
						<td>
							<input type="text" name="txTran_net_amount" id="txTran_net_amount" class="span6" value="<?php echo functions::formatMoney($selectedNetAmount);?>" onkeyup="FormatCurrency(this);" style="width:218px" readonly />
							<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
						</td>
					</tr>
				</table>
				<div align="center"><input type="submit" name="btnAdd" id="btnAdd" value=" PAY " class="btn btn-small btn-primary"></div>
				<?php } #$pyID?>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
function projSel(v){
	window.location="<?php echo functions::pageName()?>?pID=" + v + "<?php echo '&t='.functions::encode($account_type).'&y='.functions::encode($selYear).'&m='.functions::encode($selMon);?>";
}
$(document).ready(function(){
	var res = false;
	$('#btnAdd').click(function(){
		$('#msgVoType').html("");
		$('#msgDate').html("");
		$('#msgAmount').html("");

		if( $('#txVoType').val()=="" ){
			$('#txVoType').focus();
			$('#msgVoType').html("Account Type Required!");
			res=false;
		}
		else if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
			$('#msgDate').html("Date Required!");
			res=false;
		}
		else if( $('#txTran_amount').val()=="" ){
			$('#txTran_amount').focus();
			$('#msgAmount').html("Amount Required!");
			res=false;
		}
		else{
			if(confirm('Do you want to pay this billing?'))
				res=true;
			else
				res=false;
		}
		return res;
	});
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
<!-- end: JavaScript-->
</body>
</html>