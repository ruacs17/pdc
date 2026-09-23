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
    <style>.padleft{padding-right: 5px;}</style>
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
        <table class="table-hover" width="100%" border="1" style="font-size:12px;">
            <tr>
                <td>Revenue</td>
                <?php foreach($arrMonth as $month):?>
                <td><div align="center"><?php echo monthDisp($month)?></div></td>
                <?php endforeach;?>
                <td class="padleft"><div align="right">Total</div></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td>&nbsp;</td>
                <?php endforeach;?>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Gross Billed Amount</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <?php
                    $costIncome=0;
                    $qCostIncome = $db->query('SELECT sum(pi.amount) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
                    $costIncome=$db->result($qCostIncome);
                    $totalIncome += $costIncome;
                    ?>
                    <div align="right">
                        <a id="income<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-income.php?mnth=<?php echo functions::encode($monthIncome);?>','Billed Amount','1')">
                            <?php echo functions::formatMoney($costIncome);?>
                        </a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalIncome);?></div></td>
            </tr>
            <tr>
                <td colspan="<?php echo count($arrMonth) + 2?>">Less: Deductions</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;VAT</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $vat=0;
                        $qVat = $db->query('SELECT sum(pi.vat) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
                        $vat = $db->result($qVat);
                        $totalVAT += $vat;
                        $arrNetBilled[$monthIncome] += $vat;
                        ?>
                        <a id="vat<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-vat.php?mnth=<?php echo functions::encode($monthIncome);?>','VAT','1')"><?php echo functions::formatMoney($vat);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalVAT);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;EWT</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $ewt=0;
                        $qEWT = $db->query('SELECT sum(pi.ewt) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
                        $ewt = $db->result($qEWT);
                        $totalEWT += $ewt;
                        $arrNetBilled[$monthIncome] += $ewt;
                        ?>
                        <a id="ewt<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-ewt.php?mnth=<?php echo functions::encode($monthIncome);?>','EWT','1')"><?php echo functions::formatMoney($ewt);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEWT);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Retention</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $retention=0;
                        $qRetention = $db->query('SELECT sum(pi.retention) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
                        $retention = $db->result($qRetention);
                        $totalRetention += $retention;
                        $arrNetBilled[$monthIncome] +=  $retention;
                        ?>
                        <a id="ret<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-retention.php?mnth=<?php echo functions::encode($monthIncome);?>','RETENTION','1')"><?php echo functions::formatMoney($retention);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRetention);?></div></td>
            </tr>
            <tr>
                <td> &nbsp;&nbsp;&nbsp;Contractor's Tax</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $contractor=0;
                        $qContractor = $db->query('SELECT sum(pi.contractor) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
                        $contractor = $db->result($qContractor);
                        $totalConTax += $contractor;
                        $arrNetBilled[$monthIncome] += $contractor;
                        ?>
                        <a id="contractor<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-contax.php?mnth=<?php echo functions::encode($monthIncome);?>','CONTRACTOR TAX','1')"><?php echo functions::formatMoney($contractor);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConTax);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Recoupment</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $recoupment=0;
                        $qRecoupment = $db->query('SELECT sum(pi.recoupment) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
                        $recoupment = $db->result($qRecoupment);
                        $totalRecoupment += $recoupment;
                        $arrNetBilled[$monthIncome] +=  $recoupment;
                        ?>
                        <a id="recoupment<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-recoup.php?mnth=<?php echo functions::encode($monthIncome);?>','RECOUPMENT','1')"><?php echo functions::formatMoney($recoupment);?></a>
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
                        $qCostIncome = $db->query('SELECT sum(pi.amount) FROM project p, project_income pi WHERE p.proj_id=pi.proj_id AND left(pi_date,7)="'.$monthIncome.'"');
                        $costIncome = $db->result($qCostIncome);
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
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <?php
                    $costRegDep=0;
                    $qCostRegDep = $db->query('SELECT sum(as_amount) FROM account_statement WHERE transaction_type="deposit" AND as_type="debit" AND left(as_date,7)="'.$monthIncome.'"');
                    $costRegDep=$db->result($qCostRegDep);
                    $totalRegularDeposit += $costRegDep;
                    $totalNetBilled += $costRegDep;
                    ?>
                    <div align="right">
                        <a id="regDep<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('deposit');?>','Regular Deposit Details','1')">
                            <?php echo functions::formatMoney($costRegDep);?>
                        </a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRegularDeposit);?></div></td>
            </tr>
            <tr>
                <td>Loan Proceeds</td>
                <?php foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <?php
                    $costLoan=0;
                    $qCostLoan = $db->query('SELECT sum(as_amount) FROM account_statement WHERE description="LOAN PROCEEDS" AND left(as_date,7)="'.$monthIncome.'"');
                    $costLoan=$db->result($qCostLoan);
                    $totalLoan += $costLoan;
                    $totalNetBilled += $costLoan;
                    ?>
                    <div align="right">
                        <a id="loan<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('loan proceeds');?>','Loan Proceeds Transaction','1')">
                            <?php echo functions::formatMoney($costLoan);?>
                        </a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalLoan);?></div></td>
            </tr>
            <tr>
                <td>Cash Return</td>
                <?php $totalCashRet=0; foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <?php
                    $costCashRet=0;
                    $qCostCashRet = $db->query('SELECT sum(as_amount) FROM account_statement WHERE transaction_type="cash return" AND left(as_date,7)="'.$monthIncome.'"');
                    $costCashRet=$db->result($qCostCashRet);
                    $totalCashRet += $costCashRet;
                    $totalNetBilled += $costCashRet;
                    ?>
                    <div align="right">
                        <a id="cashRet<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('cash return');?>','Cash Return Transaction','1')">
                            <?php echo functions::formatMoney($costCashRet);?>
                        </a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCashRet);?></div></td>
            </tr>
            <tr>
                <td>Stock Share</td>
                <?php $totalStockShare=0; foreach($arrMonth as $monthIncome):?>
                <td class="padleft">
                    <?php
                    $costStockShare=0;
                    $qCostStockShare = $db->query('SELECT sum(as_amount) FROM account_statement WHERE transaction_type="stock share" AND left(as_date,7)="'.$monthIncome.'"');
                    $costStockShare=$db->result($qCostStockShare);
                    $totalStockShare += $costStockShare;
                    $totalNetBilled += $costStockShare;
                    ?>
                    <div align="right">
                        <a id="stockShare<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-deposit.php?mnth=<?php echo functions::encode($monthIncome);?>&tranType=<?php echo functions::encode('stock share');?>','Stock Holders','1')">
                            <?php echo functions::formatMoney($costStockShare);?>
                        </a>
                    </div>
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
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costOtherIncome=0;
                        $qInhouseMaterialOther = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi WHERE im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND im.payee != "" AND is_paid="2"');
                        $costOtherIncome += $db->result($qInhouseMaterialOther,0);

                        $qInhouseRentalOther = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND left(iel_date,7)="'.$month.'" AND iel.payee != "" AND is_paid="2"');
                        $costOtherIncome += $db->result($qInhouseRentalOther,0);

                        $totalOtherIncome += $costOtherIncome;
                        $totalNetBilled += $costOtherIncome;
                        ?>
                        <a id="materialOU<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail-inhouse-other.php?mnth=<?php echo functions::encode($month);?>&ih=<?php echo functions::encode("materials");?>','MATERIAL IN-HOUSE','1')"><?php echo functions::formatMoney($costOtherIncome);?></a>
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
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costLabor=0;
                        $qVoucherLabor = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="labor"');
                        $costLabor = $db->result($qVoucherLabor,0);

                        $qPOLabor = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="labor" AND vpp.paid > 0');
                        $costLabor += $db->result($qPOLabor,0);

                        $qInhouseMaterialLabor = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.expense_type="labor"');
                        $costLabor += $db->result($qInhouseMaterialLabor,0);

                        $totalLabor += $costLabor;
                        $arrDirectCost[$month] += $costLabor;
                        ?>
                        <a id="labor<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("labor");?>','LABOR','1')"><?php echo functions::formatMoney($costLabor);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft">
                    <div align="right"><?php echo functions::formatMoney($totalLabor);?></div>
                </td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Materials</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costMaterial=0;
                        $qVoucherMaterial = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="materials"');
                        $costMaterial += $db->result($qVoucherMaterial,0);

                        $qPO = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="materials" AND vpp.paid > 0');
                        $costMaterial += $db->result($qPO,0);

                        $totalMaterial += $costMaterial;
                        $arrDirectCost[$month] += $costMaterial;
                        ?>
                        <a id="material<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("materials");?>&ih=<?php echo functions::encode("material");?>','MATERIAL','1')"><?php echo functions::formatMoney($costMaterial);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalMaterial);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Materials (In House)</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costMaterialIH=0;
                        $qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.expense_type="materials"');
                        $costMaterialIH = $db->result($qInhouseMaterial,0);

                        $totalMaterialIH += $costMaterialIH;
                        $arrDirectCost[$month] += $costMaterialIH;
                        ?>
                        <a id="materialIH<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail-inhouse.php?mnth=<?php echo functions::encode($month);?>&ih=<?php echo functions::encode("materials");?>','MATERIAL IN-HOUSE','1')"><?php echo functions::formatMoney($costMaterialIH);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalMaterialIH);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Equipment</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costEquipment=0;
                        $totalIH=0;
                        $qVoucherEquipment = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="equipment"');
                        $costEquipment += $db->result($qVoucherEquipment,0);

                        $qPOEquip = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="equipment" AND vpp.paid > 0');
                        $costEquipment += $db->result($qPOEquip,0);

                        $qInhouseMaterialEquipment = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.expense_type="equipment"');
                        $costEquipment += $db->result($qInhouseMaterialEquipment,0);

                        $totalEquipment += $costEquipment;
                        $arrDirectCost[$month] += $costEquipment;
                        ?>
                        <a id="equiprental<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("equipment");?>&ih=<?php echo functions::encode("equipment");?>','EQUIPMENT RENTAL','1')"><?php echo functions::formatMoney($costEquipment);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEquipment);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Equipment Rental (In-House)</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costEquipmentIH=0;
                        $qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND left(iel_date,7)="'.$month.'"');
                        $costEquipmentIH = $db->result($qInhouseRental,0);

                        $totalEquipmentIH += $costEquipmentIH;
                        $arrDirectCost[$month] += $costEquipmentIH;
                        ?>
                        <a id="equipmentIH<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail-inhouse.php?mnth=<?php echo functions::encode($month);?>&ih=<?php echo functions::encode("equipment");?>','EQUIPMENT RENTAL IN-HOUSE','1')"><?php echo functions::formatMoney($costEquipmentIH);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEquipmentIH);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Commitment</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costCommitment=0;
                        $qCommitment = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="commitment"');
                        $costCommitment = $db->result($qCommitment,0);

                        $qPOCommit = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="commitment" AND vpp.paid > 0');
                        $costCommitment += $db->result($qPOCommit,0);

                        $qInhouseMaterialCommit = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.cost_type="commitment"');
                        $costCommitment += $db->result($qInhouseMaterialCommit,0);

                        $totalCommitment += $costCommitment;
                        $arrDirectCost[$month] += $costCommitment;
                        ?>
                        <a id="commitment<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("commitment");?>','COMMITMENT','1')"><?php echo functions::formatMoney($costCommitment);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalCommitment);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Finder's Fee</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costFinder=0;
                        $qFinder = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="finders fee"');
                        $costFinder = $db->result($qFinder,0);

                        $qPOFinder = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="finders fee" AND vpp.paid > 0');
                        $costFinder += $db->result($qPOFinder,0);

                        $qInhouseMaterialFinder = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.cost_type="finders fee"');
                        $costFinder += $db->result($qInhouseMaterialFinder,0);

                        $totalFinder += $costFinder;
                        $arrDirectCost[$month] += $costFinder;
                        ?>
                        <a id="findersfee<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("finders fee");?>','FINDER\'S FEE','1')"><?php echo functions::formatMoney($costFinder);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalFinder);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Consultancy</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costConsultancy=0;
                        $qConsultancy = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="consultancy"');
                        $costConsultancy = $db->result($qConsultancy,0);

                        $qPOConsultancy = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="consultancy" AND vpp.paid > 0');
                        $costConsultancy += $db->result($qPOConsultancy,0);

                        $qInhouseMaterialConsultancy = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.cost_type="consultancy"');
                        $costConsultancy += $db->result($qInhouseMaterialConsultancy,0);

                        $totalConsultancy += $costConsultancy;
                        $arrDirectCost[$month] += $costConsultancy;
                        ?>
                        <a id="consultancy<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("consultancy");?>','CONSULTANCY','1')"><?php echo functions::formatMoney($costConsultancy);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConsultancy);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Technical Fee</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costTechnical=0;
                        $qTechnical = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND left(vd_date,7)="'.$month.'" AND itd.cost_type="technical fee"');
                        $costTechnical = $db->result($qTechnical,0);

                        $qPOTechnical = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="technical fee" AND vpp.paid > 0');
                        $costTechnical += $db->result($qPOTechnical,0);

                        $qInhouseMaterialTechnical = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.cost_type="technical fee"');
                        $costTechnical += $db->result($qInhouseMaterialTechnical,0);

                        $totalTechnical += $costTechnical;
                        $arrDirectCost[$month] += $costTechnical;
                        ?>
                        <a id="technical<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode("technical fee");?>','TECHNICAL FEE','1')"><?php echo functions::formatMoney($costTechnical);?></a>
                    </div>
                </td>
                <?php endforeach;?>
                <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalTechnical);?></div></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;Subcon</td>
                <?php foreach($arrMonth as $month):?>
                <td class="padleft">
                    <div align="right">
                        <?php
                        $costSubcon=0;
                        $qVoucherSubcon = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="subcon"');
                        $costSubcon = $db->result($qVoucherSubcon,0);

                        $qPOSubcon = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="subcon" AND vpp.paid > 0');
                        $costSubcon += $db->result($qPOSubcon,0);

                        $qInhouseMaterialSubcon = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND itd.expense_type="subcon"');
                        $costSubcon += $db->result($qInhouseMaterialSubcon,0);

                        $totalSubcon += $costSubcon;
                        $arrDirectCost[$month] += $costSubcon;
                        ?>
                        <a id="subcon<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-detail.php?mnth=<?php echo functions::encode($month);?>&expenseType=<?php echo functions::encode("subcon");?>','SUBCON','1')"><?php echo functions::formatMoney($costSubcon);?></a>
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

            $arrCategory=array();

            $overVoucher = $db->query('SELECT DISTINCT category_id FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" AND left(vd.vd_date,4)="'.$year.'" ORDER BY itd.name');
            while($rOverVoucher = $db->fetch_array($overVoucher)):
                $arrCategory = functions::insert_array($arrCategory,$rOverVoucher['category_id']);
            endwhile;

            $overPO = $db->query('SELECT DISTINCT category_id FROM po p, view_po_payment vpp, item_deduction itd WHERE p.category_id=itd.item_id AND p.po_id=vpp.po_id AND paid > 0 AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" AND left(p.po_date,4)="'.$year.'" ORDER BY itd.name');
            while($rOverPO = $db->fetch_array($overPO)):
                $arrCategory = functions::insert_array($arrCategory,$rOverPO['category_id']);
            endwhile;

            $overInhouseMaterial = $db->query('SELECT DISTINCT category_id FROM inhouse_material im, item_deduction itd WHERE im.category_id=itd.item_id AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" AND left(im_date,4)="'.$year.'" ORDER BY itd.name');
            while($rOverInhouseMaterial = $db->fetch_array($overInhouseMaterial)):
                $arrCategory = functions::insert_array($arrCategory,$rOverInhouseMaterial['category_id']);
            endwhile;

            if(count($arrCategory)){
                $qSort = 'SELECT DISTINCT item_id FROM item_deduction WHERE ';
                $countSort=0;
                foreach($arrCategory as $cat):
                    if($countSort==0)
                        $qSort .= 'item_id="'.$cat.'" ';
                    else
                        $qSort .= 'OR item_id="'.$cat.'" ';
                    $countSort++;
                endforeach;
                $qSort .= ' ORDER BY name';

                $arrCategory=array();
                $qSortCat = $db->query($qSort);
                while($rSortCat = $db->fetch_array($qSortCat)):
                    $arrCategory[]=$rSortCat['item_id'];
                endwhile;
            }

            $prevTotalOperatingExpenses = 0;

            foreach( $arrCategory as $categoryID ):
                $totalCategoryCost=0;
            ?>
            <tr>
                <td>&nbsp;&nbsp;&nbsp;<?php echo $db->getValue('item_deduction','name',array('item_id'=>$categoryID));?></td>
                <?php foreach($arrMonth as $month):
                $countID++;
                if( !isset($arrOverheadCost[$month]) )
                    $arrOverheadCost[$month]=0;
                ?>
                <td class="padleft">
                    <div align="right">
                    <?php
                    $categoryCost=0;
                    $q = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND vd.category_id="'.$categoryID.'" AND left(vd_date,7)="'.$month.'"');
                    $categoryCost = $db->result($q,0);

                    $qPOCat = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp WHERE p.po_id=vpp.po_id AND p.category_id="'.$categoryID.'" AND left(p.po_date,7)="'.$month.'" AND vpp.paid > 0');
                    $categoryCost += $db->result($qPOCat,0);

                    $qInhouseMaterialCat = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$month.'" AND im.category_id="'.$categoryID.'"');
                    $categoryCost += $db->result($qInhouseMaterialCat,0);

                    $arrOverheadCost[$month] += $categoryCost;
                    $totalCategoryCost += $categoryCost;
                    $totalOverheadCost += $categoryCost;
                    $totalOperatingExpenses += $categoryCost;
                    ?>
                        <a id="cat<?php echo $countID?>" class="thickbox" style="cursor: pointer;" title="View Details" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-all-oper-exp.php?mnth=<?php echo functions::encode($month);?>&ct=<?php echo functions::encode($categoryID);?>','<?php echo $db->clean($db->getValue('item_deduction','name',array('item_id'=>$categoryID)));?>','1')"><?php echo functions::formatMoney($categoryCost);?></a>
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
        </table>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?yr="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>