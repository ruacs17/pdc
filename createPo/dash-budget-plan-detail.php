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

$default_dep = ( isset($_REQUEST['dp']) && !empty($_REQUEST['dp']) ) ? functions::decode($_REQUEST['dp']) : '';
$ba_edt = ( isset($_REQUEST['idBAEdt']) && !empty($_REQUEST['idBAEdt']) ) ? functions::decode($_REQUEST['idBAEdt']) : '';
$bpAdd = ( isset($_REQUEST['bpnew']) && !empty($_REQUEST['bpnew']) ) ? $_REQUEST['bpnew'] : '';
$bp_view = ( isset($_REQUEST['bpid']) && !empty($_REQUEST['bpid']) ) ? functions::decode($_REQUEST['bpid']) : '';
$bpEdit = ( isset($_REQUEST['bpEdit']) && !empty($_REQUEST['bpEdit']) ) ? $_REQUEST['bpEdit'] : '';
$idEdt = ( isset($_REQUEST['idEdt']) && !empty($_REQUEST['idEdt']) ) ? functions::decode($_REQUEST['idEdt']) : '';
$idDel = ( isset($_REQUEST['idDel']) && !empty($_REQUEST['idDel']) ) ? functions::decode($_REQUEST['idDel']) : '';
$txRemarks='';
$editable = ( ($bpEdit==1) || ($bpAdd==1) ) ? '' :'readonly disabled';
$qEdt = $db->select('budget_plan','*',array('bp_id'=>$bp_view));
$rEdt = $db->fetch_array($qEdt);
$bp_id = $rEdt['bp_id'];
$txItem = $rEdt['item'];
$txQtyBdt = $rEdt['bp_qty'];
$txUnitBdt = $rEdt['bp_unit'];
$txPriceBdt = $rEdt['bp_price'];
$dep_name = $db->getValue('department','dep_name',array('dep_id'=>$rEdt['dep_id']));
$selDep=($rEdt['dep_id']) ? $rEdt['dep_id'] : $default_dep;
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

