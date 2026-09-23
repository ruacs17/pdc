<?php require_once('templ_up.php');?>
<?php
$arrYear=array();
#$arrYear=array(2017,2018);
#
for($iYr=2014; $iYr<=date('Y'); $iYr++):
	$arrYear[]=$iYr;
endfor;
#$arrYear=array(2013,2014,2015,2016);
$arrCash=array();


//Current Assets
$arrTotalAsset=array();
$arrTotalCurrentAsset=array();
$ca_cash=array();
$ca_receivable=array();
$ca_prepaid=array();
$ca_investment=array();
//Non-Current Assets
$arrTotalNonAsset=array();
$arrTotalNonCurrentAsset=array();
$nca_property_equipment=array();
$nc_other_nca=array();

//Liabalities and Stockholders' Equity
//Current Liabilities
$cl_accounts_payable=array();
$cl_accrued_expenses=array();
$cl_unearned_construction_income=array();
$cl_income_tax_payable=array();

//Longterm Liabilities
$ll_loan=array();
$ll_other_longterm_payable=array();

//Stockholders' Equity
$se_paid_up=array();
$se_retained_earnings=array();

$arrIncomeTax=array();
$arrCurrentLiability=array();
$arrLongtermLiability=array();
$arrLoan=array();
$arrTotalLiability=array();


$arrStockHolderEquity=array();


//STATEMENT OF CHANGES IN STOCKHOLDERS' EQUITY
$scse_paid_up=array();
$scse_treasury_stocks=array();
$arr_retained_earnings_balance_jan1=array();$retained_future_expansion=array();$retained_earning_free=array();$net_income_for_the_period=array();$total_free_retained_earning=array();
$stock_dividend=array();$cash_dividend=array();$total_dividend=array();$free_retained_earnings_dec_31=array();$total_retained_earnings_dec_31=array();$balance_at_dec_31=array();



//STATEMENT OF CASH FLOWS
$cf_net_income_before_income_tax=array();
$cf_oper_income=array(); $cf_depreciation=array(); $cf_receivable=array(); $cf_prepaid_expenses=array(); $cf_longterm_investment=array();
$cf_accounts_payable=array(); $cf_accrued_expenses=array();
$cf_unearned_construction_income=array(); $cf_bank_loan_payment=array();$cf_other_longterm=array();
$cf_cash_gen_from_oper=array();$cf_income_tax_paid=array();$cf_net_cash_used_oper_act=array(); $cf_acquisition_prop_equip=array(); $cf_increase_other_assets=array(); $cf_net_cash_used_financing_act=array();
$cf_dividends=array();$cf_acquisition_treasury_share=array(); $cf_increase_paid_up_capital=array(); $cf_proceeds_from_loan=array();
$cf_net_cash_flow_financing=array();
$cf_net_increase_decrease_cash=array();
$cf_cash_beginning=array();
$cf_cash_dec31=array();

$arrCategory=array();
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

function getVal($query,&$arr){
	global $db;
	while($r = $db->fetch_array($query)):
		if( isset($arr[$r['dt']]) )
			$arr[$r['dt']]+=$r['amnt'];
		else
			$arr[$r['dt']]=$r['amnt'];
	endwhile;
}
$arrCostOfConstructionIncome=array();$arrFSnetIncome=array();$arrProvisionIncomeTax=array();$arrIncomeBeforeIncomeTax=array();$arrGrossProfit=array();$arrConstructionIncome=array();$arrGrossBilled = array(); $arrNetBilled=array(); $arrDirectCost=array(); $arrDeduction=array(); $arrNetIncome=array();

