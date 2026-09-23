<?php
  require_once('../class/database.php');
  $db_bk = new Database('localhost','root','phildb123*','pdc_backup');
  $db = new Database();


  $qbk = $db_bk->query('SELECT * FROM po');
  while($rbk = $db_bk->fetch_array($qbk)):
    $po_id = $rbk['po_id'];
    $invoice = $rbk['invoice'];
    #echo $db->updatePrint('po',array('invoice'=>$invoice),array('po_id'=>$po_id));
    #echo '<br>';

  endwhile;
?>