if( isset($_POST['btnManage']) ){
	$act_date=date('Y-m-d');$act_time = date('H:i:s');
	$act_date = ( isset($_POST['txActDate']) && functions::valid_date(trim($_POST['txActDate'])) ) ? trim($_POST['txActDate']) : date('Y-m-d');
	$txQtyAct = (isset($_POST['txQtyAct']) && !empty($_POST['txQtyAct']) ) ? functions::moneyToDouble($_POST['txQtyAct']) : '';
	$txUnitAct = (isset($_POST['txUnitAct']) && !empty($_POST['txUnitAct']) ) ? $_POST['txUnitAct'] : '';
	$txPriceAct = (isset($_POST['txPriceAct']) && !empty($_POST['txPriceAct']) ) ? functions::moneyToDouble($_POST['txPriceAct']) : '';
	$txRemarks = (isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';

	if( !empty($txQtyAct) && !empty($txUnitAct) && !empty($txPriceAct) && !empty($year)){
		if( !empty($bp_view) && !empty($ba_edt) ){
			$arr = array('act_date'=>$act_date,'bp_id'=>$bp_view,'act_qty'=>$txQtyAct,'act_unit'=>$txUnitAct,'act_price'=>$txPriceAct,'act_amount'=>($txPriceAct*$txQtyAct),'act_remarks'=>$txRemarks,'bp_year'=>$year);
			$db->update('budget_actual',$arr,array('ba_id'=>$ba_edt));
			$_SESSION['notif_success']='Changes successfully saved!';
			functions::sendTo(functions::pageName().'?bpid='.functions::encode($bp_view));
			die();
		}
		else{
			$arr = array('bp_id'=>$bp_view,'act_date'=>$act_date,'act_time'=>$act_time,'act_qty'=>$txQtyAct,'act_unit'=>$txUnitAct,'act_price'=>$txPriceAct,'act_amount'=>($txPriceAct*$txQtyAct),'act_remarks'=>$txRemarks,'bp_year'=>$year);
			$insertID = $db->insert('budget_actual',$arr);
			if($insertID){
				$_SESSION['notif_success']='New Actual Budget Record Added!';
				functions::sendTo(functions::pageName().'?bpid='.functions::encode($bp_view));
				die();
			}
			else{
				functions::say('Fail to add. Please try again later!');
			}
		}
	}
}
if( isset($_POST['btnSave']) ){
	$selDepartment = (isset($_POST['selDepartment']) && !empty($_POST['selDepartment']) ) ? $_POST['selDepartment'] : '';
	$txItem = (isset($_POST['txItem']) && !empty($_POST['txItem']) ) ? $_POST['txItem'] : '';
	$txQtyBdt = (isset($_POST['txQtyBdt']) && !empty($_POST['txQtyBdt']) ) ? functions::moneyToDouble($_POST['txQtyBdt']) : '';
	$txUnitBdt = (isset($_POST['txUnitBdt']) && !empty($_POST['txUnitBdt']) ) ? $_POST['txUnitBdt'] : '';
	$txPriceBdt = (isset($_POST['txPriceBdt']) && !empty($_POST['txPriceBdt']) ) ? functions::moneyToDouble($_POST['txPriceBdt']) : '';

	if( !empty($selDepartment) && !empty($txPriceBdt) && !empty($txUnitBdt) && !empty($txQtyBdt) && !empty($txItem) && !empty($year)){
		if( !empty($bp_view) ){
			$arr = array('dep_id'=>$selDepartment,'item'=>$txItem,'bp_qty'=>$txQtyBdt,'bp_unit'=>$txUnitBdt,'bp_price'=>$txPriceBdt,'bp_amount'=>($txPriceBdt*$txQtyBdt),'bp_year'=>$year);
			$db->update('budget_plan',$arr,array('bp_id'=>$bp_view));
			$_SESSION['notif_success']='Changes successfully saved!';
			functions::sendTo(functions::pageName().'?bpid='.functions::encode($bp_view));
			die();
		}
		else if( $db->getValue('budget_plan','count(*)',array('dep_id'=>$selDepartment,'item'=>$txItem,'bp_year'=>$year))==0 ){
			$arr = array('dep_id'=>$selDepartment,'item'=>$txItem,'bp_qty'=>$txQtyBdt,'bp_unit'=>$txUnitBdt,'bp_price'=>$txPriceBdt,'bp_amount'=>($txPriceBdt*$txQtyBdt),'bp_year'=>$year);
			$insertID = $db->insert('budget_plan',$arr);
			if($insertID){
				$_SESSION['notif_success']='New Item successfully Added!';
				functions::sendTo(functions::pageName().'?bpid='.functions::encode($insertID));
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
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Plan vs. Actual Budget</h2>
		</div>
		<div class="box-content" align="center">
			<div class="box-content">
				<form method="post">
					<div align="right"><a id="vwAdd" class="btn btn-small btn-info" href="?bpnew=1&dp=<?php echo functions::encode($selDep)?>">Add new Item</a></div>
					<div>&nbsp;</div>
					<div id="mngFrms">
						<?php $bgPlan='#F9E3CC';$bgActual='#96C3E9'; ?>
						<div align="center">
							<div style="width:100%">
								<table class="table table-bordered" border="0" style="font-size:12px;">
									<tr style="background-color:#CCC;">
										<th width="30%"><div align="center">Department</div></th>
										<th width="25%"><div align="center">Item</div></th>
										<th width="5%"><div align="center">Quantity</div></th>
										<th width="5%"><div align="center">UOM</div></th>
										<th width="10%"><div align="center">Price</div></th>
										<th width="10%"><div align="center">Amount</div></th>
										<th width="5%"><div align="center">Year</div></th>
										<th width="15%"><div align="center">&nbsp;</div></th>
									</tr>
									<tr>
										<?php if( empty($bpEdit) && empty($bpAdd) ){?>
										<td><div align="center"><?php echo $dep_name ?></div></td>
										<td><div align="center"><?php echo $txItem ?></div></td>
										<td><div align="center"><?php echo ($txQtyBdt) ? number_format($txQtyBdt) : ''; ?></div></td>
										<td><div align="center"><?php echo $txUnitBdt?></div></td>
										<td><div align="center"><?php echo ($txPriceBdt) ? functions::formatMoney($txPriceBdt) : ''; ?></div></td>
										<td><div align="center"><?php echo $txAmountBdt; ?></div></td>
										<td><div align="center"><?php echo $year; ?></div></td>
										<?php }else if( ($bpEdit==1) || ($bpAdd==1) ){ ?>
										<td>
											<div align="left">
												<select name="selDepartment" id="selDepartment" style="width:500px;" data-rel="chosen" required>
													<option value="">--Select--</option>
													<?php
													$qDH = $db->select('department','*',array(),'WHERE dep_head is NULL ORDER BY dep_name');
													while($r = $db->fetch_array($qDH)):
													?>
													<option value="<?php echo $r['dep_id']?>" <?php if($selDep==$r['dep_id'])echo 'selected="selected"';?> style="font-weight:bold;"><?php echo $r['dep_name']?></option>
													<?php
													$qDHS = $db->select('department','*',array('dep_head'=>$r['dep_id']),'ORDER BY dep_name');
													while($rS = $db->fetch_array($qDHS)):
													?>
													<option value="<?php echo $rS['dep_id']?>" <?php if($selDep==$rS['dep_id'])echo 'selected="selected"';?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <?php echo $rS['dep_name']?></option>
													<?php endwhile;?>
													<?php endwhile;?>
												</select>
											</div>
										</td>
										<td><textarea name="txItem" id="txItem" style="width:90%;" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesItem;?>]' <?php echo $editable; ?> required><?php echo $txItem ?></textarea></td>
										<td><div align="center"><input type="text" name="txQtyBdt" id="txQtyBdt" onkeyup="FormatCurrency(this);" style="width:70px;text-align:center;" value="<?php echo ($txQtyBdt) ? number_format($txQtyBdt) : ''; ?>"<?php echo $editable ?>  required></div></td>
										<td><div align="center"><input type="text" name="txUnitBdt" id="txUnitBdt" style="width:70px;text-align:center;" value="<?php echo $txUnitBdt?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesUnit;?>]' <?php echo $editable ?>  required></div></td>
										<td><div align="center"><input type="text" name="txPriceBdt" id="txPriceBdt" style="width:120px;text-align:center;" value="<?php echo ($txPriceBdt) ? functions::formatMoney($txPriceBdt) : ''; ?>" onkeyup="FormatCurrency(this);" <?php echo $editable ?>  required></div></td>
										<td>&nbsp;</td>
										<td>
											<div align="center">
												<select name="bdYear" id="bdYear" style="width:90px;text-align:center;" <?php echo $editable ?>  required>
													<option value="">--Select--</option>
													<?php for($yr=(date('Y')+3); $yr>=2012; $yr--): ?>
													<option value="<?php echo functions::encode($yr)?>" <?php if($year==$yr)echo 'selected="selected"';?>><?php echo $yr?></option>
													<?php endfor; ?>
												</select>
											</div>
										</td>
										<?php } ?>
										<td>
											<div align="center">
												<?php if(empty($editable)){?>
												<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary btn-mini">
												<?php if($bp_view){?>
												<a href="?bpid=<?php echo functions::encode($bp_view)?>" class="btn btn-mini">Cancel</a>
												<?php } ?>
												<?php }else{?>
												<a href="?bpid=<?php echo functions::encode($bp_view)?>&bpEdit=1" class="btn btn-warning btn-mini">Edit</a>
												<?php } ?>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div align="center">
						<div style="width:95%">
							<?php if($bp_view){ ?>
							<div style="padding:20px;"><strong>-----Actual Record-----</strong></div>
							<?php if(empty($ba_edt)){ ?>
							<table style="font-size:12px;" border="0" style="background-color:#CCC;">
								<tr style="background-color:#CCC;">
									<th><div align="center">Date</div></th>
									<th><div align="center">Quantity</div></th>
									<th><div align="center">UOM</div></th>
									<th><div align="center">Price</div></th>
									<th><div align="center">Remarks</div></th>
									<th><div align="center"></div></th>
								</tr>
								<tr style="background-color:#CCC;">
									<td><div align="center" style="padding:7px;"><input type="text" style="width: 80px;" name="txActDate" id="txActDate" value="<?php echo $txActDate ?>" required></div></td>
									<td><div align="center" style="padding:7px;"><input type="text" name="txQtyAct" id="txQtyAct" onkeyup="FormatCurrency(this);" style="width:70px;text-align:center;" value="<?php echo ($txQtyAct) ? number_format($txQtyAct) : ''; ?>"></div></td>
									<td><div align="center" style="padding:7px;"><input type="text" name="txUnitAct" id="txUnitAct" style="width:70px;text-align:center;" value="<?php echo $txUnitBdt?>" readonly></div></td>
									<td><div align="center" style="padding:7px;"><input type="text" name="txPriceAct" id="txPriceAct" style="width:120px;text-align:center;"  value="<?php echo ($txPriceAct) ? functions::formatMoney($txPriceAct) : ''; ?>" onkeyup="FormatCurrency(this);"></div></td>
									<td><div align="center" style="padding-top:17px;"><textarea name="txRemarks" id="txRemarks" rows="2" cols="15"><?php echo $txRemarks;?></textarea></div></td>
									<td style="padding:7px;">
										<div align="center">
											<input type="submit" name="btnManage" id="btnManage" value="Add" class="btn btn-primary btn-mini">
										</div>
									</td>
								</tr>
							</table>
							<?php }else{echo '<div style="padding-top:50px;">&nbsp;</div>';} ?>
							<div style="padding-top:10px;">&nbsp;</div>
							<table class="table table-bordered <?php echo empty($ba_edt) ? 'table-hover' : ''?>" style="font-size:12px;">
								<thead>
									<tr style="background-color:#CCC;">
										<th width="10%"><div align="center">Date</div></th>
										<th width="5%"><div align="center">Qty</div></th>
										<th width="5%"><div align="center">UOM</div></th>
										<th width="10%"><div align="center">Price</div></th>
										<th width="10%"><div align="center">Amount</div></th>
										<th width="7%"><div align="center">%</div></th>
										<th width="20%"><div align="center">Remarks</div></th>
										<th width="7%"><div align="center"></div></th>
									</tr>
								</thead>
								<tbody>
								<?php
								$totalActual=0;$percentage=0;
								$q = $db->select('budget_actual','*',array('bp_id'=>$bp_view),'ORDER BY act_date');
								while($r = $db->fetch_array($q)):
									$id = $r['ba_id'];
									$totalActual+=$r['act_amount'];
									$diff = $planAmount - $totalActual;
									$percentage = ($planAmount) ? number_format(($diff/$planAmount)*100,3) : 0;
									if($ba_edt==$id){
								?>
									<tr style="background-color:#33EBFF;">
										<td><div align="center"><input type="text" style="width: 80px;" name="txActDate" id="txActDate" value="<?php echo $txActDate ?>" required></div></td>
										<td><div align="center"><input type="text" name="txQtyAct" id="txQtyAct" onkeyup="FormatCurrency(this);" style="width:70px;text-align:center;" value="<?php echo ($txQtyAct) ? number_format($txQtyAct) : ''; ?>"></div></td>
										<td><div align="center"><input type="text" name="txUnitAct" id="txUnitAct" style="width:70px;text-align:center;" value="<?php echo $txUnitAct?>" readonly></div></td>
										<td><div align="center"><input type="text" name="txPriceAct" id="txPriceAct" style="width:120px;text-align:center;"  value="<?php echo ($txPriceAct) ? functions::formatMoney($txPriceAct) : ''; ?>" onkeyup="FormatCurrency(this);"></div></td>
										<td><div align="center"></div></td>
										<td><div align="center"></div></td>
										<td><div align="center"><textarea name="txRemarks" id="txRemarks" rows="3" style="width:90%;"><?php echo $txRemarks;?></textarea></div></td>
										<td>
											<div align="center">
												<input type="submit" name="btnManage" id="btnManage" value="Save" class="btn btn-primary btn-mini">
												<a href="?bpid=<?php echo functions::encode($bp_view)?>" class="btn btn-mini">Cancel</a>
											</div>
										</td>
									</tr>
								<?php }else{?>
									<tr>
										<td><div align="center"><?php echo functions::datearr($r['act_date']); ?></div></td>
										<td><div align="center"><?php echo number_format($r['act_qty']) ?></div></td>
										<td><div align="center"><?php echo $r['act_unit'] ?></div></td>
										<td><div align="right" style="padding-right:55px;"><?php echo functions::formatMoney($r['act_price']) ?></div></td>
										<td><div align="right" style="padding-right:55px;"><?php echo functions::formatMoney($r['act_amount']) ?></div></td>
										<td><div align="right" style="padding-right:20px;"><?php echo $percentage; ?>%</div></td>
										<td><div align="left"><?php echo $r['act_remarks'] ?></div></td>
										<td>
											<div align="center">
												<a id="edit<?php echo $id?>" class="btn btn-mini btn-warning" title="Modify this Item" data-rel="tooltip" href="?bpid=<?php echo functions::encode($bp_view)?>&idBAEdt=<?php echo functions::encode($id);?>"><i class="halflings-icon white pencil"></i></a>
												<a id="del<?php echo $id;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Item" data-rel="tooltip" href="?bpid=<?php echo functions::encode($bp_view)?>&idDel=<?php echo functions::encode($id);?>"><i class="halflings-icon white trash"></i></a>
											</div>
										</td>
									</tr>
								<?php } ?>
								<?php endwhile; ?>
									<tr>
										<td><div align="center"></div></td>
										<td><div align="center"></div></td>
										<td><div align="center"></div></td>
										<td>&nbsp;</td>
										<td style="<?php echo ($totalActual) ? ((functions::moneyToDouble($txAmountBdt)>$totalActual) ? 'background-color:green;color:white;':'background-color:red;color:white;') : ''; ?>"><div  align="right" style="padding-right:55px;"><strong><?php echo functions::formatMoney($totalActual) ?></strong></div></td>
										<td><div align="right" style="padding-right:20px;"><strong><?php echo $percentage; ?>%</strong></div></td>
										
										<td>&nbsp;</td>
										<td>&nbsp;</td>
									</tr>
								</tbody>
							</table>
							<?php } ?>
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
	$('#txActDate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'<?php echo $year ?>:<?php echo $year ?>',

	});


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
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>