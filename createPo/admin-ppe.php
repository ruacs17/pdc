<?php require_once('templ_up.php');?>
<?php
$arr = array();
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';
$txbYear = ''; 
$txSearchPPE = ( isset($_POST['txSearchPPE']) && isset($_POST['txSearchPPE']) ) ? $_POST['txSearchPPE'] : '';

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['ppe_eu'] = (isset($_POST['selUser']) && !empty($_POST['selUser']) ) ? $_POST['selUser'] : 0;
	$_SESSION['ppe_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : 0;
	$_SESSION['ppe_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
}
if($itemDel){
	$amount = $db->getValue('ppe_item','count(*)',array('ppe_id'=>$itemDel));
	if($amount == 0){
		$db->delete('ppe',array('ppe_id'=>$itemDel));
		$_SESSION['notif_warning']='PPE Issuance Removed!';
		functions::sendTo(functions::pageName());
		die();
	}
}

$txUser = ( isset($_SESSION['ppe_eu']) ) ? $_SESSION['ppe_eu'] : '';
$txbYear = ( isset($_SESSION['ppe_yr']) ) ? $_SESSION['ppe_yr'] : date('Y');
$txbMon = ( isset($_SESSION['ppe_mn']) ) ? $_SESSION['ppe_mn'] : date('m');

if($txbMon && $txbYear)
	$arr = array('LEFT(ppe_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
	$arr = array('LEFT(ppe_date,4)'=>$txbYear);
elseif($txbMon)
	$arr = array('SUBSTRING(ppe_date,6,2)'=>$txbMon);

if($txUser){
	if( $db->getValue('ppe','count(*)',array('emp_id'=>$txUser)) )
		$arr = array_merge($arr,array('emp_id'=>$txUser));
}

if(count($arr)){
	$rowdisplay=1000;
	$startrow=0;
}
if($txSearchPPE)
	$qList = $db->select('ppe','*',array('ppe_no'=>$txSearchPPE));
else
	$qList = $db->select('ppe','*',$arr,'ORDER BY ppe_date DESC');

$num_record = $db->getValue('ppe','count(*)',$arr);

$arrChargeList=array();
$qUser = $db->query('SELECT eu.emp_id,fname,mname,lname FROM ppe, employee eu WHERE ppe.emp_id=eu.emp_id GROUP BY emp_id,fname,mname,lname ORDER BY eu.lname,eu.fname');
while($rUser = $db->fetch_array($qUser)):
	$nme = $rUser['lname'].', '.$rUser['fname'].' '.$rUser['mname'];
	$arrChargeList[$nme]=$rUser['emp_id'];
endwhile;
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>ISSUANCE OF PERSONAL PROTECTIVE EQUIPMENT</h2>
		</div>
		<div class="box-content">
			<div align="right">
				<a id="ppeAdd" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'admin-ppe-add.php?','PPE Create')">Create New PPE</a>
				<a id="ppeStock" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'admin-ppe-stock-list.php?','PPE Stock')">Stock</a>
				<a id="ppeMonitoring" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'admin-ppe-monitoring.php?','PPE Monitoring')">PPE Monitoring</a>
			</div><br>
			<form method="post">
				<table class="table" border="0">
					<tr>
						<td colspan="3" height="70">IPPE No: <input type="text" name="txSearchPPE" id="txSearchPPE" value="<?php echo $txSearchPPE;?>">&nbsp;<input type="submit" name="btnSearchPPE" id="btnSearchPPE" value="Search" class="btn btn-primary btn-small"></td>
					</tr>
					<tr>
						<td width="20%">
							<div align="left">
								<select name="bdYear" id="bdYear" style="width:90px;">
									<option value="">All Year</option>
									<?php
									$qYr = $db->select('ppe','DISTINCT LEFT(ppe_date,4) as yr',array(),'ORDER BY ppe_date DESC');
									while($rYr = $db->fetch_array($qYr)):
									?>
									<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
									<?php endwhile;?>
								</select>
								<select name="bdMon" id="bdMon" style="width:95px;">
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
						<td width="35%" style="padding: 12px 0px 0px 0px">
							<div align="left">
								<select name="selUser" id="selUser" data-rel="chosen" style="width:650px;">
									<option value="">All User</option>
									<?php foreach($arrChargeList as $name => $id): ?>
									<option value="<?php echo $id?>" <?php if($txUser===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
									<?php endforeach;?>
								</select>
							</div>
						</td>
						<td width="8%">
							<div align="center">
								<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small">
							</div>
						</td>
					</tr>
					<tr><td colspan="3"><hr width="100%"></td></tr>
				</table> 
			</form>
			<table id="tblist" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="9%">IPPE Date</th>
						<th width="10%">IPPE No.</th>
						<th width="60%">Project</th>
						<th width="7%"><div align="center">Manage</div></th>
					</tr>
				</thead>
				<tbody>
				<?php while($rList = $db->fetch_array($qList)):?>
					<tr id="rw<?php echo $rList['ppe_id'];?>">
						<td><?php echo functions::datearr($rList['ppe_date']);?></td>
						<td><?php echo $rList['ppe_no'];?></td>
						<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id']));?></td>
						<td>
							<div align="left" style="padding-left:7px;">
								<a id="detail<?php echo $rList['ppe_id']?>" class="btn btn-mini btn-info thickbox" title="Manage IPPE item" data-rel="tooltip" onclick="showThis(this.id,'admin-ppe-item-manage.php?ppe_id=<?php echo functions::encode($rList['ppe_id']);?>','Manage Item')"><i class="halflings-icon white plus-sign"></i></a>
								<?php if( $db->getValue('ppe_item','count(*)',array('ppe_id'=>$rList['ppe_id']))==0 ){ ?>
								<a id="del<?php echo $rList['ppe_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this IPPE" data-rel="tooltip" href="<?php echo functions::pageName()?>?itemDel=<?php echo functions::encode($rList['ppe_id']);?>"><i class="halflings-icon white trash"></i></a>
								<?php }?>
							</div>
						</td>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
			<div align="center"><?php #functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
	if(confirm('Do you want to remove this Leasing?'))
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
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({
		borderColor:"#87EAC1"
	}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({
		borderColor:""
	}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>