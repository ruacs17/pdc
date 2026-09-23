<?php require_once('templ_up.php');?>
<?php

#$logs = new Logs();
#$logs->save('visit');
$count=0;
#$itemID = $db->getValue('department','dep_id',array('dep_head'=>'0','dep_desc'=>'Equipment'));
$dep_source=1;

function disp($parentItem,$level){
	global $db;
	$string='';
	$q = $db->select('department','*',array('dep_head'=>$parentItem),'ORDER BY dep_name');

	while($r = $db->fetch_array($q)):
		$dep_name = ($r['dep_name']) ? $r['dep_name'] : "";
		$dep_head = ($r['dep_head']) ? $r['dep_head'] : "";
		$dep_desc = ($r['dep_desc']) ? $r['dep_desc'] : "";
		#$dep_type = ($r['dep_type']) ? $r['dep_type'] : "";
		$dep_type = "";
		$s = '';
		$pad=0;
		for($i=1; $i<=$level; $i++):
			$pad+=40;
		endfor;
		$addSubItem = "showThis(this.id,'department-add.php?itemID=".functions::encode($r['dep_id'])."','Adding Sub-Department')";
		$manageItem = "showThis(this.id,'department-manage.php?itemID=".functions::encode($r['dep_id'])."','Manage Department')";
		$deleteItem = "?delItemID=".functions::encode($r['dep_id'])."&itemID=".functions::encode($r['dep_base']).'&delItemParent='.functions::encode($dep_head);
		$btn = '&nbsp;&nbsp;&nbsp;
		<a id="AddSubItem'.$r['dep_id'].'" class="thickbox opt" style="cursor:pointer" title="Add Sub Department" data-rel="tooltip" onclick="'.$addSubItem.'"><i class="halflings-icon plus-sign"></i></a>
		<a id="ManageItem'.$r['dep_id'].'" class="thickbox opt" style="cursor:pointer" title="Manage Department" data-rel="tooltip" onclick="'.$manageItem.'"><i class="halflings-icon pencil"></i></a>
		';
		$btn .= ($db->getValue('department','count(*)',array('dep_head'=>$r['dep_id']))==0) ?  '<a id="DeleteItem'.$r['dep_id'].'" class="opt" style="cursor:pointer" title="Remove this department" data-rel="tooltip" onClick="return delt()" href="'.$deleteItem.'"><i class="halflings-icon minus-sign"></i></a>' : '<a id="DeleteItem'.$r['dep_id'].'" style="cursor:not-allowed;opacity: 0.1;" title="Sub-department must be removed first!" data-rel="tooltip" onClick="return false" href=""><i class="halflings-icon minus-sign"></i></a>';
		$dep_name = ($dep_name) ? ' ('.$dep_name.')' : '';
		$string ='
		<tr id="rw'.$r['dep_id'].'">
			<td>
				<div style="padding-left:'.$pad.'px;">
					<div style="display:flex;">
						<div style="border:0px solid;"><img src="../img/arrow-down-right.png" height="16" width="12"> '.$dep_desc.' '.$dep_type.'</div>
						<div style="border:0px solid;padding-left:15px;">'.$dep_name.$btn.'</div>
					</div>
				</div>
			</td>
		</tr>
		';
		echo $string;
		disp($r['dep_id'],$level + 1);
    endwhile;
}
function deleteItemAndSub($dep_id){
	global $db;
	$q = $db->select('department','*',array('dep_head'=>$dep_id));
	while( $r = $db->fetch_array($q) ):
		$dep_id = $r['dep_id'];
		$db->delete('department',array('dep_id'=>$dep_id));
		deleteItemAndSub($dep_id);
	endwhile;
	$db->delete('department',array('dep_id'=>$dep_id));
}

