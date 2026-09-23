<?php
require_once('../class/functions.php');
require_once('../class/database.php');
$db = new Database();

function address($street='',$brgy='',$city='',$province=''){
	$street = ($street) ? trim($street) : '';
	$brgy = ($brgy) ? trim($brgy) : '';
	$city = ($city) ? trim($city) : '';
	$province = ($province) ? trim($province) : '';

	$adrs = $street;
	$adrs .= ( $adrs && $brgy ) ? ', '.$brgy : $brgy;
	$adrs .= ( $adrs && $city  ) ? ', '.$city : $city;
	$adrs .= ( $adrs && $province  ) ? ', '.$province : $province;
	return $adrs;
}
$q = $db->query('SELECT 
curr_street,
(SELECT brgyDesc FROM refbrgy WHERE emp.curr_add=brgyCode) as curr_brgy, 
(SELECT citymunDesc FROM refcitymun WHERE emp.curr_cityMun=cityMunCode) as curr_city, 
(SELECT provDesc FROM refprovince WHERE emp.curr_province=provCode) as curr_province,
perm_street,
(SELECT brgyDesc FROM refbrgy WHERE emp.perm_add=brgyCode) as perm_brgy, 
(SELECT citymunDesc FROM refcitymun WHERE emp.perm_cityMun=cityMunCode) as perm_city, 
(SELECT provDesc FROM refprovince WHERE emp.perm_province=provCode) as perm_province,
emp_id
FROM employee emp');
while($r = $db->fetch_array($q)):
	$curr_address = address($r['curr_street'],$r['curr_brgy'],$r['curr_city'],$r['curr_province']);
	$perm_address = address($r['perm_street'],$r['perm_brgy'],$r['perm_city'],$r['perm_province']);
	$db->update('employee',array('curr_address'=>$curr_address,'perm_address'=>$perm_address),array('emp_id'=>$r['emp_id']));
endwhile;

// function updateAdd($emp_id){
// 	global $db;
// 	$q = $db->prepareQ('SELECT 
// 		curr_street,
// 		(SELECT brgyDesc FROM refbrgy WHERE emp.curr_add=brgyCode) as curr_brgy, 
// 		(SELECT citymunDesc FROM refcitymun WHERE emp.curr_cityMun=cityMunCode) as curr_city, 
// 		(SELECT provDesc FROM refprovince WHERE emp.curr_province=provCode) as curr_province,
// 		perm_street,
// 		(SELECT brgyDesc FROM refbrgy WHERE emp.perm_add=brgyCode) as perm_brgy, 
// 		(SELECT citymunDesc FROM refcitymun WHERE emp.perm_cityMun=cityMunCode) as perm_city, 
// 		(SELECT provDesc FROM refprovince WHERE emp.perm_province=provCode) as perm_province,
// 		emp_id
// 		FROM employee emp WHERE emp_id=?',array($emp_id));
// 	while($r = $db->fetch_array($q)):
// 		$curr_address = address($r['curr_street'],$r['curr_brgy'],$r['curr_city'],$r['curr_province']);
// 		$perm_address = address($r['perm_street'],$r['perm_brgy'],$r['perm_city'],$r['perm_province']);
// 		$db->update('employee',array('curr_address'=>$curr_address,'perm_address'=>$perm_address),array('emp_id'=>$r['emp_id']));
// 	endwhile;
// }
//updateAdd($emp_id=788);
?>