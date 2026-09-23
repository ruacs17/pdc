<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
$merc_id = (isset($_REQUEST['mid']) && !empty($_REQUEST['mid']) ) ? functions::decode($_REQUEST['mid']) : 0;
$q = $db->select('material_excess_receive','*',array('merc_id'=>$merc_id));
$r = $db->fetch_array($q);
$proj_id = $r['proj_id'];
$checked_id = $r['checked_id'];
$checked_name = $r['checked_name'];
$checked_title = $r['checked_title'];
$accounted_id = $r['accounted_id'];
$accounted_name = $r['accounted_name'];
$accounted_title = $r['accounted_title'];
$received_id = $r['received_id'];
$received_name = $r['received_name'];
$received_title = $r['received_title'];
$merc_date = $r['merc_date'];
$remarks = $r['remarks'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Excess Material Receive Print</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="../img/favicon.png">
	<style>.tdSpace{padding: 0px 0px 0px 10px;}</style>
	<style>.amnt{padding-right: 12px;}</style>
	<style type="text/css">
		body {
			/*background: rgb(204,204,204);*/
			background: white;
			font-size: 11px;
			font-family: Tahoma;
		}
		table{border-collapse: collapse;}
		page[size="ltr"] {
			background: white;
			/*width: 21.6cm;
			height: 29.7cm;
			display: block;
			margin: 0 auto;
			margin-bottom: 0.5cm;
			box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);*/
		}
		@media print {
			body, page[size="ltr"] {
				margin: 0;
				box-shadow: 0;
			}
		}
	</style>
	<style type="text/css">
		#hName{font-size: 14px; font-family: Tahoma;font-weight: bolder;}
		#hAddress{font-size: 9px; font-family: Tahoma; line-height: 14px;}
		#DTitle{font-size: 14px; font-family: Tahoma;}
	</style>
</head>
<body>
	<page size="ltr">
		<table width="99%" border="1" align="center">
			<tr>
				<td align="center" colspan="5">
					<table width="100%" border="0" align="center">
						<tr>
						<td width="160" align="right"><img src="../img/header-logo.jpg" width="90" height="90"></td>
						<td align="center">
							<div id="hName">PHILKONSTRAK DEVELOPMENT CORPORATION</div>
							<div id="hAddress">
							<div>Design &bull; Estimate &bull; Construct &bull; Develop</div>
							<div>Door 3 MGR Building, Apitong Street, Sunrise Village Extension Pardo, Cebu City</div>
							<div>Tel / Fax # (Main Office) (032) 236-0992; 412-9907; Cell# 0933-453-3109; 0932-904-2475</div>
							<div>(Bohol Coordinating Office) (038) 509- 9204; 544-0271 Cell# 0917-303-5874</div>
							<div>E-mail Add: <a href="#">philkonstrak@hotmail.com</a>; <a href="#">philkonstrakdevtcorp@gmail.com</a></div>
							<div>Website: www.philkonstrak.com</div>
							</div>
						</td>
						<td width="115">&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="5"><div id="DTitle"><strong>Excess Materials Receiving Report</strong></div></td>
			</tr>
			<tr>
				<td colspan="5">
					<table width="100%" border="0">
						<tr>
							<td colspan="2"><div align="right">Series No. <strong>&nbsp;&nbsp;<?php echo $r['series_no']; ?></strong></div></td>
						</tr>
						<tr>
							<td colspan="2"><div align="left"><strong>Project Code/Name:</strong>&nbsp;&nbsp;<?php echo $db->getValue('project','proj_name',array('proj_id'=>$proj_id)); ?></div></td>
						</tr>
						<tr>
							<td><div align="left"><strong>Project Address:</strong>&nbsp;&nbsp;<?php echo $db->getValue('project','proj_location',array('proj_id'=>$proj_id)); ?></div></td>
							<td width="25%"><div align="right"><strong>Date Received:</strong>&nbsp;&nbsp;<?php echo functions::datearr($r['merc_date']); ?></div></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th>QTY</th>
				<th>UOM</th>
				<th>ITEM DESCRIPTION</th>
				<th width="15%">UNIT PRICE</th>
				<th width="15%">TOTAL COST</th>
			</tr>
			<?php
			$arrProj=array();
			$countRow=0;
			$total_cost=0;
			$qp = $db->select('material_excess_receive_detail','*',array('merc_id'=>$merc_id));
			while($rp = $db->fetch_array($qp)):
				$total_cost += $rp['totalcost'];
				$countRow++;
				?>
			<tr>
				<td><div align="center"><?php echo number_format($rp['quantity']);?></div></td>
				<td><div align="center"><?php echo $rp['unit'] ?></div></td>
				<td class="tdSpace"><div align="left"><?php echo $rp['item'] ?></div></td>
				<td class="amnt"><div align="right"><?php echo functions::formatMoney($rp['cost']);?></div></td>
				<td class="amnt"><div align="right"><?php echo functions::formatMoney($rp['totalcost']) ?></div></td>
			</tr>
			<?php endwhile; ?>
			<?php
				$remainingRow = 10 - $countRow;
				if($remainingRow >= 1){
					for($rw=1;$rw<=$remainingRow; $rw++):
			?>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
					endfor;
				} 
			?>
			<tr>
				<td colspan="4" class="amnt"><div align="right"><strong>TOTAL AMOUNT</strong></div></td>
				<td class="amnt"><div align="right"><strong><?php echo functions::formatMoney($total_cost); ?></strong></div></td>
			</tr>
			<tr>
				<td colspan="5" style="padding-bottom:4px;">
					<table width="100%" border="0">
						<tr>
							<td align="center" width="30%">Received/Encoded by:</td>
							<td align="center" width="30%">Checked and Verified by:</td>
							<td align="center" width="30%">Accounted by:</td>
						</tr>
						<tr>
							<td height="20" align="center" valign="bottom">
								<div><strong><?php echo $received_name ?></strong></div>
							</td>
							<td align="center" valign="bottom">
								<div><strong><?php echo $checked_name ?></strong></div>
							</td>
							<td align="center" valign="bottom">
								<div><strong><?php echo $accounted_name ?></strong></div>
							</td>
						</tr>
						<tr style="font-size:10px;">
							<td align="center" valign="middle">
								<div><i><?php echo $received_title ?></i></div>
							</td>
							<td align="center" valign="middle">
								<div><i><?php echo $checked_title ?></i></div>
							</td>
							<td align="center" valign="middle">
								<div><i><?php echo $accounted_title; ?></i></div>
							</td>
						</tr>
						<tr>
							<td align="center" valign="bottom">Date: _________</td>
							<td align="center" valign="bottom">Date: _________</td>
							<td align="center" valign="bottom">Date: _________</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="5">
					<div style="display:flex;">
						<div style="flex: 3;" align="left">Note: Produce this document in two (2) copies. Orginal: Warehouse, Duplicate: Accounting</div>
						<div style="flex: 1; display:none;" align="right">18PMD.FRM016.00-10/18</div>
					</div>
				</td>
			</tr>
		</table>
	</page>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	//setTimeout("closePrint()",200);
});
function closePrint(){
	//window.location="po-issuance-manage-detail.php?id=<?php #echo functions::encode($pos_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>