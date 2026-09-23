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
function monthDays($month=0,$year=0){
	$list = array();
	$month = ($month) ? $month : date('m');
	$year = ($year) ? $year : date('Y');
	for($d=1; $d<=31; $d++):
		$time = mktime(12,0,0,$month,$d,$year);
		if( date('m',$time)==$month )
			$list[]=date('Y-m-d',$time);
	endfor;
	return $list;
}

$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');

if( isset($_POST['btnSearch']) ){
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
	$_SESSION['bp_selYr']=$year;
	functions::sendTo(functions::pageName());
	die();
}
$year = ( isset($_SESSION['bp_selYr']) && !empty($_SESSION['bp_selYr']) ) ? $_SESSION['bp_selYr'] : date('Y');


$bgPlan='#F9E3CC';$bgActual='#96C3E9';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Budget Plan VS Actual</title>
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Plan vs. Actual Budget By Department Report</h2>
		</div>
		<div class="box-content" align="center">
			<ul class="nav tab-menu nav-tabs">
				<li><a href="dash-budget-plan.php">Plan</a></li>
				<li class="active"><a href="dash-budget-plan-report.php" style="opacity:.9">Report</a></li>
			</ul>
			<div class="box-content">
				<form method="post">
					<div align="left" class="input-append">
						<select name="bdYear" id="bdYear" style="width:140px;">
							<option value="">--Select Year--</option>
							<?php for($yr=(date('Y')+3); $yr>=2012; $yr--): ?>
							<option value="<?php echo functions::encode($yr)?>" <?php if($year==$yr)echo 'selected="selected"';?>><?php echo $yr?></option>
							<?php endfor; ?>
						</select>
						<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small">
					</div>
				</form>
				<div align="center">
					<div style="width:80%">
						<table class="table table-bordered table-hover" style="font-size:12px;">
							<thead>
								<tr style="background-color:#CCC;">
									<th>Department</th>
									<th width="15%"><div align="center">Plan</div></th>
									<th width="15%"><div align="center">Actual</div></th>
									<th width="15%"><div align="center">Difference</div></th>
									<th><div align="center">%</div></th>
									<th><div align="center">Details</div></th>
								</tr>
							</thead>
							<tbody>	
							<?php
							$totalPlan=0;$totalActual=0;$totalDiff=0;
							$qDH = $db->select('department','*',array(),'WHERE dep_head is NULL AND dep_id IN (SELECT DISTINCT dep_id FROM budget_plan WHERE bp_year="'.$db->clean($year).'") ORDER BY dep_name');
							while($r = $db->fetch_array($qDH)):
								$dep_id = $r['dep_id'];
								$dep_name = $r['dep_name'];
								$totalPlan += $plan = $db->getValue('budget_plan','sum(bp_amount)',array('bp_year'=>$year,'dep_id'=>$dep_id));
								$totalActual += $actual = $db->getValue('budget_actual ba, budget_plan bp','sum(ba.act_amount)',array('bp.bp_year'=>$year,'bp.dep_id'=>$dep_id),'AND bp.bp_id=ba.bp_id');
								$totalDiff += $diff = $plan - $actual;
								$percentage = ($plan) ? number_format(($diff/$plan)*100,3) : 0;
							?>
							<tr>
								<td><div align="left"><strong><?php echo $dep_name?></strong></div></td>
								<td style="padding-right:50px;"><div align="right"><?php echo functions::formatMoney($plan); ?></div></td>
								<td style="padding-right:50px;"><div align="right"><?php echo functions::formatMoney($actual); ?></div></td>
								<td style="padding-right:50px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo functions::formatMoney($diff); ?></strong></div></td>
								<td style="padding-right:50px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo $percentage.'%'; ?></strong></div></td>
								<td>
									<div align="center">
										<a id="vw<?php echo $dep_id?>" class="btn btn-mini btn-info thickbox" onclick="showThis(this.id,'dash-budget-plan-report-detail.php?bpdepid=<?php echo functions::encode($dep_id);?>','Plan Detail')"><i class="halflings-icon white search"></i></a>
									</div>
								</td>
							</tr>
							<?php
							$qDHS = $db->select('department','*',array('dep_head'=>$r['dep_id']),'AND dep_id IN (SELECT DISTINCT dep_id FROM budget_plan WHERE bp_year="'.$db->clean($year).'") ORDER BY dep_name');
							while($rS = $db->fetch_array($qDHS)):
								$dep_id = $rS['dep_id'];
								$dep_name = $rS['dep_name'];
								$totalPlan += $plan = $db->getValue('budget_plan','bp_amount',array('bp_year'=>$year,'dep_id'=>$dep_id));
								$totalActual += $actual = $db->getValue('budget_actual ba, budget_plan bp','sum(ba.act_amount)',array('bp.bp_year'=>$year,'bp.dep_id'=>$dep_id),'AND bp.bp_id=ba.bp_id');
								$totalDiff += $diff = $plan - $actual;
								$percentage = ($actual && $plan) ? number_format( ($actual / $plan) * 100,3 ) : 0;
							?>
							<tr>
								<td><div align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $dep_name?></div></td>
								<td style="padding-right:50px;"><div align="right"><?php echo functions::formatMoney($plan); ?></div></td>
								<td style="padding-right:50px;"><div align="right"><?php echo functions::formatMoney($actual); ?></div></td>
								<td style="padding-right:50px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo functions::formatMoney($diff); ?></strong></div></td>
								<td style="padding-right:50px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo $percentage.'%' ?></strong></div></td>
								<td>
									<div align="center">
										<a id="vw<?php echo $dep_id?>" class="btn btn-mini btn-info thickbox" onclick="showThis(this.id,'dash-budget-plan-report-detail.php?bpdepid=<?php echo functions::encode($dep_id);?>','Plan Detail')"><i class="halflings-icon white search"></i></a>
									</div>
								</td>
							</tr>
							<?php endwhile;
							endwhile;?>
							<tr><td colspan="5"></td></tr>
							<tr>
								<td></td>
								<td style="padding-right:50px;"><div align="right"><strong><?php echo functions::formatMoney($totalPlan); ?></strong></div></td>
								<td style="padding-right:50px;"><div align="right"><strong><?php echo functions::formatMoney($totalActual); ?></strong></div></td>
								<td style="padding-right:50px;<?php echo ($totalDiff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo functions::formatMoney($totalDiff); ?></strong></div></td>
								<td style="padding-right:50px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo ($totalPlan) ? number_format(($totalDiff / $totalPlan)*100,3) : 0;?>%</strong></div></td>
								<td></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
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