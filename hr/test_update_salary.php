<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$q = $db->select('emp_salary','*',array());
while($r = $db->fetch_array($q)):
	$es_id = $r['es_id'];
	$es_type='';
	$wd = $r['working_days'];
	$es_daily = ($r['es_salary'] && $wd) ? ($r['es_salary']/$wd) : 0;
	$es_hourly = ( $es_daily) ? $es_daily/8 : 0;
	$es_minute = ( $es_daily) ? $es_hourly/60 : 0;
	if($wd==24)
		$es_type='flexible';
	if($wd==26)
		$es_type='fixed';	
	$db->update('emp_salary',array('es_daily'=>$es_daily,'es_hourly'=>$es_hourly,'es_minute'=>$es_minute,'es_type'=>$es_type),array('es_id'=>$es_id));
endwhile;
?>