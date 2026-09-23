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

$count=0;
$c_id=(isset($_REQUEST['cid']) && !empty($_REQUEST['cid']) ) ? functions::decode($_REQUEST['cid']) : 0;

$yr=date('Y');
$yrSearch = " AND left(vd_date,4)='".$yr."'";
$yrSearchPO = " AND left(po_date,4)='".$yr."'";
$yr = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : 0;
if($yr){
	$yrSearch = " AND left(vd_date,4)='".$db->clean($yr)."'";
	$yrSearchPO = " AND left(po_date,4)='".$db->clean($yr)."'";
}

$arrItem = array();
$total_amount=0;

$qPO = $db->select('po','*',array('category_id'=>$c_id,'received'=>1),$yrSearchPO.'ORDER BY po_date DESC');
while($rPO = $db->fetch_array($qPO)):
	$category = $db->getValue('item_deduction','name',array('item_id'=>$rPO['category_id']));
	$projName = $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id']));
	$amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id']));
	$item='';
	$qPOI = $db->select('po_item','*',array('po_id'=>$rPO['po_id']));
	$countPOI=0;
	$total_amount += $amount;
	while($rPOI = $db->fetch_array($qPOI)):
		if($countPOI)
			$item.='<br>';
		$item .= $rPOI['item'].' ( '.round($rPOI['qty_delivered'],2).' '.$rPOI['unit'].' x '.functions::formatMoney($rPOI['cost']).')';
		$countPOI++;
	endwhile;
	$arrItem[] = array('itemType'=>'P.O.','itemID'=>$rPO['po_no'],'date'=>$rPO['po_date'],'project'=>$projName,'category'=>$category,'item'=>$item,'amount'=>$amount);
endwhile;

$qvDetails = $db->select('voucher_detail','*',array('category_id'=>$c_id),$yrSearch.'ORDER BY vd_date DESC');
while($rvDetails = $db->fetch_array($qvDetails)):
	$total_amount += $rvDetails['amount'];
	$projName = $db->getValue('project','proj_name',array('proj_id'=>$rvDetails['proj_id']));
	$vid = $db->query('SELECT voucher_id FROM voucher_detail vd, voucher_particular vp WHERE vd.vp_id=vp.vp_id AND vd.vd_id="'.$db->clean($rvDetails['vd_id']).'"');
	$voucher_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$db->result()));
	$category = $db->getValue('item_deduction','name',array('item_id'=>$rvDetails['category_id']));
	$arrItem[] = array('itemType'=>'Voucher','itemID'=>$voucher_no,'date'=>$rvDetails['vd_date'],'project'=>$projName,'category'=>$category,'item'=>$rvDetails['item'],'amount'=>$rvDetails['amount']);
endwhile;
if(count($arrItem))
	functions::sortMultiArray($arrItem,$orderBy='date',$sort_AscDesc="DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Project Cost View</title>
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
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Particular Details</h2>
		</div>
		<div class="box-content">
			<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-striped table-bordered bootstrap-datatableA datatableA" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="7%" scope="col"><div align="left">Date</div></th>
						<th width="9%" scope="col"><div align="left">Reference No.</div></th>
						<th width="30%" scope="col"><div align="left">Project</div></th>
						<th width="15%" scope="col"><div align="left">Charges Category</div></th>
						<th width="30%" scope="col"><div align="left">Item Detail</div></th>
						<th width="8%" scope="col"><div align="right">Amount</div></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($arrItem as $item):?>
					<tr>
						<td><?php echo functions::datearr($item['date']);?></td>
						<td><?php echo $item['itemType']. ': '.$item['itemID'];?></td>
						<td><?php echo $item['project']?></td>
						<td><?php echo $item['category'];?></td>
						<td><?php echo $item['item']?></td>
						<td><div align="right"><?php echo functions::formatMoney($item['amount']);?></div></td>
					</tr>
					<?php endforeach;?>
				<tbody>
			</table>
			<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-striped table-bordered" style="font-size:12px;">
				<tr>
					<td width="85%"><div align="right"><strong>Total Amount</strong></div></td>
					<td width="15%"><strong><?php echo functions::formatMoney($total_amount)?></strong></td>
				</tr>
			</table><p>&nbsp;</p>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<!-- end: JavaScript-->
</body>
</html>