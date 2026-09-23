<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
$searchVal='';
$selAccredited='';$selEvaluated='';

$deleteID = (isset($_REQUEST['dltID']) && !empty($_REQUEST['dltID']) ) ? functions::decode($_REQUEST['dltID']) : 0;
$orderBy = (isset($_REQUEST['orderBy']) && !empty($_REQUEST['orderBy']) ) ? functions::decode($_REQUEST['orderBy']) : 'pc_name';

if($deleteID){
	if( $db->getValue('project','count(*)',array('pc_id'=>$deleteID))==0 ){
		$db->delete('project_client',array('pc_id'=>$deleteID));
		$_SESSION['notif_warning']='Client Removed!';
		functions::sendTo(functions::pageName());
		die();
	}
}
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
	$qDisp = $db->query('SELECT * FROM project_client WHERE pc_name LIKE "%'.$db->clean($searchVal).'%" ORDER BY pc_name');
}
else if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSupName']) ) ? $_POST['txSupName'] : '';
	$qDisp = $db->query('SELECT * FROM project_client WHERE pc_name LIKE "%'.$db->clean($searchVal).'%" ORDER BY pc_name');
}
else
	$qDisp = $db->select('project_client','*',$arrVal,'ORDER BY pc_name');

$arrDisp=array();
while($rDisp = $db->fetch_array($qDisp)):
	$arrDisp[] = array('pc_id'=>$rDisp['pc_id'],'pc_name'=>$rDisp['pc_name'],'address'=>$rDisp['address'],'contact_no'=>$rDisp['contact_no'],'establishment_type'=>$rDisp['establishment_type']);
endwhile;
if(count($arrDisp))
	functions::sortMultiArray($arrDisp,$orderBy,$sort_AscDesc);
?>
<!-- body content: start here-->
<div align="right">
	<a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'project_client_manage.php?','Client Detail')">Add New Client</a>
	<a id="adcd" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'project_client_monitoring.php?','Client Detail')">Client's Project Monitoring</a>
</div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Client List</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0">
						<tr>
							<td style="padding-top: 7px;"><input type="text" name="txSupName" id="txSupName" value="<?php echo $searchVal?>"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info btn-small" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info btn-small" name="btnAll" id="btnAll" value="View All"></td>
						</tr>
					</table>
				</div>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="30%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('pc_name').'&ascDes='.$ascDes?>">NAME</a></th>
						<th width="20%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('address').'&ascDes='.$ascDes?>">ADDRESS</a></th>
						<th width="10%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('contact_no').'&ascDes='.$ascDes?>">CONTACT #</a></th>
						<th width="11%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('establishment_type').'&ascDes='.$ascDes?>">ESTABLISHMENT TYPE</a></th>
						<th width="9%" scope="col"><div align="left" style="padding-left:15px;">OPTIONS</div></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($arrDisp as $itm):?>
				<tr id="rw<?php echo $itm['pc_id']?>">
					<td style="font-size: 14px;"><?php echo $itm['pc_name'];?></td>
					<td><?php echo $itm['address']?></td>
					<td><?php echo $itm['contact_no'];?></td>
					<td><?php echo $itm['establishment_type']?></td>
					<td>
						<div align="left" style="padding-left:15px;">
							<a id="vw<?php echo $itm['pc_id']?>" class="btn btn-mini btn-info thickbox" title="Client Project List" data-rel="tooltip" onclick="showThis(this.id,'project_client_projects.php?pcID=<?php echo functions::encode($itm['pc_id']);?>','Client Projects','1')"><i class="halflings-icon white zoom-in"></i></a>
							<a id="edit<?php echo $itm['pc_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Client" data-rel="tooltip" onclick="showThis(this.id,'project_client_manage.php?edtID=<?php echo functions::encode($itm['pc_id']);?>','Client Detail')"><i class="halflings-icon white pencil"></i></a>
							<?php if( $db->getValue('project','count(*)',array('pc_id'=>$itm['pc_id']))==0 ){ ?>
							<a id="dlt<?php echo $itm['pc_id']?>" onClick="return delt()" class="btn btn-mini btn-danger" title="Modify this Client" data-rel="tooltip" href="?dltID=<?php echo functions::encode($itm['pc_id'])?>"><i class="halflings-icon white trash"></i></a>
							<?php } ?>
						</div>
					</td>
				</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<script>function delt(){if(confirm('Do you want to remove this client?'))return true; else return false;}</script>
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