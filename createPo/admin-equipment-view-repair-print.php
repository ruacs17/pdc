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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;

$txProj = ( isset($_SESSION['er_proj']) ) ? $_SESSION['er_proj'] : '';
$txbYear = ( isset($_SESSION['er_yr']) ) ? $_SESSION['er_yr'] : date('Y');
$txbMon = ( isset($_SESSION['er_mn']) ) ? $_SESSION['er_mn'] : date('m');
$txbDay = ( isset($_SESSION['er_day']) ) ? $_SESSION['er_day'] : date('d');

$txbYearTo = ( isset($_SESSION['er_yrTo']) ) ? $_SESSION['er_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['er_mnTo']) ) ? $_SESSION['er_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['er_dayTo']) ) ? $_SESSION['er_dayTo'] : date('d');

$where = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (er_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(er_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(er_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(er_date,6,2)>="'.$txbMon.'" AND SUBSTRING(er_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(er_date,4) >= "'.$txbYear.'" AND LEFT(er_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND er_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	$where .=' AND proj_id="'.$db->clean($txProj).'"';
}
$qShow = $db->select('equip_repair','*',array('equip_id'=>$equip_id),$where.' ORDER BY er_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Equipment Repair/Maintenance Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/printerfoot.css" rel="stylesheet">
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
	.amnt{padding-right:10px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table width="90%" border="0" align="center">
	<thead>
		<tr>
			<td>
				<?php 
				require_once('../class/print_header.php');
				print_header('PROPERTY REPAIR/MAINTENANCE HISTORY');
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
				<table width="100%" border="0" style="font-size:12px;">
					<tr>
						<td width="50%">
							<div align="left">&nbsp;Property: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div>
						</td>
					</tr>
				</table><br>
				<div class="box-content">
					<table class="tables table-bordered" border="1" style="font-size:12px">
						<thead>
							<tr style="background-color:#CCC !important;-webkit-print-color-adjust: exact;">
								<th width="7%" scope="col"><div align="center">Date</div></th>
								<th width="5%" scope="col"><div align="center">Repair<br>Type</div></th>
								<th width="20%" scope="col"><div align="center">Damage / Spare Parts</div></th>
								<th width="5%" scope="col"><div align="center">Labor<br>Quotation</div></th>
								<th width="5%" scope="col"><div align="center">Parts<br>Quotation</div></th>
								<th width="7%" scope="col"><div align="center">Total<br>Quotation</div></th>
								<th width="7%" scope="col"><div align="center">Reference</div></th>
								<th width="12%" scope="col"><div align="center">Charge To</div></th>
								<th width="5%" scope="col"><div align="center">Performed By</div></th>
								<th width="5%" scope="col"><div align="center">Remarks</div></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$total_labor=0;$total_parts=0;$overall_quotation=0;
							while($rShow = $db->fetch_array($qShow)):
								$discount = ($rShow['discount']) ? $rShow['discount'] / 100 : 0;
								$less = ($rShow['quotation_labor'] + $rShow['quotation_part']) * $discount;
								$totalQuotation = ($rShow['quotation_labor'] + $rShow['quotation_part']) - $less;
								$total_labor += ($rShow['quotation_labor']) ? $rShow['quotation_labor'] : 0;
								$total_parts += ($rShow['quotation_part']) ? $rShow['quotation_part'] : 0;
								$overall_quotation += $totalQuotation;
							?>
							<tr>
								<td><div align="center"><?php echo functions::datearr($rShow['er_date'])?></div></td>
								<td><div align="center"><?php echo $rShow['repair_type']?></div></td>
								<td><?php echo nl2br($rShow['description'])?></td>
								<td><div align="right" class="amnt"><?php echo ($rShow['quotation_labor']) ? functions::formatMoney($rShow['quotation_labor']) : "";?></div></td>
								<td><div align="right" class="amnt"><?php echo ($rShow['quotation_part']) ? functions::formatMoney($rShow['quotation_part']) : "";?></div></td>
								<td><div align="right" class="amnt"><?php echo functions::formatMoney($totalQuotation)?></div></td>
								<td><div align="center"><?php echo $rShow['invoice']?></div></td>
								<td><div align="center" style="font-size:11px;"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rShow['proj_id']))?></div></td>
								<td><?php echo $rShow['incharge']?></td>
								<td><?php echo $rShow['remarks']?></td>
							</tr>
							<?php endwhile; ?>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><div align="right" class="amnt"><strong><?php echo functions::formatMoney($total_labor)?></strong></div></td>
								<td><div align="right" class="amnt"><strong><?php echo functions::formatMoney($total_parts)?></strong></div></td>
								<td><div align="right" class="amnt"><strong><?php echo functions::formatMoney($overall_quotation);?></strong></div></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<footer>
	<div align="right">18PMD.FRM040.00-10/18</div>
</footer>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-equipment-view-repair.php?vdidVw=<?php echo functions::encode($equip_id);?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>