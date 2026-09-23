<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
$searchVal='';
$qDisp = $db->select('leave_config','*',array(),'ORDER BY leave_name');
?>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'leave_type_manage.php?','Leave Type Detail')">Add New Leave Type</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>LEAVE TYPE List</h2>
		</div>
		<div class="box-content">
			<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="27%" scope="col">NAME</th>
						<th width="10%" scope="col">DAYS ALLOWED</th>
						<th width="10%" scope="col">PAYMENT</th>
						<th width="9%" scope="col"><div align="center">OPTION</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				while($rDisp = $db->fetch_array($qDisp)):
					$rID = $rDisp['lc_id'];
					$allwdays = '';
					if($db->getValue('leave_config_add','count(*)',array('lc_id'=>$rID))){
						$qlca = $db->select('leave_config_add','*',array('lc_id'=>$rID),'ORDER BY service_year_from');
						while($rlca = $db->fetch_array($qlca)):
							$allwdays .= $rlca['service_year_from'].' - '.$rlca['service_year_to'].' Years ('.$rlca['allowed_days'].' Days)<br>';
						endwhile;
					}
					else{
						$allwdays = ($rDisp['allowed_days']==0) ? '~' : $rDisp['allowed_days'];
					}
					?>
					<tr>
						<td style="font-size: 14px;"><?php echo $rDisp['leave_name'];?></td>
						<td><?php echo $allwdays;?></td>
						<td><?php echo ($rDisp['with_pay']) ? 'With Pay' : 'Without Pay';?></td>
						<td>
							<div align="center">
								<a id="edit<?php echo $rID?>" class="btn btn-mini btn-warning thickbox" title="Modify Leave Detail" data-rel="tooltip" onclick="showThis(this.id,'leave_type_manage.php?eid=<?php echo functions::encode($rID);?>','Leave type Detail Update')"><i class="halflings-icon white pencil"></i></a>
							</div>
						</td>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}</script>
<?php require_once('templ_down.php');?>