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

$pos_id = (isset($_REQUEST['id']) && !empty($_REQUEST['id']) ) ? functions::decode($_REQUEST['id']) : 0;
$q = $db->select('po_issuance','*',array('pos_id'=>$pos_id));
$r = $db->fetch_array($q);
$txpono='';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Issuance Details Print</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="../img/favicon.png">
	<style>.tdSpace{padding: 0px 0px 0px 10px;}</style>
	<style>.amnt{padding-right: 12px;}</style>
	<style type="text/css">
		body {
			/*background: rgb(204,204,204);*/
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
		<?php 
		require_once('../class/print_header.php');
		print_header('ISSUANCE FORM');
		?>
		<table width="99%" border="1" align="center">
			<tr>
				<td align="center" colspan="5">
				</td>
			</tr>
			<tr>
				<td colspan="5">
					<table width="100%" border="0">
						<tr>
							<td>
								<div align="left">
									Purchase Order No.:
									<u>
									<?php
									$qpo = $db->select('po_issuance pis, po_issuance_details pisd, po p','*',array('pis.pos_id'=>$pos_id),'AND pis.pos_id=pisd.pos_id AND pisd.po_id=p.po_id');
									$countPO = $db->num_rows($qpo);
									$countTemp=1;
									while($rpo = $db->fetch_array($qpo)):
										echo $rpo['po_no'];
										if( $countTemp < $countPO)
											echo ', ';
										$countTemp++;
									endwhile;
									?>
									</u>
								</div>
							</td>
							<td><div align="right">Series No. <strong><?php echo $r['series_no']; ?></strong></div></td>
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
			$qp = $db->select('po_issuance pis, po_issuance_details pisd, po p','*',array('pis.pos_id'=>$pos_id),'AND pis.pos_id=pisd.pos_id AND pisd.po_id=p.po_id');
			$countPO = $db->num_rows($qp);
			while($rp = $db->fetch_array($qp)):
				
				$arrProj[$rp['proj_id']]=$rp['proj_id'];
				$po_total=0;
				$qpoi = $db->select('po_issuance_item posi, po_item poi','*',array('posi.po_id'=>$rp['po_id'],'pos_id'=>$pos_id),'AND posi.po_item_id=poi.po_item_id');
				while($rpoi = $db->fetch_array($qpoi)):
					$cost = $rpoi['cost'] * $rpoi['qty_issue'];
					$total_cost += $cost;
					$po_total += $cost;
					$countRow++;
				?>
			<tr>
				<td><div align="center"><?php echo number_format($rpoi['qty_issue']);?></div></td>
				<td><div align="center"><?php echo $rpoi['unit'] ?></div></td>
				<td class="tdSpace"><div align="left"><?php echo $rpoi['item'] ?></div></td>
				<td class="amnt"><div align="right"><?php echo functions::formatMoney($rpoi['cost']);?></div></td>
				<td class="amnt"><div align="right"><?php echo functions::formatMoney($cost) ?></div></td>
			</tr>
				<?php endwhile; ?>
				<?php if($countPO > 1){ $countRow++;?>
			<tr>
				<td colspan="5" class="amnt"><div align="right"><strong><?php echo functions::formatMoney($po_total); ?></strong></div></td>
			</tr>
				<?php } ?>
			<?php endwhile; ?>
			<tr>
				<td colspan="5" class="amnt"><div align="right"> Total <strong><?php echo functions::formatMoney($total_cost); ?></strong></div></td>
			</tr>
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
				<td colspan="5" style="padding-top:4px; padding-bottom:4px;">
					<?php if(count($arrProj) == 1){ ?>
					<div>Project Name / Location:
					<?php foreach($arrProj as $projid): echo '<strong>'.$db->getValue('project','proj_name',array('proj_id'=>$projid)).'</strong>'; endforeach; ?>
					</div>
					<?php }else{?>
					<div>Project Name / Location:</div>
					<?php foreach($arrProj as $projid): echo '<div style="padding-left:30px;"><strong>'.$db->getValue('project','proj_name',array('proj_id'=>$projid)).'</strong></div>'; endforeach; ?>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td colspan="5" style="padding-bottom:4px;">
					<table width="100%" border="0">
						<tr>
							<td align="center" width="25%">Prepared and Issued by:</td>
							<td align="center" width="25%">Reviewed by:</td>
							<td align="center" width="25%">Approved by:</td>
							<td align="center" width="25%">Checked and Received by:</td>
						</tr>
						<tr>
							<td height="20" align="center" valign="bottom"><div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['prepared_by'])) ?></strong></div></td>
							<td align="center" valign="bottom"><div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['reviewed_by'])) ?></strong></div></td>
							<td align="center" valign="bottom"><div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['approved_by'])) ?></strong></div></td>
							<td align="center" valign="bottom"><div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['received_by'])) ?></strong></div></td>
						</tr>
						<tr style="font-size:10px;">
							<td align="center" valign="middle"><div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['prepared_by']),'AND ep.dp_id=dp.dp_id') ?></i></div></td>
							<td align="center" valign="middle"><div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['reviewed_by']),'AND ep.dp_id=dp.dp_id') ?></i></div></td>
							<td align="center" valign="middle"><div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['approved_by']),'AND ep.dp_id=dp.dp_id') ?></i></div></td>
							<td align="center" valign="middle"><div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['received_by']),'AND ep.dp_id=dp.dp_id') ?></i></div></td>
						</tr>
						<tr>
							<td align="center" valign="bottom">Date: <u><?php echo functions::datearr($r['issue_date']); ?></u></td>
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
						<div style="flex: 3;" align="left">Note: Produce this document in <strong>two (2) copies</strong>. Original: Warehouse, Duplicate: Receiver</div>
						<div style="flex: 1;" align="right">24LGD.FRM004.00-05/24</div>
					</div>
				</td>
			</tr>
		</table>
	</page>
	<br><br>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="po-issuance-manage-detail.php?id=<?php echo functions::encode($pos_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>