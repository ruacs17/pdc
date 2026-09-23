<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['tProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : '';
	$_SESSION['tMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['tYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo(functions::pageName());
	die();
}
$delTo = (isset($_REQUEST['delTo']) && !empty($_REQUEST['delTo']) ) ? functions::decode($_REQUEST['delTo']) : 0;
if($delTo){
	$tod = $db->getValue('travel_order_detail','count(*)',array('to_id'=>$delTo));
	if($tod==0){
		$db->delete('travel_order',array('to_id'=>$delTo));
		$_SESSION['notif_warning']='Travel Order Removed!';
	}
	functions::sendTo(functions::pageName());
	die();
}
$arr = array();
$projID = ( isset($_SESSION['tProj']) && !empty($_SESSION['tProj']) ) ? $_SESSION['tProj'] : '';
$txbMon = ( isset($_SESSION['tMon']) ) ? $_SESSION['tMon'] : date('m');
$txbYear = ( isset($_SESSION['tYear']) ) ? $_SESSION['tYear'] : date('Y');

if($txbMon && $txbYear)
	$arr = array('LEFT(to_date,7)'=>$txbYear.'-'.$txbMon);
else if($txbMon)
	$arr = array('SUBSTRING(to_date,6,2)'=>$txbMon);
elseif($txbYear)
	$arr = array('LEFT(to_date,4)'=>$txbYear);

if($projID)
	$arr = array_merge($arr,array('proj_id'=>$projID));
?>
<style type="text/css">
.table-wrapper th{
	background: #DDD;
	position: sticky;
	top: 0;
}
</style>
<!-- body content: start here-->
<div align="right"><a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'travel_manage.php?','Travel Order Form')">Add Travel Order Form</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Travel Order</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0" width="90%">
						<tr>
							<td width="20%" style="padding-top: 8px;">
								<div align="center">
									<select name="bdYear" id="bdYear" style="width:90px;">
										<option value="">All Year</option>
										<?php
										$qYr = $db->select('travel_order','DISTINCT LEFT(to_date,4) as yr',array(),'ORDER BY to_date DESC');
										while($rYr = $db->fetch_array($qYr)):
										?>
										<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
										<?php endwhile;?>
									</select>
									<select name="bdMon" id="bdMon" style="width:105px;">
										<option value="">All Month</option>
										<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
								</div>
							</td>
							<td width="50%" style="padding-top: 7px;">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:98%;font-size:12px;">
									<option value="">--All Projects / Department--</option>
									<?php 
									$qProj = $db->select('project','*',array(),'WHERE proj_id IN (SELECT DISTINCT proj_id FROM travel_order) ORDER BY proj_name');
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
				<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size: 12px;">
					<thead>
						<tr style="background-color:#CCC;">
							<th width="9%" scope="col">Travel Date</th>
							<th width="30%" scope="col">Project / Department</th>
							<th width="15%" scope="col">Purpose</th>
							<th width="10%" scope="col"><div align="center">Options</div></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$qDisp = $db->select('travel_order','*',$arr,'ORDER BY to_date DESC LIMIT 50');
						while($rDisp = $db->fetch_array($qDisp)):
							$rID = $rDisp['to_id'];
							$tod = $db->getValue('travel_order_detail','count(*)',array('to_id'=>$rID));
						?>
						<tr id="rw<?php echo $rID?>">
							<td><?php echo functions::datearr($rDisp['to_date'])?></td>
							<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rDisp['proj_id']));?></td>
							<td><?php echo $rDisp['purpose']?></td>
							<td>
								<div align="left" style="padding-left: 10px;">
									<a id="vw<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="Travel Order Detail" data-rel="tooltip" onclick="showThis(this.id,'travel_manage_detail.php?to=<?php echo functions::encode($rID);?>','Travel Order Detail')"><i class="halflings-icon white zoom-in"></i></a>
									<a id="vwEdt<?php echo $rID?>" class="btn btn-mini btn-warning thickbox" title="Travel Order Detail" data-rel="tooltip" onclick="showThis(this.id,'travel_manage.php?to=<?php echo functions::encode($rID);?>','Travel Order Detail')"><i class="halflings-icon white pencil"></i></a>
									<a id="print<?php echo $rID?>" class="btn btn-mini btn-success thickbox" title="Print Travel Order Detail" data-rel="tooltip" onclick="showThis(this.id,'travel_print.php?to=<?php echo functions::encode($rID);?>','Travel Order Detail','1')"><i class="halflings-icon white print"></i></a>
									<?php if($tod==0){?>
									<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Travel Order" data-rel="tooltip" href="?delTo=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
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
<script>
function delt(){
	if(confirm('Do you want to remove this Travel Order?'))
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
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){/*
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
*/
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$('.table-wrapper').animate({
		scrollTop: $('#rw<?php echo $_SESSION['notif_id'] ?>').offset().top - 400
	}, 100);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>