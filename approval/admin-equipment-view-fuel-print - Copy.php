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

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';

$txProj = ( isset($_SESSION['efo_proj']) ) ? $_SESSION['efo_proj'] : '';
$txbYear = ( isset($_SESSION['efo_yr']) ) ? $_SESSION['efo_yr'] : date('Y');
$txbMon = ( isset($_SESSION['efo_mn']) ) ? $_SESSION['efo_mn'] : date('m');
$txbDay = ( isset($_SESSION['efo_day']) ) ? $_SESSION['efo_day'] : date('d');

$txbYearTo = ( isset($_SESSION['efo_yrTo']) ) ? $_SESSION['efo_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['efo_mnTo']) ) ? $_SESSION['efo_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['efo_dayTo']) ) ? $_SESSION['efo_dayTo'] : date('d');
$count=0;
$totalUnpaidAmount=0;
$totalAmount=0;
$totalAmountFuel=0;
$totalAmountOil=0;
$totalFuelLiter=0;
$totalOilLiter=0;
$arrFPO = array();

$where = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (po_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(po_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(po_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(po_date,6,2)>="'.$txbMon.'" AND SUBSTRING(po_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(po_date,4) >= "'.$txbYear.'" AND LEFT(po_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND po_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	if( $db->getValue('po','count(*)',array('proj_id'=>$txProj)) )
		$where .=' AND p.proj_id="'.$db->clean($txProj).'"';
	else
		$where .=' AND pf.payee="'.$db->clean($txProj).'"';
}

$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_item poi, po_fuel_equipment pfe WHERE p.po_id=poi.po_id AND p.po_id=pf.po_id AND p.po_id=pfe.po_id AND pfe.po_item_id=poi.po_item_id AND po_type="fuel" AND pfe.equip_id="'.$db->clean($equip_id).'" '.$where.' ORDER BY po_date DESC');
while($rPO = $db->fetch_array($qPO)):
	$count++;
	$payee = $db->getValue('po_fuel','payee',array('po_id'=>$rPO['po_id']));
	$amountFuel = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%fuel%"');
	$totalAmountFuel += $amountFuel;

	$fuelLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%fuel%"');
	$totalFuelLiter += $fuelLiter;
	$fuelPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%fuel%"');
	$fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

	$oilLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%oil%"');
	$totalOilLiter += $oilLiter;
	$oilLiter = (functions::isfloat($oilLiter)) ? functions::formatMoney($oilLiter) : $oilLiter;
	$oilPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%oil%"');

	$amountOil = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%oil%"');
	$totalAmountOil += $amountOil;

	$amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']));
	$totalAmount += $amount;
	$amount = ($amount) ? $amount : $db->getValue('po_item','sum( cost - (cost * (discount/100)) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']));
	$arrFPO[] = array('po_no'=>$rPO['po_no'],'po_date'=>$rPO['po_date'],'invoice'=>$rPO['invoice'],'proj_id'=>$rPO['proj_id'],'payee'=>$payee,'fuelLiter'=>$fuelLiter,'fuelPrice'=>$fuelPrice,'oilLiter'=>$oilLiter,'oilPrice'=>$oilPrice,'amount'=>$amount,'tranType'=>'po');
endwhile;

$whereVoucher = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$whereVoucher .=' AND (vd_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$whereVoucher .=' AND ( LEFT(vd_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(vd_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$whereVoucher .=' AND (SUBSTRING(vd_date,6,2)>="'.$txbMon.'" AND SUBSTRING(vd_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$whereVoucher .=' AND (LEFT(vd_date,4) >= "'.$txbYear.'" AND LEFT(vd_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$whereVoucher .=' AND vd_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj)
	$whereVoucher .=' AND proj_id="'.$db->clean($txProj).'"';

$qFuelVoucher = $db->query('SELECT * FROM equip_fuel ef, voucher_detail vd WHERE vd.ef_id=ef.ef_id AND ef.equip_id="'.$db->clean($equip_id).'" '.$whereVoucher.' ORDER BY vd_date DESC');
while( $rFV = $db->fetch_array($qFuelVoucher)):
	$count++;
	$oilPrice=0;
	$fuelPrice=0;
	$voucher_id = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rFV['vp_id']));
	$voucher_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$voucher_id));
	$amount = $rFV['amount_issue'];
	$totalAmount += $amount;
	if( $db->getValue('equip_fuel','count(*)',array('ef_id'=>$rFV['ef_id']),'AND remarks LIKE "%oil%"') )
		$oilPrice = $amount;
	else
		$fuelPrice = $amount;
	$totalAmountOil += $oilPrice;
	$totalAmountFuel += $fuelPrice;
	$arrFPO[] = array('po_no'=>$voucher_no,'po_date'=>$rFV['vd_date'],'invoice'=>'','proj_id'=>$rFV['proj_id'],'payee'=>'','fuelLiter'=>'','fuelPrice'=>$fuelPrice,'oilLiter'=>'','oilPrice'=>$oilPrice,'amount'=>$amount,'tranType'=>'voucher');
endwhile;


#inhouse
$whereInhouse = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$whereInhouse .=' AND (im_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$whereInhouse .=' AND ( LEFT(im_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(im_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$whereInhouse .=' AND (SUBSTRING(im_date,6,2)>="'.$txbMon.'" AND SUBSTRING(im_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$whereInhouse .=' AND (LEFT(im_date,4) >= "'.$txbYear.'" AND LEFT(im_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$whereInhouse .=' AND im_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj)
	$whereInhouse .=' AND proj_id="'.$db->clean($txProj).'"';

$qFuelInhouse = $db->query('SELECT * FROM inhouse_material WHERE im_id IN (SELECT DISTINCT im_id FROM  inhouse_material_fuel_equipment WHERE equip_id="'.$db->clean($equip_id).'") '.$whereInhouse);
while( $rFI = $db->fetch_array($qFuelInhouse)):
	$count++;

	$fuelPrice=0;
	$fuelPriceArr=array();
	$amountFuel=0;
	$fuelLiter=0;

	$oilLiter=0;
	$amountOil=0;
	$oilPrice=0;
	$oilPriceArr=array();

	$dt = $rFI['im_date'];
	$expDate = explode('-', $dt);
	$no = (isset($expDate[0]) && isset($expDate[1]) ) ? $expDate[0].$expDate[1].$rFI['im_id'] : '';
	$payee = $rFI['payee'];

	$qFIFuel = $db->select('inhouse_material_item imi, inhouse_material_fuel_equipment imfe','*',array('imi.im_id'=>$rFI['im_id'],'equip_id'=>$equip_id),'AND imi.imi_id=imfe.imi_id AND item LIKE "%fuel%"');
	while($rFIFuel = $db->fetch_array($qFIFuel)):
		$amountFuel += ($rFIFuel['quantity'] * $rFIFuel['cost']) - ( ($rFIFuel['quantity'] * $rFIFuel['cost']) * ($rFIFuel['discount']/100) );
		$fuelLiter += $rFIFuel['quantity'];
		$fuelPriceArr[] = $rFIFuel['cost'];
	endwhile;

	$qFIOil = $db->select('inhouse_material_item imi, inhouse_material_fuel_equipment imfe','*',array('imi.im_id'=>$rFI['im_id'],'equip_id'=>$equip_id),'AND imi.imi_id=imfe.imi_id AND item LIKE "%oil%"');
	while($rFIOil = $db->fetch_array($qFIOil)):
		$amountOil += ($rFIOil['quantity'] * $rFIOil['cost']) - ( ($rFIOil['quantity'] * $rFIOil['cost']) * ($rFIOil['discount']/100) );
		$oilLiter += $rFIOil['quantity'];
		$oilPriceArr[] = $rFIOil['cost'];
	endwhile;
	$totalAmountFuel += $amountFuel;

	$totalFuelLiter += $fuelLiter;
	$fuelPrice = functions::average($fuelPriceArr);
	$fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

	$totalOilLiter += $oilLiter;
	$oilLiter = (functions::isfloat($oilLiter)) ? functions::formatMoney($oilLiter) : $oilLiter;
	$oilPrice = $db->getValue('inhouse_material_item imi, inhouse_material_fuel_equipment imfe','avg(cost)',array('imi.im_id'=>$rFI['im_id'],'equip_id'=>$equip_id),'AND imi.imi_id=imfe.imi_id AND item LIKE "%oil%"');

	$totalAmountOil += $amountOil;

	$amount = $amountFuel + $amountOil;
	$totalAmount += $amount;

	$arrFPO[] = array('po_no'=>$no,'po_date'=>$rFI['im_date'],'invoice'=>$no,'proj_id'=>$rFI['proj_id'],'payee'=>$payee,'fuelLiter'=>$fuelLiter,'fuelPrice'=>$fuelPrice,'oilLiter'=>$oilLiter,'oilPrice'=>$oilPrice,'amount'=>$amount,'tranType'=>'inhouse');
endwhile;

if(count($arrFPO))
	functions::sortMultiArray($arrFPO,$orderBy='po_date',$ascDes='DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Fuel and Oil Consumption Print</title>
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
			print_header('FUEL & OIL CONSUMPTION');
			?>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td height="500" valign="top">
				<table width="100%" border="0">
					<tr>
						<td width="50%">
							<div align="left">
							&nbsp;Property: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong>
							</div>
						</td>
					</tr>
				</table><br>
				<div class="box-content">
					<table class="table table-bordered" style="font-size:12px">
						<thead>
							<tr>
								<th width="9%">P.O. No</th>
								<th width="9%">P.O. Date</th>
								<th width="9%">Invoice</th>
								<th width="35%">Charge To</th>
								<th width="10%"><div align="center">Fuel (Liter) / Price</div></th>
								<th width="10%"><div align="center">Oil / Price</div></th>
								<th width="7%"><div align="center">Cost</div></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach($arrFPO as $rPO):?>
							<tr>
								<td><?php echo ($rPO['tranType']=='po') ? $rPO['po_no'] : $rPO['po_no'].' <i>(voucher #)</i>';?></td>
								<td><?php echo functions::datearr($rPO['po_date']);?></td>
								<td><?php echo $rPO['invoice'];?></td>
								<td><?php echo ($rPO['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id'])) : $rPO['payee'].' <i>(outsider)</i>';?></td>
								<td>
									<div align="center">
									<?php 
									if($rPO['tranType']=='po')
									echo ($rPO['fuelLiter']) ? $rPO['fuelLiter'].' @ '.functions::formatMoney($rPO['fuelPrice']) : '';
									else
									echo ($rPO['fuelPrice']) ? functions::formatMoney($rPO['fuelPrice']) : '';
									?>
									</div>
								</td>
								<td>
									<div align="center">
									<?php
									if($rPO['tranType']=='po')
									echo ($rPO['oilLiter']) ? $rPO['oilLiter'].' @ '.functions::formatMoney($rPO['oilPrice']) : '';
									else
									echo ($rPO['oilPrice']) ? functions::formatMoney($rPO['oilPrice']) : '';
									?>
									</div>
								</td>
								<td><div align="right"><?php echo ($rPO['amount']) ? functions::formatMoney($rPO['amount']) : '--';?></div></td>
							</tr>
							<?php endforeach; ?>
							<?php
							if($count==0){
								echo '<tr><td colspan="7"><div align="center"><strong>-- No result --</strong></div></td></tr>';
							}
							else{
							?>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><div align="center"><strong><?php echo (functions::isfloat($totalFuelLiter)) ? functions::formatMoney($totalFuelLiter) : $totalFuelLiter; echo ' | '.functions::formatMoney($totalAmountFuel)?></strong></div></td>
								<td><div align="center"><strong><?php echo (functions::isfloat($totalOilLiter)) ? functions::formatMoney($totalOilLiter) : $totalOilLiter; echo ' | '.functions::formatMoney($totalAmountOil)?></strong></div></td>
								<td><div align="right"><strong><?php echo (functions::isfloat($totalAmount)) ? functions::formatMoney($totalAmount) : $totalAmount;?></strong></div></td>
							</tr>
							<?php }?>
						</tbody>
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<footer>
    <div align="right">18PMD.FRM039.00-10/18</div>
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
	window.location="admin-equipment-view-fuel.php?vdidVw=<?php echo functions::encode($equip_id);?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>