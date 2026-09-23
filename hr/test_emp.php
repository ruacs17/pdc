<?php
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();


$qUser = $db->select('equip_user','*',array());
while($r = $db->fetch_array($qUser)):
	$extname='';
	$fname = strtoupper($r['fname']);
	$mname = strtoupper($r['mname']);
	$lname = strtoupper($r['lname']);
	$extArr = explode(',',$fname);
	if( count($extArr) > 1 ){
		$lname = $extArr[0];
		$extname = $extArr[1];
	}
	$bdate = $r['bdate'];
	$cell_no = $r['mobile'];
	$civil_status = ucwords(strtolower($r['marital_status']));
	$arrField = array('fname'=>$fname,'mname'=>$mname,'lname'=>$lname,'extname'=>$extname,'bdate'=>$bdate,'cell_no'=>$cell_no);
	echo $db->insertPrint('employee',$arrField);
	echo '<br>';
endwhile;
?>