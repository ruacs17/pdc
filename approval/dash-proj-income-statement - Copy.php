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
$totalLabor=0;  $totalMaterial=0; $totalEquipment=0;  $totalCommitment=0; $totalFinder=0; $totalTechnical=0; $totalSubcon=0; $totalConsultancy=0; $totalDirectCost=0; $totalOverheadCost=0; $totalIncome=0; $totalNetBilled=0;
$countID=0;$totalVAT=0;$totalEWT=0;$totalRetention=0;$totalConTax=0;$totalRecoupment=0; $totalDeductions=0; $totalMaterialIH=0; $totalEquipmentIH=0;

$month_start = date('m');
$year_start = date('Y');
$month_end = date('m');
$year_end = date('Y');
$qProject = $db->select('project','*',array('proj_id'=>$project_id));
$rProj = $db->fetch_array($qProject);

$arrMonth=array();

$arrayMonth=array();

#get from Billing
$qBilling = $db->select('project_income','DISTINCT left(pi_date,7) as pdate',array('proj_id'=>$project_id),'AND amount > 0 AND pi_date IS NOT NULL ORDER BY pi_date ASC');
while($rBilling = $db->fetch_array($qBilling)):
	$arrayMonth = functions::insert_array($arrayMonth,$rBilling['pdate']);
endwhile;

#get from voucher
$qVoucherDate1 = $db->query('SELECT left(vd_date,7) as pdate FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" ORDER BY vd_date');
while($rVoucherDate1 = $db->fetch_array($qVoucherDate1)):
	$arrayMonth = functions::insert_array($arrayMonth,$rVoucherDate1['pdate']);
endwhile;

$qVoucherDate = $db->query('SELECT DISTINCT left(po_date,7) as pdate FROM po_item poi, po p where p.po_id=poi.po_id AND p.proj_id="'.$db->clean($project_id).'" ORDER BY `p`.`po_date`');
while($rVoucherDate = $db->fetch_array($qVoucherDate)):
	$arrayMonth = functions::insert_array($arrayMonth,$rVoucherDate['pdate']);
endwhile;

#get From P.O.
#$qPODate = $db->query('SELECT DISTINCT left(po_date,7) as pdate FROM po_item poi, po p where p.po_id=poi.po_id AND p.proj_id="'.$db->clean($project_id).'" AND vp_id IS NOT NULL ORDER BY `p`.`po_date`');
$qPODate = $db->select('view_po_payment','DISTINCT left(po_date,7) as pdate',array('proj_id'=>$project_id),'AND paid > 0');
while($rPODate = $db->fetch_array($qPODate)):
	$arrayMonth = functions::insert_array($arrayMonth,$rPODate['pdate']);
endwhile;


#get From Warehouse.
$qWarehouse = $db->query('SELECT DISTINCT left(im_date,7) as pdate FROM inhouse_material WHERE proj_id="'.$db->clean($project_id).'"');
while($rWarehouse = $db->fetch_array($qWarehouse)):
	$arrayMonth = functions::insert_array($arrayMonth,$rWarehouse['pdate']);
endwhile;


#get From Leasing.
$qLeasing = $db->query('SELECT DISTINCT left(iel_date,7) as pdate FROM inhouse_equip_leasing WHERE proj_id="'.$db->clean($project_id).'"');
while($rLeasing = $db->fetch_array($qLeasing)):
	$arrayMonth = functions::insert_array($arrayMonth,$rLeasing['pdate']);
endwhile;


#get From Voucher Transaction
$qOverhead = $db->query('SELECT DISTINCT category_id FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND itd.expense_type="overhead" AND cost_type="operating expenses" ORDER BY itd.name');
while($rOverhead = $db->fetch_array($qOverhead)):
	$q = $db->query('SELECT DISTINCT left(vd_date,7) as pdate FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND vd.category_id="'.$rOverhead['category_id'].'" ORDER BY vd_date');
	while($r = $db->fetch_array($q)):
		$arrayMonth = functions::insert_array($arrayMonth,$r['pdate']);
	endwhile;
