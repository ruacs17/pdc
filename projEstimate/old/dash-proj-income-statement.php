<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="12" ){
  header("Location: ../");
  die();
}
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
$qBilling = $db->select('project_income','DISTINCT left(pi_date,7) as pdate',array('proj_id'=>$project_id),'AND amount > 0 ORDER BY pi_date ASC');
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
      <div class="row-fluid">
        
        <div class="box span12">
          <div class="box-header">
            <h2><i class="halflings-icon white th"></i><span class="break"></span>Comparative Income Statement & Retained Earnings</h2>
          </div>
<div><br>Project: <strong><?php echo $db->getValue('project','proj_name',array('proj_id'=>$project_id))?></strong><br><br></div>
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
              $costIncome = $db->getValue('project_income','sum(amount)',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
              $incomeID = $db->getValue('project_income','pi_id',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
              $totalIncome += $costIncome;
        
        ?>  
        <?php if($costIncome){?>
              <div align="right">
                <a id="update<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Income Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-income.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Income Detail','1')">
                  <?php echo functions::formatMoney($costIncome);?>
                </a>
              </div>        
        <?php }?>
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
        <?php
              $vat = $db->getValue('project_income','sum(vat)',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
              $totalVAT += $vat;
              $arrNetBilled[$monthIncome] += $vat;
        ?>
        <?php if( $vat ){?>
              <div align="right">
                <a id="vatupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="Show VAT Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-vat.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','VAT Detail','1')">
                  <?php echo functions::formatMoney($vat);?>
                </a>
              </div>        
        <?php }?>
    </td>
    <?php endforeach;?>
    <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalVAT);?></div></td>
  </tr>

  <tr>
    <td>&nbsp;&nbsp;&nbsp;EWT</td>
    <?php foreach($arrMonth as $monthIncome):?>
    <td class="padleft">
        <?php
            $ewt = $db->getValue('project_income','sum(ewt)',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
            $totalEWT += $ewt;
            $arrNetBilled[$monthIncome] +=  $ewt;
        ?>
        <?php if( $ewt ){?>
              <div align="right">
                <a id="ewtupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View EWT Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-ewt.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','EWT Detail','1')">
                  <?php echo functions::formatMoney($ewt);?>
                </a>
              </div>        
        <?php }?>
    </td>
    <?php endforeach;?>
    <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalEWT);?></div></td>
  </tr>

  <tr>
    <td>&nbsp;&nbsp;&nbsp;Retention</td>
    <?php foreach($arrMonth as $monthIncome):?>
    <td class="padleft">
        <?php
            $retention = $db->getValue('project_income','sum(retention)',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
            $totalRetention += $retention;
            $arrNetBilled[$monthIncome] +=  $retention;
        ?>
        <?php if( $retention ){?>
              <div align="right">
                <a id="retupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Retention Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-retention.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Retention Detail','1')">
                  <?php echo functions::formatMoney($retention);?>
                </a>
              </div>        
        <?php }?>
    </td>
    <?php endforeach;?>
    <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRetention);?></div></td>
  </tr>
  <tr>
    <td>
    &nbsp;&nbsp;&nbsp;Contractor's Tax
    </td>
    <?php foreach($arrMonth as $monthIncome):?>
    <td class="padleft">
        <?php
            $contractor=0;
            $contractor = $db->getValue('project_income','sum(contractor)',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
            $totalConTax += $contractor;
            $arrNetBilled[$monthIncome] +=  $contractor;
        ?>
        <?php if( $contractor ){?>
              <div align="right">
                <a id="contupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Contractor's Tax" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-contax.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Contractor Tax Detail','1')">
                  <?php echo functions::formatMoney($contractor);?>
                </a>
              </div>        
        <?php }?>
    </td>
    <?php endforeach;?>
    <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalConTax);?></div></td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Recoupment</td>
    <?php foreach($arrMonth as $monthIncome):?>
    <td class="padleft">
        <?php
            $recoupment=0;
            $recoupment = $db->getValue('project_income','sum(recoupment)',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
            $totalRecoupment += $recoupment;
            $arrNetBilled[$monthIncome] +=  $recoupment;
        ?>
        <?php if( $recoupment ){?>
              <div align="right">
                <a id="recoupupdate<?php echo $monthIncome?>" class="thickbox" style="cursor: pointer;" title="View Recoupment Detail" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-recoup.php?prjID=<?php echo functions::encode($project_id);?>&mnth=<?php echo functions::encode($monthIncome);?>','Recoupment Detail','1')">
                  <?php echo functions::formatMoney($recoupment);?>
                </a>
              </div>        
        <?php }?>
    </td>
    <?php endforeach;?>
    <td class="padleft"><div align="right"><?php echo functions::formatMoney($totalRecoupment);?></div></td>
  </tr>
  <tr>
    <td><strong>Net Billed Amount</strong></td>
    <?php foreach($arrMonth as $monthIncome):?>
    <td>
    <div align="right"><strong>
  <?php
      $totalDeductions += $arrNetBilled[$monthIncome];
      $costIncome = $db->getValue('project_income','sum(amount)',array('left(pi_date,7)'=>$monthIncome,'proj_id'=>$project_id));
      $totalNetBilled += $costIncome - $arrNetBilled[$monthIncome];
      echo functions::formatMoney($costIncome - $arrNetBilled[$monthIncome]);
  ?>
    </strong></div>
    </td>
    <?php endforeach;?>
    <td><div align="right"><strong><?php echo functions::formatMoney($totalNetBilled);?></strong></div></td>
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
            $qVoucherLabor = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id WHERE vd.category_id=id.item_id AND proj_id="'.$db->clean($project_id).'"AND left(vd_date,7)="'.$month.'" AND id.expense_type="labor" ');
            $costLabor = $db->result($qVoucherLabor,0);

            $qPOLabor = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="labor" AND vpp.paid > 0');
            $costLabor += $db->result($qPOLabor,0);

            $qInhouseMaterialLabor = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.expense_type="labor"');
            $costLabor += $db->result($qInhouseMaterialLabor,0);

            $totalLabor += $costLabor;
            $arrDirectCost[$month] += $costLabor;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("labor");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costLabor) ? functions::formatMoney($costLabor) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("labor");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalLabor);?>
          </a>
      </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Materials</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costMaterial=0;
            $qVoucherMaterial = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id WHERE vd.category_id=id.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND id.expense_type="materials"');
            $costMaterial += $db->result($qVoucherMaterial,0);

            $qPO = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="materials" AND vpp.paid > 0');
            $costMaterial += $db->result($qPO,0);

            $totalMaterial += $costMaterial;
            $arrDirectCost[$month] += $costMaterial;
            
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("materials");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costMaterial) ? functions::formatMoney($costMaterial) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("materials");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalMaterial);?>
          </a>
      </div>
    </td>
  </tr>
  </tr>
    <tr>
    <td>&nbsp;&nbsp;&nbsp;Materials (In House)</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costMaterialIH=0;

            $qInhouseMaterialIH = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.expense_type="materials"');
            $costMaterialIH = $db->result($qInhouseMaterialIH,0);

            $totalMaterialIH += $costMaterialIH;
            $arrDirectCost[$month] += $costMaterialIH;  
        ?>
            <a id="material<?php echo $month?>" class="thickbox" style="cursor: pointer;" title="View Detail" data-rel="tooltip" onClick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("materials");?>&mon=<?php echo functions::encode($month);?>','MATERIAL IN-HOUSE','1')">
              <?php echo ($costMaterialIH) ? functions::formatMoney($costMaterialIH) : '';?>
            </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailIH<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("materials");?>','MATERIAL IN-HOUSE','1')">
              <?php echo functions::formatMoney($totalMaterialIH);?>
          </a>
      </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Equipment/Rental</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costEquipment=0;
            $qVoucherEquipment = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id WHERE vd.category_id=id.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND id.expense_type="equipment" ');
            $costEquipment += $db->result($qVoucherEquipment,0);

            $qPOEquip = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="equipment" AND vpp.paid > 0');
            $costEquipment += $db->result($qPOEquip,0);

            $qInhouseMaterialEquipment = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.expense_type="equipment"');
            $costEquipment += $db->result($qInhouseMaterialEquipment,0);

            $totalEquipment += $costEquipment;
            $arrDirectCost[$month] += $costEquipment;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("equipment");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costEquipment) ? functions::formatMoney($costEquipment) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("equipment");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalEquipment);?>
          </a>
      </div>
    </td>
  </tr>
    <tr>
    <td>&nbsp;&nbsp;&nbsp;Equipment Rental (In-House)</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costEquipmentIH=0;

            $qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND proj_id="'.$db->clean($project_id).'" AND left(iel_date,7)="'.$month.'"');
            $costEquipmentIH = $db->result($qInhouseRental,0);

            $totalEquipmentIH += $costEquipmentIH;
            $arrDirectCost[$month] += $costEquipmentIH;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("equipment");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costEquipmentIH) ? functions::formatMoney($costEquipmentIH) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotalIH<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail-inhouse.php?pid=<?php echo functions::encode($project_id);?>&ih=<?php echo functions::encode("equipment");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalEquipmentIH);?>
          </a>
      </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Commitment</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costCommitment=0;
            $qCommitment = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'"AND left(vd_date,7)="'.$month.'" AND itd.cost_type="commitment"');
            $costCommitment = $db->result($qCommitment,0);

            $qPOCommit = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="commitment" AND vpp.paid > 0');
            $costCommitment += $db->result($qPOCommit,0);

            $qInhouseMaterialCommit = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.cost_type="commitment"');
            $costCommitment += $db->result($qInhouseMaterialCommit,0);

            $totalCommitment += $costCommitment;

            $arrDirectCost[$month] += $costCommitment;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("commitment");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costCommitment) ? functions::formatMoney($costCommitment) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("commitment");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalCommitment);?>
          </a>
      </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Finder's Fee</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costFinder=0;
            $qFinder = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND itd.cost_type="finders fee"');
            $costFinder = $db->result($qFinder,0);

            $qPOFinder = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="finders fee" AND vpp.paid > 0');
            $costFinder += $db->result($qPOFinder,0);

            $qInhouseMaterialFinder = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.cost_type="finders fee"');
            $costFinder += $db->result($qInhouseMaterialFinder,0);

            $totalFinder += $costFinder;

            $arrDirectCost[$month] += $costFinder;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("finders fee");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costFinder) ? functions::formatMoney($costFinder) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("finders fee");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalFinder);?>
          </a>
      </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Consultancy</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costConsultancy=0;
            $qConsultancy = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND itd.cost_type="consultancy"');
            $costConsultancy = $db->result($qConsultancy,0);

            $qPOConsultancy = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="consultancy" AND vpp.paid > 0');
            $costConsultancy += $db->result($qPOConsultancy,0);

            $qInhouseMaterialConsultancy = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.cost_type="consultancy"');
            $costConsultancy += $db->result($qInhouseMaterialConsultancy,0);

            $totalConsultancy += $costConsultancy;

            $arrDirectCost[$month] += $costConsultancy;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("consultancy");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costConsultancy) ? functions::formatMoney($costConsultancy) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("consultancy");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalConsultancy);?>
          </a>
      </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Technical Fee</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costTechnical=0;
            $qTechnical = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND itd.cost_type="technical fee"');
            $costTechnical = $db->result($qTechnical,0);

            $qPOTechnical = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.cost_type="technical fee" AND vpp.paid > 0');
            $costTechnical += $db->result($qPOTechnical,0);

            $qInhouseMaterialTechnical = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.cost_type="technical fee"');
            $costTechnical += $db->result($qInhouseMaterialTechnical,0);

            $totalTechnical += $costTechnical;

            $arrDirectCost[$month] += $costTechnical;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("technical fee");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costTechnical) ? functions::formatMoney($costTechnical) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&ct=<?php echo functions::encode("technical fee");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalTechnical);?>
          </a>
      </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;Subcon</td>
    <?php foreach($arrMonth as $month):?>
    <td class="padleft">
        <div align="right">
        <?php
            $costSubcon=0;
            $qSubcon = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND left(vd_date,7)="'.$month.'" AND itd.expense_type="subcon"');
            $costSubcon = $db->result($qSubcon,0);

            $qPOSubcon = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp, item_deduction itd WHERE p.po_id=vpp.po_id AND p.category_id=itd.item_id AND p.proj_id="'.$db->clean($project_id).'" AND left(p.po_date,7)="'.$month.'" AND itd.expense_type="subcon" AND vpp.paid > 0');
            $costSubcon += $db->result($qPOSubcon,0);

            $qInhouseMaterialSubcon = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND itd.expense_type="subcon"');
            $costSubcon += $db->result($qInhouseMaterialSubcon,0);

            $totalSubcon += $costSubcon;

            $arrDirectCost[$month] += $costSubcon;
        ?>
          <a id="costdetail<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("subcon");?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($costSubcon) ? functions::formatMoney($costSubcon) : '';?>
          </a>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <a id="costdetailTotal<?php echo $countID++;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&et=<?php echo functions::encode("subcon");?>','Project Cost Details','1')">
              <?php echo functions::formatMoney($totalSubcon);?>
          </a>
      </div>
    </td>
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
          $categoryCost=0;
          $q = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($project_id).'" AND vd.category_id="'.$categoryID.'" AND left(vd_date,7)="'.$month.'"');
          $categoryCost = $db->result($q,0);
          

          $qPOCat = $db->query('SELECT round(sum(cost),2) FROM po p, view_po_payment vpp WHERE p.po_id=vpp.po_id AND p.proj_id="'.$db->clean($project_id).'" AND p.category_id="'.$categoryID.'" AND left(p.po_date,7)="'.$month.'" AND vpp.paid > 0');
          $categoryCost += $db->result($qPOCat,0);

          $qInhouseMaterialCat = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.proj_id="'.$db->clean($project_id).'" AND left(im_date,7)="'.$month.'" AND im.category_id="'.$categoryID.'"');
          $categoryCost += $db->result($qInhouseMaterialCat,0);


          $arrOverheadCost[$month] += $categoryCost;
          $totalCategoryCost += $categoryCost;
          $totalOverheadCost += $categoryCost;
      ?>
          <a id="costdetail<?php echo $rOverhead['category_id'].$countID;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&cid=<?php echo functions::encode($categoryID);?>&mon=<?php echo functions::encode($month);?>','Project Cost Details','1')">
              <?php echo ($categoryCost) ? functions::formatMoney($categoryCost) : '';?>
          </a>
      </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
        <a id="costTotaldetail<?php echo $rOverhead['category_id'].$countID;?>" class="thickbox" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-detail.php?pid=<?php echo functions::encode($project_id);?>&cid=<?php echo functions::encode($categoryID);?>','Project Cost Details','1')">
        <?php echo functions::formatMoney($totalCategoryCost);?>
        </a>
      </div>
    </td>
  </tr>
<?php endforeach;?>
  <tr>
    <td><strong>Total Operating Expenses</strong></td>
    <?php $countOhCost=0; foreach($arrOverheadCost as $ohCost): $countOhCost++;?>
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
<script>function projSel(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?prjID="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>