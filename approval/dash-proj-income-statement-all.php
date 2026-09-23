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
$arrDirectCost=array();
$arrOverheadCost=array();
$arrNetBilled=array();
$totalLabor=0;  $totalMaterial=0; $totalEquipment=0;  $totalCommitment=0; $totalFinder=0; $totalTechnical=0; $totalSubcon=0; $totalConsultancy=0; $totalDirectCost=0; $totalOverheadCost=0; $totalOperatingExpenses=0; $totalIncome=0; $totalNetBilled=0; $totalLoan=0; $totalRegularDeposit=0;
$countID=0;$totalVAT=0;$totalEWT=0;$totalRetention=0;$totalConTax=0;$totalRecoupment=0; $totalDeductions=0; $totalMaterialIH=0; $totalEquipmentIH=0; $totalOtherIncome=0;

$year = date('Y');
$month_start = date('m');
$year_start = date('Y');
$month_end = date('m');
$year_end = date('Y');
$year = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : $year;
$arrMonth=array();

$month_start = '01';
$year_start = $year;

$month_end = '11';
$year_end=$year;
$start_date=$year.'-01-01';
$end_date=$year.'-12-31';
#end getting end month

$month_diff = functions::month_diff($start_date,$year_end.'-'.$month_end.'-01');
for($i=0; $i<=$month_diff; $i++):
	$mkDate = mktime(0,0,0,$month_start + $i,1,$year_start);
	$arrMonth[] = date('Y-m',$mkDate);
	$arrDirectCost[date('Y-m',$mkDate)]=0;
	$arrNetBilled[date('Y-m',$mkDate)]=0;
