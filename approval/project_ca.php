<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array();
if($p_id){
	$arrVal = array('proj_id'=>$p_id,'project'=>'1');
}
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Project List</h2>
		</div><br>
		<div align="right">
			<a id="elm" title="Equipment Labor Material Unit Cost" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'elm-equipment.php?','Equipment Manpower Material Unit Cost','1')"><i class="halflings-icon white zoom-in"></i>ELM Unit Cost</a>
			<a id="elc" title="Equipment Labor Material Capabilities" data-rel="tooltip" class="btn btn-mini thickbox" onclick="showThis(this.id,'elc.php?','Equipment Manpower Material Capabilities','1')"><i class="halflings-icon white zoom-in"></i>ELM Capabilites</a>&nbsp;&nbsp;&nbsp;
		</div>
		<div align="left"><br>&nbsp;&nbsp;
			<select name="selProj" id="selProj" data-rel="chosen" style="width:80%;font-size:12px;" onChange="projSel(this.value)">
				<option value="">--All Projects--</option>
				<?php $qProj = $db->select('project','*',array('project'=>'1'),'ORDER BY proj_name');
				while($rProj = $db->fetch_array($qProj)):
				?>
				<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
				<?php endwhile;?>
			</select>
		</div>
		<div class="box-content">
			<table class="table table-bordered table-hover table-striped" style="font-size:12px; font-family:Tahoma;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="50%">Project Name</th>
						<th width="10%"><div align="center">Date Started</div></th>
						<th width="7%"><div align="center">DQC</div></th>
						<th width="7%"><div align="center">Cost Analysis</div></th>
						<th width="7%"><div align="center">POW</div></th>
						<th width="7%"><div align="center">BOQ</div></th>
						<th width="7%"><div align="center">Full Details</div></th>
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
				?>
					<tr>
						<td><?php echo $rProj['proj_name'];?></td>
						<td><div align="center"><?php echo functions::datearr($rProj['date_start']);?> </i></div></td>
						<td><div align="center"><a id="dqc<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Detailed Quantity Calculation" data-rel="tooltip" onclick="showThis(this.id,'dqc.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Detailed Quantity Calculation','1')"><i class="halflings-icon white list-alt"></i></a></div></td>
						<td><div align="center"><a id="ca<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Cost Analysis" data-rel="tooltip" onclick="showThis(this.id,'ca_dqc.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Cost Analysis','1')"><i class="halflings-icon white list-alt"></i></a></div></td>
						<td>
							<?php if( $db->getValue('cost_analysis','count(*)',array('proj_id'=>$rProj['proj_id'])) ){?>
							<div align="center"><a id="pow<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Program Of Works" data-rel="tooltip" onclick="showThis(this.id,'pow.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Program Of Works','1')"><i class="halflings-icon white list-alt"></i></a></div>
							<?php }?>
						</td>
						<td>
							<?php if( $db->getValue('cost_analysis','count(*)',array('proj_id'=>$rProj['proj_id'])) ){?>
							<div align="center"><a id="boq<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Bill Of Quantities" data-rel="tooltip" onclick="showThis(this.id,'boq.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Bill Of Quantities','1')"><i class="halflings-icon white list-alt"></i></a></div>
							<?php }?>
						</td>
						<td><div align="center"><a id="detail<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Project Detail" data-rel="tooltip" onclick="showThis(this.id,'project_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white zoom-in"></i></a></div></td>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
			<div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?pid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
</script>
<?php require_once('templ_down.php');?>