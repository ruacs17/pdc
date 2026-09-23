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
$category_id = ( isset($_REQUEST['ct']) && !empty($_REQUEST['ct']) ) ? functions::decode($_REQUEST['ct']) : 0;

$arrProject=array();

$qVProj = $db->query('SELECT p.proj_id,p.proj_name FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND vd.category_id="'.$db->clean($category_id).'" AND left(vd_date,7)="'.$db->clean($mnth).'" ORDER BY p.proj_name');
while($rVProj = $db->fetch_array($qVProj)):
	$arrProject[$rVProj['proj_id']]=array('name'=>$rVProj['proj_name'],'id'=>$rVProj['proj_id'],'type'=>'project');
endwhile;

$overPO = $db->query('SELECT DISTINCT p.proj_id,proj_name FROM po p, view_po_payment vpp, project proj WHERE p.proj_id=proj.proj_id AND p.po_id=vpp.po_id AND paid > 0 AND p.category_id="'.$db->clean($category_id).'" AND left(p.po_date,7)="'.$mnth.'" ORDER BY proj.proj_name');
while($rOverPO = $db->fetch_array($overPO)):
	$arrProject[$rOverPO['proj_id']]=array('name'=>$rOverPO['proj_name'],'id'=>$rOverPO['proj_id'],'type'=>'project');
endwhile;

$overInhouseMaterial = $db->query('SELECT DISTINCT im.proj_id,proj_name FROM inhouse_material im, project proj WHERE im.proj_id=proj.proj_id AND im.category_id="'.$db->clean($category_id).'" AND left(im.im_date,7)="'.$mnth.'" ORDER BY proj.proj_name');
while($rOverInhouseMaterial = $db->fetch_array($overInhouseMaterial)):
	$arrProject[$rOverInhouseMaterial['proj_id']]=array('name'=>$rOverInhouseMaterial['proj_name'],'id'=>$rOverInhouseMaterial['proj_id'],'type'=>'project');
endwhile;

$overInhouseMaterial = $db->query('SELECT DISTINCT im.payee as prjnme FROM inhouse_material im WHERE im.category_id="'.$db->clean($category_id).'" AND left(im.im_date,7)="'.$mnth.'" ORDER BY im.payee');
while($rOverInhouseMaterial = $db->fetch_array($overInhouseMaterial)):
	$arrProject[sha1($rOverInhouseMaterial['prjnme'])]=array('name'=>$rOverInhouseMaterial['prjnme'],'id'=>$rOverInhouseMaterial['prjnme'],'type'=>'payee');
endwhile;

if(count($arrProject))
    functions::sortMultiArray($arrProject,$orderBy="name",$ascDes="ASC");

$arrCategory=array();
$arrProjCost=array();

$q1 = $db->query('SELECT vd.proj_id as prj, round(sum(amount),2) as amnt FROM voucher_detail vd, item_deduction itd, project p WHERE p.proj_id=vd.proj_id AND vd.category_id=itd.item_id AND vd.category_id="'.$db->clean($category_id).'" AND left(vd_date,7)="'.$db->clean($mnth).'" GROUP BY prj');
while($r1 = $db->fetch_array($q1)):
	$proj_id = $r1['prj'];
	$amount = $r1['amnt'];
	if(isset($arrProjCost[$proj_id])){
		$arrProjCost[$proj_id] = ($arrProjCost[$proj_id] + $amount);
	}
	else{
		$arrProjCost[$proj_id] = $amount;
	}
endwhile;

$q2 = $db->query('SELECT p.proj_id as prj, round(sum(cost),2) as amnt FROM po p, view_po_payment vpp WHERE p.po_id=vpp.po_id AND left(p.po_date,7)="'.$mnth.'" AND p.category_id="'.$db->clean($category_id).'" AND vpp.paid > 0 GROUP BY prj');
while($r2 = $db->fetch_array($q2)):
	$proj_id = $r2['prj'];
	$amount = $r2['amnt'];
	if(isset($arrProjCost[$proj_id])){
		$arrProjCost[$proj_id] = ($arrProjCost[$proj_id] + $amount);
	}
	else{
		$arrProjCost[$proj_id] = $amount;
	}
endwhile;

$q3 = $db->query('SELECT im.proj_id as prj, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.category_id="'.$db->clean($category_id).'" AND left(im.im_date,7)="'.$mnth.'" GROUP BY prj');
while($r3 = $db->fetch_array($q3)):
	if($r3['prj']){
		$proj_id = $r3['prj'];
		$amount = $r3['amnt'];
		if(isset($arrProjCost[$proj_id])){
			$arrProjCost[$proj_id] = ($arrProjCost[$proj_id] + $amount);
		}
		else{
			$arrProjCost[$proj_id] = $amount;
		}
	}

endwhile;

$q31 = $db->query('SELECT im.payee as prj, round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as amnt FROM inhouse_material im, inhouse_material_item imi, item_deduction itd WHERE im.category_id=itd.item_id AND im.im_id=imi.im_id AND im.category_id="'.$db->clean($category_id).'" AND left(im.im_date,7)="'.$mnth.'" GROUP BY prj');
#echo '<br>'.$db->last_query;
while($r31 = $db->fetch_array($q31)):
	$proj_id = $r31['prj'];
	$amount = $r31['amnt'];
	if(isset($arrProjCost[$proj_id])){
		$arrProjCost[$proj_id] = ($arrProjCost[$proj_id] + $amount);
	}
	else{
		$arrProjCost[$proj_id] = $amount;
	}
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Operation Expenses Detail</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
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
<div id="spinner"></div>
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>OPERATING EXPENSES</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<table width="50%" align="center" border="0" class="table table-bordered table-striped table-hover" style="font-size:12px;" >
					<thead>
						<tr style="background-color:#CCCC">
							<th width="70%"><div align="center">Project</div></th>
							<th width="10%"><div align="right" style="padding-right:20px">Amount</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$totalAmount=0;$count=0;
						foreach( $arrProject as $pdetail):
							$amount=$arrProjCost[$pdetail['id']];
							$totalAmount += $amount;
						?>
						<tr>
							<td>
								<div align="left"><?php echo $pdetail['name']; echo ($pdetail['type']=='payee') ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>(Inhouse Payee)</i>' : '';?></div>
							</td>
							<td><div align="right" style="padding-right:20px"><a id="dtv<?php echo $count++?>" class="thickbox" title="Amount Details" style="cursor:pointer;" data-rel="tooltip" onclick="showThis(this.id,'dash-proj-income-statement-all-oper-exp-detail.php?mnth=<?php echo functions::encode($mnth)?>&cat=<?php echo functions::encode($category_id)?>&pid=<?php echo functions::encode($pdetail['id'])?>','Amount Details','1')"><?php echo ($amount) ? functions::formatMoney($amount) : '';?></a></div></td>
						</tr>
						<?php endforeach;?>
						<tr>
							<td>&nbsp;</td>
							<td><div align="right" style="padding-right:20px"><strong><?php echo functions::formatMoney($totalAmount)?></strong></div></td>
						</tr>
					</tbody>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
</body>
</html>