<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/voucher_advance.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

function voucherHier($vid){
	global $db;
	$source=0;
	$arrVID_list=array();
	$parent = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$vid));
	if($parent){
		$grand = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$parent));
		if($grand)
			$arrVID_list = functions::insert_array(voucherHier($parent),$parent);
		else
			$arrVID_list = functions::insert_array($arrVID_list,$parent);
	}
	return $arrVID_list;
}

function getFirstVoucher($vid){
	global $db;
	$source=0;
	$parent = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$vid));
	if($parent){
		$grand = $db->getValue('voucher_charge_order','voucher_top',array('voucher_owner'=>$parent));
		if($grand)
			$source=getFirstVoucher($parent);
		else
			$source = $parent;
	}
	return $source;
}

$vid = (isset($_REQUEST['vid']) && !empty($_REQUEST['vid']) ) ? functions::decode($_REQUEST['vid']) : 0;
$arrVHier = (voucherHier($vid));
$editItem = (isset($_REQUEST['edtID']) && !empty($_REQUEST['edtID']) ) ? functions::decode($_REQUEST['edtID']) : 0;
$deleteItem = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
$approveStatus = $db->getValue('voucher','count(approved)',array('voucher_id'=>$vid));
$deduction_name='';$deduction_value='';$remarks='';
$va = new voucherAdvance($vid);
if($editItem){
	$q = $db->select('voucher_deduction','*',array('vd_id'=>$editItem));
	while($r = $db->fetch_array($q)):
		$deduction_name = $r['deduction_name'];
		$deduction_value = functions::formatMoney($r['deduction_value']);
		$remarks = $r['remarks'];
	endwhile;
}

