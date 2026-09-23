<?php require_once('templ_up.php');?>
<?php

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
#echo functions::formatMoney(allDepreciation('2018'));


  $jan1Dep=0;
  $dec31Dep=0;
  $qEquipYr = $db->query('SELECT DISTINCT substring(date_acquired,1,4) as yr FROM equipment WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" ORDER BY date_acquired');
  while($rEquipYr = $db->fetch_array($qEquipYr)):
        $jan1 = $db->getValue('equipment','sum(price * quantity)',array(),'WHERE substring(date_acquired,1,4) < "'.$rEquipYr['yr'].'" AND classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00"');
        $additions = $db->getValue('equipment','sum(price * quantity)',array(),'WHERE substring(date_acquired,1,4) = "'.$rEquipYr['yr'].'" AND classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00"');
        $dec31 = $db->getValue('equipment','sum(price * quantity)',array(),'WHERE substring(date_acquired,1,4) <= "'.$rEquipYr['yr'].'" AND classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00"');
        /*$qAdditionDep = $db->query('SELECT equip_id,date_acquired, proposed_life, ('.$rEquipYr['yr'].' - substring(date_acquired,1,4) ) * ((price * quantity) / (substring(proposed_life,1,4) - substring(date_acquired,1,4)) ) as decrm FROM `equipment` WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" AND price <> "" AND substring(proposed_life,1,4)-substring(date_acquired,1,4) >= (('.$rEquipYr['yr'].' - substring(date_acquired,1,4))) and ('.$rEquipYr['yr'].' - substring(date_acquired,1,4)) >= 0');
        #$qAdditionDep = $db->query('SELECT sum('.$rEquipYr['yr'].' - substring(date_acquired,1,4) ) * (price / (substring(proposed_life,1,4) - substring(date_acquired,1,4)) ) as decrm FROM `equipment` WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" and price <> "" and substring(proposed_life,1,4)-substring(date_acquired,1,4) >= ('.$rEquipYr['yr'].' - substring(date_acquired,1,4)) and ('.$rEquipYr['yr'].' - substring(date_acquired,1,4)) >= 0');
          $additionDepreciation=0;
            while($rAdditionDep = $db->fetch_array($qAdditionDep)):
                  $additionDepreciation += $rAdditionDep['decrm'];
            endwhile;*/
            $additionDepreciation = depreciationAll($rEquipYr['yr']);
            #$additionDepreciation = depreciationCurrent($rEquipYr['yr']);
          $dec31Dep = $jan1Dep + $additionDepreciation;
          $netValue = $dec31 - $dec31Dep;
          $arr_fs[] = array('year'=>$rEquipYr['yr'],'jan1'=>$jan1,'additions'=>$additions,'dec31'=>$dec31,'jan1Dep'=>$jan1Dep,'additionDepreciation'=>$additionDepreciation,'dec31Dep'=>$dec31Dep,'netValue'=>$netValue);
        $jan1Dep += $additionDepreciation;
  endwhile;
  functions::sortMultiArray($arr_fs,'year','DESC');
?>
<style>
.tdSpace{padding: 3px;}
</style>
<!-- body content: start here-->
<form method="post">
  <div class="row-fluid">
      <div class="box span12">
          <div class="box-header" data-original-title>
              <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Equipments Inventory Report</h2>
          </div>
          <br>
      <div>

      <div>
        <table border="1" style="font-size: 12px">
          <tr>
            <?php
              $countCategory=0;
              $arrCategory=array();
              $allCost=0;
              $qCategory = $db->query('SELECT DISTINCT classification FROM equipment WHERE classification <> "" ORDER BY classification');
              while($rCat = $db->fetch_array($qCategory)):
                $countCategory++;
                $arrCategory[]=$rCat['classification'];
            ?>
            <th><?php echo $rCat['classification']?></th>
            <?php endwhile;?>
            <th>Cost</th>
            <th>Year</th>
          </tr>
          <?php
            $classificationCost=0;
            $qEquipYr = $db->query('SELECT DISTINCT substring(date_acquired,1,4) as yr FROM equipment WHERE classification <> "" AND date_acquired <> "" AND date_acquired <> "0000-00-00" ORDER BY date_acquired DESC');
            while($rEquipYr = $db->fetch_array($qEquipYr)):
          ?>
          <tr>
            <?php 
            $classificationCost=0;
            foreach($arrCategory as $classification):
              $cost = $db->getValue('equipment','sum(price * quantity)',array('classification'=>$classification),'AND substring(date_acquired,1,4)="'.$rEquipYr['yr'].'" AND price <> ""');
              $classificationCost+=$cost;
              $allCost += $cost;
            ?>
            <td class="tdSpace"><?php echo ($cost) ? functions::formatMoney($cost) : '';?></td>
          <?php endforeach;?>
            <td class="tdSpace"><?php echo functions::formatMoney($classificationCost);?></td>
            <td class="tdSpace"><?php echo $rEquipYr['yr']?></td>
          </tr>
        <?php endwhile;?>
          <tr>
            <?php foreach($arrCategory as $classification): ?>
            <td class="tdSpace"><strong><?php echo functions::formatMoney($db->getValue('equipment','sum(price * quantity)',array('classification'=>$classification)))?></strong></td>
          <?php endforeach;?>
            <td class="tdSpace"><strong><?php echo functions::formatMoney($allCost);?></strong></td>
            <td>&nbsp;</td>
          </tr>
        </table>
      </div>



      <br><br><br>
      <div>
        <table border="0" style="font-size: 12px" class="table">
          <tr>
            <td colspan="5"><div align="center">COST</div></td>
            <td colspan="6"><div align="center">DEPRECIATION</div></td>
          </tr>
          <tr>
            <th>Year</th>
            <th>January 01</th>
            <th>Additions</th>
            <th>Disposal</th>
            <th>December 31</th>
            <th>&nbsp;</th>
            <th>January 01</th>
            <th>Additions</th>
            <th>Disposal</th>
            <th>December 31</th>
            <th>Net Value</th>
          </tr>
          <?php
              foreach($arr_fs as $fs):
          ?>
          <tr>
            <td><?php echo $fs['year']?></td>
            <td><?php echo functions::formatMoney($fs['jan1'])?></td>
            <td><?php echo functions::formatMoney($fs['additions'])?></td>
            <td></td>
            <td><?php echo functions::formatMoney($fs['dec31'])?></td>
            <td><div align="left">|</div></td>
            <td><?php echo functions::formatMoney($fs['jan1Dep'])?></td>
            <td><?php echo functions::formatMoney($fs['additionDepreciation']);?></td>
            <td></td>
            <td><?php echo functions::formatMoney($fs['dec31Dep'])?></td>
            <td><strong><?php echo functions::formatMoney($fs['netValue'])?></strong></td>
          </tr>
        <?php endforeach;?>
        </table>

      </div>
  </div>
</form>
<!-- body content: end here-->
<?php require_once('templ_down.php');?>