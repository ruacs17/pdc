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

function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	return $position;
}
$ref_id = (isset($_REQUEST['ref_id']) && !empty($_REQUEST['ref_id']) ) ? functions::decode($_REQUEST['ref_id']) : 0;
$por_id = (isset($_REQUEST['porid']) && !empty($_REQUEST['porid']) ) ? functions::decode($_REQUEST['porid']) : 0;
$por_id_rem = (isset($_REQUEST['poridrem']) && !empty($_REQUEST['poridrem']) ) ? functions::decode($_REQUEST['poridrem']) : 0;
if( isset($_REQUEST['poridrem']) && !empty($_REQUEST['poridrem']) && $ref_id ){
	$db->delete('po_receive',array('por_id'=>functions::decode($_REQUEST['poridrem'])));

	$all_received=1;
	$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand');
	while($rPOI = $db->fetch_array($qPOI)):
		$qty_request = round($rPOI['qty']);
		$qty_received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
		$qty_receivable = $qty_request - $qty_received;
		
		//Update the actual P.O.
		$db->update('po p, po_item poi',array('qty_delivered'=>$qty_received),array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND p.po_id=poi.po_id');

		//checking all the items if all items were completed in receiving, it will assigned the variable into 0 if there's one item is not yet complete.
		if($qty_receivable)
			$all_received=0;
	endwhile;
	//update PO receiving no
	$countRR=1;
	$receive_no = '';
	$qrn = $db->select('po_receive','receive_no',array('ref_id'=>$ref_id));
	$actualRR = $db->num_rows($qrn);
	while($rrn = $db->fetch_array($qrn)):
		$receive_no.=$rrn['receive_no'];
		if($countRR<$actualRR)
			$receive_no.=", ";
		$countRR++;
	endwhile;
	$db->update('po',array('receive_no'=>$receive_no,'received'=>$all_received),array('ref_id'=>$ref_id));
	
	$_SESSION['notif_warning']='Receiving Report Removed!';
	functions::sendTo('?ref_id='.functions::encode($ref_id));
	die();
}
$_SESSION['notif_id_list']=$ref_id;
$qpo = $db->select('po','ref_id,po_date,delivery_date,supplierID,invoice,item_received_by,item_encoded_by,report_received_by,receive_no,remarks',array('ref_id'=>$ref_id),'GROUP BY ref_id');
$rPO = $db->fetch_array($qpo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Purchase Order Received Update</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Receiving Report</h2>
		</div>
		<div class="box-content">
			<div align="right" style="padding-bottom:40px;">
				<a id="recv" class="btn btn-small btn-info" href="po_receive_manage.php?ref_id=<?php echo functions::encode($ref_id)?>">Create Receiving Report</a>&nbsp;
			</div>
			<table width="80%" border="0" align="center">
					<tr>
						<td>
							<table width="100%" border="0" style="font-size:11px;">
								<tr>
									<td align="left">Provider: <strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
									<td align="left">P.O. Date: <strong> <?php echo functions::datearr($rPO['po_date']);?></strong></td>
								</tr>
								<tr>
									<td align="left">Address: <strong><?php echo strtoupper($db->getValue('supplier','address',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
									<td align="left">P.O. No: <strong> <?php echo $rPO['ref_id'];?></strong></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table width="100%" border="1" style="font-size:11px;">
								<thead>
									<tr style="background-color:#CCC;">
										<th width="40%" scope="col" height="40px;"><div align="left">Item</div></th>
										<th width="26%" scope="col"><div align="left">Brand</div></th>
										<th width="8%" scope="col"><div align="center">Unit</div></th>
										<th width="8%" scope="col"><div align="center">Qty Request</div></th>
										<th width="10%" scope="col"><div align="center">Qty Received</div></th>
										<th width="8%" scope="col"><div align="center">Qty Receivable</div></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$total_amount=0;$disc_amount=0;$amount=0;
									$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand ORDER BY pi.item');
									while($rPOI = $db->fetch_array($qPOI)):
										$qty_request = round($rPOI['qty']);
										$qty_received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']),'AND por.por_id=pori.por_id');
										$qty_receivable = $qty_request - $qty_received;
										$arr = array('item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand']);
										$selItm = functions::encode(serialize($arr));
									?>
									<tr>
										<td height="20px;"><?php echo $rPOI['items'];?></td>
										<td><?php echo $rPOI['brand'];?></td>
										<td><div align="center"><?php echo $rPOI['unit'];?></div></td>
										<td><div align="center"><?php echo $qty_request;?></div></td>
										<td><div align="center"><?php echo $qty_received;?></div></td>
										<td><div align="center"><?php echo $qty_receivable;?></div></td>
									</tr>
									<?php endwhile;?>
								</tbody>
							</table>
						</td>
					</tr>
			</table>
			<?php
			$qRec = $db->select('po_receive','*',array('ref_id'=>$ref_id),'ORDER BY delivery_date');
			if($db->num_rows($qRec)){
			?>
			<div align="center" style="padding-top:50px;border-collapse: collapse;font-size:12px;">
				<table width="80%" border="0" >
					<thead>
						<tr style="background-color:#CCC;">
							<th height="30px;"><div align="left">PO #</div></th>
							<th><div align="left">RR #</div></th>
							<th><div align="left">Received Date</div></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$qRec = $db->select('po_receive','*',array('ref_id'=>$ref_id),'ORDER BY delivery_date');
					while($rRec = $db->fetch_array($qRec)):
					?>
						<tr style="">
							<td height="30px;" style="border-top:1px;"><div align="left"><?php echo $rRec['ref_id'];?></div></td>
							<td><div align="left"><?php echo $rRec['receive_no'];?></div></td>
							<td><div align="left"><?php echo functions::datearr($rRec['delivery_date']); ?></div></td>
							<td width="20%">
								<div align="center">
									<a id="por<?php echo $rRec['por_id']?>Open" href="#" class="btn btn-success btn-mini" title="View P.O. Details" data-rel="tooltip" onClick="manage(this.id,'por<?php echo $rRec['por_id']?>Close','por<?php echo $rRec['por_id']?>row')"><i class="halflings-icon white chevron-down"></i></a>
									<a id="por<?php echo $rRec['por_id']?>Close" href="#" class="btn btn-success btn-mini" title="View P.O. Details" data-rel="tooltip" onClick="manage('por<?php echo $rRec['por_id']?>Open',this.id,'por<?php echo $rRec['por_id']?>row')" style="display:none;"><i class="halflings-icon white chevron-up"></i></a>
									<a class="btn btn-mini btn-warning" href="po_receive_manage.php?ref_id=<?php echo functions::encode($ref_id)?>&poridupt=<?php echo functions::encode($rRec['por_id'])?>" title="Modify" data-rel="tooltip"><i class="halflings-icon white pencil"></i></a>
									<a class="btn btn-mini btn-info" href="po_receive_print.php?ref_id=<?php echo functions::encode($ref_id)?>&poridprint=<?php echo functions::encode($rRec['por_id'])?>" title="Print" data-rel="tooltip"><i class="halflings-icon white print"></i></a>
									<a class="btn btn-mini btn-danger" href="?ref_id=<?php echo functions::encode($ref_id)?>&poridrem=<?php echo functions::encode($rRec['por_id'])?>" onClick="if(confirm('Do you want to remove this report?')){return true;}else{return false;}" title="Remove this report" data-rel="tooltip"><i class="halflings-icon white trash"></i></a>
									
								</div>
							</td>
						</tr>
						<tr id="por<?php echo $rRec['por_id']?>row" style="display:none;border-right: 1px solid black;border-left: 1px solid black;border-bottom: 1px solid black;">
							<td colspan="4">
								<div align="center" style="padding:30px 0px 30px 0px;">
									<table width="900" border="0" style="font-size:11px;">
										<tr>
											<td>
												<table width="100%">
													<tr>
														<td align="left">Provider: <strong><?php echo strtoupper($db->getValue('supplier','name',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
														<td align="left">R.R. No: <strong> <?php echo $rRec['receive_no'];?></strong></td>
													</tr>
													<tr>
														<td align="left">Address: <strong><?php echo strtoupper($db->getValue('supplier','address',array('supplierID'=>$rPO['supplierID'])));?></strong></td>
														<td align="left">P.O. No: <strong> <?php echo $rRec['ref_id'];?></strong></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<table width="100%" border="1">
													<thead>
														<tr style="background-color:#CCC;">
															<th width="40%" scope="col" height="40px;"><div align="left">Item</div></th>
															<th width="26%" scope="col"><div align="left">Brand</div></th>
															<th width="8%" scope="col"><div align="center">Unit</div></th>
															<th width="10%" scope="col"><div align="center">Qty Received</div></th>
														</tr>
													</thead>
													<tbody>
														<?php
														$total_amount=0;$disc_amount=0;$amount=0;
														$qPOI = $db->query('SELECT pi.item as items,sum(quantity) as qty,unit,brand FROM po p, po_item pi WHERE p.po_id=pi.po_id AND p.ref_id="'.$ref_id.'" GROUP by pi.item,unit,brand ORDER BY pi.item');
														while($rPOI = $db->fetch_array($qPOI)):
															$qty_request = round($rPOI['qty']);
															$qty_received = $db->getValue('po_receive por, po_receive_item pori','sum(qty_received)',array('ref_id'=>$ref_id,'item'=>$rPOI['items'],'unit'=>$rPOI['unit'],'brand'=>$rPOI['brand'],'por.por_id'=>$rRec['por_id']),'AND por.por_id=pori.por_id');
															$qty_receivable = $qty_request - $qty_received;
															if($qty_received){
														?>
														<tr>
															<td height="20px;"><?php echo $rPOI['items'];?></td>
															<td><?php echo $rPOI['brand'];?></td>
															<td><div align="center"><?php echo $rPOI['unit'];?></div></td>
															<td><div align="center"><?php echo $qty_received;?></div></td>
														</tr>
														<?php }endwhile;?>
													</tbody>
												</table>
											</td>
										</tr>
										<?php if($rRec['remarks']){?>
										<tr style="font-size:11px;">
											<td><strong>Remarks:</strong> <?php echo $rRec['remarks'];?></td>
										</tr>
										<?php }?>
										<tr>
											<td>
												<table width="100%">
													<tr>
														<td align="center">
															<table width="100%" border="1" style="font-size:10px;">
																<tr>
																	<td align="left" width="33%">
																		Item Received By:<br>
																		<strong><?php echo strtoupper($db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rRec['item_received_by'])));?></strong><br>
																		<?php echo position($rRec['item_received_by']);?>
																	</td>
																	<td align="left" width="33%">
																		Encoded By:<br>
																		<strong><?php echo strtoupper($db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rRec['item_encoded_by'])));?></strong><br>
																		<?php echo position($rRec['item_encoded_by']);?>
																	</td>
																	<td align="left" width="33%">
																		Report Received By:<br>
																		<strong><?php echo strtoupper($db->getValue('employee','CONCAT(fname," ",left(mname,1),". ",lname)',array('emp_id'=>$rRec['report_received_by'])));?></strong><br>
																		<?php echo position($rRec['report_received_by']);?>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
			</div>
			<?php } ?>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/fullcalendar.min.js'></script>
<script src='../js/jquery.dataTables.min.js'></script>
<script src="../js/excanvas.js"></script>
<script src="../js/jquery.flot.js"></script>
<script src="../js/jquery.flot.pie.js"></script>
<script src="../js/jquery.flot.stack.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.chosen.min.js"></script>
<script src="../js/jquery.uniform.min.js"></script>
<script src="../js/jquery.cleditor.min.js"></script>
<script src="../js/jquery.noty.js"></script>
<script src="../js/jquery.elfinder.min.js"></script>
<script src="../js/jquery.raty.min.js"></script>
<script src="../js/jquery.iphone.toggle.js"></script>
<script src="../js/jquery.uploadify-3.1.min.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/jquery.imagesloaded.js"></script>
<script src="../js/jquery.masonry.min.js"></script>
<script src="../js/jquery.knob.modified.js"></script>
<script src="../js/jquery.sparkline.min.js"></script>
<script src="../js/counter.js"></script>
<script src="../js/retina.js"></script>
<script src="../js/custom.js"></script>
<script>
$(document).ready(function(){
	/*
	$('#lclose,#row2').hide();
	$('#lopen').click(function(){
		//$('#row2').fadeIn();
		$('#row2').slideDown('slow');
		$(this).hide();
		$('#lclose').show();
	});
	$('#lclose').click(function(){
		$('#row2').slideUp();
		$(this).hide();
		$('#lopen').show();
	});*/

});
<?php if(isset($_SESSION['porid_update'])){?>
manage('por<?php echo $_SESSION['porid_update']?>Open','por<?php echo $_SESSION['porid_update']?>Close','por<?php echo $_SESSION['porid_update']?>row');
<?php unset($_SESSION['porid_update']);} ?>
function manage(btnOpn,btnClose,rw){
	var x = document.getElementById(rw);
	var bO = document.getElementById(btnOpn);
	var bC = document.getElementById(btnClose);
	if(x.style.display==='none'){
		x.style.display='';
		bO.style.display='none';
		bC.style.display='';
	}
	else{
		x.style.display='none';
		bO.style.display='';
		bC.style.display='none';
	}
}
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>