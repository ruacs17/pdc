<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
//'eat_id'=>56,'emp_id'=>10
$q = $db->select('emp_attendance_detail','*',array());
while($r = $db->fetch_array($q)):
	$eatd_id = $r['eatd_id'];
	$wage_day = $r['wage_day'];
	$wage_hour = ($wage_day) ? ($wage_day/8) : 0;
	$wage_minutes = ($wage_day) ? ($wage_hour/60) : 0;

	$duty_amount = $r['duty_min'] * $wage_minutes;
	$absent_amount = ($r['late_min'] + $r['under_min']) * $wage_minutes;
	$ot_amount = $r['ot_min'] * $wage_minutes;

	$db->update('emp_attendance_detail',array('wage_hour'=>$wage_hour,'wage_minute'=>$wage_minutes,'duty_amount'=>$duty_amount,'ot_amount'=>$ot_amount,'absent_amount'=>$absent_amount),array('eatd_id'=>$eatd_id));
	#echo $db->last_query.'<br>';
endwhile;
?>