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

$rm = (isset($_REQUEST['rm']) && !empty($_REQUEST['rm']) ) ? functions::decode($_REQUEST['rm']) : 0;
$pos_id = (isset($_REQUEST['id']) && !empty($_REQUEST['id']) ) ? functions::decode($_REQUEST['id']) : 0;
$_SESSION['notif_id_list']=$pos_id;
$q = $db->select('po_issuance','*',array('pos_id'=>$pos_id));
$r = $db->fetch_array($q);
$txpono='';

if($rm){
	$db->delete('po_issuance_details',array('pos_id'=>$pos_id,'po_id'=>$rm));
	$_SESSION['notif_delt']='Purchase Order Removed!';
	functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id));
	die();
}
if( isset($_POST['btnSearch']) ){
	$txpono = ( isset($_POST['txpono']) && !empty($_POST['txpono']) ) ? $_POST['txpono'] : '';
	$qp = $db->select('po p, po_item poi','*',array('po_no'=>$txpono),'AND p.po_id=poi.po_id');
}
if( isset($_POST['btnAdd']) ){
	$txpono = ( isset($_POST['txpono']) && !empty($_POST['txpono']) ) ? $_POST['txpono'] : '';
	$po_id = $db->getValue('po','po_id',array('po_no'=>$txpono));
	//if( $db->getValue('po_issuance_details','count(*)',array('pos_id'=>$pos_id,'po_id'=>$po_id))==0 ){
	if( $db->getValue('po_issuance_details','count(*)',array('pos_id'=>$pos_id))==0 && $po_id ){
		$posd_id = $db->insert('po_issuance_details',array('pos_id'=>$pos_id,'po_id'=>$po_id));
		if($posd_id){
			$q = $db->select('po p, po_item poi','*',array('p.po_id'=>$po_id),'AND p.po_id=poi.po_id');
			#echo $db->last_query;
			while($r = $db->fetch_array($q)):
				$db->insert('po_issuance_item',array('posd_id'=>$posd_id,'pos_id'=>$pos_id,'po_id'=>$po_id,'po_item_id'=>$r['po_item_id'],'qty_issue'=>$r['qty_delivered']));
				#echo '<br>';
			endwhile;
			$_SESSION['notif_add']='Purchase Order Added!';
			functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id));
			die();
		}
	}
	else{
		functions::say('PO Already Exist!');
		functions::sendTo(functions::pageName().'?id='.functions::encode($pos_id));
		die();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>P.O. Issuance Manage Details</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
	<script src="../js/inputInt.js"></script>
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
	<style>.tdSpace{padding: 0px 0px 0px 10px;}</style>
	<style>.amnt{padding-right: 12px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Issuance Details</h2>
		</div>
		<div class="box-content">
			<div align="center">
				<form method="post" action="po-issuance-manage-detail-edit.php?id=<?php echo functions::encode($pos_id)?>">
					<div align="right" style="padding:10px;">
						<a href="po-issuance-manage.php?id=<?php echo functions::encode($pos_id) ?>" class="btn btn-small btn-warning" title="Update this issuance"><i class="halflings-icon pencil white"></i>&nbsp;&nbsp;Update</a>
						<a href="po-issuance-print.php?id=<?php echo functions::encode($pos_id) ?>" class="btn btn-small btn-info" title="Print this issuance"><i class="halflings-icon print white"></i></a>
					</div>
					<table width="90%" border="0" style="font-size:12px;">
						<tr>
							<td>
								<div align="right">Series No. <strong><?php echo $r['series_no']; ?></strong></div>
								<div align="left">
									P.O.<input type="text" name="txpono" id="txpono" value="<?php echo $txpono; ?>">
									<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-info btn-small">
								</div>
								<table width="100%" border="1">
									<thead>
										<tr style="background-color:#CCC">
											<th height="30">QTY</th>
											<th>UOM</th>
											<th>DESCRIPTION</th>
											<th width="15%">UNIT PRICE</th>
											<th width="15%">TOTAL COST</th>
										</tr>
									</thead>
									<tbody>
									<?php
									$arrProj=array();
									$total_cost=0;
									if($txpono){
										$qp = $db->select('po p','*',array('po_no'=>$txpono));
									}
									else{
										$qp = $db->select('po_issuance pis, po_issuance_details pisd, po p','*',array('pis.pos_id'=>$pos_id),'AND pis.pos_id=pisd.pos_id AND pisd.po_id=p.po_id');
									}
									$countPO = $db->num_rows($qp);
									while($rp = $db->fetch_array($qp)):
										$arrProj[$rp['proj_id']]=$rp['proj_id'];
									?>
										<tr>
											<td colspan="5" style="padding:10px;">
												<?php echo $rp['po_no']; ?>
												<?php if(empty($txpono)){ ?>&nbsp;&nbsp;<a title="Update this P.O" data-rel="tooltip" href="po-issuance-manage-detail-edit.php?id=<?php echo functions::encode($pos_id);?>&poid=<?php echo functions::encode($rp['po_id']);?>&posd=<?php echo functions::encode($rp['posd_id']);?>"><i class="halflings-icon pencil"></i></a>&nbsp;&nbsp;<a onClick="return ask()" title="Remove this P.O" data-rel="tooltip" href="<?php echo functions::pageName()?>?id=<?php echo functions::encode($pos_id);?>&rm=<?php echo functions::encode($rp['po_id']);?>"><i class="halflings-icon minus-sign"></i></a><?php } ?>
											</td>
										</tr>
									<?php
										$po_total=0;
										$qpoi = $db->select('po_issuance_item posi, po_item poi','*',array('posi.po_id'=>$rp['po_id'],'pos_id'=>$pos_id),'AND posi.po_item_id=poi.po_item_id');
										#$qpoi = $db->select('po_item','*',array('po_id'=>$rp['po_id']));
										while($rpoi = $db->fetch_array($qpoi)):
											$cost = $rpoi['cost'] * $rpoi['qty_issue'];
											$total_cost += $cost;
											$po_total += $cost;
										?>
										<tr>
											<td><div align="center"><?php echo number_format($rpoi['qty_issue']);?></div></td>
											<td><div align="center"><?php echo $rpoi['unit'] ?></div></td>
											<td class="tdSpace"><div><?php echo $rpoi['item'] ?></div></td>
											<td class="amnt"><div align="right"><?php echo functions::formatMoney($rpoi['cost']);?></div></td>
											<td class="amnt"><div align="right"><?php echo functions::formatMoney($cost) ?></div></td>
										</tr>
										<?php endwhile; ?>
										<?php if($countPO > 1){ ?>
										<tr>
											<td colspan="5" style="padding:5px;"><div align="right"><strong><?php echo functions::formatMoney($po_total); ?></strong></div></td>
										</tr>
										<?php } ?>
									<?php endwhile; ?>
										<tr>
											<td colspan="5" style="padding:7px;"><div align="right"> Total <strong><?php echo functions::formatMoney($total_cost); ?></strong></div></td>
										</tr>
									</tbody>
								</table>
								<div style="padding:20px;">
									<?php if(count($arrProj) == 1){ ?>
									<div>Project Name / Location:
									<?php foreach($arrProj as $projid): echo '<strong>'.$db->getValue('project','proj_name',array('proj_id'=>$projid)).'</strong>'; endforeach; ?>
									</div>
									<?php }else{?>
									<div>Project Name / Location:</div>
									<?php foreach($arrProj as $projid): echo '<div style="padding-left:30px;"><strong>'.$db->getValue('project','proj_name',array('proj_id'=>$projid)).'</strong></div>'; endforeach; ?>
									<?php } ?>
								</div>
								<table width="100%" border="0">
									<tr>
										<td align="center" width="25%">Prepared and Issued by:</td>
										<td align="center" width="25%">Reviewed by:</td>
										<td align="center" width="25%">Approved by:</td>
										<td align="center" width="25%">Checked and Received by:</td>
									</tr>
									<tr>
										<td height="80" align="center" valign="bottom">
											<div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['prepared_by'])) ?></strong></div>
											<div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['prepared_by']),'AND ep.dp_id=dp.dp_id') ?></i></div>
										</td>
										<td align="center" valign="bottom">
											<div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['reviewed_by'])) ?></strong></div>
											<div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['reviewed_by']),'AND ep.dp_id=dp.dp_id') ?></i></div>
										</td>
										<td align="center" valign="bottom">
											<div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['approved_by'])) ?></strong></div>
											<div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['approved_by']),'AND ep.dp_id=dp.dp_id') ?></i></div>
										</td>
										<td align="center" valign="bottom">
											<div><strong><?php echo $db->getValue('employee','concat(fname," ",lname)',array('emp_id'=>$r['received_by'])) ?></strong></div>
											<div><i><?php echo $db->getValue('emp_position ep, dep_position dp','pos_name',array('emp_id'=>$r['received_by']),'AND ep.dp_id=dp.dp_id') ?></i></div>
										</td>
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
					</table>
				</form>
			</div>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function ask(){
    if(confirm('Do you want to remove this P.O.?'))
        return true;
    else
        return false; 
}
</script>
<?php if(isset($_SESSION['notif_delt'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_delt'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_delt']);} ?>
<?php if(isset($_SESSION['notif_add'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_add'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_add']);} ?>
<!-- end: JavaScript-->
</body>
</html>