$qCostIncome = $db->query('SELECT sum(pi.amount) as amnt, left(pi_date,4) dt  FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
getVal($qCostIncome,$arrGrossBilled);

$qVat = $db->query('SELECT sum(pi.vat) as amnt, left(pi_date,4) dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
getVal($qVat,$arrDeduction);

$qEWT = $db->query('SELECT sum(pi.ewt) as amnt, left(pi_date,4) dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
getVal($qEWT,$arrDeduction);

$qContractor = $db->query('SELECT sum(pi.contractor) as amnt, left(pi_date,4) dt FROM project p, project_income pi WHERE p.proj_id=pi.proj_id GROUP BY dt');
getVal($qContractor,$arrDeduction);

$qCostLoan = $db->query('SELECT sum(as_amount) as amnt, left(as_date,4) dt FROM account_statement WHERE description="LOAN PROCEEDS" GROUP BY dt');
getVal($qCostLoan,$arrGrossBilled);

$qInhouseMaterialOther = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi WHERE im.im_id=imi.im_id AND im.payee != "" GROUP BY dt');
getVal($qInhouseMaterialOther,$arrGrossBilled);

$qInhouseRentalOther = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as amnt, left(iel_date,4) dt FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND iel.payee != "" GROUP BY dt');
getVal($qInhouseRentalOther,$arrGrossBilled);

$qVoucherLabor = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="labor" GROUP BY dt');
getVal($qVoucherLabor,$arrDirectCost);

$qPOLabor = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="labor" AND vpp.paid > 0 GROUP BY dt');
getVal($qPOLabor,$arrDirectCost);

$qInhouseMaterialLabor = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.expense_type="labor" GROUP BY dt');
getVal($qInhouseMaterialLabor,$arrDirectCost);

$qVoucherMaterial = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="materials" GROUP BY dt');
getVal($qVoucherMaterial,$arrDirectCost);

$qPO = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="materials" AND vpp.paid > 0 GROUP BY dt');
getVal($qPO,$arrDirectCost);

$qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.expense_type="materials" GROUP BY dt');
getVal($qInhouseMaterial,$arrDirectCost);

$qVoucherEquipment = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="equipment" GROUP BY dt');
getVal($qVoucherEquipment,$arrDirectCost);

$qPOEquip = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="equipment" AND vpp.paid > 0 GROUP BY dt');
getVal($qPOEquip,$arrDirectCost);

$qInhouseMaterialEquipment = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.expense_type="equipment" GROUP BY dt');
getVal($qInhouseMaterialEquipment,$arrDirectCost);

$qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as amnt, left(iel_date,4) dt FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id GROUP BY dt');
getVal($qInhouseRental,$arrDirectCost);

$qCommitment = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="commitment" GROUP BY dt');
getVal($qCommitment,$arrDirectCost);

$qPOCommit = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="commitment" AND vpp.paid > 0 GROUP BY dt');
getVal($qPOCommit,$arrDirectCost);

$qInhouseMaterialCommit = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="commitment" GROUP BY dt');
getVal($qInhouseMaterialCommit,$arrDirectCost);

$qFinder = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="finders fee" GROUP BY dt');
getVal($qFinder,$arrDirectCost);

$qPOFinder = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="finders fee" AND vpp.paid > 0 GROUP BY dt');
getVal($qPOFinder,$arrDirectCost);

$qInhouseMaterialFinder = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="finders fee" GROUP BY dt');
getVal($qInhouseMaterialFinder,$arrDirectCost);

$qConsultancy = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="consultancy" GROUP BY dt');
getVal($qConsultancy,$arrDirectCost);

$qPOConsultancy = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="consultancy" AND vpp.paid > 0 GROUP BY dt');
getVal($qPOConsultancy,$arrDirectCost);

$qInhouseMaterialConsultancy = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="consultancy" GROUP BY dt');
getVal($qInhouseMaterialConsultancy,$arrDirectCost);

$qTechnical = $db->query('SELECT round(sum(amount),2) as amnt, left(vd_date,4) dt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.cost_type="technical fee" GROUP BY dt');
getVal($qTechnical,$arrDirectCost);

$qPOTechnical = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.cost_type="technical fee" AND vpp.paid > 0 GROUP BY dt');
getVal($qPOTechnical,$arrDirectCost);

$qInhouseMaterialTechnical = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.cost_type="technical fee" GROUP BY dt');
getVal($qInhouseMaterialTechnical,$arrDirectCost);

$qVoucherSubcon = $db->query('SELECT sum(amount) as amnt, left(vd_date,4) dt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND id.expense_type="subcon" GROUP BY dt');
getVal($qVoucherSubcon,$arrDirectCost);

$qPOSubcon = $db->query('SELECT round(sum(cost),2) as amnt, left(p.po_date,4) dt FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND itd.expense_type="subcon" AND vpp.paid > 0 GROUP BY dt');
getVal($qPOSubcon,$arrDirectCost);

$qInhouseMaterialSubcon = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt, left(im_date,4) dt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND itd.expense_type="subcon" GROUP BY dt');
getVal($qInhouseMaterialSubcon,$arrDirectCost);


$qGrossBill = $db->query('SELECT sum(amount/1.12) as amnt, left(pi_date,4) dt FROM project_income WHERE (vat <> "" OR ewt <> "") GROUP BY dt');
getVal($qGrossBill,$arrConstructionIncome);

foreach($arrYear as $perYear):

	$arrNetBilled[$perYear]=0;
	$arrNetIncome[$perYear]=0;
	$arrCostOfConstructionIncome[$perYear]=0;

	if( !isset($arrGrossBilled[$perYear]) )
		$arrGrossBilled[$perYear]=0;

	if( !isset($arrDeduction[$perYear]) )
		$arrDeduction[$perYear]=0;

	if( !isset($arrDirectCost[$perYear]) )
		$arrDirectCost[$perYear]=0;

	if( !isset($arrConstructionIncome[$perYear]) )
		$arrConstructionIncome[$perYear]=0;

	$arrNetBilled[$perYear] = $arrGrossBilled[$perYear] - $arrDeduction[$perYear];

	//End Direct Cost 

	//Operating Expenses
	$arrOverheadCost=array();
	$prevTotalOperatingExpenses = 0;
	$arrOperatingExpense=array();
	$countID=0;

	foreach( $arrCategoryName as $categoryID => $categoryName ):
		if( !isset($arrOverheadCost[$perYear]) ){
			$arrOverheadCost[$perYear]=0;
			$arrOperatingExpenses[$perYear]=0;
		}
		$categoryCost=isset($arrCategory[$categoryID][$perYear]) ? $arrCategory[$categoryID][$perYear] : 0;
		$arrOverheadCost[$perYear] += $categoryCost;
		$arrOperatingExpenses[$perYear] += $categoryCost;
	endforeach;
	//End of Operating Expenses

	//Net Income
	$arrNetIncome[$perYear] = $arrGrossBilled[$perYear] - ($arrDeduction[$perYear] + $arrDirectCost[$perYear] + $arrOperatingExpenses[$perYear]);
	//End Net Income
	$arrCostOfConstructionIncome[$perYear] = $arrDeduction[$perYear] + $arrDirectCost[$perYear];
	$arrGrossProfit[$perYear] = $arrConstructionIncome[$perYear] - $arrCostOfConstructionIncome[$perYear];


	$arrIncomeBeforeIncomeTax[$perYear] = $arrGrossProfit[$perYear] - $arrOperatingExpenses[$perYear];
	$arrProvisionIncomeTax[$perYear] = $arrIncomeBeforeIncomeTax[$perYear] * .3;
	$arrFSnetIncome[$perYear] = $arrIncomeBeforeIncomeTax[$perYear] - $arrProvisionIncomeTax[$perYear];
endforeach;


$arrCostYearStart=array();
function costYearStart($year){
	global $db;
	$allDep =0;
	$q = $db->query('SELECT (price*quantity) as cost, substring(proposed_life,1,4)-substring(date_acquired,1,4) as prposedYr, substring(date_acquired,1,4) as yrAcqrd, substring(proposed_life,1,4) as yrExpyrd from equipment WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" AND price <> "" HAVING yrAcqrd <= "'.($year-1).'" and yrExpyrd > "'.$year.'"');
	while($r = $db->fetch_array($q)):
		$allDep += $r['cost'];  
	endwhile;
	return $allDep;
}

function depreciationYearStart($year){
	global $db;
	$allDep =0;
	$q = $db->query('SELECT (price*quantity) / (substring(proposed_life,1,4) - substring(date_acquired,1,4) ) as depPerYr, substring(proposed_life,1,4)-substring(date_acquired,1,4) as prposedYr, substring(date_acquired,1,4) as yrAcqrd, substring(proposed_life,1,4) as yrExpyrd from equipment WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" AND price <> "" HAVING yrAcqrd <= "'.($year-1).'" and yrExpyrd >= "'.($year-1).'"');
	while($r = $db->fetch_array($q)):
		$count=0;
		for($i=$r['yrAcqrd']; $i<$r['yrExpyrd']; $i++):
			$count++;
			if($i==$year){
				$allDep += $count * $r['depPerYr'];
			}
		endfor;
	endwhile;
	return $allDep;
}

function depreciationAll($year){
	global $db;
	$allDep =0;
	$q = $db->query('SELECT (price*quantity) / (substring(proposed_life,1,4) - substring(date_acquired,1,4) ) as depPerYr, substring(proposed_life,1,4)-substring(date_acquired,1,4) as prposedYr, substring(date_acquired,1,4) as yrAcqrd, substring(proposed_life,1,4) as yrExpyrd from equipment WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" AND price <> "" HAVING yrAcqrd <= "'.$year.'" and yrExpyrd >= "'.$year.'"');
	while($r = $db->fetch_array($q)):
		$count=0;
		for($i=$r['yrAcqrd']; $i<$r['yrExpyrd']; $i++):
			$count++;
			if($i==$year){
				$allDep += $count * $r['depPerYr'];
			}
		endfor;
	endwhile;
	return $allDep;
}

function depreciationCurrent($year){
	global $db;
	$allDep =0;
	$q = $db->query('SELECT (price*quantity) / (substring(proposed_life,1,4) - substring(date_acquired,1,4) ) as depPerYr, substring(proposed_life,1,4)-substring(date_acquired,1,4) as prposedYr, substring(date_acquired,1,4) as yrAcqrd, substring(proposed_life,1,4) as yrExpyrd from equipment WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" AND price <> "" HAVING yrAcqrd = "'.$year.'"');
	while($r = $db->fetch_array($q)):
		$count=0;
		for($i=$r['yrAcqrd']; $i<$r['yrExpyrd']; $i++):
			$count++;
			if($i==$year){
				$allDep += $count * $r['depPerYr'];
			}
		endfor;
	endwhile;
	return $allDep;
}

function accrued_expenses($year){
	global $db;

	$allRecoupment=0;
	$qProj = $db->select('project','*',array('project'=>1,'left(date_start,4)'=>$year),'ORDER BY proj_name');
	while( $rProj = $db->fetch_array($qProj)):
		$qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND lower(name) != "advance payment" AND  lower(name) != "retention payment" ORDER BY pi_date ASC');
		$projRecoupment=0;
		while($rPI = $db->fetch_array($qPI)):
			if($rPI['pi_date'])
				$projRecoupment += $rPI['recoupment'];
		endwhile;
		$advance_payment = 0;
		$qPI = $db->select('project_income','*',array('proj_id'=>$rProj['proj_id']),' AND (lower(name) = "advance payment" OR lower(name) = "retention payment") ORDER BY pi_date ASC');
		while($rPI = $db->fetch_array($qPI)):
			$advance_payment += ($rPI['name']=="Advance Payment") ? $rPI['amount'] : 0;
		endwhile;//while($rPI = $db->fetch_array($qPI)):
		if($advance_payment)
			$allRecoupment += $advance_payment-$projRecoupment;
	endwhile;
	return $allRecoupment;
}

$jan1Dep=0;
$dec31Dep=0;
$yrStart = $db->getValue('equipment','substring(date_acquired,1,4) as yr',array(),'WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" ORDER BY date_acquired ASC LIMIT 1');
$yrEnd = $db->getValue('equipment','substring(proposed_life,1,4) as yr',array(),'WHERE classification <> "" AND proposed_life <> "" AND proposed_life <> "0000-00-00" ORDER BY proposed_life DESC LIMIT 1');

$arrAdditions=array();
$qAdditions = $db->query('SELECT sum(price * quantity) as amnt, left(date_acquired,4) as dt FROM equipment WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" GROUP BY dt');
getVal($qAdditions,$arrAdditions);
for($i=$yrStart; $i<=$yrEnd; $i++):
	$equipYr=$i;
	$jan1 = costYearStart($equipYr);
	#$additions = $db->getValue('equipment','sum(price * quantity)',array(),'WHERE substring(date_acquired,1,4) = "'.$equipYr.'" AND classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00"');
	$additions = isset($arrAdditions[$equipYr]) ? $arrAdditions[$equipYr] : 0;
	$dec31 = $jan1 + $additions;
	$jan1Dep = depreciationYearStart($equipYr);
	$additionDepreciation = depreciationCurrent($equipYr);
	$dec31Dep = $jan1Dep + $additionDepreciation;
	$netValue = $dec31 - $dec31Dep;
	$arr_fs[$i] = array('year'=>$equipYr,'jan1'=>$jan1,'additions'=>$additions,'dec31'=>$dec31,'jan1Dep'=>$jan1Dep,'additionDepreciation'=>$additionDepreciation,'dec31Dep'=>$dec31Dep,'netValue'=>$netValue);
	$jan1Dep += $additionDepreciation;
endfor;

foreach($arrYear as $yr):
	$totalWitholding=0;
	$qVoucher = $db->select('voucher v, supplier s','s.supplierID,sum(witholding_tax) as wt',array('LEFT(cheque_date,4)'=>$yr),'AND v.supplierID=s.supplierID GROUP BY v.supplierID HAVING wt > 0 ORDER BY s.name');
	$total=0;$witholding_tax=0;$advance_payment=0;$voucher_advance_payment=0;
	while($rVoucher = $db->fetch_array($qVoucher)):
		$supplierWitholdingTax=0;
		$qTaxCompute = $db->select('voucher','*',array('LEFT(cheque_date,4)'=>$yr,'supplierID'=>$rVoucher['supplierID']));
		while($rTC = $db->fetch_array($qTaxCompute)):
			$amount_q = $db->query('SELECT SUM(vd.amount_issue)  FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rTC['voucher_id']).'"');
			$non_po = $db->result($amount_q);

			$amount_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rTC['voucher_id']).'"');
			$po = $db->result($amount_po_q);
			$amount = $non_po + $po;

			$wtax = ($rTC['witholding_tax'] > 0) ? ($rTC['witholding_tax'] / 100) : 0;

			$witholding = $wtax * ($amount / 1.12);

			if( isset($arrVoucherAdvanceID[$rTC['voucher_id']]) ){
				$va = new voucherAdvance($rTC['voucher_id']);
				$witholding = $va->current_voucher_withholding_tax_amount;
			}
			$supplierWitholdingTax += $witholding;
		endwhile;

		$witholding_tax = $supplierWitholdingTax;
		$totalWitholding += $witholding_tax;
	endwhile;
	$arrIncomeTax[$yr]=$totalWitholding;
endforeach;
#print_r($arrIncomeTax);
?>
<!-- body content: start here-->
<style>
	.grpPad{padding-left: 50px;}
</style>
<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left" style="visibility: hidden;">
	<tr>
		<td width="3%" style="visibility:hidden"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
		<td width="47%" style="visibility:hidden"> Inactive</td>
		<td width="50%"><div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'account_statement_add.php?t=<?php echo functions::encode($account_type)?>&y=<?php echo functions::encode($year)?>&m=<?php echo functions::encode($selMonth)?>','Statement Add')">Add New Statement</a></div></td>
	</tr>
</table><br><br>
<div class="row-fluid">
	<form method="post">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Financial Statement</h2>
			</div>
			<div class="box-content">
				<table class="table table-bordered table-hover table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<td>&nbsp;</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="center"><strong><?php echo $yr?></strong></div></td>
							<?php endforeach;?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><i>Current Assets</i></strong></td>
							<?php foreach($arrYear as $yr):?>
							<td>&nbsp;</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td class="grpPad" style="padding-left: 30px;">Cash</td>
							<?php foreach($arrYear as $yr):?>
							<td>
								<div align="right">
								<?php
								$cashYr = $yr + 1;
								$as_date = $cashYr.'-01-01';
								$cash = $db->getValue('account_statement','sum(as_amount)',array('as_date'=>$as_date,'transaction'=>'REMAINING BALANCE of '.$yr),'AND account_type<>""');
								$ca_cash[$yr]=$cash;
								$arrTotalCurrentAsset[$yr]=$cash;
								$arrTotalAsset[$yr]=$cash;
								echo functions::formatMoney($cash);
								?>
								</div>
							</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Receivable</td>
							<?php foreach($arrYear as $yr):?>
							<td>
								<div align="right">
								<?php
								$qCollectible = $db->query('SELECT sum(amount) as net FROM project_income WHERE left(submit_date,4)="'.$yr.'" AND pi_date IS NULL');
								#echo $db->last_query;
								$collectibleAmount = $db->result($qCollectible);
								$ca_receivable[$yr]=$collectibleAmount;
								$arrTotalCurrentAsset[$yr]+=$collectibleAmount;
								$arrTotalAsset[$yr]+=$collectibleAmount;
								echo functions::formatMoney($collectibleAmount);
								?>
								</div>
							</td>
							<?php endforeach;?>
						</tr>
						<tr>
						<td style="padding-left: 30px;">Prepaid Insurance (Philam)</td>
						<?php
						foreach($arrYear as $yr):
							$insurance = $db->getValue('account_fs','afs_value',array('afs_name'=>'insurance','afs_yr'=>$yr));
							$ca_prepaid[$yr]=$insurance;
							$arrTotalCurrentAsset[$yr]+=$insurance;
							$arrTotalAsset[$yr]+=$insurance;
						?>
						<td><div align="right"><a id="idInsurance<?php echo $yr?>" href="#" class="thickbox" onclick="showThis(this.id,'account_financial_statement_manage.php?t=<?php echo functions::encode('Prepaid Insurance (Philam)')?>&dbt=<?php echo functions::encode('insurance')?>&y=<?php echo functions::encode($yr)?>','Statement Add')"><?php echo functions::formatMoney($insurance)?></a></div></td>
						<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Investment (UITF Metrobank)</td>
							<?php
							foreach($arrYear as $yr):
								$investment_uitf = $db->getValue('account_fs','afs_value',array('afs_name'=>'investment_uitf','afs_yr'=>$yr));
								$ca_investment[$yr]=$investment_uitf;
								$arrTotalCurrentAsset[$yr]+=$investment_uitf;
								$arrTotalAsset[$yr]+=$investment_uitf;
							?>
							<td><div align="right"><a id="idInvestment<?php echo $yr?>" href="#" class="thickbox" onclick="showThis(this.id,'account_financial_statement_manage.php?t=<?php echo functions::encode('Investment (UITF Metrobank)')?>&dbt=<?php echo functions::encode('investment_uitf')?>&y=<?php echo functions::encode($yr)?>','Statement Add')"><?php echo functions::formatMoney($investment_uitf)?></a></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Total Current Assets</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($arrTotalCurrentAsset[$yr])?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td><strong><i>Non-Current Assests</i></strong></td>
							<?php foreach($arrYear as $yr):?>
							<td>&nbsp;</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Property and Equipment</td>
							<?php foreach($arrYear as $yr):?>
							<td>
								<div align="right">
								<?php
									$nca_property_equipment[$yr]=$arr_fs[$yr]['netValue'];
									$arrTotalAsset[$yr]+=$arr_fs[$yr]['netValue'];
									$arrTotalNonCurrentAsset[$yr]=$arr_fs[$yr]['netValue'];
									echo functions::formatMoney($arr_fs[$yr]['netValue']);
								?>
								</div>
							</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Other Non-Current Assets</td>
							<?php
							foreach($arrYear as $yr):
								$nonCurrentAsset = $db->getValue('account_fs','afs_value',array('afs_name'=>'non_current_asset','afs_yr'=>$yr));
								$nc_other_nca[$yr]=$nonCurrentAsset;
								$arrTotalAsset[$yr]+=$nonCurrentAsset;
								$arrTotalNonCurrentAsset[$yr]+=$nonCurrentAsset;
							?>
							<td><div align="right"><a id="idNonCurrentAssets<?php echo $yr?>" href="#" class="thickbox" onclick="showThis(this.id,'account_financial_statement_manage.php?t=<?php echo functions::encode('Non-Current Assets')?>&dbt=<?php echo functions::encode('non_current_asset')?>&y=<?php echo functions::encode($yr)?>','Statement Add')"><?php echo functions::formatMoney($nonCurrentAsset)?></a></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Total Non-current Assets</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($arrTotalNonCurrentAsset[$yr])?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td height="5" style="padding-right: 30px;"><div align="right"><strong>Total Assets</strong></div></td>
							<?php foreach($arrYear as $yr):?>
							<td valign="middle"><div align="right"><strong><?php echo functions::formatMoney($arrTotalAsset[$yr])?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td height="90" colspan="<?php echo count($arrYear) + 1?>">&nbsp;</td>
						</tr>
						<?php
						//STATEMENT OF CHANGES IN STOCKHOLDERS' EQUITY
						$arrCapitalStockPaidUp=array();
						$qCapitalStockPaidUp = $db->query('SELECT sum(as_amount) as amnt, left(as_date,4) as dt FROM account_statement WHERE transaction_type="stock share" GROUP BY dt');
						getVal($qCapitalStockPaidUp,$arrCapitalStockPaidUp);

						foreach($arrYear as $yr):
							//Paid-up
							#$capital_stock_paid_up = $db->getValue('account_statement','sum(as_amount)',array('left(as_date,4)'=>$yr,'transaction_type'=>'stock share'));
							$capital_stock_paid_up = isset($arrCapitalStockPaidUp[$yr]) ? $arrCapitalStockPaidUp[$yr] : 0; 
							$scse_paid_up[$yr]=$capital_stock_paid_up;

							//Treasury Stocks
							$scse_treasury_stocks[$yr]=0;

							//Balance at January 1
							$retained_earnings_balance_jan1[$yr] = isset($retained_earnings_balance_jan1[$yr]) ? $retained_earnings_balance_jan1[$yr] : 0;
							
							//Retained Appropriated for Future Expansion
							$afs_value = $db->getValue('account_fs','afs_value',array('afs_name'=>'retained_future','afs_yr'=>$yr));
							#$retained_future_expansion[$yr] = $db->getValue('account_fs','afs_value',array('afs_name'=>'retained_future','afs_yr'=>$yr));
							$retained_future_expansion[$yr] = isset($retained_future_expansion[($yr-1)]) ? $retained_future_expansion[($yr-1)] + $afs_value : $afs_value;
							#$retained_future_expansion[$yr] = $afs_value;
							//Retained Earnings Free
							$ref = $retained_earnings_balance_jan1[$yr] - $retained_future_expansion[$yr]; 
							$retained_earning_free[$yr] = isset($retained_earning_free[($yr+1)]) ? $retained_earning_free[($yr+1)] + $ref : $ref;
							//Net income for the period
							//$net_income_for_the_period[$yr] = $arrFSnetIncome[$yr];
							$net_income_for_the_period[$yr] = isset($net_income_for_the_period[($yr-1)]) ? $net_income_for_the_period[($yr-1)] + $arrFSnetIncome[$yr] : $arrFSnetIncome[$yr];
							//Total Free retained earnings
							$total_free_retained_earning[$yr] = $retained_earning_free[$yr] + $net_income_for_the_period[$yr];

							//Stocks Dividends
							$stock_dividend[$yr] = $db->getValue('account_fs','afs_value',array('afs_name'=>'stock_dividend','afs_yr'=>$yr));
							//Cash Dividends
							$cash_dividend[$yr] = $db->getValue('account_fs','afs_value',array('afs_name'=>'cash_dividend','afs_yr'=>$yr));
							//Total Dividends
							$total_dividend[$yr] = $stock_dividend[$yr] + $cash_dividend[$yr];
							//Free Retained Earnings Dec. 31
							$free_retained_earnings_dec_31[$yr] = $total_free_retained_earning[$yr] - $total_dividend[$yr];
							//Total Retained Earnings Dec. 31
							$total_retained_earnings_dec_31[$yr] = $retained_future_expansion[$yr] + $free_retained_earnings_dec_31[$yr];

							$retained_earnings_balance_jan1[$yr + 1] = $total_retained_earnings_dec_31[$yr];
							//Balance at December 31
							$balance_at_dec_31[$yr] = $scse_paid_up[$yr] + $total_retained_earnings_dec_31[$yr];
						endforeach;
						#print_r($retained_future_expansion);
						$arrPO_payable=array();
						$qPO_payable = $db->query('SELECT sum(balance) as amnt, left(po_date,4) as dt FROM view_po_payment GROUP BY dt');
						getVal($qPO_payable,$arrPO_payable);

						$arrLoans=array();
						$qLoans = $db->query('SELECT sum(as_amount) as amnt, left(as_date,4) as dt FROM account_statement WHERE description="LOAN PROCEEDS" GROUP BY dt');
						getVal($qLoans,$arrLoans);


						$arrStockShareCommon=array();
						$qStockShareCommon = $db->query('SELECT LEFT(ast.as_date,4) as dt, sum(ast.as_amount) as amnt FROM account_statement ast, stockshare sh WHERE sh.stocktype="common" AND ast.as_id=sh.as_id GROUP BY dt ORDER BY dt');
						getVal($qStockShareCommon,$arrStockShareCommon);

						$arrStockSharePreferred=array();
						$qStockSharePreferred = $db->query('SELECT LEFT(ast.as_date,4) as dt, sum(ast.as_amount) as amnt FROM account_statement ast, stockshare sh WHERE sh.stocktype="preferred" AND ast.as_id=sh.as_id GROUP BY dt ORDER BY dt');
						getVal($qStockSharePreferred,$arrStockSharePreferred);

						foreach($arrYear as $yr):
							//Accounts Payable
							$po_payable = isset($arrPO_payable[$yr]) ? $arrPO_payable[$yr] : 0;
							$arrCurrentLiability[$yr] = isset($arrCurrentLiability[$yr]) ? ( isset($arrCurrentLiability[($yr-1)]) ? $arrCurrentLiability[($yr-1)] + $arrCurrentLiability[$yr] : $arrCurrentLiability[$yr] ) : ( isset($arrCurrentLiability[($yr-1)]) ? $arrCurrentLiability[($yr-1)] : 0 );
							#$arrCurrentLiability[$yr] = isset($arrCurrentLiability[($yr-1)]) ? $arrCurrentLiability[($yr-1)] + $po_payable : $po_payable;
							

							$arrTotalLiability[$yr] = isset($arrTotalLiability[($yr-1)]) ? $arrTotalLiability[($yr-1)] + $po_payable : $po_payable;
							$arrTotalLiability[$yr] = isset($arrTotalLiability[$yr]) ? ( isset($arrTotalLiability[($yr-1)]) ? $arrTotalLiability[($yr-1)] + $arrTotalLiability[$yr] : $arrTotalLiability[$yr] ) : ( isset($arrTotalLiability[($yr-1)]) ? $arrTotalLiability[($yr-1)] : 0 );

							#$cl_accounts_payable[$yr]+=$po_payable;
							#$cl_accounts_payable[$yr] = isset($cl_accounts_payable[($yr-1)]) ? $cl_accounts_payable[($yr-1)] + $po_payable : $po_payable;
							#$cl_accounts_payable[$yr] = isset($cl_accounts_payable[$yr]) ? ( isset($cl_accounts_payable[($yr-1)]) ? $cl_accounts_payable[($yr-1)] + $po_payable : $cl_accounts_payable[$yr] ) : ( isset($cl_accounts_payable[($yr-1)]) ? $cl_accounts_payable[($yr-1)] : 0 );
							$cl_accounts_payable[$yr] = isset($cl_accounts_payable[($yr-1)]) ? $cl_accounts_payable[($yr-1)] + $po_payable : $po_payable;
							//Accrued Expenses
							$accured_expenses = accrued_expenses($yr);
							$arrCurrentLiability[$yr]+=$accured_expenses;
							$arrTotalLiability[$yr]+=$accured_expenses;
							#$cl_accrued_expenses[$yr]+=$accured_expenses;
							$cl_accrued_expenses[$yr] = isset($cl_accrued_expenses[($yr-1)]) ? $cl_accrued_expenses[($yr-1)] + $accured_expenses : $accured_expenses;

							$cl_unearned_construction_income[$yr]=0;

							//Income Tax Payable
							$incomeTax = isset($arrIncomeTax[$yr]) ? $arrIncomeTax[$yr] : 0;
							$cl_income_tax_payable[$yr]=$arrIncomeTax[$yr];
							//$cl_income_tax_payable[$yr] = isset($cl_income_tax_payable[($yr-1)]) ? $cl_income_tax_payable[($yr-1)] + $incomeTax : $incomeTax;
							$arrCurrentLiability[$yr]+=$arrIncomeTax[$yr];
							//$arrCurrentLiability[$yr]=$arrIncomeTax[$yr];
							$arrTotalLiability[$yr]+=$arrIncomeTax[$yr];

							//Loan
							$loan = isset($arrLoans[$yr]) ? $arrLoans[$yr] : 0;
							$ll_loan[$yr] = $loan;
							$arrLongtermLiability[$yr]=$loan;
							$arrTotalLiability[$yr]+=$loan;

							//Other Long Term Payable
							$ll_other_longterm_payable[$yr]=0;
							$arrLongtermLiability[$yr]+=$ll_other_longterm_payable[$yr];
							$arrTotalLiability[$yr]+=$ll_other_longterm_payable[$yr];

							$arrStockShareCommon[$yr] = isset($arrStockShareCommon[$yr]) ? ( isset($arrStockShareCommon[($yr-1)]) ? $arrStockShareCommon[($yr-1)] + $arrStockShareCommon[$yr] : $arrStockShareCommon[$yr] ) : ( isset($arrStockShareCommon[($yr-1)]) ? $arrStockShareCommon[($yr-1)] : 0 );

							$arrStockSharePreferred[$yr] = isset($arrStockSharePreferred[$yr]) ? ( isset($arrStockSharePreferred[($yr-1)]) ? $arrStockSharePreferred[($yr-1)] + $arrStockSharePreferred[$yr] : $arrStockSharePreferred[$yr] ) : ( isset($arrStockSharePreferred[($yr-1)]) ? $arrStockSharePreferred[($yr-1)] : 0 );
							
							$stockShare = isset($arrStockShareCommon[$yr]) ? $arrStockShareCommon[$yr] : 0;
							$stockShare += isset($arrStockSharePreferred[$yr]) ? $arrStockSharePreferred[$yr] : 0;
							$arrStockHolderEquity[$yr]=$stockShare;
							$arrTotalLiability[$yr]+=$stockShare;

							//Retained Earnings
							#$se_retained_earnings[$yr]= isset($se_retained_earnings[($yr-1)]) ? $se_retained_earnings[($yr-1)] + $total_retained_earnings_dec_31[$yr] : $total_retained_earnings_dec_31[$yr];
							$se_retained_earnings[$yr]=$total_retained_earnings_dec_31[$yr];
							$arrStockHolderEquity[$yr]+=$total_retained_earnings_dec_31[$yr];
							$arrTotalLiability[$yr]+=$total_retained_earnings_dec_31[$yr];
						endforeach;

						?>
						<tr>
							<td colspan="<?php echo count($arrYear) + 1?>"><strong>LIABILITIES AND STOCKHOLDERS' EQUITY</strong></td>
						</tr>
						<tr>
							<td></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="center"><strong><?PHP echo $yr?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td><strong><i>Current Liabilities</i></strong></td>
							<?php foreach($arrYear as $yr):?>
							<td>&nbsp;</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Accounts payable</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cl_accounts_payable[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Accrued expenses</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cl_accrued_expenses[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Unearned Construction Income</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cl_unearned_construction_income[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Income tax payable</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cl_income_tax_payable[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Total Current Liabilities</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($arrCurrentLiability[$yr])?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td><strong><i>Longterm Liabilities</i></strong></td>
							<?php foreach($arrYear as $yr):?>
							<td>&nbsp;</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Loan</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($ll_loan[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Other Long term Payable</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($ll_other_longterm_payable[$yr])?></div></td>
							<?php endforeach;?>
						</tr>

						<tr>
							<td style="padding-left: 30px;"><i><strong>Total Longterm Liabilities</i></strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($arrLongtermLiability[$yr])?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td height="30">&nbsp;</td>
							<?php foreach($arrYear as $yr):?>
							<td>&nbsp;</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td><strong>Stocholders' Equity</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><strong>&nbsp;</strong></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Common</td>
							<?php foreach($arrYear as $yr): ?>
							<td><div align="right"><?php echo isset($arrStockShareCommon[$yr]) ? functions::formatMoney($arrStockShareCommon[$yr]) : 0;?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Preferred</td>
							<?php foreach($arrYear as $yr): ?>
							<td><div align="right"><?php echo isset($arrStockSharePreferred[$yr]) ? functions::formatMoney($arrStockSharePreferred[$yr]) : 0;?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Retained Earnings</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($se_retained_earnings[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><i><strong>Total Stockholders' Equity</strong><i></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($arrStockHolderEquity[$yr])?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<?php foreach($arrYear as $yr):?>
							<td>&nbsp;</td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td height="5" style="padding-right: 30px;"><div align="right"><strong>Total Liabilities</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($arrTotalLiability[$yr])?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td height="90" colspan="<?php echo count($arrYear) + 1?>">&nbsp;</td>
						</tr>

						<tr>
							<td colspan="<?php echo count($arrYear) + 1?>"><strong>STATEMENT OF INCOME</strong></td>
						</tr>
						<tr>
							<td></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="center"><strong><?PHP echo $yr?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Construction Income</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($arrConstructionIncome[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Cost of Construction Income</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($arrCostOfConstructionIncome[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Gross Profit</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($arrGrossProfit[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Operating Expenses</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($arrOperatingExpenses[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Income Before Income Tax</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($arrIncomeBeforeIncomeTax[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Provision for Income Tax</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($arrProvisionIncomeTax[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Net Income</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($arrFSnetIncome[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td height="90" colspan="<?php echo count($arrYear) + 1?>">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="<?php echo count($arrYear) + 1?>"><strong>STATEMENT OF CHANGES IN STOCKHOLDERS' EQUITY</strong></td>
						</tr>
						<?php
						//Computation for this report where moved before the computation of STATEMENT OF INCOME
						?>
						<tr>
							<td></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="center"><strong><?PHP echo $yr?></strong></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td colspan="<?php echo count($arrYear) + 1?>"><strong>Capital Stock - P100 par Value</strong></td>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Paid-up</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($scse_paid_up[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>

						<tr>
							<td style="padding-left: 30px;">Less: Treasury Stocks</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($scse_treasury_stocks[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Total Paid-up Capital</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($scse_paid_up[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td colspan="<?php echo count($arrYear) + 1?>"><strong>Retained Earnings</strong></td>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Balance at January 1</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($retained_earnings_balance_jan1[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Less: Retained Appropriated for Future Expansion</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><a id="id_retained_future<?php echo $yr?>" href="#" class="thickbox" onclick="showThis(this.id,'account_financial_statement_manage.php?t=<?php echo functions::encode('Retained Appropriated for Future Expansion')?>&dbt=<?php echo functions::encode('retained_future')?>&y=<?php echo functions::encode($yr)?>','Statement Add')"><?php echo functions::formatMoney($retained_future_expansion[$yr])?></a></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Retained Earnings Free</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($retained_earning_free[$yr])?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Net income for the period</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($net_income_for_the_period[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Total Free retained earnings</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($total_free_retained_earning[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Less: dividends: </td>
							<?php foreach($arrYear as $yr):?>
							<td></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Stocks Dividends</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><a id="id_stock_dividend<?php echo $yr?>" href="#" class="thickbox" onclick="showThis(this.id,'account_financial_statement_manage.php?t=<?php echo functions::encode('Stocks Dividends')?>&dbt=<?php echo functions::encode('stock_dividend')?>&y=<?php echo functions::encode($yr)?>','Statement Add')"><?php echo functions::formatMoney($stock_dividend[$yr])?></a></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Cash Dividends</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><a id="id_cash_dividend<?php echo $yr?>" href="#" class="thickbox" onclick="showThis(this.id,'account_financial_statement_manage.php?t=<?php echo functions::encode('Cash Dividends')?>&dbt=<?php echo functions::encode('cash_dividend')?>&y=<?php echo functions::encode($yr)?>','Statement Add')"><?php echo functions::formatMoney($cash_dividend[$yr])?></a></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Total Dividends</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($total_dividend[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Free Retained Earnings Dec. 31</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($free_retained_earnings_dec_31[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Total Retained Earnings Dec. 31</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($total_retained_earnings_dec_31[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Balance at December 31</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($balance_at_dec_31[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td height="90" colspan="<?php echo count($arrYear) + 1?>">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="<?php echo count($arrYear) + 1?>"><strong>STATEMENT OF CASH FLOWS</strong></td>
						</tr>
						<tr>
							<td></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="center"><strong><?PHP echo $yr?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<?php
						//STATEMENT OF CASH FLOWS
						
						foreach($arrYear as $yr):
							//Net Income Before Income Tax
							$cf_net_income_before_income_tax[$yr]=$arrIncomeBeforeIncomeTax[$yr];
							//Depreciation
							$cf_depreciation[$yr] = isset($arr_fs[$yr]['additionDepreciation']) ? $arr_fs[$yr]['additionDepreciation'] : '';
							//Operating income before working capital changes
							$cf_oper_income[$yr]=$arrIncomeBeforeIncomeTax[$yr] + $cf_depreciation[$yr];

							//Receivables
							$last_year_receivable = isset($ca_receivable[$yr-1]) ? $ca_receivable[$yr-1] : 0;
							$current_year_receivable = isset($ca_receivable[$yr]) ? $ca_receivable[$yr] : 0;
							$cf_receivable[$yr] = $last_year_receivable - $current_year_receivable;

							//Prepaid Expenses
							$last_year_prepaid = isset($ca_prepaid[$yr-1]) ? $ca_prepaid[$yr-1] : 0;
							$current_year_prepaid = isset($ca_prepaid[$yr]) ? $ca_prepaid[$yr] : 0;
							$cf_prepaid_expenses[$yr] = $last_year_prepaid - $current_year_prepaid;

							//Increase in long term investment
							$cf_longterm_investment[$yr] = $ca_investment[$yr];

							//Accounts payable
							$last_year_accounts_payable = isset($cl_accounts_payable[$yr-1]) ? $cl_accounts_payable[$yr-1] : 0;
							$current_year_accounts_payable = isset($cl_accounts_payable[$yr]) ? $cl_accounts_payable[$yr] : 0;
							$cf_accounts_payable[$yr]=$current_year_accounts_payable - $last_year_accounts_payable;

							//Accured expenses
							$last_year_accrued_expenses = isset($cl_accrued_expenses[$yr-1]) ? $cl_accrued_expenses[$yr-1] : 0;
							$current_year_accrued_expenses = isset($cl_accrued_expenses[$yr]) ? $cl_accrued_expenses[$yr] : 0;
							$cf_accrued_expenses[$yr]=$current_year_accrued_expenses - $last_year_accrued_expenses;

							//Increase in Unearned Construction Income
							$cf_unearned_construction_income[$yr]=0;
							//Bank Loan payment
							$cf_bank_loan_payment[$yr]=0;
							//Other Long term
							$cf_other_longterm[$yr]=0;
							//Cash generated from operations
							$cf_cash_gen_from_oper[$yr]=$cf_oper_income[$yr]+$cf_receivable[$yr]+$cf_prepaid_expenses[$yr]+$cf_longterm_investment[$yr]+$cf_accounts_payable[$yr]+$cf_accrued_expenses[$yr]+$cf_unearned_construction_income[$yr]+$cf_bank_loan_payment[$yr]+$cf_other_longterm[$yr];
							//Income tax paid and other taxes payments
							$cf_income_tax_paid[$yr]=0;
							//Net Cash Used in Operating Activities
							$cf_net_cash_used_oper_act[$yr]=$cf_cash_gen_from_oper[$yr]+$cf_income_tax_paid[$yr];

							//Acquisition of property and equipment - Net
							$cf_acquisition_prop_equip[$yr]=$ll_other_longterm_payable[$yr];
							//Increase in other assets
							$cf_increase_other_assets[$yr]=0;
							//Net Cash Used in Financing Activities
							$cf_net_cash_used_financing_act[$yr]=$cf_acquisition_prop_equip[$yr]+$cf_increase_other_assets[$yr];
							//Dividends
							$cf_dividends[$yr]=$total_dividend[$yr];
							$cf_acquisition_treasury_share[$yr]=$scse_treasury_stocks[$yr];

							//Increase in Paid-up Capital
							$last_year_paid_up = isset($scse_paid_up[$yr-1]) ? $scse_paid_up[$yr-1] : 0;
							$current_year_paid_up = isset($scse_paid_up[$yr]) ? $scse_paid_up[$yr] : 0;
							$cf_increase_paid_up_capital[$yr]=$current_year_paid_up - $last_year_paid_up;
							//Proceeds from Loan
							$cf_proceeds_from_loan[$yr]=$ll_loan[$yr];
							//Net Cash Flow from Financing
							$cf_net_cash_flow_financing[$yr]=$cf_dividends[$yr]+$cf_acquisition_treasury_share[$yr]+$cf_increase_paid_up_capital[$yr]+$cf_proceeds_from_loan[$yr];
							//Net Increase(Decrease) in Cash
							$cf_net_increase_decrease_cash[$yr]=$cf_net_cash_used_oper_act[$yr]+$cf_net_cash_used_financing_act[$yr]+$cf_net_cash_flow_financing[$yr];
							//Cash, Beginning
							$cf_cash_beginning[$yr]=isset($cf_cash_dec31[$yr-1]) ? $cf_cash_dec31[$yr-1] : 0;
							//Cash, December 31
							$cf_cash_dec31[$yr]=$cf_net_increase_decrease_cash[$yr]+$cf_cash_beginning[$yr];

						endforeach;
						?>
						<tr>
							<td><strong>Cash Flows from Operating Activities</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Net Income Before Income Tax</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_net_income_before_income_tax[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Adjustments for: </td>
							<?php foreach($arrYear as $yr):?>
							<td></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Depreciation</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_depreciation[$yr]); ?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Operating income before working capital changes</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_oper_income[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Decrease (increase) in: </td>
							<?php foreach($arrYear as $yr):?>
							<td></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Receivables</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_receivable[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Prepaid Expenses</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_prepaid_expenses[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Increase in long term investment</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_longterm_investment[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Increase (decrease) in: </td>
							<?php foreach($arrYear as $yr):?>
							<td></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Accounts payable</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_accounts_payable[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Accured expenses</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_accrued_expenses[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Increase in Unearned Construction Income</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_unearned_construction_income[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Bank Loan payment</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_bank_loan_payment[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 60px;">Other Long term </td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_other_longterm[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Cash generated from operations</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($cf_cash_gen_from_oper[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Income tax paid and other taxes payments</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_income_tax_paid[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Net Cash Used in Operating Activities</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_net_cash_used_oper_act[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Cash Flows from Investing Activities</strong></td>
							<td colspan="<?php echo count($arrYear)?>">&nbsp;</td>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Acquisition of property and equipment - Net</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_acquisition_prop_equip[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Increase in other assets</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_increase_other_assets[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Net Cash Used in Financing Activities</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_net_cash_used_financing_act[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Cash Flows from Financing Activities</strong></td>
							<td colspan="<?php echo count($arrYear)?>">&nbsp;</td>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Dividends</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($cf_dividends[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Acquisition of Treasury Shares</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><strong></strong></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Increase in Paid-up Capital</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($cf_increase_paid_up_capital[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Proceeds from Loan</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_proceeds_from_loan[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;">Net Cash Flow from Financing</td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><?php echo functions::formatMoney($cf_net_cash_flow_financing[$yr]);?></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Net Increase(Decrease) in Cash</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($cf_net_increase_decrease_cash[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Cash, Beginning</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($cf_cash_beginning[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
						<tr>
							<td style="padding-left: 30px;"><strong>Cash, December 31</strong></td>
							<?php foreach($arrYear as $yr):?>
							<td><div align="right"><strong><?php echo functions::formatMoney($cf_cash_dec31[$yr]);?></strong></div></td>
							<?php endforeach;?>
						</tr>
					</tbody>
				</table>
			</div>
		</div><!--/span-->
	</form>
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>