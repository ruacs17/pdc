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

$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? $db->clean(functions::decode($_REQUEST['yr'])) : 0;
$mn = (isset($_REQUEST['mn']) && !empty($_REQUEST['mn']) ) ? $db->clean(functions::decode($_REQUEST['mn'])) : 0;
$supplierID = (isset($_REQUEST['sup']) && !empty($_REQUEST['sup']) ) ? functions::decode($_REQUEST['sup']) : 0;
$dateFrom = (isset($_REQUEST['fmDte']) && !empty($_REQUEST['fmDte']) ) ? $db->clean(functions::decode($_REQUEST['fmDte'])) : 0;
$dateTo = (isset($_REQUEST['toDte']) && !empty($_REQUEST['toDte']) ) ? $db->clean(functions::decode($_REQUEST['toDte'])) : 0;
$qYrMn='';
if($yr && $mn)
	$qYrMn = ' AND LEFT(p.po_date,7)="'.$yr.'-'.$mn.'"';
elseif($yr)
	$qYrMn = ' AND LEFT(p.po_date,4)="'.$yr.'"';

if($dateFrom && $dateTo)
	$qShow = $db->query('SELECT vwpp.proj_id,round(sum(balance),2) as bal FROM view_po_payment vwpp, project p WHERE vwpp.proj_id=p.proj_id AND balance > 0 AND vwpp.supplierID="'.$db->clean($supplierID).'" AND vwpp.po_date BETWEEN "'.$db->clean($dateFrom).'" AND "'.$db->clean($dateTo).'" GROUP BY vwpp.proj_id ORDER BY vwpp.po_date');
