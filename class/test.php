<?php
require_once('database.php');

$db = new Database();
if($db->status())
	echo 'connected';
else
	echo 'not connected';
?>