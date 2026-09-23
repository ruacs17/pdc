<?php
class PO_history{
	private static function insertDeleteItem($po_item_id,$user_id,$act_mode){
		require_once('database.php');
		$db = new Database();
		$po_id = $db->getValue('po p, po_item pi','DISTINCT p.po_id',array('po_item_id'=>$po_item_id),'AND p.po_id=pi.po_id');
		$po_no = $db->getValue('po','po_no',array('po_id'=>$po_id));
		$po_type = $db->getValue('po','po_type',array('po_id'=>$po_id));
		$qPODetails = $db->select('po_item','*',array('po_item_id'=>$po_item_id));
		$content='';
		while($rPODetails = $db->fetch_array($qPODetails)):
			if($po_type=='service'){
				$content .='Item: '.$rPODetails['item'];				
			}
			else{
				$content .='Item: '.$rPODetails['item'].' <br> Qty Request: '.$rPODetails['quantity'].' <br> Qty Delivered: '.$rPODetails['qty_delivered'];
				$content .= ($rPODetails['unit']) ? ' <br> Unit: '.$rPODetails['unit'] : ' <br> Unit: --';
				$content .= ($rPODetails['brand']) ? ' <br> Brand: '.$rPODetails['brand'] : ' <br> Brand: --';
			}
			$content .= ($rPODetails['cost']) ? ' <br> Cost: '.functions::formatMoney($rPODetails['cost']) : ' <br> Cost: --';
			$content .= ($rPODetails['discount']) ? ' <br> Discount: '.$rPODetails['discount'].'%' : ' <br> Discount: --';	
		endwhile;
		$db->insert('po_history',array('po_id'=>$po_id,'po_no'=>$po_no,'user_id'=>$user_id,'content'=>$content,'act_mode'=>$act_mode,'act_date'=>date('Y-m-d'),'act_time'=>date('H:i:s')));
	}

	public static function insertItem($po_item_id,$user_id){
		PO_history::insertDeleteItem($po_item_id,$user_id,'add item');
	}
	public static function deleteItem($po_item_id,$user_id){
		PO_history::insertDeleteItem($po_item_id,$user_id,'delete item');
	}

	public static function editItemBefore($po_item_id){
		require_once('database.php');
		$db = new Database();
		$po_id = $db->getValue('po p, po_item pi','DISTINCT p.po_id',array('po_item_id'=>$po_item_id),'AND p.po_id=pi.po_id');
		$po_type = $db->getValue('po','po_type',array('po_id'=>$po_id));
		$qPODetails = $db->select('po_item','*',array('po_item_id'=>$po_item_id));
		$content='';
		while($rPODetails = $db->fetch_array($qPODetails)):
			$content .='Before: <br>';
			if($po_type=='service'){
				$content .='Item: '.$rPODetails['item'];
			}
			else{
				$content .='Item: '.$rPODetails['item'].' <br> Qty Request: '.$rPODetails['quantity'].' <br> Qty Delivered: '.$rPODetails['qty_delivered'];
				$content .= ($rPODetails['unit']) ? ' <br> Unit: '.$rPODetails['unit'] : ' <br> Unit: --';
				$content .= ($rPODetails['brand']) ? ' <br> Brand: '.$rPODetails['brand'] : ' <br> Brand: --';
			}
			$content .= ($rPODetails['cost']) ? ' <br> Cost: '.functions::formatMoney($rPODetails['cost']) : ' <br> Cost: --';
			$content .= ($rPODetails['discount']) ? ' <br> Discount: '.$rPODetails['discount'].'%' : ' <br> Discount: --';
			$content .= ' <br><br> ';
		endwhile;
		return $content;
	}

