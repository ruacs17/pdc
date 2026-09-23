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

$where = ''; 
$supplier = ( isset($_SESSION['pfSup']) && !empty($_SESSION['pfSup']) ) ? $_SESSION['pfSup'] : '';
$charge = ( isset($_SESSION['pfCharge']) && !empty($_SESSION['pfCharge']) ) ? $_SESSION['pfCharge'] : '';
$equip_id = ( isset($_SESSION['pfEquip']) && !empty($_SESSION['pfEquip']) ) ? $_SESSION['pfEquip'] : '';
$txbMon = ( isset($_SESSION['pfMon']) ) ? $_SESSION['pfMon'] : date('m');
$txbYear = ( isset($_SESSION['pfYear']) ) ? $_SESSION['pfYear'] : date('Y');
$txbDay = ( isset($_SESSION['pfDay']) ) ? $_SESSION['pfDay'] : date('d');  
$txbMonTo = ( isset($_SESSION['pfMonTo']) ) ? $_SESSION['pfMonTo'] : date('m');
$txbYearTo = ( isset($_SESSION['pfYearTo']) ) ? $_SESSION['pfYearTo'] : date('Y');
$txbDayTo = ( isset($_SESSION['pfDayTo']) ) ? $_SESSION['pfDayTo'] : date('d');  
$arr = array('po_type'=>'fuel');
      
if($charge){
	if( $db->getValue('project','count(proj_id)',array('proj_id'=>$charge)) )
		$where .= ' AND vd.proj_id="'.$charge.'"';
}
if($equip_id){
	if( $db->getValue('equipment','count(equip_id)',array('equip_id'=>$equip_id)) )
		$where .= ' AND ef.equip_id="'.$equip_id.'"';
}
if($supplier){
	$where .= ' AND v.supplierID="'.$supplier.'"';
}
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND (vd_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT(vd_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(vd_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING(vd_date,6,2)>="'.$txbMon.'" AND SUBSTRING(vd_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT(vd_date,4) >= "'.$txbYear.'" AND LEFT(vd_date,4) <= "'.$txbYearTo.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND vd_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

#$qvDetails = $db->query('SELECT * FROM voucher_detail vd LEFT JOIN equip_fuel ef ON vd.ef_id=ef.ef_id WHERE vd.category_id IN (SELECT item_id FROM item_deduction WHERE name LIKE "%fuel%") '.$where.' ORDER BY vd_date DESC');
$qvDetails = $db->query('SELECT * FROM voucher v, voucher_particular vp, voucher_detail vd LEFT JOIN equip_fuel ef ON vd.ef_id=ef.ef_id WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND vd.category_id IN (SELECT item_id FROM item_deduction WHERE name LIKE "%fuel%") '.$where.' ORDER BY vd_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Voucher Fuel Report Print</title>
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
			print_header('Voucher Fuel Report');
			?><br>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table" style="font-size:12px;">
					<thead>
						<tr>
							<th width="8%" scope="col"><div align="left">Voucher No.</div></th>
							<th width="8%" scope="col"><div align="left">Date</div></th>
							<th width="25%" scope="col"><div align="left">Equipment</div></th>
							<th width="25%" scope="col"><div align="left">Charge To</div></th>
							<th width="10%" scope="col"><div align="left">Liter / Price</div></th>
							<th width="10%" scope="col"><div align="left">Cost</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$vdate='';$total_amount=0;
					while($rvDetails = $db->fetch_array($qvDetails)):
						$total_amount += $rvDetails['amount'];
						$equip_id = ($rvDetails['ef_id']) ? $db->getValue('equip_fuel','equip_id',array('ef_id'=>$rvDetails['ef_id'])) : 0;
						$eqpName = ($equip_id) ? $db->getValue('equipment','name',array('equip_id'=>$equip_id)) : '---';
						$eqp_liter = $db->getValue('equip_fuel','liter',array('ef_id'=>$rvDetails['ef_id']));
						$equip_liter = ($eqp_liter) ? $eqp_liter : '';
						$price_liter = ($equip_liter && $rvDetails['amount']) ? ' / '.functions::formatMoney($rvDetails['amount']/$equip_liter) : '';
					?>
						<tr>
							<td>
								<?php 
								$vid = $db->query('SELECT voucher_id FROM voucher_detail vd, voucher_particular vp WHERE vd.vp_id=vp.vp_id AND vd.vd_id="'.$db->clean($rvDetails['vd_id']).'"');
								echo $db->getValue('voucher','voucher_no',array('voucher_id'=>$db->result()));
								?>
							</td>
							<td><?php echo functions::datearr($rvDetails['vd_date']);?></td>
							<td><?php echo $eqpName?></td>
							<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rvDetails['proj_id']));?></td>
							<td><?php echo $equip_liter.$price_liter;?></td>
							<td><?php echo functions::formatMoney($rvDetails['amount']);?></td>
						</tr>
					<?php endwhile;?>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td colspan="2"><div align="right"><strong>Total Amount</strong></div></td>
							<td><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
						</tr>
					<tbody>
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
	window.location="po_fuel_voucher_report.php";
}
</script>
<!-- end: JavaScript-->
</body>
</html>