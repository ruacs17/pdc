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
$idEdt = ( isset($_REQUEST['idEdt']) && !empty($_REQUEST['idEdt']) ) ? functions::decode($_REQUEST['idEdt']) : '';
$idDel = ( isset($_REQUEST['idDel']) && !empty($_REQUEST['idDel']) ) ? functions::decode($_REQUEST['idDel']) : '';
$qEdt = $db->select('budget_plan','*',array('bp_id'=>$idEdt));
$rEdt = $db->fetch_array($qEdt);
$bp_id = $rEdt['bp_id'];
$txItem = $rEdt['item'];
$txQtyBdt = $rEdt['bp_qty'];
$txUnitBdt = $rEdt['bp_unit'];
$txPriceBdt = $rEdt['bp_price'];
$txQtyAct = $rEdt['act_qty'];
$txUnitAct = $rEdt['act_unit'];
$txPriceAct = $rEdt['act_price'];

if( !empty($idDel) ){
	if( $db->getValue('budget_actual','count(*)',array('bp_id'=>$idDel))==0 ){
		$db->delete('budget_plan',array('bp_id'=>$idDel));
		$_SESSION['notif_success']='Item successfully removed!';
	}
	else{
		$_SESSION['notif_warning']='Warning: Item cannot be removed for it has Actual Budget Details!';
	}
	functions::sendTo(functions::pageName());
	die();
}
$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');

