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

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
$txSearchVo='';

$equip_id='';
if( isset($_POST['btnSearchVO']) ){
	$_SESSION['fr_Vo'] = ( isset($_POST['txSearchVo']) && !empty($_POST['txSearchVo']) ) ? $_POST['txSearchVo'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$txSearchVo = ( isset($_SESSION['fr_Vo']) ) ? $_SESSION['fr_Vo'] : '';
$where = '';

if($txSearchVo)
	$where .= ' AND voucher_no='.$db->clean($txSearchVo);

$count=0;
$totalUnpaidAmount=0;
$totalAmount=0;
$totalAmountFuel=0;
$totalAmountOil=0;
$totalFuelLiter=0;
$totalOilLiter=0;
$arrFPO = array();

if($txSearchVo){
	$qPO = $db->query('SELECT * FROM po p, po_fuel pf, voucher v, voucher_particular vp WHERE vp.voucher_id=v.voucher_id AND p.vp_id=vp.vp_id AND p.po_id=pf.po_id AND po_type="fuel" AND p.vp_id IS NOT NULL '.$where.' ORDER BY cheque_date DESC');
	while($rPO = $db->fetch_array($qPO)):
		$count++;
		$payee = $db->getValue('po_fuel','payee',array('po_id'=>$rPO['po_id']));
		$qItem = $db->select('po_item','*',array('po_id'=>$rPO['po_id']));
		while($rItem = $db->fetch_array($qItem)):
			$amountFuel = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%fuel%"');
			$totalAmountFuel += $amountFuel;

			$fuelLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%fuel%"');
			$totalFuelLiter += $fuelLiter;

			$fuelPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%fuel%"');

			$oilLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%oil%"');
			$totalOilLiter += $oilLiter;

			$oilPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%oil%"');

			$amountOil = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']),'AND item LIKE "%oil%"');
			$totalAmountOil += $amountOil;

			$amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']));
			$totalAmount += $amount;

			$amount = ($amount) ? $amount : $db->getValue('po_item','sum( cost - (cost * (discount/100)) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rItem['po_item_id']));
		endwhile;

		$fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

		$oilLiter = (functions::isfloat($oilLiter)) ? functions::formatMoney($oilLiter) : $oilLiter;

		$arrFPO[] = array('po_id'=>$rPO['po_id'],'po_no'=>$rPO['po_no'],'voucher_no'=>$rPO['voucher_no'],'vdate'=>$rPO['vdate'],'cheque_date'=>$rPO['cheque_date'],'po_date'=>$rPO['po_date'],'invoice'=>$rPO['invoice'],'proj_id'=>$rPO['proj_id'],'payee'=>$payee,'fuelLiter'=>$fuelLiter,'fuelPrice'=>$fuelPrice,'oilLiter'=>$oilLiter,'oilPrice'=>$oilPrice,'amount'=>$amount,'equip_id'=>'','tranType'=>'po');
	endwhile;
}


$whereVoucher = ''; 
if($txSearchVo){
	$whereVoucher = ' AND voucher_no='.$db->clean($txSearchVo);
$qFuelVoucher = $db->query('SELECT * FROM equip_fuel ef, voucher_detail vd, voucher v, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vd.vp_id=vp.vp_id AND vd.ef_id=ef.ef_id '.$whereVoucher.' ORDER BY cheque_date DESC');

while( $rFV = $db->fetch_array($qFuelVoucher)):
	$count++;
	$oilPrice=0;
	$fuelPrice=0;
	$amount = $rFV['amount_issue'];
	$totalAmount += $amount;
	if( $db->getValue('equip_fuel','count(*)',array('ef_id'=>$rFV['ef_id']),'AND remarks LIKE "%oil%"') )
		$oilPrice = $amount;
	else
		$fuelPrice = $amount;
	$totalAmountOil += $oilPrice;
	$totalAmountFuel += $fuelPrice;
	$arrFPO[] = array('po_id'=>'','po_no'=>$rFV['voucher_no'],'voucher_no'=>$rFV['voucher_no'],'vdate'=>$rFV['vdate'],'cheque_date'=>$rFV['cheque_date'],'po_date'=>$rFV['vd_date'],'invoice'=>'','proj_id'=>$rFV['proj_id'],'payee'=>'','fuelLiter'=>'','fuelPrice'=>$fuelPrice,'oilLiter'=>'','oilPrice'=>$oilPrice,'amount'=>$amount,'equip_id'=>$rFV['equip_id'],'tranType'=>'voucher');
endwhile;
}
if($txSearchVo){
	$qPart = $db->query('SELECT * FROM voucher v, voucher_particular vp, voucher_detail vd WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_no="'.$db->clean($txSearchVo).'"');
	while($rPart = $db->fetch_array($qPart)):
		$totalAmount += $rPart['amount'];
		$arrFPO[] = array('po_id'=>'','po_no'=>$rPart['voucher_no'],'voucher_no'=>$rPart['voucher_no'],'vdate'=>$rPart['vdate'],'cheque_date'=>$rPart['cheque_date'],'po_date'=>$rPart['vd_date'],'invoice'=>'','proj_id'=>$rPart['proj_id'],'payee'=>'','fuelLiter'=>'','fuelPrice'=>'','oilLiter'=>'','oilPrice'=>'','amount'=>$rPart['amount'],'equip_id'=>'','tranType'=>'voucher');
	endwhile;
}
if(count($arrFPO))
	functions::sortMultiArray($arrFPO,$orderBy='proj_id',$ascDes='DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Equipment Fuel Detail</title>
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
	<script src="../js/inputInt.js"></script>
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
	<style>.tdSpace{padding: 4px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<form method="post">
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white edit"></i><span class="break"></span>FUEL CONSUMPTION REPORT</h2>
			</div><br>
			<div align="right"><a id="mrPrint" href="fuel-paid-report-print.php" class="btn btn-small btn-info"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div>
			<table border="0">
				<tr>
					<td colspan="2" height="70">Voucher No: <input type="text" name="txSearchVo" id="txSearchVo" value="<?php echo $txSearchVo;?>">&nbsp;<input type="submit" name="btnSearchVO" id="btnSearchVO" value="Search" class="btn btn-primary btn-small">&nbsp;</td>
				</tr>
			</table>
			<div class="box-content">
				<table class="table table-bordered" style="font-size:12px">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="7%">Voucher No</th>
							<th width="7%">Cheque Date</th>
							<th width="7%">P.O. No</th>
							<th width="7%">Invoice</th>
							<th width="20%"><div align="center">Equipment</div></th>
							<th width="30%">Charge To</th>
							<th width="7%" style="display: none;"><div align="center">Fuel (Liter) / Price</div></th>
							<th width="7%" style="display: none;"><div align="center">Oil / Price</div></th>
							<th width="7%"><div align="center">Cost</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$chargeTo='';$totalCost=0;$count=0;
						foreach($arrFPO as $rPO):
							$dif=0;
							$chargeTo=($rPO['proj_id']) ? $rPO['proj_id'] : $rPO['payee'];
							if( isset($arrFPO[$count + 1]) ){
								$nextCharge = ($arrFPO[$count +1]['proj_id']) ? $arrFPO[$count +1]['proj_id'] : $arrFPO[$count +1]['payee'];
								if($chargeTo != $nextCharge )
									$dif=1;
							}
							else
								$dif=1;
							$count++;
						?>
						<tr>
							<td><?php echo $rPO['voucher_no'];?></td>
							<td><?php echo functions::datearr($rPO['cheque_date']);?></td>
							<td><?php echo ($rPO['tranType']=='po') ? $rPO['po_no'] : '<i>(non-po)</i>';?></td>
							<td><?php echo $rPO['invoice'];?></td>
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

									$arrUsedEquip=array();
									while($rEqp = $db->fetch_array($qEqp)):
										$eqpName = $db->getValue('equipment','name',array('equip_id'=>$rEqp['equip_id']));
										$eqpName = ($eqpName) ? $eqpName :  $rEqp['other_equip'];
										$arrUsedEquip = functions::insert_array($arrUsedEquip,$eqpName);
									endwhile;
									sort($arrUsedEquip);
									$allEqp = count($arrUsedEquip);
									$cntEqp=1;
									foreach($arrUsedEquip as $eqp_name):
										$cntEqpDisp = ($allEqp > 1) ? '<strong>'.$cntEqp++.')</strong>' : '&nbsp;';
										echo '<tr>';
											echo '<td style="border: none;padding: 0px">'.$cntEqpDisp.'</td>';
											echo '<td style="border: none;padding: 0px">'.$eqp_name.'</td>';
										echo '</tr>';
									endforeach;
									if(($rPO['tranType']=='voucher')){
										echo '<tr>';
											echo '<td style="border: none;padding: 0px">&nbsp;</td>';
											echo '<td style="border: none;padding: 0px">'.$db->getValue('equipment','name',array('equip_id'=>$rPO['equip_id'])).'</td>';
										echo '</tr>';
									}
								?>
								</table>
							</td>
							<td><?php echo ($rPO['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id'])) : $rPO['payee'].' <i>(outsider)</i>';?></td>
							<td style="display: none;">
								<div align="center">
									<?php 
									if($rPO['tranType']=='po')
										echo ($rPO['fuelLiter']) ? $rPO['fuelLiter'].' @ '.functions::formatMoney($rPO['fuelPrice']) : '';
									else
										echo ($rPO['fuelPrice']) ? functions::formatMoney($rPO['fuelPrice']) : '';
									?>
								</div>
							</td>
							<td style="display: none;">
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
						<?php
						$totalCost+=$rPO['amount'];
						if($dif){?>
						<tr bgColor="#fee392">
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td style="display: none;"><div align="center"><strong><?php echo (functions::isfloat($totalFuelLiter)) ? functions::formatMoney($totalFuelLiter) : $totalFuelLiter; echo ' | '.functions::formatMoney($totalAmountFuel)?></strong></div></td>
							<td style="display: none;"><div align="center"><strong><?php echo (functions::isfloat($totalOilLiter)) ? functions::formatMoney($totalOilLiter) : $totalOilLiter; echo ' | '.functions::formatMoney($totalAmountOil)?></strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($totalCost);?></strong></div></td>
						</tr>
						<tr><td colspan="9">&nbsp;</td></tr>
						<?php $totalCost=0;
						}
						endforeach;?>
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
							<td>&nbsp;</td>
							<td><div align="right"><strong>Total</strong></div></td>
							<td style="display: none;"><div align="center"><strong><?php echo (functions::isfloat($totalFuelLiter)) ? functions::formatMoney($totalFuelLiter) : $totalFuelLiter; echo ' | '.functions::formatMoney($totalAmountFuel)?></strong></div></td>
							<td style="display: none;"><div align="center"><strong><?php echo (functions::isfloat($totalOilLiter)) ? functions::formatMoney($totalOilLiter) : $totalOilLiter; echo ' | '.functions::formatMoney($totalAmountOil)?></strong></div></td>
							<td><div align="right"><strong><?php echo (functions::isfloat($totalAmount)) ? functions::formatMoney($totalAmount) : $totalAmount;?></strong></div></td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			</div>
		</div><!--/span-->
	</div><!--/row-->
</form>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>