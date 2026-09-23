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
$arr = array();
$mon='';$txProj = '';$txPayee = ''; 
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
$im_id='';
$txProj = ( isset($_SESSION['in_proj']) ) ? $_SESSION['in_proj'] : '';

$txbYear = ( isset($_SESSION['in_yr']) ) ? $_SESSION['in_yr'] : date('Y');
$txbMon = ( isset($_SESSION['in_mn']) ) ? $_SESSION['in_mn'] : date('m');
$txbDay = ( isset($_SESSION['in_day']) ) ? $_SESSION['in_day'] : date('d');

$txbYearTo = ( isset($_SESSION['in_yrTo']) ) ? $_SESSION['in_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['in_mnTo']) ) ? $_SESSION['in_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['in_dayTo']) ) ? $_SESSION['in_dayTo'] : date('d');

$where = 'WHERE 1'; 

if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (im_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(im_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(im_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(im_date,6,2)>="'.$txbMon.'" AND SUBSTRING(im_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(im_date,4) >= "'.$txbYear.'" AND LEFT(im_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND im_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	if( $db->getValue('inhouse_material','count(*)',array('proj_id'=>$txProj)) )
		$where .=' AND proj_id="'.$db->clean($txProj).'"';
	else
		$where .=' AND payee="'.$db->clean($txProj).'"';
}

if($txProj)
	$qList = $db->select('inhouse_material','*',array(),$where.' ORDER BY im_date DESC');
else
	$qList = $db->select('inhouse_material','*',array('im_id'=>0),' ORDER BY im_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Warehouse Report</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:50px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
	<thead>
		<tr>
			<td>
			<?php 
			require_once('../class/print_header.php');
			print_header('WAREHOUSE REPORT');
			?>
			</td>
		</tr>
		<tr>
			<td valign='bottom'>&nbsp;</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="500" valign="top">
				<div>Project/Payee:
					<strong>
						<?php
						if($txProj){
							$qProj = $db->select('project','*',array('proj_id'=>$txProj));
							while($rProj = $db->fetch_array($qProj)):
								echo strtoupper($rProj['proj_name']);
								echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';
							endwhile;
						}
						else
							echo $db->getValue('inhouse_material','payee',array('im_id'=>$im_id));
						?>
					</strong>
				</div>
				<?php
				$totalAmount=0;
				while($rList = $db->fetch_array($qList)):
					$amount = $db->getValue('inhouse_material_item','sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) )',array('im_id'=>$rList['im_id']));
					$totalAmount += $amount;

					$im_id = $rList['im_id'];
					$project_id = $db->getValue('inhouse_material','proj_id',array('im_id'=>$im_id));
				?>
				<table width="100%" border="0" align="center" class="table" style="font-size: 12px;page-break-inside:avoid">
						<tr>
							<td colspan="7">
								<div align="left">
									<div>Date: <strong><?php echo functions::datearr($db->getValue('inhouse_material','im_date',array('im_id'=>$im_id)));?></strong></div>
									<div>Order No: 
										<strong>
										<?php
										$exp = explode('-',$db->getValue('inhouse_material','im_date',array('im_id'=>$im_id)));
										$m=0;$y=0;
										if(count($exp)==3){
											$m = $exp['1'];
											$y = $exp['0'];
										}
										echo $y.$m.$im_id;
										?>
										</strong>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th width="25%" scope="col"><div align="left">Item</div></th>
							<th width="7%" scope="col"><div align="left">Quantity</div></th>
							<th width="8%" scope="col"><div align="left">Unit</div></th>
							<th width="15%" scope="col"><div align="left">Brand</div></th>
							<th width="8%" scope="col"><div align="right">Price</div></th>
							<th width="5%" scope="col"><div align="center">Discount</div></th>
							<th width="9%" scope="col"><div align="right">Amount</div></th>
						</tr>
						<?php
						$total_amount=0;$disc_amount=0;$amount=0;
						$qPOI = $db->select('inhouse_material_item','*',array('im_id'=>$im_id));
						while($rPOI = $db->fetch_array($qPOI)):
							$amount = $rPOI['cost'] * $rPOI['quantity'];
							$disc_amount = ($rPOI['discount']) ? $amount * ($rPOI['discount'] / 100) : 0;
							$amount = $amount - $disc_amount;
							$total_amount += $amount;
						?>
						<tr>
							<td><?php echo $rPOI['item'];?></td>
							<td><?php echo round($rPOI['quantity'],2);?></td>
							<td><?php echo $rPOI['unit'];?></td>
							<td><?php echo $rPOI['brand'];?></td>
							<td><div align="right"><?php echo functions::formatMoney($rPOI['cost']);?></div></td>
							<td><div align="center"><?php echo $rPOI['discount'];?>%</div></td>
							<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						</tr>
						<?php endwhile;?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td colspan="2"><div align="right">Total Amount</div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($total_amount)?></strong></div></td>
						</tr>
					</table>
				<?php endwhile;?>
				<table width="100%" border="0" align="center" class="table" style="font-size: 12px;page-break-inside:avoid">
					<tr>
						<td width="20%">&nbsp;</td>
						<td width="7%">&nbsp;</td>
						<td width="8%">&nbsp;</td>
						<td width="15%">&nbsp;</td>
						<td width="17%" colspan="2"><div align="right">Overall Amount</div></td>
						<td width="8%"><div align="right"><strong><?php echo functions::formatMoney($totalAmount)?></strong></div></td>
					</tr>
				</table>
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
	window.location="admin-inhouse-material-payee-view.php";
}
</script>
<!-- end: JavaScript-->
</body>
</html>