if( isset($_POST['btnSearch']) ){
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
	$txSearchItem = ( isset($_POST['txSearchItem']) && !empty($_POST['txSearchItem']) ) ? $_POST['txSearchItem'] : '';
	$_SESSION['bp_searchItem']=$txSearchItem;
	$_SESSION['bp_selYr']=$year;
	$_SESSION['bp_dep'] = (isset($_POST['selDepartment']) && !empty($_POST['selDepartment']) ) ? $_POST['selDepartment'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$year = ( isset($_SESSION['bp_selYr']) && !empty($_SESSION['bp_selYr']) ) ? $_SESSION['bp_selYr'] : date('Y');
$txSearchItem = ( isset($_SESSION['bp_searchItem']) && !empty($_SESSION['bp_searchItem']) ) ? $_SESSION['bp_searchItem'] : '';
$selDep = ( isset($_SESSION['bp_dep']) && !empty($_SESSION['bp_dep']) ) ? $_SESSION['bp_dep'] : '';

$qItem = $db->select('budget_plan','DISTINCT item',array());
$namesItem='';
while($rItem=$db->fetch_array($qItem)):
	$string = preg_replace("/'/",'"',$rItem['item']);
	$namesItem .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesItem .= '"--"';

$qUnit = $db->select('budget_plan','DISTINCT bp_unit',array());
$namesUnit='';
while($rUnit=$db->fetch_array($qUnit)):
	$string = preg_replace("/'/",'"',$rUnit['bp_unit']);
	$namesUnit .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesUnit .= '"--"';
$bgPlan='#F9E3CC';$bgActual='#96C3E9';




function disp($dep_head,$level,$currentItemID){
	global $db;
	global $itemID;
	$string='';

	$q = $db->select('department','*',array('dep_head'=>$dep_head),'ORDER BY dep_name');
	while($r = $db->fetch_array($q)):
		$s = '';
		for($i=1; $i<=$level; $i++):
			$s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
		endfor;
		$dep_desc = ($r['dep_desc']) ? '&nbsp;&nbsp;('.$r['dep_desc'].')': '';
		$s .= $r['dep_name'].$dep_desc;
		$selected = ($currentItemID==$r['dep_id']) ? 'selected="selected"' : "";
		$string ='
		<option value="'.$r['dep_id'].'" '.$selected.'>'.$s.'</option>
		';
		echo $string;
		disp($r['dep_id'],$level + 1,$currentItemID);
	endwhile;
}


$arr_current_assetsSel = array();$arr_noncurrent_assetsSel = array();
$arr_current_liabilitiesSel=array();$arr_noncurrent_liabilitiesSel=array();
$arr_equitySel=array(); $arr_incomeSel=array();
$arr_exp_laborSel=array();$arr_exp_materialSel=array();$arr_exp_subconSel=array();$arr_exp_incoSel=array();$arr_exp_gaseSel=array();
$arr_othersSel=array();
$countChargeSel=0;
$qC = $db->select('item_deduction','*',array(),'ORDER BY account_type,account_type_group,numbering is null, numbering,name');
while($rC = $db->fetch_array($qC)):
	$arr = array('numbering'=>$rC['numbering'],'item_id'=>$rC['item_id'],'name'=>$rC['name'],'description'=>$rC['description'],'cost_type'=>$rC['cost_type'],'expense_type'=>$rC['expense_type'],'account_type'=>$rC['account_type'],'account_type_group'=>$rC['account_type_group']);
	if($rC['account_type']=='assets'){
		if($rC['account_type_group']=='current asset')
			$arr_current_assetsSel[]=$arr;
		else if($rC['account_type_group']=='non-current asset')
			$arr_noncurrent_assetsSel[]=$arr;
	}
	elseif($rC['account_type']=='liabilities'){
		if($rC['account_type_group']=='current liabilities')
			$arr_current_liabilitiesSel[]=$arr;
		else if($rC['account_type_group']=='non-current liabilities')
			$arr_noncurrent_liabilitiesSel[]=$arr;
	}
	elseif($rC['account_type']=='equity'){
		if($rC['account_type_group']=='equity')
			$arr_equitySel[]=$arr;
	}
	elseif($rC['account_type']=='income'){
		if($rC['account_type_group']=='income')
			$arr_incomeSel[]=$arr;
	}
	elseif($rC['account_type']=='expense'){
		if($rC['account_type_group']=='direct labor')
			$arr_exp_laborSel[]=$arr;
		else if($rC['account_type_group']=='direct material')
			$arr_exp_materialSel[]=$arr;
		else if($rC['account_type_group']=='sub contractor')
			$arr_exp_subconSel[]=$arr;
		else if($rC['account_type_group']=='indirect cost')
			$arr_exp_incoSel[]=$arr;
		else if($rC['account_type_group']=='general, administrative and selling expense')
			$arr_exp_gaseSel[]=$arr;
	}
	else{
		$arr_othersSel[]=$arr;
	}
$countChargeSel++;
endwhile;
$arrChargeSel=array();
$arrChargeSel = array_merge($arr_current_assetsSel,$arr_noncurrent_assetsSel,$arr_current_liabilitiesSel,$arr_noncurrent_liabilitiesSel,$arr_equitySel,$arr_incomeSel,$arr_exp_laborSel,$arr_exp_materialSel,$arr_exp_subconSel,$arr_exp_incoSel,$arr_exp_gaseSel,$arr_othersSel);







$arr_current_assets = array();$arr_noncurrent_assets = array();
$arr_current_liabilities=array();$arr_noncurrent_liabilities=array();
$arr_equity=array(); $arr_income=array();
$arr_exp_labor=array();$arr_exp_material=array();$arr_exp_subcon=array();$arr_exp_inco=array();$arr_exp_gase=array();
$arr_others=array();
$countCharge=0;
if($txSearchItem)
	$qC = $db->select('item_deduction','*',array('item_id'=>$txSearchItem),'ORDER BY account_type,account_type_group,numbering is null, numbering,name');
else
	$qC = $db->select('item_deduction','*',array(),'ORDER BY account_type,account_type_group,numbering is null, numbering,name');
while($rC = $db->fetch_array($qC)):
	$arr = array('numbering'=>$rC['numbering'],'item_id'=>$rC['item_id'],'name'=>$rC['name'],'description'=>$rC['description'],'cost_type'=>$rC['cost_type'],'expense_type'=>$rC['expense_type'],'account_type'=>$rC['account_type'],'account_type_group'=>$rC['account_type_group']);
	if($rC['account_type']=='assets'){
		if($rC['account_type_group']=='current asset')
			$arr_current_assets[]=$arr;
		else if($rC['account_type_group']=='non-current asset')
			$arr_noncurrent_assets[]=$arr;
	}
	elseif($rC['account_type']=='liabilities'){
		if($rC['account_type_group']=='current liabilities')
			$arr_current_liabilities[]=$arr;
		else if($rC['account_type_group']=='non-current liabilities')
			$arr_noncurrent_liabilities[]=$arr;
	}
	elseif($rC['account_type']=='equity'){
		if($rC['account_type_group']=='equity')
			$arr_equity[]=$arr;
	}
	elseif($rC['account_type']=='income'){
		if($rC['account_type_group']=='income')
			$arr_income[]=$arr;
	}
	elseif($rC['account_type']=='expense'){
		if($rC['account_type_group']=='direct labor')
			$arr_exp_labor[]=$arr;
		else if($rC['account_type_group']=='direct material')
			$arr_exp_material[]=$arr;
		else if($rC['account_type_group']=='sub contractor')
			$arr_exp_subcon[]=$arr;
		else if($rC['account_type_group']=='indirect cost')
			$arr_exp_inco[]=$arr;
		else if($rC['account_type_group']=='general, administrative and selling expense')
			$arr_exp_gase[]=$arr;
	}
	else{
		$arr_others[]=$arr;
	}
$countCharge++;
endwhile;
$arrCharge=array();
$arrCharge = array_merge($arr_current_assets,$arr_noncurrent_assets,$arr_current_liabilities,$arr_noncurrent_liabilities,$arr_equity,$arr_income,$arr_exp_labor,$arr_exp_material,$arr_exp_subcon,$arr_exp_inco,$arr_exp_gase,$arr_others);

$arrActualCost=array();
$actualCostQ = $db->select('view_po_actual','category_id,sum(paid) as sumpaid',array('substring(po_date,1,4)'=>$year,'dep_id'=>$selDep),'GROUP BY category_id');
while($rac = $db->fetch_array($actualCostQ)):
	$arrActualCost[$rac['category_id']]=$rac['sumpaid'];
endwhile;


$arrPlanBudget=array();
$planQ = $db->select('budget_plan','*',array('bp_year'=>$year,'dep_id'=>$selDep));
while($planR = $db->fetch_array($planQ)):
	if($planR['cat_id'])
		$arrPlanBudget[$planR['cat_id']]=array('dep_id'=>$planR['dep_id'],'amount'=>$planR['bp_amount']);
endwhile;
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
	$txItem = (isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? $_POST['txItem'] : '';
	$txQtyBdt = (isset($_POST['txQtyBdt']) && !empty($_POST['txQtyBdt']) ) ? functions::moneyToDouble($_POST['txQtyBdt']) : '';
	$txUnitBdt = (isset($_POST['txUnitBdt']) && !empty($_POST['txUnitBdt']) ) ? $_POST['txUnitBdt'] : '';
	$txPriceBdt = (isset($_POST['txPriceBdt']) && !empty($_POST['txPriceBdt']) ) ? functions::moneyToDouble($_POST['txPriceBdt']) : '';
	$txQtyAct = (isset($_POST['txQtyAct']) && !empty($_POST['txQtyAct']) ) ? functions::moneyToDouble($_POST['txQtyAct']) : '';
	$txUnitAct = (isset($_POST['txUnitAct']) && !empty($_POST['txUnitAct']) ) ? $_POST['txUnitAct'] : '';
	$txPriceAct = (isset($_POST['txPriceAct']) && !empty($_POST['txPriceAct']) ) ? functions::moneyToDouble($_POST['txPriceAct']) : '';
	if( !empty($txPriceBdt) && !empty($txUnitBdt) && !empty($txQtyBdt) && !empty($txItem) && !empty($year)){
		if( !empty($bp_id) ){
			$arr = array('item'=>$txItem,'bp_qty'=>$txQtyBdt,'bp_unit'=>$txUnitBdt,'bp_price'=>$txPriceBdt,'bp_amount'=>($txPriceBdt*$txQtyBdt),
										'act_qty'=>$txQtyAct,'act_unit'=>$txUnitAct,'act_price'=>$txPriceAct,'act_amount'=>($txPriceAct*$txQtyAct),'bp_year'=>$year);
			$db->update('budget_plan',$arr,array('bp_id'=>$bp_id));
			$_SESSION['notif_success']='Changes successfully saved!';
			functions::sendTo(functions::pageName());
			die();
		}
		else if( $db->getValue('budget_plan','count(*)',array('item'=>$txItem,'bp_year'=>$year))==0 ){
			$arr = array('item'=>$txItem,'bp_qty'=>$txQtyBdt,'bp_unit'=>$txUnitBdt,'bp_price'=>$txPriceBdt,'bp_amount'=>($txPriceBdt*$txQtyBdt),
										'act_qty'=>$txQtyAct,'act_unit'=>$txUnitAct,'act_price'=>$txPriceAct,'act_amount'=>($txPriceAct*$txQtyAct),'bp_year'=>$year);
			$insertID = $db->insert('budget_plan',$arr);
			if($insertID){
				$_SESSION['notif_success']='New Item successfully Added!';
				functions::sendTo(functions::pageName());
				die();
			}
			else{
				functions::say('Fail to add. Please try again later!');
			}
		}
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
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="dash-budget-plan.php" style="opacity:.9">Plan</a></li>
				<li><a href="dash-budget-plan-report.php">Report</a></li>
			</ul>
			<div class="box-content">
				<form method="post">
					<div align="left">
						<select name="txSearchItem" id="txSearchItem" style="width:500px;" data-rel="chosen">
							<option value="">--All--</option>
							<?php
							$accountType='';
							foreach($arrChargeSel as $rDisp):
							$accnt=($rDisp['account_type']) ? $rDisp['account_type'] : 'unknown account type';
							if($accountType!=$accnt){
								if($accountType)
									echo '</optgroup>';
								$accountType=$accnt;
								echo '<optgroup label="'.strtoupper($accountType).'">';
								
							}
							?>
							<option value="<?php echo $rDisp['item_id']?>" <?php if($txSearchItem==$rDisp['item_id'])echo 'selected="selected"';?>><?php echo $rDisp['name']; echo ($rDisp['numbering']) ? ' ( '.$rDisp['numbering'].' )' : '';?></option>
							<?php
							endforeach;
							?>
						</select>&nbsp;&nbsp;&nbsp;
						<select name="selDepartment" id="selDepartment" style="width:500px;" data-rel="chosen">
							<?php
							$q = $db->select('department','*',array('dep_head'=>NULL),'ORDER BY dep_name');
							while($r = $db->fetch_array($q)):
								$dep_desc = ($r['dep_desc']) ? '&nbsp;&nbsp;('.$r['dep_desc'].')': '';
							?>
							<option value="<?php echo $r['dep_id']?>" <?php if($selDep==$r['dep_id'])echo 'selected="selected"';?>><?php echo $r['dep_name'].$dep_desc;?></option>
							<?php
							disp($r['dep_id'],1,$selDep);
							endwhile;
							?>
						</select>
						<select name="bdYear" id="bdYear" style="width:140px;">
							<option value="">--Select Year--</option>
							<?php for($yr=(date('Y')+3); $yr>=2012; $yr--): ?>
							<option value="<?php echo functions::encode($yr)?>" <?php if($year==$yr)echo 'selected="selected"';?>><?php echo $yr?></option>
							<?php endfor; ?>
						</select>
						<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary btn-small">
					</div>
				</form>
				<table class="table table-bordered table-hover" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="40%"><div align="left">Item</div></th>
							<th width="15%"><div align="center">Plan</div></th>
							<th width="15%"><div align="center">Actual</div></th>
							<th width="15%"><div align="center">Difference</div></th>
							<th width="5%"><div align="center">%</div></th>
							<th width="5%">&nbsp;</th>
						</tr>
					</thead>
					<body>
						<?php
						$accountType='';
						$count=0;$totalPlanAmount=0;$totalActualAmount=0;$totalDiff=0;
						foreach($arrCharge as $rDisp):
							$accnt=($rDisp['account_type']) ? $rDisp['account_type'] : 'unknown account type';
							if($accountType!=$accnt){
								$accountType=$accnt;
								echo '<tr>
								<td height="50px"><div align="center" style="padding-top:20px;font-size:16px;"><strong>'.strtoupper($accountType).'</strong></div></td>
								<td colspan="5"></td>
								</tr>';
							}
							$totalPlanAmount += $amnt_plan= isset($arrPlanBudget[$rDisp['item_id']]['amount']) ? $arrPlanBudget[$rDisp['item_id']]['amount'] : 0;
							#$amnt_plan = ($r['bp_amount']) ? $r['bp_amount'] : 0;
							$totalActualAmount += $actual_amount = isset($arrActualCost[$rDisp['item_id']]) ? $arrActualCost[$rDisp['item_id']] : 0;
							$diff = $amnt_plan - $actual_amount;
							#$totalPlanAmount += $r['bp_amount'];
							#$totalActualAmount += $r['actualamount'];
							$totalDiff += $diff;
							$percentage = ($amnt_plan) ? number_format(($diff/$amnt_plan)*100,3) : 0;
						?>
						<tr>
							<td><div align="left"><?php echo $rDisp['name']; echo ($rDisp['numbering']) ? ' <i>('.$rDisp['numbering'].')</i>' : '';?></div></td>
							<td style="padding-right:30px;background-color:<?php echo $bgPlan?>;"><div align="right"><?php echo functions::formatMoney($amnt_plan); ?></div></td>
							<td style="padding-right:30px;background-color:<?php echo $bgActual?>;"><div align="right"><?php echo functions::formatMoney($actual_amount) ?></div></td>
							<td style="padding-right:30px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="right"><?php echo functions::formatMoney($diff) ?></div></td>
							<td style="padding-right:0px;<?php echo ($diff>=1) ? 'background-color:green;color:white;':'background-color:red;color:white;'; ?>"><div align="center"><?php echo $percentage ?>%</div></td>
							<td>
								<div align="center">
									<a id="vw<?php echo $count++;?>" class="btn btn-mini btn-info thickbox" onclick="showThis(this.id,'dash-budget-plan-detail.php?yr=<?php echo functions::encode($year);?>&cat=<?php echo functions::encode($rDisp['item_id'])?>&dep=<?php echo functions::encode($selDep)?>','Plan Detail')"><i class="halflings-icon white search"></i></a>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</body>
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