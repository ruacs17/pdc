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

$year = ( isset($_SESSION['bp_selYr']) && !empty($_SESSION['bp_selYr']) ) ? $_SESSION['bp_selYr'] : date('Y');
#$selDep = ( isset($_SESSION['bp_dep']) && !empty($_SESSION['bp_dep']) ) ? $_SESSION['bp_dep'] : '';
$selDep = ( isset($_REQUEST['bpdepid']) && !empty($_REQUEST['bpdepid']) ) ? functions::decode($_REQUEST['bpdepid']) : '';

$bgPlan='#F9E3CC';$bgActual='#96C3E9';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Budget Plan VS Actual Report</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
	<script src="../js/inputInt.js"></script>
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
	<style>
	/* Style the header */
	.header {
		background: #CCC;
	}
	/* The sticky class is added to the header with JS when it reaches its scroll position */
	.sticky {
		position: fixed;
		top: 0;
		width: 97%
	}
	.hideit{
		display:none;
	}
	.brdrNone{
		border:none;
	}
	</style>
	<!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Plan vs. Actual Budget Report</h2>
		</div>
		<div class="box-content" align="center">
			<div class="box-content">
				<form method="post">
				<table class="table table-bordered table-hover" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="25%"><div align="left">Item</div></th>
							<th width="33%" colspan="4"><div align="center">Plan</div></th>
							<th width="18%" colspan="2"><div align="center">Actual</div></th>
							<th width="10%" rowspan="2"><div align="center">Difference</div></th>
							<th width="10%" rowspan="2"><div align="center">%</div></th>
							<th width="10%" rowspan="2">&nbsp;</th>
						</tr>
						<tr style="background-color:#CCC;">
							<th><div align="center">&nbsp;</div></th>
							<th><div align="center">Qty</div></th>
							<th><div align="center">UOM</div></th>
							<th><div align="center">Price</div></th>
							<th><div align="center">Amount</div></th>
							<th><div align="center">Qty</div></th>
							<th><div align="center">Amount</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$q = $db->query("SELECT bp.bp_id,dep.dep_id,dep.dep_name,bp.item,bp_qty,bp_unit,bp_price,bp_amount,bp.bp_year,sum(ba.act_qty) as 'actualqty',sum(ba.act_amount) as 'actualamount' FROM budget_plan bp left join budget_actual ba on ba.bp_id=bp.bp_id left join department dep on bp.dep_id=dep.dep_id WHERE bp.bp_year='".$db->clean($year)."' AND bp.dep_id='".$db->clean($selDep)."' GROUP BY bp.bp_id,dep.dep_id,dep.dep_name,bp.item,bp_qty,bp_unit,bp_price,bp_amount,bp.bp_year ORDER BY bp.item");
					$countRec=0;$totalPlanAmount=0;$totalActualAmount=0;$totalDiff=0;
					while($r = $db->fetch_array($q)):
						$id = $r['bp_id'];
						$countRec++;
						$amnt = ($r['bp_amount']) ? $r['bp_amount'] : 0;
						$actAmnt = ($r['actualamount']) ? $r['actualamount'] : 0;
						$diff = $amnt - $actAmnt;
						$totalPlanAmount += $r['bp_amount'];
						$totalActualAmount += $r['actualamount'];
						$totalDiff += $diff;
						$percentage = ($amnt) ? number_format(($diff / $amnt) * 100,3) : 0;
					?>
						<tr>
							<td><div align="left"><?php echo $r['item'] ?></div></td>
							<td style="background-color:<?php echo $bgPlan?>;"><div align="center"><?php echo number_format($r['bp_qty']) ?></div></td>
							<td style="background-color:<?php echo $bgPlan?>;"><div align="center"><?php echo $r['bp_unit'] ?></div></td>
							<td style="background-color:<?php echo $bgPlan?>;"><div align="center"><?php echo functions::formatMoney($r['bp_price']) ?></div></td>
							<td style="background-color:<?php echo $bgPlan?>;"><div align="center"><?php echo functions::formatMoney($r['bp_amount']) ?></div></td>
							<td style="background-color:<?php echo $bgActual?>;"><div align="center"><?php echo number_format($r['actualqty']) ?></div></td>
							<td style="background-color:<?php echo $bgActual?>;"><div align="center"><?php echo functions::formatMoney($r['actualamount']) ?></div></td>
							<td style="padding-right:40px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><?php echo functions::formatMoney($diff) ?></div></td>
							<td style="padding-right:40px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><?php echo $percentage ?>%</div></td>
							<td>
								<div align="center">
									<a id="vw<?php echo $id?>" class="btn btn-mini btn-info thickbox" onclick="showThis(this.id,'dash-budget-plan-detail.php?bpid=<?php echo functions::encode($id);?>','Plan Detail')"><i class="halflings-icon white search"></i></a>
								</div>
							</td>
						</tr>
					<?php endwhile;
					if($countRec==0){echo '<tr><td colspan="8"><div align="center">--Nothing to Report--</div></td></tr>';}else{?>
						<tr><td colspan="9">&nbsp;</td></tr>
						<tr>
							<td><div align="left">&nbsp;</div></td>
							<td><div align="center">&nbsp;</div></td>
							<td><div align="center">&nbsp;</div></td>
							<td><div align="center">&nbsp;</div></td>
							<td style="background-color:<?php echo $bgPlan?>;"><div align="center"><strong><?php echo functions::formatMoney($totalPlanAmount) ?></strong></div></td>
							<td><div align="center">&nbsp;</div></td>
							<td style="background-color:<?php echo $bgActual?>;"><div align="center"><strong><?php echo functions::formatMoney($totalActualAmount) ?></strong></div></td>
							<td style="padding-right:40px;<?php echo ($totalDiff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo functions::formatMoney($totalDiff) ?></strong></div></td>
							<td style="padding-right:40px;<?php echo ($totalDiff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo ($totalPlanAmount) ? number_format(($totalDiff / $totalPlanAmount)*100,3) : 0;?>%</strong></div></td>
							<td></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
	<?php if(empty($bp_id)){?>$('#mngFrm').hide();<?php } ?>
	$('#btnDispAdd').click(function(){
		if( $('#mngFrm').is(":visible") ){
			$('#mngFrm').hide('slow');
			$('#btnDispAdd').val('ADD');
		}
		else{
			$('#mngFrm').show('slow');
			$('#btnDispAdd').val('Cancel');
		}
	});
	
	$('#btnCancel').click(function(){
		<?php if(empty($bp_id)){?>
		$('#mngFrm').hide('slow');
		$('#btnDispAdd').val('ADD');
		<?php }else{ ?>
			window.location="<?php echo functions::pageName()?>"
		<?php } ?>
	});
	
	$('#btnSave').click(function(){
		if( confirm('Do you want to save this item?') )
			return true;
		else
			return false;
	});
});
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;

// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction(){
	if (window.pageYOffset > sticky){
		header.classList.add("sticky");
		header.classList.remove("hideit");
	}else{
		header.classList.remove("sticky");
		header.classList.add("hideit");
	}
}
function delt(){
	if(confirm('Do you want to remove this Item?'))
		return true;
	else
		return false;
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
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 7000,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>