<div style="font-family: Tahoma; font-size: 14px">
<?php 
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();

    $qItem = $db->select('po_item','item',array(),'ORDER BY item');
    while($rItem=$db->fetch_array($qItem)):
        $oldString=$rItem['item'];
        $string = preg_replace("/'/",'"',$rItem['item']);
        $newString = trim(preg_replace('/\s\s+/', ' ', $string));

        if( functions::encode($oldString)!= functions::encode($newString) ){
          echo $oldString.' | '.$newString;
          $db->update('po_item',array('item'=>$newString),array('item'=>$oldString));
          echo '<br>';
        }
    endwhile;


/*$qItem = $db->select('po_item','DISTINCT item',array(),'ORDER BY item');
  while($rItem=$db->fetch_array($qItem)):
    echo $rItem['item'];
    echo '<br>';
  endwhile;*/
/*
$qItem = $db->select('qry','*',array(),'ORDER BY id');
  while($rItem=$db->fetch_array($qItem)):
    echo $rItem['stmnt'];
    echo '<br>';
  endwhile;
  */  
?>
</div>


