<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
$searchVal='';
$selAccredited='';$selEvaluated='';
$orderBy = (isset($_REQUEST['orderBy']) && !empty($_REQUEST['orderBy']) ) ? functions::decode($_REQUEST['orderBy']) : 'name';

$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
$sort_AscDesc="ASC";
if($ascDes==="DESC"){
	$ascDes="ASC";
	$sort_AscDesc="ASC";
}
else if($ascDes==="ASC"){
	$ascDes="DESC";
	$sort_AscDesc="DESC";
}

if( isset($_REQUEST['srch']) ){
	$searchVal = ( isset($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : '';
	$selAccredited = ( isset($_REQUEST['selAccredited']) ) ? $_REQUEST['selAccredited'] : '';
	$selEvaluated = ( isset($_REQUEST['selEvaluated']) ) ? $_REQUEST['selEvaluated'] : '';
	$qSearchAccredited = ($selAccredited) ? 'accredited="'.$db->clean($selAccredited).'" AND ' : '';
	$qSearchEvaluated = ($selEvaluated) ? 'evaluated="'.$db->clean($selEvaluated).'" AND ' : '';
	$qDisp = $db->query('SELECT * FROM supplier WHERE '.$qSearchEvaluated.$qSearchAccredited.' (name LIKE "%'.$db->clean($searchVal).'%" OR business_type LIKE "%'.$db->clean($searchVal).'%") ORDER BY name');
}
else if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSupName']) ) ? $_POST['txSupName'] : '';
	$selAccredited = ( isset($_POST['selAccredited']) ) ? $_POST['selAccredited'] : '';
	$selEvaluated = ( isset($_POST['selEvaluated']) ) ? $_POST['selEvaluated'] : '';
	$qSearchAccredited = ($selAccredited) ? 'accredited="'.$db->clean($selAccredited).'" AND ' : '';
	$qSearchEvaluated = ($selEvaluated) ? 'evaluated="'.$db->clean($selEvaluated).'" AND ' : '';
	$qDisp = $db->query('SELECT * FROM supplier WHERE '.$qSearchEvaluated.$qSearchAccredited.' (name LIKE "%'.$db->clean($searchVal).'%" OR business_type LIKE "%'.$db->clean($searchVal).'%") ORDER BY name');
}
else
	$qDisp = $db->select('supplier','*',$arrVal,'ORDER BY name');
$arrDisp=array();
while($rDisp = $db->fetch_array($qDisp)):
	$contact = ($rDisp['contactPhoneNo']) ? $rDisp['contactPhoneNo'].'<br>' : '';
	$contact .= ($rDisp['contactPhoneNo2']) ? $rDisp['contactPhoneNo2'].'<br>' : '';
	$contact .= ($rDisp['contactCellNo']) ? $rDisp['contactCellNo'].'<br>' : '';
	$contact .= ($rDisp['contactCellNo2']) ? $rDisp['contactCellNo2'].'<br>' : '';
	$arrDisp[] = array('supplierID'=>$rDisp['supplierID'],'name'=>$rDisp['name'],'address'=>$rDisp['address'],'contactPhoneNo'=>$contact,'business_type'=>$rDisp['business_type'],'accredited'=>$rDisp['accredited'],'evaluated'=>$rDisp['evaluated'],'vat'=>$rDisp['vat']);
endwhile;
if(count($arrDisp))
	functions::sortMultiArray($arrDisp,$orderBy,$sort_AscDesc);
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Supplier / Payee Directory</h2>
			<div>
				<a id="adc" href="#" class="btn btn-success btn-small btn-setting thickbox" onclick="showThis(this.id,'supplier_add.php?','Supplier Detail')">
					<i class="halflings-icon white plus"></i> Add New Supplier
				</a>
			</div>
		</div>
		<div class="box-content">
			<!-- Search / Filter Form Toolbar -->
			<form method="post" class="form-inline" style="margin-bottom: 20px; background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
				<div class="row-fluid" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
					<input type="text" name="txSupName" id="txSupName" value="<?php echo htmlspecialchars($searchVal); ?>" placeholder="Search name or type..." class="span3" style="margin-bottom: 0;">
					
					<select name="selAccredited" id="selAccredited" class="span3" style="margin-bottom: 0;">
						<option value="">-- All Accreditations --</option>
						<option value="Accredited" <?php if($selAccredited=='Accredited')echo 'selected="selected"';?>>Accredited</option>
						<option value="Pre-Accredited" <?php if($selAccredited=="Pre-Accredited")echo 'selected="selected"';?>>Pre-Accredited</option>
						<option value="Not Accredited" <?php if($selAccredited=="Not Accredited")echo 'selected="selected"';?>>Not Accredited</option>
					</select>
					
					<select name="selEvaluated" id="selEvaluated" class="span3" style="margin-bottom: 0;">
						<option value="">-- All Evaluations --</option>
						<option value="Evaluated" <?php if($selEvaluated=='Evaluated')echo 'selected="selected"';?>>Evaluated</option>
						<option value="Pass" <?php if($selEvaluated=='Pass')echo 'selected="selected"';?>>Pass</option>
						<option value="Failed" <?php if($selEvaluated=='Failed')echo 'selected="selected"';?>>Failed</option>
						<option value="N/A" <?php if($selEvaluated=='N/A')echo 'selected="selected"';?>>N/A</option>
					</select>
					
					<div style="display: flex; gap: 5px;">
						<button type="submit" class="btn btn-info btn-small" name="btnSearch" id="btnSearch"><i class="halflings-icon white search"></i> Search</button>
						<button type="submit" class="btn btn-default btn-small" name="btnAll" id="btnAll">View All</button>
					</div>
				</div>
			</form>

			<!-- Sorting & Count Control Bar -->
			<div style="margin-bottom: 15px; font-size: 12px; color: #555; display: flex; justify-content: space-between; align-items: center; background: #f1f1f1; padding: 8px 12px; border-radius: 4px; flex-wrap: wrap; gap: 10px;">
				<span>Showing <strong><?php echo count($arrDisp); ?></strong> suppliers</span>
				<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
					<span style="color: #333; font-weight: bold;">Sort by:</span>
					<a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('name').'&ascDes='.$ascDes?>" class="btn btn-mini <?php echo ($orderBy=='name') ? 'btn-info' : 'btn-default'; ?>">Name</a>
					<a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('address').'&ascDes='.$ascDes?>" class="btn btn-mini <?php echo ($orderBy=='address') ? 'btn-info' : 'btn-default'; ?>">Address</a>
					<a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('business_type').'&ascDes='.$ascDes?>" class="btn btn-mini <?php echo ($orderBy=='business_type') ? 'btn-info' : 'btn-default'; ?>">Business Type</a>
					<a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('accredited').'&ascDes='.$ascDes?>" class="btn btn-mini <?php echo ($orderBy=='accredited') ? 'btn-info' : 'btn-default'; ?>">Accreditation</a>
					<a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('evaluated').'&ascDes='.$ascDes?>" class="btn btn-mini <?php echo ($orderBy=='evaluated') ? 'btn-info' : 'btn-default'; ?>">Evaluation</a>
				</div>
			</div>

			<!-- Full-Width List Container -->
			<div style="display: flex; flex-direction: column; gap: 10px; width: 100%; box-sizing: border-box;" id="tblist">
			<?php if(count($arrDisp) > 0): ?>
				<?php foreach($arrDisp as $itm):?>
					<div id="rw<?php echo $itm['supplierID']?>" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box;">
						
						<!-- Left Section: Name, Type & Badges -->
						<div style="flex: 2; padding-right: 15px;">
							<div style="font-size: 15px; font-weight: bold; color: #333; margin-bottom: 6px;">
								<?php echo htmlspecialchars($itm['name']); ?>
								<?php echo ($itm['vat']) ? '<small style="font-weight: normal; color: #777;">('.htmlspecialchars($itm['vat']).')</small>' : '';?>
							</div>
							<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; font-size: 12px;">
								<span style="color: #666;"><strong>Business Type:</strong> <span style="background: #f1f1f1; padding: 2px 8px; border-radius: 4px; color: #333;"><?php echo htmlspecialchars($itm['business_type'] ? $itm['business_type'] : 'General'); ?></span></span>
								<?php 
									// Accreditation Badge Styling
									$acc = $itm['accredited'];
									$badgeClass = 'label';
									if($acc == 'Accredited') $badgeClass .= ' label-success';
									elseif($acc == 'Pre-Accredited') $badgeClass .= ' label-warning';
									elseif($acc == 'Not Accredited') $badgeClass .= ' label-important';
									
									echo ($acc) ? '<span class="'.$badgeClass.'">'.htmlspecialchars($acc).'</span>' : '';

									// Evaluation Badge Styling & Icons (Clean visual indicator without "Eval:" text)
									$eval = $itm['evaluated'];
									$evalClass = 'label';
									$evalIcon = '';
									if($eval == 'Pass' || $eval == 'Passed') {
										$evalClass .= ' label-success';
										$evalIcon = '<i class="halflings-icon white ok"></i>';
									} elseif($eval == 'Evaluated') {
										$evalClass .= ' label-info';
										$evalIcon = '<i class="halflings-icon white eye-open"></i>';
									} elseif($eval == 'Failed') {
										$evalClass .= ' label-important';
										$evalIcon = '<i class="halflings-icon white remove"></i>';
									} else { // N/A or default
										$evalClass .= ' label-default';
										$evalIcon = '<i class="halflings-icon white minus"></i>';
									}
									
									echo ($eval) ? '<span class="'.$evalClass.'" title="Evaluation Status: '.htmlspecialchars($eval).'" data-rel="tooltip">'.$evalIcon.' '.htmlspecialchars($eval).'</span>' : '';
								?>
							</div>
						</div>

						<!-- Middle Section: Address & Contact Details -->
						<div style="flex: 2; padding-right: 15px; font-size: 12px; color: #555; border-left: 1px solid #f0f0f0; border-right: 1px solid #f0f0f0; padding-left: 15px;">
							<p style="margin: 0 0 4px 0;"><strong>Address:</strong> <?php echo htmlspecialchars($itm['address'] ? $itm['address'] : 'No address provided'); ?></p>
							<?php if($itm['contactPhoneNo']): ?>
								<p style="margin: 0;"><strong>Contact Numbers:</strong><br><?php echo $itm['contactPhoneNo']; ?></p>
							<?php endif; ?>
						</div>

						<!-- Right Section: Actions with Spacing -->
						<div style="flex: 0 0 90px; text-align: right; display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
							<a id="vw<?php echo $itm['supplierID']?>" class="btn btn-mini btn-success thickbox" title="View Supplier Details" data-rel="tooltip" onclick="showThis(this.id,'supplier_view.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
							<a id="edit<?php echo $itm['supplierID']?>" class="btn btn-mini btn-info thickbox" title="Modify Supplier" data-rel="tooltip" onclick="showThis(this.id,'supplier_edit.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail')"><i class="halflings-icon white pencil"></i></a>
						</div>

					</div>
				<?php endforeach;?>
			<?php else: ?>
				<div style="text-align: center; padding: 40px; background: #fff; border: 1px solid #ddd; border-radius: 6px; width: 100%; box-sizing: border-box;" class="muted">
					<i class="halflings-icon warning-sign"></i> No suppliers found matching your query.
				</div>
			<?php endif; ?>
			</div>

		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}</script>
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>