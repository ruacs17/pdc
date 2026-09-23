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

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array();

$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $txSearch='';

$txPayee = ( isset($_SESSION['poPayee']) && !empty($_SESSION['poPayee']) ) ? $_SESSION['poPayee'] : '';
$txbMon = ( isset($_SESSION['poMon']) ) ? $_SESSION['poMon'] : date('m');
$txbYear = ( isset($_SESSION['poYear']) ) ? $_SESSION['poYear'] : date('Y');


$searchType = ( isset($_SESSION['poSelSearch']) ) ? $_SESSION['poSelSearch'] : '';
$searchVal = ( isset($_SESSION['poSearchVal']) ) ? $_SESSION['poSearchVal'] : '';
$arrSpcSrch=array();
if($searchType=='po_no')
	$arrSpcSrch = array('po_no'=>$searchVal);
else if($searchType=='rr_no')
	$arrSpcSrch = array('receive_no'=>$searchVal);
else if($searchType=='is_no')
	$arrSpcSrch = array('receive_no'=>$searchVal);

if($txPayee)
	$arr = array_merge($arr,array('supplierID'=>$txPayee));

if($txbMon && $txbYear)
	$arr = array_merge($arr,array('LEFT(po_date,7)'=>$txbYear.'-'.$txbMon));
else if($txbMon)
	$arr = array_merge($arr,array('SUBSTRING(po_date,6,2)'=>$txbMon));
elseif($txbYear)
	$arr = array_merge($arr,array('LEFT(po_date,4)'=>$txbYear));  

#$qPO = $db->select('po','*',$arr,'ORDER BY po_date DESC');


if($searchType=='is_no'){
	$qPO = $db->select('po_issuance poi, po_issuance_details posd, po p','*',array('series_no'=>$searchVal),'AND poi.pos_id=posd.pos_id AND p.po_id=posd.po_id');
}
else if($searchType){
	$qPO = $db->select('po','*',$arrSpcSrch,'ORDER BY po_date DESC');
}
else{
	$qPO = $db->select('po','*',$arr,'ORDER BY po_date DESC');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>PO Fuel Report Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">.padParLeft{padding-left:10px;}.padAmLeft{padding-left:60px;}</style>
</head>
<body bgcolor="#FFFFFF">
<div id="spinner"></div>
<!-- body content: start here-->
<table border="0" align="center">
	<thead>
		<tr>
			<td>
			<?php
			require_once('../class/print_header.php');
			print_header('P.O. Monitoring Report');
			?><br>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<table class="table table-bordered" style="font-size:12px">
					<thead>
						<tr>
							<th width="10%">Date</th>
							<th width="5%"><div>P.O. #</div></th>
							<th width="5%"><div>R.R. #</div></th>
							<th width="5%"><div>Issuance #</div></th>
							<th width="10%">External Provider</th>
							<th width="10%">Item</th>
							<th width="5%">Qty</th>
							<th width="5%">Unit</th>
							<th width="5%">Brand</th>
							<th width="5%">Cost</th>
							<th width="5%">Amount</th>
							<th width="7%">Terms of Payment</th>
							<th width="10%">Delivery Date</th>
							<th width="7%">Status</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($rPO = $db->fetch_array($qPO)):
						$qPOI = $db->select('po_item','*',array('po_id'=>$rPO['po_id']));
						while($rPOI = $db->fetch_array($qPOI)):
					?>
						<tr>
							<td><?php echo functions::datearr($rPO['po_date']);?></td>
							<td><div><?php echo $rPO['po_no'];?></div></td>
							<td><div><?php echo $rPO['receive_no'];?></div></td>
							<td>
								<div>
									<?php
									$qpo = $db->select('po_issuance poi, po_issuance_details posd, po p','*',array('p.po_id'=>$rPO['po_id']),'AND poi.pos_id=posd.pos_id AND p.po_id=posd.po_id');
									while($rpo = $db->fetch_array($qpo)):
										echo '<div>'.$rpo['series_no'].'</div>';
									endwhile;
									?>
								</div>
							</td>
							<td><div style="font-size: 11px;"><?php echo $db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID']));?></div></td>
							<td><?php echo $rPOI['item']?></td>
							<td><?php echo round($rPOI['qty_delivered'],2)?></td>
							<td><?php echo $rPOI['unit']?></td>
							<td><?php echo $rPOI['brand']?></td>
							<td><?php echo functions::formatMoney($rPOI['cost'])?></td>
							<td><?php echo functions::formatMoney($rPOI['qty_delivered'] * $rPOI['cost'])?></td>
							<td><?php echo $rPO['payment_term']?></td>
							<td><?php echo functions::datearr($rPO['delivery_date']);?></td>
							<td><?php echo ($rPO['received']==1) ? 'Received' : 'Not Received';?></td>
						</tr>
						<?php endwhile; //rPOI
					endwhile; //rPO?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="po_monitoring.php";
}
</script>
<!-- end: JavaScript-->
</body>
</html>