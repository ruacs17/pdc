<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$db->utf8();
function array_to_csv_download($array, $filename = "export.csv", $delimiter=",") {
	header('Content-Type: application/csv; charset=ISO-8859-1');
	#header('Content-Type: application/csv; charset=utf8');
	header('Content-Disposition: attachment; filename="'.$filename.'";');
	$f = fopen('php://output', 'w');
	fputs($f, $bom=( chr(0xEF).chr(0xBB).chr(0xBF) ));
	foreach ($array as $line) {
		fputcsv($f, $line, $delimiter);

	}
}

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$txSearch=( isset($_REQUEST['txSrch']) && !empty($_REQUEST['txSrch']) ) ? functions::decode($_REQUEST['txSrch']) : 0;
$orderBy = (isset($_REQUEST['orderBy']) && !empty($_REQUEST['orderBy']) ) ? functions::decode($_REQUEST['orderBy']) : 'type';
$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
$sort_AscDesc="ASC";
if($ascDes==="DESC"){
	$ascDes="ASC";
	$sort_AscDesc="ASC";
}
else if($ascDes==="ASC"){
	$ascDes="DESC";
	$sort_AscDesc="DESC";
}

$count=0;
$search='';
if( $txSearch ){
	$txSearch = $db->clean($txSearch);
	$search = "WHERE type LIKE '%".$txSearch."%' OR inventory_id LIKE '%".$txSearch."%' OR brand LIKE '%".$txSearch."%' OR model LIKE '%".$txSearch."%' OR serial_no LIKE '%".$txSearch."%'";
}

$arrEquipment = array();
$num_record = $db->getValue('equipment','count(*)',array(),$search);
#$q_equip = $db->query("SELECT * FROM equipment ".$search.' LIMIT 10');
$q_equip = isset($_SESSION['equip_list_q']) ? $db->query($_SESSION['equip_list_q']) : $db->query("SELECT * FROM equipment ". $search.'');
#$q_equip = $db->query("SELECT * FROM equipment LIMIT 20");
while($rEquip=$db->fetch_array($q_equip)):
	$equip_id = $rEquip['equip_id'];
	$status = $rEquip['status'];
	$lastDateMaintenance = $db->getValue('equip_repair','er_date',array('equip_id'=>$equip_id),' AND repair_type LIKE "%maintenance%" ORDER BY er_date DESC');
	$qHrs = $db->query('SELECT sum(duration) FROM inhouse_equip_leasing_item ieli, inhouse_equip_leasing iel WHERE ieli.iel_id=iel.iel_id AND ieli.equip_id="'.$db->clean($equip_id).'" AND iel.iel_date > "'.$lastDateMaintenance.'" ');
	$hrsFromLastMaintenance = $db->result($qHrs) * 60;
	$mLimit = $db->getValue('equipment','limit_minutes',array('equip_id'=>$equip_id));
	$maintenanceLimit = (is_numeric($mLimit)) ? $mLimit : 0;
	$lifeYear = $db->getValue('equipment','(datediff(curdate(),"'.$rEquip['date_acquired'].'") / 365) as yr',array(),'LIMIT 1');
	$lifeYearDetail = $db->getValue('equipment',"CONCAT(TIMESTAMPDIFF( YEAR, '".$rEquip['date_acquired']."', now() ),'yr/s ', TIMESTAMPDIFF( MONTH, '".$rEquip['date_acquired']."', now() ) % 12,'mon/s') as AGE",array(),'LIMIT 1');
	$forMaintenance = ( ($maintenanceLimit > 0) && ($hrsFromLastMaintenance > 0) && ($hrsFromLastMaintenance >= $maintenanceLimit) ) ? 1 : 0;
	$loc = $db->query('SELECT * FROM `mr_item` mi, mr WHERE mi.mr_id=mr.mr_id AND equip_id="'.$db->clean($equip_id).'" ORDER BY mr.mr_date DESC');
	$rLoc = $db->fetch_array($loc);
	if($rLoc['returned'] == 1){
		$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
	}
	else
		$location=$rEquip['location']." (in possession)";
	$arrEquipment[] = array('inventory_id'=>$rEquip['inventory_id'],'equip_id'=>$rEquip['equip_id'],'category'=>$rEquip['category'],'type'=>$rEquip['type'],'name'=>$rEquip['name'],'equip_desc'=>$rEquip['equip_desc'],'brand'=>$rEquip['brand'],'serial_no'=>$rEquip['serial_no'],'plate_no'=>$rEquip['plate_no'],'model'=>$rEquip['model'],'remarks'=>$rEquip['remarks'],'location'=>$location,'date_acquired'=>$rEquip['date_acquired'],'price'=>$rEquip['price'] * $rEquip['quantity'],'forMaintenance'=>$forMaintenance,'lifeYear'=>$lifeYear,'lifeYearDetail'=>$lifeYearDetail,'status'=>$status);
endwhile;
if(count($arrEquipment))
	functions::sortMultiArray($arrEquipment,$orderBy,$sort_AscDesc);

#$arrCSV[] = array('SEQUENCE NO.','NAME','DEPARTMENT');
#$qd = $db->query('SELECT ea_id as "seqNo",concat(lname,", ",fname) as empname,ofc.ofc_name as "office" FROM employee emp, emp_attendance empat, emp_office empo, office ofc WHERE emp.emp_id=empat.emp_id AND emp.emp_id=empo.emp_id AND ofc.ofc_id=empo.ofc_id AND emp.guest=0 ORDER BY ea_id');
// $qd = $db->query('SELECT ea_id as "seqNo",emp.emp_id,concat(lname,", ",fname) as empname,ofc.ofc_name as "office",ea_time as "timein" FROM employee emp LEFT JOIN emp_office empo ON emp.emp_id=empo.emp_id LEFT JOIN office ofc ON empo.ofc_id=ofc.ofc_id JOIN emp_attendance ea ON emp.emp_id=ea.emp_id ORDER BY `seqNo` ASC');
// while($rd = $db->fetch_array($qd)):
// 	$arrCSV[] = array($rd['seqNo'],$rd['emp_id'],$rd['empname'],$rd['office'],$rd['timein']);
// endwhile;
$arrCSV[] = array('Category (Fixed Assets and Consumable Items)','Type','Code','Description','Serial/Plate Number','Date Acquired','Acquisition Price','Location','Remarks');
foreach($arrEquipment as $equip):
	$plate_serial='';
	$desc=$equip['equip_desc'];
	$desc.=($equip['plate_no']) ? ' ('.$equip['plate_no'].')' : '';
	if($equip['serial_no'] && $equip['plate_no'])
		$plate_serial=$equip['serial_no'].' / '.$equip['plate_no'];
	else if( empty($equip['serial_no']) && $equip['plate_no'] )
		$plate_serial=$equip['plate_no'];
	else if( empty($equip['plate_no']) && $equip['serial_no'] )
		$plate_serial=$equip['serial_no'];
	$arrCSV[] = array($equip['category'],$equip['type'],$equip['inventory_id'],$desc,$plate_serial,functions::datearr($equip['date_acquired']),number_format($equip['price']),$equip['location'],$equip['status']);
endforeach;
#print_r($arrCSV);
$fn = 'property_report_'.date('Y_m_d_H_i_s').'.csv';
array_to_csv_download($arrCSV,$fn);
?>