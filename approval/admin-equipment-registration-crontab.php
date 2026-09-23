<?php
require_once('/home/del/web/class/database.php');
require_once('/home/del/web/class/functions.php');
$db = new Database();
echo 'Start: '.date('Y-m-d H:i:s');
$qEquip = $db->query('SELECT * FROM equipment WHERE reg_renew IS NOT NULL AND status NOT IN ("Trade In","Sold","Inactive","Unserviceable")');
while($r = $db->fetch_array($qEquip)):
	$equip_id=$r['equip_id'];
	$reg_renew = $r['reg_renew'];
	$date_acquired = $r['date_acquired'];
	$acquired_year = date('Y',strtotime($date_acquired));
	$renw = explode('-', $reg_renew);
	$renew_mon = isset($renw[0]) ? $renw[0] : NULL;
	$renew_week = isset($renw[1]) ? $renw[1] : NULL;
	if($renew_week=='01')
		$renew_week='first';
	else if($renew_week=='02')
		$renew_week='second';
	else if($renew_week=='03')
		$renew_week='third';
	else if($renew_week=='04')
		$renew_week='fourth';

	if($acquired_year && $reg_renew){
		$reg_term=date('Y');
		$date_due = date("Y-m-d", strtotime($renew_week." friday ".$reg_term."-".$renew_mon));
		$date_validity = date("Y-m-d", strtotime($renew_week." friday ".($reg_term+1)."-".$renew_mon));
		$date_due_start = date('Y-m-d',strtotime('-2 months',strtotime($date_due)));
		if( $db->getValue('equip_registration','count(*)',array('equip_id'=>$equip_id,'reg_term'=>$reg_term))==0 )
			$db->insert('equip_registration',array('equip_id'=>$equip_id,'reg_term'=>$reg_term,'date_validity'=>$date_validity,'date_due'=>$date_due,'date_due_start'=>$date_due_start,'stat'=>'For Renewal'));
		else
			$db->update('equip_registration',array('date_validity'=>$date_validity,'date_due'=>$date_due,'date_due_start'=>$date_due_start),array('equip_id'=>$equip_id,'reg_term'=>$reg_term));
		echo $db->last_query;echo ' ';
	}
endwhile;

echo ' --- end: '.date('Y-m-d H:i:s');
?>
