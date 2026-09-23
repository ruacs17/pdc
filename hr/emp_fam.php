<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="13" ){
    header("Location: ../");
    die();
}
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/databaseHR.php');
require_once('../class/functions.php');
$db = new DatabaseHR();
$field = ( isset($_GET['f']) && !empty($_GET['f'])) ? $db->clean($_GET['f']) : "";
$value = ( isset($_GET['v']) && !empty($_GET['v'])) ? $_GET['v'] : "";
$emp_id = ( isset($_GET['di']) && !empty($_GET['di'])) ? $_GET['di'] : "0";
if($field)
	$db->update('employee',array($field=>strtoupper(trim($value))),array('emp_id'=>$emp_id));
?>