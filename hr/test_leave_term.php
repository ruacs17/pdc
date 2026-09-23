<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$q = $db->select('leave_file_detail','*',array());
while($r = $db->fetch_array($q)):
	$lfd_id = $r['lfd_id'];

	$arr_dle = explode('-', $r['term_end']);
	$yy = isset($arr_dle[0]) ? $arr_dle[0] : 0;
	$mm = isset($arr_dle[1]) ? $arr_dle[1] : 0;
	$dd = isset($arr_dle[2]) ? $arr_dle[2] : 0;
	$term_end = date('Y-m-d',mktime(0,0,0,$mm,$dd - 1,$yy));
	$db->update('leave_file_detail',array('term_end'=>$term_end),array('lfd_id'=>$lfd_id));
endwhile;
?>