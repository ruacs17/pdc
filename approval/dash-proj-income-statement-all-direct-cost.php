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
$mnth = ( isset($_REQUEST['mnth']) && !empty($_REQUEST['mnth']) ) ? $db->clean(functions::decode($_REQUEST['mnth'])) : '';
$expense_type = ( isset($_REQUEST['expenseType']) && !empty($_REQUEST['expenseType']) ) ? functions::decode($_REQUEST['expenseType']) : '';
$cost_type = ( isset($_REQUEST['ct']) && !empty($_REQUEST['ct']) ) ? functions::decode($_REQUEST['ct']) : 0;
$inhouse = ( isset($_REQUEST['ih']) && !empty($_REQUEST['ih']) ) ? functions::decode($_REQUEST['ih']) : 0;

$arrProject=array();

$vProj = 'SELECT DISTINCT p.proj_name FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$db->clean($mnth).'"';
$vProj .= ($expense_type) ? ' AND id.expense_type="'.$db->clean($expense_type).'"' : '';
$vProj .= ($cost_type) ? ' AND id.cost_type="'.$db->clean($cost_type).'"' : '';
$vProj .= ' ORDER BY p.proj_name';
$qVProj = $db->query($vProj);
while($rVProj = $db->fetch_array($qVProj)):
	$arrProject = functions::insert_array($arrProject,$rVProj['proj_id']);
endwhile;

$overPO ='SELECT DISTINCT p.proj_id FROM po p, view_po_payment vpp, item_deduction id WHERE p.po_id=vpp.po_id AND p.category_id=it.item_id AND paid > 0 AND left(p.po_date,7)="'.$mnth.'"';
$overPO .= ($expense_type) ? ' AND id.expense_type="'.$db->clean($expense_type).'"' : '';
$overPO .= ($cost_type) ? ' AND id.cost_type="'.$db->clean($cost_type).'"' : '';
$overPO .= ' ORDER BY p.proj_name';
$qOverPO = $db->query($overPO);
while($rOverPO = $db->fetch_array($qOverPO)):
	$arrProject = functions::insert_array($arrProject,$rOverPO['proj_id']);
endwhile;

$overInhouseMaterial = 'SELECT DISTINCT im.proj_id FROM inhouse_material im, item_deduction id WHERE im.category_id=id.item_id AND is_paid="2" AND left(im.im_date,7)="'.$mnth.'"';
$overInhouseMaterial .= ($expense_type) ? ' AND id.expense_type="'.$db->clean($expense_type).'"' : '';
$overInhouseMaterial .= ($cost_type) ? ' AND id.cost_type="'.$db->clean($cost_type).'"' : '';
$overInhouseMaterial .= ' ORDER BY im.proj_name';
$qOverInhouseMaterial = $db->query($overInhouseMaterial);
while($rOverInhouseMaterial = $db->fetch_array($qOverInhouseMaterial)):
	$arrProject = functions::insert_array($arrProject,$rOverInhouseMaterial['proj_id']);
endwhile;

if($inhouse=='equipment'){
	$overInhouseEquipment = $db->query('SELECT DISTINCT proj_id FROM inhouse_equip_leasing iel WHERE left(iel_date,7)="'.$mnth.'" AND is_paid="2"');
	while($rOverInhouseEquipment = $db->fetch_array($overInhouseEquipment)):
		$arrProject = functions::insert_array($arrProject,$rOverInhouseEquipment['proj_id']);
	endwhile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Income Statement All Direct Cost</title>
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
<div class="row-fluid">
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
					foreach($arrProject as $project):
						$projAmount=0;

						$vProj = 'SELECT sum(amount) FROM `voucher_detail` vd, item_deduction id, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=id.item_id AND left(vd_date,7)="'.$db->clean($mnth).'"';
						$vProj .= ($expense_type) ? ' AND id.expense_type="'.$db->clean($expense_type).'"' : '';
						$vProj .= ($cost_type) ? ' AND id.cost_type="'.$db->clean($cost_type).'"' : '';
						$vProj .= ' AND p.proj_id="'.$project.'"';
						$qVProj = $db->query($vProj);
						$projAmount = $db->result($qVProj);

						$overPO ='SELECT sum(cost) FROM po p, view_po_payment vpp, item_deduction id WHERE p.po_id=vpp.po_id AND p.category_id=id.item_id AND left(p.po_date,7)="'.$mnth.'" AND vpp.paid > 0');
						$overPO .= ($expense_type) ? ' AND id.expense_type="'.$db->clean($expense_type).'"' : '';
						$overPO .= ($cost_type) ? ' AND id.cost_type="'.$db->clean($cost_type).'"' : '';
						$overPO .= ' AND p.proj_id="'.$project.'"';
						$qOverPO = $db->query($overPO);
						$projAmount += $db->result($qOverPO);

						$qFromInhouseMaterial = $db->query(' AND im.category_id="'.$category_id.'"');

						$overInhouseMaterial = 'SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi, item_deduction id WHERE im.im_id=imi.im_id AND im.category_id=id.item_id AND left(im_date,7)="'.$mnth.'" AND is_paid="2"';
						$overInhouseMaterial .= ($expense_type) ? ' AND id.expense_type="'.$db->clean($expense_type).'"' : '';
						$overInhouseMaterial .= ($cost_type) ? ' AND id.cost_type="'.$db->clean($cost_type).'"' : '';
						$overInhouseMaterial .= ' AND im.proj_id="'.$project.'"';
						$qOverInhouseMaterial = $db->query($overInhouseMaterial);
						$projAmount += $db->result($qOverInhouseMaterial);

						if($inhouse==='equipment'){
							$overInhouseEquipment = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) as res FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND left(iel_date,7)="'.$mnth.'" AND is_paid="2" AND iel.proj_id="'.$project.'"');
							$projAmount += $db->result($overInhouseEquipment);
						}
						$totalAmount += $projAmount;
					?>
					<tr>
						<td><div align="left"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$project));?></div></td>
						<td><div align="center"><?php echo functions::formatMoney($projAmount)?></div></td>
					</tr>
					<?php endforeach;?>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
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