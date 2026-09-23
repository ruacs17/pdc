<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
  
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? functions::decode($_REQUEST['mf_id']) : 0;
$_SESSION['notif_id_list']=$mf_id;
$editTrue=0;
$poidEdt='';
$item='';$itemDesc='';$unit='';$brand='';$class='';$type='';$min='';$cat='';$size='';$code='';

$qvedt = $db->select('material_reference','*',array('mf_id'=>$mf_id));
$rvedt = $db->fetch_array($qvedt);
$brcode = $rvedt['mf_id'];
$item = $rvedt['item'];
$unit = $rvedt['unit'];
$brand = $rvedt['brand'];
$code = $rvedt['m_code'];
$class = $rvedt['classification'];
$type = $rvedt['mtype'];
$min = $rvedt['least_required'];
$size = $rvedt['msize'];
$itemDesc = $rvedt['mdescription'];
$cat = $rvedt['category'];
$cad = $rvedt['cad'];
$engr_standard = $rvedt['engr_standard'];
$dupa = $rvedt['dupa'];
$specification = $rvedt['specification'];
$product_brochure = $rvedt['product_brochure'];
?>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Material Reference</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<?php 
$barcode = functions::barcd($brcode); ?>
<?php #echo $barcode ?>
<div align="center" style="padding-top:55px;">
	<table width="100%" border="0">
		<?php for($i=0;$i<=13;$i++): ?>
		<tr>
			<td><div align="center"><?php echo $barcode ?></div></td>
			<td><div align="center"><?php echo $barcode ?></div></td>
			<td><div align="center"><?php echo $barcode ?></div></td>
			<td><div align="center"><?php echo $barcode ?></div></td>
			<td><div align="center"><?php echo $barcode ?></div></td>
			<td><div align="center"><?php echo $barcode ?></div></td>
		</tr>
		<tr>
			<td colspan="6">&nbsp;</td>
		</tr>
		<?php endfor; ?>
	</table>
</div>

<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	window.print();
	//setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-material-reference-detail.php?mf_id=<?php echo functions::encode($mf_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>