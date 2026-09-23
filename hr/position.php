<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();

$searchVal='';
$qDisp = $db->query('SELECT * FROM dep_position dp LEFT JOIN department d ON dp.dep_id=d.dep_id ORDER BY pos_name');
if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	$qDisp = $db->query('SELECT * FROM dep_position dp LEFT JOIN department d ON dp.dep_id=d.dep_id AND (pos_name LIKE "%'.$db->clean($searchVal).'%" OR pos_desc LIKE "%'.$db->clean($searchVal).'%") ORDER BY pos_desc DESC');
}
#echo $db->last_query;
?>
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'position_manage.php?','Position Detail')">Add New Position</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Position List</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center" style="display:none;">
					<table border="0">
						<tr>
							<td style="padding-top: 7px;"><input type="text" name="txSearch" id="txSearch" value="<?php echo $searchVal?>"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnAll" id="btnAll" value="View All"></td>
						</tr>
					</table>
				</div>
			</form>
			<div class="table-wrapper">
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="35%" scope="col">POSITION TITLE</th>
							<th width="25%" scope="col">DESCRIPTION</th>
							<th width="30%" scope="col">DEPARTMENT</th>
							<th width="9%" scope="col"><div align="center"></div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($rDisp = $db->fetch_array($qDisp)):
						$rID = $rDisp['dp_id'];
					?>
						<tr id="rw<?php echo $rID?>">
							<td><?php echo $rDisp['pos_name'];?></td>
							<td><?php echo $rDisp['pos_desc']?></td>
							<td><?php echo $rDisp['dep_desc'].' ('.$rDisp['dep_name'].')';?></td>
							<td>
								<div align="center">
									<a style="display:none;" id="vw<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="Position Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_detail.php?eid=<?php echo functions::encode($rID);?>','Position Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
									<a id="edit<?php echo $rID?>" class="btn btn-mini btn-warning thickbox" title="Modify Position Detail" data-rel="tooltip" onclick="showThis(this.id,'position_manage.php?eid=<?php echo functions::encode($rID);?>','Position Detail Update')"><i class="halflings-icon white pencil"></i></a>
								</div>
							</td>
						</tr>
						<?php endwhile;?>
					</tbody>
				</table>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}</script>
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>