if($deleteItem && $vid){
	$db->delete('voucher_deduction',array('vd_id'=>$deleteItem));
	$_SESSION['notif_warning']='Item Removed!';
	functions::sendTo(functions::pageName().'?vid='.functions::encode($vid));
	die();
}
$txVoucherPrev=$db->getValue('voucher_charge_order vco, voucher v','voucher_no',array('voucher_owner'=>$vid),'AND v.voucher_id=vco.voucher_top');
$qItemDeduction = $db->query('SELECT DISTINCT deduction_name FROM voucher_deduction WHERE charge_type="surcharge" ORDER BY deduction_name');
$namesDeduction='';
while($rItemDeduction=$db->fetch_array($qItemDeduction)):
	$string = preg_replace("/'/",'"',$rItemDeduction['deduction_name']);
	$namesDeduction .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesDeduction .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Voucher Surcharges</title>
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
	<!-- end: Favicon -->
	<?php
	if( isset($_POST['btnCancel']) && !empty($eid) && !empty($eat_id) ){
		functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&eatid='.functions::encode($eat_id));
		die();
	}
	if( isset($_POST['btnSave']) && !empty($vid) ){
		$deduction_name = ( isset($_POST['txDeductName']) ) ? trim($_POST['txDeductName']) : '';
		$deduction_value = ( isset($_POST['txDeductValue']) ) ? functions::moneyToDouble($_POST['txDeductValue']) : '';
		$remarks = ( isset($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
		$arrField = array('deduction_name'=>$deduction_name,'deduction_value'=>$deduction_value,'remarks'=>$remarks,'voucher_id'=>$vid,'charge_type'=>'surcharge');
		if($deduction_name && $vid){
			if($editItem){
				$db->update('voucher_deduction',$arrField,array('vd_id'=>$editItem));
				$_SESSION['notif_success']='Changes Saved!';
			}
			else{
				$ins = $db->insert('voucher_deduction',$arrField);
				if($ins){
					$_SESSION['notif_success']='New Item Added!';
				}
			}
			functions::sendTo(functions::pageName().'?vid='.functions::encode($vid));
			die();
		}
		else
		functions::say('Please fill up the form properly!');
	}
	?>
</head>
<body>
<!-- body content: start here-->
<?php
$arrCharges=array();
foreach($arrVHier as $vded):
	$qvd = $db->select('voucher_deduction','*',array('voucher_id'=>$vded,'charge_type'=>'surcharge'));
	while($rvd = $db->fetch_array($qvd)):
		$d_name = $rvd['deduction_name'];
		$d_value = $rvd['deduction_value'];
		if( isset($arrCharges[$d_name]) )
			$arrCharges[$d_name] += $d_value;
		else
			$arrCharges[$d_name] = $d_value;
	endwhile;
endforeach;
?>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Voucher Surcharges</h2>
		</div>
		<div class="box-content">
			<ul class="nav tab-menu nav-tabs">
				<li class="active"><a href="voucher_manage_surcharge.php?vid=<?php echo functions::encode($vid)?>" style="opacity:.9">Surcharge</a></li>
				<li><a href="voucher_manage_deduction.php?vid=<?php echo functions::encode($vid)?>">Other Deductions</a></li>
			</ul>
			
			<form class="form-horizontal" method="post">
				<!-- Main Container with controlled max-width for modern layout -->
				<div style="max-width: 800px; margin: 0 auto;">
					
					<!-- Form Panel (Only shown if editable) -->
					<?php if($approveStatus==0){?>
					<div class="well" style="background-color: #fcfcfc; border: 1px solid #e3e3e3; padding: 20px; margin-bottom: 25px; border-radius: 6px;">
						<h4 style="margin-top: 0; margin-bottom: 15px; color: #333; font-size: 14px; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 8px;">
							<?php echo $editItem ? 'Edit Surcharge Item' : 'Add New Surcharge'; ?>
						</h4>
						
						<div class="row-fluid" style="margin-bottom: 10px;">
							<div class="span7">
								<label style="font-size: 11px; font-weight: bold; color: #555; margin-bottom: 3px;">Surcharge Name</label>
								<input type="text" name="txDeductName" id="txDeductName" class="span12" autofocus value="<?php echo $deduction_name;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesDeduction;?>]' placeholder="e.g. Late Fee">
							</div>
							<div class="span5">
								<label style="font-size: 11px; font-weight: bold; color: #555; margin-bottom: 3px;">Amount</label>
								<input type="text" name="txDeductValue" id="txDeductValue" class="span12" style="text-align: right; font-weight: bold;" value="<?php echo $deduction_value?>" onkeyup="FormatCurrency(this);" placeholder="0.00">
							</div>
						</div>

						<div class="row-fluid" style="margin-bottom: 15px;">
							<div class="span12">
								<label style="font-size: 11px; font-weight: bold; color: #555; margin-bottom: 3px;">Remarks / Notes</label>
								<textarea name="txRemarks" id="txRemarks" class="span12" style="height: 50px; resize: vertical;" placeholder="Optional remarks..."><?php echo $remarks?></textarea>
							</div>
						</div>

						<div style="text-align: right;">
							<input type="submit" name="btnSave" id="btnSave" value="<?php echo $editItem ? 'UPDATE CHANGES' : 'SAVE SURCHARGE'; ?>" class="btn btn-primary" style="padding: 6px 15px;">
							<?php if($editItem){?>
								<a href="?vid=<?php echo functions::encode($vid);?>" class="btn btn-default" style="padding: 6px 15px; margin-left: 5px;">Cancel</a>
							<?php } else { ?>
								<input type="submit" name="btnCancel" id="btnCancel" value="CLEAR" class="btn btn-default" style="padding: 6px 15px; margin-left: 5px;">
							<?php } ?>
						</div>
					</div>
					<?php }?>

					<!-- List of Surcharges (Card Feed Layout) -->
					<div style="margin-bottom: 10px; font-weight: bold; color: #666; font-size: 12px; text-transform: uppercase;">
						Recorded Surcharges
					</div>

					<div style="display: flex; flex-direction: column; gap: 10px;">
						<?php
						$total_adjustment=0;
						$q = $db->select('voucher_deduction','*',array('voucher_id'=>$vid,'charge_type'=>'surcharge'),'ORDER BY deduction_name');
						$hasItems = false;
						while($r = $db->fetch_array($q)):
							$hasItems = true;
							$rID = $r['vd_id'];
							$total_adjustment += $r['deduction_value'];
							$previousCharges = isset($arrCharges[$r['deduction_name']]) ? $arrCharges[$r['deduction_name']] : 0;
						?>
						<div style="background: #fff; border: 1px solid #ddd; border-left: 4px solid #0044cc; padding: 15px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
							<div style="flex-grow: 1; padding-right: 15px;">
								<div style="font-size: 14px; font-weight: bold; color: #333; margin-bottom: 4px;">
									<?php echo htmlspecialchars($r['deduction_name']);?>
								</div>
								<?php if($r['remarks'] || $previousCharges){ ?>
									<div style="font-size: 12px; color: #666; margin-top: 4px;">
										<?php echo htmlspecialchars($r['remarks']); ?>
										<?php if($previousCharges){
											if($r['remarks']) echo '<br>';
											echo '<span class="label label-info" style="font-size: 10px; margin-top: 4px; display: inline-block;">Total '.$r['deduction_name'].' ('.functions::formatMoney($previousCharges + $r['deduction_value']).')</span>';
										}?>
									</div>
								<?php } ?>
							</div>
							
							<div style="display: flex; align-items: center; gap: 15px;">
								<div style="font-size: 15px; font-weight: bold; color: #0044cc; font-family: monospace;">
									<?php echo functions::formatMoney($r['deduction_value']);?>
								</div>
								<?php if($approveStatus==0){?>
									<div style="display: flex; align-items: center; gap: 6px;">
										<a id="edt<?php echo $rID;?>" class="btn btn-mini btn-warning" title="Modify" data-rel="tooltip" href="?vid=<?php echo functions::encode($vid);?>&edtID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white pencil"></i></a>
										<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Item" data-rel="tooltip" href="?vid=<?php echo functions::encode($vid);?>&delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
									</div>
								<?php }?>
							</div>
						</div>
						<?php endwhile;?>

						<?php if(!$hasItems): ?>
						<div style="background: #fdfdfd; border: 1px dashed #ddd; padding: 30px; text-align: center; border-radius: 4px; color: #999; font-style: italic;">
							No surcharges recorded for this voucher yet.
						</div>
						<?php endif; ?>
					</div>

					<!-- Total Footer Card -->
					<?php if($hasItems): ?>
					<div style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
						<div style="font-weight: bold; color: #333; text-transform: uppercase; font-size: 12px;">Total Adjustments</div>
						<div style="display: flex; align-items: center; gap: 15px;">
							<div style="font-size: 16px; font-weight: bold; color: #0044cc; font-family: monospace;">
								<?php echo functions::formatMoney($total_adjustment)?>
							</div>
							<?php if($approveStatus==0){?>
								<div style="visibility: hidden; display: flex; align-items: center; gap: 6px;">
									<a class="btn btn-mini"><i class="halflings-icon pencil"></i></a>
									<a class="btn btn-mini"><i class="halflings-icon trash"></i></a>
								</div>
							<?php }?>
						</div>
					</div>
					<?php endif; ?>

				</div>
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
<script>
function delt(){if(confirm('Do you want to remove this item?'))return true; else return false;}
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