<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array();
#$arrVal=array('project'=>'1');
if($p_id){
	#$arrVal = array('proj_id'=>$p_id,'project'=>'1');
	$arrVal = array('proj_id'=>$p_id);
}

if( isset($_REQUEST['pidDel']) && !empty($_REQUEST['pidDel']) ){
	$projDelID = functions::decode($_REQUEST['pidDel']);
	$db->delete('po',array('proj_id'=>$projDelID));
	$db->delete('voucher_detail',array('proj_id'=>$projDelID));
	$db->delete('project',array('proj_id'=>$projDelID));
}
?>
<!-- body content: start here-->
<form method="post">
	<table width="390" cellspacing="4" cellpadding="6" border='0' align="right">
		<tr>
			<td width="25" height='30'><div style="background-color:#ff0000; width:20px;">&nbsp;</div></td>
			<td width="82">>= 41%</td>
			<td width="25"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
			<td width="69">>= 31%</td>
			<td width="25"><div style="background-color:#eaff00; width:20px;">&nbsp;</div></td>
			<td width="59">>= 20%</td>
			<td width="25"><div style="background-color:#86f59b; width:20px;">&nbsp;</div></td>
			<td width="59">>= DONE</td>
		</tr>
	</table>
	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title>
				<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Project List</h2>
			</div>
			<div align="left"><br>&nbsp;&nbsp;
				<select name="selProj" id="selProj" data-rel="chosen" style="width:80%;font-size:12px;" onChange="projSel(this.value)">
					<option value="">--All Projects--</option>
					<?php 
					$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
					while($rProj = $db->fetch_array($qProj)):?>
					<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
					<?php endwhile;?>
				</select>
			</div>
			<div class="box-content">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px; font-family:Tahoma;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="28%">Project Name</th>
							<th width="10%"><div align="center">Date Started</div></th>
							<th width="5%"><div align="center">Contract Duration<br>(Days)</div></th>
							<th width="9%"><div align="center">Target Date Completion</div></th>
							<th width="5%"><div align="center">Approved Time Extension</div></th>
							<th width="9%"><div align="center">Revised Target Date of Completion</div></th>
							<th width="9%"><div align="center">Contract Amount</div></th>
							<th width="9%"><div align="center">Actual Spent</div></th>
							<th width="4%"><div align="center">Days Elapse</div></th>
							<th width="4%"><div align="center">Days Remain</div></th>
							<th width="14%"><div align="center">Options</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					if(!is_numeric($startrow))
						$startrow=0;
					$qProj = $db->select('project','*',$arrVal,'ORDER BY date_start DESC LIMIT '.$startrow.', '.$rowdisplay);
					$num_record = $db->getValue('project','count(*)',$arrVal);
					while($rProj = $db->fetch_array($qProj)):
						$amount=0;
						$po_amount=0;
						$dateStart = $rProj['date_start'];
						$dateCompletion = $rProj['date_completion'];
						$dateReviseCompletion = $rProj['date_revise_completion'];
						$dateCompleted = $rProj['date_completed'];
						$non_po=$db->getValue('voucher_detail','sum(amount)',array('proj_id'=>$rProj['proj_id']));
						#$amount += $non_po;
						$daysExtension = functions::date_diff($dateCompletion,$dateReviseCompletion);
						$daysDuration = functions::date_diff($dateStart,$dateCompletion);

						$daysElapsed=0;
						$daysRemaining=0;

						if($dateStart <= date('Y-m-d')){
							if( isset($rProj['date_completed']) ){
								$daysElapsed = functions::date_diff($dateStart,$rProj['date_completed']);

								if($rProj['date_revise_completion'])
									$daysRemaining = functions::date_diff($rProj['date_completed'],$dateReviseCompletion);
								else if($rProj['date_completion'])
									$daysRemaining = functions::date_diff($rProj['date_completed'],$rProj['date_completion']);
							}
							else if( !isset($rProj['date_completed']) ){
								$daysElapsed = functions::date_diff($dateStart,date('Y-m-d'));
								if($rProj['date_revise_completion'])
									$daysRemaining = functions::date_diff(date('Y-m-d'),$dateReviseCompletion);
								else if($rProj['date_completion'])
									$daysRemaining = functions::date_diff(date('Y-m-d'),$rProj['date_completion']);
							}
						}

						$qOverhead = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="overhead" AND cost_type="operating expenses"');
						$amount +=  $db->result($qOverhead);

						$qMaterials = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="materials"');
						$amount += $db->result($qMaterials,0);

						$qPO = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) as res FROM po_item poi, po p where p.po_id=poi.po_id AND p.proj_id="'.$db->clean($rProj['proj_id']).'"');
						$amount += $db->result($qPO,0);

						$qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id AND im.proj_id="'.$db->clean($rProj['proj_id']).'"');
						$amount += $db->result($qInhouseMaterial,0);

						$qEquipment = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="equipment"');
						$amount += $db->result($qEquipment);

						$qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND proj_id="'.$db->clean($rProj['proj_id']).'"');
						$amount += $db->result($qInhouseRental,0);

						$qLabor = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="labor"');
						$amount += $db->result($qLabor,0);






						/*$qPO_amount = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) FROM po, po_item WHERE po.po_id=po_item.po_id AND po.proj_id="'.$db->clean($rProj['proj_id']).'"');
						$po_amount = $db->result();
						$amount += $po_amount;

						$qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id AND im.proj_id="'.$db->clean($rProj['proj_id']).'"');
						$amount += $db->result($qInhouseMaterial,0);

						$qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND proj_id="'.$db->clean($rProj['proj_id']).'"');
						$amount += $db->result($qInhouseRental,0);*/

						$bgColor='';
						$consumedPercent=0;
						if( isset($rProj['date_completed']) ){
							$bgColor = 'bgcolor="#86f59b"';
						}
						else if($amount && $rProj['proj_cost']){
							$consumedPercent = ($amount/$rProj['proj_cost']) * 100;
							if( $consumedPercent >= 41)
								$bgColor = 'bgcolor="#E94B4B" style="color: #14D2C2;"';
							elseif( $consumedPercent <= 40 && $consumedPercent >= 31)
								$bgColor = 'bgcolor="#f5ae00"';
							elseif( $consumedPercent <= 30 && $consumedPercent >= 20)
								$bgColor = 'bgcolor="#eaff00"';
						}
					?>
						<tr id="rw<?php echo $rProj['proj_id']?>" <?php echo $bgColor;?>>
							<td><?php echo $rProj['proj_name'];?></td>
							<td><div align="center"><?php echo functions::datearr($rProj['date_start']);?> </i></div></td>
							<td><div align="center"><?php echo $daysDuration;?></div></td>
							<td><div align="center"><?php echo functions::datearr($dateCompletion);?></div></td>
							<td><div align="center"><?php echo $daysExtension;?></div></td>
							<td><div align="center"><?php echo functions::datearr($dateReviseCompletion);?></div></td>
							<td><div align="right"><?php echo functions::formatMoney($rProj['proj_cost']);?></div></td>
							<td><div align="right"><a id="costdetail<?php echo $rProj['proj_id']?>" class="label label-info thickbox" title="Actual Cost Details" data-rel="tooltip" onclick="showThis(this.id,'project_cost_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Cost Details','1')"><?php echo functions::formatMoney($amount);?></a></div></td>
							<td><div align="center"><?php echo $daysElapsed;?></div></td>
							<td><div align="center"><?php echo $daysRemaining;?></div></td>
							<td>
								<div align="center">
									<a id="detail<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Project Detail" data-rel="tooltip" onclick="showThis(this.id,'project_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white zoom-in"></i></a>
									<a id="edit<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Project" data-rel="tooltip" onclick="showThis(this.id,'project_edit.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white pencil"></i></a>
									<?php if($amount==0){?>
									<a id="del<?php echo $rProj['proj_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Project" data-rel="tooltip" href="project_list.php?pidDel=<?php echo functions::encode($rProj['proj_id']);?>"><i class="halflings-icon white trash"></i></a>
									<?php }?>
								</div>
							</td>
						</tr>
					<?php endwhile;?>
					</tbody>
				</table>
				<div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
			</div>
		</div><!--/span-->
	</div><!--/row-->
</form>
<!-- body content: end here-->
<script>
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?pid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
function delt(){
	if(confirm('Do you want to remove this Project?'))
		return true;
	else
		return false; 
}
</script>
<?php require_once('templ_down.php');?>
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