	public static function editItemAfter($po_item_id,$user_id,$itemBefore){
		require_once('database.php');
		$db = new Database();
		$po_id = $db->getValue('po p, po_item pi','DISTINCT p.po_id',array('po_item_id'=>$po_item_id),'AND p.po_id=pi.po_id');
		$po_no = $db->getValue('po','po_no',array('po_id'=>$po_id));
		$po_type = $db->getValue('po','po_type',array('po_id'=>$po_id));
		$qPODetails = $db->select('po_item','*',array('po_item_id'=>$po_item_id));
		$content=$itemBefore;
		while($rPODetails = $db->fetch_array($qPODetails)):
			$content .='After: <br>';
			if($po_type=='service'){
				$content .='Item: '.$rPODetails['item'];
			}
			else{
				$content .='Item: '.$rPODetails['item'].' <br> Qty Request: '.$rPODetails['quantity'].' <br> Qty Delivered: '.$rPODetails['qty_delivered'];
				$content .= ($rPODetails['unit']) ? ' <br> Unit: '.$rPODetails['unit'] : ' <br> Unit: --';
				$content .= ($rPODetails['brand']) ? ' <br> Brand: '.$rPODetails['brand'] : ' <br> Brand: --';				
			}
			$content .= ($rPODetails['cost']) ? ' <br> Cost: '.functions::formatMoney($rPODetails['cost']) : ' <br> Cost: --';
			$content .= ($rPODetails['discount']) ? ' <br> Discount: '.$rPODetails['discount'].'%' : ' <br> Discount: --';
			$content .= ' <br><br> ';
		endwhile;
		$db->insert('po_history',array('po_id'=>$po_id,'po_no'=>$po_no,'user_id'=>$user_id,'content'=>$content,'act_mode'=>'update item','act_date'=>date('Y-m-d'),'act_time'=>date('H:i:s')));
	}


	private static function poCreateModify($po_id,$user_id,$act_mode){
		require_once('database.php');
		$db = new Database();
		$content='';
		$q = $db->select('po','*',array('po_id'=>$po_id));
		$r = $db->fetch_array($q);
		$content = 'P.O. Date: '.functions::datearr($r['po_date']);
		$content .= '<br> Project: '.$db->getValue('project','proj_name',array('proj_id'=>$r['proj_id']));
		$content .= '<br> Payee/Supplier: '.$db->getValue('supplier','name',array('supplierID'=>$r['supplierID']));
		$content .= '<br> Category: '.$db->getValue('item_deduction','name',array('item_id'=>$r['category_id']));
		$content .= ($r['invoice']) ? '<br> Invoice: '.$r['invoice'] : '<br> Invoice: --';
		
		if($r['po_type']=='fuel'){
			$requested_by = $db->getValue('po_fuel','requested_by',array('po_id'=>$r['po_id']));
			$content .= ($r['proj_detail']) ? "<br> Purpose: ".$r['proj_detail'] : "<br> Purpose: --";
			$content .= '<br> Requested By: '.$db->getValue('employee','concat(lname,", ",fname)',array('emp_id'=>$requested_by));
			$content .= '<br> Served By: '.$db->getValue('users','concat(lname,", ",fname)',array('user_id'=>$r['purchaser']));
		}
		else if($r['po_type']=='service'){
			$content .= ($r['proj_detail']) ? "<br> Purpose: ".$r['proj_detail'] : "<br> Purpose: --";
			$content .= '<br> Maker: '.$db->getValue('users','concat(lname,", ",fname)',array('user_id'=>$r['purchaser']));
		}
		else{
			$content .= ($r['proj_detail']) ? "<br> Additional Desc: ".$r['proj_detail'] : "<br> Additional Desc: --";
			$content .= '<br> Purchaser: '.$db->getValue('users','concat(lname,", ",fname)',array('user_id'=>$r['purchaser']));
		}
		$content .= '<br> Approval: '.$db->getValue('users','concat(lname,", ",fname)',array('user_id'=>$r['approved_by']));
		$content .= ($r['payment_term']) ? '<br> Payment Term: '.$r['payment_term'] : '<br> Payment Term: --';
		$content .= ($r['remarks']) ? '<br> remarks: '.$r['remarks'] : '<br> Remarks: --';
		$db->insert('po_history',array('po_id'=>$r['po_id'],'po_no'=>$r['po_no'],'user_id'=>$user_id,'act_mode'=>$act_mode,'content'=>$content,'act_date'=>date('Y-m-d'),'act_time'=>date('H:i:s')));
	}

	public static function poCreate($po_id,$user_id){
		PO_history::poCreateModify($po_id,$user_id,'create po');
	}

	public static function poModify($po_id,$user_id){
		PO_history::poCreateModify($po_id,$user_id,'modify po');
	}

	public static function poTrash($po_id,$user_id){
		PO_history::poCreateModify($po_id,$user_id,'trash po');
	}

	public static function poPrint($po_id,$user_id){
		require_once('database.php');
		$db = new Database();
		$q = $db->select('po','*',array('po_id'=>$po_id));
		$r = $db->fetch_array($q);
		$db->insert('po_history',array('po_id'=>$r['po_id'],'po_no'=>$r['po_no'],'user_id'=>$user_id,'act_mode'=>'Print','act_date'=>date('Y-m-d'),'act_time'=>date('H:i:s')));
	}
}
?>