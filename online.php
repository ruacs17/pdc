<?php 
require_once('class/database.php');

$host = '108.179.232.14';
$user = 'delacasi_pdc';
$password='pdcDB@123*';
$db = 'delacasi_pdc';



$db = new Database($host,$user,$password,$db);
echo $db->info();
/*
$q = $db->query('select * from samp');
while($r = $db->fetch_array($q)):
	echo $r['a'].'<br>';
endwhile;
*/
?>