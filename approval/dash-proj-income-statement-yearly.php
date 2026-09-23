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
$project_id = ( isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : '';

function monthDisp($date=0){
	$exp = explode('-',$date);
	$monthName='';
	if(count($exp)==2){
		$mn = $exp[1];
		$yr = $exp[0];
		$mkDate = mktime(0,0,0,$mn,1,$yr);
		$monthName = date("F'y",$mkDate);
	}
	return $monthName;
}
#$project_id=9;
$arrGrossBilled=array();
$arrDirectCost=array();
$arrOverheadCost=array();
$arrNetBilled=array();
$arrTotalDirectCost = array();
$arrTotalNetBilled = array();
$arrDeduction=array();
$totalLabor=0;  $totalMaterial=0; $totalEquipment=0;  $totalCommitment=0; $totalFinder=0; $totalTechnical=0; $totalSubcon=0; $totalConsultancy=0; $totalDirectCost=0; $totalOverheadCost=0; $totalOperatingExpenses=0; $totalIncome=0; $totalNetBilled=0;
$countID=0;$totalVAT=0;$totalEWT=0;$totalRetention=0;$totalConTax=0;$totalRecoupment=0; $totalDeductions=0;$totalExpenses=0;$totalNetIncome=0;$totalMaterialIH=0; $totalEquipmentIH=0; $totalOtherIncome=0;
$arrYear=array();
$qProjYear = $db->query('SELECT distinct left(date_start,4) as yr FROM `project` WHERE date_start IS NOT NULL ORDER BY yr');
while($rProjYear = $db->fetch_array($qProjYear)):
	$arrYear[]=$rProjYear['yr'];
endwhile;
foreach($arrYear as $perYear):
	$arrGrossBilled[$perYear]=0;
	$arrDeduction[$perYear]=0;
	$arrDirectCost[$perYear]=0;
	$arrNetBilled[$perYear]=0;
	$arrTotalDirectCost[$perYear]=0;
	$arrTotalNetBilled[$perYear]=0;
endforeach;
function getVals($query,&$array){
	global $db;
	while($rVL = $db->fetch_array($query)):
	$array[$rVL['dt']] = (isset($array[$rVL['dt']])) ? ($array[$rVL['dt']] + $rVL['amnt']) : $rVL['amnt'];
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Report</title>
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
	<style>
	.padleft{padding-right: 5px;}
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header">
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Comparative Income Statement & Retained Earnings of All Projects</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="dash-proj-income-statement-yearly.php" style="opacity:.9">Yearly Report</a></li>
				<li><a href="dash-proj-income-statement-all.php">All Projects</a></li>
				<li><a href="dash-proj-income-statement.php">Selected Project</a></li>
			</ul>
		</div>
		<div class="table-wrapper">
			<table class="table-hover" width="100%" border="1" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th height="30px">Revenue</th>
						<?php foreach($arrYear as $perYear):?>
						<th><div align="center"><?php echo $perYear?></div></th>
						<?php endforeach;?>
						<th class="padleft"><div align="center">Total</div></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrYear as $perYear):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Gross Billed Amount</td>
						<?php
						$arrCostIncome = array();
						$qCostIncome = $db->query('SELECT sum(pi.amount) as amnt, left(pi_date,4) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
						getVals($qCostIncome,$arrCostIncome);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costIncome=0;
								$costIncome=isset($arrCostIncome[$perYear]) ? $arrCostIncome[$perYear] : 0;
								$arrGrossBilled[$perYear]=$costIncome;
								$totalIncome += $costIncome;
								echo functions::formatMoney($costIncome);
								?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalIncome);?></div></td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrYear) + 2?>">Less: Deductions</td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;VAT</td>
						<?php
						$arrVAT = array();
						$qVat = $db->query('SELECT sum(pi.vat) as amnt, left(pi_date,4) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
						getVals($qVat,$arrVAT);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
								<?php
								$vat = isset($arrVAT[$perYear]) ? $arrVAT[$perYear] : 0;
								$totalVAT += $vat;
								$arrNetBilled[$perYear] += $vat;
								$arrDeduction[$perYear] += $vat;
								echo functions::formatMoney($vat);
								?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalVAT);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;EWT</td>
						<?php
						$arrEWT=array();
						$qEWT = $db->query('SELECT sum(pi.ewt) as amnt, left(pi_date,4) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
						getVals($qEWT,$arrEWT);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$ewt = isset($arrEWT[$perYear]) ? $arrEWT[$perYear] : 0;
							$totalEWT += $ewt;
							$arrNetBilled[$perYear] += $ewt;
							$arrDeduction[$perYear] += $ewt; 
							echo functions::formatMoney($ewt);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEWT);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Retention</td>
						<?php
						$arrRetention=array();
						$qRetention = $db->query('SELECT sum(pi.retention) as amnt, left(pi_date,4) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
						getVals($qRetention,$arrRetention);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$retention = isset($arrRetention[$perYear]) ? $arrRetention[$perYear] : 0;
							$totalRetention += $retention;
							$arrNetBilled[$perYear] += $retention;
							$arrDeduction[$perYear] += $retention;
							echo functions::formatMoney($retention);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRetention);?></div></td>
					</tr>
					<tr>
						<td> &nbsp;&nbsp;&nbsp;Contractor's Tax</td>
						<?php
						$arrContractor=array();
						$qContractor = $db->query('SELECT sum(pi.contractor) as amnt, left(pi_date,4) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
						getVals($qContractor,$arrContractor);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$contractor = isset($arrContractor[$perYear]) ? $arrContractor[$perYear] : 0;
							$totalConTax += $contractor;
							$arrNetBilled[$perYear] += $contractor;
							$arrDeduction[$perYear] += $contractor;
							echo functions::formatMoney($contractor);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConTax);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Recoupment</td>
						<?php
						$arrRecoupment=array();
						$qRecoupment = $db->query('SELECT sum(pi.recoupment) as amnt, left(pi_date,4) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
						getVals($qRecoupment,$arrRecoupment);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$recoupment = isset($arrRecoupment[$perYear]) ? $arrRecoupment[$perYear] : 0;
							$totalRecoupment += $recoupment;
							$arrNetBilled[$perYear] += $recoupment;
							$arrDeduction[$perYear] += $recoupment;
							echo functions::formatMoney($recoupment);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRecoupment);?></div></td>
					</tr>
					<tr>
						<td><strong>Net Billed Amount</strong></td>
						<?php
						$arrGrossBill=array();
						$qCostIncome = $db->query('SELECT sum(pi.amount) as amnt, left(pi_date,4) as dt  FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
						getVals($qCostIncome,$arrGrossBill);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								$totalDeductions += $arrNetBilled[$perYear];
								$costIncome = isset($arrGrossBill[$perYear]) ? $arrGrossBill[$perYear] : 0;
								$totalNetBilled += $arrGrossBilled[$perYear] - $arrNetBilled[$perYear];
								$arrTotalNetBilled[$perYear] += $arrGrossBilled[$perYear] - $arrNetBilled[$perYear];
								echo functions::formatMoney($arrGrossBilled[$perYear] - $arrNetBilled[$perYear]);
								?>
								</strong>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalNetBilled);?></strong></div></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrYear as $perYear):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><strong>Regular Deposit</strong></td>
						<?php
						$arrRegDep=array();
						$qCostRegDep = $db->query('SELECT sum(as_amount) as amnt, left(as_date,4) as dt FROM account_statement WHERE transaction_type="deposit" AND as_type="credit" GROUP BY dt');
						getVals($qCostRegDep,$arrRegDep);
						$totalRegDep=0;foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costRegDep = isset($arrRegDep[$perYear]) ? $arrRegDep[$perYear] : 0;
							$totalRegDep += $costRegDep;
							$totalNetBilled += $costRegDep;
							$arrTotalNetBilled[$perYear] += $costRegDep;
							$arrGrossBilled[$perYear] += $costRegDep;
							echo functions::formatMoney($costRegDep);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalRegDep);?></strong></div></td>
					</tr>
					<tr>
						<td><strong>Loan Proceeds</strong></td>
						<?php
						$arrCostLoan=array();
						$qCostLoan = $db->query('SELECT sum(as_amount) as amnt, left(as_date,4) as dt FROM account_statement WHERE description="LOAN PROCEEDS" GROUP BY dt');
						getVals($qCostLoan,$arrCostLoan);
						$totalLoan=0;foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costLoan = isset($arrCostLoan[$perYear]) ? $arrCostLoan[$perYear] : 0;
							$totalLoan += $costLoan;
							$totalNetBilled += $costLoan;
							$arrTotalNetBilled[$perYear] += $costLoan;
							$arrGrossBilled[$perYear] += $costLoan;
							echo functions::formatMoney($costLoan);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalLoan);?></strong></div></td>
					</tr>
					<tr>
						<td><strong>Cash Return</strong></td>
						<?php
						$arrCashRet=array();
						$qCostCashRet = $db->query('SELECT sum(as_amount) as amnt, left(as_date,4) as dt FROM account_statement WHERE transaction_type="cash return" AND confirmned="2" GROUP BY dt');
						getVals($qCostCashRet,$arrCashRet);
						$totalCashRet=0;foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costCashRet = isset($arrCashRet[$perYear]) ? $arrCashRet[$perYear] : 0;
							$totalCashRet += $costCashRet;
							$totalNetBilled += $costCashRet;
							$arrTotalNetBilled[$perYear] += $costCashRet;
							$arrGrossBilled[$perYear] += $costCashRet;
							echo functions::formatMoney($costCashRet);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalCashRet);?></strong></div></td>
					</tr>
					<tr>
						<td><strong>Stock Share</strong></td>
						<?php
						$arrStockShare=array();
						$qCostStockShare = $db->query('SELECT sum(as_amount) as amnt, left(as_date,4) as dt FROM account_statement WHERE transaction_type="stock share" GROUP BY dt');
						getVals($qCostStockShare,$arrStockShare);
						$totalStockShare=0;foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costStockShare = isset($arrStockShare[$perYear]) ? $arrStockShare[$perYear] : 0;
							$totalStockShare += $costStockShare;
							$totalNetBilled += $costStockShare;
							$arrTotalNetBilled[$perYear] += $costStockShare;
							$arrGrossBilled[$perYear] += $costStockShare;
							echo functions::formatMoney($costStockShare);
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalStockShare);?></strong></div></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrYear as $perYear):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrYear) + 2?>"><strong>Other Income</strong></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;In-House (Outside)</td>
						<?php
						$arrIhO=array();
						$qInhouseMaterialOther = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi WHERE im.im_id=imi.im_id AND im.payee != "" AND is_paid="2" GROUP BY dt');
						getVals($qInhouseMaterialOther,$arrIhO);

						$qInhouseRentalOther = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as amnt, left(iel_date,4) as dt FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND iel.payee != "" AND is_paid="2" GROUP BY dt');
						getVals($qInhouseRentalOther,$arrIhO);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costOtherIncome = isset($arrIhO[$perYear]) ? $arrIhO[$perYear] : 0;
							$totalOtherIncome += $costOtherIncome;
							$totalNetBilled += $costOtherIncome;
							$arrTotalNetBilled[$perYear] += $costOtherIncome;
							$arrGrossBilled[$perYear] += $costOtherIncome;
							echo ($costOtherIncome) ? functions::formatMoney($costOtherIncome) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalOtherIncome);?></div></td>
					</tr> 
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrYear as $perYear):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrYear) + 2?>">Less: Direct Cost</td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Labor</td>
						<?php
						$arrLabor=array();
						$qVoucherLabor = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="labor" GROUP BY dt');
						getVals($qVoucherLabor,$arrLabor);

						$qPOLabor = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="labor" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOLabor,$arrLabor);

						$qInhouseMaterialLabor = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.expense_type="labor" GROUP BY dt');
						getVals($qInhouseMaterialLabor,$arrLabor);

						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costLabor = isset($arrLabor[$perYear]) ? $arrLabor[$perYear] : 0;
							$totalLabor += $costLabor;
							$arrDirectCost[$perYear] += $costLabor;
							echo ($costLabor) ? functions::formatMoney($costLabor) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalLabor);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Materials</td>
						<?php
							$arrMaterials=array();
							$qVoucherMaterial = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="materials" GROUP BY dt');
							getVals($qVoucherMaterial,$arrMaterials);

							$qPO = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="materials" AND vpp.paid > 0 GROUP BY dt');
							getVals($qPO,$arrMaterials);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costMaterial = isset($arrMaterials[$perYear]) ? $arrMaterials[$perYear] : 0;
							$totalMaterial += $costMaterial;
							$arrDirectCost[$perYear] += $costMaterial;
							echo ($costMaterial) ? functions::formatMoney($costMaterial) : '';  
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft">
						<div align="right"><?php echo functions::formatMoney($totalMaterial);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Materials (In House)</td>
						<?php
						$arrMaterialsIH=array();
						$qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.expense_type="materials" GROUP BY dt');
						getVals($qInhouseMaterial,$arrMaterialsIH);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costMaterialIH = isset($arrMaterialsIH[$perYear]) ? $arrMaterialsIH[$perYear] : 0;
							$totalMaterialIH += $costMaterialIH;
							$arrDirectCost[$perYear] += $costMaterialIH;
							echo ($costMaterialIH) ? functions::formatMoney($costMaterialIH) : '';  
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalMaterialIH);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Equipment/Rental</td>
						<?php
						$arrEquipRent=array();
						$qVoucherEquipment = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="equipment" GROUP BY dt');
						getVals($qVoucherEquipment,$arrEquipRent);

						$qPOEquip = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="equipment" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOEquip,$arrEquipRent);

						$qInhouseMaterialEquipment = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.expense_type="equipment" GROUP BY dt');
						getVals($qInhouseMaterialEquipment,$arrEquipRent);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costEquipment = isset($arrEquipRent[$perYear]) ? $arrEquipRent[$perYear] : 0;
							$totalEquipment += $costEquipment;           
							$arrDirectCost[$perYear] += $costEquipment;
							echo ($costEquipment) ? functions::formatMoney($costEquipment) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEquipment);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Equipment Rental (In-House)</td>
						<?php
						$arrIhRental = array();
						$qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as amnt, left(iel_date,4) as dt FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id GROUP BY dt');
						getVals($qInhouseRental,$arrIhRental);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costEquipmentIH = isset($arrIhRental[$perYear]) ? $arrIhRental[$perYear] : 0;
							$totalEquipmentIH += $costEquipmentIH;           
							$arrDirectCost[$perYear] += $costEquipmentIH;
							echo ($costEquipmentIH) ? functions::formatMoney($costEquipmentIH) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEquipmentIH);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Commitment</td>
						<?php
						$arrCommitment=array();
						$qCommitment = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="commitment" GROUP BY dt');
						getVals($qCommitment,$arrCommitment);

						$qPOCommit = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="commitment" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOCommit,$arrCommitment);

						$qInhouseMaterialCommit = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="commitment" GROUP BY dt');
						getVals($qInhouseMaterialCommit,$arrCommitment);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costCommitment = isset($arrCommitment[$perYear]) ? $arrCommitment[$perYear] : 0;
							$totalCommitment += $costCommitment;
							$arrDirectCost[$perYear] += $costCommitment;
							echo ($costCommitment) ? functions::formatMoney($costCommitment) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCommitment);?></div></td>
					</tr>

					<tr>
						<td>&nbsp;&nbsp;&nbsp;Finder's Fee</td>
						<?php
						$arrFinder=array();
						$qFinder = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="finders fee" GROUP BY dt');
						getVals($qFinder,$arrFinder);

						$qPOFinder = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="finders fee" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOFinder,$arrFinder);

						$qInhouseMaterialFinder = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="finders fee" GROUP BY dt');
						getVals($qInhouseMaterialFinder,$arrFinder);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costFinder = isset($arrFinder[$perYear]) ? $arrFinder[$perYear] : 0;
							$totalFinder += $costFinder;
							$arrDirectCost[$perYear] += $costFinder;
							echo ($costFinder) ? functions::formatMoney($costFinder) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalFinder);?></div></td>
					</tr>

					<tr>
						<td>&nbsp;&nbsp;&nbsp;Consultancy</td>
						<?php
						$arrConsultant=array();
						$qConsultancy = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="consultancy" GROUP BY dt');
						getVals($qConsultancy,$arrConsultant);

						$qPOConsultancy = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="consultancy" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOConsultancy,$arrConsultant);

						$qInhouseMaterialConsultancy = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="consultancy" GROUP BY dt');
						getVals($qInhouseMaterialConsultancy,$arrConsultant);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costConsultancy = isset($arrConsultant[$perYear]) ? $arrConsultant[$perYear] : 0;
							$totalConsultancy += $costConsultancy;
							$arrDirectCost[$perYear] += $costConsultancy;
							echo ($costConsultancy) ? functions::formatMoney($costConsultancy) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConsultancy);?></div></td>
					</tr>

					<tr>
						<td>&nbsp;&nbsp;&nbsp;Technical Fee</td>
						<?php
						$arrTechFee=array();
						$qTechnical = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="technical fee" GROUP BY dt');
						getVals($qTechnical,$arrTechFee);

						$qPOTechnical = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="technical fee" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOTechnical,$arrTechFee);

						$qInhouseMaterialTechnical = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="technical fee" GROUP BY dt');
						getVals($qInhouseMaterialTechnical,$arrTechFee);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costTechnical = isset($arrTechFee[$perYear]) ? $arrTechFee[$perYear] : 0;
							$totalTechnical += $costTechnical;
							$arrDirectCost[$perYear] += $costTechnical;
							echo ($costTechnical) ? functions::formatMoney($costTechnical) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalTechnical);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Subcon</td>
						<?php
						$arrSubcon=array();
						$qVoucherSubcon = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="subcon" GROUP BY dt');
						getVals($qVoucherSubcon,$arrSubcon);

						$qPOSubcon = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="subcon" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOSubcon,$arrSubcon);

						$qInhouseMaterialSubcon = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id  AND itd.expense_type="subcon" GROUP BY dt');
						getVals($qInhouseMaterialSubcon,$arrSubcon);
						foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costSubcon = isset($arrSubcon[$perYear]) ? $arrSubcon[$perYear] : 0;
							$totalSubcon += $costSubcon;
							$arrDirectCost[$perYear] += $costSubcon;
							echo ($costSubcon) ? functions::formatMoney($costSubcon) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalSubcon);?></div></td>
					</tr>
					<tr>
						<td><strong>Total Direct Cost</strong></td>
						<?php foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								$totalDirectCost += $arrDirectCost[$perYear];
								$arrTotalDirectCost[$perYear] += $arrDirectCost[$perYear];
								echo functions::formatMoney($arrDirectCost[$perYear]);
								?>
								</strong>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalDirectCost);?></strong></div></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrYear as $perYear):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrYear) + 2?>">Less: Operating Expenses</td>
					</tr>
					<?php
					$arrCategory = array();
					$arrCategoryName = array();
					$overVoucher = $db->query('SELECT category_id, itd.name as nme FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" GROUP BY category_id, itd.name ORDER BY itd.name');
					while($rOverVoucher = $db->fetch_array($overVoucher)):
						$arrCategoryName[$rOverVoucher['category_id']]=$rOverVoucher['nme'];
					endwhile;

					$overPO = $db->query('SELECT category_id, itd.name as nme FROM po p, view_po_payment vpp, item_deduction itd WHERE p.category_id=itd.item_id AND p.po_id=vpp.po_id AND paid > 0 AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" GROUP BY category_id, itd.name ORDER BY itd.name');
					while($rOverPO = $db->fetch_array($overPO)):
						$arrCategoryName[$rOverPO['category_id']]=$rOverPO['nme'];
					endwhile;

					$overInhouseMaterial = $db->query('SELECT category_id, itd.name as nme FROM inhouse_material im, item_deduction itd WHERE im.category_id=itd.item_id AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" GROUP BY category_id, itd.name ORDER BY itd.name');
					while($rOverInhouseMaterial = $db->fetch_array($overInhouseMaterial)):
						$arrCategoryName[$rOverInhouseMaterial['category_id']]=$rOverInhouseMaterial['nme'];
					endwhile;
					asort($arrCategoryName);

					$q1 = $db->query('SELECT left(vd_date,4) as dt, vd.category_id as catid, round(sum(amount),2) as amnt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id GROUP BY dt,vd.category_id ORDER BY itd.name');
					while($r1 = $db->fetch_array($q1)):
						$date = $r1['dt'];
						$catid = $r1['catid'];
						$amount = $r1['amnt'];
						if( isset($arrCategoryName[$catid]) ){
							if(isset($arrCategory[$catid][$date])){
								$arrCategory[$catid][$date] = ($arrCategory[$catid][$date] + $amount);
							}
							else{
								$arrCategory[$catid][$date] = $amount;
							}
						}
					endwhile;

					$q2 = $db->query('SELECT left(p.po_date,4) as dt, p.category_id as catid, round(sum(cost),2) as amnt FROM po p, view_po_payment vpp WHERE p.po_id=vpp.po_id AND vpp.paid > 0 GROUP BY dt,p.category_id');
					while($r2 = $db->fetch_array($q2)):
						$date = $r2['dt'];
						$catid = $r2['catid'];
						$amount = $r2['amnt'];
						if( isset($arrCategoryName[$catid]) ){
							if(isset($arrCategory[$catid][$date])){
								$arrCategory[$catid][$date] = ($arrCategory[$catid][$date] + $amount);
							}
							else{
								$arrCategory[$catid][$date] = $amount;
							}
						}
					endwhile;

					$q3 = $db->query('SELECT left(im_date,4) as dt, im.category_id as catid, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id  GROUP BY dt, im.category_id,itd.name');
					while($r3 = $db->fetch_array($q3)):
						$date = $r3['dt'];
						$catid = $r3['catid'];
						$amount = $r3['amnt'];
						if( isset($arrCategoryName[$catid]) ){
							if(isset($arrCategory[$catid][$date])){
								$arrCategory[$catid][$date] = ($arrCategory[$catid][$date] + $amount);
							}
							else{
								$arrCategory[$catid][$date] = $amount;
							}
						}
					endwhile;
					$prevTotalOperatingExpenses = 0;
					foreach( $arrCategoryName as $categoryID => $categoryName ):
					#foreach( $arrCategory as $categoryID ):
						$totalCategoryCost=0;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;<?php echo $categoryName; #echo $db->getValue('item_deduction','name',array('item_id'=>$categoryID));?></td>
						<?php foreach($arrYear as $perYear):
							$countID++;
							if( !isset($arrOverheadCost[$perYear]) )
								$arrOverheadCost[$perYear]=0;
						?>
						<td class="padleft">
							<div align="right">
							<?php
							$categoryCost=0;
							$categoryCost=isset($arrCategory[$categoryID][$perYear]) ? $arrCategory[$categoryID][$perYear] : 0;
							$arrOverheadCost[$perYear] += $categoryCost;
							$totalCategoryCost += $categoryCost;
							$totalOverheadCost += $categoryCost;
							$totalOperatingExpenses += $categoryCost;
							echo ($categoryCost) ? functions::formatMoney($categoryCost) : '';
							?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCategoryCost);?></div></td>
					</tr>
					<?php endforeach;?>
					<tr>
						<td><strong>Total Operating Expenses</strong></td>
						<?php foreach($arrOverheadCost as $ohCost):?>
						<td class="padleft"><div align="right"><strong><?php echo ($ohCost) ? functions::formatMoney($ohCost) : '';?></strong></div></td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalOverheadCost); ?> </strong></div></td>
					</tr>
					<tr>
						<td><div align="left"><strong>Total Expenses&nbsp;&nbsp;</strong></div></td>
						<?php foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								if( isset($arrTotalDirectCost[$perYear]) && isset($arrOverheadCost[$perYear]) ){
								$totalExpenses += $arrTotalDirectCost[$perYear] + $arrOverheadCost[$perYear];
								echo functions::formatMoney($arrTotalDirectCost[$perYear] + $arrOverheadCost[$perYear]);
								}?>
								</strong>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalExpenses); ?> </strong></div></td>
					</tr>
					<tr>
						<td><div align="left"><strong>Net Income&nbsp;&nbsp;</strong></div></td>
						<?php foreach($arrYear as $perYear):?>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								if( isset($arrTotalNetBilled[$perYear]) && isset($arrTotalDirectCost[$perYear]) && isset($arrOverheadCost[$perYear]) ){
									$netIncome = $arrGrossBilled[$perYear] - ($arrDeduction[$perYear] + $arrDirectCost[$perYear] + $arrOverheadCost[$perYear]);
									$totalNetIncome += $arrTotalNetBilled[$perYear] - ($arrTotalDirectCost[$perYear] + $arrOverheadCost[$perYear]);
									#echo functions::formatMoney($arrTotalNetBilled[$perYear] - ($arrTotalDirectCost[$perYear] + $arrOverheadCost[$perYear]));
									echo functions::formatMoney($netIncome);
								}?>
								</strong>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalNetIncome); ?> </strong></div></td>
					</tr>
				</tbody>
			</table>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>function selDt(PiEwgD){window.location="<?php echo functions::pageName()?>?yr="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>