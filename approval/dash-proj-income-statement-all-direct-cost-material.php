<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$mnth = ( isset($_REQUEST['mnth']) && !empty($_REQUEST['mnth']) ) ? functions::decode($_REQUEST['mnth']) : '';

$arrayProject=array();

#get Project from Voucher
$qVoucher = $db->query('SELECT DISTINCT proj_name FROM `voucher_detail` vd, item_deduction id,project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$db->clean($mnth).'" AND id.expense_type="materials"');
while($rVoucher = $db->fetch_array($qVoucher)):
	$arrayProject = functions::insert_array($arrayProject,$rVoucher['proj_name']);
endwhile;

#get Project from P.O
$qPO = $db->query('SELECT DISTINCT proj_name FROM po_item poi, po p,project proj WHERE p.proj_id=proj.proj_id AND p.po_id=poi.po_id AND left(po_date,7)="'.$db->clean($mnth).'" AND vp_id IS NOT NULL');
while($rPO = $db->fetch_array($qPO)):
	$arrayProject = functions::insert_array($arrayProject,$rPO['proj_name']);
endwhile;

#get Project from P.O
$qInhouse = $db->query('SELECT DISTINCT proj_name FROM inhouse_material im, inhouse_material_item imi, project p WHERE p.proj_id=im.proj_id AND im.im_id=imi.im_id AND left(im_date,7)="'.$mnth.'" AND is_paid="2"');
while($rInhouse = $db->fetch_array($qInhouse)):
	$arrayProject = functions::insert_array($arrayProject,$rInhouse['proj_name']);
endwhile;
sort($arrayProject);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Direct Cost</title>
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
			<h2><i class="halflings-icon white list"></i><span class="break"></span>DIRECT COST</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="50%" align="center" border="0" class="table table-bordered table-hover" >
					<tr style="background-color:#E4E1E1">
						<th width="70%"><div align="center">Project</div></th>
						<th width="10%"><div align="center">Amount</div></th>
					</tr>
					<?php
					$totalAmount=0;
					foreach($arrayProject as $projectName):
						$project_expense = 0;
						$project = $db->getValue('project','proj_id',array('proj_name'=>$projectName));

						#$qCostVoucher = $db->query('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND p.proj_name="'.$db->clean($projectName).'" AND left(vd_date,7)="'.$db->clean($mnth).'" AND id.expense_type="materials"');
						$qCostVoucher = $db->prepareQ('SELECT sum(amount) as amnt FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND p.proj_name=? AND left(vd_date,7)=? AND id.expense_type="materials"',array($projectName,$mnth));
						#echo $db->last_query;
						$project_expense += $db->result($qCostVoucher,0);

						#$qCostPO = $db->query('SELECT sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ) as res FROM po_item poi, po p, project pj WHERE p.proj_id=pj.proj_id AND p.po_id=poi.po_id AND pj.proj_name="'.$db->clean($projectName).'" AND left(po_date,7)="'.$db->clean($mnth).'" AND vp_id IS NOT NULL');
						$qCostPO = $db->prepareQ('SELECT sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ) as res FROM po_item poi, po p, project pj WHERE p.proj_id=pj.proj_id AND p.po_id=poi.po_id AND pj.proj_name=? AND left(po_date,7)=? AND vp_id IS NOT NULL',array($projectName,$mnth));
						$project_expense += $db->result($qCostPO,0);

						#$qCostInhouse = $db->query('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi, project p WHERE p.proj_id=im.proj_id AND im.im_id=imi.im_id AND p.proj_name="'.$db->clean($projectName).'" AND left(im_date,7)="'.$db->clean($mnth).'" AND is_paid="2"');
						$qCostInhouse = $db->prepareQ('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi, project p WHERE p.proj_id=im.proj_id AND im.im_id=imi.im_id AND p.proj_name=? AND left(im_date,7)=? AND is_paid="2"',array($projectName,$mnth));
						$project_expense += $db->result($qCostInhouse,0);
						$totalAmount += $project_expense;
					?>
					<tr>
						<td><div align="left"><?php echo $projectName;?></div></td>
						<td><div align="center"><?php echo functions::formatMoney($project_expense)?></div></td>
					</tr>
					<?php
					endforeach;
					?>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><div align="right"><strong>Total</strong></div></td>
						<td><div align="center"><strong><?php echo functions::formatMoney($totalAmount)?></strong></div></td>
					</tr>
				</table>
			</form>
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
<!-- end: JavaScript-->
</body>
</html>