else
	$qShow = $db->query('SELECT vwpp.proj_id,round(sum(balance),2) as bal FROM view_po_payment vwpp, project p WHERE vwpp.proj_id=p.proj_id AND balance > 0 AND vwpp.supplierID="'.$db->clean($supplierID).'" GROUP BY vwpp.proj_id ORDER BY vwpp.po_date');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Payable P.O. Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
	<script>//window.print();</script>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table border="0" align="center">
	<thead>
		<tr>
			<td>
			<?php 
			require_once('../class/print_header.php');
			print_header('PAYABLE PURCHASE ORDER');
			?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="60" align="left" valign="middle">
				<div>Supplier: <strong><?php echo $db->getValue('supplier','name',array('supplierID'=>$supplierID));?></strong></div>
				<div>Date: <strong><?php echo functions::datearr($dateFrom)?> - <?php echo functions::datearr($dateTo)?></strong></div>
			</td>
		</tr>
		<tr>
			<td style="font-size: 12px;">
				<?php
				$totalAmount=0;$amount=0;$count=0;$total_payable=0;$total_amount=0;
				while($rShow = $db->fetch_array($qShow)):
					$totalAmount += $amountPo =$rShow['bal'];
				?>
					<br><br><div align="left">Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rShow['proj_id']));?></strong></div>
					<?php
					if($dateFrom && $dateTo)
						$qPO = $db->query('SELECT * FROM view_po_payment p WHERE supplierID="'.$db->clean($supplierID).'" AND proj_id="'.$rShow['proj_id'].'" AND balance > 0 AND p.po_date BETWEEN "'.$db->clean($dateFrom).'" AND "'.$db->clean($dateTo).'"');
					else
						$qPO = $db->query('SELECT * FROM view_po_payment p WHERE supplierID="'.$db->clean($supplierID).'" AND proj_id="'.$rShow['proj_id'].'" AND balance > 0 ');
					while($rPO = $db->fetch_array($qPO)):
					?>
						<div align="left"><br>Date: <strong><?php echo functions::datearr($rPO['po_date'])?></strong></div>
						<table width="100%" border="0" align="center" class="table table-bordered">
							<?php 
							$amount=0;$amountPerDate=0;
							$qPOI = $db->select('po_item','*',array('po_id'=>$rPO['po_id']));
							$po_type = $db->getValue('po','po_type',array('po_id'=>$rPO['po_id']));
							?>
							<?php if($po_type=='service'){?>
							<tr>
								<th width="23%" scope="col"><div align="left">Item</div></th>
								<th width="10%" scope="col"><div align="left">Price</div></th>
								<th width="10%" scope="col"><div align="left">Amount</div></th>
							</tr>
							<?php
							while($rPOI = $db->fetch_array($qPOI)):
								$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
								$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
								$amount = $amount - $disc_amount;
								$total_amount += $amount;
								$amountPerDate +=$amount;
							?>
							<tr>
								<td><?php echo $rPOI['item'];?></td>
								<td><?php echo functions::formatMoney($rPOI['cost']);?></td>
								<td><?php echo functions::formatMoney($amount);?><?php echo ($rPOI['discount']) ? '<br><i style="font-size:11px">('.$rPOI['discount'].'% off)</i>' : '';?></td>
							</tr>
							<?php endwhile; #$rPOI ?>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><strong><?php echo functions::formatMoney($amountPerDate);?></strong></td>
							</tr>
							<?php }//end po type service
							else{
							?>
							<tr>
								<th width="23%" scope="col"><div align="left">Item</div></th>
								<th width="7%" scope="col"><div align="right">Quantity</div></th>
								<th width="5%" scope="col"><div align="left">Unit</div></th>
								<th width="10%" scope="col"><div align="left">Brand</div></th>
								<th width="10%" scope="col"><div align="right">Price</div></th>
								<th width="10%" scope="col"><div align="right">Amount</div></th>
							</tr>
							<?php
							while($rPOI = $db->fetch_array($qPOI)):
								$amount = $rPOI['cost'] * $rPOI['qty_delivered'];
								$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
								$amount = $amount - $disc_amount;
								$total_amount += $amount;
								$amountPerDate +=$amount;
							?>
							<tr>
								<td><?php echo $rPOI['item'];?></td>
								<td><div align="right"><?php echo round($rPOI['qty_delivered'],2);?></div></td>
								<td><?php echo $rPOI['unit'];?></td>
								<td><?php echo $rPOI['brand'];?></td>
								<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
								<td><div align="right"><?php echo functions::formatMoney($amount);?><?php echo ($rPOI['discount']) ? '<br><i style="font-size:11px">('.$rPOI['discount'].'% off)</i>' : '';?></div></td>
							</tr>
							<?php endwhile; #$rPOI ?>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><div align="right"><strong><?php echo functions::formatMoney($amountPerDate);?></strong></div></td>
							</tr>
							<?php }//end if po type not service ?>
						</table>
						<table width="100%">
							<tr>
								<td>
									<?php
									$qPayHist = $db->select('voucher_po_payment','*',array('po_id'=>$rPO['po_id']),'ORDER BY vpp_id');
									if( $db->num_rows($qPayHist)>0 ){
									?>
										<table width="45%" border="1" align="right">
											<tr>
												<td colspan="3" align="left" height="35px">Payment History of the P.O. above</td>
											</tr>
											<tr>
												<th width="30%" scope="col"><div align="left">Voucher No</div></th>
												<th width="32%" scope="col"><div align="left">Date</div></th>
												<th width="30%" scope="col"><div align="left">Payment</div></th>
											</tr>
											<?php 
											while($rPH = $db->fetch_array($qPayHist)):
												$phVid = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rPH['vp_id']));
												$voucherChkDate = $db->getValue('voucher','cheque_date',array('voucher_id'=>$phVid));
											?>
											<tr>
												<td height="35px"><?php echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$phVid));?></td>
												<td><?php echo functions::datearr($voucherChkDate);?></td>
												<td><?php echo functions::formatMoney($rPH['amount']);?></td>
											</tr>
											<?php endwhile; ?>
											<tr>
												<td></td>
												<td align="right" height="35px"><strong>Payable</strong>&nbsp;&nbsp;</td>
												<td><strong><?php echo functions::formatMoney($db->getValue('view_po_payment','balance',array('po_id'=>$rPO['po_id'])));?></strong></td>
											</tr>
										</table><br><br>
									<?php } #if( $db->num_rows($qPayHist)>0 )?>
								</td>
							</tr>
						</table>
					<?php endwhile; #$rPO?>
				<?php endwhile;?>
				<div align="right">Total Amount: <strong><?php echo functions::formatMoney($totalAmount)?></strong></div>
            </td>
        </tr>
    </tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
    window.print();
    setTimeout("closePrint()",200);
});
function closePrint(){
    window.location="dash-po-payable-set-print-view.php?sup=<?php echo functions::encode($supplierID)?>&fmDte=<?php echo functions::encode($dateFrom)?>&toDte=<?php echo functions::encode($dateTo)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>