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
$vd_id=0;
$txTran_amount=0;$mon='';$day='';$year='';$txTran_AccntType='';
$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;


$vp_id=(isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : $db->getValue('voucher_particular','vp_id',array('vp_title'=>'TRANSFER OF FUNDS','voucher_id'=>$vid,'vtype'=>'cash'));
$vd_id = $db->getValue('voucher_detail','vd_id',array('vp_id'=>$vp_id,'category_id'=>'51'));
$approved = $db->getValue('voucher','count(approved)',array('voucher_id'=>$vid));
if( isset($_POST['btnSave']) ){
	$txTran_AccntType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	$txTran_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;

	$from_AccntType = $db->getValue('voucher','vt_id',array('voucher_id'=>$vid));
	$fromAccount = $db->getValue('voucher_type','vt_name',array('vt_id'=>$from_AccntType));
	$toAccount = $db->getValue('voucher_type','vt_name',array('vt_id'=>$txTran_AccntType));
	$itemName = 'FUND TRANSFER FROM '.$fromAccount .' TO '.$toAccount;

	$txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';

	$txBdate = (functions::valid_date($txbYear.'-'.$txbMon.'-'.$txbDay)) ? $txbYear.'-'.$txbMon.'-'.$txbDay : NULL;

	$vp_id = $db->getValue('voucher_particular','vp_id',array('vp_title'=>$itemName,'voucher_id'=>$vid,'vtype'=>'transfer'));

	if($vid && $txBdate && $txTran_AccntType && $vp_id && $vd_id){
		$arrVP = array('vd_date'=>$txBdate,'category_id'=>'51','item'=>$itemName,'amount'=>$txTran_amount,'amount_issue'=>$txTran_amount,'vp_id'=>$vp_id);
		$db->update('voucher_detail',$arrVP,array('vd_id'=>$vd_id));
		$voucher_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));
		$db->update('account_statement',array('as_type'=>'credit','transaction'=>$itemName,'description'=>'voucher: '.$voucher_no,'as_date'=>$txBdate,'as_amount'=>round($txTran_amount,2),'account_type'=>$txTran_AccntType),array('tag_id'=>$vd_id));
	}
	$_SESSION['notif_success']='Changes Saved!';
	functions::sendTo('voucher_fund_transfer.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
	die();
}


if( isset($_POST['btnAdd']) ){
	$txTran_AccntType = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
	$txTran_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;

	$from_AccntType = $db->getValue('voucher','vt_id',array('voucher_id'=>$vid));
	$fromAccount = $db->getValue('voucher_type','vt_name',array('vt_id'=>$from_AccntType));
	$toAccount = $db->getValue('voucher_type','vt_name',array('vt_id'=>$txTran_AccntType));
	$itemName = 'FUND TRANSFER FROM '.$fromAccount .' TO '.$toAccount;

	$txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$txBdate = (functions::valid_date($txbYear.'-'.$txbMon.'-'.$txbDay)) ? $txbYear.'-'.$txbMon.'-'.$txbDay : NULL;


	if( $db->getValue('voucher_particular','count(voucher_id)',array('vp_title'=>$itemName,'voucher_id'=>$vid,'vtype'=>'transfer')) )
		$vp_id = $db->getValue('voucher_particular','vp_id',array('vp_title'=>$itemName,'voucher_id'=>$vid,'vtype'=>'transfer'));
	else{
		$vp_id = $db->insert('voucher_particular',array('vp_title'=>$itemName,'voucher_id'=>$vid,'vtype'=>'transfer'));
	}

	if($vid && $txBdate && $txTran_AccntType && $vp_id){
		$arr = array('vd_date'=>$txBdate,'category_id'=>'51','item'=>$itemName,'amount'=>$txTran_amount,'amount_issue'=>$txTran_amount,'vp_id'=>$vp_id);
		$vd_id = $db->insert('voucher_detail',$arr);
		$voucher_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$vid));
		$db->insert('account_statement',array('as_type'=>'credit','transaction'=>$itemName,'description'=>'voucher: '.$voucher_no,'as_date'=>$txBdate,'as_amount'=>round($txTran_amount,2),'account_type'=>$txTran_AccntType,'confirmned'=>'2','tag_id'=>$vd_id,'transaction_type'=>'fund transfer'));
		$_SESSION['notif_success']='Item Added!';
	}
	functions::sendTo('voucher_fund_transfer.php?vid='.functions::encode($vid).'&vpid='.functions::encode($vp_id));
	die();
}

$txTran_AccntType = $db->getValue('account_statement','account_type',array('tag_id'=>$vd_id));
$stmntDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$vid));
$txTran_amount = $db->getValue('account_statement','as_amount',array('tag_id'=>$vd_id));
if($stmntDate){
	$sDate = explode("-",$stmntDate);
	$mon = $sDate[1];
	$day = $sDate[2];
	$year = $sDate[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Fund Transfer</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Fund Transfer Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30">Destination Account</td>
						<td width="43%">
							<?php if($approved==0){?>
							<select name="txVoType" id="txVoType">
								<option value="">--Account Type--</option>
								<?php 
								$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
								while($rVtype = $db->fetch_array($qVtype)):
								?>
								<option value="<?php echo $rVtype['vt_id']?>" <?php if($txTran_AccntType==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
								<?php endwhile;?>
							</select>
							<?php }
							else
								echo $db->getValue('voucher_type','vt_name',array('vt_id'=>$txTran_AccntType));
							?>
							<span class="help-inline warning" id="msgVoType" style="font-weight:bold;" name="msgVoType"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Transaction Date</td>
						<td>
							<?php if($approved==0){?>
								<select name="bdMon" id="bdMon" style="width:80px;">
									<option value="">Month</option>
									<option value="01" <?php if($mon=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($mon=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($mon=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($mon=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($mon=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($mon=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($mon=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($mon=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($mon=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($mon=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($mon=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($mon=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="bdDay" id="bdDay" style="width:60px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($day==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
								<select name="bdYear" id="bdYear" style="width:70px;">
									<option value="">Year</option>
									<?php for($y=(date('Y')+1);$y>=2015;$y--):?>
									<option value="<?php echo $y;?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
									<?php endfor;?>
								</select>
							<?php }
							else
								echo functions::datearr($stmntDate);
							?>
							<span class="help-inline warning" name="msgDate" id="msgDate" style="font-weight:bold;"></span>
						</td>
					</tr>
					<tr>
						<td height="30">Amount</td>
						<td>
							<?php if($approved==0){?>
								<input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo ($txTran_amount) ? functions::formatMoney($txTran_amount) : '';?>" onkeyup="FormatCurrency(this);" />
							<?php }
							else
								echo functions::formatMoney($txTran_amount);
							?>
							<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
						</td>
					</tr>
				</table>
				<?php if($approved==0){?>
					<div align="center">
						<?php if($vd_id==0){?><input type="submit" name="btnAdd" id="btnAdd" value=" ADD " class="btn btn-small btn-primary"><?php }?>
						<?php if($vd_id){?><input type="submit" name="btnSave" id="btnAdd" value=" SAVE " class="btn btn-small btn-primary"><?php }?>
					</div>
				<?php }?>
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
		else
			res=true;
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