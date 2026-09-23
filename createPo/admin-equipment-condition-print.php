<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));

$equip_id = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$q = $db->select('equipment','*',array('equip_id'=>$equip_id));
$r = $db->fetch_array($q);
$txInventoryNo = $r['inventory_id'];
$txDesc = $r['equip_desc'];
$equip_name = $txInventoryNo.' '.$txDesc;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Property Status History Printing</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style>.tdSpace{padding: 0px 0px 0px 10px;}</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
	<tr>
		<td>
			<?php
			require_once('../class/print_header.php');
			print_header('Property Status History');
			?><br>
		</td>
	</tr>
	<tr>
		<td>
			<div style="padding:0px 0px 15px 0px;">Property: <strong><?php echo $equip_name ?></strong></div>
			<table width="100%" border="1">
				<tr>
					<th width="15%" align="center" scope="row">Status</th>
					<th width="10%" align="center">Date</th>
				</tr>
				<?php
				$q = $db->select('equip_condition','*',array('equip_id'=>$equip_id),'ORDER BY con_date');
				while($r=$db->fetch_array($q)):
				?>
				<tr>
					<td align="center"><?php echo $r['con_stat']?></td>
					<td align="center"><?php echo functions::datearr($r['con_date'])?></td>
				</tr>
				<?php endwhile;?>
			</table>
		</td>
	</tr>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-equipment-condition-manage.php?eid=<?php echo functions::encode($equip_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>