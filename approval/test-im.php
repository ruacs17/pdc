<?php
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  

$q = $db->select('inhouse_material_item','item,unit,brand',array(),'GROUP BY item,unit,brand');
while($r = $db->fetch_array($q)):

  if( $db->getValue('material_reference','count(*)',array('item'=>$r['item'],'unit'=>$r['unit'],'brand'=>$r['brand']))==0 )
    echo $db->insertPrint('material_reference',array('item'=>$r['item'],'unit'=>$r['unit'],'brand'=>$r['brand'])).';';

echo '<br>';
endwhile;
?>