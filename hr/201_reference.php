<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
$searchVal='';
if( isset($_REQUEST['rm']) && !empty($_REQUEST['rm']) ){
	$rm = functions::decode($_REQUEST['rm']);
	if( $db->getValue('emp_201','count(*)',array('e2r_id'=>$rm))==0 ){
		$db->delete('emp_201_reference',array('e2r_id'=>$rm));
	}
	$_SESSION['notif_success']='Checklist Removed!';
	functions::sendTo('?');
	die();
}

?>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'201_reference_manage.php?','Manage Reference')">Add New CheckList</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>201 Checklist References</h2>
		</div>
		<div class="box-content">
			<form method="post" style="display:none;">
				<div align="center">
					<table border="0">
						<tr>
							<td style="padding-top: 7px;"><input type="text" name="txSearch" id="txSearch" value="<?php echo $searchVal?>"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnAll" id="btnAll" value="View All"></td>
						</tr>
					</table>
				</div>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="27%" scope="col">NAME</th>
						<th width="5%" scope="col"><div align="center">MANAGE</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$qDisp = $db->select('emp_201_reference','*',array(),'ORDER BY e2r_name');
				while($rDisp = $db->fetch_array($qDisp)):
					$rID = $rDisp['e2r_id'];
				?>
					<tr id="rw<?php echo $rID?>">
						<td style="font-size: 14px;"><?php echo $rDisp['e2r_name'];?></td>
						<td>
							<div align="left" style="padding-left:60px;">
								<a id="vw<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="View Detail" data-rel="tooltip" onclick="showThis(this.id,'201_reference_view.php?eid=<?php echo functions::encode($rID);?>','201 View')"><i class="halflings-icon white zoom-in"></i></a>
								<a id="edit<?php echo $rID?>" class="btn btn-mini btn-warning thickbox" title="Modify Checklist" data-rel="tooltip" onclick="showThis(this.id,'201_reference_manage.php?eid=<?php echo functions::encode($rID);?>','201 Manage')"><i class="halflings-icon white pencil"></i></a>
								<?php if( $db->getValue('emp_201','count(*)',array('e2r_id'=>$rID))==0 ){ ?>
								<a href="?rm=<?php echo functions::encode($rID) ?>" class="btn btn-mini btn-danger" title="Remove Checklist" data-rel="tooltip" onClick="return delt()"><i class="halflings-icon white trash"></i></a>
								<?php } ?>
							</div>
						</td>
					</tr>
				<?php endwhile; ?>
				</tbody>
			</table>
			
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this checklist?'))return true; else return false;}</script>
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
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>