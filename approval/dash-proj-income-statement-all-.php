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
$totalLabor=0;  $totalMaterial=0; $totalEquipment=0;  $totalCommitment=0; $totalFinder=0; $totalTechnical=0; $totalConsultancy=0; $totalDirectCost=0; $totalOverheadCost=0; $totalOperatingExpenses=0; $totalIncome=0; $totalNetBilled=0;
$countID=0;$totalVAT=0;$totalEWT=0;$totalRetention=0;$totalConTax=0;$totalRecoupment=0; $totalDeductions=0;

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
            <h2><i class="halflings-icon white th"></i><span class="break"></span>Comparative Income Statement & Retained Earnings of All Projects</h2>
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
                </table>
                <br>
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
            $qVoucherLabor = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND project=0 AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="labor"');
            $totalLabor += $costLabor = $db->result($qVoucherLabor,0);
            $arrDirectCost[$month] += $costLabor;
            echo ($costLabor) ? functions::formatMoney($costLabor) : '';
        ?>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <?php echo functions::formatMoney($totalLabor);?>
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
            $qVoucherMaterial = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND project=0 AND vd.category_id=id.item_id AND left(vd_date,7)="'.$month.'" AND id.expense_type="materials"');
            $costMaterial += $db->result($qVoucherMaterial,0);

            $qPO = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) as res FROM po_item poi, po p, project pj WHERE p.proj_id=pj.proj_id AND project=0 AND p.po_id=poi.po_id AND left(po_date,7)="'.$month.'" AND vp_id IS NOT NULL');
            $costMaterial += $db->result($qPO,0);
            $totalMaterial += $costMaterial;
            $arrDirectCost[$month] += $costMaterial;
            echo ($costMaterial) ? functions::formatMoney($costMaterial) : '';  
        ?>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <?php echo functions::formatMoney($totalMaterial);?>
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
            $qVoucherEquipment = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND project=0 AND left(vd_date,7)="'.$month.'" AND id.expense_type="equipment"');
            $totalEquipment += $costEquipment = $db->result($qVoucherEquipment,0);
            $arrDirectCost[$month] += $costEquipment;
            echo ($costEquipment) ? functions::formatMoney($costEquipment) : '';
        ?>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <?php echo functions::formatMoney($totalEquipment);?>
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
            $qCommitment = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND project=0 AND left(vd_date,7)="'.$month.'" AND itd.cost_type="commitment"');
            $totalCommitment += $costCommitment = $db->result($qCommitment,0);
            $arrDirectCost[$month] += $costCommitment;
            echo ($costCommitment) ? functions::formatMoney($costCommitment) : '';
        ?>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
        <?php echo functions::formatMoney($totalCommitment);?>
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
            $qFinder = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND project=0 AND left(vd_date,7)="'.$month.'" AND itd.cost_type="finders fee"');
            $totalFinder += $costFinder = $db->result($qFinder,0);
            $arrDirectCost[$month] += $costFinder;
            echo ($costFinder) ? functions::formatMoney($costFinder) : '';
        ?>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
        <?php echo functions::formatMoney($totalFinder);?>
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
            $qConsultancy = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND project=0 AND left(vd_date,7)="'.$month.'" AND itd.cost_type="consultancy"');
            $totalConsultancy += $costConsultancy = $db->result($qConsultancy,0);
            $arrDirectCost[$month] += $costConsultancy;
            echo ($costConsultancy) ? functions::formatMoney($costConsultancy) : '';
        ?>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
        <?php echo functions::formatMoney($totalConsultancy);?>
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
            $qTechnical = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND project=0 AND left(vd_date,7)="'.$month.'" AND itd.cost_type="technical fee"');
            $totalTechnical += $costTechnical = $db->result($qTechnical,0);
            $arrDirectCost[$month] += $costTechnical;
            echo ($costTechnical) ? functions::formatMoney($costTechnical) : '';
        ?>
        </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
          <?php echo functions::formatMoney($totalTechnical);?>
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
    <td class="padleft">
      <div align="right">
        <strong>
          <?php 
              #$totalDirectCost += $prevTotalDirectCost;
              echo functions::formatMoney($totalDirectCost);
          ?>
        </strong>
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
    <td colspan="<?php echo count($arrMonth) + 2?>">Less: Operating Expenses</td>
  </tr>
<?php
  $prevTotalOperatingExpenses = 0;
  $qOverhead = $db->query('SELECT DISTINCT category_id FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND project=0 AND itd.expense_type="overhead" AND cost_type="operating expenses" AND left(vd.vd_date,4)="'.$year.'" ORDER BY itd.name');
  while($rOverhead = $db->fetch_array($qOverhead)):
    $totalCategoryCost=0;
?>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;<?php echo $db->getValue('item_deduction','name',array('item_id'=>$rOverhead['category_id']));?></td>
    <?php foreach($arrMonth as $month):
    $countID++;
        if( !isset($arrOverheadCost[$month]) )
            $arrOverheadCost[$month]=0;
    ?>
    <td class="padleft">
      <div align="right">
      <?php
          $categoryCost=0;
          $q = $db->query('SELECT round(sum(amount),2) FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND project=0 AND vd.category_id="'.$rOverhead['category_id'].'" AND left(vd_date,7)="'.$month.'"');
          $categoryCost = $db->result($q,0);
          $arrOverheadCost[$month] += $categoryCost;
          $totalCategoryCost += $categoryCost;
          $totalOverheadCost += $categoryCost;
          $totalOperatingExpenses += $categoryCost;
          echo ($categoryCost) ? functions::formatMoney($categoryCost) : '';
      ?>
      </div>
    </td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
        <?php echo functions::formatMoney($totalCategoryCost);?>
      </div>
    </td>
  </tr>
<?php endwhile;?>
  <tr>
    <td><strong>Total Operating Expenses</strong></td>
    <?php foreach($arrOverheadCost as $ohCost):?>
    <td class="padleft"><div align="right"><strong><?php echo ($ohCost) ? functions::formatMoney($ohCost) : '';?></strong></div></td>
    <?php endforeach;?>
    <td class="padleft">
      <div align="right">
        <strong>
          <?php
           echo functions::formatMoney($totalOverheadCost);
           ?>
        </strong>
      </div>
    </td>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?yr="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>