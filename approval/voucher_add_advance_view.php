<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="8" ){
  	header("Location: ../");
  	die();
}
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$count=0;
$supplierID = (isset($_REQUEST['payeeID']) && !empty($_REQUEST['payeeID']) ) ? $_REQUEST['payeeID'] : 2;
?>
<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
<link id="base-style" href="../css/style.css" rel="stylesheet">
<link rel="stylesheet" href="../css/bootstrap-multiselect.css" type="text/css">
<select name="selAdvancePayment[]" id="selAdvancePayment" multiple="multiple">
	<?php
	$qVoucherList = $db->select('voucher','*',array('supplierID'=>$supplierID));
	while($rVL = $db->fetch_array($qVoucherList)):?>
	<option value="<?php echo functions::encode($rVL['voucher_id'])?>" ><?php echo $rVL['voucher_no']?></option>
	<?php endwhile;?>
</select>
<script>alert('asdf')</script>