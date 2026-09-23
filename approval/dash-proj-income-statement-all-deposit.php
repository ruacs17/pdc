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
$mnth = ( isset($_REQUEST['mnth']) && !empty($_REQUEST['mnth']) ) ? functions::decode($_REQUEST['mnth']) : '';
$transaction_type = ( isset($_REQUEST['tranType']) && !empty($_REQUEST['tranType']) ) ? functions::decode($_REQUEST['tranType']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>STOCKSHARE</title>
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
			<h2><i class="halflings-icon white list"></i><span class="break"></span>TRANSACTION DETAIL</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="50%" align="center" border="0" class="table table-bordered table-striped table-hover" style="font-size:12px;" >
					<thead>
						<tr style="background-color:#E4E1E1">
							<th width="10%"><div align="center">Date</div></th>
							<th width="55%"><div align="center">Transaction</div></th>
							<th width="10%"><div align="center">Amount</div></th>
							<th width="25%"><div align="center">Account</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$totalDeposit=0;
						if($transaction_type=='deposit')
							$qShow = $db->select('account_statement','*',array('left(as_date,7)'=>$mnth,'transaction_type'=>$transaction_type,'as_type'=>'credit'),'ORDER BY as_date');
						else
							$qShow = $db->select('account_statement','*',array('left(as_date,7)'=>$mnth,'transaction_type'=>$transaction_type,'confirmned'=>2),'ORDER BY as_date');

						while($rShow = $db->fetch_array($qShow)):
						$totalDeposit+=$rShow['as_amount'];
						?>
						<tr>
							<td><div align="center"><?php echo functions::datearr($rShow['as_date'])?></div></td>
							<td>
								<div align="left">
								<?php
								echo $rShow['transaction'];
								if( $transaction_type=='stock share' ){
									$euid = $db->getValue('stockshare','emp_id',array('as_id'=>$rShow['as_id']));
									echo '&nbsp;&nbsp;&nbsp;<i>('.$db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$euid)).')</i>';
								}
								elseif( $rShow['description'] ){
									echo '&nbsp;&nbsp;&nbsp;<i>('.$rShow['description'].')</i>';
								}
								?>
								</div>
							</td>
							<td><div align="right"><?php echo functions::formatMoney($rShow['as_amount'])?></div></td>
							<td><div align="left" style="padding-left:15%;"><?php echo $db->getValue('voucher_type','vt_name',array('vt_id'=>$rShow['account_type']))?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td colspan="4">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div align="right"><strong><?php echo functions::formatMoney($totalDeposit)?></strong></div></td>
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
<!-- end: JavaScript-->
</body>
</html>