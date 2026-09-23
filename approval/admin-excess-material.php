<?php require_once('templ_up.php');?>
<?php
$arr = array();
if( isset($_REQUEST['recieve']) ){
	$_SESSION['xrc_recieve']=1;
	$_SESSION['xrc_release']=0;
}
if( isset($_REQUEST['release']) ){
	$_SESSION['xrc_recieve']=0;
	$_SESSION['xrc_release']=1;
}
$recieve = ( isset($_SESSION['xrc_recieve']) ) ? $_SESSION['xrc_recieve'] : 0;
$release = ( isset($_SESSION['xrc_release']) ) ? $_SESSION['xrc_release'] : 0;
if( empty($recieve) && empty($release) ){
	$_SESSION['xrc_recieve']=1;
	$recieve=1;
}
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mon='';$txProj = '';$txPayee = ''; 
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
unset($_SESSION['im_arr_proj'],$_SESSION['RefID']);
if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['xrc_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
	$_SESSION['xrc_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	$_SESSION['xrc_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['xrc_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
	$_SESSION['xrc_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
	$_SESSION['xrc_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
	$_SESSION['xrc_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
	functions::sendTo(functions::pageName());
	die();
}

$txProj = ( isset($_SESSION['xrc_proj']) ) ? $_SESSION['xrc_proj'] : '';

$txbYear = ( isset($_SESSION['xrc_yr']) ) ? $_SESSION['xrc_yr'] : date('Y');
$txbMon = ( isset($_SESSION['xrc_mn']) ) ? $_SESSION['xrc_mn'] : date('m');
$txbDay = ( isset($_SESSION['xrc_day']) ) ? $_SESSION['xrc_day'] : date('d');

$txbYearTo = ( isset($_SESSION['xrc_yrTo']) ) ? $_SESSION['xrc_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['xrc_mnTo']) ) ? $_SESSION['xrc_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['xrc_dayTo']) ) ? $_SESSION['xrc_dayTo'] : date('d');

$where = 'WHERE 1'; 
$table_name = ($recieve) ? 'material_excess_receive' : 'material_excess_release';
$table_date = ($recieve) ? 'merc_date' : 'merl_date';
$table_id = ($recieve) ? 'merc_id' : 'merl_id';

if($itemDel){
	#$amount = $db->getValue($table_name,'count(*)',array($table_id=>$itemDel));
	#if($amount == 0){
		$db->delete($table_name,array($table_id=>$itemDel));
		$_SESSION['notif_warning']='Record Removed!';
		functions::sendTo(functions::pageName());
		die();
	#}
}


if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
	$where .=' AND ('.$table_date.' BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
	$where .=' AND ( LEFT('.$table_date.',7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT('.$table_date.',7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
	$where .=' AND (SUBSTRING('.$table_date.',6,2)>="'.$txbMon.'" AND SUBSTRING('.$table_date.',6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
	$where .=' AND (LEFT('.$table_date.',4) >= "'.$txbYear.'" AND LEFT('.$table_date.',4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
	$where .=' AND '.$table_date.'="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
	$where .=' AND proj_id="'.$db->clean($txProj).'"';
}

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}

$qList = $db->select($table_name,'*',array(),$where.' ORDER BY '.$table_date.' DESC');
$num_record = $db->getValue($table_name,'count(*)',$arr);

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM '.$table_name.') ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
	if($rProj['proj_id'])
		$arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>MATERIAL EXCESS - <?php echo ($recieve) ? 'Receive' : 'Release'; ?></h2>
			<div class="box-icon">
				<a id="receive" <?php if( empty($recieve) ){?> onmouseover="this.style.opacity='.9';" onmouseout="this.style.opacity='.5';"<?php }?> style="text-decoration:none;cursor:pointer;<?php if( empty($recieve) ){?>opacity:.4<?php }?>" href="?recieve=receive"><span style="color:white">Receive</span>&nbsp;&nbsp;<span class="break"></span>
				<a id="release" <?php if( empty($release) ){?> onmouseover="this.style.opacity='.9';" onmouseout="this.style.opacity='.5';"<?php }?> style="text-decoration:none;cursor:pointer;<?php if( empty($release) ){?>opacity:.4<?php }?>" href="?release=release"><span style="color:white">Release&nbsp;&nbsp;</span>
			</div>
		</div>
		<div class="box-content">
			<div align="right">
				<?php if( $recieve ){?><a id="addrecv" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'admin-excess-material-receive-manage.php?','Receiving Report')">Add New Receiving Report</a>&nbsp;&nbsp;<?php }?>
				<?php if( $release ){?><a id="addrecv" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'admin-excess-material-release-manage.php?','Releasing Report')">Add New Releasing Report</a>&nbsp;&nbsp;<?php }?>
			</div><br>
			<form method="post">
				<table class="table" border="0">
					<tr>
						<td width="20%">
							<div align="center">
								<div align="center"><strong>FROM</strong></div>
								<select name="bdYear" id="bdYear" style="width:90px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('inhouse_material','DISTINCT LEFT(im_date,4) as yr',array(),'ORDER BY im_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMon" id="bdMon" style="width:85px;">
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
								<select name="bdDay" id="bdDay" style="width:60px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
							<div align="center">
								<div align="center"><strong>TO</strong></div>
								<select name="bdYearTo" id="bdYearTo" style="width:90px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('inhouse_material','DISTINCT LEFT(im_date,4) as yr',array(),'ORDER BY im_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMonTo" id="bdMonTo" style="width:85px;">
									<option value="">All Month</option>
									<option value="01" <?php if($txbMonTo=='01')echo 'selected="selected"';?>>Jan</option>
									<option value="02" <?php if($txbMonTo=='02')echo 'selected="selected"';?>>Feb</option>
									<option value="03" <?php if($txbMonTo=='03')echo 'selected="selected"';?>>Mar</option>
									<option value="04" <?php if($txbMonTo=='04')echo 'selected="selected"';?>>Apr</option>
									<option value="05" <?php if($txbMonTo=='05')echo 'selected="selected"';?>>May</option>
									<option value="06" <?php if($txbMonTo=='06')echo 'selected="selected"';?>>Jun</option>
									<option value="07" <?php if($txbMonTo=='07')echo 'selected="selected"';?>>Jul</option>
									<option value="08" <?php if($txbMonTo=='08')echo 'selected="selected"';?>>Aug</option>
									<option value="09" <?php if($txbMonTo=='09')echo 'selected="selected"';?>>Sep</option>
									<option value="10" <?php if($txbMonTo=='10')echo 'selected="selected"';?>>Oct</option>
									<option value="11" <?php if($txbMonTo=='11')echo 'selected="selected"';?>>Nov</option>
									<option value="12" <?php if($txbMonTo=='12')echo 'selected="selected"';?>>Dec</option>
								</select>
								<select name="bdDayTo" id="bdDayTo" style="width:60px;">
									<option value="">Day</option>
									<?php for($i=1;$i<=31;$i++):?>
									<option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
									<?php endfor;?>
								</select>
							</div>
						</td>
						<td width="35%">
							<div align="left">
								<select name="selProj" id="selProj" data-rel="chosen" style="width:99%;">
									<option value="">All Project</option>
									<?php foreach($arrChargeList as $name => $id): ?>
									<option value="<?php echo $id?>" <?php if($txProj===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
									<?php endforeach;?>
								</select>
							</div>
						</td>
						<td width="8%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></div></td>
					</tr>
					<tr><td colspan="4"><hr width="100%"></td></tr>
				</table>
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_merc_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="9%">Date</th>
						<th width="9%">Series Number</th>
						<th width="40%">Project</th>
						<th width="10%"><div align="center">Amount</div></th>
						<th width="11%"><div align="center">Options</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$totalAmount=0;

				while($rList = $db->fetch_array($qList)):
					$id=$rList[$table_id];
					$amount=0;
					$allow_delete=0;
					$pageEdit='admin-excess-material-release-manage.php?merlid='.functions::encode($id);
					$manageItemLink='admin-excess-material-release-manage-detail.php?mid='.functions::encode($id);
					$printItemLink='admin-excess-material-release-print.php?mid='.functions::encode($id);
					if($recieve){
						$pageEdit='admin-excess-material-receive-manage.php?mercid='.functions::encode($id);
						$manageItemLink='admin-excess-material-receive-manage-detail.php?mid='.functions::encode($id);
						$amount = $db->getValue('material_excess_receive_detail','sum(totalcost)',array('merc_id'=>$id));
						$printItemLink='admin-excess-material-receive-print.php?mid='.functions::encode($id);
						#$allow_delete = $db->getValue('material_excess_receive_detail','count(*)',array('merc_id'=>$id));
					}
					else{
						$amount = $db->getValue('inhouse_material_item imi, material_excess_release_detail merld','sum( (quantity * cost) )',array('merl_id'=>$id),'AND imi.im_id=merld.im_id');
						#echo $db->last_query;
					}
					$totalAmount+=$amount;
				?>
					<tr id="pl<?php echo $id?>">
						<td><?php echo functions::datearr($rList[$table_date]);?></td>
						<td><?php echo $rList['series_no'];?></td>
						<td><?php echo ($rList['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id'])) : $db->getValue('inhouse_material','payee',array('im_id'=>$rList['im_id']));?></td>
						<td><div align="right"><?php echo functions::formatMoney($amount);?></div></td>
						<td>
							<div align="left" style="padding-left:7px;">
								<a id="detail<?php echo $id?>" class="btn btn-mini btn-info thickbox" title="Manage Receive item" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $manageItemLink?>','Material Details')"><i class="halflings-icon white plus-sign"></i></a>
								<a id="edit<?php echo $id?>" class="btn btn-mini btn-warning thickbox" title="Modify Receive Order" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $pageEdit?>','Edit Material Details')"><i class="halflings-icon white pencil"></i></a>
								<a id="print<?php echo $id?>" class="btn btn-mini btn-success thickbox" title="Print Receive Order" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $printItemLink?>','Material Print','1')"><i class="halflings-icon white print"></i></a>
								<?php if( empty($amount) ){ ?>
								<a id="del<?php echo $id;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Receive Order" data-rel="tooltip" href="<?php echo functions::pageName()?>?itemDel=<?php echo functions::encode($id);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
			<table class="table table-bordered table-hover">
				<tr>
					<td width="9%">&nbsp;</td>
					<td width="49%"><div align="right"><strong>Total Amount</strong></div></td>
					<td width="10%"><div align="right"><strong><?php echo functions::formatMoney($totalAmount);?></strong></div></td>
					<td width="11%">&nbsp;</td>
				</tr>
			</table>
			<div align="center"><?php #functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
	if(confirm('Do you want to remove this Record?'))
		return true;
	else
		return false; 
}
</script>
<?php require_once('templ_down.php');?>
<?php if(isset($_SESSION['notif_id_merc_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#pl<?php echo $_SESSION['notif_id_merc_list'] ?>').centerView();
	$('#pl<?php echo $_SESSION['notif_id_merc_list'] ?>').css('border','3px solid green');
	$("#pl<?php echo $_SESSION['notif_id_merc_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#pl<?php echo $_SESSION['notif_id_merc_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_merc_list']);} ?>

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