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

$arrBudgetPlan=array();
$qbp = $db->select('budget_plan','dep_id,sum(bp_amount) as amnt,bp_year',array('bp_year'=>$year),'GROUP BY dep_id,bp_year');
while($rbp = $db->fetch_array($qbp)):
	$arrBudgetPlan[$rbp['dep_id']]=$rbp['amnt'];
endwhile;


$arrBudgetActual=array();
$qba = $db->select('view_po_actual','dep_id,sum(paid) as amnt,substring(po_date,1,4) as yr',array('substring(po_date,1,4)'=>$year),'AND dep_id<>"" GROUP BY dep_id,yr HAVING sum(paid) > 0');
while($rba = $db->fetch_array($qba)):
	if($rba['dep_id'])
		$arrBudgetActual[$rba['dep_id']]=$rba['amnt'];
endwhile;
$totalPlan=0;$totalActual=0;$totalDiff=0;
function disp($parentItem,$level){
	global $db;
	global $arrBudgetPlan;
	global $arrBudgetActual;
	global $totalPlan;
	global $totalActual;
	global $totalDiff;
	global $year;
	$string='';
	$q = $db->select('department','*',array('dep_head'=>$parentItem),'ORDER BY dep_name');
	while($r = $db->fetch_array($q)):
		$budget_plan = isset($arrBudgetPlan[$r['dep_id']]) ? $arrBudgetPlan[$r['dep_id']] : 0;
		$budget_actual = isset($arrBudgetActual[$r['dep_id']]) ? $arrBudgetActual[$r['dep_id']] : 0;
		$budget_plan_disp = isset($arrBudgetPlan[$r['dep_id']]) ? functions::formatMoney($arrBudgetPlan[$r['dep_id']]) : '';
		$budget_actual_disp = isset($arrBudgetActual[$r['dep_id']]) ? functions::formatMoney($arrBudgetActual[$r['dep_id']]) : '';

		$totalPlan += $budget_plan;
		$totalActual += $budget_actual;
		$totalDiff += $diff = $budget_plan - $budget_actual;
		$percentage = ($budget_plan) ? number_format(($diff/$budget_plan)*100,3) : 0;

		$bgDiff = ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;';
		$bgPercentage = ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;';

		$dep_name = ($r['dep_name']) ? $r['dep_name'] : "";
		$dep_head = ($r['dep_head']) ? $r['dep_head'] : "";
		$dep_desc = ($r['dep_desc']) ? $r['dep_desc'] : "";
		$dep_id = $r['dep_id'];

		#$dep_type = ($r['dep_type']) ? $r['dep_type'] : "";
		$dep_type = "";
		$s = '';
		$pad=0;
		for($i=1; $i<=$level; $i++):
			$pad+=40;
		endfor;
		$dep_name = ($dep_name) ? ' ('.$dep_name.')' : '';
		$showThis = "showThis(this.id,'dash-budget-plan-report-detail.php?yr=".functions::encode($year)."&dep=".functions::encode($dep_id)."','Plan Detail','1')";
		$string ='
		<tr id="rw'.$dep_id.'">
			<td>
				<div style="padding-left:'.$pad.'px;">
					<div style="display:flex;">
						<div style="border:0px solid;"><img src="../img/arrow-down-right.png" height="16" width="12"></div>
						<div style="border:0px solid;padding-left:15px;">'.$dep_desc.' '.$dep_name.'</div>
					</div>
				</div>
			</td>
			<td style="padding-right:50px;"><div align="right">'.$budget_plan_disp.'</div></td>
			<td style="padding-right:50px;"><div align="right">'.$budget_actual_disp.'</div></td>
			<td style="padding-right:50px;'.$bgDiff.'"><div align="right">'.functions::formatMoney($diff).'</div></td>
			<td style="padding-right:50px;'.$bgPercentage.'"><div align="right">'.$percentage.' %</div></td>
			<td>
				<div align="center"><a id="vw'.$dep_id.'" class="btn btn-mini btn-info thickbox" onclick="'.$showThis.'"><i class="halflings-icon white search"></i></a></div>
			</td>
		</tr>
		';
		echo $string;
		disp($r['dep_id'],$level + 1);
    endwhile;
}

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
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Plan vs. Actual Budget By Department Report</h2>
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
				<table id="tblist" width="95%" border="1" align="center" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="35%">Department</th>
							<th width="10%"><div align="center">Plan</div></th>
							<th width="10%"><div align="center">Actual</div></th>
							<th width="10%"><div align="center">Difference</div></th>
							<th width="10%"><div align="center">%</div></th>
							<th width="7%"><div align="center">Details</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count=0;
						$qList = $db->select('department','*',array('dep_head'=>NULL),'ORDER BY dep_name');
						while($rList = $db->fetch_array($qList)):
							$dep_name = ($rList['dep_name']) ? ' ('.$rList['dep_name'].')' : '';
							$allowed_del = ($db->getValue('department','count(*)',array('dep_head'=>$rList['dep_id']))==0) ? 1 : 0;

							$budget_plan = isset($arrBudgetPlan[$rList['dep_id']]) ? $arrBudgetPlan[$rList['dep_id']] : 0;
							$budget_actual = isset($arrBudgetActual[$rList['dep_id']]) ? $arrBudgetActual[$rList['dep_id']] : 0;
							$budget_plan_disp = isset($arrBudgetPlan[$rList['dep_id']]) ? functions::formatMoney($arrBudgetPlan[$rList['dep_id']]) : '';
							$budget_actual_disp = isset($arrBudgetActual[$rList['dep_id']]) ? functions::formatMoney($arrBudgetActual[$rList['dep_id']]) : '';

							$totalPlan += $budget_plan;
							$totalActual += $budget_actual;
							$totalDiff += $diff = $budget_plan - $budget_actual;
							$percentage = ($budget_plan) ? number_format(($diff/$budget_plan)*100,3) : 0;

							$bgDiff = ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;';
							$bgPercentage = ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;';


						?>
						<tr id="rw<?php echo $rList['dep_id']?>">
							<td width="25%"><strong><?php echo $rList['dep_desc'].' '.$dep_name;?></strong></td>
							<td style="padding-right:50px;"><div align="right"><strong><?php echo $budget_plan_disp; ?></strong></div></td>
							<td style="padding-right:50px;"><div align="right"><strong><?php echo $budget_actual_disp; ?></strong></div></td>
							<td style="padding-right:50px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo functions::formatMoney($diff); ?></strong></div></td>
							<td style="padding-right:50px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><strong><?php echo ($budget_plan) ? number_format(($diff / $budget_plan)*100,3) : 0;?>%</strong></div></td>
							<td>
								<div align="center">
									<a id="vw<?php echo $rList['dep_id'].$count++;?>" class="btn btn-mini btn-info thickbox" onclick="showThis(this.id,'dash-budget-plan-report-detail.php?yr=<?php echo functions::encode($year);?>&dep=<?php echo functions::encode($rList['dep_id'])?>','Plan Detail')"><i class="halflings-icon white search"></i></a>
								</div>
							</td>
						</tr>
						<?php
						disp($rList['dep_id'],1);
						endwhile;
						?>
						<tr><td colspan="6"></td></tr>
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
					<div style="width:80%">
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