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
$biid = ( isset($_REQUEST['biid']) && !empty($_REQUEST['biid']) ) ? functions::decode($_REQUEST['biid']) : '';
if( isset($_POST['btnSave']) ){

	$bi_date='';
	$bi_name = ( isset($_POST['txTran_name']) && !empty($_POST['txTran_name']) ) ? $_POST['txTran_name'] : '';
	$vat_percent = ( isset($_POST['txTran_vat']) && !empty($_POST['txTran_vat']) ) ? $_POST['txTran_vat'] : 0;
	$ewt_percent = ( isset($_POST['txTran_ewt']) && !empty($_POST['txTran_ewt']) ) ? $_POST['txTran_ewt'] : 0;
	$bi_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;

	$txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$bi_date = functions::valid_date($txbYear.'-'.$txbMon.'-'.$txbDay) ? $txbYear.'-'.$txbMon.'-'.$txbDay : NULL;

	if( $bi_name && $bi_amount && $bi_date && $biid){
		$db->update('billing_income',array('bi_name'=>$bi_name,'bi_date'=>$bi_date,'bi_amount'=>$bi_amount,'vat_percent'=>$vat_percent,'ewt_percent'=>$ewt_percent),array('bi_id'=>$biid));
		$_SESSION['notif_success']="Billing Statement Updated!";
		functions::sendTo(functions::pageName().'?biid='.functions::encode($biid));
		die();
	}
}
$selMon=0;$selDay=0;$selYear=0;
$q = $db->select('billing_income','*',array('bi_id'=>$biid));
$r = $db->fetch_array($q);
$bi_name = ($r['bi_name']) ? $r['bi_name'] : '';
$bi_amount = ($r['bi_amount']) ? functions::formatMoney($r['bi_amount']) : '';
$vat_percent = ($r['vat_percent']) ? $r['vat_percent'] : 0;
$ewt_percent = ($r['ewt_percent']) ? $r['ewt_percent'] : 0;
$bi_date = ($r['bi_date']) ? $r['bi_date'] : '';
$dateArr = explode("-",$bi_date);
if( count($dateArr)==3 ){
	$selMon = $dateArr[1];
	$selYear = $dateArr[0];
	$selDay = $dateArr[2];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Billing Statement Update</title>
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
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Billing Income Update</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"><div align="right">Billing Name</div></td>
						<td width="43%">
							<input type="text" name="txTran_name" id="txTran_name" class="span6" value="<?php echo $bi_name ?>">
							<span class="help-inline warning" id="msgName" style="font-weight:bold;" name="msgName"></span>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Transaction Date</div></td>
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
							<select name="bdDay" id="bdDay" style="width:60px;">
								<option value="">Day</option>
								<?php for($i=1;$i<=31;$i++):?>
								<option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($selDay==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
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
						<td height="30"><div align="right">Amount</div></td>
						<td>
							<input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo $bi_amount?>" onkeyup="FormatCurrency(this);" />
							<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">VAT %</div></td>
						<td>
							<input type="text" name="txTran_vat" id="txTran_vat" class="span6" value="<?php echo $vat_percent?>" onkeyup="FormatCurrency(this);" />
							<span class="help-inline warning" id="msgVat" style="font-weight:bold;" name="msgVat"></span>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">EWT %</div></td>
						<td>
							<input type="text" name="txTran_ewt" id="txTran_ewt" class="span6" value="<?php echo $ewt_percent?>" onkeyup="FormatCurrency(this);" />
							<span class="help-inline warning" id="msgEWT" style="font-weight:bold;" name="msgEWT"></span>
						</td>
					</tr>
				</table>
				<div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-small btn-primary"></div>
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
$(document).ready(function(){
	var res = false;
	$('#btnSave').click(function(){
		$('#msgName').html("");
		$('#msgDate').html("");
		$('#msgAmount').html("");

		if( $('#txTran_name').val()=="" ){
			$('#txTran_name').focus();
			$('#msgName').html("Transaction Name Required!");
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
			if(confirm('Do you want to update this statement?'))
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
<!-- end: JavaScript-->
</body>
</html>