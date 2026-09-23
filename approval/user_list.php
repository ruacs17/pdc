<?php require_once('templ_up.php');?>
<?php
$u_id=(isset($_REQUEST['uid']) && !empty($_REQUEST['uid']) ) ? functions::decode($_REQUEST['uid']) : 0;
$u_idDel=(isset($_REQUEST['uidDel']) && !empty($_REQUEST['uidDel']) ) ? functions::decode($_REQUEST['uidDel']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
if($u_id){
	$arrVal = array('user_id'=>$u_id);
}
#check PO
$purchaser = $db->getValue('po','count(*)',array('purchaser'=>$u_idDel));
$voucherPrepared = $db->getValue('voucher','count(*)',array('preparedBy'=>$u_idDel));
$voucherChecked = $db->getValue('voucher','count(*)',array('checkedBy'=>$u_idDel));
$voucherPrepared = $db->getValue('voucher','count(*)',array('preparedBy'=>$u_idDel));

if($u_idDel){
	$allowDel=1;
	if( $db->getValue('po','count(*)',array('purchaser'=>$u_idDel)) )
		$allowDel=0;
	else if( $db->getValue('po','count(*)',array('approved_by'=>$u_idDel)) )
		$allowDel=0;
	else if( $db->getValue('voucher','count(*)',array('preparedBy'=>$u_idDel)) )
		$allowDel=0;
	else if( $db->getValue('voucher','count(*)',array('checkedBy'=>$u_idDel)) )
		$allowDel=0;
	else if( $db->getValue('voucher','count(*)',array('approvedBy'=>$u_idDel)) )
		$allowDel=0;
	else if( $db->getValue('voucher','count(*)',array('approved'=>$u_idDel)) )
		$allowDel=0;
	else if( $db->getValue('project','count(*)',array('incharge'=>$u_idDel)) )
		$allowDel=0;

	if($allowDel){
		$db->delete('role_assignment',array('user_id'=>$u_idDel));
		$db->delete('users',array('user_id'=>$u_idDel));
		$_SESSION['notif_warning']='Account Removed!';
	}
	functions::sendTo('user_list.php');
	die();
}
?>
            <!-- body content: start here-->
<table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
	<tr>
		<td width="3%"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
		<td width="47%"> Inactive</td>
		<td width="50%"><div align="right"><a id="adc" href="#" class="btn btn-info btn-small thickbox" onclick="showThis(this.id,'user_add.php?','Account Detail')">Add New Account</a></div></td>
	</tr>
</table><br><br>

<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Account List</h2>
		</div>
		<div align="left"><br>&nbsp;&nbsp;
			<select name="selProj" id="selProj" data-rel="chosen" style="width:650px;" onChange="userSel(this.value)">
				<option value="">--All Account--</option>
				<?php $qUser = $db->select('users','*',array(),'ORDER BY lname');
				while($rUser = $db->fetch_array($qUser)):
				?>
				<option value="<?php echo functions::encode($rUser['user_id'])?>" <?php if($u_id==$rUser['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rUser['lname'].', '.$rUser['fname']);?></option>
				<?php endwhile;?>
			</select>
		</div>
		<div class="box-content">
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="20%">Username</th>
						<th width="30%">Name</th>
						<th width="20%">Access Type</th>
						<th width="10%"><div align="center">Options</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				if(!is_numeric($startrow))
					$startrow=0;
				$qUser = $db->select('users','*',$arrVal,'ORDER BY lname LIMIT '.$startrow.', '.$rowdisplay);
				$num_record = $db->getValue('users','count(*)',$arrVal);
				while($rUser = $db->fetch_array($qUser)):
					$allowDel=1;
					if( $db->getValue('po','count(*)',array('purchaser'=>$rUser['user_id'])) )
						$allowDel=0;
					else if( $db->getValue('po','count(*)',array('approved_by'=>$rUser['user_id'])) )
						$allowDel=0;
					else if( $db->getValue('voucher','count(*)',array('preparedBy'=>$rUser['user_id'])) )
						$allowDel=0;
					else if( $db->getValue('voucher','count(*)',array('checkedBy'=>$rUser['user_id'])) )
						$allowDel=0;
					else if( $db->getValue('voucher','count(*)',array('approvedBy'=>$rUser['user_id'])) )
						$allowDel=0;
					else if( $db->getValue('voucher','count(*)',array('approved'=>$rUser['user_id'])) )
						$allowDel=0;
					else if( $db->getValue('project_incharge','count(*)',array('user_id'=>$rUser['user_id'])) )
						$allowDel=0;
					$bgColor='';
					$consumedPercent=0;
					if($rUser['status']=='inactive')
						$bgColor = 'style="background-color:#f5ae00"';
				?>
					<tr id="rw<?php echo $rUser['user_id']?>" <?php echo $bgColor;?>>
						<td><?php echo $rUser['username'];?></td>
						<td><?php echo strtoupper($rUser['lname'].', '.$rUser['fname']);?></td>
						<td>
							<?php
							$qRole = $db->query('SELECT * FROM role_assignment ra, role r WHERE ra.role_id=r.role_id AND user_id="'.$db->clean($rUser['user_id']).'"');
							while( $rRole = $db->fetch_array($qRole)):
								echo $rRole['role_description'].'<br>';
							endwhile;
							?>
						</td>
						<td>
							<div align="left">
								<a id="detail<?php echo $rUser['user_id']?>" class="btn btn-mini btn-info thickbox" title="Account Detail" data-rel="tooltip" onclick="showThis(this.id,'user_info.php?uid=<?php echo functions::encode($rUser['user_id']);?>','Account Detail')"><i class="halflings-icon white zoom-in"></i></a>
								<a id="edit<?php echo $rUser['user_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Account" data-rel="tooltip" onclick="showThis(this.id,'user_edit.php?uid=<?php echo functions::encode($rUser['user_id']);?>','Account Detail')"><i class="halflings-icon white pencil"></i></a>
								<?php if($allowDel==1){?>
								<a id="del<?php echo $rUser['user_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this User" data-rel="tooltip" href="user_list.php?uidDel=<?php echo functions::encode($rUser['user_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
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
function userSel(PiEwgD){
	if(PiEwgD)
		window.location="user_list.php?uid="+PiEwgD 
	else 
		window.location="user_list.php"
}
function delt(){
	if(confirm('Do you want to remove this User?'))
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