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
$did = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;

if( isset($_POST['btnSave']) ){
	$txBdate='';
	$did = ( isset($_POST['did']) && !empty($_POST['did']) ) ? functions::decode($_POST['did']) : '';
	$txTran_amount = ( isset($_POST['txTran_amount']) && !empty($_POST['txTran_amount']) ) ? functions::moneyToDouble($_POST['txTran_amount']) : 0;
	$txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	if($txbMon && $txbDay && $txbYear)
		$txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;
	if($txTran_amount===0){
		$db->delete('project_income',array('pi_id'=>$did)); 
		functions::sendTo('dash-proj-income-statement-edit.php?eid='.functions::encode($did));
		die(); 
	}
	if( $txBdate && $did ){
		$db->update('project_income',array('pi_date'=>$txBdate,'amount'=>$txTran_amount),array('pi_id'=>$did)); 
		functions::sendTo('dash-proj-income-statement-edit.php?eid='.functions::encode($did));
		die(); 
	}
}
$txTran_amount=0;$mon='';$day='';$year='';$sType='';
$q = $db->select('project_income','*',array('pi_id'=>$did));
while($r = $db->fetch_array($q)):
	$txTran_amount = $r['amount'];
	$vdate = $r['pi_date'];
	$xdate = explode('-',$vdate);
	if(count($xdate)==3){
		$mon = $xdate[1];
		$day = $xdate[2];
		$year = $xdate[0];
	}
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Account Statement Adding</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Statement Update Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<input type="hidden" name="did" id="did" value="<?php echo functions::encode($did);?>">
				<table width="50%" align="center" border="0" style="background-color:#E4E1E1">
					<tr>
						<th><div align="center">Transaction Date</div></th>
						<th><div align="center">Amount</div></th>
						<th>&nbsp;</th>
					</tr>
					<tr>
						<td width="43%">
							<div align="center">
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
									<option value="<?php echo ($i<10) ? '0'.$i : $i;?>" <?php if($day==$i)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
								<select name="bdYear" id="bdYear" style="width:70px;">
									<option value="">Year</option>
									<?php for($y=(date('Y')+1);$y>=2015;$y--):?>
									<option value="<?php echo $y;?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
									<?php endfor;?>
								</select>
								<span class="help-inline warning" id="msgDate" style="font-weight:bold;" name="msgDate"></span>
							</div>
						</td>
						<td>
							<div align="center">
								<input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo functions::formatMoney($txTran_amount);?>" onkeyup="FormatCurrency(this);"/>
								<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
							</div>
						</td>
						<td><div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary"></div></td>
					</tr>
				</table>
				<table width="50%" align="center" border="0" class="table" style="background-color:#E4E1E1">
					<tr>
						<th><div align="center">Transaction Date</div></th>
						<th><div align="center">Amount</div></th>
						<th>&nbsp;</th>
					</tr>
					<tr>
						<td width="43%"><div align="center">a</div></td>
						<td>
							<div align="center">
								<input type="text" name="txTran_amount" id="txTran_amount" class="span6" value="<?php echo functions::formatMoney($txTran_amount);?>" onkeyup="FormatCurrency(this);"/>
								<span class="help-inline warning" id="msgAmount" style="font-weight:bold;" name="msgAmount"></span>
							</div>
						</td>
						<td><div align="center">a</div></td>
					</tr>
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
$(document).ready(function(){
	var res = false;
	$('#btnSave').click(function(){
		$('#msgDate').html("");
		$('#msgAmount').html("");

		if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
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
<!-- end: JavaScript-->
</body>
</html>