<?php require_once('authorize.php');
require_once('../class/database.php');
require_once('../class/functions.php');

$db = new Database();
$chk = (isset($_REQUEST['chk']) && !empty($_REQUEST['chk']) ) ? $_REQUEST['chk'] : 0;
$po_item_id = (isset($_REQUEST['itmid']) && !empty($_REQUEST['itmid']) ) ? functions::decode($_REQUEST['itmid']) : 0;
$qty = (isset($_REQUEST['qty']) && !empty($_REQUEST['qty']) ) ? $_REQUEST['qty'] : 0;
$pos_id = (isset($_REQUEST['posid']) && !empty($_REQUEST['posid']) ) ? functions::decode($_REQUEST['posid']) : 0;
$posd_id = (isset($_REQUEST['posd']) && !empty($_REQUEST['posd']) ) ? functions::decode($_REQUEST['posd']) : 0;
#$posi_id = (isset($_REQUEST['posidid']) && !empty($_REQUEST['posidid']) ) ? functions::decode($_REQUEST['posidid']) : 0;
if( $pos_id && $posd_id){
	$po_id = $db->getValue('po_issuance_details','po_id',array('posd_id'=>$posd_id,'pos_id'=>$pos_id));
	if($chk){
		#echo $posi_id = $db->getValue('po_issuance_item','posi_id',array('pos_id'=>$pos_id,'po_id'=>$po_id,'poi_item_id'=>$po_item_id));
		if( $posi_id = $db->getValue('po_issuance_item','posi_id',array('pos_id'=>$pos_id,'po_id'=>$po_id,'po_item_id'=>$po_item_id)) ){
			$db->delete('po_issuance_item',array('posi_id'=>$posi_id));
		}
		else{
			$db->insert('po_issuance_item',array('posd_id'=>$posd_id,'pos_id'=>$pos_id,'po_id'=>$po_id,'po_item_id'=>$po_item_id,'qty_issue'=>$qty));
		}
		echo 'chk'.$db->last_query;
	}
	else{
		if( $posi_id = $db->getValue('po_issuance_item','posi_id',array('posd_id'=>$posd_id,'pos_id'=>$pos_id,'po_id'=>$po_id,'po_item_id'=>$po_item_id)) )
			$db->update('po_issuance_item',array('qty_issue'=>$qty),array('posi_id'=>$posi_id));
		#echo 'upd ';
	}
}
?>