endfor;

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
	<title>Project Report Yearly</title>
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
				<li><a href="dash-proj-income-statement-yearly.php">Yearly Report</a></li>
				<li class="active"><a href="dash-proj-income-statement-all.php" style="opacity:.9">All Projects</a></li>
				<li><a href="dash-proj-income-statement.php">Selected Project</a></li>
			</ul>
		</div>
		<div align="center"><br>
			<table width="80%" border="0">
				<tr>
					<td width="50%" align="right"><strong>Select Year:</strong>&nbsp;</td>
					<td width="50%" align="left" valign="middle" style="padding: 12px 0px 4px 0px">
						<select name="yr" id="yr" style="width:150px;" onchange='selDt(this.value)'>
							<option value="">-- Select --</option>
							<?php
							$qYr = $db->query('SELECT DISTINCT left(vd_date,4) as dt FROM voucher_detail ORDER BY vd_date DESC ');
							while($rYr = $db->fetch_array($qYr)):
							?>
							<option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($year==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
							<?php endwhile;?>
						</select>
					</td>
				</tr>
			</table><br>
		</div>
		<div class="table-wrapper">
			<table class="table-hover" width="100%" border="1" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th height="30px">Revenue</th>
						<?php foreach($arrMonth as $month):?>
						<th><div align="center"><?php echo monthDisp($month)?></div></th>
						<?php endforeach;?>
						<th class="padleft"><div align="center">Total</div></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Gross Billed Amount</td>
						<?php
						$arrCostIncome=array();
						$qCostIncome = $db->query('SELECT sum(pi.amount) as amnt, left(pi_date,7) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,4)="'.$year.'" GROUP BY dt');
						getVals($qCostIncome,$arrCostIncome);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$costIncome=isset($arrCostIncome[$monthIncome]) ? $arrCostIncome[$monthIncome] : 0;
							$totalIncome += $costIncome;
							if($costIncome){
							?>
							<div align="right">
								<a id="income<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-income.php?mnth=<?php echo functions::encode($monthIncome);?>','Billed Amount','1')">
								<?php echo functions::formatMoney($costIncome);?>
								</a>
							</div>
							<?php } ?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalIncome);?></div></td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrMonth) + 2?>">Less: Deductions</td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;VAT</td>
						<?php
						$arrVat = array();
						$qVat = $db->query('SELECT sum(pi.vat) as amnt, left(pi_date,7) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,4)="'.$year.'" GROUP BY dt');
						getVals($qVat,$arrVat);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<div align="right">
								<?php
								$vat=isset($arrVat[$monthIncome]) ? $arrVat[$monthIncome] : 0;
								$totalVAT += $vat;
								$arrNetBilled[$monthIncome] += $vat;
								if($vat){
								?>
								<a id="vat<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-vat.php?mnth=<?php echo functions::encode($monthIncome);?>','VAT','1')"><?php echo functions::formatMoney($vat);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalVAT);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;EWT</td>
						<?php
						$arrEWT = array();
						$qEWT = $db->query('SELECT sum(pi.ewt) as amnt, left(pi_date,7) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,4)="'.$year.'" GROUP BY dt');
						getVals($qEWT,$arrEWT);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<div align="right">
								<?php
								$ewt=isset($arrEWT[$monthIncome]) ? $arrEWT[$monthIncome] : 0;
								$totalEWT += $ewt;
								$arrNetBilled[$monthIncome] += $ewt;
								if($ewt){
								?>
								<a id="ewt<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-ewt.php?mnth=<?php echo functions::encode($monthIncome);?>','EWT','1')"><?php echo functions::formatMoney($ewt);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEWT);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Retention</td>
						<?php
						$arrRetention=array();
						$qRetention = $db->query('SELECT sum(pi.retention) as amnt, left(pi_date,7) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,4)="'.$year.'" GROUP BY dt');
						getVals($qRetention,$arrRetention);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<div align="right">
								<?php
								$retention=isset($arrRetention[$monthIncome]) ? $arrRetention[$monthIncome] : 0;
								$totalRetention += $retention;
								$arrNetBilled[$monthIncome] +=  $retention;
								if($retention){
								?>
								<a id="ret<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-retention.php?mnth=<?php echo functions::encode($monthIncome);?>','RETENTION','1')"><?php echo functions::formatMoney($retention);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRetention);?></div></td>
					</tr>
					<tr>
						<td> &nbsp;&nbsp;&nbsp;Contractor's Tax</td>
						<?php
						$arrContractor=array();
						$qContractor = $db->query('SELECT sum(pi.contractor) as amnt, left(pi_date,7) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,4)="'.$year.'" GROUP BY dt');
						getVals($qContractor,$arrContractor);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<div align="right">
								<?php
								$contractor=isset($arrContractor[$monthIncome]) ? $arrContractor[$monthIncome] : 0;
								$totalConTax += $contractor;
								$arrNetBilled[$monthIncome] += $contractor;
								if($contractor){
								?>
								<a id="contractor<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-contax.php?mnth=<?php echo functions::encode($monthIncome);?>','CONTRACTOR TAX','1')"><?php echo functions::formatMoney($contractor);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConTax);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Recoupment</td>
						<?php
						$arrRecoupment=array();
						$qRecoupment = $db->query('SELECT sum(pi.recoupment) as amnt, left(pi_date,7) as dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,4)="'.$year.'" GROUP BY dt');
						getVals($qRecoupment,$arrRecoupment);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<div align="right">
								<?php
								$recoupment=isset($arrRecoupment[$monthIncome]) ? $arrRecoupment[$monthIncome] : 0;
								$totalRecoupment += $recoupment;
								$arrNetBilled[$monthIncome] +=  $recoupment;
								if($recoupment){
								?>
								<a id="recoupment<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-recoup.php?mnth=<?php echo functions::encode($monthIncome);?>','RECOUPMENT','1')"><?php echo functions::formatMoney($recoupment);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRecoupment);?></div></td>
					</tr>
					<tr>
						<td><strong>Net Billed Amount</strong></td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								$totalDeductions += $arrNetBilled[$monthIncome];
								#$qCostIncome = $db->query('SELECT sum(pi.amount) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
								#$costIncome = $db->result($qCostIncome);
								$costIncome=isset($arrCostIncome[$monthIncome]) ? $arrCostIncome[$monthIncome] : 0;
								$totalNetBilled += $costIncome - $arrNetBilled[$monthIncome];
								echo functions::formatMoney($costIncome - $arrNetBilled[$monthIncome]);
								?>
								</strong>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft">
							<div align="right">
								<strong><?php echo functions::formatMoney($totalNetBilled);?></strong>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>Regular Deposit</td>
						<?php
						$arrRegDep=array();
						$qCostRegDep = $db->query('SELECT sum(as_amount) as amnt, left(as_date,7) as dt FROM account_statement WHERE transaction_type="deposit" AND as_type="credit" AND left(as_date,4)="'.$year.'" GROUP BY dt');
						getVals($qCostRegDep,$arrRegDep);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$costRegDep=isset($arrRegDep[$monthIncome]) ? $arrRegDep[$monthIncome] : 0;
							$totalRegularDeposit += $costRegDep;
							$totalNetBilled += $costRegDep;
							if($costRegDep){
							?>
							<div align="right">
								<a id="regDep<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('deposit');?>','Regular Deposit Details','1')">
									<?php echo functions::formatMoney($costRegDep);?>
								</a>
							</div>
							<?php } ?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRegularDeposit);?></div></td>
					</tr>
					<tr>
						<td>Loan Proceeds</td>
						<?php
						$arrLoan = array();
						$qCostLoan = $db->query('SELECT sum(as_amount) as amnt, left(as_date,7) as dt FROM account_statement WHERE description="LOAN PROCEEDS" AND left(as_date,4)="'.$year.'" GROUP BY dt');
						getVals($qCostLoan,$arrLoan);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$costLoan=isset($arrLoan[$monthIncome]) ? $arrLoan[$monthIncome] : 0;
							$totalLoan += $costLoan;
							$totalNetBilled += $costLoan;
							if($costLoan){
							?>
							<div align="right">
								<a id="loan<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('loan proceeds');?>','Loan Proceeds Transaction','1')">
									<?php echo functions::formatMoney($costLoan);?>
								</a>
							</div>
							<?php } ?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalLoan);?></div></td>
					</tr>
					<tr>
						<td>Cash Return</td>
						<?php
						$totalCashRet=0;
						$arrCashRet=array();
						$qCostCashRet = $db->query('SELECT sum(as_amount) as amnt, left(as_date,7) as dt FROM account_statement WHERE transaction_type="cash return" AND left(as_date,4)="'.$year.'" AND confirmned="2" GROUP BY dt');
						getVals($qCostCashRet,$arrCashRet);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$costCashRet=isset($arrCashRet[$monthIncome]) ? $arrCashRet[$monthIncome] : 0;
							$totalCashRet += $costCashRet;
							$totalNetBilled += $costCashRet;
							if($costCashRet){
							?>
							<div align="right">
								<a id="cashRet<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('cash return');?>','Cash Return Transaction','1')">
									<?php echo functions::formatMoney($costCashRet);?>
								</a>
							</div>
							<?php } ?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCashRet);?></div></td>
					</tr>
					<tr>
						<td>Stock Share</td>
						<?php
						$totalStockShare=0;
						$arrStockShare=array();
						$qCostStockShare = $db->query('SELECT sum(as_amount) as amnt, left(as_date,7) as dt FROM account_statement WHERE transaction_type="stock share" AND left(as_date,4)="'.$year.'" GROUP BY dt');
						getVals($qCostStockShare,$arrStockShare);
						foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$costStockShare=isset($arrStockShare[$monthIncome]) ? $arrStockShare[$monthIncome] : 0;
							$totalStockShare += $costStockShare;
							$totalNetBilled += $costStockShare;
							if($costStockShare){
							?>
							<div align="right">
								<a id="stockShare<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('stock share');?>','Stock Holders','1')">
									<?php echo functions::formatMoney($costStockShare);?>
								</a>
							</div>
							<?php } ?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalStockShare);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrMonth) + 2?>"><strong>Other Income</strong></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;In-House (Outside)</td>
						<?php
						$arrInHouseOutside=array();
						$qInhouseMaterialOther = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi WHERE im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND im.payee != "" AND is_paid="2" GROUP BY dt');
						getVals($qInhouseMaterialOther,$arrInHouseOutside);

						$qInhouseRentalOther = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as amnt, left(iel_date,7) as dt FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND left(iel_date,4)="'.$year.'" AND iel.payee != "" AND is_paid="2" GROUP BY dt');
						getVals($qInhouseRentalOther,$arrInHouseOutside);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costOtherIncome=isset($arrInHouseOutside[$month]) ? $arrInHouseOutside[$month] : 0;
								$totalOtherIncome += $costOtherIncome;
								$totalNetBilled += $costOtherIncome;
								if($costOtherIncome){
								?>
								<a id="materialOUT<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail-inhouse-other.php?mnth=<?php echo functions::encode($month);?>&ih=<?php echo functions::encode("materials");?>','MATERIAL IN-HOUSE','1')"><?php echo functions::formatMoney($costOtherIncome);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft">
							<div align="right"><?php echo functions::formatMoney($totalOtherIncome);?></div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrMonth) + 2?>">Less: Direct Cost</td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Labor</td>
						<?php
						$arrLabor=array();
						$qVoucherLabor = $db->query('SELECT sum(amount) as amnt, left(vd_date,7) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,4)="'.$year.'" AND id.expense_type="labor" GROUP BY dt');
						getVals($qVoucherLabor,$arrLabor);

						$qPOLabor = $db->query('SELECT round(sum(cost),2) as amnt,left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.expense_type="labor" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOLabor,$arrLabor);

						$qInhouseMaterialLabor = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.expense_type="labor" GROUP BY dt');
						getVals($qInhouseMaterialLabor,$arrLabor);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costLabor=isset($arrLabor[$month]) ? $arrLabor[$month] : 0;
								$totalLabor += $costLabor;
								$arrDirectCost[$month] += $costLabor;
								if($costLabor){
								?>
								<a id="labor<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("labor");?>','LABOR','1')"><?php echo functions::formatMoney($costLabor);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft">
							<div align="right"><?php echo functions::formatMoney($totalLabor);?></div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Materials</td>
						<?php
						$arrMaterials=array();
						$qVoucherMaterial = $db->query('SELECT sum(amount) as amnt, left(vd_date,7) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,4)="'.$year.'" AND id.expense_type="materials" GROUP BY dt');
						getVals($qVoucherMaterial,$arrMaterials);

						$qPO = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.expense_type="materials" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPO,$arrMaterials);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costMaterial=isset($arrMaterials[$month]) ? $arrMaterials[$month] : 0;
								$totalMaterial += $costMaterial;
								$arrDirectCost[$month] += $costMaterial;
								if($costMaterial){
								?>
								<a id="material<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("materials");?>&ih=<?php echo functions::encode("material");?>','MATERIAL','1')"><?php echo functions::formatMoney($costMaterial);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalMaterial);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Materials (In House)</td>
						<?php
						$arrInhouseMaterial=array();
						$qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.expense_type="materials" GROUP BY dt');
						getVals($qInhouseMaterial,$arrInhouseMaterial);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costMaterialIH=isset($arrInhouseMaterial[$month]) ? $arrInhouseMaterial[$month] : 0;
								$totalMaterialIH += $costMaterialIH;
								$arrDirectCost[$month] += $costMaterialIH;
								if($costMaterialIH){
								?>
								<a id="materialIH<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail-inhouse.php?mnth=<?php echo functions::encode($month);?>&ih=<?php echo functions::encode("materials");?>','MATERIAL IN-HOUSE','1')"><?php echo functions::formatMoney($costMaterialIH);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalMaterialIH);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Equipment</td>
						<?php
						$arrEquipment=array();
						$qVoucherEquipment = $db->query('SELECT sum(amount) as amnt, left(vd_date,7) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,4)="'.$year.'" AND id.expense_type="equipment" GROUP BY dt');
						getVals($qVoucherEquipment,$arrEquipment);

						$qPOEquip = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.expense_type="equipment" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOEquip,$arrEquipment);

						$qInhouseMaterialEquipment = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.expense_type="equipment" GROUP BY dt');
						getVals($qInhouseMaterialEquipment,$arrEquipment);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costEquipment=isset($arrEquipment[$month]) ? $arrEquipment[$month] : 0;
								$totalEquipment += $costEquipment;
								$arrDirectCost[$month] += $costEquipment;
								if($costEquipment){
								?>
								<a id="equiprental<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("equipment");?>&ih=<?php echo functions::encode("equipment");?>','EQUIPMENT RENTAL','1')"><?php echo functions::formatMoney($costEquipment);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEquipment);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Equipment Rental (In-House)</td>
						<?php
						$arrInhouseRental=array();
						$qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as amnt, left(iel_date,7) as dt FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND left(iel_date,4)="'.$year.'" GROUP BY dt');
						getVals($qInhouseRental,$arrInhouseRental);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costEquipmentIH=isset($arrInhouseRental[$month]) ? $arrInhouseRental[$month] : 0;
								$totalEquipmentIH += $costEquipmentIH;
								$arrDirectCost[$month] += $costEquipmentIH;
								if($costEquipmentIH){
								?>
								<a id="equipmentIH<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail-inhouse.php?mnth=<?php echo functions::encode($month);?>&ih=<?php echo functions::encode("equipment");?>','EQUIPMENT RENTAL IN-HOUSE','1')"><?php echo functions::formatMoney($costEquipmentIH);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEquipmentIH);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Commitment</td>
						<?php
						$arrCommitment=array();
						$qCommitment = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,7) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,4)="'.$year.'" AND itd.cost_type="commitment" GROUP BY dt');
						getVals($qCommitment,$arrCommitment);

						$qPOCommit = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.cost_type="commitment" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOCommit,$arrCommitment);

						$qInhouseMaterialCommit = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.cost_type="commitment" GROUP BY dt');
						getVals($qInhouseMaterialCommit,$arrCommitment);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costCommitment=isset($arrCommitment[$month]) ? $arrCommitment[$month] : 0;
								$totalCommitment += $costCommitment;
								$arrDirectCost[$month] += $costCommitment;
								if($costCommitment){
								?>
								<a id="commitment<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("commitment");?>','COMMITMENT','1')"><?php echo functions::formatMoney($costCommitment);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCommitment);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Finder's Fee</td>
						<?php
						$arrFinder=array();
						$qFinder = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,7) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,4)="'.$year.'" AND itd.cost_type="finders fee" GROUP BY dt');
						getVals($qFinder,$arrFinder);

						$qPOFinder = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.cost_type="finders fee" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOFinder,$arrFinder);

						$qInhouseMaterialFinder = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.cost_type="finders fee" GROUP BY dt');
						getVals($qInhouseMaterialFinder,$arrFinder);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costFinder=isset($arrFinder[$month]) ? $arrFinder[$month] : 0;
								$totalFinder += $costFinder;
								$arrDirectCost[$month] += $costFinder;
								if($costFinder){
								?>
								<a id="findersfee<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("finders fee");?>','FINDER\'S FEE','1')"><?php echo functions::formatMoney($costFinder);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalFinder);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Consultancy</td>
						<?php
						$arrConsultancy=array();
						$qConsultancy = $db->query('SELECT round(sum(amount),2) as amnt,left(vd_date,7) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,4)="'.$year.'" AND itd.cost_type="consultancy" GROUP BY dt');
						getVals($qConsultancy,$arrConsultancy);

						$qPOConsultancy = $db->query('SELECT round(sum(cost),2) as amnt,left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.cost_type="consultancy" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOConsultancy,$arrConsultancy);

						$qInhouseMaterialConsultancy = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt,left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.cost_type="consultancy" GROUP BY dt');
						getVals($qInhouseMaterialConsultancy,$arrConsultancy);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costConsultancy=isset($arrConsultancy[$month]) ? $arrConsultancy[$month] : 0;
								$totalConsultancy += $costConsultancy;
								$arrDirectCost[$month] += $costConsultancy;
								if($costConsultancy){
								?>
								<a id="consultancy<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("consultancy");?>','CONSULTANCY','1')"><?php echo functions::formatMoney($costConsultancy);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConsultancy);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Technical Fee</td>
						<?php
						$arrTechnicalFee=array();
						$qTechnical = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,7) as dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,4)="'.$year.'" AND itd.cost_type="technical fee" GROUP BY dt');
						getVals($qTechnical,$arrTechnicalFee);

						$qPOTechnical = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.cost_type="technical fee" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOTechnical,$arrTechnicalFee);

						$qInhouseMaterialTechnical = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.cost_type="technical fee" GROUP BY dt');
						getVals($qInhouseMaterialTechnical,$arrTechnicalFee);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costTechnical=isset($arrTechnicalFee[$month]) ? $arrTechnicalFee[$month] : 0;
								$totalTechnical += $costTechnical;
								$arrDirectCost[$month] += $costTechnical;
								if($costTechnical){
								?>
								<a id="technical<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("technical fee");?>','TECHNICAL FEE','1')"><?php echo functions::formatMoney($costTechnical);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalTechnical);?></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Subcon</td>
						<?php
						$arrSubcon=array();
						$qVoucherSubcon = $db->query('SELECT sum(amount) as amnt, left(vd_date,7) as dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,4)="'.$year.'" AND id.expense_type="subcon" GROUP BY dt');
						getVals($qVoucherSubcon,$arrSubcon);

						$qPOSubcon = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,7) as dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,4)="'.$year.'" AND itd.expense_type="subcon" AND vpp.paid > 0 GROUP BY dt');
						getVals($qPOSubcon,$arrSubcon);

						$qInhouseMaterialSubcon = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,7) as dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$year.'" AND itd.expense_type="subcon" GROUP BY dt');
						getVals($qInhouseMaterialSubcon,$arrSubcon);
						foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costSubcon=isset($arrSubcon[$month]) ? $arrSubcon[$month] : 0;
								$totalSubcon += $costSubcon;
								$arrDirectCost[$month] += $costSubcon;
								if($costSubcon){
								?>
								<a id="subcon<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("subcon");?>','SUBCON','1')"><?php echo functions::formatMoney($costSubcon);?></a>
								<?php } ?>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalSubcon);?></div></td>
					</tr>
					<tr>
						<td><strong>Total Direct Cost</strong></td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								$totalDirectCost += $arrDirectCost[$month];
								echo functions::formatMoney($arrDirectCost[$month]);
								?>
								</strong>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalDirectCost);?></strong></div></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrMonth) + 2?>">Less: Operating Expenses</td>
					</tr>
					<?php
					$arrCategory = array();
					$arrCategoryName = array();
					$overVoucher = $db->query('SELECT category_id, itd.name as nme FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" AND left(vd.vd_date,4)="'.$year.'" GROUP BY category_id, itd.name ORDER BY itd.name');
					while($rOverVoucher = $db->fetch_array($overVoucher)):
						$arrCategoryName[$rOverVoucher['category_id']]=$rOverVoucher['nme'];
					endwhile;

					$overPO = $db->query('SELECT category_id, itd.name as nme FROM po p, view_po_payment vpp, item_deduction itd WHERE p.category_id=itd.item_id AND p.po_id=vpp.po_id AND paid > 0 AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" AND left(p.po_date,4)="'.$year.'" GROUP BY category_id, itd.name ORDER BY itd.name');
					while($rOverPO = $db->fetch_array($overPO)):
						$arrCategoryName[$rOverPO['category_id']]=$rOverPO['nme'];
					endwhile;

					$overInhouseMaterial = $db->query('SELECT category_id, itd.name as nme FROM inhouse_material im, item_deduction itd WHERE im.category_id=itd.item_id AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" AND left(im_date,4)="'.$year.'" GROUP BY category_id, itd.name ORDER BY itd.name');
					while($rOverInhouseMaterial = $db->fetch_array($overInhouseMaterial)):
						$arrCategoryName[$rOverInhouseMaterial['category_id']]=$rOverInhouseMaterial['nme'];
					endwhile;
					asort($arrCategoryName);

					$q1 = $db->query('SELECT left(vd_date,7) as dt, vd.category_id as catid, round(sum(amount),2) as amnt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,4)="'.$db->clean($year).'" GROUP BY dt,vd.category_id ORDER BY itd.name');
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

					$q2 = $db->query('SELECT left(p.po_date,7) as dt, p.category_id as catid, round(sum(cost),2) as amnt FROM po p, view_po_payment vpp WHERE p.po_id=vpp.po_id AND left(p.po_date,4)="'.$db->clean($year).'" AND vpp.paid > 0 GROUP BY dt,p.category_id');
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

					$q3 = $db->query('SELECT left(im_date,7) as dt, im.category_id as catid, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,4)="'.$db->clean($year).'" GROUP BY dt, im.category_id,itd.name');
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
						$totalCategoryCost=0;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;<?php echo $categoryName; #echo $catname = isset($arrCategoryName[$categoryID]) ? $arrCategoryName[$categoryID] : '';#echo $db->getValue('item_deduction','name',array('item_id'=>$categoryID));?></td>
						<?php foreach($arrMonth as $month):
						$countID++;
						if( !isset($arrOverheadCost[$month]) )
							$arrOverheadCost[$month]=0;
						?>
						<td class="padleft">
							<div align="right">
								<?php
								$categoryCost=isset($arrCategory[$categoryID][$month]) ? $arrCategory[$categoryID][$month] : 0;
								$arrOverheadCost[$month] += $categoryCost;
								$totalCategoryCost += $categoryCost;
								$totalOverheadCost += $categoryCost;
								$totalOperatingExpenses += $categoryCost;
								if ($categoryCost){
								?>
								<a id="cat<?php echo $countID++?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-oper-exp.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode($categoryID);?>','<?php echo $categoryName?>','1')"><?php echo functions::formatMoney($categoryCost);?></a>
								<?php } ?>
							</div>
						</td>
							<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCategoryCost);?></div></td>
					</tr>
					<?php endforeach;?>
					<tr>
						<td><strong>Total Operating Expenses</strong></td>
						<?php foreach($arrOverheadCost as $ohCost):?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($ohCost);?></strong></div></td>
						<?php endforeach;
						if(count($arrOverheadCost)==0){
							foreach($arrMonth as $monthIncome):
							echo '<td class="padleft"><div align="right"><strong>0.00</strong></div></td>';
							endforeach;
						}
						?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalOverheadCost);?></strong></div></td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrMonth) + 1?>"><div align="right"><strong>Total Expenses&nbsp;&nbsp;</strong></div></td>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								$totalExpenses = $totalDirectCost + $totalOperatingExpenses;
								echo functions::formatMoney($totalExpenses);
								?>
								</strong>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrMonth) + 1?>"><div align="right"><strong>Net Income&nbsp;&nbsp;</strong></div></td>
						<td class="padleft">
							<div align="right">
								<strong>
								<?php
								$netIncome = ($totalNetBilled) - $totalExpenses;
								echo functions::formatMoney($netIncome);
								?>
								</strong>
							</div>
						</td>
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