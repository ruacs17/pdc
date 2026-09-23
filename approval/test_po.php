<?php
die();
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$inserted=0;$count=0;
#$q = $db->query('SELECT * FROM po p where received=1 AND delivery_date IS NOT NULL  ORDER BY po_id');
$q = $db->query('SELECT * FROM po p WHERE receive_no IS NOT NULL ORDER BY receive_no');
while($r = $db->fetch_array($q)):
	$count++;
	$por_id = $db->insert('po_receive',array('ref_id'=>$r['ref_id'],'po_no'=>$r['po_no'],'received'=>$r['received'],'po_type'=>$r['po_type'],'delivery_date'=>$r['delivery_date'],'item_received_by'=>$r['item_received_by'],'item_encoded_by'=>$r['item_encoded_by'],'report_received_by'=>$r['report_received_by'],'receive_no'=>$r['receive_no'],'remarks'=>$r['remarks']));
	if($por_id){
		$inserted++;
		$qi = $db->select('po_item','*',array('po_id'=>$r['po_id']),'ORDER BY po_item_id');
		while($ri = $db->fetch_array($qi)):
			$db->insert('po_receive_item',array('por_id'=>$por_id,'item'=>$ri['item'],'qty_received'=>$ri['qty_delivered'],'unit'=>$ri['unit'],'brand'=>$ri['brand']));
		endwhile;
	}
endwhile;
echo $count.' = '.$inserted;
?>