endwhile;

#print_r($arrayMonth);
sort($arrayMonth);
foreach($arrayMonth as $mnth):
	$arrMonth[] = $mnth;
	$arrDirectCost[$mnth]=0;
	$arrNetBilled[$mnth]=0;    
endforeach;

function yearAmnt($field,$project_id){
	global $db;
	$arr=array();
	$q = $db->select('project_income','left(pi_date,7) dte, sum('.$field.') amnt',array('proj_id'=>$project_id),'GROUP BY dte');
	while($r = $db->fetch_array($q)):
		$arr[$r['dte']]=$r['amnt'];
	endwhile;
	return $arr;
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
	<style>.padleft{padding-right: 5px;}
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
			<h2><i class="halflings-icon white th"></i><span class="break"></span>Comparative Income Statement & Retained Earnings</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="dash-proj-income-statement-yearly.php">Yearly Report</a></li>
				<li><a href="dash-proj-income-statement-all.php">All Projects</a></li>
				<li class="active"><a href="dash-proj-income-statement.php" style="opacity:.9">Selected Project</a></li>
			</ul>
		</div>
		<div align="left"><br>&nbsp;&nbsp;
			<select name="selProj" id="selProj" data-rel="chosen" style="width:850px;font-size:12px;height:50px;" onChange="projSel(this.value)">
				<option value="">-- Select Project --</option>
				<?php $qProj = $db->select('project','*',array('project'=>'1'),'ORDER BY proj_name');
				while($rProj = $db->fetch_array($qProj)):
				?>
				<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($project_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
				<?php endwhile;?>
			</select><br><br>
		</div>
		<div class="table-wrapper">
			<table class="table-hover" width="100%" border="1" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th height="30px">Revenue</th>
						<?php foreach($arrMonth as $month):?>
						<th><div align="center"><?php echo monthDisp($month)?></div></th>
						<?php endforeach;?>
						<th class="padleft"><div align="right">Total</div></th>
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
					<?php $arrCostIncome = yearAmnt($field='amount',$project_id); ?>
					<tr>
						<td>Gross Billed Amount</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$costIncome=0;
							$costIncome = isset($arrCostIncome[$monthIncome]) ? $arrCostIncome[$monthIncome] : 0;$totalIncome += $costIncome;
							?>  
							<?php if($costIncome){?>
							<div align="right"><a id="update<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Income Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-income.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Income Detail','1')"><?php echo functions::formatMoney($costIncome);?></a></div>        
							<?php }?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalIncome);?></div></td>
					</tr>
					<tr>
						<td colspan="<?php echo count($arrMonth) + 2?>">Less: Deductions</td>
					</tr>
					<?php $arrNetBilledQ = yearAmnt($field='vat',$project_id); ?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;VAT</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$vat = isset($arrNetBilledQ[$monthIncome]) ? $arrNetBilledQ[$monthIncome] : 0;
							$totalVAT += $vat;
							$arrNetBilled[$monthIncome] += $vat;
							?>
							<?php if( $vat ){?>
							<div align="right"><a id="vatupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="Show VAT Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-vat.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','VAT Detail','1')"><?php echo functions::formatMoney($vat);?></a></div>        
							<?php }?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalVAT);?></div></td>
					</tr>
					<?php $arrNetBilledQ = yearAmnt($field='ewt',$project_id); ?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;EWT</td>
						<?php foreach($arrMonth as $monthIncome):?>
							<td class="padleft">
							<?php
							$ewt = isset($arrNetBilledQ[$monthIncome]) ? $arrNetBilledQ[$monthIncome] : 0;
							$totalEWT += $ewt;
							$arrNetBilled[$monthIncome] += $ewt;
							?>
							<?php if( $ewt ){?>
							<div align="right"><a id="ewtupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View EWT Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-ewt.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','EWT Detail','1')"><?php echo functions::formatMoney($ewt);?></a></div>        
							<?php }?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEWT);?></div></td>
					</tr>
					<?php $arrNetBilledQ = yearAmnt($field='retention',$project_id); ?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Retention</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$retention = isset($arrNetBilledQ[$monthIncome]) ? $arrNetBilledQ[$monthIncome] : 0;
							$totalRetention += $retention;
							$arrNetBilled[$monthIncome] +=  $retention;
							?>
							<?php if( $retention ){?>
							<div align="right"><a id="retupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Retention Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-retention.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Retention Detail','1')"><?php echo functions::formatMoney($retention);?></a></div>        
							<?php }?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRetention);?></div></td>
					</tr>
					<?php $arrNetBilledQ = yearAmnt($field='contractor',$project_id); ?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Contractor's Tax</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$contractor = isset($arrNetBilledQ[$monthIncome]) ? $arrNetBilledQ[$monthIncome] : 0;
							$totalConTax += $contractor;
							$arrNetBilled[$monthIncome] +=  $contractor;
							?>
							<?php if( $contractor ){?>
							<div align="right"><a id="contupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Contractor's Tax" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-contax.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Contractor Tax Detail','1')"><?php echo functions::formatMoney($contractor);?></a></div>        
							<?php }?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConTax);?></div></td>
					</tr>
					<?php $arrNetBilledQ = yearAmnt($field='recoupment',$project_id); ?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Recoupment</td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<?php
							$recoupment = isset($arrNetBilledQ[$monthIncome]) ? $arrNetBilledQ[$monthIncome] : 0;
							$totalRecoupment += $recoupment;
							$arrNetBilled[$monthIncome] +=  $recoupment;
							?>
							<?php if( $recoupment ){?>
							<div align="right"><a id="recoupupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Recoupment Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-recoup.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Recoupment Detail','1')"><?php echo functions::formatMoney($recoupment);?></a></div>        
							<?php }?>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRecoupment);?></div></td>
					</tr>
					<?php $arrNetBilledQ = yearAmnt($field='amount',$project_id); ?>
					<tr>
						<td><strong>Net Billed Amount</strong></td>
						<?php foreach($arrMonth as $monthIncome):?>
						<td class="padleft">
							<div align="right">
								<strong>
									<?php
									$totalDeductions += $arrNetBilled[$monthIncome];
									$costIncome = isset($arrNetBilledQ[$monthIncome]) ? $arrNetBilledQ[$monthIncome] : 0;
									$totalNetBilled += $costIncome - $arrNetBilled[$monthIncome];
									echo functions::formatMoney($costIncome - $arrNetBilled[$monthIncome]);
									?>
								</strong>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalNetBilled);?></strong></div></td>
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
					<?php
					$arrCostLabor=array();
					$qCostLabor = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) as dte, sum(amount) as amnt',array('proj_id'=>$project_id,'itd.expense_type'=>'labor'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rCL = $db->fetch_array($qCostLabor)):
						$arrCostLabor[$rCL['dte']]=$rCL['amnt'];
					endwhile;
					
					$arrPOCostLabor=array();
					$qPOLabor = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) as dte, round(sum(cost),2) as amnt',array('p.proj_id'=>$project_id,'itd.expense_type'=>'labor'),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rPCL = $db->fetch_array($qPOLabor)):
						$arrPOCostLabor[$rPCL['dte']]=$rPCL['amnt'];
					endwhile;

					$arrIMCostLabor=array();
					$qIMLabor = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) as dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.expense_type'=>'labor'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rIML = $db->fetch_array($qIMLabor)):
						$arrIMCostLabor[$rIML['dte']]=$rIML['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Labor</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costLabor=0;
							$costLabor = isset($arrCostLabor[$month]) ? $arrCostLabor[$month] : 0;

							$costLabor += isset($arrPOCostLabor[$month]) ? $arrPOCostLabor[$month] : 0;

							$costLabor += isset($arrIMCostLabor[$month]) ? $arrIMCostLabor[$month] : 0;

							$totalLabor += $costLabor;
							$arrDirectCost[$month] += $costLabor;
							?>
							<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("labor");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costLabor) ? functions::formatMoney($costLabor) : '';?></a>
						</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("labor");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalLabor);?></a></div></td>
					</tr>
					<?php
					$arrVMCost=array();
					$qVoucherMaterial = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) as dte, sum(amount) as amnt',array('proj_id'=>$project_id,'itd.expense_type'=>'materials'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rVM = $db->fetch_array($qVoucherMaterial)):
						$arrVMCost[$rVM['dte']]=$rVM['amnt'];
					endwhile;

					$arrPOMCost=array();
					$qPOMaterial = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) as dte, round(sum(cost),2) as amnt',array('itd.expense_type'=>'materials','p.proj_id'=>$project_id),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rPOM = $db->fetch_array($qPOMaterial)):
						$arrPOMCost[$rPOM['dte']]=$rPOM['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Materials</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
							<?php
							$costMaterial=0;
							$costMaterial += isset($arrVMCost[$month]) ? $arrVMCost[$month] : 0;
							$costMaterial += isset($arrPOMCost[$month]) ? $arrPOMCost[$month] : 0;

							$totalMaterial += $costMaterial;
							$arrDirectCost[$month] += $costMaterial;
							?>
							<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("materials");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costMaterial) ? functions::formatMoney($costMaterial) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("materials");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalMaterial);?></a></div></td>
					</tr>
					<?php
					$arrCostMaterialIH=array();
					$qInhouseMaterialIH = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) as dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.expense_type'=>'materials'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rIMIH = $db->fetch_array($qInhouseMaterialIH)):
						$arrCostMaterialIH[$rIMIH['dte']]=$rIMIH['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Materials (In House)</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costMaterialIH = isset($arrCostMaterialIH[$month]) ? $arrCostMaterialIH[$month] : 0;
								#$qInhouseMaterialIH = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.expense_type="materials"');
								#$costMaterialIH = $db->result($qInhouseMaterialIH,0);

								$totalMaterialIH += $costMaterialIH;
								$arrDirectCost[$month] += $costMaterialIH;
								?>
								<a id="material<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("materials");?>&mon=<?php echo functions::encode($month);?>','MATERIAL IN-HOUSE','1')"><?php echo ($costMaterialIH) ? functions::formatMoney($costMaterialIH) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailIH<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("materials");?>','MATERIAL IN-HOUSE','1')"><?php echo functions::formatMoney($totalMaterialIH);?></a></div></td>
					</tr>
					<?php
					$arrVECost=array();
					$qVoucherEquipment = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) as dte ,sum(amount) as amnt',array('proj_id'=>$project_id,'itd.expense_type'=>'equipment'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rVEC = $db->fetch_array($qVoucherEquipment)):
						$arrVECost[$rVEC['dte']]=$rVEC['amnt'];
					endwhile;

					$arrPOECost=array();
					$qPOEquip = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) as dte, round(sum(cost),2) as amnt',array('p.proj_id'=>$project_id,'itd.expense_type'=>'equipment'),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rPOEC = $db->fetch_array($qPOEquip)):
						$arrPOECost[$rPOEC['dte']]=$rPOEC['amnt'];
					endwhile;

					$arrIHMCost=array();
					$qPOEquip = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) as dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.expense_type'=>'equipment'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rPOIMC = $db->fetch_array($qPOEquip)):
						$arrIHMCost[$rPOIMC['dte']]=$rPOIMC['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Equipment/Rental</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costEquipment=0;
								#$qVoucherEquipment = $db->query('SELECT sum(amount) as amnt FROM voucher_detail vd, item_deduction id WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND itd.expense_type="equipment" ');
								#$costEquipment += $db->result($qVoucherEquipment,0);
								$costEquipment += isset($arrVECost[$month]) ? $arrVECost[$month] : 0;

								#$qPOEquip = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="equipment" AND vpp.paid > 0');
								#$costEquipment += $db->result($qPOEquip,0);
								$costEquipment += isset($arrPOECost[$month]) ? $arrPOECost[$month] : 0;

								$qInhouseMaterialEquipment = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.expense_type="equipment"');
								#$costEquipment += $db->result($qInhouseMaterialEquipment,0);
								$costEquipment += isset($arrIHMCost[$month]) ? $arrIHMCost[$month] : 0;

								$totalEquipment += $costEquipment;
								$arrDirectCost[$month] += $costEquipment;
								?>
								<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("equipment");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costEquipment) ? functions::formatMoney($costEquipment) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("equipment");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalEquipment);?></a></div></td>
					</tr>
					<?php
					$arrIHRCost=array();
					$qInhouseRental = $db->select('inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli','left(iel_date,7) as dte, sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as amnt',array('proj_id'=>$project_id),'AND iel.iel_id=ieli.iel_id GROUP BY dte');
					while($rIHR = $db->fetch_array($qInhouseRental)):
						$arrIHRCost[$rIHR['dte']]=$rIHR['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Equipment Rental (In-House)</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costEquipmentIH = isset($arrIHRCost[$month]) ? $arrIHRCost[$month] : 0;
								$totalEquipmentIH += $costEquipmentIH;
								$arrDirectCost[$month] += $costEquipmentIH;
								?>
								<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("equipment");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costEquipmentIH) ? functions::formatMoney($costEquipmentIH) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotalIH<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("equipment");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalEquipmentIH);?></a></div></td>
					</tr>
					<?php
					
					$arrCostCommitment=array();
					$qCommitment = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) as dte, round(sum(amount),2) as amnt',array('proj_id'=>$project_id,'itd.cost_type'=>'commitment'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rCC = $db->fetch_array($qCommitment)):
						$arrCostCommitment[$rCC['dte']]=$rCC['amnt'];
					endwhile;

					$arrPOCostCommitment=array();
					$qPOCommit = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) as dte, round(sum(cost),2) as amnt',array('proj_id'=>$project_id,'itd.cost_type'=>'commitment'),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rPOC = $db->fetch_array($qPOCommit)):
						$arrPOCostCommitment[$rPOC['dte']]=$rPOC['amnt'];
					endwhile;

					$arrIHMCCommitment=array();
					$qInhouseMaterialCommit = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) as dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.cost_type'=>'commitment'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rIMHC = $db->fetch_array($qInhouseMaterialCommit)):
						$arrIHMCCommitment[$rIMHC['dte']]=$rIMHC['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Commitment</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costCommitment=0;
								$costCommitment = isset($arrCostCommitment[$month]) ? $arrCostCommitment[$month] : 0;

								$costCommitment += isset($arrPOCostCommitment[$month]) ? $arrPOCostCommitment[$month] : 0;

								$costCommitment += isset($arrIHMCCommitment[$month]) ? $arrIHMCCommitment[$month] : 0;

								$totalCommitment += $costCommitment;

								$arrDirectCost[$month] += $costCommitment;
								?>
								<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("commitment");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costCommitment) ? functions::formatMoney($costCommitment) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("commitment");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalCommitment);?></a></div></td>
					</tr>
					<?php
					#$db->select('','',array(''=>,''=>''),'');
					$arrCostFinder=array();
					$qq = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) dte, round(sum(amount),2) as amnt',array('proj_id'=>$project_id,'itd.cost_type'=>'finders fee'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrCostFinder[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrPOCostFinder=array();
					$qq = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) dte, round(sum(cost),2) as amnt',array('p.proj_id'=>$project_id,'itd.cost_type'=>'finders fee'),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrPOCostFinder[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrIHCostFinder=array();
					$qq = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.cost_type'=>'finders fee'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrIHCostFinder[$rr['dte']]=$rr['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Finder's Fee</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costFinder=0;
								#$qFinder = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND itd.cost_type="finders fee"');
								#$costFinder = $db->result($qFinder,0);
								$costFinder = isset($arrCostFinder[$month]) ? $arrCostFinder[$month] : 0;

								#$qPOFinder = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="finders fee" AND vpp.paid > 0');
								#$costFinder += $db->result($qPOFinder,0);
								$costFinder += isset($arrPOCostFinder[$month]) ? $arrPOCostFinder[$month] : 0;

								#$qInhouseMaterialFinder = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.cost_type="finders fee"');
								#$costFinder += $db->result($qInhouseMaterialFinder,0);
								$costFinder += isset($arrIHCostFinder[$month]) ? $arrIHCostFinder[$month] : 0;

								$totalFinder += $costFinder;

								$arrDirectCost[$month] += $costFinder;
								?>
								<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("finders fee");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costFinder) ? functions::formatMoney($costFinder) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("finders fee");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalFinder);?></a></div></td>
					</tr>
					<?php
					#$db->select('','',array(''=>,''=>''),'');
					$arrCostConsultancy=array();
					$qq = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) dte, round(sum(amount),2) as amnt',array('proj_id'=>$project_id,'itd.cost_type'=>'consultancy'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrCostConsultancy[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrPOCostConsultancy=array();
					$qq = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) dte, round(sum(cost),2) as amnt',array('p.proj_id'=>$project_id,'itd.cost_type'=>'consultancy'),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrPOCostConsultancy[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrIHCostConsultancy=array();
					$qq = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.cost_type'=>'consultancy'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrIHCostConsultancy[$rr['dte']]=$rr['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Consultancy</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								#$costConsultancy=0;
								#$qConsultancy = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND itd.cost_type="consultancy"');
								#$costConsultancy = $db->result($qConsultancy,0);
								$costConsultancy = isset($arrCostConsultancy[$month]) ? $arrCostConsultancy[$month] : 0;

								#$qPOConsultancy = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="consultancy" AND vpp.paid > 0');
								#$costConsultancy += $db->result($qPOConsultancy,0);
								$costConsultancy += isset($arrPOCostConsultancy[$month]) ? $arrPOCostConsultancy[$month] : 0;

								#$qInhouseMaterialConsultancy = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.cost_type="consultancy"');
								#$costConsultancy += $db->result($qInhouseMaterialConsultancy,0);
								$costConsultancy += isset($arrIHCostConsultancy[$month]) ? $arrIHCostConsultancy[$month] : 0;

								$totalConsultancy += $costConsultancy;

								$arrDirectCost[$month] += $costConsultancy;
								?>
								<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("consultancy");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costConsultancy) ? functions::formatMoney($costConsultancy) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("consultancy");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalConsultancy);?></a></div></td>
					</tr>
					<?php
					$arrCostTF=array();
					$qq = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) dte, round(sum(amount),2) as amnt',array('proj_id'=>$project_id,'itd.cost_type'=>'technical fee'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrCostTF[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrPOCostTechnical=array();
					$qq = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) dte, round(sum(cost),2) as amnt',array('p.proj_id'=>$project_id,'itd.cost_type'=>'technical fee'),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrPOCostTechnical[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrIHCostTechnical=array();
					$qq = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.cost_type'=>'technical fee'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrIHCostTechnical[$rr['dte']]=$rr['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Technical Fee</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costTechnical=0;
								$costTechnical = isset($arrCostTF[$month]) ? $arrCostTF[$month] : 0;

								$costTechnical += isset($arrPOCostTechnical[$month]) ? $arrPOCostTechnical[$month] : 0;

								$costTechnical += isset($arrIHCostTechnical[$month]) ? $arrIHCostTechnical[$month] : 0;

								$totalTechnical += $costTechnical;

								$arrDirectCost[$month] += $costTechnical;
								?>
								<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("technical fee");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costTechnical) ? functions::formatMoney($costTechnical) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("technical fee");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalTechnical);?></a></div></td>
					</tr>
					<?php
					$arrCostSubcon=array();
					$qq = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) dte, round(sum(amount),2) as amnt',array('proj_id'=>$project_id,'itd.expense_type'=>'subcon'),'AND vd.category_id=itd.item_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrCostSubcon[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrPOCostSubcon=array();
					$qq = $db->select('po p, view_po_payment vpp, item_deduction itd','left(p.po_date,7) dte, round(sum(cost),2) as amnt',array('p.proj_id'=>$project_id,'itd.expense_type'=>'subcon'),'AND p.po_id=vpp.po_id AND p.category_id=itd.item_id AND vpp.paid > 0 GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrPOCostSubcon[$rr['dte']]=$rr['amnt'];
					endwhile;

					$arrIHCostSubcon=array();
					$qq = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) dte, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id,'itd.expense_type'=>'subcon'),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id GROUP BY dte');
					while($rr = $db->fetch_array($qq)):
						$arrIHCostSubcon[$rr['dte']]=$rr['amnt'];
					endwhile;
					?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;Subcon</td>
						<?php foreach($arrMonth as $month):?>
						<td class="padleft">
							<div align="right">
								<?php
								$costSubcon=0;
								$costSubcon = isset($arrCostSubcon[$month]) ? $arrCostSubcon[$month] : 0;

								$costSubcon += isset($arrPOCostSubcon[$month]) ? $arrPOCostSubcon[$month] : 0;

								$costSubcon += isset($arrIHCostSubcon[$month]) ? $arrIHCostSubcon[$month] : 0;

								$totalSubcon += $costSubcon;

								$arrDirectCost[$month] += $costSubcon;
								?>
								<a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("subcon");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($costSubcon) ? functions::formatMoney($costSubcon) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("subcon");?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalSubcon);?></a></div></td>
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
					<?php #die(); ?>
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

					$overVoucher = $db->query('SELECT DISTINCT category_id FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" ORDER BY itd.name');
					while($rOverVoucher = $db->fetch_array($overVoucher)):
						$arrCategory = functions::insert_array($arrCategory,$rOverVoucher['category_id']);
					endwhile;

					$overPO = $db->query('SELECT DISTINCT category_id FROM po p, view_po_payment vpp, item_deduction itd WHERE p.category_id=itd.item_id AND p.po_id=vpp.po_id AND p.proj_id="'.$db->clean($project_id).'" AND paid > 0 AND itd.expense_type="overhead" AND itd.cost_type="operating expenses" ORDER BY itd.name');
					while($rOverPO = $db->fetch_array($overPO)):
						$arrCategory = functions::insert_array($arrCategory,$rOverPO['category_id']);
					endwhile;

					$overInhouseMaterial = $db->query('SELECT DISTINCT category_id FROM inhouse_material im, item_deduction itd WHERE im.category_id=itd.item_id AND im.proj_id="'.$db->clean($project_id).'" AND itd.expense_type="overhead" AND itd.cost_type="operating expenses"');
					while($rOverInhouseMaterial = $db->fetch_array($overInhouseMaterial)):
						$arrCategory = functions::insert_array($arrCategory,$rOverInhouseMaterial['category_id']);
					endwhile;
					//$arrCategory=array('10'=>'10');

					if(count($arrCategory)){
						$qSort = 'SELECT DISTINCT item_id FROM item_deduction';
						$countSort=0;
						foreach($arrCategory as $cat):
							if($countSort==0)
								$qSort .= ' WHERE item_id="'.$cat.'" ';
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
					#$q = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND vd.category_id="'.$categoryID.'" AND left(vd_date,7)="'.$month.'"');
					$arrCatVoucherCost=array();
					$qq = $db->select('voucher_detail vd, item_deduction itd','left(vd_date,7) as dte, vd.category_id as cat, round(sum(amount),2) as amnt',array('proj_id'=>$project_id),'AND vd.category_id=itd.item_id GROUP BY dte, cat');
					while($rr = $db->fetch_array($qq)):
						$arrCatVoucherCost[$rr['dte']][$rr['cat']]=$rr['amnt'];
					endwhile;
					
					$arrCatPOCost=array();
					#$qPOCat = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp WHERE p.po_id=vpp.po_id AND p.proj_id="'.$db->clean($project_id).'" AND p.category_id="'.$categoryID.'" AND left(p.po_date,7)="'.$month.'" AND vpp.paid > 0');
					$qq = $db->select('po p, view_po_payment vpp','left(p.po_date,7) as dte, p.category_id as cat, round(sum(cost),2) as amnt',array('p.proj_id'=>$project_id),'AND p.po_id=vpp.po_id AND vpp.paid > 0 GROUP BY dte, cat');
					while($rr = $db->fetch_array($qq)):
						$arrCatPOCost[$rr['dte']][$rr['cat']]=$rr['amnt'];
					endwhile;


					#$qInhouseMaterialCat = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND im.category_id="'.$categoryID.'"');
					$arrCatIHCost=array();
					$qq = $db->select('inhouse_material im, inhouse_material_item imi, item_deduction itd','left(im_date,7) as dte, im.category_id as cat, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt',array('im.proj_id'=>$project_id),'AND im.category_id=itd.item_id AND im.im_id=imi.im_id');
					while($rr = $db->fetch_array($qq)):
						$arrCatIHCost[$rr['dte']][$rr['cat']]=$rr['amnt'];
					endwhile;
					//die();
					foreach( $arrCategory as $categoryID):
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
								// $categoryCost=0;
								// #$q = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND vd.category_id="'.$categoryID.'" AND left(vd_date,7)="'.$month.'"');
								// #$categoryCost = $db->result($q,0);
								$categoryCost = isset( $arrCatVoucherCost[$month][$categoryID] ) ? $arrCatVoucherCost[$month][$categoryID] : 0;

								// #$qPOCat = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp WHERE p.po_id=vpp.po_id AND p.proj_id="'.$db->clean($project_id).'" AND p.category_id="'.$categoryID.'" AND left(p.po_date,7)="'.$month.'" AND vpp.paid > 0');
								// #$categoryCost += $db->result($qPOCat,0);
								$categoryCost += isset( $arrCatPOCost[$month][$categoryID] ) ? $arrCatPOCost[$month][$categoryID] : 0;

								// #$qInhouseMaterialCat = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND im.category_id="'.$categoryID.'"');
								// #$categoryCost += $db->result($qInhouseMaterialCat,0);
								$categoryCost += isset( $arrCatIHCost[$month][$categoryID] ) ? $arrCatIHCost[$month][$categoryID] : 0;

								$arrOverheadCost[$month] += $categoryCost;
								$totalCategoryCost += $categoryCost;
								$totalOverheadCost += $categoryCost;
								?>
								<a id="costdetail<?php echo $rOverhead['category_id'].$countID;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&cid=<?php echo functions::encode($categoryID);?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')"><?php echo ($categoryCost) ? functions::formatMoney($categoryCost) : '';?></a>
							</div>
						</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><a id="costTotaldetail<?php echo $rOverhead['category_id'].$countID;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&cid=<?php echo functions::encode($categoryID);?>','Project Cost Details','1')"><?php echo functions::formatMoney($totalCategoryCost);?></a></div></td>
					</tr>
					<?php endforeach;?>
					<tr>
						<td><strong>Total Operating Expenses</strong></td>
						<?php
						$countOhCost=0;
						foreach($arrOverheadCost as $ohCost): $countOhCost++;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($ohCost);?></strong></div></td>
						<?php
						endforeach;
						if(count($arrOverheadCost)==0){
							foreach($arrMonth as $monthIncome):
								echo '<td class="padleft"><div align="right"><strong>0.00</strong></div></td>';
							endforeach;
						}
						?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalOverheadCost);?></strong></div></td>
					</tr>
					<tr>
						<td><strong>Total Expenses</strong></td>
						<?php foreach($arrMonth as $month):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalDirectCost + $totalOverheadCost);?></strong></div></td>
					</tr>
					<tr>
						<td><strong>Net Income</strong></td>
						<?php foreach($arrMonth as $month):?>
						<td>&nbsp;</td>
						<?php endforeach;?>
						<td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($totalIncome - ($totalDirectCost + $totalOverheadCost + $totalDeductions));?></strong></div></td>
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
<script>function projSel(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?prjID="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>