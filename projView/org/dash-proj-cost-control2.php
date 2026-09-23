<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="9" ){
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
  $arrVal = array('incharge'=>$user_id);
  $proj_date = (isset($_REQUEST['pdt']) && !empty($_REQUEST['pdt']) ) ? functions::decode($_REQUEST['pdt']) : '';
  if($proj_date)
    $arrVal = array('left(date_contract,4)'=>$proj_date,'incharge'=>$user_id);
  
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
              <!-- end: Favicon -->

  </head>
  <body>
      <div class="row-fluid">
        
        <div class="box span12">
          <div class="box-header">
            <h2><i class="halflings-icon white th"></i><span class="break"></span>Project Cost Control Report</h2>
          </div>
          <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
              <li class="active"><a href="#">Cost View</a></li>
              <li><a href="dash-proj-cost-control.php?pdt=<?php echo functions::encode($proj_date);?>">Deductions</a></li>
            </ul>
          </div>
                <div class="box-content" align="center">
                    <div>
                    <table width="29%" border="0">
                      <tr>
                        <td width="32%" valign="middle">Contract Year: </td>
                        <td width="68%" valign="middle">
                            <select name="proj_yr" id="proj_yr" onChange="selDt(this.value)">
                            <option value="">-- All Projects --</option>
                            <?php
                                $qYr = $db->query('SELECT DISTINCT left(date_contract,4) as dt FROM `project` where incharge="'.$db->clean($user_id).'" AND date_contract IS NOT NULL ORDER BY date_contract DESC ');
                                while($rYr = $db->fetch_array($qYr)):
                            ?>
                             <option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($proj_date==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
                             <?php endwhile;?>
                             </select>
                        </td>
                      </tr>
                    </table>
                  </div>  
                      <table border="1" style="font-size:13px; font-family:Tahoma;">
                        <?php
                            $qProj = $db->select('project','*',$arrVal,'ORDER BY proj_name');
                            $num_record = $db->getValue('project','count(*)',$arrVal);
                            $totalProj_cost=0; $totalVat=0; $totalEwt=0; $totalMpF=0; $totalGenover=0; $totalProfit=0; $totalTechnical=0; $totalConsultancy=0; $totalBudget=0; $totalActualOverhead=0; $totalActualMaterial=0; $totalActualEquipment=0; $totalActualLabor=0; $totalActual=0; $totalBalance=0; $totalBudgetOverhead=0; $totalBudgetMaterial=0; $totalBudgetEquipment=0; $totalBudgetLabor=0;
                            $totalCommitment=0; $totalFinders=0;
                            while($rProj = $db->fetch_array($qProj)):
                                  $amount=0;
                                  $po_amount=0;
                                  $totalProj_cost += $proj_cost = $rProj['proj_cost'];
                                  $totalVat += $totalVat = $vat12 = ($proj_cost / 1.12) * .12;
                                  $totalEwt += $ewt = ($proj_cost / 1.12) * .02;
                                  $totalMpF += $mpFee = $proj_cost * .008;
                                  $totalGenover += $genover = $proj_cost * .12;
                                  $totalProfit += $profit = $proj_cost * .10;
                                  $totalCommitment += $commitment = $proj_cost * ($rProj['commitment'] / 100);
                                  $totalConsultancy += $consultancy = $proj_cost * ($rProj['consultancy'] / 100);
                                  $totalFinders += $finders = $proj_cost * ($rProj['finders'] / 100);
                                  $totalTechnical += $technical = $proj_cost * ($rProj['technical'] / 100);

                                  $totalBudget += $budget = $proj_cost - ($vat12 + $ewt + $mpFee + $genover + $profit + $commitment + $consultancy + $finders + $technical);
                                  
                                  $totalBudgetOverhead += $budgetOverhead = $budget * .1;
                                  $totalBudgetMaterial += $budgetMaterial = $budget * .6;
                                  $totalBudgetEquipment += $budgetEquipment = $budget * .1;
                                  $totalBudgetLabor += $budgetLabor = $budget * .2;
                                  
                                  $qOverhead = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="overhead" AND cost_type="operating expenses"');
                                  #$qOverhead = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="overhead"');
                                  $totalActualOverhead += $actualOverhead = $db->result($qOverhead);
                                  $materialPO=0;
                                  $materialVo=0;
                                  $qMaterials = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="materials"');
                                  $qMat = $db->last_query;
                                  $materialVo = $db->result($qMaterials);

                                  $qPO = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) as res FROM po_item poi, po p where p.po_id=poi.po_id AND p.proj_id="'.$db->clean($rProj['proj_id']).'"');
                                  $qP = $db->last_query;
                                  $materialPO = $db->result($qPO,0);
                                  $totalActualMaterial += $actualMaterial = $materialPO + $materialVo;


                                  $qEquipment = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="equipment"');
                                  $totalActualEquipment += $actualEquipment = $db->result($qEquipment);


                                  $qLabor = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="labor"');
                                  $totalActualLabor += $actualLabor = $db->result($qLabor,0);
      
                                  $totalActual += $AllActual = $actualMaterial + $actualLabor + $actualEquipment + $actualOverhead;
                                  
                                  $totalBalance += $balance = $budget - $AllActual;

                                    ?>
                                      <tr>
                                          <td colspan="16" height="35"><strong><?php echo $rProj['proj_name'];?></strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" width="20%"><div align="center"><strong>Project Overhead</strong></div></td>
                                            <td colspan="3" width="20%"><div align="center"><strong>Materials</strong></div></td>
                                            <td colspan="3" width="20%"><div align="center"><strong>Equipment</strong></div></td>
                                            <td colspan="3" width="20%"><div align="center"><strong>Labor</strong></div></td>
                                            <td width="5%"><div align="center"><strong>Budget</strong></div></td>
                                            <td width="5%"><div align="center"><strong>Total<br>Spent</strong></div></td>
                                            <td width="5%"><div align="center"><strong>%</strong></div></td>
                                            <td width="5%"><div align="center"><strong>Gain / Loss</strong></div></td>
                                        </tr>
                                        <tr>
                                          <td width="8%">Budget</td>
                                          <td width="8%">Actual</td>
                                          <td width="4%"><div align="center">%</div></td>
                                          <td width="8%">Budget</td>
                                          <td width="8%">Actual</td>
                                          <td width="4%"><div align="center">%</div></td>
                                          <td width="8%">Budget</td>
                                          <td width="8%">Actual</td>
                                          <td width="4%"><div align="center">%</div></td>
                                          <td width="8%">Budget</td>
                                          <td width="8%">Actual</td>
                                          <td width="4%"><div align="center">%</div></td>
                                          <td>&nbsp;</td>
                                          <td>&nbsp;</td>
                                          <td>&nbsp;</td>
                                          <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><?php echo functions::formatMoney($budgetOverhead);?></td>
                                            <td><?php echo functions::formatMoney($actualOverhead);?></td>
                                            <td><?php echo ($budgetOverhead) ? functions::formatMoney(($actualOverhead / $budgetOverhead) * 100) : 0;?>%</td>
                                            <td><?php echo functions::formatMoney($budgetMaterial);?></td>
                                            <td><?php echo functions::formatMoney($actualMaterial);?></td>
                                            <td><?php echo ($budgetMaterial) ? functions::formatMoney(($actualMaterial / $budgetMaterial) * 100) : 0;?>%</td>
                                            <td><?php echo functions::formatMoney($budgetEquipment);?></td>
                                            <td><?php echo functions::formatMoney($actualEquipment);?></td>
                                            <td><?php echo ($budgetEquipment) ? functions::formatMoney(($actualEquipment / $budgetEquipment) * 100) : 0;?>%</td>
                                            <td><?php echo functions::formatMoney($budgetLabor);?></td>
                                            <td><?php echo functions::formatMoney($actualLabor);?></td>
                                            <td><?php echo ($budgetLabor) ? functions::formatMoney(($actualLabor / $budgetLabor) * 100) : 0;?>%</td>
                                            <td><?php echo functions::formatMoney($budget);?></td>
                                            <td><?php echo functions::formatMoney($AllActual);?></td>
                                            <td><div align="center"><?php echo ($budget) ? round(($AllActual / $budget) * 100,2) : 0;?>%</div></td>
                                            <td><?php echo functions::formatMoney($balance);?></td>
                                        </tr>
                                      <tr>
                                          <td colspan="16" height="50" valign="center">&nbsp;</td>
                                        </tr>
                                    <?php endwhile;?>
                                        <tr>
                                          <td width="8%" height="40"><strong><?php echo functions::formatMoney($totalBudgetOverhead);?></strong></td>
                                          <td width="8%"><strong><?php echo functions::formatMoney($totalActualOverhead);?></strong></td>
                                          <td width="4%">&nbsp;</td>
                                          <td width="8%"><strong><?php echo functions::formatMoney($totalBudgetMaterial);?></strong></td>
                                          <td width="8%"><strong><?php echo functions::formatMoney($totalActualMaterial);?></strong></td>
                                          <td width="4%">&nbsp;</td>
                                          <td width="8%"><strong><?php echo functions::formatMoney($totalBudgetEquipment);?></strong></td>
                                          <td width="8%"><strong><?php echo functions::formatMoney($totalActualEquipment);?></strong></td>
                                          <td width="4%">&nbsp;</td>
                                          <td width="8%"><strong><?php echo functions::formatMoney($totalBudgetLabor);?></strong></td>
                                          <td width="8%"><strong><?php echo functions::formatMoney($totalActualLabor);?></strong></td>
                                          <td width="4%">&nbsp;</td>
                                          <td><strong><?php echo functions::formatMoney($totalBudget);?></strong></td>
                                          <td><strong><?php echo functions::formatMoney($totalActual);?></strong></td>
                                          <td>&nbsp;</td>
                                          <td><strong><?php echo functions::formatMoney($totalBalance);?></strong></td>
                                        </tr>
                                </table>      

        </div><!--/span-->
      
      </div><!--/row--> 

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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>