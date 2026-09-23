<?php
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$working_days=26;
$q = $db->query('SELECT * FROM emp_salary WHERE working_days<>26');
while($r = $db->fetch_array($q)):
	$sal = $r['es_salary'];
	$daily = $sal/$working_days;
	$hourly = $daily/8;
	$minute = $hourly/60;
	#echo $daily.' '.$hourly.' '.$minute.'<br>';
	$es_id = $r['es_id'];
	$db->update('emp_salary',array('working_days'=>$working_days,'es_daily'=>$daily,'es_hourly'=>$hourly,'es_minute'=>$minute),array('es_id'=>$es_id));
	echo $db->last_query.'<br>';
endwhile;
?>