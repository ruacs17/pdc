<?php
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  die();
echo 'Start : '. date('H:i:s');
#for multiple po_item_id
$qq = $db->query('SELECT po_id,count(po_id) FROM `po_fuel_equip` group by po_id having count(po_id) > 1');
$item='';$quantity='';$unit='';$brand='';$cost='';$discount='';$new_po_item_id=0;
while($rr = $db->fetch_array($qq)):
  $po_id = $rr['po_id'];

  $qPOI = $db->select('po_item','*',array('po_id'=>$po_id));
  while($rPOI = $db->fetch_array($qPOI)):
      $item = $rPOI['item'];
      $quantity = $rPOI['quantity'];
      $unit = $rPOI['unit'];
      $brand = $rPOI['brand'];
      $cost = $rPOI['cost'];
      $discount = $rPOI['discount'];
  endwhile;
  $arrPO_Item = array('po_id'=>$po_id,'item'=>$item,'quantity'=>$quantity,'qty_delivered'=>$quantity,'unit'=>$unit,'brand'=>$brand,'cost'=>$cost,'discount'=>$discount);
  $db->delete('po_item',array('po_id'=>$po_id));

  $q = $db->select('po_fuel_equip','*',array('po_id'=>$po_id));
  while ( $r = $db->fetch_array($q) ):

    $equip_id = ($r['equip_id']) ? $r['equip_id'] : '';
    $other_equip = ($r['other_equip']) ? $r['other_equip'] : '';
    $po_id = $r['po_id'];
      
    $qInsertPOITEM = $db->insertPrint('po_item',$arrPO_Item);
    #echo '<br>';
    $db->query($qInsertPOITEM);
    $new_po_item_id = $db->insert_id();

    $arrPFE = array('po_id'=>$po_id,'po_item_id'=>$new_po_item_id,'equip_id'=>$equip_id,'other_equip'=>$other_equip);
    $qInsertPFE = $db->insertPrint('po_fuel_equipment',$arrPFE);
    $db->query($qInsertPFE);

    #echo '<br>';
    #echo '<br>';
  endwhile;

#echo '<br>';
endwhile;





#for single po_item_id




$qqq = $db->query('SELECT po_id,count(po_id) FROM `po_fuel_equip` group by po_id having count(po_id) = 1');
$item='';$quantity='';$unit='';$brand='';$cost='';$discount='';$new_po_item_id=0;
while($rr = $db->fetch_array($qqq)):
  $po_id = $rr['po_id'];

  $q = $db->select('po_fuel_equip','*',array('po_id'=>$rr['po_id']));
  while ( $r = $db->fetch_array($q)):

    $pfe_id = $r['pfe_id'];
    $equip_id = ($r['equip_id']) ? $r['equip_id'] : '';
    $other_equip = ($r['other_equip']) ? $r['other_equip'] : '';
    $po_id = $r['po_id'];

    $qPOI = $db->select('po_item','*',array('po_id'=>$po_id));
    while($rPOI = $db->fetch_array($qPOI)):
      $po_item_id = $rPOI['po_item_id'];
      $item = $rPOI['item'];
      $quantity = $rPOI['quantity'];
      $unit = $rPOI['unit'];
      $brand = $rPOI['brand'];
      $cost = $rPOI['cost'];
      $discount = $rPOI['discount'];

      $arrPFE = array('po_id'=>$po_id,'po_item_id'=>$po_item_id,'equip_id'=>$equip_id,'other_equip'=>$other_equip);

      #echo '<br>';
      $db->insert('po_fuel_equipment',$arrPFE);
    endwhile;

    #echo '<br>';
  endwhile;
#echo '<br>';
endwhile;


echo '<br>End : '. date('H:i:s');
?>