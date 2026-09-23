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
$selDepHead='';
$dep = ( isset($_REQUEST['dep']) && !empty($_REQUEST['dep']) ) ? functions::decode($_REQUEST['dep']) : '';
$cat = ( isset($_REQUEST['cat']) && !empty($_REQUEST['cat']) ) ? functions::decode($_REQUEST['cat']) : '';
$year = ( isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : '';
$default_dep = ( isset($_REQUEST['dp']) && !empty($_REQUEST['dp']) ) ? functions::decode($_REQUEST['dp']) : '';
$ba_edt = ( isset($_REQUEST['idBAEdt']) && !empty($_REQUEST['idBAEdt']) ) ? functions::decode($_REQUEST['idBAEdt']) : '';
$bpAdd = ( isset($_REQUEST['bpnew']) && !empty($_REQUEST['bpnew']) ) ? $_REQUEST['bpnew'] : '';
$bp_view = ( isset($_REQUEST['bpid']) && !empty($_REQUEST['bpid']) ) ? functions::decode($_REQUEST['bpid']) : '';
$bpEdit = ( isset($_REQUEST['bpEdit']) && !empty($_REQUEST['bpEdit']) ) ? $_REQUEST['bpEdit'] : '';
$idEdt = ( isset($_REQUEST['idEdt']) && !empty($_REQUEST['idEdt']) ) ? functions::decode($_REQUEST['idEdt']) : '';
$idDel = ( isset($_REQUEST['idDel']) && !empty($_REQUEST['idDel']) ) ? functions::decode($_REQUEST['idDel']) : '';
$txRemarks='';
$editable = ( ($bpEdit==1) || ($bpAdd==1) ) ? '' :'readonly disabled';
$qEdt = $db->select('budget_plan','*',array('dep_id'=>$dep,'cat_id'=>$cat,'bp_year'=>$year));
$rEdt = $db->fetch_array($qEdt);
$bp_id = $rEdt['bp_id'];
#$txItem = $rEdt['item'];
$txItem = $db->getValue('item_deduction','concat(name," (",numbering,")")',array('item_id'=>$cat));
$txQtyBdt = $rEdt['bp_qty'];
$txUnitBdt = $rEdt['bp_unit'];
$txPriceBdt = $rEdt['bp_price'];
$dep_name = $db->getValue('department',"concat(dep_desc,' (',dep_name,')')",array('dep_id'=>$dep));
$selDep=($rEdt['dep_id']) ? $rEdt['dep_id'] : $default_dep;
$budget_actual_amount = $rEdt['bp_amount'];
$txAmountBdt = functions::formatMoney($rEdt['bp_amount']);
$planAmount = $rEdt['bp_amount'];

$qAEdt = $db->select('budget_actual','*',array('ba_id'=>$ba_edt));
$db->last_query;
$rAEdt = $db->fetch_array($qAEdt);
$txQtyAct = $rAEdt['act_qty'];
$txUnitAct = $rAEdt['act_unit'];
$txPriceAct = $rAEdt['act_price'];
$txActDate = ($rAEdt['act_date']) ? $rAEdt['act_date'] : date('Y-m-d');
$txRemarks = $rAEdt['act_remarks'];
if( !empty($idDel) ){
	$db->delete('budget_actual',array('ba_id'=>$idDel));
	$_SESSION['notif_warning']='Item removed!';
	functions::sendTo(functions::pageName().'?bpid='.functions::encode($bp_view));
	die();
}
$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');

if( isset($_POST['btnSearch']) ){
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
	$txSearchItem = ( isset($_POST['txSearchItem']) && !empty($_POST['txSearchItem']) ) ? $_POST['txSearchItem'] : '';
	$_SESSION['bp_searchItem']=$txSearchItem;
	$_SESSION['bp_selYr']=$year;
	functions::sendTo(functions::pageName().'?bpid='.functions::encode($bp_view));
	die();
}
$year = ( isset($_SESSION['bp_selYr']) && !empty($_SESSION['bp_selYr']) ) ? $_SESSION['bp_selYr'] : date('Y');
$txSearchItem = ( isset($_SESSION['bp_searchItem']) && !empty($_SESSION['bp_searchItem']) ) ? $_SESSION['bp_searchItem'] : '';
$namesItem='';$namesUnit='';


$qItem = $db->select('budget_plan','DISTINCT item',array());
while($rItem=$db->fetch_array($qItem)):
	$string = preg_replace("/'/",'"',$rItem['item']);
	$namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesItem .= '"--"';

$qUnit = $db->select('budget_plan','DISTINCT bp_unit',array());

while($rUnit=$db->fetch_array($qUnit)):
	$string = preg_replace("/'/",'"',$rUnit['bp_unit']);
	$namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesUnit .= '"--"';

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
<?php
if( isset($_POST['btnSave']) ){
	$txQtyBdt = (isset($_POST['txQtyBdt']) && !empty($_POST['txQtyBdt']) ) ? functions::moneyToDouble($_POST['txQtyBdt']) : '';
	$txUnitBdt = (isset($_POST['txUnitBdt']) && !empty($_POST['txUnitBdt']) ) ? $_POST['txUnitBdt'] : '';
	$txPriceBdt = (isset($_POST['txPriceBdt']) && !empty($_POST['txPriceBdt']) ) ? functions::moneyToDouble($_POST['txPriceBdt']) : '';

	if( !empty($dep) && !empty($txPriceBdt) && !empty($cat) && !empty($year) ){
		if( $db->getValue('budget_plan','count(*)',array('dep_id'=>$dep,'cat_id'=>$cat,'bp_year'=>$year)) ){
			$db->update('budget_plan',array('bp_amount'=>($txPriceBdt)),array('dep_id'=>$dep,'cat_id'=>$cat,'bp_year'=>$year));
			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo('?yr='.functions::encode($year).'&cat='.functions::encode($cat).'&dep='.functions::encode($dep));
			die();
		}
		else{
			$arr = array('dep_id'=>$dep,'cat_id'=>$cat,'bp_amount'=>($txPriceBdt),'bp_year'=>$year);
			$insertID = $db->insert('budget_plan',$arr);
			if($insertID){
				$_SESSION['notif_success']='New Item successfully Added!';
				functions::sendTo('?yr='.functions::encode($year).'&cat='.functions::encode($cat).'&dep='.functions::encode($dep));
				die();
			}
			else{
				functions::say('Fail to add. Please try again later!');
			}			
		}
	}
	else{
		$_SESSION['notif_warning']='Please fill up the form properly!';
	}
}
?>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Plan vs. Actual Budget</h2>
		</div>
		<div class="box-content" align="center">
			<div class="box-content">
				<form method="post">
					<div id="mngFrms">
						<?php $bgPlan='#F9E3CC';$bgActual='#96C3E9'; ?>
						<div align="center">
							<div style="width:100%">
								<table class="table table-bordered" border="0" style="font-size:12px;">
									<tr style="background-color:#CCC;">
										<th width="30%"><div align="center">Department</div></th>
										<th width="25%"><div align="center">Item</div></th>
										<th width="15%"><div align="center">Amount</div></th>
										<th width="5%"><div align="center">Year</div></th>
										<th width="10%"><div align="center">&nbsp;</div></th>
									</tr>
									<tr>
										
										<td><div align="center"><?php echo $dep_name ?></div></td>
										<td><div align="center"><?php echo $txItem ?></div></td>
										<?php if( empty($bpEdit) ){?>
										<td><div align="center"><?php echo functions::formatMoney($planAmount); ?></div></td>
										<td><div align="center"><?php echo $year; ?></div></td>
										<?php }else if( ($bpEdit==1) ){ ?>
										<td><div align="center"><input type="text" name="txPriceBdt" id="txPriceBdt" style="width:120px;text-align:center;" value="<?php echo ($planAmount) ? functions::formatMoney($planAmount) : ''; ?>" onkeyup="FormatCurrency(this);" <?php echo $editable ?>  required></div></td>
										<td><div align="center"><?php echo $year; ?></div></td>
										<?php } ?>
										<td>
											<div align="center">
												<?php if(empty($editable)){?>
												<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-mini">
												<?php if($bpEdit){?>
												<a href="?yr=<?php echo functions::encode($year);?>&cat=<?php echo functions::encode($cat)?>&dep=<?php echo functions::encode($dep)?>" class="btn btn-mini">Cancel</a>
												<?php } ?>
												<?php }else{?>
												<a href="?yr=<?php echo functions::encode($year);?>&cat=<?php echo functions::encode($cat)?>&dep=<?php echo functions::encode($dep)?>&bpEdit=1" class="btn btn-warning btn-mini">Edit</a>
												<?php } ?>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div align="center">
						<div style="width:65%">
							<div align="center" style="padding:20px;"><strong>-----Actual Record-----</strong></div>
							<table class="table table-bordered table-hover" style="font-size:12px;">
								<thead>
									<tr style="background-color:#CCC;">
										<th width="30%"><div align="center">Date</div></th>
										<th width="30%"><div align="center">Amount</div></th>
										<th width="30%"><div align="center">Running Total</div></th>
										<th width="10%"><div align="center">Details</div></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$countActual=0;$percentage=0;
									$runningTotal=0;$totalActual=0;
									$qa = $db->select('view_po_actual','*',array('category_id'=>$cat,'SUBSTRING(po_date,1,4)'=>$year,'dep_id'=>$dep),' AND paid>0 ORDER BY po_date');
									while($ra = $db->fetch_array($qa)):
										$runningTotal+=$ra['paid'];
										$bg='';
										if($budget_actual_amount<$runningTotal)
											$bg='style="background-color:orange;"';


										$totalActual+=$ra['paid'];
										$diff = $planAmount - $totalActual;
										$percentage = ($planAmount) ? number_format(($diff/$planAmount)*100,3) : 0;


									?>
									<tr <?php echo $bg; ?>>
										<td><div align="center"><?php echo functions::datearr($ra['po_date']); ?></div></td>
										<td><div align="center"><?php echo functions::formatMoney($ra['paid']) ?></div></td>
										<td><div align="center"><?php echo functions::formatMoney($runningTotal) ?></div></td>
										<td><div align="center"><a id="vw<?php echo $countActual++;?>" class="btn btn-mini btn-info thickbox" onclick="showThis(this.id,'dash-budget-plan-detail-po.php?po_id=<?php echo functions::encode($ra['po_id']);?>','Actual Detail','1')"><i class="halflings-icon white search"></i></a></div></td>
									</tr>
									<?php endwhile; ?>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td style="<?php echo ($totalActual) ? ((functions::moneyToDouble($planAmount)>$totalActual) ? 'background-color:green;color:white;':'background-color:red;color:white;') : ''; ?>"><div  align="center"><strong><?php echo functions::formatMoney($totalActual) ?></strong></div></td>
										<td><div align="right" style="padding-right:20px;"><strong><?php echo $percentage; ?> %</strong></div></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
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
<script src="../js/jquery.chosen.min.js"></script>
<script src="../js/jquery.uniform.min.js"></script>
<script src="../js/jquery.cleditor.min.js"></script>
<script src="../js/jquery.noty.js"></script>
<script src="../js/jquery.elfinder.min.js"></script>
<script src="../js/jquery.raty.min.js"></script>
<script src="../js/jquery.uploadify-3.1.min.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/jquery.masonry.min.js"></script>
<script src="../js/jquery.knob.modified.js"></script>
<script src="../js/jquery.sparkline.min.js"></script>
<script src="../js/counter.js"></script>
<script src="../js/custom.js"></script>
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
$(document).ready(function(){
	$('#btnSave').click(function(){
		if( confirm('Do you want to update this item?') )
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
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>