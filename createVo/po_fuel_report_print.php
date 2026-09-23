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

$startrow=0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array();
$day='';
$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = '';
$txbMonTo = '';$txbYearTo = '';
$equip_id='';
$txSearchPO='';
$totalAmount=0;
$unpaid=0;

$where = ''; 
if($txSearchPO){
	$where .= ' AND po_no LIKE "%'.$db->clean($txSearchPO).'%"';
}
else{
	$charge = ( isset($_SESSION['pfCharge']) && !empty($_SESSION['pfCharge']) ) ? $_SESSION['pfCharge'] : '';
	$equip_id = ( isset($_SESSION['pfEquip']) && !empty($_SESSION['pfEquip']) ) ? $_SESSION['pfEquip'] : '';
	$supplier = ( isset($_SESSION['pfSuplr']) && !empty($_SESSION['pfSuplr']) ) ? $_SESSION['pfSuplr'] : '';
	$txbMon = ( isset($_SESSION['pfMon']) ) ? $_SESSION['pfMon'] : date('m');
	$txbYear = ( isset($_SESSION['pfYear']) ) ? $_SESSION['pfYear'] : date('Y');
	$txbDay = ( isset($_SESSION['pfDay']) ) ? $_SESSION['pfDay'] : date('d');
	$txbMonTo = ( isset($_SESSION['pfMonTo']) ) ? $_SESSION['pfMonTo'] : date('m');
	$txbYearTo = ( isset($_SESSION['pfYearTo']) ) ? $_SESSION['pfYearTo'] : date('Y');
	$txbDayTo = ( isset($_SESSION['pfDayTo']) ) ? $_SESSION['pfDayTo'] : date('d');  
	$arr = array('po_type'=>'fuel');

	if($charge){
		if( $db->getValue('project','count(proj_id)',array('proj_id'=>$charge)) )
			$where .= ' AND proj_id="'.$charge.'"';
		else
			$where .= ' AND pf.payee="'.$charge.'"';
	}
	if($equip_id){
		if( $db->getValue('equipment','count(equip_id)',array('equip_id'=>$equip_id)) )
			$where .= ' AND pfe.equip_id="'.$equip_id.'"';
		else
			$where .=' AND pfe.other_equip="'.$equip_id.'"';
	}

	if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
		$where .=' AND (po_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
	else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
		$where .=' AND ( LEFT(po_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(po_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
	else if($txbMon && $txbMonTo)
		$where .=' AND (SUBSTRING(po_date,6,2)>="'.$txbMon.'" AND SUBSTRING(po_date,6,2) <= "'.$txbMonTo.'")';
	else if($txbYear && $txbYearTo)
		$where .=' AND (LEFT(po_date,4) >= "'.$txbYear.'" AND LEFT(po_date,4) <= "'.$txbYearTo.'")';
	else if($txbMon && $txbYear && $txbDay)
		$where .=' AND po_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';
	if($supplier)
		$where .=' AND supplierID="'.$db->clean($supplier).'"';
}

