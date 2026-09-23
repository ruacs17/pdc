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
$loan_proceed_id='';
$readonly = ( isset($_REQUEST['readonly']) && !empty($_REQUEST['readonly']) ) ? 'disabled' : '';
$as_id = ( isset($_REQUEST['asid']) && !empty($_REQUEST['asid']) ) ? functions::decode($_REQUEST['asid']) : '';
$as_amount = $db->getValue('account_statement','as_amount',array('as_id'=>$as_id));
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Loan Proceeds Amortization</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="80%" align="center" class="table table-bordered" border="0" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30">&nbsp;</td>
						<td width="43%">
							<select name="selLoan" id="selLoan" data-rel="chosen" style="width:700px;" onChange="getLoan(this.value)" <?php echo $readonly; ?>>
								<option value="">--Select Loan Proceeds--</option>
								<?php
								$qLoan = $db->select('account_statement','*',array('transaction_type'=>'loan proceeds'),'ORDER BY as_date DESC');
								while($rLn = $db->fetch_array($qLoan)):
								?>
								<option value="<?php echo functions::encode($rLn['as_id'])?>" <?php if($as_id==$rLn['as_id']){echo 'selected="selected"';} ?>><?php echo functions::datearr($rLn['as_date']).': '.$rLn['transaction'].' ('.functions::formatMoney($rLn['as_amount']).')'; ?></option>
								<?php endwhile; ?>
							</select>
						</td>
					</tr>
				</table>
				<div align="center">
					<div style="width:60%;">
						<table class="table table-bordered table-striped table-hover" style="font-size:12px;">
							<thead>
								<tr style="background-color:#CCC;">
									<th>Date</th>
									<th>Voucher No</th>
									<th><div align="right">Amount</div></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$total_amount=0;
								$qL = $db->select('account_loan al, voucher_detail vd','*',array('as_id'=>$as_id),'AND vd.vd_id=al.vd_id ORDER BY vd_date');
								while($rL = $db->fetch_array($qL)):
									$voucher_no = $db->getValue('voucher_particular vp, voucher v','voucher_no',array('vp_id'=>$rL['vp_id']),'AND vp.voucher_id=v.voucher_id');
									$total_amount += $rL['amount_issue'];
								?>
								<tr>
									<td><div align="left"><?php echo functions::datearr($rL['vd_date']) ?></div></td>
									<td><div align="left"><?php echo $voucher_no; ?></div></td>
									<td><div align="right"><?php echo functions::formatMoney($rL['amount_issue']) ?></div></td>
								</tr>
								<?php endwhile; ?>
								<tr>
									<td></td>
									<td><div align="right"><strong>Total Amount</strong></div></td>
									<td><div align="right"><strong><?php echo functions::formatMoney($total_amount) ?></strong></div></td>
								</tr>
								<tr>
									<td></td>
									<td><div align="right"><strong>Balance</strong></div></td>
									<td><div align="right"><strong><?php echo functions::formatMoney($as_amount-$total_amount) ?></strong></div></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
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
});
function getLoan(lid){
	window.location="<?php echo $_SERVER['PHP_SELF']?>?asid="+lid
}
</script>
<!-- end: JavaScript-->
</body>
</html>