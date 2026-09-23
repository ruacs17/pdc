<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array('project'=>1);
if($p_id){
	$arrVal = array('proj_id'=>$p_id,'project'=>1);
}
?>
<!-- body content: start here-->
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
			<select name="selProj" id="selProj" data-rel="chosen" style="width:90%;font-size:12px;" onChange="projSel(this.value)">
				<option value="">--All Projects--</option>
				<?php $qProj = $db->select('project','*',array('project'=>1),'ORDER BY proj_name');
				while($rProj = $db->fetch_array($qProj)):
				?>
				<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
				<?php endwhile;?>
			</select>
		</div>
		<div class="box-content">
			<table class="table table-bordered" style="font-size:12px; font-family:Tahoma;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="40%">Project Name</th>
						<th width="7%"><div align="center">Date Started</div></th>
						<th width="5%"><div align="right">Contract Duration<br>(Days)</div></th>
						<th width="7%"><div align="center">Target Date Completion</div></th>
						<th width="5%"><div align="right">Approved Time Extension</div></th>
						<th width="7%"><div align="center">Revised Target Date of Completion</div></th>
						<th width="4%"><div align="right">Days Elapse</div></th>
						<th width="4%"><div align="right">Days Remain</div></th>
						<th width="5%">&nbsp;</th>
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
					$qPO_amount = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) FROM po, po_item WHERE po.po_id=po_item.po_id AND po.proj_id="'.$db->clean($rProj['proj_id']).'"');
					$po_amount = $db->result();
					$amount = $non_po + $po_amount;
					$bgColor='';
					$consumedPercent=0;
					if( isset($rProj['date_completed']) ){
						$bgColor = 'bgcolor="#86f59b"';
					}
					else if($amount && $rProj['proj_cost']){
						$consumedPercent = ($amount/$rProj['proj_cost']) * 100;
						if( $consumedPercent >= 41)
							$bgColor = 'bgcolor="#ff0000" style="color: #FFF;"';
						elseif( $consumedPercent <= 40 && $consumedPercent >= 31)
							$bgColor = 'bgcolor="#f5ae00"';
						elseif( $consumedPercent <= 30 && $consumedPercent >= 20)
							$bgColor = 'bgcolor="#eaff00"';
					}
				?>
					<tr <?php echo $bgColor;?>>
						<td><?php echo $rProj['proj_name'];?></td>
						<td><div align="center"><?php echo functions::datearr($rProj['date_start']);?></i></div></td>
						<td><div align="right"><?php echo $daysDuration;?></div></td>
						<td><div align="center"><?php echo functions::datearr($dateCompletion);?></div></td>
						<td><div align="right"><?php echo $daysExtension;?></div></td>
						<td><div align="center"><?php echo functions::datearr($dateReviseCompletion);?></div></td>
						<td><div align="right"><?php echo $daysElapsed;?></div></td>
						<td><div align="right"><?php echo $daysRemaining;?></div></td>
						<td><div align="center"><a id="detail<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Project Detail" data-rel="tooltip" onclick="showThis(this.id,'project_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white zoom-in"></i></a></div></td>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
			<div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function projSel(PiEwgD){window.location="project_list.php?pid="+PiEwgD}
</script>
<?php require_once('templ_down.php');?>