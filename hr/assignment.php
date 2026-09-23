<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
if($delID){
	if( $db->getValue('emp_attendance','count(*)',array('eas_id'=>$delID)) == 0){
		$_SESSION['notif_warning']='Group Removed!';
		$db->delete('emp_assignment',array('eas_id'=>$delID));
	}
	functions::sendTo(functions::pageName());
	die();
}

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['asProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : '';
	functions::sendTo(functions::pageName());
	die();
}
$arr = array();
$projID = ( isset($_SESSION['asProj']) && !empty($_SESSION['asProj']) ) ? $_SESSION['asProj'] : '';

if($projID)
	$arr = array_merge($arr,array('p.proj_id'=>$projID));

#unset($_SESSION['arr_attendance_emp']);
?>
<style type="text/css">
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'assignment_manage.php?','Employee Assignment')">Add Employee Assignment</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Group Assignment</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0" width="90%">
						<tr>
							<td width="50%" style="padding-top: 7px;">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:98%;font-size:12px;">
									<option value="">--All Projects / Department--</option>
									<?php 
									$qProj = $db->select('project','*',array(),'WHERE proj_id IN (SELECT DISTINCT proj_id FROM emp_assignment) ORDER BY proj_name');
									while($rProj = $db->fetch_array($qProj)):?>
									<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($projID==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
									<?php endwhile;?>
								</select>
							</td>
							<td width="7%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></div></td>
						</tr>
					</table>
				</div>
			</form>
			<div class="table-wrapper">
				<table class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="50%" scope="col">Project / Department</th>
							<th width="30%" scope="col">Group Name</th>
							<th width="10%" scope="col"><div align="center">Member</div></th>
							<th width="10%" scope="col"><div align="center">&nbsp;</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					if($projID)
						$qDisp = $db->select('emp_assignment p','*',array('proj_id'=>$projID),'ORDER BY proj_id');
					else
						$qDisp = $db->query('SELECT * FROM emp_assignment ea, project p WHERE ea.proj_id=p.proj_id ORDER BY proj_name');
					#echo $db->last_query;
					while($rDisp = $db->fetch_array($qDisp)):
						$rID = $rDisp['eas_id'];
						$member = $db->getValue('emp_assign_detail','count(*)',array('eas_id'=>$rID));
					?>
						<tr id="rw<?php echo $rID;?>">
							<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rDisp['proj_id']));?></td>
							<td><?php echo $rDisp['eas_name']?></td>
							<td><div align="right" style="padding-right:30px;"><?php echo $member?></div></td>
							<td>
								<div align="center">
									<a id="vw<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="Group Detail" data-rel="tooltip" onclick="showThis(this.id,'assignment_group_select.php?easid=<?php echo functions::encode($rID);?>','Group Detail')"><i class="halflings-icon white zoom-in"></i></a>
									<?php if( $db->getValue('emp_attendance','count(*)',array('eas_id'=>$rID)) == 0){?>
									<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Group" data-rel="tooltip" href="?delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
									<?php }?>
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
<script>
function delt(){if(confirm('Do you want to remove this Group?'))return true; else return false;}
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