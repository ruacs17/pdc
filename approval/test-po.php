<?php

  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();

#$db->query('UPDATE po SET po_no=ref_id');
$qRefID = $db->select('po','DISTINCT ref_id',array());
while($rRefID = $db->fetch_array($qRefID)):
  $refID = $rRefID['ref_id'];

  if( $db->getValue('po','count(*)',array('ref_id'=>$refID)) > 1 ){
  $qShow = $db->select('po','*',array('ref_id'=>$refID));
  $count=0;
  while($rShow = $db->fetch_array($qShow)):
    $count++;
    $db->update('po',array('po_no'=>$refID.'-'.$count),array('po_id'=>$rShow['po_id']));
    #echo $rShow['po_id'].' - '.$rShow['po_date'].' - '. $rShow['ref_id']. ' - '.$rShow['po_no'];
  #echo '<br>';
  endwhile;
  }

  #echo '<br>';
endwhile;
?>