if($equip_id){
	$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_fuel_equip pfe WHERE p.po_id=pf.po_id AND p.po_id=pfe.po_id AND po_type="fuel" '.$where.' ORDER BY po_date DESC');
	if($unpaid)
		$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_fuel_equip pfe WHERE p.po_id=pf.po_id AND p.po_id=pfe.po_id AND po_type="fuel" AND vp_id is NULL '.$where.' ORDER BY po_date DESC');
}
else{
	$qPO = $db->query('SELECT * FROM po p, po_fuel pf WHERE p.po_id=pf.po_id AND po_type="fuel" '.$where.' ORDER BY po_date DESC');
	if($unpaid)
		$qPO = $db->query('SELECT * FROM po p, po_fuel pf WHERE p.po_id=pf.po_id AND po_type="fuel" AND vp_id is NULL '.$where.' ORDER BY po_date DESC');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>PO Fuel Report Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">.padParLeft{padding-left:10px;}.padAmLeft{padding-left:60px;}</style>
</head>
<body bgcolor="#FFFFFF">
<div id="spinner"></div>
<!-- body content: start here-->
<table border="0" align="center">
	<thead>
		<tr>
			<td>
			<?php
			require_once('../class/print_header.php');
			print_header('Fuel P.O Report');
			?><br>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<table class="table table-bordered table-hover" style="font-size:12px">
					<thead>
						<tr>
							<th width="9%">P.O. No</th>
							<th width="9%">P.O. Date</th>
							<th width="15%">Equipment</th>
							<th width="21%">Charge To</th>
							<th width="5%"><div align="center">Fuel (Liter)</div></th>
							<th width="5%"><div align="center">Price</div></th>
							<th width="5%"><div align="center">Oil</div></th>
							<th width="5%"><div align="center">Price</div></th>
							<th width="10%">Cost</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$count=0;$totalUnpaidAmount=0;$totalAmount=0;$totalAmountFuel=0;$totalAmountOil=0;$totalFuelLiter=0;$totalOilLiter=0;
					while($rPO = $db->fetch_array($qPO)):
						$count++;
						$payee = $db->getValue('po_fuel','payee',array('po_id'=>$rPO['po_id']));

						$fuelLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id']),'AND item LIKE "%fuel%"');
						$totalFuelLiter += $fuelLiter;
						$fuelPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id']),'AND item LIKE "%fuel%"');
						$totalAmountFuel += $fuelPrice * $fuelLiter;
						$fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

						$oilLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id']),'AND item LIKE "%oil%"');
						$totalOilLiter += $oilLiter;
						$oilPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id']),'AND item LIKE "%oil%"');
						$totalAmountOil += $oilLiter * $oilPrice;
						$oilLiter = (functions::isfloat($oilLiter)) ? functions::formatMoney($oilLiter) : $oilLiter;


						$amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']));
						$totalAmount += $amount;
						$amount = ($amount) ? $amount : $db->getValue('po_item','sum( cost - (cost * (discount/100)) )',array('po_id'=>$rPO['po_id']));
					?>
						<tr>
							<td><?php echo $rPO['po_no'];?></td>
							<td><?php echo functions::datearr($rPO['po_date']);?></td>
							<td>
								<table>
								<?php 
								if($equip_id){
									if( $db->getValue('equipment','count(equip_id)',array('equip_id'=>$equip_id)) )
										$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'equip_id'=>$equip_id));
									else
										$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id'],'other_equip'=>$equip_id));
								}
								else
									$qEqp = $db->select('po_fuel_equipment','*',array('po_id'=>$rPO['po_id']));
								$allEqp = $db->num_rows($qEqp);
								$cntEqp=1;
								while($rEqp = $db->fetch_array($qEqp)):
									$cntEqpDisp = ($allEqp > 1) ? '<strong>'.$cntEqp++.')</strong>' : '&nbsp;';
									echo '<tr>';
									if( $eqpName = $db->getValue('equipment','name',array('equip_id'=>$rEqp['equip_id'])) ){
										echo '<td style="border: none;">'.$cntEqpDisp.'</td>';
										echo '<td style="border: none;">'.$eqpName.'</td>';
									}
									else{
										echo '<td style="border: none;">'.$cntEqpDisp.'</td>';
										echo '<td style="border: none;">'.$rEqp['other_equip'].' <i>(outsider)</i></td>';
									}
									echo '</tr>';
								endwhile;
								?>
								</table>
							</td>
							<td><?php echo ($rPO['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id'])) : $payee.' <i>(outsider)</i>';?></td>
							<td><div align="center"><?php echo $fuelLiter;?></div></td>
							<td><div align="center"><?php echo ($fuelPrice) ? functions::formatMoney($fuelPrice) : '';?></div></td>
							<td><div align="center"><?php echo $oilLiter;?></div></td>
							<td><div align="center"><?php echo ($oilPrice) ? functions::formatMoney($oilPrice) : '';?></div></td>
							<td><?php echo ($amount) ? functions::formatMoney($amount) : '--';?></td>
						</tr>
					<?php endwhile; ?>
					<?php
					if($count==0){
						echo '<tr><td colspan="9"><div align="center"><strong>-- No result --</strong></div></td></tr>';
					}
					else{
					?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><div align="center"><strong><?php echo (functions::isfloat($totalFuelLiter)) ? functions::formatMoney($totalFuelLiter) : number_format($totalFuelLiter);?></strong></div></td>
							<td><div align="center"><strong><?php echo functions::formatMoney($totalAmountFuel);?></strong></div></td>
							<td><div align="center"><strong><?php echo (functions::isfloat($totalOilLiter)) ? functions::formatMoney($totalOilLiter) : $totalOilLiter;?></strong></div></td>
							<td><div align="center"><strong><?php echo functions::formatMoney($totalAmountOil);?></strong></div></td>
							<td><strong><?php echo functions::formatMoney($totalAmount);?></strong></td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="po_fuel_report.php";
}
</script>
<!-- end: JavaScript-->
</body>
</html>