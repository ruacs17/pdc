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
unset($_SESSION['po_arr_proj'],$_SESSION['RefID']);
if( isset($_POST['btnSearchPO']) ){
	unset($_SESSION['pfCharge'],$_SESSION['pfEquip'],$_SESSION['pfMon'],$_SESSION['pfYear']);
	$txSearchPO = ( isset($_POST['txSearchPO']) && !empty($_POST['txSearchPO']) ) ? $_POST['txSearchPO'] : '';
}

if( isset($_POST['btnViewAll']) ){
	unset($_SESSION['pfCharge'],$_SESSION['pfEquip'],$_SESSION['pfMon'],$_SESSION['pfYear'],$_SESSION['pfDay']);
}
if( isset($_POST['btnSearch']) ){
	$_SESSION['pfCharge'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $db->clean($_POST['selProj']) : '';
	$_SESSION['pfSuplr'] = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? $db->clean(functions::decode($_POST['selSupplier'])) : '';
	$_SESSION['pfEquip'] = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? $db->clean(functions::decode($_POST['selEquip'])) : '';
	$_SESSION['pfMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $db->clean($_POST['bdMon']) : '';
	$_SESSION['pfYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $db->clean($_POST['bdYear']) : '';
	$_SESSION['pfDay'] = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $db->clean($_POST['bdDay']) : '';
	$_SESSION['pfMonTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $db->clean($_POST['bdMonTo']) : '';
	$_SESSION['pfYearTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $db->clean($_POST['bdYearTo']) : '';
	$_SESSION['pfDayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $db->clean($_POST['bdDayTo']) : '';
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnUnpaid']) ){
	$_SESSION['pfCharge'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $db->clean($_POST['selProj']) : '';
	$_SESSION['pfSuplr'] = ( isset($_POST['selSupplier']) && !empty($_POST['selSupplier']) ) ? $db->clean(functions::decode($_POST['selSupplier'])) : '';
	$_SESSION['pfEquip'] = ( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ) ? $db->clean(functions::decode($_POST['selEquip'])) : '';
	$_SESSION['pfMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $db->clean($_POST['bdMon']) : '';
	$_SESSION['pfYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $db->clean($_POST['bdYear']) : '';
	$_SESSION['pfDay'] = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $db->clean($_POST['bdDay']) : ''; 
	$_SESSION['pfMonTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $db->clean($_POST['bdMonTo']) : '';
	$_SESSION['pfYearTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $db->clean($_POST['bdYearTo']) : '';
	$_SESSION['pfDayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $db->clean($_POST['bdDayTo']) : '';
	$unpaid=1;
}

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM po WHERE po_type="fuel") ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
	if($rProj['proj_id'])
		$arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;

$qPayee = $db->query('SELECT DISTINCT payee FROM po_fuel ORDER BY payee');
while($rPayee = $db->fetch_array($qPayee)):
	if($rPayee['payee'])
		$arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;
ksort($arrChargeList);


$arrEquipList=array();
#$qEquip = $db->query('SELECT * FROM equipment e, po_fuel_equip pfe WHERE e.equip_id=pfe.equip_id');
$qEquip = $db->query('SELECT * FROM equipment ORDER BY name');
while($rEquip = $db->fetch_array($qEquip)):
	if($rEquip['name'])
		$arrEquipList[$rEquip['inventory_id'].' '.$rEquip['name']]=$rEquip['equip_id'];
endwhile;

$qOtherEquip = $db->query('SELECT DISTINCT other_equip FROM po_fuel_equip ORDER BY other_equip');
while($rOtherEquip = $db->fetch_array($qOtherEquip)):
	if($rOtherEquip['other_equip'])
	$arrEquipList[strtoupper($rOtherEquip['other_equip'])]=$rOtherEquip['other_equip'];
endwhile;
ksort($arrEquipList);

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
	$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_fuel_equipment pfe WHERE p.po_id=pf.po_id AND p.po_id=pfe.po_id AND po_type="fuel" '.$where.' ORDER BY po_date DESC');
	if($unpaid)
		$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_fuel_equipment pfe WHERE p.po_id=pf.po_id AND p.po_id=pfe.po_id AND po_type="fuel" AND vp_id is NULL '.$where.' ORDER BY po_date DESC');
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
	<title>P.O. Fuel Report</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Fuel Purchase Order Report</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="po_voucher_fuel_report.php">PO / Voucher Fuel</a></li>
				<li><a href="po_fuel_voucher_report.php">Voucher Fuel</a></li>
				<li class="active"><a href="#" style="opacity:.9">PO Fuel</a></li>
			</ul>
			<form class="form-horizontal" method="post">
				<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
					<tr>
						<td width="50%"><div align="right"><a id="whprint" class="btn btn-info" href="po_fuel_report_print.php"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div><br><br></td>
					</tr>
				</table>
				<table border="0">
					<tr>
						<td width="20%">
							<div align="center">
								<div align="center"><strong>FROM</strong></div>
								<select name="bdYear" id="bdYear" style="width:80px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array('po_type'=>'fuel'),'ORDER BY po_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMon" id="bdMon" style="width:85px;">
									<option value="">All Month</option>
									<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="bdDay" id="bdDay" style="width:65px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div><br>
							<div align="center">
								<div align="center"><strong>TO</strong></div>
								<select name="bdYearTo" id="bdYearTo" style="width:80px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array('po_type'=>'fuel'),'ORDER BY po_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMonTo" id="bdMonTo" style="width:85px;">
									<option value="">All Month</option>
									<option value="01" <?php if($txbMonTo=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($txbMonTo=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($txbMonTo=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($txbMonTo=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($txbMonTo=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($txbMonTo=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($txbMonTo=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($txbMonTo=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($txbMonTo=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($txbMonTo=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($txbMonTo=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($txbMonTo=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="bdDayTo" id="bdDayTo" style="width:65px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
						</td>
						<td width="20%">
							<div align="left">
								<table border="0" class="tablea">
									<tr>
										<td style="padding: 15px 10px 15px 0px;">Charge To:</td>
										<td>
											<select name="selProj" id="selProj" data-rel="chosen" style="width:650px;">
												<option value="">--All Charge To--</option>
												<?php foreach($arrChargeList as $name => $projid): ?>
												<option value="<?php echo $projid?>" <?php if($charge===$projid)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 15px 10px 15px 0px;">Supplier</td>
										<td>
											<select name="selSupplier" id="selSupplier" data-rel="chosen" style="width:650px;">
												<option value="">-- All Supplier --</option>
												<?php
												$qItmD = $db->select('po p, po_fuel pf, supplier s','p.supplierID, name, count(*)',array(),'WHERE s.supplierID=p.supplierID AND p.po_id=pf.po_id AND po_type="fuel" GROUP BY p.supplierID ORDER BY s.name');
												while($rItmD = $db->fetch_array($qItmD)):
												?>
												<option value="<?php echo functions::encode($rItmD['supplierID'])?>" <?php if($rItmD['supplierID']===$supplier)echo 'selected="selected"';?>><?php echo ucwords(strtolower($rItmD['name']));?></option>
												<?php endwhile;?>
											</select>
										</td>
									</tr>
									<tr>
										<td style="padding: 15px 10px 15px 0px;">Equipment</td>
										<td>
											<select name="selEquip" id="selEquip" data-rel="chosen" style="width:650px;">
												<option value="">-- All Equipment --</option>
												<?php foreach($arrEquipList as $equip_name => $equipid): ?>
												<option value="<?php echo functions::encode($equipid)?>" <?php if($equipid===$equip_id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($equip_name));?></option>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
								</table>
							</div>
						</td>
						<td width="8%">
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
					<tr><td colspan="4"><hr width="100%"></td></tr>
				</table>
				<table class="table table-bordered table-hover" style="font-size:12px">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="9%">P.O. No</th>
							<th width="9%">P.O. Date</th>
							<th width="10%">Supplier</th>
							<th width="15%">Equipment</th>
							<th width="19%">Charge To</th>
							<th width="4%"><div align="center">Fuel (Liter)</div></th>
							<th width="4%"><div align="center">Price</div></th>
							<th width="4%"><div align="center">Oil</div></th>
							<th width="4%"><div align="center">Price</div></th>
							<th width="8%"><div align="center">Cost</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$count=0;$totalUnpaidAmount=0;$totalAmount=0;$totalAmountFuel=0;$totalAmountOil=0;$totalFuelLiter=0;$totalOilLiter=0;
					while($rPO = $db->fetch_array($qPO)):
						$count++;
						$payee = $db->getValue('po_fuel','payee',array('po_id'=>$rPO['po_id']));
						#$equip_id = $db->getValue('po_fuel','equip_id',array('po_id'=>$rPO['po_id']));
						#$equipment = $db->getValue('equipment','concat(inventory_id," ",name)',array('equip_id'=>$equip_id));

						#$amountFuel = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']),'AND item LIKE "%fuel%"');
						#$totalAmountFuel += $amountFuel;
						$fuelLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id']),'AND item LIKE "%fuel%"');
						$totalFuelLiter += $fuelLiter;
						$fuelPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id']),'AND item LIKE "%fuel%"');
						$totalAmountFuel += $fuelPrice * $fuelLiter;
						$fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

						#$amountOil = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']),'AND item LIKE "%oil%"');
						#$totalAmountOil += $amountOil;

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
							<td><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></td>
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
							<td><div align="right"><?php echo ($fuelPrice) ? functions::formatMoney($fuelPrice) : '';?></div></td>
							<td><div align="center"><?php echo $oilLiter;?></div></td>
							<td><div align="right"><?php echo ($oilPrice) ? functions::formatMoney($oilPrice) : '';?></div></td>
							<td><div align="right"><?php echo ($amount) ? functions::formatMoney($amount) : '--';?></div></td>
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
							<td>&nbsp;</td>
							<td><div align="center"><strong><?php echo (functions::isfloat($totalFuelLiter)) ? functions::formatMoney($totalFuelLiter) : number_format($totalFuelLiter);?></strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($totalAmountFuel);?></strong></div></td>
							<td><div align="center"><strong><?php echo (functions::isfloat($totalOilLiter)) ? functions::formatMoney($totalOilLiter) : $totalOilLiter;?></strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($totalAmountOil);?></strong></div></td>
							<td><div align="right"><strong><?php echo functions::formatMoney($totalAmount);?></strong></div></td>
						</tr>
						<?php }?>
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
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html