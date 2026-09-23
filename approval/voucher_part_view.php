<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$vp_id=(isset($_REQUEST['vpid']) && !empty($_REQUEST['vpid']) ) ? functions::decode($_REQUEST['vpid']) : 0;
$particular_name=$db->getValue('voucher_particular','vp_title',array('voucher_id'=>$vid,'vp_id'=>$vp_id));
$category="";   $item="";   $proj_id="";    $amount=""; $txDate="";

$editTrue=0;
$vdidEdt=0;
$txDate = date('m/d/Y');
if( isset($_REQUEST['vdidEdt']) && !empty($_REQUEST['vdidEdt']) ){
	$vdidEdt = functions::decode($_REQUEST['vdidEdt']);
	$editTrue = $db->getValue('voucher_detail','count(*)',array('vd_id'=>$vdidEdt));
	$qvedt = $db->select('voucher_detail','*',array('vd_id'=>$vdidEdt));
	$rvedt = $db->fetch_array($qvedt);
	$category = $rvedt['category'];
	$item = $rvedt['item'];
	$proj_id = $rvedt['proj_id'];
	$amount = $rvedt['amount'];
	$txDateEdt = explode("-",$rvedt['vd_date']);
	if(count($txDate)==3)
		$txDate = $txDateEdt[1].'/'.$txDateEdt[2].'/'.$txDateEdt[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher Particular</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="60%" align="center" border="0">
					<tr>
						<td>
							<div align='center'>Particular: <strong><?php echo $particular_name?></strong></div><br><br>
						</td>
					</tr>
				</table>
				<?php if($vp_id){?>
				<input type="hidden" name="txvd_id" id="txvd_id" value="<?php echo functions::encode($vdidEdt);?>">
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="9%" scope="col"><div align="left">Date</div></th>
							<th width="20%" scope="col"><div align="left">Category</div></th>
							<th width="19%" scope="col"><div align="left">Item Detail</div></th>
							<th width="35%" scope="col"><div align="left">Project</div></th>
							<th width="9%" scope="col"><div align="right">Amount Issued</div></th>
							<th width="9%" scope="col"><div align="right">Amount Spent</div></th>
						</tr>
					</thead>
					<tbody>
						<tr><td colspan="6" height="25"></td></tr>
						<?php 
						$vdate='';$total_amount=0;$total_amount_issued=0;
						$qvDetails = $db->select('voucher_detail','*',array('vp_id'=>$vp_id),'ORDER BY vd_date');
						while($rvDetails = $db->fetch_array($qvDetails)):
							$total_amount += $rvDetails['amount'];
							$total_amount_issued += $rvDetails['amount_issue'];
						?>
						<tr>
							<td>
								<?php
								if($vdate != $rvDetails['vd_date']){
									$vdate = $rvDetails['vd_date'];
									echo functions::datearr($rvDetails['vd_date']);
								}
								?>
							</td>
							<td><?php echo $db->getValue('item_deduction','name',array('item_id'=>$rvDetails['category_id']));?></td>
							<td><?php echo $rvDetails['item']?></td>
							<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rvDetails['proj_id']));?></td>
							<td><div align="right"><?php echo functions::formatMoney($rvDetails['amount_issue']);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rvDetails['amount']);?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td><div align="right"><strong>Total Amount</strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($total_amount_issued)?></strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
						</tr>
					</tbody>
				</table>
				<?php }#if $vp_id?><p>&nbsp;</p>
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