<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
if($p_id){
	$arrVal = array('vt_id'=>$p_id);
}
?>
<div align="right"><a id="adc" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'voucher_type_add.php?','Supplier Detail')">Add New Voucher Type</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Voucher Type</h2>
		</div>
		<div class="box-content">
			<table class="table table-bordered table-hover table-striped">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="29%" scope="col">NAME</th>
						<th width="22%" scope="col">DESCRIPTION</th>
						<th width="5%" scope="col"><div align="center">OPTION</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$qDisp = $db->select('voucher_type','*',$arrVal,'ORDER BY vt_name');
					while($rDisp = $db->fetch_array($qDisp)):
					?>
					<tr>
						<td><?php echo $rDisp['vt_name']?></td>
						<td><?php echo $rDisp['vt_desc']?></td>
						<td><div align="center"><a id="edit<?php echo $rDisp['vt_id']?>" class="btn btn-mini btn-info thickbox" title="Modify this Supplier" data-rel="tooltip" onclick="showThis(this.id,'voucher_type_edit.php?did=<?php echo functions::encode($rDisp['vt_id']);?>','Supplier Detail')"><i class="halflings-icon white pencil"></i></a></div></td>
					</tr>
					<?php endwhile;?>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}</script>