function deleteItem($delItemID){
	global $db;
	$dep_head = $db->getValue('department','dep_head',array('dep_id'=>$delItemID));
	deleteItemAndSub($delItemID);
}
$delItemID = (isset($_REQUEST['delItemID']) && !empty($_REQUEST['delItemID']) ) ? functions::decode($_REQUEST['delItemID']) : 0;
$delItemParent = (isset($_REQUEST['delItemParent']) && !empty($_REQUEST['delItemParent']) ) ? functions::decode($_REQUEST['delItemParent']) : 0;
if( $delItemID ){
	if( $db->getValue('po','count(*)',array('dep_id'=>$delItemID)) ){
		$_SESSION['notif_warning']='Department is still attached to P.O.!';
		$_SESSION['notif_id_list']=$delItemID;
	}
	else if( $db->getValue('department','count(*)',array('dep_head'=>$delItemID)) ){
		$_SESSION['notif_warning']='Sub-department must be removed first!';
		$_SESSION['notif_id_list']=$delItemID;
	}
	else if( $db->getValue('emp_position','count(*)',array('dep_id'=>$delItemID)) ){
		$_SESSION['notif_warning']='Un-assigned employees in this department first!';
		$_SESSION['notif_id_list']=$delItemID;
	}
	else{
		deleteItem($delItemID);
		$_SESSION['notif_warning']='Department removed!';
		$_SESSION['notif_id_list']=$delItemParent;
	}
	functions::sendTo(functions::pageName());
	die();
}
?>
<style>
.opt{
	opacity: 0.4;
	filter: alpha(opacity=20); 
	cursor:pointer
}
.opt:hover {
	opacity: 1.0;
	filter: alpha(opacity=100);
	cursor:pointer
}
</style>
<script>
function delt(){
	if(confirm('Do you want to remove this Department?'))
		return true;
	else
		return false; 
}
</script>
<!-- body content: start here-->
<div align="right" style="padding-bottom:20px;"><a id="AddNewDept" class="btn btn-info btn-small thickbox" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="showThis(this.id,'department-add-head.php?','New Department Add')">Add Head Department</a></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list"></i><span class="break"></span>Department List</h2>
		</div>
		<div class="box-content">
			<div class="table-wrapper">
				
				<table id="tblist" width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
						<?php
						$qList = $db->select('department','*',array('dep_head'=>NULL),'ORDER BY dep_name');
						while($rList = $db->fetch_array($qList)):
							$dep_name = ($rList['dep_name']) ? ' ('.$rList['dep_name'].')' : '';
							$allowed_del = ($db->getValue('department','count(*)',array('dep_head'=>$rList['dep_id']))==0) ? 1 : 0;
						?>
						<tr id="rw<?php echo $rList['dep_id']?>">
							<td width="25%" >
								<strong><?php echo $rList['dep_desc'].' '.$dep_name;?></strong>&nbsp;&nbsp;&nbsp;
								<a id="AddSubItem<?php echo $rList['dep_id']?>" class="thickbox opt" style="cursor:pointer" title="Add Sub Item" data-rel="tooltip" onclick="showThis(this.id,'department-add.php?itemID=<?php echo functions::encode($rList['dep_id']);?>','Department Manage')"><i class="halflings-icon plus-sign"></i></a>
								<a id="ManageItem<?php echo $rList['dep_id']?>" class="thickbox opt" style="cursor:pointer" title="Manage Department" data-rel="tooltip" onclick="showThis(this.id,'department-manage.php?itemID=<?php echo functions::encode($rList['dep_id'])?>','Manage Department')"><i class="halflings-icon pencil"></i></a>
								<a id="DeleteItem<?php echo $rList['dep_id']?>" class="opt" data-rel="tooltip" <?php if($allowed_del){?> style="cursor:pointer" title="Remove this department" onClick="return delt()" href="?delItemID=<?php echo functions::encode($rList['dep_id'])?>&itemID=<?php echo functions::encode($rList['dep_base'])?>"<?php }else{?>title="Sub-department must be removed first!" style="cursor:not-allowed;opacity: 0.1;"<?php }?> ><i class="halflings-icon minus-sign"></i></a>
							</td>
						</tr>
						<?php
						disp($rList['dep_id'],1);
						endwhile;
						?>
						<tr>
							<td>&nbsp;</td>
						</tr>
				</table><p>&nbsp;</p>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<?php require_once('templ_down.php');?>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>function itemSel(PiEwgD){window.location="<?php echo functions::pageName()?>?itemID="+PiEwgD}</script>
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