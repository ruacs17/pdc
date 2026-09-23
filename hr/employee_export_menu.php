<?php
require_once('authorize.php');
require_once('../class/database.php');
require_once('../class/functions.php');
$dwld=(isset($_REQUEST['dwld']) && !empty($_REQUEST['dwld']) ) ? $_REQUEST['dwld'] : "";
if($dwld){
	// echo '<link id="base-style" href="../css/loader.css" rel="stylesheet">';
	// echo 'Please wait while attendance is still processing........<br>';
	// echo '<div id="spinner"></div>';
	functions::sendTo('employee_export_all.php');
}
	#functions::sendTo('http://192.168.1.200/mgr/hrpdc/employee_export_all.php');
clearstatcache();
$page_from=(isset($_REQUEST['from']) && !empty($_REQUEST['from']) ) ? functions::decode($_REQUEST['from']) : "";
?>
<a href="?dwld=xy">Click Here to Download</a><br><br>
<a href="<?php echo $page_from?>">Go Back</a>