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
						<option value="Passed" <?php if($selEvaluated=='Passed')echo 'selected="selected"';?>>Passed</option>
						<option value="Failed" <?php if($selEvaluated=='Failed')echo 'selected="selected"';?>>Failed</option>
					</select>
					
					<div style="display: flex; gap: 5px;">
						<button type="submit" class="btn btn-info btn-small" name="btnSearch" id="btnSearch"><i class="halflings-icon white search"></i> Search</button>
						<button type="submit" class="btn btn-default btn-small" name="btnAll" id="btnAll">View All</button>
					</div>
				</div>
			</form>

			<!-- Data Table -->
			<div class="table-responsive">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-striped table-hover bootstrap-datatable" style="font-size: 12px; width: 100%;">
					<thead>
						<tr style="background-color: #f5f5f5;">
							<th width="22%"><a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('name').'&ascDes='.$ascDes?>">NAME / VAT</a></th>
							<th width="18%"><a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('address').'&ascDes='.$ascDes?>">ADDRESS</a></th>
							<th width="15%"><a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('contactPhoneNo').'&ascDes='.$ascDes?>">CONTACT #</a></th>
							<th width="12%"><a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('business_type').'&ascDes='.$ascDes?>">BUSINESS TYPE</a></th>
							<th width="12%"><a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('accredited').'&ascDes='.$ascDes?>">ACCREDITATION</a></th>
							<th width="11%"><a href="?srch=<?php echo urlencode($searchVal)?>&orderBy=<?php echo functions::encode('evaluated').'&ascDes='.$ascDes?>">EVALUATION</a></th>
							<th width="10%" style="text-align: center;">ACTIONS</th>
						</tr>
					</thead>
					<tbody>
					<?php if(count($arrDisp) > 0): ?>
						<?php foreach($arrDisp as $itm):?>
							<tr id="rw<?php echo $itm['supplierID']?>">
								<td style="font-size: 13px; font-weight: 600;">
									<?php echo htmlspecialchars($itm['name']); ?>
									<?php echo ($itm['vat']) ? '<br><small class="muted">('.htmlspecialchars($itm['vat']).')</small>' : '';?>
								</td>
								<td><small><?php echo htmlspecialchars($itm['address']); ?></small></td>
								<td><small><?php echo $itm['contactPhoneNo'];?></small></td>
								<td><?php echo htmlspecialchars($itm['business_type']); ?></td>
								<td>
									<?php 
										$acc = $itm['accredited'];
										$badgeClass = 'label';
										if($acc == 'Accredited') $badgeClass .= ' label-success';
										elseif($acc == 'Pre-Accredited') $badgeClass .= ' label-warning';
										elseif($acc == 'Not Accredited') $badgeClass .= ' label-important';
										
										echo ($acc) ? '<span class="'.$badgeClass.'">'.htmlspecialchars($acc).'</span>' : '<span class="muted">----</span>';
									?>
								</td>
								<td>
									<?php 
										$eval = $itm['evaluated'];
										$evalClass = 'label';
										if($eval == 'Passed' || $eval == 'Evaluated') $evalClass .= ' label-success';
										elseif($eval == 'Failed') $evalClass .= ' label-important';
										
										echo ($eval) ? '<span class="'.$evalClass.'">'.htmlspecialchars($eval).'</span>' : '<span class="muted">----</span>';
									?>
								</td>
								<td style="text-align: center;">
									<div style="display: flex; gap: 5px; justify-content: center; align-items: center;">
										<a id="vw<?php echo $itm['supplierID']?>" class="btn btn-mini btn-success thickbox" title="View Supplier Details" data-rel="tooltip" onclick="showThis(this.id,'supplier_view.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
										<a id="edit<?php echo $itm['supplierID']?>" class="btn btn-mini btn-info thickbox" title="Modify Supplier" data-rel="tooltip" onclick="showThis(this.id,'supplier_edit.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail')"><i class="halflings-icon white pencil"></i></a>
									</div>
								</td>
							</tr>
						<?php endforeach;?>
					<?php else: ?>
						<tr>
							<td colspan="7" style="text-align: center; padding: 30px;" class="muted">
								<i class="halflings-icon warning-sign"></i> No suppliers found matching your query.
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
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
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>